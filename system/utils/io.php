<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class SystemIO
{
	public static $instance = array();

	static function get($name, $type = "def", $default="")
	{
		$value = $default;
		if(isset($_GET[$name]))
		{
			$value = $_GET[$name];
			if($type=="string")
			settype($value, "string");
			else
			{
				if($type=="int")
				settype($value, "int");
				else
				{
					if($type=="flt")
					settype($value, "float");
					else
					{
						if($type=="bool")
						settype($value, "boolean");
					}
				}
			}
		}

		return $value;
	}

	static function post($name, $type = "def", $default="")
	{
		$value = $default;
		if(isset($_POST[$name]))
		{
			$value = $_POST[$name];
			switch ($type)
			{
				case "str":
					settype($value, "string");
					break;
				case "int":
					settype($value, "int");
					break;
				case "flt":
					settype($value, "float");
					break;
				case "arr":
					settype($value, "array");
					break;
				default:
					break;
			}
		}

		return $value;
	}
	/***
	 * function name: getOption
	 * description: ham tra ve du lieu ve option
	 * @param $options - mang gia tri cac option
	 * @param $selected - gia tri duoc chon hien tai
	 * @return string
	 ***/
	static function getOption($options, $selected) {
		$input='';
		if (is_array($options) and sizeof($options) > 0)
		foreach ( $options as $key => $text ) {
			$input .= '<option value="' . $key . '"';
			if ($key == $selected) {
				$input .= ' selected';
			}
			$input .= '>' . $text . '</option>';
		}
		return $input;
	}

	/***
	 * function name: getMutileOption
	 * description: ham tra ve du lieu ve option
	 * @param $options - mang gia tri cac option
	 * @param $arrSelected - gia tri duoc chon hien tai
	 * @return string
	 *
	 ***/
	static function getMutileOption($options, $arrSelected) {
		if(!is_array($arrSelected))
		ObjInput::getOption($options, $arrSelected);
		elseif(sizeof($arrSelected) === 0)
		ObjInput::getOption($options, 0);
		elseif (is_array($options) and sizeof($options))
		foreach ( $options as $key => $text ) {
			$input .= '<option value="' . $key . '"';
			if (in_array($key, $arrSelected)) {
				$input .= ' selected';
			}
			$input .= '>' . $text . '</option>';
		}
		return $input;
	}
	static function arrayToOption($array, $key, $name)
	{
		$arrayReturn=array();
		if ($array)
		foreach ($array as $ar)
		{
			$arrayReturn[$ar[$key]]=$ar[$name];
		}
		return $arrayReturn;
	}
	static function getClientIp(){
		if (getenv('HTTP_CLIENT_IP'))
		{
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif (getenv('HTTP_X_FORWARDED_FOR'))
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_X_FORWARDED'))
		{
			$ip = getenv('HTTP_X_FORWARDED');
		}
		elseif (getenv('HTTP_FORWARDED_FOR'))
		{
			$ip = getenv('HTTP_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_FORWARDED'))
		{
			$ip = getenv('HTTP_FORWARDED');
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	/***
	 *  Function name: getOption
	 *  description: ham tra ve du lieu ve option
	 * @param $options - mang gia tri cac option
	 * @param $selected - gia tri duoc chon hien tai
	 * @return string
	 ***/
	static function getRadioList($options,$input_name,$selected,$br=' ') {
		if ($options)
		foreach ( $options as $key => $text ) {
			$input .= '<input type="radio" name="'.$input_name.'" id="'.$input_name.'" value="'.$key.'"';
			if ($key==$selected) {
				$input .= ' checked="checked" ';
			}
			$input .= '> ' .$text.$br;
		}
		return $input;
	}
	/**
	 *	@deprecated ham cat xau hoan chinh (cat du tu)
	 *	@param $str xau can can cat
	 *	@param $start - ky tu bat dau cat
	 *	@param $num - so ky tu duoc cat
	 */
	public function getSubStr($str, $start, $num)
	{
		$str = String::removeHTMLtag($str);
		$pos = strpos(substr($str,$start + $num + 1,10),' ');
		$st = substr($str,$start,$num + $pos + 2);
		return $st;
	}
	/**
	 *	description ham remove ca the html
	 *	@param $tagSource - doan html can remove the html
	 *	@return text khong co the html
	 */
	static function removeHTMLtag ($tagSource)
	{
		$searchTags = array ('@<script[^>]*?>.*?</script>@si', // Strip out javascript
        '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
        '@([\r\n])[\s]+@', // Strip out white space
        '@&(quot|#34);@i', // Replace HTML entities
        '@&(amp|#38);@i',
        '@&(lt|#60);@i',
        '@&(gt|#62);@i',
        '@&(nbsp|#160);@i',
        '@&(iexcl|#161);@i',
        '@&(cent|#162);@i',
        '@&(pound|#163);@i',
        '@&(copy|#169);@i',
        '@&#(\d+);@e'); // evaluate as php

		$replaceTags = array ('',
        '',
        '\1',
        '"',
        '&',
        '<',
        '>',
        ' ',
		chr(161),
		chr(162),
		chr(163),
		chr(169),
        'chr(\1)');
		return  preg_replace ($searchTags, $replaceTags, $tagSource);
	}
	/**
	 *	@description Ham boi vang tu khoa
	 *	@param $source - doan titel co chua tu khoa
	 *	@param $keywords chuoi tu khoa can boi bang
	 */
	static function hightlightKeyword($source, $keywords)
	{
		if($keywords)
		{
			$keywords = str_replace(array('/', '//','"', '\''),'', $keywords);
			$arrKeyword = explode(' ', $keywords);
			$arrKeywordHL = array();
			for($i = 0 ; $i < count($arrKeyword); $i++)
			{
				$keyword = $arrKeyword[$i];
				if($keyword)
				{
					$arrKeywordHL[] = $keyword;
				}
			}
			foreach ($arrKeywordHL as $key=>$value)
			{
				//if(!in_array($value, array('/','"','\'')))
				$source = preg_replace(array("/^($value)([^\w])/i","/([^\w])($value)([^\w])/i"), array("<strong><font style='background-color:yellow' color=black>\\1</font></strong>\\2","\\1<strong><font style='background-color:yellow' color=black>\\2</font></strong>\\3"), $source);
			}
		}
		return $source;
	}
	/**
	 * Ham cat xau tra ve du tu
	 *
	 * @param string $str - xau can cat
	 * @param int $len - so ky tu can cat
	 * @param string $pad - chuoi can noi them vao cuoi, defaul '...'
	 * @param boolean $strip - yeu cau trip_tags truoc khi cat hay khong
	 * @return string
	 */
	static function strLeft($str, $len, $pad='...', $strip=FALSE)
	{
		if(strlen($str) <= $len)
		return $str;
		if($strip)
		$str = strip_tags($str);
		$txt = substr($str,0,$len);
		$pos = strrpos($txt,' ');
		return substr_replace($txt, $pad, $pos+1);
	}
	static function strip($str)
	{
		return stripslashes(stripslashes(stripslashes($str)));
	}

	static function wordLimiter($str, $limit = 100, $end_char = '...')
	{
		if (trim($str) == '')
		{
			return $str;
		}
		preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $str, $matches);

		if (strlen($str) == strlen($matches[0]))
		{
			$end_char = '';
		}

		return rtrim($matches[0]).$end_char;
	}
	/**
	 * tao the selectbox
	 *
	 * @param array du lieu $array
	 * @param gia tri checked $checked
	 * @param truong can kiem tra checked $field_check
	 * @param truong hien thi $field_show
	 * @param mo rong cua the option $ext_option
	 */
	static function selectBox($array, $checked,$field_value ="" , $field_check = "id", $field_show = "name", $ext_option = "")
	{
		$select = '';

		foreach ($array as $arr)
		{
			if(in_array($arr[$field_check].($ext_option != "" ? "_".$arr[$ext_option] : ""), $checked))
			$select .= '<option selected="selected" value="'.$arr[$field_value].'">'.$arr[$field_show].'</option>';
			else
			$select .= '<option value="'.$arr[$field_value].'">'.$arr[$field_show].'</option>';
		}
		return $select;
	}

	public static function createObject($class_name)
	{
		if(!isset(self::$instance[$class_name]))
		{
			$obj = new $class_name;
			self::$instance[$class_name] = $obj;
		}

		return self::$instance[$class_name];
	}
	/**
	 * cắt chuoi
	 */
	static function CutStr($str,$number = 150,$dot=true)
	{
		$leng = strlen($str);
		if($leng > $number)
		{
			$temp  = substr($str, 0, $number);
			$str   = substr($temp, 0, strrpos($temp, " ")).($dot ? "..." : "");
		}
		 
		return $str;
	}
}

?>