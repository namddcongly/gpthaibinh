<?php
session_start();
/*
 * @author: Ken Phan <kenphan19@gmail.com>
 * @copyright: Ken Phan <http://kenphan.com>
 * @license: http://www.opensource.org/licenses/gpl-license.php
 * @using: The core object class used to call Module, Class then sent to floor View assign to the template
 */

/*
 * @description: config path
 */
define ('DS',DIRECTORY_SEPARATOR);
define ('KFM', 'plugins/kfm');
define ('FOLDER', 'upload');
define ('MODULES', 'modules');
define ('DOMAIN_SITE', 'http://back.xahoi.com.vn/data/');
define ('DATA_DIR', preg_replace('#\/#i', DS, $_SERVER['DOCUMENT_ROOT']).DS.'data');
define ('UPLOAD_DIR',DATA_DIR);// tn project + &#273;&#432;&#7901;ng d&#7851;n &#273;&#7871;n th&#432; m&#7909;c data
/*
 * @description: file allowed
 */
define ('ALLOWEDFLASH', 'swf');
define ('ALLOWEDIMAGE', 'bmp, gif, jpeg, jpg, png');
define ('ALLOWEDMEDIA', 'avi, flv, mp3, wma, wmv');
define ('ALLOWEDFILE', 'ai, doc, fla, mdb, pdf, ppt, psd, rar, swf, txt, wma, xls, zip, gzip, chm, ttf, gdf, bmp, gif, jpeg, jpg, png');
define ('ALLOWEDEXTS', 'bmp, gif, jpeg, jpg, png, ai, avi, doc, fla, flv, mdb, mp3, pdf, ppt, psd, rar, swf, txt, wma, xls, zip, gzip, wmv, chm, ttf, gdf');

/*
 * not change
 */
define ('PATH',			dirname(__FILE__));
define ('SITE_PATH',	realpath(dirname(__FILE__) . DS . '..' . DS) . DS);
define ('DATA_PATH',	DATA_DIR . DS. FOLDER . DS);
define ('MODULE_PATH',	SITE_PATH . DS . MODULES . DS);
?>