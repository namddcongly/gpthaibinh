<?php
defined ( 'IN_JOC' ) or die ( 'Restricted Access' );

class BaseException extends Exception {
	protected $cause;

	function __construct($p1, $p2 = null) {

		$cause = null;

		if ($p2 !== null) {
			$msg = $p1;
			$cause = $p2;
		} else {
			if ($p1 instanceof Exception) {
				$msg = "";
				$cause = $p1;
			} else {
				$msg = $p1;
			}
		}

		parent::__construct ( $msg );

		if ($cause !== null) {
			$this->cause = $cause;
			$this->message .= " [wrapped: " . $cause->getMessage () . "]";
		}
	}

	/**
	 * Get Cause
	 *
	 */
	public function getCause() {
		return $this->cause;
	}
}

class SystemException extends BaseException {}
class AppException extends BaseException {}
class ModelException extends BaseException {}