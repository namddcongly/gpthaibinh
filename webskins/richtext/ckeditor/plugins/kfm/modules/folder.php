<?php

/*
 * @author: Ken Phan <kenphan19@gmail.com>
 * @copyright: Ken Phan <http://kenphan.com>
 * @license: http://www.opensource.org/licenses/gpl-license.php
 * @using: The core object class used to call Module, Class then sent to floor View assign to the template
 */
 
class Folder extends KFManager
{
	function view ()
	{
		$path = $this->formatPath( $_GET['path'] );
		$db = $this->getdir($path);
		if(!$db) {
			echo '({ rep: 0, ';
		} else {
			echo '({ rep: 1, ';
		}
		echo 'dirs: '.json_encode($db).' })';
	}
	
	function make ()
	{
		$name 	= strtolower($_GET['name']);
		$path	= $this->formatPath( $_GET['path'] );
		$hasul  = $_GET['hasul'];
		$folder	= $path . DS . $name;
		if(!$this->validdir($name) || !is_dir($path)) {
			echo '({ rep: 0 })';
			return false;
		}
		if($hasul == 1) {
			if (is_dir($folder)) {
				echo '({ rep: 2, ';
			} else {
				if(mkdir($folder, 0777)) {
					echo '({ rep: 1, ';
				} else {
					echo '({ rep: 0, ';
				}
			}
			$d = $this->getdir($path);
			echo 'dirs: '.json_encode($d).' })';
		} else {
			if (is_dir($folder)) {
				echo '({ rep: 2 })';
			} else {
				if(mkdir($folder, 0777)) {
					echo '({ rep: 1, n: "'.$name.'", h: 0 })';
				} else {
					echo '({ rep: 0 })';
				}
			}
		}
	}
	
	function delete ()
	{
		$path = $this->formatPath( $_GET['path'] );
		if(!$this->deleledir($path)) {
			echo '({ rep: 0 })';
		} else {
			echo '({ rep: 1 })';
		}
	}
	
	function rename ()
	{
		$new	= $_GET['newname'];
		$old	= $_GET['oldname'];
		$list	= $this->formatPath($_GET['path'], true);
		$path	= DATA_PATH . substr($list, 0, strlen($list) - strlen($old));
		$npath	= $path . $new;
		$opath	= DATA_PATH . $list;
		
		if(!$this->validdir($new)) {
			echo '({ rep: 0 })';
			return false;
		}
		if(@file_exists($npath)) {
			echo '({ rep: 2 })';
		} else if(@rename($opath, $npath)) {
			echo '({ rep: 1 })';
		} else {
			echo '({ rep: 0, o: '.json_encode($opath).', n: '.json_encode($npath).' })';
		}
	}
}
?>