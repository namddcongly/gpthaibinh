<?php
/*****
 * class Captcha
 * Tạo các Captcha Image dùng cho hệ thống
 * @Author Namdd
 *****/
class Captcha {
	public $key; // ultra private static text
	public $long; // size of text
	public $lx; // width of picture
	public $ly; // height of picture
	public $nb_noise; // nb of background noisy characters
	public $filename; // file of captcha picture stored on disk
	public $imagetype = "png"; // can also be "png";
	public $imagepath= 'data/captchar/images/'; // Thư mục lưu ảnh captchar
	public $lang = "fr"; // also "en"
	public $public_key; // public key
	public $font_file;
	public $sessionName;
	public $default_size=array('3'=>array('lx'=>48,'ly'=>20),
							   '4'=>array('lx'=>60,'ly'=>20),
							   '5'=>array('lx'=>74,'ly'=>20),
							   '6'=>array('lx'=>90,'ly'=>20),
	);
	// Hàm khởi tạo Captcha
	function Captcha($long = 3,$lx=false,$ly=false, $nb_noise = 20) {
		$this->key = md5 ( "joc vietnam" );
		$this->long = $long;
		if (!$lx)
		$this->lx = $this->default_size[$long]['lx'];
		else
		$this->lx =$lx;
		if (!$ly)
		$this->ly=$this->default_size[$long]['ly'];
		else
		$this->ly = $ly;
		$this->nb_noise = $nb_noise;
		$this->font_file = ROOT_PATH.'captchar/font'.DS.'arial.ttf';
	}
	// Trả về đường dẫn file captcha được lưu
	function getFilename($public = "") {
		if ($public == "")
		$public=$this->public_key;
		$fileName = md5 ( $public . $this->sessionName);
		return $this->imagepath . $fileName . "." . $this->imagetype;
	}
	// check if the public text is link to the private text
	function checkCaptcha($public,$sessionName='VerifyImage') {
		// when check, destroy picture on disk
		$this->sessionName=$sessionName;
		$fileName=$this->getFilename ($public );
		if (file_exists ($fileName))
		{
			unlink ($fileName);
			if (isset($sessionName) && isset($_SESSION[$sessionName]))
			unset($_SESSION[$sessionName]);
			return true;
		}
		else
		return false;
	}
	// check if the public text is link to the private text
	function validateCaptcha($public,$sessionKey) {
		// when check, destroy picture on disk
		if (!$public or !$sessionKey)
		return false;
		$key=md5($sessionKey);

		$public=md5(strtolower($public));
		if ($_SESSION[$key]==$public)
		{
			unset($_SESSION[$key]);
			return true;
		}
		else
		{
			unset($_SESSION[$key]);
			return false;
		}
	}
	// display a captcha picture with private text and return the public text
	function makeCaptcha($noise = true,$sessionKey=false)
	{
		$this->public_key = substr ( md5 ( uniqid ( rand (), true ) ), 0, $this->long ); // generate public key with entropy
		$private_key = $this->public_key;
		$image = imagecreatetruecolor ( $this->lx, $this->ly );
		$back = ImageColorAllocate ( $image, intval ( rand ( 224, 255 ) ), intval ( rand ( 224, 255 ) ), intval ( rand ( 224, 255 ) ) );
		ImageFilledRectangle ( $image, 0, 0, $this->lx, $this->ly, $back );
		if ($noise) { // rand characters in background with random position, angle, color
			for($i = 0; $i < $this->nb_noise; $i ++) {
				$size = intval ( rand ( 6, 14 ) );
				$angle = intval ( rand ( 0, 360 ) );
				$x = intval ( rand ( 10, $this->lx - 10 ) );
				$y = intval ( rand ( 0, $this->ly - 5 ) );
				$color = imagecolorallocate ( $image, intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ) );
				$text = chr ( intval ( rand ( 45, 250 ) ) );
				ImageTTFText ( $image, $size, $angle, $x, $y, $color, $this->font_file, $text );
			}
		} else { // random grid color
			for($i = 0; $i < $this->lx; $i += 10) {
				$color = imagecolorallocate ( $image, intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ) );
				imageline ( $image, $i, 0, $i, $this->ly, $color );
			}
			$color = imagecolorallocate ( $image, intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ) );
			imageline ( $image, $this->lx-1, 0, $this->lx-1, $this->ly, $color );
			for($i = 0; $i < $this->ly; $i += 10) {
				$color = imagecolorallocate ( $image, intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ) );
				imageline ( $image, 0, $i, $this->lx, $i, $color );
			}
			$color = imagecolorallocate ( $image, intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ), intval ( rand ( 160, 224 ) ) );
			imageline ( $image, 0, $this->ly-1, $this->lx, $this->ly-1, $color );

		}
		// private text to read
		for($i = 0, $x = 2; $i < $this->long; $i ++) {
			$r = intval ( rand ( 0, 128 ) );
			$g = intval ( rand ( 0, 128 ) );
			$b = intval ( rand ( 0, 128 ) );
			$color = ImageColorAllocate ( $image, $r, $g, $b );
			$shadow = ImageColorAllocate ( $image, $r + 128, $g + 128, $b + 128 );
			$size = intval ( rand ( 11, 14 ) );
			$angle = intval ( rand ( -5,5 ) );
			$text = strtoupper ( substr ( $private_key, $i, 1 ) );
			ImageTTFText ( $image, $size, $angle, $x + 2, $this->ly-4, $shadow, $this->font_file, $text );
			ImageTTFText ( $image, $size, $angle, $x, $this->ly-4, $color, $this->font_file, $text );
			$x += $size + 2;
		}
		if (!$sessionKey)
		{
			if ($this->imagetype == "jpg")
			imagejpeg ( $image, $this->getFilename (), 100 );
			else
			imagepng ( $image, $this->getFilename () );

		}
		else
		{
			$_SESSION[md5($sessionKey)]=md5(strtolower($this->public_key));
			$this->sendHeaders();
			//Output captcha image
			if ($this->imagetype == "jpg")
			imagejpeg ($image);
			else
			imagepng ($image);
		}
		ImageDestroy ( $image );

	}
	// Tạo ảnh và trả về src hoặc img html
	function getCaptcha($noise = false,$src=true,$sessionName='VerifyImage') {
		$this->sessionName=$sessionName;
		$this->makeCaptcha ( $noise );
		if ($src)
		$res = "<img src='" . $this->getFilename () . "' border='0'>\n";
		else
		$res =$this->getFilename ();
		return $res;
	}
	/**
	 * To avoid image caching we need to send additional no-cache headers,
	 * as well as content type.
	 *
	 * @return void
	 */
	function sendHeaders()
	{
		//  Back in the good old days :)
		header("expires: mon, 26 jul 1997 05:00:00 gmt");
		header("cache-control: no-cache, must-revalidate");
		header("pragma: no-cache");
		//  This is GIF image!
		header('Content-type: image/gif');
	}
}
?>