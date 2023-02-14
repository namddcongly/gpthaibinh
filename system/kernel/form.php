<?php
if(defined(IN_JOC)) die("Direct access not allowed!");

class Form
{
	private $class;

	private $class_name;

	function __construct($object)
	{
		$class_name = get_class($object);

		$this->class[$class_name] = $object;

		$this->class_name = $class_name;

		if($this->is_submit($class_name))
		{
			$object = $this->class[$class_name];

			$object->on_submit();
		}
	}
	function begin($upload=false, $method="POST", $ext="")
	{
		return '<form name="'.$this->class_name.'" method="'.$method.'" '.($upload ? 'enctype="multipart/form-data"' : '').' '.$ext.' >';
	}
	function end()
	{
		return '<input type="hidden" name="'.$this->class_name.'" value="1"></form>';
	}
	function is_submit($name='')
	{
		return isset($_REQUEST[$name]) ? true : false;
	}
}
?>