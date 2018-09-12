<?php 
/** 
* desription 压缩图片 
* @param sting $imgsrc 图片路径 
* @param string $imgdst 压缩后保存路径 
*/
function image_png_size_add($imgsrc,$imgdst){ 
  list($width,$height,$type)=getimagesize($imgsrc); 
  $new_width = ($width>600?600:$width)*0.9; 
  $new_height =($height>600?600:$height)*0.9; 
  switch($type){ 
    case 1: 
      $giftype=check_gifcartoon($imgsrc); 
      if($giftype){ 
        header('Content-Type:image/gif'); 
        $image_wp=imagecreatetruecolor($new_width, $new_height); 
        $image = imagecreatefromgif($imgsrc); 
        imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
        imagejpeg($image_wp, $imgdst,75); 
        imagedestroy($image_wp); 
      } 
      break; 
    case 2: 
      header('Content-Type:image/jpeg'); 
      $image_wp=imagecreatetruecolor($new_width, $new_height); 
      $image = imagecreatefromjpeg($imgsrc); 
      imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
      imagejpeg($image_wp, $imgdst,75); 
      imagedestroy($image_wp); 
      break; 
    case 3: 
      header('Content-Type:image/png'); 
      $image_wp=imagecreatetruecolor($new_width, $new_height); 
      $image = imagecreatefrompng($imgsrc); 
      imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
      imagejpeg($image_wp, $imgdst,75); 
      imagedestroy($image_wp); 
      break; 
  } 
} 