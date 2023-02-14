<?php

/*
 * @author: Ken Phan <kenphan19@gmail.com>
 * @copyright: Ken Phan <http://kenphan.com>
 * @license: http://www.opensource.org/licenses/gpl-license.php
 * @using: The core object class used to call Module, Class then sent to floor View assign to the template
 */

ob_start();
session_start();

require_once 'connect/config.php';
require_once 'connect/KFManager.php';

$type = $_GET['type'];
$action = $_GET['action'];

$k = new KFManager();
if(!$k->install($type, $action)) {
	echo 'die '. MODULE_PATH;
}
?>