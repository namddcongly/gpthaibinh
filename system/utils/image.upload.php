<?php
//defined ( 'IN_JOC' ) or die ( 'Restricted Access' );

/**
@author: NamDD < namdd@xahoinet.vn>
* Huong dan su dung
* require_once(UTILS_PATH.'system.upload.php');
$uploader = ObjInput::createObject('Uploader');
$uploader->setMaxSize(500);
$uploader->setFileType('custom',array('jpg','jpeg','png','gif','swf'));
$res = $uploader->doUpload('name_input');
*/
class Uploader
{

	//Instance to keep Uploader object.
	private static $instance;

	//Private properties of the class, change them with the setters methods bellow.
	private $uploadPath = 'data/upload/';
	private $newName = false;
	private $allowOverwrite = false;
	private $newExtension =false;
	private $encryptName = false;
	private $maxSize = 2000;
	private $maxHeight = 2000;
	private $maxWidth = 2000;
	private $fileType = 'image';
	private $error = false;
	private $acceptExt = false;
	private $fileExt = "";

	//No direct instantiating or cloning of the object. We need only one.
	public function __construct(){
	}

	private function __clone(){}
	public function doUpload($formFile)
	{
		$path = $this->uploadPath;
		if ( ! $this->validateUploadPath())
		{
			// errors will already be set by validate_upload_path() so just return FALSE
			return FALSE;
		}
		//No file to upload.
		if(!isset($_FILES[$formFile]) OR $_FILES[$formFile]['error'] != 0)
		{
			$this->setError('Bạn chưa chọn file để upload');
		}
		//Check if it's uploaded file.
		if(!is_uploaded_file($_FILES[$formFile]['tmp_name']))
		{
			$this->_errorDelete($formFile);
			$this->setError('File upload chưa thành công');
		}
		//Get the extension or false.
		$this->fileExt = $this->_isCorrectFile($_FILES[$formFile]['type'], $this->fileType, $formFile);
		if(!$this->fileExt)
		{
			$this->_errorDelete($formFile);
			$this->setError('Định dạng file ' . $this->fileExt . ' không được phép tải lên ');
		}

		//Check for any upload errors.
		if($_FILES[$formFile]['error'] != 0)
		{
			$this->_errorDelete($formFile);
			$this->setError('Có lỗi trong khi upload file. Vui lòng thử lại');
		}

		//Check for the maximum size of the file.
		if($_FILES[$formFile]['size'] > $this->maxSize * 1024)
		{
			$this->_errorDelete($formFile);
			$this->setError('Kích thước file không được vượt quá '. $this->maxSize .' kb');
		}

		//If we have image. Check for maximum width and height.
		if($this->_isExtImg($extension))
		{
			$dimension = @getimagesize($_FILES[$formFile]['tmp_name']);
			if($dimension[0] > $this->maxWidth)
			{
				$this->_errorDelete($formFile);
				$this->setError('Chiều dài file ảnh vượt quá kích thước cho phép tối đa là' .$this->maxWidth .' px');
			}
			if($dimension[1] > $this->maxHeight)
			{
				$this->_errorDelete($formFile);
				$this->setError('Chiều cao file ảnh vượt quá kích thước cho phép tối đa là' . $this->maxHeight . ' px');
			}
		}

		//Clean the name and check if it's empty.
		if(!$this->newName){
			$this->newName = $this->_cleanFileName($_FILES[$formFile]['name']);
			//var_dump($this->newName);
			if($this->newName  == ''){
				$this->_errorDelete($formFile);
				$this->setError('Tên file không được chứa các ký tự đặc biệt');
			}
			if ($this->allowOverwrite == FALSE){
				$this->newName = $this->setFilename($this->uploadPath, $this->newName);

				if ($this->newName === FALSE)
				{
					return FALSE;
				}
			}
		}else{
			@chmod($this->uploadPath.$this->newName,0777);
		}
		//Try to copy or move the uploaded file. This is from the CI upload class.

		if(!@copy($_FILES[$formFile]['tmp_name'], $this->uploadPath.$this->newName))
		{
			if(!@move_uploaded_file($_FILES[$form_file]['tmp_name'], $this->uploadPath.$this->newName))
			{
				$this->_errorDelete($formFile);
				$this->setError('Không thể upload được file');
			}
		}

		//Return the path and the new name with the extension... Change it to whatever you want.
		if($this->error !== false) return array('error'=>$this->error);
		//        $res = $this->moveUpload($path.$this->newName);
		return array('path'=>$path,'name'=>$this->newName,'ext'=>$this->fileExt);

	}

	//Public setter methods for more flexibility.
	public function setPath($path)
	{
		$this->uploadPath = rtrim($path, '/').'/';
	}

	public function setNewName($name)
	{
		$this->newName = $name;
	}
	public function setOverwrite($value)
	{
		$this->allowOverwrite = $value;
	}

	public function setMaxSize($size)
	{
		$this->maxSize = $size;
	}

	public function setMaxWidth($width)
	{
		$this->maxWidth = $width;
	}

	public function setMaxHeight($height)
	{
		$this->maxHeight = $height;
	}

	public function setFileType($type,$acceptExt = false)
	{
		$this->fileType = $type;
		$this->acceptExt =  $acceptExt;
	}

	private function setError($string){
		$this->error[] = $string;
	}

	//Private methods.

	//Try to delete the file without throwing error.
	private function _errorDelete($formFile)
	{
		@unlink($_FILES[$formFile]['tmp_name']);
	}

	//If you want to add more types, extend this method. And add another method to check for the new type.
	private function _isCorrectFile($file, $type = 'image', $formFile)
	{
		switch($type)
		{
			case 'image':
				return $this->_isImage($file);
				break;
			case 'xml':
				return $this->_isXml($formFile);
				break;
			case 'custom':
				return $this->_isCustom($formFile);
				break ;

		}
	}

	//Methods to check if the file is xml. Return is xml
	private function _isXml($formFile)
	{
		$extension = strtolower(end(explode('.', $_FILES[$formFile]['name'])));
		if($extension == 'xml')
		{
			return 'xml';
		}
		return false;
	}

	//Possible returns are: png, jpeg, gif or false. Taken from CI.
	private function _isImage($file)
	{
		$pngMimes  = array('image/x-png', 'image/png');
		$jpegMimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');
		$gifMimes = array('image/gif');
		if(in_array($file, $pngMimes))
		{
			$file = 'png';
		}
		if(in_array($file, $jpegMimes))
		{
			$file = 'jpeg';
		}
		if(in_array($file, $gifMimes))
		{
			$file = 'gif';
		}
		$imgMimes = array(
            'gif',
            'jpeg',
            'png',
		);
		return (in_array($file, $imgMimes)) ? $file : FALSE;
	}

	private function _isCustom($formFile)
	{
		$extension = strtolower(end(explode('.', $_FILES[$formFile]['name'])));
		return (in_array($extension, $this->acceptExt)) ? $extension : FALSE;
	}
	//Check if the extension is image one.
	private function _isExtImg($ext)
	{
		$imgs = array('png', 'jpeg', 'gif');
		if(in_array($ext, $imgs))
		{
			return true;
		}
		return false;
	}


	//CI method to clean the file name.
	private function _cleanFileName($filename)
	{
		include_once UTILS_PATH.'convert.php';
		$bad = array(
                        "<!--",
                        "-->",
                        "'",
                        "<",
                        ">",
                        '"',
                        '&',
                        '$',
                        '=',
                        ';',
                        '?',
                        '/',
                        "%20",
                        "%22",
                        "%3c",        // <
                        "%253c",     // <
                        "%3e",         // >
                        "%0e",         // >
                        "%28",         // (
                        "%29",         // )
                        "%2528",     // (
                        "%26",         // &
                        "%24",         // $
                        "%3f",         // ?
                        "%3b",         // ;
                        "%3d"        // =
		);
		
		$filename = str_replace($bad, '', $filename);
		$filename = Convert::convertUtf8ToSMS($filename);
		$filename = str_replace(' ','_',$filename);
		return stripslashes($filename);
	}

	function setFilename($path, $filename)
	{
		if ($this->encryptName == TRUE)
		{
			mt_srand();
			$filename = md5(uniqid(mt_rand())).$this->fileExt;
		}

		if ( ! file_exists($path.$filename))
		{
			return $filename;
		}

		$fileInfo = pathinfo($filename);
		$filename = $fileInfo['filename'];
		$new_filename = '';
		for ($i = 1; $i < 100; $i++)
		{
			if ( ! file_exists($path.$filename.$i.'.'.$this->fileExt))
			{
				$new_filename = $filename.$i.'.'.$this->fileExt;
				break;
			}
		}

		if ($new_filename == '')
		{
			//File da ton tai
			return FALSE;
		}
		else
		{
			return $new_filename;
		}
	}
	/**
	 * Validate Upload Path
	 *
	 * Verifies that it is a valid upload path with proper permissions.
	 *
	 *
	 * @access    public
	 * @return    bool
	 */
	function validateUploadPath()
	{
		if ($this->uploadPath == '')
		{
			$this->setError('Không tồn tại đường dẫn thư mục upload');
			return FALSE;
		}

		if (function_exists('realpath') AND @realpath($this->uploadPath) !== FALSE)
		{
			$this->uploadPath = str_replace("\\", "/", realpath($this->uploadPath));
		}
		if ( ! @is_dir($this->uploadPath))
		{
			$this->setError('Không tồn tại đường dẫn thư mục upload');
			return FALSE;
		}

		//        if ( ! is_really_writable($this->uploadPath))
		//        {
		//            $this->setError('Thư mục upload không có quyền ghi');
		//            return FALSE;
		//        }

		$this->uploadPath = preg_replace("/(.+?)\/*$/", "\\1/",  $this->uploadPath);
		return TRUE;
	}

}
