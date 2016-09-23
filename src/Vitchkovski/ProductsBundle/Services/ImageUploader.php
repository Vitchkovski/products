<?php

namespace Vitchkovski\ProductsBundle\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function upload(UploadedFile $file, $userId)
    {
        //generating new filename
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        //moving original image to the user's folder
        $file->move($this->targetDir.$userId.'/original', $fileName);

        //generating cropped image 64x64
        $productImage = new resizeImage();
        $productImage->load($this->targetDir."/".$userId."/original/".$fileName);
        $productPictureHeight = $productImage->getHeight();
        $productPictureWidth = $productImage->getWidth();
        if ($productPictureHeight >= $productPictureWidth)
            $productImage->resizeToHeight(64);
        else
            $productImage->resizeToWidth(64);

        //moving cropped 64x64 image to the destination folder
        @mkdir($this->targetDir.$userId."/cropped/64/", 0777, true);
        $productImage->save($this->targetDir.$userId."/cropped/64/".$fileName);

        //generating cropped image 128x128
        $productImage->load($this->targetDir."/".$userId."/original/".$fileName);
        $productPictureHeight = $productImage->getHeight();
        $productPictureWidth = $productImage->getWidth();
        if ($productPictureHeight >= $productPictureWidth)
            $productImage->resizeToHeight(128);
        else
            $productImage->resizeToWidth(128);

        //moving cropped 64x64 image to the destination folder
        @mkdir($this->targetDir.$userId."/cropped/128/", 0777, true);
        $productImage->save($this->targetDir.$userId."/cropped/128/".$fileName);


        return $fileName;
    }
}

class resizeImage
{
    var $image;
    var $image_type;
    function load($filename)
    {
        $image_info = getimagesize($filename);
        $this->image_type = $image_info[2];
        if ($this->image_type == IMAGETYPE_JPEG) {
            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->image_type == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
        } elseif ($this->image_type == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filename);
        }
    }
    function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {
        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } elseif ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename);
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }
    function getWidth()
    {
        return imagesx($this->image);
    }
    function getHeight()
    {
        return imagesy($this->image);
    }
    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }
    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }
    function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
    }
}