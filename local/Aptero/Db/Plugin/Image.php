<?php
namespace Aptero\Db\Plugin;

use Aptero\Db\Plugin\PluginAbstract;

use Aptero\Exception\Exception;
use Aptero\File\Image as ApteroImage;
use Aptero\File\File as ApteroFile;

class Image extends PluginAbstract
{
    const THUMBS_PATH = '/files/thumbs';

    const ERROR_FORMAT = 1;
    const ERROR_SIZE   = 2;
    const ERROR_FILE   = 3;

    /**
     * @var string
     */
    protected $maxFileSize = 2000000;

    /**
     * @var string
     */
    protected $folder = null;

    /**
     * @var array
     */
    protected $resolutions = [];

    /**
     * @var array
     */
    protected $image = [
        'filename'  => '',
        'desc'      => '',
    ];

    /**
     * @var array
     */
    protected $imageNew = null;

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var bool
     */
    protected $changed = false;

    /**
     * @param string $folder
     * @return $this
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @param array $versions
     */
    public function addResolutions($versions)
    {
        foreach($versions as $prefix => $options) {
            $this->addResolution($prefix, $options);
        }
    }

    public function addResolution($prefix, $options)
    {
        $default = array(
            'width'       => 2000,
            'height'      => 2000,
            'crop'        => false,
            'watermark'   => false,
        );

        $options = array_merge($default, $options);

        if(array_key_exists($prefix, $this->resolutions)) {
            throw new \Aptero\Db\Exception\RuntimeException('Image version "' . $prefix . '" already exists');
        }

        if($prefix == '') {
            throw new \Aptero\Db\Exception\RuntimeException('Image prefix can\'t be empty');
        }

        $this->resolutions[$prefix] = array(
            'width'      => $options['width'],
            'height'     => $options['height'],
            'watermark'  => $options['watermark'],
            'crop'       => $options['crop'],
            'updated'    => false
        );

        return $this;
    }

    /**
     * @return bool
     */
    public function hasImage()
    {
        $this->load();

        return $this->image['filename'] ? true : false;
    }

    /**
     * @param $prefix
     * @return string
     * @throws Exception
     */
    public function getImage($prefix)
    {
        if(!isset($this->resolutions[$prefix])) {
            throw new Exception('Image prefix "' . $prefix . '" not found');
        }

        $this->load();

        $filename = $this->image['filename'] ? $this->image['filename'] : 'default.png';
        $fullPath = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder;
        $urlPath = self::THUMBS_PATH . '/' . $this->folder . '/' . $prefix . '/' . $filename;

        if(file_exists($fullPath . '/' . $prefix . '/' . $filename)) {
            return $urlPath;
        }

        $imageObj = new ApteroImage();
        $imageObj->setFilePath($fullPath . '/' . $filename);
        $imageObj->setResultDirPath($fullPath . '/' . $prefix);

        $imageObj->setOptions(array(
            'width'  => $this->resolutions[$prefix]['width'],
            'height' => $this->resolutions[$prefix]['height'],
            'crop'   => $this->resolutions[$prefix]['crop'],
        ));

        $imageObj->createThumbnail();

        return $urlPath;
    }

    public function setImage($data)
    {
        $this->load();

        $filePath = $data['filepath'];

        if($filePath) {
            list($width, $height, $type) = getimagesize($filePath);

            if(!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
                return self::ERROR_FORMAT;
            }

            if(file_exists($filePath) && filesize($filePath) > $this->maxFileSize) {
                return self::ERROR_SIZE;
            }

            $this->imageNew = array(
                'filepath'  => $filePath,
            );
        }

        if($data['filename']) {
            $this->imageNew['filename'] =  \Aptero\String\Translit::url($data['filename']);
        } else {
            $this->imageNew['filename'] = \Aptero\String\StringFn::randomString();
        }

        if($data['desc']) {
            $this->imageNew['desc'] = $data['desc'];
        }

        $this->changed = true;

        return 0;
    }

    public function getFileName()
    {
        return ApteroFile::getClearFileName($this->image['filename']);
    }

    public function get($name)
    {
        return isset($this->image[$name]) ? $this->image[$name] : '';
    }

    public function load()
    {
        $parentId = $this->getParentId();

        if(!$parentId) {
            return $this;
        }

        if($this->loaded) {
            return $this;
        }

        if($this->cacheLoad()) {
            $this->loaded = true;
            return $this;
        }

        $select = clone $this->select();

        $select
            ->where(array('t.' . $this->parentFiled => $parentId));

        $result = $this->fetchRow($select);

        if($result) {
            $this->fill($result);
        }

        $this->loaded = true;

        $this->cacheSave($result);

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function updateImageFile() {
        if(empty($this->imageNew)) {
            return $this;
        }

        $this->load();

        if($this->image['filename']) {
            $oldFilename = $this->image['filename'];

            //Rename
            if(!$this->imageNew['filepath']) {
                $imageObj = new ApteroFile();
                $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/');
                $imageObj->setFileName($oldFilename);
                $imageObj->rename($this->imageNew['filename'], true);

                return $this;
            }
        }

        //Replace file
        $imageObj = new ApteroImage();
        $filePath = $this->imageNew['filepath'];
        $fileName = $this->imageNew['filename'];

        $fullPath = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder;

        $imageObj->setFileName($fileName);
        $imageObj->setFilePath($filePath);
        $imageObj->setResultDirPath($fullPath);

        $newFileName = $imageObj->save();
        $this->image['filename'] = $newFileName;

        if(isset($oldFilename)) {
            $imageObj = new ApteroImage();

            foreach($this->resolutions as $prefix => $resolution) {
                $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $prefix);
                $imageObj->setFileName($oldFilename);
                $imageObj->remove();
            }

            $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder);
            $imageObj->setFileName($oldFilename);
            $imageObj->remove();
        }

        return $this;
    }

    public function save($transaction = false)
    {
        if(!$this->changed) {
            return true;
        }

        $this->load();

        $this->updateImageFile();

        if(!$this->id) {
            $insert = $this->insert();
            $insert->values($this->image + [$this->parentFiled => $this->getParentId()]);

            $this->execute($insert);

            $this->id = $this->adapter->getDriver()->getLastGeneratedValue();
        } else {
            $update = $this->update();
            $update->where([$this->primary => $this->id]);

            $update->set($this->image);

            $this->execute($update);
        }

        $this->cacheClear();

        return true;
    }

    protected function removeFile($filename)
    {
       /* $imageObj = new ApteroFile();
        $imageObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $this->image['filename']);
        $imageObj->setFileName($filename);
        $imageObj->remove();*/


    }

    public function remove()
    {
        $this->load();

        $imageObj = new ApteroImage();

        if($this->image['filename']) {
            $imageObj
                ->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder)
                ->setFileName($this->image['filename'])
                ->remove(true);
        }

        $delete = $this->delete();
        $delete->where([
            $this->parentFiled => $this->getParentId(),
            'id' => $this->getId()
        ]);

        $this->execute($delete);

        $this->image = [
            'filename'  => '',
            'desc'      => '',
        ];

        $this->id = 0;

        $this->changed = false;

        $this->cacheClear();

        return true;
    }

    /**
     * @param $data
     * @return Image
     */
    public function fill($data)
    {
        $this->image['filename'] = $data['filename'];
        $this->id                = $data['id'];

        unset($data['filename']);
        unset($data['id']);

        foreach($data as $key => $val) {
            $this->image[$key] = $val;
        }

        $this->loaded = true;

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getCacheName($name = '') {
        return 'db-plugin-image-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
    }

    /**
     * @return bool
     */
    protected function cacheLoad()
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = @$this->getCacheName($this->getParentId());

        if($data = $this->getCacheAdapter()->getItem($cacheName)) {
            $this->fill($data);
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function cacheSave($data)
    {
        if(!$this->cacheEnabled || !$this->getCacheAdapter()) {
            return false;
        }

        $cacheName = @$this->getCacheName($this->getParentId());

        $this->getCacheAdapter()->setItem($cacheName, $data);
        $this->getCacheAdapter()->setTags($cacheName, array($this->table()));

        return true;
    }

    /**
     * @return bool
     */
    protected function cacheClear()
    {
        if(!$this->getCacheAdapter()) {
            return false;
        }

        $this->getCacheAdapter()->clearByTags(array($this->table()));
        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    public function unserializeArray($data)
    {
        if(!isset($data['image']) || !is_array($data['image'])) {
            return true;
        }

        $imageInfo = $data['image'];

        if(empty($imageInfo['filepath']) && empty($imageInfo['filename']) && empty($imageInfo['desc'])) {
            return true;
        }

        if(isset($imageInfo['del']) && $imageInfo['del'] == 'on') {
            $this->remove();
            return true;
        }

        if($imageInfo['filepath']) {
            $imageInfo['filepath'] = PUBLIC_DIR . urldecode($imageInfo['filepath']);
        }

        $this->setImage($imageInfo);

        return true;
    }

    /**
     * @param $result
     * @param string $prefix
     * @return array
     */
    public function serializeArray($result = array(), $prefix = '')
    {
        $this->load();

        $result[$prefix . 'file'] = '';

        return $result;
    }
}