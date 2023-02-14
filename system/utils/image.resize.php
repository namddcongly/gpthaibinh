<?php
/*****
 * class ImageResize
 * @author: NamDD <namdd@xahoinet.vn>
 * @copyright: JOC
 */
defined ( 'IN_JOC' ) or die ( 'Restricted Access' );
class ImageResize {

	/**
	 * Ham resize anh ve
	 *
	 * @param int $thumbWidth - chieu rong toi da cua anh can resize
	 * @param int $thumbHeight  - chieu cao toi da cua anh can resize
	 * @param string $imageUrl - duong dan cua anh goc ex: 'data/images/test.gif' (Duong dan dung), neu duong dan 'http://chodientu.vn/data/images/test.gif' (duong dan sai)
	 * @param string $savePath - duong dan thu muc can chua anh resize default 'data/images/temp'
	 * @param booleand $isNotThumb - co quy dinh resize co giu nguyen ty le anh (width/height),
	 * @param Neu true la se thumb lai anh dung bang kich thuc width va height truyen vao
	 * @param Neu false giu lai dung ty le anh cu
	 * @return string (duong dan anh duoc resize)
	 */
	public function imgResize($thumbWidth, $thumbHeight, $imagePath, $savePath = 'congly\data\temp\\', $isNotThumb = true)
	{

		if(is_dir($savePath) === false){
			return -1;// khong ton tai thu muc luu anh thumb
		}
		$basName = '';
		if($isNotThumb)
		$basName = 'thumb';
		$basName .= $thumbHeight.'.'.$thumbHeight;
		$basName .= '_'.basename($imagePath, ".gif").'.gif';
		$pathFileName = $savePath.$basName;
		return $this -> resize($thumbWidth, $thumbHeight, $imagePath, $pathFileName, $isNotThumb);
	}
	/**
	 * Tao thumb cho anh
	 *
	 * @param int $thumbWidth - chieu rong toi da cua anh can resize
	 * @param int $thumbHeight  - chieu cao toi da cua anh can resize
	 * @param string $imageFrom - duong dan cua anh goc ex: 'data/images/test.gif' (Duong dan dung), neu duong dan 'http://chodientu.vn/data/images/test.gif' (duong dan sai)
	 * @param string $imageTo - duong dan cua anh duoc resize ex: 'data/images/test_resize.gif' (Duong dan dung), neu duong dan 'http://chodientu.vn/data/images/test_resize.gif' (duong dan sai)
	 * @param booleand $isNotThumb - co quy dinh resize co giu nguyen ty le anh (width/height),
	 * @param Neu true la se thumb lai anh dung bang kich thuc width va height truyen vao
	 * @param Neu false giu lai dung ty le anh cu
	 * @return string (dung dan anh duoc thumb)
	 */
	public function createThumb($thumbWidth, $thumbHeight, $imageFrom, $imageTo, $isNotThumb = false)
	{
		return $this -> resize($thumbWidth, $thumbHeight, $imageFrom, $imageTo, $isNotThumb);
	}
	/**
	 * Tao anh truc tiep, khong luu vao he thong (thung dung cho captcha)
	 *
	 * @param int $thumbWidth - chieu rong toi da cua anh can resize
	 * @param int $thumbHeight  - chieu cao toi da cua anh can resize
	 * @param string $imageFrom - duong dan cua anh goc ex: 'data/images/test.gif' (Duong dan dung), neu duong dan 'http://chodientu.vn/data/images/test.gif' (duong dan sai)
	 * @param booleand $isNotThumb - co quy dinh resize co giu nguyen ty le anh (width/height),
	 * @param Neu true la se thumb lai anh dung bang kich thuc width va height truyen vao
	 * @param Neu false giu lai dung ty le anh cu
	 * @return anh duoc tao ra
	 */
	public function genareImage($thumbWidth, $thumbHeight, $imageFrom, $isNotThumb = true)
	{
		return $this -> resize($thumbWidth, $thumbHeight, $imageFrom, '', $isNotThumb, true);
	}
	protected function resize($thumbWidth, $thumbHeight, $imagePath, $savePathFileName, $isThumb = false, $isNotSave = false) {
		if(!file_exists($imagePath)) {
			return -2;// khong ton tai duong dan anh goc
		}
		if (filesize($imagePath) > 1048576){
			return -3;// dung luong lon hon quy dinh
		}

		$info = @getimagesize($imagePath);

		if(empty($info)) {
			return -4;
		}

		$width = $info[0];
		$height = $info[1];
		$mime = $info['mime'];

		$ratio = $height / $width;
		//$thumb_ratio = $thumbHeight / $thumbWidth;
		$new_width = $width;
		$new_height = $height;

		if ($width <= $thumbWidth && $height <= $thumbHeight && !$isThumb){
			copy($imagePath, $savePathFileName);
			return $savePathFileName;
		}
		else {
			if ($width <= $thumbWidth && $height <= $thumbHeight){
				$new_width = $width;
				$new_height = $height;
			}
			else {
				if ($width > $thumbWidth){
					$new_width = $thumbWidth;
					$new_height = $new_width * $ratio;
				}
				if ($new_height > $thumbHeight){
					$new_height = $thumbHeight;
					$new_width = $new_height / $ratio;
				}
			}
			$type = substr(strrchr($mime, '/'), 1);
			$type = $type == "jpeg" ? "jpg" : $type;

			/*
			 if ($isThumb)
			 $new_name = "thumb.".basename($imageUrl, ".gif").'.'.$thumbWidth.'.'.$thumbHeight.'.gif';
			 else
			 $new_name = basename($imageUrl, ".gif").'.gif';

			 $savePathFileName = $savePathFileName.$new_name;*/

			if (file_exists($savePathFileName)) {
				//var_dump($savePathFileName);
				return $savePathFileName;
			}

			$dst = imagecreatetruecolor($new_width, $new_height);

			//$dimension = max($new_height, $new_width);

			switch ($type) {
				case 'png':
					$src = imagecreatefrompng($imagePath);
					$fnc = "imagepng";
					$header = "image/png";
					break;

				case 'bmp':
					$src = ImageResize::imagecreatefrombmp($imagePath);
					$fnc = "imagejpeg";
					$header = "image/jpeg";
					break;

				case 'gif':
					$src = imagecreatefromgif($imagePath);
					$fnc = "imagegif";
					$header = "image/gif";
					break;

				case 'jpg':
				case 'jpeg':
				default:
					$src = imagecreatefromjpeg($imagePath);
					$fnc = "imagejpeg";
					$header = "image/jpeg";
			}

			imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

			if (!$isThumb){
				$bgr = imagecreatetruecolor($thumbWidth, $thumbHeight);
				$bg_color = imagecolorallocate($bgr, 0xFF, 0xFF, 0xFF);
				imagefill($bgr, 0, 0, $bg_color);

				imagecopymerge($bgr, $dst, $thumbWidth / 2 - $new_width / 2, $thumbHeight / 2 - $new_height / 2, 0, 0, $new_width, $new_height, 100);


				if ($isNotSave){
					header("Content-Type: ".$header);
					$process = $fnc($bgr);
				}
				else {
					$process = $fnc($bgr, $savePathFileName);
				}
				imagedestroy($bgr);
			}
			else {
				if ($isNotSave){
					header("Content-Type: ".$header);
					$process = $fnc($dst);
				}
				else {
					$process = $fnc($dst, $savePathFileName);
				}
			}

			imagedestroy($src);

			imagedestroy($dst);
		}
		return $savePathFileName;
	}
	protected function imagecreatefrombmp($filename) {
		$tmp_name = tempnam("./temp_files", "GD");
		if (ImageResize::ConvertBMP2GD($filename, $tmp_name)) {
			$img = imagecreatefromgd($tmp_name);
			unlink($tmp_name);
			return $img;
		}
		return false;
	}
	protected function ConvertBMP2GD($src, $dest = false) {
		if (!($src_f = fopen($src, "rb"))) {
			return false;
		}
		if (!($dest_f = fopen($dest, "wb"))) {
			return false;
		}
		$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f, 14));
		$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread($src_f, 40));

		extract($info);
		extract($header);

		if ($type != 0x4D42) {
			// signature "BM"
			return false;
		}

		$palette_size = $offset - 54;
		$ncolor = $palette_size / 4;
		$gd_header = "";
		// true-color vs. palette
		$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
		$gd_header .= pack("n2", $width, $height);
		$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
		if ($palette_size) {
			$gd_header .= pack("n", $ncolor);
		}
		// no transparency
		$gd_header .= "\xFF\xFF\xFF\xFF";

		fwrite($dest_f, $gd_header);

		if ($palette_size) {
			$palette = fread($src_f, $palette_size);
			$gd_palette = "";
			$j = 0;
			while ($j < $palette_size) {
				$b = $palette{$j++};
				$g = $palette{$j++};
				$r = $palette{$j++};
				$a = $palette{$j++};
				$gd_palette .= "$r$g$b$a";
			}
			$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
			fwrite($dest_f, $gd_palette);
		}

		$scan_line_size = (($bits * $width) + 7) >> 3;
		$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;

		for ($i = 0, $l = $height - 1; $i < $height; $i++, $l--) {
			// BMP stores scan lines starting from bottom
			fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
			$scan_line = fread($src_f, $scan_line_size);
			if ($bits == 24) {
				$gd_scan_line = "";
				$j = 0;
				while ($j < $scan_line_size) {
					$b = $scan_line{$j++};
					$g = $scan_line{$j++};
					$r = $scan_line{$j++};
					$gd_scan_line .= "\x00$r$g$b";
				}
			} elseif ($bits == 8) {
				$gd_scan_line = $scan_line;
			} elseif ($bits == 4) {
				$gd_scan_line = "";
				$j = 0;
				while ($j < $scan_line_size) {
					$byte = ord($scan_line{$j++});
					$p1 = chr($byte >> 4);
					$p2 = chr($byte & 0x0F);
					$gd_scan_line .= "$p1$p2";
				}
				$gd_scan_line = substr($gd_scan_line, 0, $width);
			} elseif ($bits == 1) {
				$gd_scan_line = "";
				$j = 0;
				while ($j < $scan_line_size) {
					$byte = ord($scan_line{$j++});
					$p1 = chr((int)(($byte & 0x80) != 0));
					$p2 = chr((int)(($byte & 0x40) != 0));
					$p3 = chr((int)(($byte & 0x20) != 0));
					$p4 = chr((int)(($byte & 0x10) != 0));
					$p5 = chr((int)(($byte & 0x08) != 0));
					$p6 = chr((int)(($byte & 0x04) != 0));
					$p7 = chr((int)(($byte & 0x02) != 0));
					$p8 = chr((int)(($byte & 0x01) != 0));
					$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
				}
				$gd_scan_line = substr($gd_scan_line, 0, $width);
			}

			fwrite($dest_f, $gd_scan_line);
		}
		fclose($src_f);
		fclose($dest_f);
		return true;
	}
	/**
	 * Clean file name
	 *
	 * @param mixed $filename
	 * @return string
	 */
	static function cleanFileName($filename){
		$bad = array(
                        "<!--",
                        "-->",
                        "'",
                        " ",
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
                        "%3d",        // =
                        "%"
                        );

                        $filename = str_replace($bad, '', $filename);
                        return stripslashes($filename);
	}
	static function unlinkThumb($img_name,$date,$folder)
	{
		if($img_name=='' && $date=='0' && $folder=='') return false;
		return @unlink($folder.'/'.date('Y/n/j',$date).'/'.$img_name);
	}

}
?>
