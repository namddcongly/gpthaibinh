<?php
ini_set('display_errors',0);
session_cache_expire(3600);
session_start();
ini_set('session.gc-maxlifetime', 3600);
date_default_timezone_set('Asia/Bangkok');
include 'define.php';
include KERNEL_PATH . 'portal.php';
if (IN_DEBUG) {
    Profiler::getInstance()->mark('End script', 'run.index.php');
    echo Profiler::debug();
}

?>