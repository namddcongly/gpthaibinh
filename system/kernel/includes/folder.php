<?php
/**
 * Folder
 *
 * Lop xu ly filesystem
 *
 * @author 			NamDD
 * @package 		system
 */

defined ( 'IN_JOC' ) or die ( 'Restricted Access' );
class Folder {
	/**
	 * parrent
	 * duong dan link parent
	 *
	 * @var string
	 */
	private $parrent;

	/**
	 * Name
	 * ten thu muc
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Files
	 * mang danh sach cac files cua thu muc
	 *
	 * @var Array
	 */
	private $files;

	/**
	 * Cause
	 * mang cac object/nam cac folder con
	 *
	 * @var Array
	 */
	private $cause;

	/**
	 * Construct
	 *
	 * @param string $path // Duong dan thu muc
	 * @param int $deep // Do sau cua thu muc muon di vao
	 */
	public function __construct($path, $deep = 10) {
		if (! self::clean ( $path )) {
			throw new SystemException ( 'This "' . $path . '" is not exist!' );
		}
		$parrent = dirname ( $path );
		$name = str_replace ( $parrent . DS, '', $path );
		$this->name = $name;
		$this->parrent = $parrent;

		//Open folder va set danh sach folder va file nam trong folder
		$cause = array ();
		$files = array ();
		$handle = opendir ( $path );
		while ( ($file = readdir ( $handle )) !== false ) {
			if (($file != '.') && ($file != '..')) {
				$dir = $path . DS . $file;
				if (is_dir ( $dir )) {
					if ($deep == 0) {
						$cause [] = $file;
					} else {
						$cause [] = new Folder ( $dir, $deep - 1 );
					}
				} else {
					$files [] = $file;
				}
			}
		}
		sort ( $cause );
		sort ( $files );
		$this->cause = $cause;
		$this->files = $files;
		if (IN_DEBUG) {
			Profiler::getInstance()->mark('Construct new Folder object, path = "' . $path . '"', 'system.filesystem.' . get_class($this));
		}
	}

	public function __toString() {
		return $this->name;
	}
	function copyDirectory( $source, $destination ) {
		if ( is_dir( $source ) ) {
			$old = umask(0);
			if(!is_dir($destination))
			mkdir($destination, 0777);
			umask($old);
			$directory = dir( $source );
			while ( FALSE !== ( $readDirectory = $directory->read() ) ) {
				if ( $readDirectory == '.' || $readDirectory == '..' || $readDirectory == '.svn') {
					continue;
				}
				$pathDir = $source . '/' . $readDirectory;
				if ( is_dir( $pathDir ) ) {
					self::copyDirectory( $pathDir, $destination . '/' . $readDirectory );
					continue;
				}
				copy( $pathDir, $destination . '/' . $readDirectory );
				chmod($destination . '/' . $readDirectory, 0777);
			}
			$directory->close();
			return true;
		}else{
			return false;
		}

	}
	/**
	 * Explode
	 *
	 * @param boolean $fullPath. true = su dung duong dan truc tiep
	 * @param boolean $fullInfo. true = display danh sach cac thu muc con
	 * @return Array
	 */
	public function explode($fullPath = false, $fullInfo = true) {
		$files = $this->files;
		$folders = $this->cause;

		$tree = array ();
		$tree ['name'] = ($fullPath ? $this->parrent . DS : '') . $this->name;
		$tree ['parrent'] = $this->parrent;
		if ($fullPath) {
			for($i = 0, $size = sizeof ( $files ); $i < $size; ++ $i) {
				$tree ['files'] [] = $this->parrent . DS . $this->name . DS . $files [$i];
			}
		} else {
			$tree ['files'] = $files;
		}
		$tree ['folders'] = array ();
		for($i = 0, $sizeFolders = sizeof ( $folders ); $i < $sizeFolders; ++ $i) {
			if (($folders [$i] instanceof Folder) && $fullInfo) {
				$tree ['folders'] [] = $folders [$i]->explode ( $fullPath, $fullInfo );
			} else {
				$tree ['folders'] [] = ($fullPath ? $this->parrent . DS : '') . $folders [$i];
			}
		}
		return $tree;
	}

	/**
	 * Clear File
	 * Xoa file khoi folder
	 *
	 * @param string $prefix. Filter theo prefix
	 * @param string $ext. Filter theo extension file, not include '.'
	 * @param boolean $clearAll. Neu $clearAll = true, cac file cua thu muc con cung bi xoa
	 */
	public function clearFiles($prefix = '', $ext = '.', $clearAll = false) {
		if ((strpos ( $this->parrent, 'cache' ) === false) && (strpos ( $this->name, 'cache' ) === false)) {
			throw new SystemException ( 'Not allow auto clear files in this path "' . $this->parrent . DS . $this->name . '"' );
		}

		if ($clearAll === true) { //Clear file nam trong thu muc con
			for($i = 0, $size = sizeof ( $this->cause ); $i < $size; ++ $i) {
				if ($this->cause [$i] instanceof Folder)
				$this->cause [$i]->clearFiles ( $prefix, $ext, $clearAll );
				else
				self::clearFilesInPath ( $this->parrent . DS . $this->name . DS . $this->cause [$i], $prefix, $ext, $clearAll );
			}
		}

		//Clear file nam trong thu muc hien tai
		$files = $this->files;
		for($i = 0, $size = sizeof ( $files ); $i < $size; ++ $i) {
			$isFilterByPrefix = true;
			$isFilterByExt = true;

			if ($prefix != '') { //Kiem tra File co chua prefix duoc chi dinh
				$isFilterByPrefix = (strpos ( $files [$i], $prefix ) === 0);
			}

			if ($ext != '.' && $ext != '') { //Kiem tra file co nam trong dinh dang cho phep xoa
				$info = pathinfo ( $files [$i] );
				$isFilterByExt = ($ext == $info ['extension']);
			}

			if ($isFilterByPrefix && $isFilterByExt) {
				@unlink ( $this->parrent . DS . $this->name . DS . $files [$i] );
				unset ( $this->files [$i] );
			}
		}
		if(IN_DEBUG)
		Profiler::getInstance ()->mark ( 'Remove all file filter in "' . $this->parrent . DS . $this->name . '" by prefix = "' . $prefix . '" and extension = "' . $ext . '"', 'system.filesystem' . get_class ( $this ) );
	}

	/**
	 * Files
	 * lay danh sach file
	 *
	 * @param string $path duong dan
	 * @param string $filter filter mime type
	 * @param int $deep
	 * @param boolean $fullPath
	 * @return Array
	 */

	public static function files($path, $filter = '.', $deep = 1, $fullPath = false) {
		$arr = array ();

		//Check path
		$path = self::clean ( $path );

		// Is the path a folder?
		if (! is_dir ( $path )) {
			return false;
		}

		// read the source directory
		$handle = opendir ( $path );
		while ( ($file = readdir ( $handle )) !== false ) {
			if (($file != '.') && ($file != '..')) {
				$dir = $path . DS . $file;
				if (is_dir ( $dir )) {
					$deep --;
					if ($deep !== 0) {
						$arr2 = self::files ( $dir, $filter, $deep, $fullPath );
						$arr = array_merge ( $arr, $arr2 );
					}
				} else {
					if (strpos ( $file, $filter ) !== false) {
						if ($fullPath) {
							$arr [] = $path . DS . $file;
						} else {
							$arr [] = $file;
						}
					}
				}
			}
		}
		closedir ( $handle );

		asort ( $arr );
		return $arr;
	}

	/**
	 * Clean
	 *  strip '/', '\' trong path
	 *
	 * @static
	 * @param	string	$path	Duong dan
	 * @param	string	$ds		Directory separator
	 * @return	string	The cleaned path
	 */
	public static function clean($path, $ds = DS) {
		$path = trim ( $path );

		if (empty ( $path )) {
			$path = ''; //wait for define
		} else {
			// Remove double slashes and backslahses and convert all slashes and backslashes to DS
			$path = preg_replace ( '#[/\\\\]+#', $ds, $path );
		}

		return $path;
	}

	/**
	 * Read File To Array
	 *
	 * @param string $file
	 * @return Array
	 */
	public static function readFileToArray($file) {
		if (! self::existsFile ( $file )) {
			return false;
		}
		$file = self::clean ( $file );

		$data = array ();

		$handfile = @fopen ( $file, 'r' );
		if ($handfile) {
			while ( ! feof ( $handfile ) ) {
				$data [] = str_replace ( chr ( 10 ), '', fgets ( $handfile ) );
			}
		}
		fclose ( $handfile );
		return $data;
	}

	/**
	 * Write File
	 *
	 * @param string $file
	 * @param mixed $buff
	 * @return bytes
	 */
	function writeFile($path, $data, $mode = 'a'){
		if ( ! $fp = @fopen($path, $mode)){
			return false;
		}
		flock($fp, LOCK_EX);
		fwrite($fp, $data);
		flock($fp, LOCK_UN);
		fclose($fp);
		return true;
	}
	/**
	 * Create
	 *
	 * Tao folder, chmod
	 *
	 * @param string $path
	 * @param string $mode
	 */
	public static function create($path, $mode = '0777') {
		$path = self::clean ( $path );
		static $nested = 0;

		//Kiểm tra thư mục cha.
		$parent = dirname ( $path );
		if (! self::exists ( $parent )) {
			$nested ++;

			if (($nested > 20) || ($parent == $path)) {
				$nested --;
				return false;
			}

			//create parent
			if (self::create ( $parent, $mode ) !== true) {
				$nested --;
				return false;
			}
			$nested --;
		}

		// Check if dir already exists
		if (self::exists ( $path )) {
			return true;
		}
		$old = umask(0);
		@mkdir( $path, $mode, true );
		umask($old);
		@chmod($path,$mode);
		return true;
	}

	/**
	 * Clear files in path
	 *
	 * @param string $path
	 * @param string $prefix
	 * @param string $ext extension file, not include '.'
	 * @param boolean $clearAll
	 */
	public static function clearFilesInPath($path, $prefix = '', $ext = '.', $clearAll = false) {
		$path = self::clean ( $path );
		if (strpos ( $path, 'cache' ) === false) {
			throw new SystemException ( 'Not allow auto clear files in this path "' . $path . '"' );
		}

		if (! is_dir ( $path )) {
			throw new SystemException ( 'This "' . $path . '" is not exist!' );
		}
		$handle = opendir ( $path );
		while ( ($file = readdir ( $handle )) !== false ) {
			if ($file == '..' || $file == '.')
			continue;

			if ($clearAll && is_dir ( $path . DS . $file )) {
				self::clearFilesInPath ( $path . DS . $file, $prefix, $ext, $clearAll );
			} else if (is_file ( $path . DS . $file )) {
				$isFilterByPrefix = true;
				$isFilterByExt = true;

				if ($prefix != '') { //Kiem tra File co chua prefix duoc chi dinh
					$isFilterByPrefix = (strpos ( $file, $prefix ) === 0);
				}

				if ($ext != '.' && $ext != '') { //Kiem tra file co nam trong dinh dang cho phep xoa
					$info = pathinfo ( $file );
					$isFilterByExt = ($ext == $info ['extension']);
				}

				if ($isFilterByExt && $isFilterByPrefix) {
					@unlink ( $path . DS . $file );
				}
			}
		}

		closedir ( $handle );
		if(IN_DEBUG)
		Profiler::getInstance ()->mark ( 'Remove all file filter in "' . $path . '" by prefix = "' . $prefix . '" and extension = "' . $ext . '"', 'system.filesystem' . @get_class($this) );
	}

	/**
	 * Read File
	 *
	 * @param unknown_type $file
	 * @return unknown
	 */
	static function readFile($file) {
		if ( ! file_exists($file)){
			return false;
		}
		if (function_exists('file_get_contents')){
			return file_get_contents($file);
		}
		if ( ! $fp = @fopen($file, 'r')){
			return false;
		}
		flock($fp, LOCK_SH);
		$data = '';
		if (filesize($file) > 0){
			$data =& fread($fp, filesize($file));
		}
		flock($fp, LOCK_UN);
		fclose($fp);
		return $data;
	}

	/**
	 * Kiem tra xem co ton tai hay khong
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function exists($path) {
		return is_dir ( self::clean ( $path ) );
	}

	/**
	 * Exists File
	 *
	 * @param string $file
	 * @return boolean
	 */
	public static function existsFile($file) {
		return is_file ( self::clean ( $file ) );
	}

	/**
	 * Empty Directory
	 * Xoa toan bo cac file trong thu muc
	 *
	 * @param string $name duong dan thu muc
	 */
	public static function emptyDir($name) {
		$name = rtrim($name,DS).DS;
		$dir = opendir ( $name );
		while ( $file = readdir ($dir ) ) {
			if ($file != '..' && $file != '.'&& $file != '.svn') {
				if(!is_dir($file)){
					@unlink ( $name . $file );
				}else{
					self::emptyDir($name.$file);
				}
			}
		}
		closedir ( $dir );
		return true;
	}
	/**
	 * Ham xoa thu muc
	 * @param: $pathFolder duong dan thu muc can xoa
	 * @return: void
	 */
	public static function delFolder($pathFolder) {
		$dir = rtrim($pathFolder,DS);
		if(!is_dir($pathFolder))
		return;
		$handle = opendir($dir);
		if ($handle) {
			while (false !== ($item = readdir($handle))) {
				if ($item != "." && $item != "..") {
					if (is_dir($dir.DS.$item)) {
						self::delFolder($dir.DS.$item);
					} else {
						@unlink($dir.DS.$item);
					}
				}
			}
			closedir($handle);
			@rmdir($dir);
		}
	}
	/**
	 * Check folder is empty or not empty
	 * @param $path duong dan thu muc.
	 * @return boolean. return true if empty, return false if not empty
	 */
	function checkEmptyFolder($path) {
		$contents = scandir ( $path );
		if ($contents == false)
		return true;
		foreach ( $contents as $folder ) {
			if ($folder != '.' and $folder != '..') {
				return false;
			}
		}
		return true;
	}
	/**
	 Ghi file log
	 */
	static function writeLogFile($file,$content)
	{
		if (self::existsFile ( $file )) {
			self::create ( dirname ( $file ) );
		}
		$ft=fopen($file,'a');
		fwrite($ft,$content);
		fclose($ft);
	}
	static function getEXFile($name)
	{
		$dot=strripos($name,'.');
		return substr($name,$dot,strlen($name));
	}
	static function getPermissions($path)
	{
		$path = self::clean($path);
		$mode = @ decoct(@ fileperms($path) & 0777);

		if (strlen($mode) < 3) {
			return '---------';
		}
		$parsed_mode = '';
		for ($i = 0; $i < 3; $i ++)
		{
			// read
			$parsed_mode .= ($mode { $i } & 04) ? "r" : "-";
			// write
			$parsed_mode .= ($mode { $i } & 02) ? "w" : "-";
			// execute
			$parsed_mode .= ($mode { $i } & 01) ? "x" : "-";
		}
		return $parsed_mode;
	}
	static function setPermissions($path, $filemode = '0644', $foldermode = '0755') {
		// Initialize return value
		$ret = true;

		if (is_dir($path))
		{
			$dh = opendir($path);
			while ($file = readdir($dh))
			{
				if ($file != '.' && $file != '..') {
					$fullpath = $path.'/'.$file;
					if (is_dir($fullpath)) {
						if (!path::setPermissions($fullpath, $filemode, $foldermode)) {
							$ret = false;
						}
					} else {
						if (isset ($filemode)) {
							if (!@ chmod($fullpath, octdec($filemode))) {
								$ret = false;
							}
						}
					} // if
				} // if
			} // while
			closedir($dh);
			if (isset ($foldermode)) {
				if (!@ chmod($path, octdec($foldermode))) {
					$ret = false;
				}
			}
		}
		else
		{
			if (isset ($filemode)) {
				$ret = @ chmod($path, octdec($filemode));
			}
		} // if
		return $ret;
	}
	static function canChmod($path)
	{
		$perms = fileperms($path);
		if ($perms !== false)
		{
			if (@ chmod($path, $perms ^ 0001))
			{
				@chmod($path, $perms);
				return true;
			}
		}
		return false;
	}
}
?>