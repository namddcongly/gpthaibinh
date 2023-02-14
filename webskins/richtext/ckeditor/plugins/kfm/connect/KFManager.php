<?php

/*
 * @author: Ken Phan <kenphan19@gmail.com>
 * @copyright: Ken Phan <http://kenphan.com>
 * @license: http://www.opensource.org/licenses/gpl-license.php
 * @using: The core object class used to call Module, Class then sent to floor View assign to the template
 */

class KFManager
{
	var $allowedExts = array();
	
	function install ($type, $action)
	{
		$classfile = MODULE_PATH . $type . '.php';
		if(!is_file($classfile) || $action == '') {
			return false;
		}
		require_once $classfile;
		$object = ucfirst($type);
		$install = new $object();
		$install->$action();
		return true;
	}
	
	function getdir ($path)
	{
		$dirs = $this->filterdir($path);
		if($dirs == '2') {
			return false;
		}
		$arr = Array();
		$k = 0;
		foreach( $dirs as $v ) {
			$currpath = $path . DS . $v;
			if( is_dir( $currpath ) ) {
				$currfile 	  = $path . DS . $v;
				$arr[$k]['n'] = $v;
				$arr[$k]['h'] = $this->checkdir($currfile);
				$k++;
			}
		}
		return $arr;
	}
	
	function deleledir($path)
	{
		$dirs = $this->filterdir($path);
		if($dirs == '2') {
			return false;
		}
		foreach($dirs as $v) {
			$pathsub = $path . DS . $v;
			if(is_file($pathsub)) {
				@unlink($pathsub);
			} else {
				$this->deleledir($pathsub);
			}
		}
		rmdir($path);
		return true;
	}
	
	function checkdir($path)
	{
		$dirs = $this->filterdir($path);
		if($dirs == '2') {
			return '2';
		}
		foreach( $dirs as $v ) {
			$currpath = $path . DS . $v;
			if( is_dir( $currpath ) ) {
				return '1';
			}
		}
		return '0';
	}
	
	function validdir($name)
	{
		return true;
	}
	
	function filterdir ($path)
	{
		if(!is_dir($path)) {
			return '2';
		}
		$dirs = array_diff( scandir($path . DS), array('.', '..') );
		return $dirs;
	}
	
	function formatExts($str)
	{
		$arr = explode(', ', $str);
		return $arr;
	}
	
	function formatPath ($path, $notFull = false, $rep = false) {
		if(!$rep) {
			if(!$notFull) {
				$sitepath = DATA_PATH;
			}
			$path = $sitepath . preg_replace('#\.#i', DS, $path);
		} else {
			if($rep == 'url') {
				if(!$notFull) {
					$path = preg_replace('#\\\#i', '/', $path[0]).'/'.$path[1];
				} else {
					$path = preg_replace('#\.#i', '/', $path);
				}
			} else if($rep == 'path') {
				$path = preg_replace('#\\\|\/#i', '.', $path);
			}
		}
		return $path;
	}
	
	function formatBytes($bytes, $precision = 2)
	{
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
	  	$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	} 
}
?>