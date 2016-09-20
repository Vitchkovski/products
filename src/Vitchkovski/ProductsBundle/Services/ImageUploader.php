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

        //generating cropped image
        $productImage = new resizeImage();
        $productImage->load($this->targetDir."/".$userId."/original/".$fileName);
        $productPictureHeight = $productImage->getHeight();
        $productPictureWidth = $productImage->getWidth();
        if ($productPictureHeight >= $productPictureWidth)
            $productImage->resizeToHeight(48);
        else
            $productImage->resizeToWidth(48);

        //moving cropped image to the destination folder
        @mkdir($this->targetDir.$userId."/cropped/", 0777, true);
        $productImage->save($this->targetDir.$userId."/cropped/".$fileName);


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