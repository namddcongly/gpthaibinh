<?php
define('IN_JOC',true);
define('DS', '/' );
/*  define path  */
//define('PROJECT_NAME','/');
define('ROOT_PATH',$_SERVER['DOCUMENT_ROOT'].DS);
define('SYSTEM_PATH',ROOT_PATH.'system'.DS);
define('KERNEL_PATH',SYSTEM_PATH.'kernel'.DS);
define('UTILS_PATH',SYSTEM_PATH.'utils'.DS);
define('KERNEL_INCLUDE_PATH',SYSTEM_PATH.'kernel'.DS.'includes'.DS);
define('CONFIG_PATH', 'config'.DS);
define('APPLICATION_PATH', ROOT_PATH.'application'.DS);
define('WEBSKINS_PATH', 'webskins'.DS);
define('LAYOUT_PATH',WEBSKINS_PATH.'layout'.DS);
define('JAVASCRIPT_PATH',WEBSKINS_PATH.'javascript'.DS);
define('SKINS_PATH',WEBSKINS_PATH.'skins'.DS);
define('CACHE_FILE_PATH',ROOT_PATH.'cache'.DS);
define('TEMPLATE_PATH',WEBSKINS_PATH.'template'.DS);
define('CACHE_PATH',ROOT_PATH.'cache'.DS);
define('AJAX_PATH',ROOT_PATH.'ajax'.DS);
define('LOG_PATH',ROOT_PATH.'log'.DS);
define('TEMP_PATH',ROOT_PATH.'temp'.DS);
define('LOG_SLOW_QUERY_FOLDER',LOG_PATH);
/*  define url  */

define('ROOT_URL','https://'.$_SERVER['HTTP_HOST'].DS);
define('IMAGE_URL','http://'.$_SERVER['HTTP_HOST'].DS);
define('STATIC_URL','http://'.$_SERVER['HTTP_HOST'].DS);
define('DATA_URL','http://'.$_SERVER['HTTP_HOST'].DS);
define('NEWS_IMG_URL','data/news/'.date('Y',time()).'/'.date('n',time()).'/'.date('j',time()).'/');
define('NEWS_IMG_PATH',ROOT_PATH.'data'.DS.'news'.DS);
define('NEWS_IMG_UPLOAD',ROOT_PATH.'data'.DS.'news'.DS);
define('VIDEO_UPLOAD',ROOT_PATH.'data'.DS.'video'.DS);
define('ADV_UPLOAD',ROOT_PATH.'data'.DS.'adv'.DS);
define('ADV_UPLOAD_BANNER',ROOT_PATH.'banner'.DS);
define('NEWS_IMG_UPLOAD_BAOGIAY',ROOT_PATH.'data'.DS.'baogiay'.DS);
/* define const */
define('REWRITE',1);//core write 1 la bat rewrite 0 la tat
define('IS_LOCAL',false);
define('IN_DEBUG', false);
define('SHOW_QUERY',false);
define('OBJECT_COMBINE',false);
define('MAX_MEMORY_LOG',8);
define('CACHE_FILE_EXTENSION','.cache');
define('EMAIL_WEB_MASTER','htnamdd@gmail.com');
define('NAME_SESSION_USER','user_joc');
define('NAME_SESSION_USER_CUSTOMEM','user_customer');
define('IS_SLOW_QUERY',1);
define('TIME_RELOAD_USER',7200);
define('DEFAULT_PREFIX_PASSWORD','joc');
define('ENABLE_GZIP', true);
define('CSS_VERSION','1.16');
define('JS_VERSION','1.0');
define('IS_MEMCACHED',false);
define('MEMCACHED_HOST', '127.0.0.1');
define('MEMCACHED_PORT', 11211);
?>
