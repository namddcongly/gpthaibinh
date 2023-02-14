<?php
/**
 * Profiler
 * Object su dung danh dau cac hoat dong cua project
 * su dung trong moi truong debug
 *
 * @author 		NamDD<namdd@xahoinet.vn>
 * @package 	system
 * @subpackage 	error
 * @version 	1.0
 *
 */
class Profiler {
	/**
	 * Buffer
	 *
	 * @var string
	 */
	private $_buffer;

	/**
	 * Start
	 * Thoi diem bat dau
	 *
	 * @var int
	 */
	private $_start = 0;
	/**
	 * Instance;
	 *
	 * @var object Profiler
	 */
	private static $_instance;

	private function __construct() {
		$this->_start = $this->_getMicrotime ();
		$this->_buffer = array ();
	}

	/**
	 * Get Instance
	 *
	 * @return object Profiler
	 */
	public static function getInstance() {
		if (! isset ( self::$_instance )) {
			self::$_instance = new Profiler ( );
		}

		return self::$_instance;
	}

	/**
	 * Get Buffer
	 *
	 * @return array
	 */
	public function getBuffer() {
		return $this->_buffer;
	}

	/**
	 * Get Memory Usage
	 *
	 * @return float MB
	 */
	public function getMemUsage() {
		$mem = sprintf ( '%0.3f', memory_get_usage () / 1048576 ) . ' MB';
		return $mem;
	}

	/**
	 * Output a time mark
	 *
	 * The mark is returned as text enclosed in <div> tags
	 * with a CSS class of 'profiler'.
	 *
	 * @access public
	 * @param string A label for the time mark
	 * @return string Mark enclosed in <div> tags
	 */
	public function mark($label, $package = 'news') {
		$mark = 'Package: <strong>' . $package . '</strong>. ';
		$mark .= $label . '. <strong>&raquo;</strong> ';
		$mark .= sprintf ( 'Time %.5f', $this->_getMicrotime () - $this->_start ) . ' seconds';
		$mark .= ', ' . sprintf ( '%0.3f', memory_get_usage () / 1048576 ) . ' MB.';
		$this->_buffer [] = $mark;
		return $mark;
	}

	/**
	 * Debug
	 * draw ra khối debug.
	 *
	 * @return HTML content
	 */
	public static function debug() {
		if (! IN_DEBUG)
		return;
		$profiler = Profiler::getInstance ();
		ob_start ();
		echo '<style type="text/css">#system-debug{font-size: 9pt; margin-top:10px;color:#555555; line-height: 1.5em;}
				#system-debug div#sql-report strong{ color:#993333}
			</style><div id="system-debug" class="profiler"><h3>TRONG MÔI TRƯỜNG DEBUG</h3>';

		#List Mark
		echo '<h4>THÔNG TIN HOẠT ĐỘNG</h4>';
		echo '<ol>';
		$marks = $profiler->getBuffer ();
		for($i = 0, $msize = sizeof ( $marks ); $i < $msize; ++ $i) {
			echo '<li>' . $marks [$i] . '</li>';
		}
		echo '</ol>';
		echo '<div>';
		echo '<h4>SỬ DỤNG BỘ NHỚ</h4>';
		$totalM=$profiler->getMemUsage ();
		echo $totalM;
		if ($totalM>MAX_MEMORY_LOG)
		{
			$content=date('d/m/Y H:i',time()).' '.$_SERVER['REQUEST_URI']. ' Memory: '.$totalM."\n\n";
			$file='data/log_slow.txt';
			Folder::writeLogFile($file,$content);
		}
		echo '</div>';

		#Show log SQL queries
		$newlineSQLKeywords = '/<strong>' . '(FROM|LEFT|INNER|OUTER|WHERE|SET|VALUES|ORDER|GROUP|HAVING|LIMIT|ON|AND|OR)' . '<\\/strong>/i';
		$sqlKeyword = array ('ASC', 'AS', 'ALTER', 'AND', 'AGAINST', 'BETWEEN', 'BOOLEAN', 'BY', 'COUNT', 'DESC', 'DISTINCT', 'DELETE', 'EXPLAIN', 'FOR', 'FROM', 'GROUP', 'HAVING', 'INSERT', 'INNER', 'INTO', 'IN', 'JOIN', 'LIKE', 'LIMIT', 'LEFT', 'MATCH', 'MODE', 'NOT', 'ORDER', 'OR', 'OUTER', 'ON', 'REPLACE', 'RIGHT', 'STRAIGHT_JOIN', 'SELECT', 'SET', 'TO', 'TRUNCATE', 'UPDATE', 'VALUES', 'WHERE' );

		$sqlReplaceKeyword = array ('<strong>ASC</strong>', '<strong>AS</strong>', '<strong>ALTER</strong>', '<strong>AND</strong>', '<strong>AGAINST</strong>', '<strong>BETWEEN</strong>', '<strong>BOOLEAN</strong>', '<strong>BY</strong>', '<strong>COUNT</strong>', '<strong>DESC</strong>', '<strong>DISTINCT</strong>', '<strong>DELETE</strong><br />', '<strong>EXPLAIN</strong>', '<strong>FOR</strong>', '<strong>FROM</strong>', '<strong>GROUP</strong>', '<strong>HAVING</strong>', '<strong>INSERT</strong>', '<strong>INNER</strong>', '<strong>INTO</strong>', '<strong>IN</strong>', '<strong>JOIN</strong>', '<strong>LIKE</strong>', '<strong>LIMIT</strong>', '<strong>LEFT</strong>', '<strong>MATCH</strong>', '<strong>MODE</strong>', '<strong>NOT</strong>', '<strong>ORDER</strong>', '<strong>OR</strong>', '<strong>OUTER</strong>', '<strong>ON</strong>', '<strong>REPLACE</strong><br />', '<strong>RIGHT</strong>', '<strong>STRAIGHT_JOIN</strong>', '<strong>SELECT</strong><br />', '<strong>SET</strong>', '<strong>TO</strong>', '<strong>TRUNCATE</strong><br />', '<strong>UPDATE</strong><br />', '<strong>VALUES</strong>', '<strong>WHERE</strong>' );

		$daObjs = EDatabase::getDBO ();
		$queriesLogNo = 0;
		$executeTime = 0;
		echo '<div id="sql-report"><h4>CÂU TRUY VẤN</h4><ol>';
		foreach ( $daObjs as $daObj ) {
			$queries = $daObj->getLog ();
			for($i = 0, $qsize = sizeof ( $queries ); $i < $qsize; ++ $i) {
				$sql = str_replace ( $sqlKeyword, $sqlReplaceKeyword, $queries [$i] ['sql'] );
				$sql = preg_replace ( '/\"([^\"])+\"/', '<font color="#ff000;">\\0</font>', $sql );
				$sql = preg_replace ( '/\'([^\'])+\'/', '<font color="#ff000;">\\0</font>', $sql );
				$sql = preg_replace ( $newlineSQLKeywords, '<br />&nbsp;&nbsp;\\0', $sql );
				echo '<li>' . $sql . '<br />&nbsp;&nbsp;&nbsp;&nbsp;<em> thời gian thực hiện: ' . $queries [$i] ['time'] . ' giây</em></li>';
				$executeTime += $queries [$i] ['time'];
			}
			$queriesLogNo += $qsize;
		}
		echo '</ol><br />TỔNG <strong>' . $queriesLogNo . '</strong> Truy vấn SQL mất <em>' . $executeTime . '</em> giây</h4>';

		echo '<div><h4>File Included</h4><ol>';
		$files = get_included_files ();
		for($i = 0, $size = sizeof ( $files ); $i < $size; ++ $i) {
			echo '<li>' . $files [$i] . '</li>';
		}
		echo '</ol><br />Tổng <strong>' . $size . '</strong> included files.</div>';

		$debug = ob_get_clean ();
		return $debug;
	}

	/**
	 * Get the current time.
	 *
	 * @access public
	 * @return float The current time
	 */
	private function _getMicrotime() {
		list ( $usec, $sec ) = explode ( ' ', microtime () );
		return (( float ) $usec + ( float ) $sec);
	}

	private function __clone() {
	}
}