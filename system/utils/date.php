<?php
/*****
 * class Convert
 * Xu ly thoi gian
 * @ author NamDD < namdd@xahoinet.vn >
 *****/
define('TIME_REFERENCE','gmt');
define('REGEX_DATE','/^[^\d]*(\d{1,2})[^\d]+(\d{1,2})[^\d]+(\d{4}|\d{2}).*$/');
/*Time reference often local or gmt*/
class Date{
	function now(){
		if(strtolower(TIME_REFERENCE)=='gmt'){
			$now = time();
			$system_time = mktime(gmdate("H", $now), gmdate("i", $now), gmdate("s", $now), gmdate("m", $now), gmdate("d", $now), gmdate("Y", $now));
			if (strlen($system_time) < 10){
				$system_time = time();
			}
			return $system_time;
		}else{
			return time();
		}
	}
	function mdate($datestr = '', $time = '',$sys=true){
		if ($datestr == '')	return '';
		if($sys) $datestr=str_replace('-',DATE_SEPERATOR,$datestr);
		if ($time == '')	$time = now();
		$datestr = str_replace('%\\', '', preg_replace("/([a-z]+?){1}/i", "\\\\\\1", $datestr));
		return date($datestr, $time);
	}
	function formatDateSys(){
		$tmp=explode('-',FORMAT_DATE);
		$date=$tmp[0].DATE_SEPERATOR.$tmp[1].DATE_SEPERATOR.$tmp[2];
		return $date;
	}
	function mdateSys($time){
		$datestr=format_date_sys();
		$datestr = str_replace('%\\', '', preg_replace("/([a-z]+?){1}/i", "\\\\\\1", $datestr));
		return date($datestr, $time);
	}
	function standardDate($fmt = 'DATE_RFC822', $time = ''){
		$formats = array(
						'DATE_ATOM'		=>	'%Y-%m-%dT%H:%i:%s%Q',
						'DATE_COOKIE'	=>	'%l, %d-%M-%y %H:%i:%s UTC',
						'DATE_ISO8601'	=>	'%Y-%m-%dT%H:%i:%s%O',
						'DATE_RFC822'	=>	'%D, %d %M %y %H:%i:%s %O',
						'DATE_RFC850'	=>	'%l, %d-%M-%y %H:%m:%i UTC',
						'DATE_RFC1036'	=>	'%D, %d %M %y %H:%i:%s %O',
						'DATE_RFC1123'	=>	'%D, %d %M %Y %H:%i:%s %O',
						'DATE_RSS'		=>	'%D, %d %M %Y %H:%i:%s %O',
						'DATE_W3C'		=>	'%Y-%m-%dT%H:%i:%s%Q'
						);
						if ( ! isset($formats[$fmt])){
							return FALSE;
						}
						return mdate($formats[$fmt], $time);
	}
	function daysInMonth($month = 0, $year = ''){
		if ($month < 1 OR $month > 12){
			return 0;
		}
		if ( ! is_numeric($year) OR strlen($year) != 4){
			$year = date('Y');
		}
		if ($month == 2){
			if ($year % 400 == 0 OR ($year % 4 == 0 AND $year % 100 != 0)){
				return 29;
			}
		}
		$days_in_month	= array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		return $days_in_month[$month - 1];
	}
	function localToGmt($time = ''){
		if ($time == '') $time = time();
		return mktime( gmdate("H", $time), gmdate("i", $time), gmdate("s", $time), gmdate("m", $time), gmdate("d", $time), gmdate("Y", $time));
	}
	function gmtToLocal($time = '', $timezone = 'UTC', $dst = FALSE){
		if ($time == ''){
			return now();
		}
		$time += timezones($timezone) * 3600;
		if ($dst == TRUE){
			$time += 3600;
		}
		return $time;
	}
	function afterDate($day){
		$nextday=time()+($day*24*60*60);
		return  date('Y-m-d',$nextday);
	}
	function mysqlFormatDate($datetime){
		@define('S_DB_DATE','$3-$2-$1');
		$count=0;
		$datetime = preg_replace(REGEX_DATE,S_DB_DATE,$datetime,1,$count);
		if($count) return $datetime; else return '0000-00-00';
	}
}
