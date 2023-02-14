<?php
/**
 * Memcache
 *
 * @author 		Thunv<thunv@xahoi.com.vn>
 * @version 	1.0
 * @since 		JOC
 */
defined ( 'IN_JOC' ) or die ( 'Restricted Access' );

class Memcached
{
	private $obj;

	function __construct()
	{
		
		$this->obj = new Memcache;
		$this->obj->pconnect(MEMCACHED_HOST, MEMCACHED_PORT);
		
	}
	/**
	 * @key Khoa de luu du lieu
	 * @value gia tri luu
	 * @expire thoi gian het han(tinh theo giay, 0 ko bao gio het han)
	 * @compress cho phep nen hay ko nen du lieu
	 */
	function addData($key, $value,$expire = 0, $compress = false)
	{
		return $this->obj->add($key, $value, $compress, $expire);
	}
	function setData($key, $value,$expire = 0, $compress = false)
	{		
		return $this->obj->set($key, $value, $compress, $expire);
	}
	function getData($key)
	{
		return $this->obj->get($key);
	}
	function closeMem()
	{
		$this->obj->close();
	}
	function deleteData($key)
	{
		return $this->obj->delete($key);
	}
	function flushData()
	{
		return $this->obj->flush();
	}
}