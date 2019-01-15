<?php
namespace Aptero\File;

use Aptero\Exception\Exception;
use Aptero\File\File;

class Image extends File
{
    /**
     * @var array
     */
    protected $options = array(
        'width'     => 2000,
        'height'    => 2000,
        'watermark' => false,
        'crop'      => false,
        'quality'   => ['jpg' => 90, 'png' => 9],
    );

    public function save()
    {
        $fileName = $this->getFileName();
        $filePath = $this->getFilePath();

        list($width, $height, $type) = getimagesize($filePath);

        switch ($type) {
            case IMAGETYPE_GIF:
                $fileType = '.gif';
                break;
            case IMAGETYPE_JPEG:
                $fileType = '.jpg';
                break;
            case IMAGETYPE_PNG:
                $fileType = '.png';
                break;
            default:
                return false;
        }

        if(empty($fileName)) {
            $fileName = rtrim(basename($filePath), strrchr($filePath, "."));
        }

        $fileName .= $fileType;

        $fileName = $this->getUniqueFilename($fileName);

        $newFilePath = $this->getResultDirPath() . '/' . $fileName;

        //echo $filePath . "\n" . $newFilePath; die();

        if (!copy($filePath, $newFilePath)) {
            return false;
        }

        return $fileName;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);

        return $this;
    }

    public function createThumbnail()
    {
        if(!is_dir($this->getResultDirPath())) {
            mkdir($this->getResultDirPath(), 0777, true);
        }

        $filePath = $this->getFilePath();

        if(!file_exists($filePath)) {
            return '';
        }

        $fileName = $this->getFileName() ? $this->getFileName() : basename($filePath);

        $newFile = $this->getResultDirPath() . '/' . $fileName;

        list($sourceWidth, $sourceHeight, $type) = getimagesize($filePath);

        switch ($type) {
            case IMAGETYPE_GIF:
                $imageSource = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_JPEG:
                $imageSource = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $imageSource = imagecreatefrompng($filePath);
                break;
            default:
                return false;
        }

        if (!$imageSource) {
            return false;
        }

        if(!$this->options['crop']) {
            $newImage = $this->resizeWithoutCrop($type, $imageSource, $sourceWidth, $sourceHeight, $this->options['width'], $this->options['height']);
        } else {
            $newImage = $this->resizeWithCrop($type, $imageSource, $sourceWidth, $sourceHeight, $this->options['width'], $this->options['height']);
        }

        if($this->options['watermark']) {
            //$newImage = $this->setWatermark($newImage);
        }

        if($type == IMAGETYPE_PNG) {
            imagepng($newImage, $newFile, $this->options['quality']['png']);
        } else {
            imagejpeg($newImage, $newFile, $this->options['quality']['jpg']);
        }

        imagedestroy($imageSource);
        imagedestroy($newImage);

        return $newFile;
    }

    protected function setTransparency($image)
    {
        imagesavealpha($image, true);
        imagealphablending($image, true);
        $transparency = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparency);
        imagecolortransparent($image, $transparency);
    }

    protected function resizeWithoutCrop($type, $source, $sourceWidth, $sourceHeight, $maxWidth, $maxHeight)
    {
        $ratio = $sourceWidth / $sourceHeight;

        $maxWidth = ($maxWidth == 0 ? $sourceWidth : min($sourceWidth, $maxWidth));
        $maxHeight = ($maxHeight == 0 ? $sourceHeight : min($sourceHeight, $maxHeight));

        $newWidth = $maxWidth;
        $newHeight = $newWidth / $ratio;

        if ($newHeight > $maxHeight) {
            $newHeight = $maxHeight;
            $newWidth = $newHeight * $ratio;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        $rgb = imagecolorat($source, 0, 0);
        $colors = imagecolorsforindex($source, $rgb);

        if($colors['alpha'] = 127 && $type == IMAGETYPE_PNG) {
            $this->setTransparency($newImage);
            $this->setTransparency($source);
        }

        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        return $newImage;
    }

    protected function resizeWithCrop($type, $source, $sourceWidth, $sourceHeight, $maxWidth, $maxHeight)
    {
        $source_aspect_ratio = $sourceWidth / $sourceHeight;
        $desired_aspect_ratio = $maxWidth / $maxHeight;

        if ($source_aspect_ratio > $desired_aspect_ratio) {
            $temp_height = $maxHeight;
            $temp_width = (int) ($maxHeight * $source_aspect_ratio);
        } else {
            $temp_width = $maxWidth;
            $temp_height = (int) ($maxWidth / $source_aspect_ratio);
        }

        if($maxWidth > $sourceWidth) {
            $temp_width = $sourceWidth;
            $maxWidth = $sourceWidth;
        }

        if($maxHeight > $sourceHeight) {
            $temp_height = $sourceHeight;
            $maxHeight = $sourceHeight;
        }

        $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);

        $rgb = imagecolorat($source, 0, 0);
        $colors = imagecolorsforindex($source, $rgb);

        if($colors['alpha'] = 127 && $type == IMAGETYPE_PNG) {
            $this->setTransparency($source);
            $this->setTransparency($temp_gdim);
        }

        imagecopyresampled($temp_gdim, $source, 0, 0, 0, 0, $temp_width, $temp_height, $sourceWidth, $sourceHeight);

        $x0 = ($temp_width - $maxWidth) / 2;
        $y0 = ($temp_height - $maxHeight) / 2;

        $desired_gdim = imagecreatetruecolor($maxWidth, $maxHeight);

        if($colors['alpha'] = 127 && $type == IMAGETYPE_PNG) {
            $this->setTransparency($desired_gdim);
        }

        imagecopy($desired_gdim, $temp_gdim, 0, 0, $x0, $y0, $maxWidth, $maxHeight);

        return $desired_gdim;
    }
}
