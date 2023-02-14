<?php
defined ( 'IN_JOC' ) or die ( 'Restricted Access' );
$host='localhost';
$hot_reserve='localhost';
return array (
	'default'=>'db',
	'database' => array (
		'db' => array (
			'username' => 'etstech_dev',
			'password' => 'sX5X$qtawp@kYx&^',
			'host' =>$host,
			'host_reserve'=>$hot_reserve, 
			'dbname' => 'platform',
			'object' => 'MySQLi'),
		'news' => array (
			'username' => 'etstech_dev',
			'password' => 'sX5X$qtawp@kYx&^',
			'host' => $host, 
			'host_reserve'=>$hot_reserve,	
			'dbname' => 'news',
			'object' => 'MySQLi')
)
);
