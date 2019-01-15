<?php
namespace Aptero\Db\Plugin;

use Aptero\Db\Plugin\PluginAbstract;

use Aptero\Exception\Exception;
use Aptero\File\File as ApteroFile;

class File extends PluginAbstract
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
    protected $file = array(
        'filename'  => '',
    );

    /**
     * @var array
     */
    protected $fileNew = null;

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
     * @return bool
     */
    public function hasFile()
    {
        $this->load();

        return $this->file['filename'] ? true : false;
    }

    public function getFile()
    {
        $this->load();

        $filename = $this->file['filename'];
        $urlPath = self::THUMBS_PATH . '/' . $this->folder . '/' . $filename;

        return $urlPath;
    }

    public function setFile($data)
    {
        $this->load();

        $filePath = $data['filepath'];

        if($filePath && file_exists($filePath)) {
            $this->fileNew = array(
                'filepath'  => $filePath,
            );
        }

        $this->changed = true;

        return 0;
    }

    public function getFileName()
    {
        return ApteroFile::getClearFileName($this->file['filename']);
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

    public function updateFile() {
        if(empty($this->fileNew)) {
            return $this;
        }

        $this->load();

        if($this->file['filename']) {
            $oldFilename = $this->file['filename'];

            //Rename
            if(!$this->fileNew['filepath']) {
                $fileObj = new ApteroFile();
                $fileObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/');
                $fileObj->setFileName($oldFilename);
                $fileObj->rename($this->fileNew['filename']);
                $this->file['filename'] = $fileObj->getFileName();

                return $this;
            }
        }

        //Replace file
        $fileObj = new ApteroFile();
        $filePath = $this->fileNew['filepath'];
        $fileName = $this->fileNew['filename'];

        $fullPath = PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder;

        $fileObj->setFileName($fileName);
        $fileObj->setFilePath($filePath);
        $fileObj->setResultDirPath($fullPath);

        $newFileName = $fileObj->save();
        $this->file['filename'] = $newFileName;

        if(isset($oldFilename)) {
            $fileObj = new ApteroFile();
            $fileObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder);
            $fileObj->setFileName($oldFilename);
            $fileObj->remove();
        }

        return $this;
    }

    public function save($transaction = false)
    {
        if(!$this->changed) {
            return true;
        }

        $this->load();

        $this->updateFile();

        if(!$this->id) {
            $insert = $this->insert();
            $insert->values(array(
                'filename' => $this->file['filename'],
                $this->parentFiled => $this->getParentId(),
            ));

            $this->execute($insert);

            $this->id = $this->adapter->getDriver()->getLastGeneratedValue();
        } else {
            $update = $this->update();
            $update->where(array($this->primary => $this->id));

            $update->set(array(
                'filename' => $this->file['filename'],
            ));

            $this->execute($update);
        }

        $this->cacheClear();

        return true;
    }

    public function remove()
    {
        $this->load();

        $fileObj = new ApteroFile();

        if($this->file['filename']) {
            $fileObj->setFilePath(PUBLIC_DIR . self::THUMBS_PATH . '/' . $this->folder . '/' . $this->file['filename']);
            $fileObj->remove();
        }

        $delete = $this->delete();
        $delete->where(array(
            $this->parentFiled => $this->getParentId(),
        ));

        $this->execute($delete);

        $this->file = array(
            'filename'  => '',
        );

        $this->id = 0;

        $this->changed = false;

        $this->cacheClear();

        return true;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fill($data)
    {
        $this->file['filename'] = $data['filename'];
        $this->id               = $data['id'];

        $this->loaded = true;

        return $this;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getCacheName($name = '') {
        return 'db-plugin-file-' . str_replace('_', '-', $this->table()) . ($name ? '-' . $name : '');
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
        if(!isset($data['file']) || !is_array($data['file'])) {
            return true;
        }

        $fileInfo = $data['file'];

        if(isset($fileInfo['del']) && $fileInfo['del'] == 'on') {
            $this->remove();
            return true;
        }

        if(empty($fileInfo['filepath'])) {
            return true;
        }

        if($fileInfo['filepath']) {
            $fileInfo['filepath'] = PUBLIC_DIR . urldecode($fileInfo['filepath']);
        }

        $this->setFile($fileInfo);

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