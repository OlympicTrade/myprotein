<?php
namespace Aptero\Db\Plugin;

use Aptero\File\Image as ApteroImage;
use Aptero\File\File as ApteroFile;

class Images extends PluginAbstract implements \Iterator
{
    const THUMBS_PATH = '/files/thumbs';

    /**
     * @var string
     */
    protected $folder = null;

    /**
     * @var array
     */
    protected $resolutions = array();

    /**
     * @var array
     */
    protected $images = array();

    /**
     * @var array
     */
    protected $imagesNew = array();

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
            'width'       => 3000,
            'height'      => 3000,
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
     * @param string $prefix
     * @return string
     */
    public function getImage($prefix)
    {
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

    public function getFileName()
    {
        return ApteroFile::getClearFileName($this->image['filename']);
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

        $result = $this->fetchAll($select);

        if($result) {
            $this->fill($result);
        }

        $this->loaded = true;

        $this->cacheSave($result);

        return $this;
    }

    public function save($transaction = false)
    {
        if(empty($this->imagesNew)) {
            return;
        }

        foreach($this->imagesNew as $image) {
            $imageObj = new ApteroImage();
            $imageObj->setFileName(\Aptero\String\StringFn::randomString());
            $imageObj->setFilePath(PUBLIC_DIR . $image['filename']);
            $imageObj->setResultDirPath($fullPath = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder);

            $fileName = $imageObj->save();

            $insert = $this->insert();
            $insert->values(array(
                'filename' => $fileName,
                'desc'     => $image['desc'],
                $this->parentFiled => $this->getParentId(),
            ));

            $this->execute($insert);
        }
    }

    public function remove()
    {
        foreach($this as $image) {
            $image->remove();
        }
    }

    /**
     * @param $data
     * @return Image
     */
    public function fill($data)
    {
        $this->images = array();
        foreach($data as $row) {
            $this->images[] = array(
                'filename'  => $row['filename'],
                'desc'      => $row['desc'],
                'id'        => $row['id'],
            );
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
        if(empty($data) || empty($data['images'])) {
            return true;
        }

        $data = $data['images'];

        if(isset($data['del'])) {
            foreach($this as $image) {
                if(in_array($image->getId(), $data['del'])) {
                    $image->remove();
                }
            }
        }

        if(isset($data['add'])) {
            $dataToAdd = $data['add'];
            for($i = 0; $i < count($dataToAdd['path']); $i++) {
                $this->imagesNew[] = [
                    'filename'  => urldecode($dataToAdd['path'][$i]),
                    'desc'      => $dataToAdd['desc'][$i],
                    'sort'      => $dataToAdd['sort'][$i],
                ];
            }
        }

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

    public function count()
    {
        return count($this->images);
    }

    /* Iterator */
    public function rewind()
    {
        $this->load();
        reset($this->images);

        return $this;
        //return reset($this->images);
    }

    public function current()
    {
        $imageData = current($this->images);

        $image = new Image();
        $image->setTable($this->table());
        $image->setFolder($this->folder);
        $image->addResolutions($this->resolutions);
        $image->fill($imageData);
        $image->setParent($this->getParent());

        return $image;
    }

    public function key()
    {
        return key($this->images);
    }

    public function next()
    {
        return next($this->images);
    }

    public function valid()
    {
        $key = key($this->images);
        return ($key !== null && $key !== false);
    }
}