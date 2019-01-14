<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of uploadimage
 *
 * @author RyanJiang
 */

namespace upload;

class Uploadimage {

    //put your code here
    public $parm = array();
    public $savepath = "";         //上传图片保存的绝对路径，不是相对路径
    public $filetype = array('jpg', 'jpeg', 'gif', 'png'); //上传图片格式
    public $maxsize = 2097152;       //上传图片大小
    public $mkdir_mode = 0777;           //上传图片权限
    public $versions = array(//生成缩略图属性
        '50x50' => array(
            'max_width' => 50,
            'max_height' => 50,
            'quality' => 100
        ),
        '100x100' => array(
            'max_width' => 100,
            'max_height' => 100,
            'quality' => 100
        ),
        '150x150' => array(
            'max_width' => 150,
            'max_height' => 150,
            'quality' => 100
        ),
        '200x200' => array(
            'max_width' => 200,
            'max_height' => 200,
            'quality' => 100
        ),
        '400x400' => array(
            'max_width' => 400,
            'max_height' => 400,
            'quality' => 100
        ),
        '800x800' => array(
            'max_width' => 800,
            'max_height' => 800,
            'quality' => 100
        )
    );

    public function __construct($array) {
        (isset($array['savepath']) && $array['savepath'] != "") ? $this->parm['savepath'] = $array['savepath'] : $this->parm['savepath'] = $this->savepath;
        (isset($array['filetype']) && $array['filetype'] != "") ? $this->parm['filetype'] = $array['filetype'] : $this->parm['filetype'] = $this->filetype;
        (isset($array['maxsize']) && $array['maxsize'] != "") ? $this->parm['maxsize'] = $array['maxsize'] : $this->parm['maxsize'] = $this->maxsize;
        (isset($array['versions']) && $array['versions'] != "") ? $this->parm['versions'] = $array['versions'] : $this->parm['versions'] = $this->versions;
        (isset($array['post']) && $array['post'] != "") ? $this->parm['post'] = $array['post'] : $this->parm['post'] = "";
    }

    /**
     * 如果上传出错 返回错误，成功返回上传后的图片name
     * @return string
     */
    public function uploadImage() {
        //判断图片格式是否能上传
        $fileParts = pathinfo($this->parm['post']['file']['name']);
        if (!in_array(strtolower($fileParts['extension']), $this->parm['filetype'])) {
            return $returnArray['error'] = 'formatError';
        }
        //判断尺寸是否能上传
        if ($this->parm['post']['file']['size'] > $this->parm['maxsize']) {

            return $returnArray['error'] = 'sizeBigError';
        }
        //图片新name
        $fileName = date('YmdHis') . rand(10000, 99999) . "." . $fileParts['extension'];
        $tempFile = $this->parm['post']['file']['tmp_name'];
        $targetFile = $this->parm['savepath'] . '/' . $fileName;
        if (@move_uploaded_file($tempFile, $targetFile)) {
            $this->scaledimages($targetFile, $fileName, $this->parm['savepath']);
            return $returnArray['error'] = $fileName;
        } else {
            return $returnArray['error'] = 'image save path error';
        }
    }

    /**
     * @param 图片的资源目录         $targetFile 
     * @param 图片的名称                $file_name
     * @param 图片开始的绝对路径  $targetPath
     */
    public function scaledimages($targetFile, $fileName, $targetPath) {
        if (is_array($this->parm['versions']) && count($this->parm['versions']) != 0) {
            foreach ($this->parm['versions'] as $version => $options) {
                $this->createscaledimage($targetFile, $fileName, $targetPath . '/' . $version . '/', $options);
            }
        }
    }

    /**
     * @param 图片的资源目录      $targetFile
     * @param 图片的名称             $fileName
     * @param 生成缩略图的路劲  $newfilePath
     * @param 生成缩略图的属性  $options
     * @return boolean
     */
    public function createscaledimage($targetFile, $fileName, $newfilePath, $options) {
        if ($newfilePath != '') {
            if (!is_dir($newfilePath)) {
                mkdir($newfilePath, $this->mkdir_mode, true);
            }
        } else {
            return false;
        }
        list ($img_width, $img_height) = getimagesize($targetFile);
        if (!$img_width || !$img_height) {
            return false;
        }
        $new_file_url = $newfilePath . $fileName;
        $scale = min($options['max_width'] / $img_width, $options['max_height'] / $img_height);
        if ($scale >= 1) {
            return copy($targetFile, $new_file_url);
            return true;
        }

        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($fileName, '.'), 1))) {
            case 'jpg' :
            case 'jpeg' :
                $src_img = imagecreatefromjpeg($targetFile);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ? $options['jpeg_quality'] : 75;
                break;
            case 'gif' :
                imagecolortransparent($new_img, imagecolorallocate($new_img, 0, 0, 0));
                $src_img = imagecreatefromgif($targetFile);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png' :
                imagecolortransparent($new_img, imagecolorallocate($new_img, 0, 0, 0));
                imagealphablending($new_img, false);
                imagesavealpha($new_img, true);
                $src_img = imagecreatefrompng($targetFile);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ? $options['png_quality'] : 9;
                break;
            default :
                $src_img = null;
        }
        $success = $src_img && imagecopyresampled($new_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $img_width, $img_height) && $write_image($new_img, $new_file_url, $image_quality);
        imagedestroy($src_img);
        imagedestroy($new_img);
        return $success;
    }

}
