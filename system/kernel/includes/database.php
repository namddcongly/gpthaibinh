<?php
/**
 * EDatabase
 *
 * @author		NamDD <namdd@xahoi.com.vn>
 * @since		JOC
 * @version 	1.0
 * @package 	system
 * @subpackage 	database
 */
class EDatabase {
	const TYPE_MYSQL = 1;
	const TYPE_MYSQLI = 2;
	const TYPE_PDO = 3;

	protected $_log = array ();

	/**
	 * Config
	 * mang config
	 *
	 * @var Array
	 */
	protected static $config = array();

	/**
	 * Instances
	 * mang cac doi tuong khoi Data Object
	 *
	 * @var Array
	 */
	protected static $instances = array();

	/**
	 * Use Transaction
	 *
	 * @var boolean
	 */
	private $_transaction;

	protected function __construct() {
		$this->_transaction = isset($config['transaction']) ? (boolean) $config['transaction'] : false;
		if (IN_DEBUG)
		Profiler::getInstance()->mark('Kết nối CSDL', 'system.database.' . get_class($this));
	}

	/**
	 * Log
	 *
	 * @param string $sql
	 * @param float $time
	 * @return void
	 */
	public function log($sql, $time) {
		$this->_log[] = array ('sql' => $sql, 'time' => $time );
		if (SHOW_QUERY) {
			echo '<p style="font-size:0.9em">&raquo;[SQL]: ' . $sql
			. '<br /> &nbsp;&nbsp;&nbsp;Time: <strong>' . $time . '</strong></p>';
		}
		if ($time >= IS_SLOW_QUERY) {
			$fileName = date('d-n-Y_H') . '.log';
			$handle = fopen(LOG_SLOW_QUERY_FOLDER . $fileName, 'a');
			$content = "[" . date('d/m/Y H:i:s', time()) . '] ' . $sql ."\nTime: " . $time."\n";
			fwrite($handle, $content);
			fclose($handle);
			if (IN_DEBUG) {
				Profiler::getInstance()->mark('Hiển thị Log', 'system.database.' . get_class($this));
			}
		}
	}

	/**
	 * Get Log query
	 *
	 * @return Array
	 */
	public function getLog() {
		return $this->_log;
	}

	/**
	 * Get Database Object
	 *
	 * @return Array
	 */
	public static function getDBO() {
		return self::$instances;
	}

	/**
	 * Get Microtime
	 *
	 * @return float
	 */
	public function getMicrotime() {
		list ( $usec, $sec ) = explode ( ' ', microtime () );
		return (( float ) $usec + ( float ) $sec);
	}

	/**
	 * Initialize Configuration
	 *
	 * @return void
	 */
	public static function initConfig() {
		self::$config = require_once ROOT_PATH . DS . 'config' . DS . 'database.config.php';
	}

	/**
	 * Get Database
	 *
	 * @param string $name
	 * @throws SystemException
	 */
	public static function getConnection($name = null) {
		if (sizeof ( self::$config ) === 0) {
			self::initConfig ();
		}

		if ($name === null || ! isset ( self::$config ['database'] [$name] )) { // Neu khong chi dinh ro ten hoac ten khong nam trong config thi se load ra doi tuong default
			$name = self::getDefault ();
		}

		if (! isset ( self::$config ['database'] [$name] )) { // Neu khong co config nao co ten nhu vay
			throw new SystemException ( 'Không tìm thấy cấu hình !' );
		}
		
		$dsn = self::$config ['database'] [$name] ['username'] . ':' . self::$config ['database'] [$name] ['password'] . '@' . self::$config ['database'] [$name] ['host'];

		$object = self::$config ['database'] [$name] ['object'];

		if (! isset ( self::$instances [$dsn] )) {
			require_once dirname ( __FILE__ ) . DS . 'database.' . strtolower ( $object ) . '.php';
			$object = 'E' . $object;
			self::$instances [$dsn] = new $object ( self::$config ['database'] [$name] );
		} else {
			self::$instances [$dsn]->selectDb ( self::$config ['database'] [$name] ['dbname'] );
		}

		return self::$instances [$dsn];
	}

	public static function getDefault() {
		return self::$config ['default'];
	}
} // End Class Database

//Khoi tao cac con tro


	if (!function_exists('db')) {
		function db() {
			return EDatabase::getConnection('db');
		}
	}
	if (!function_exists('news')) {
		function news() {
			return EDatabase::getConnection('news');
		}
	}
	if (!function_exists('xahoi')) {
		function xahoi() {
			return EDatabase::getConnection('xahoi');
		}
	}
	if (!function_exists('raovat')) {
		function raovat() {
			return EDatabase::getConnection('raovat');
		}
	}
	if (!function_exists('bds')) {
		function bds() {
			return EDatabase::getConnection('bds');
		}
	}
	if (!function_exists('com')) {
		function com() {
			return EDatabase::getConnection('com');
		}
	}
