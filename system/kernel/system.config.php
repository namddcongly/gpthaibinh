<?php
defined('IN_JOC') or die('Restricted Access'); // File ko phải được require ở Running file
/*
 * Danh sách các config class dùng chung trong toàn bộ hệ thống
 */

require_once KERNEL_INCLUDE_PATH.'system.php'; // Class System
require_once KERNEL_INCLUDE_PATH.'folder.php'; // Class xử lý File / thu muc
System:: requireClass(array('url.php','image.show.php'
),UTILS_PATH,'utils');
System:: requireClass(array('database.php',
					        'template.php',
							'error.profiler.php',
							'error.exception.php',
                            'database.object.php',
							'system.privilege.php',
							'user.customer.php',
							'user.current.php'
							),KERNEL_INCLUDE_PATH,'kernel');
							$databaseObject = new DatabaseObject();
							function &dbObject()
							{
								global $databaseObject;
								return $databaseObject;
							}
							$joc=new Template('./');
							function &joc()
							{
								global $joc;
								return $joc;
							}
							?>