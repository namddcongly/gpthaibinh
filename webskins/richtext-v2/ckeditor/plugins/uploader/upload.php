<?php
//type=Images&CKEditor=content&CKEditorFuncNum=2&langCode=vi
define('DS','/');
$uploadFilePath = preg_replace('#\/#i', DS, $_SERVER['DOCUMENT_ROOT']).DS.'data'.DS.'upload'.DS;
$uploadReturnPath = '/data/upload/'.$_GET['type'].DS;

class Uploader
{

	var $allowedTypes = array(
	'jpeg',
	'gif',
	'png',
	'pjpeg',
	'x-png',
	'jpg'
	);

	function uploadImage($uploadedInfo, $uploadTo)
	{
		$webpath = $uploadTo;
		$upload_dir = str_replace("/", DS, $uploadTo);
		$upload_path = $upload_dir;
		$max_file = "2097152"; 						// Approx 2MB
		//	$max_width = 500;
		$userfile_name = $uploadedInfo['name'];
		$userfile_tmp =  $uploadedInfo["tmp_name"];
		$userfile_size = $uploadedInfo["size"];
		$filename = basename($uploadedInfo["name"]);
		$file_ext = strtolower(substr($filename, strrpos($filename, ".") + 1));
		$uploadTarget = $upload_path.$filename;
		if(empty($uploadedInfo)) {
			return false;
		}
		if(!$this->checkType($file_ext)){
			return false;
		}
		if (isset($uploadedInfo['name'])){
			move_uploaded_file($userfile_tmp, $uploadTarget );
		}
		return array('imagePath' => $webpath.$filename, 'imageName' => $filename,'uploadTo'=>$uploadTo,'imageWidth' => $this->getWidth($uploadTarget), 'imageHeight' => $this->getHeight($uploadTarget));
	}

	function checkType($extension)
	{
		foreach($this->allowedTypes as $value){
			if($extension == strtolower($value)){
				return true;
			}
		}
		// $this->_error("FileUpload::_checkType() {$this->uploadedFile['type']} is not in the allowedTypes array.");
		return false;
	}
	function getHeight($image) {
		$sizes = getimagesize($image);
		$height = $sizes[1];
		return $height;
	}
	function getWidth($image) {
		$sizes = getimagesize($image);
		$width = $sizes[0];
		return $width;
	}
	function SendUploadResults( $uploadReturnPath,$imageName, $customMsg = '' )
	{
		$funcNum = $_GET['CKEditorFuncNum'];
		$file = $uploadReturnPath.$imageName;

		echo "<script type=\"text/javascript\">";
		echo 'alert("upload");';
		echo "(function(){var d=document.domain;while (true){try{var A=window.parent.document.domain;break;}catch(e) {};d=d.replace(/.*?(?:\.|$)/,'');if (d.length==0) break;try{document.domain=d;}catch (e){break;}}})();";
		echo 'window.parent.CKEDITOR.tools.callFunction('.$funcNum.',"'.$file.'","'.$customMsg.'")';
		echo '</script>' ;
		exit ;
	}
}
$uploader = new Uploader();
$uploadTo = $uploadFilePath.$_GET['type'].DS;
$info = $uploader->uploadImage($_FILES['upload'], $uploadTo);
$uploader->SendUploadResults($uploadReturnPath,$info['imageName']);
?>