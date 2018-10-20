<?php
namespace CatalogAdmin\Model\Plugin;

use Aptero\Db\Plugin\Image;
use Aptero\Db\Plugin\Images;
use Aptero\File\Image as ApteroImage;

class ProductImages extends Images
{
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
                'size_id'  => $image['size_id'],
                'taste_id' => $image['taste_id'],
                'sort'     => $image['sort'],
                $this->parentFiled => $this->getParentId(),
            ));

            $this->execute($insert);
        }
    }

    /**
     * @param $data
     * @return Image
     */
    public function fill($data)
    {
        $this->images = [];
        foreach($data as $row) {
            $this->images[] = array(
                'filename'  => $row['filename'],
                'desc'      => $row['desc'],
                'taste_id'  => $row['taste_id'],
                'size_id'   => $row['size_id'],
                'sort'      => $row['sort'],
                'id'        => $row['id'],
            );
        }

        $this->loaded = true;

        return $this;
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
                    'size_id'   => $dataToAdd['size_id'][$i],
                    'taste_id'  => $dataToAdd['taste_id'][$i],
                    'sort'      => $dataToAdd['sort'][$i],
                ];
            }
        }

        return true;
    }

    public function current()
    {
        $imageData = current($this->images);

        $image = new ProductImage();
        $image->setTable($this->table());
        $image->setFolder($this->folder);
        $image->addResolutions($this->resolutions);
        $image->fill($imageData);
        $image->setParent($this->getParent());

        return $image;
    }
}