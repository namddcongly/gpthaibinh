<?php

/*
 * @author: Ken Phan <kenphan19@gmail.com>
 * @copyright: Ken Phan <http://kenphan.com>
 * @license: http://www.opensource.org/licenses/gpl-license.php
 * @using: The core object class used to call Module, Class then sent to floor View assign to the template
 */
 
class File extends KFManager
{
	function view ()
	{
		$list		= FOLDER;
		if($_GET['list'] != '') {
			$list .= '.' . $_GET['list'];
		}
		$listDs		= $this->formatPath($list, true);
		$listPath	= $this->formatPath($list, true, 'url');
		$listFull	= $this->formatPath($_GET['list']);
		
		$dirs = $this->filterdir($listFull);
		if($dirs == '2') {
			echo '({ rep: 2, total: 0 })';
			return false;
		}
		
		$fileExt	= $this->formatExts(ALLOWEDEXTS);
		$imgExt		= $this->formatExts(ALLOWEDIMAGE);
		$allowedExt = array_diff($fileExt, $imgExt);
		
		$arr = Array(); $i = 0;
		foreach( $dirs as $val ) {
			$ext = pathinfo($val);
			if(in_array(strtolower($ext['extension']), $fileExt)) {
				if(in_array(strtolower($ext['extension']), $allowedExt)) {
					$imgFile = DS.'data'.DS. KFM . '/clientscript/images/file/' . $ext['extension'] . '.gif';
					$arr[$i]['w'] = 48;	$arr[$i]['h'] = 50; $arr[$i]['d'] = 1;
				} else {
					$imgFile = DS.'data'.DS. $listPath . '/' . $val;
					$file = $listFull . DS . $val;
					list($w, $h) = @getimagesize($file);
					$arr[$i]['w'] = $w;	$arr[$i]['h'] = $h; $arr[$i]['d'] = 0;
				}
				$arr[$i]['n'] = $val;
				$arr[$i]['p'] = $list;
				$arr[$i]['f'] = $imgFile;
				$arr[$i]['e'] = $ext['extension'];
				$arr[$i]['s'] = $this->formatBytes( @filesize($file) );
				$arr[$i]['t'] = date("d/m/Y - g:i A", @filemtime($file));
				$i++;
			}
		}
		echo '({ rep: 1, f: '.json_encode($listFull).', total: '.count($arr).', files: '.json_encode($arr).' })';
	}
	
	function rename ()
	{
		$path = $this->formatPath($_GET['path']);
		$newf = $path . DS . strtolower($_GET['newname']);
		$oldf = $path . DS . $_GET['oldname'];
		if(!$this->validdir($_GET['newname'])) {
			echo '({ rep: 0, v: "va" })';
			return false;
		}
		if(@file_exists($newf)) {
			echo '({ rep: 2 })';
		} else if(@rename($oldf, $newf)) {
			echo '({ rep: 1 })';
		} else {
			echo '({ rep: 0 })';
		}
	}
	
	function delete()
	{
		$path 	= $this->formatPath($_GET['path']);
		$file	= $path . DS . $_GET['file'];
		if(@unlink($file)) {
			echo '({ rep: 1 })';
		} else {
			echo '({ rep: 0, f: "'.$file.'" })';
		}
	}
	
	function download ()
	{
		$file = DATA_PATH . $_GET['f'];
		if(!file_exists($file) || !is_file($file) || !is_readable($file)) {
			die("file not defined !");
		}
		$fp = fopen($file, "rb");
		header("Content-type: application/octet-stream");
		header('Content-disposition: attachment; filename="'.$file.'"');
		header("Content-length: " . filesize($file));
		fpassthru($fp);
		fclose($fp);
	}
	
	function upload ()
	{
		if (!empty($_FILES)) {
			$fileFolder = FOLDER . DS;
			if($_GET['folder'] != '') {
				$fileFolder .=  $this->formatPath($_GET['folder'] . '.', true);
			}
			$filePath	= UPLOAD_DIR. DS . $fileFolder;
			$fileTemp	= $_FILES['Filedata']['tmp_name'];
			$fileName	= $_FILES['Filedata']['name'];
			$fileTarget	= $filePath . $fileName;
			
			move_uploaded_file($fileTemp, $fileTarget);
			chmod($fileTarget, 0777);
			
			$fp = fopen('log.txt', 'w');
			fwrite($fp, 'folder: '.$_GET['folder']." \n");
			fwrite($fp, 'fullfolder: '.$fileFolder." \n");
			fwrite($fp, 'fileFolder: '.$fileTarget." \n");
			fclose($fp);
		}
		echo '1';
	}
}
?>