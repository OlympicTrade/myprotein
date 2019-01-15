<?php
namespace Aptero\File;

class File
{
    /**
     * @var string
     */
    protected $filePath = '';

    /**
     * @var string
     */
    protected $resultDirPath = '';

    /**
     * @var string
     */
    protected $fileName = '';

    public function remove($recursive = false)
    {
        if($recursive) {
            $files = glob($this->getFilePath() . '/*/' . $this->getFileName());
            foreach ($files as $file) {
                unlink($file);
            }
        }

        $file = $this->getFilePath() . '/' . $this->getFileName();
        if(file_exists($file)) {
            unlink($file);
        }

        $this->setFileName('');

        return $this;
    }

    public function save()
    {
        $fileName = $this->getFileName();
        $filePath = $this->getFilePath();

        if(empty($fileName)) {
            $fileName = \Aptero\String\StringFn::randomString() . self::getFileType($filePath);
        }

        $fileName = $this->getUniqueFilename($fileName);

        $newFilePath = $this->getResultDirPath() . '/' . $fileName;

        if (!copy($filePath, $newFilePath)) {
            return false;
        }

        return $fileName;
    }

    public function rename($filename, $recursive = false)
    {
        $filename = $filename . self::getFileType($this->getFileName());

        $oldFIlename = $this->getFilePath() . '/' . $this->getFileName();
        $newFIlename = $this->getFilePath() . '/' . $filename;

        if(!file_exists($oldFIlename) || $oldFIlename == $newFIlename) {
            return $this;
        }

        if($recursive) {
            $dirs = glob($this->getFilePath() . '/*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                if(file_exists($dir . '/' . $this->getFileName())) {
                    rename($dir . '/' . $this->getFileName(), $dir . '/' . $filename);
                }
            }
        }

        rename($oldFIlename, $newFIlename);

        $this->setFileName($filename);

        return $this;
    }

    /**
     * @param $filePath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = rtrim($filePath, '/');

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param $resultDir
     * @return $this
     */
    public function setResultDirPath($resultDir)
    {
        $this->resultDirPath = $resultDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getResultDirPath()
    {
        return $this->resultDirPath;
    }

    /**
     * @param $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getUniqueFilename($fileName) {
        if(!$fileName) {
            return '';
        }

        $type = strtolower(strrchr($fileName, "."));
        $name = rtrim($fileName, $type);

        $filePath = $this->getResultDirPath();

        $i = 1;
        while(true) {
            if(!file_exists($filePath . '/' . $fileName)) {
                return $fileName;
            }

            $fileName = $name . '_' . $i . $type;
            $i++;
        }
    }

    static public function getFileType($fileName)
    {
        return strtolower(strrchr($fileName, "."));
    }

    static public function getClearFileName($filename)
    {
        return rtrim(basename($filename), strrchr($filename, "."));
    }
}
