<?php
class Tool_Uploadh5{
	public $savePath;
	private $extension;
    public function getSaveName(){
		$newName = md5(md5(date('YmdHis') . rand(1000, 99999)).'pyp123');
		return $newName;
	}
    function uploadh5($pImg,$tSavename){
		list($tType, $tData) = explode(',', $pImg); 

        if(strstr($tType,'image/jpeg')!==''){  
            $this->extension = 'jpg';  
        }elseif(strstr($tType,'image/gif')!==''){  
             $this->extension ='gif';  
        }elseif(strstr($tType,'image/png')!==''){  
 			 $this->extension ='png';  
        }else{
            Tool_Fnc::ajaxMsg('非法文件'); 
        } 
         
        $tDir = $this->savePath;
        $this->createDir($tDir);
		$res = Tool_Fnc::writefile($tDir.'/'.$tSavename.'.'.$this->extension,base64_decode($tData));
        //return 1;
		//if($res){
			return $tDir.'/'.$tSavename.'.'.$this->extension;
		//}else{
		//	return false;
		//}
	}

	/**
	 * Create thumb
     * @access public
     * $sourceImg: source image
     * $targetWidth  缩略图宽度
     * $targetHeight 缩略图高度
	 * $path: target path to store thumb image
	 *
     * @return full path + file name if success 
     */
	function createThumbh5($targetWidth, $targetHeight, $path){
		$sourceImg = $this->savePath.'/'.$this->saveName.$this->extension;
		
		// Get image size
		$originalSize = getimagesize($sourceImg);
		
		// Set thumb image size
		$targetSize = $this->setWidthHeight($originalSize[0], $originalSize[1], $targetWidth, $targetHeight);
		
		// Get image extension
		$this->getExtension($sourceImg);
		
		// Determine source image type
		if($this->extension == 'gif'){
			$src = imagecreatefromgif($sourceImg);
		}elseif($this->extension == 'png'){
			$src = imagecreatefrompng($sourceImg);
		}elseif ($this->extension == 'jpg' || $this->extension == 'jpeg'){
			$src = imagecreatefromjpeg($sourceImg);
		}else{
			return $this->errorArr[0];
		}
		
		// Copy image
		$dst = imagecreatetruecolor($targetSize[0], $targetSize[1]);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetSize[0], $targetSize[1],$originalSize[0], $originalSize[1]);    
		
		if(!file_exists($path)){
			if(!$this->createDir($path)){
				return $this->errorArr[5];
			}
		}
		
		// Path + fileName
		$thumbName = $path.$this->saveName;
		
		if($this->extension == 'gif'){
			imagegif($dst, $thumbName);
		}else if($this->extension == 'png'){
			imagepng($dst, $thumbName);
		}else if($this->extension == 'jpg' || $this->extension == 'jpeg'){
			imagejpeg($dst, $thumbName, 100);
		}else{
			return $this->errorArr[6];
		}
		
		imagedestroy($dst);
		imagedestroy($src);
		return $thumbName;
	}
	/**
	 * Check file info before upload and set target file name if everything is OK
	 */ 
	function checkFileInfo(){
		$valid = 1;
	
		// Check file type
		if(!in_array($this->extension, $this->validTypes)){
			$this->error = $this->errorArr[0];
			$valid = false;
		}
		
		// Check file size
		if($this->size > $this->maxSize){
			$this->error = $this->errorArr[1];
			$valid = false;
		}
		
		if(!file_exists($this->savePath)){
			$this->createDir($this->savePath);
		}
				
		// Check whether save path is writable
		if(!is_writable($this->savePath)){
			$this->error = $this->errorArr[3];
			$valid = false;
		}
				
		// Set target name
		$this->setTargetName();
		
		// File exists ?
		if(!$this->overWrite && file_exists($this->savePath.$this->saveName)){
			$this->error = $this->errorArr[2];
			$valid = false;
		}
		
		return $valid;
	}

	
	/**
	 * Copy file
     */
	function copyFile(){
		// Copy file to final path
		if(move_uploaded_file($this->tempName, $this->savePath.$this->saveName)){
			if($this->delete){
				@unlink($this->tempName);
			}
			//raiseError($traceInfo, $this->errorArr[4]);
		}

		return true;
	}


	/**
     * set target name
     */
	function setTargetName(){
		// saveName is not set, generate a random file name
		if(!isset($this->saveName)){ 
			srand((double)microtime() * 1000000);
			$rand = rand(100, 999);
			$finalName  = date('U') + $rand;
			$finalName .= '.'.$this->extension;
		}else{
			$finalName = $this->saveName.'.'.$this->extension;
		}
		
		$this->saveName = $finalName;
	}
	
		
	
	
	/**
	 * Set thumb image width and height
	 */
	function setWidthHeight($width, $height, $maxWidth, $maxHeight) {
		if($width > $height){
			if($width > $maxWidth){
				$difinwidth = $width/$maxWidth;
				$height = intval($height/$difinwidth);
				$width  = $maxWidth;
				
				if($height > $maxHeight){
					$difinheight = $height/$maxHeight;
					$width  = intval($width/$difinheight);
					$height = $maxHeight;
				}
			}else{
				if($height > $maxHeight){
					$difinheight = $height/$maxHeight;
					$width  = intval($width/$difinheight);
					$height = $maxHeight;
				}
			}
		}else{
			if($height > $maxHeight){
				$difinheight = $height/$maxHeight;
				$width  = intval($width/$difinheight);
				$height = $maxHeight;
				
				if($width > $maxWidth){
					$difinwidth = $width/$maxWidth;
					$height = intval($height/$difinwidth);
					$width  = $maxWidth;
				}
			}else{
				if($width > $maxWidth){
					$difinwidth = $width/$maxWidth;
					$height = intval($height/$difinwidth);
					$width  = $maxWidth;
				}
			}
		}
		
		$final = array($width, $height);
		return $final;
	}
	
	
	/**
	 * Functionality: Add watermark
	 * @Params:
			$source: source img with path
			$destination: target img with path
			$watermarkPath: water mark img
	 *  @Retrun: image with watermark
	 */
	function addWatermark($source, $destination, $watermarkPath){
		list($owidth,$oheight) = getimagesize($source);
		$width = $height = 300;
		$im = imagecreatetruecolor($width, $height);
		$img_src = imagecreatefromjpeg($source);
		imagecopyresampled($im, $img_src, 0, 0, 0, 0, $width, $height, $owidth, $oheight);
		$watermark = imagecreatefrompng($watermarkPath);
		list($w_width, $w_height) = getimagesize($watermarkPath);
		$pos_x = $width - $w_width;
		$pos_y = $height - $w_height;
		imagecopy($im, $watermark, $pos_x, $pos_y, 0, 0, $w_width, $w_height);
		imagejpeg($im, $destination, 100);
		imagedestroy($im);
	}
	
	
	/**
	 * Get file extension 
	 */
	function getExtension($fileName){
		$info = pathinfo($fileName);    
		$this->extension = strtolower($info['extension']);
	}
	
	/**
	 * Create directory recursively
	 */
	function createDir($folder){
		$reval = false;
		if(!file_exists($folder)){
			@umask(0);
			preg_match_all('/([^\/]*)\/?/i', $folder, $atmp);
			$base = ($atmp[0][0] == '/') ? '/' : '';
			
			foreach($atmp[1] AS $val) {
				if('' != $val){
					$base .= $val;
					if ('..' == $val || '.' == $val) {
						$base .= '/';
						continue;
					}
				}else{
					continue;
				}

				$base .= '/';

				if(!file_exists($base)){
					if(@mkdir($base, 0777)){
						@chmod($base, 0777);
						$reval = true;
					}
				}
			}
		}else{
			$reval = is_dir($folder);
		}
		
		clearstatcache();
		return $reval;
	}
}
