<?php
/**
 * File Cache
 *
 * @author 		NamDD<namdd@xahoinet.vn>
 * @version 	1.0
 * @since 		JOC
 */
defined ( 'IN_JOC' ) or die ( 'Restricted Access' );

class CacheFile {
	private $_path;
	private $_ext;
	const TYPE_FILE = 1;

	public function __construct() {
		$this->_path = CACHE_FILE_PATH;
		$this->_ext = CACHE_FILE_EXTENSION;

		if (IN_DEBUG) {
			Profiler::getInstance ()->mark ( 'Construct new FileCache object', 'system.cache.' . get_class ( $this ) );
		}
	}

	/**
	 * Get Type
	 *
	 * @return int file cache storage
	 */
	public function getType() {
		return CacheFile::TYPE_FILE;
	}

	/**
	 * @return string $path
	 */
	public function getPath() {
		return $this->_path;
	}

	/**
	 * @param string $path
	 */
	public function setPath($path) {
		$this->_path = $path;
	}

	/**
	 * Set Extension
	 *
	 * @param string $s
	 */
	public function setExtension($s) {
		if (strpos ( $s, '.' ) === false) {
			$this->_ext = '.' . $s;
		} else {
			$this->_ext = $s;
		}
	}

	/**
	 * Get Extension
	 *
	 * @return string
	 */
	public function getExtension() {
		return $this->_ext;
	}

	/**
	 * Set
	 *
	 * @param string $file
	 * @param string $content
	 * @param int $expiration
	 * @return boolean
	 */
	public function set($file, $content, $expiration = 0, $prefix = '', $path = null) {
		if ($path === null) {
			$path = $this->_path;
		}

		$_temp = '["' . $prefix . '_' . $file . '"]';
		$file = $prefix . '_' . $file . $this->_ext;

		if ($expiration <= 0)
		$expiration = CACHE_DEFAULT_EXPIRE;
		$expiration += time ();
		//chmod($path . $file, 0755);
		//chinhnd   $bytes = file_put_contents($path . $file, serialize ($content));
		if (IN_DEBUG) { //Neu trong moi truong debug thi mark lai thong tin cache
			Profiler::getInstance()->mark('Saving ' . $_temp . ' (' . $bytes . ' bytes) to Cache, expire on '
			. date ('d/m/Y H:i:s', $expiration), 'system.cache.' . get_class($this));
		}
		return @touch ( $path . $file, $expiration );
	}

	/**
	 * Get
	 *
	 * @param string $files
	 * @return string $content
	 */
	public function get($file, $prefix = '', $path = null,$time_expire=0) {
		if ($path === null) {
			$path = $this->_path;
		}
		$id = $prefix . '_' . $file;
		$file = $path . $prefix . '_' . $file . $this->_ext;

		if (! is_file ( $file ))
		return false;

		if ($time_expire==0 or (filemtime ( $file ) < (abs(time ()- $time_expire)))) {
			@unlink($file); //Khong xoa file khi exprise
			return false;
		}

		$result = file_get_contents ( $file );
		$result = unserialize ( $result );

		if (IN_DEBUG)
		Profiler::getInstance ()->mark ( 'Retrieve "' . $id . '" from cache', 'system.cache.' . get_class ( $this ) );

		return $result;
	}

	/**
	 * Get Multi Cache
	 *
	 * @param Array $files
	 * @param Array $writeOn
	 */
	public function getMulti($files, &$writeOn, $path = null) {
		for($i = 0, $size = sizeof ($files); $i < $size; ++ $i) {
			$writeOn [$files [$i]] = $this->get($files[$i]['key'], $files[$i]['prefix'], $path );
		}
	}

	/**
	 * Call Back
	 *
	 * @param string $files ten file
	 * @param array $callback callback function
	 * @param array $args parameter
	 * @param int $expiration seconds
	 * @return mixed
	 */
	public function callback($callback, $args = null, $expiration = 0, $prefix = '', $path = null) {
		$args = (array) $args;

		$key = implode('::', $callback ) . '(' . implode (',', $args) . ')';
		$keyHash = md5($key);

		if (($result = $this->get($keyHash, $prefix, $path)) === false) {
			$result = call_user_func_array($callback, $args);
			$this->set($keyHash, $result, $expiration, $prefix, $path);
		}

		if (IN_DEBUG) {
			Profiler::getInstance ()->mark ( 'Call back result method ' . $key, 'system.cache.' . get_class ( $this ) );
		}

		return $result;
	}

	/**
	 * Delete
	 * Delete cache by $file
	 *
	 * @param string $file
	 * @param string $prefix
	 * @param string $path
	 *
	 * @return boolean
	 */
	public function delete($file, $prefix = '', $path = null) {
		if ($path === null)
		$path = $this->_path;

		$_temp = $prefix . '_' . $file;
		$file = $prefix . '_' . $file . $this->_ext;
		$result = @unlink($path . $file);

		if (IN_DEBUG && $result === true) {
			Profiler::getInstance()->mark('Remove cache ["' . $_temp . '"]' . $file, 'system.cache.' . get_class($this));
		}

		return $result;
	}

	/**
	 * Flush
	 * Xoa toan bo cache
	 *
	 */
	public function flush() {
		$path = $this->_path;

		if (($handle = opendir($path)) === false)
		return false;

		while($file = readdir($handle)) {
			if ($file [0] === '.')
			continue;
			$fullPath = $path . DS . $file;
			if (is_file ($fullPath))
			unlink ($fullPath);
		}
		closedir($handle);

		if (IN_DEBUG)
		Profiler::getInstance()->mark('Flush all cache', 'system.cache.' . get_class($this));

		return true;
	}

	/**
	 * Garbage collect expired cache data
	 *
	 * @return void
	 *
	 */
	public function gc() {
		$path = $this->_path;

		if (($handle = opendir($path)) === false)
		return;

		while($file = readdir($handle)) {
			if (($file [0] === '.') || ($file [0] === '..'))
			continue;
			$fullPath = $path . DS . $file;
			if (is_file ($fullPath)) {
				if (filemtime($fullPath) < time())
				unlink($fullPath);
			}
		}
		closedir($handle);

		if (IN_DEBUG)
		Profiler::getInstance()->mark('Garbage collect expired cache data', 'system.cache.' . get_class($this));
	}
	/**
	 * request toi 1 dia chi url;
	 *
	 * @param unknown_type $url
	 */
	static function requestUrl($url)
	{
		@file_get_contents(ROOT_URL.$url);
	}
}
