<?php
/**
 * System
 *
 * @author 			NamDD <namdd@xahoinet.net>
 * @since 			JOC
 * @version 		1.0
 * @package 		system
 * @subpackage 		database
 *
 */

defined('IN_JOC') or die('Restricted Access');
class System
{
	public static $false=false;
	static public function clearSqlCache($dir,$cache='cache')
	{
		//return Folder::clearFilesInPath(ROOT_PATH.DS.'cache'.DS.$dir,false,$cache,false);
		return Folder::empty_dir(ROOT_PATH.DS.'cache'.DS.$dir);
	}
	/**
	 *	Require Class & Combine
	 */
	static function requireClass($arrayObjects,$path,$prefix)
	{
		if (!$arrayObjects) return;
		if (!OBJECT_COMBINE)
		{
			foreach ($arrayObjects as $object)
			require_once $path.$object;
		}
		else
		{
			$file_name=$path.$prefix.'.object.php';
			if (file_exists($file_name))
			require_once $file_name;
			else
			{
				$codeObject='';
				foreach ($arrayObjects as $object)
				$codeObject.= file_get_contents($path.$object);
				//Remove comment
				$codeObject = preg_replace('/(\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/)|(\/\/.*)|(#.*)/', '', $codeObject);
				//Remove space, tab, new line
				$codeObject = preg_replace('/([\s]+)|([\t]+)|([\n]+)/', ' ', $codeObject);
				//Remove blank line
				$codeObject = preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $codeObject);
				$codeObject=str_replace(array("<?php","?>")," ",$codeObject);
				if ($codeObject!='')
				Folder::writeFile($file_name,"<?php ".$codeObject);
				if (file_exists($file_name))
				require_once $file_name;
			}
		}
	}
}
?>