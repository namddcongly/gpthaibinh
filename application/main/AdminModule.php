<?php
if (defined(IN_JOC)) die("Direct access not allowed!");
if (!UserCurrent::havePrivilege('ADMIN_MODULE')) {
    Url::urlDenied();
}
require_once(KERNEL_INCLUDE_PATH . 'folder.php');

class AdminModule extends Form
{
    private $msg = "";

    private $moduleObj;
    private $modules;
    private $files;

    function __construct()
    {
        $this->moduleObj = SystemIO::createObject('ModuleModel');

        $this->modules = $this->moduleObj->getInfoModule("id,name,portal_name,path", "", "", "", "");

        Form::__construct($this);
    }

    function on_submit()
    {
        $temp = scandir(APPLICATION_PATH);
        $tmp_md = $this->modules;
        $this->modules = array();
        if (count($tmp_md) > 0) {
            foreach ($tmp_md as $tm)
                $this->modules[$tm['portal_name'] . '_' . $tm['name']] = $tm;
        }

        if (count($temp) > 0) {
            foreach ($temp as $tmp) {
                if ($tmp != "." && $tmp != ".." && $tmp != ".svn") {
                    $foldObj = new Folder(APPLICATION_PATH . $tmp);

                    $folders = $foldObj->explode();
                    $this->show($folders, $tmp . DS, $tmp);

                }
            }
            if (count($this->files) > 0) {
                foreach ($this->files as $file) {
                    $tem = str_replace(".php", "", $file['name']);
                    if (!isset($this->modules[$file['portal_name'] . '_' . $tem])) {

                        $this->moduleObj->addModule(array("name" => $tem, "portal_name" => $file['portal_name'], "path" => $file['path'] . $file['name']));
                        $this->msg .= "Cập nhật module: <b>" . $file['name'] . "</b> (" . $file['path'] . $file['name'] . ")<br />";
                    } else {
                        if ($this->modules[$file['portal_name'] . '_' . $tem]['portal_name'] != $file['portal_name']) {

                            $this->moduleObj->addModule(array("name" => $tem, "portal_name" => $file['portal_name'], "path" => $file['path'] . $file['name']));
                            $this->msg .= "Cập nhật module: <b>" . $file['name'] . "</b> (" . $file['path'] . $file['name'] . ")<br />";
                        }
                    }
                }
                /*scan on server
                 if(isset($_REQUEST['submit_server']))
                    $this->moduleObj->deleteModule();
                */
            }

            if ($this->msg == "")
                $this->msg .= "Không có module nào được cập nhật";
        }
    }

    function index()
    {
        Page::setHeader("Danh sách Module", "", "");

        Page::registerFile('admin_platform.js', Module::pathJS() . 'platform.js', 'footer', 'js');

        joc()->set_file('HOME', Module::pathTemplate() . "admin_module.htm");

        joc()->set_block('HOME', 'MODULE');

        joc()->set_var('msg', $this->msg);

        joc()->set_var('begin_form', Form::begin(false, "POST", 'onsubmit="return add_page()"'));

        joc()->set_var('end_form', Form::end());

        $html_module = "";

        $temp_modules = $this->modules;

        if ($temp_modules) {
            $modules = array();

            foreach ($temp_modules as $tmd)
                $modules[$tmd['portal_name']][] = $tmd;

            $i = 1;

            foreach ($modules as $portal => $module) {
                joc()->set_var('stt', $i);

                joc()->set_var('portal_name', $portal);

                $html_path = "";

                foreach ($module as $mod)
                    $html_path .= "<p style=\"text-align:left;\">+ <b>" . $mod['name'] . "</b> (" . APPLICATION_PATH . $mod['path'] . ").  [ <a href='javascript:delModule(" . $mod['id'] . ")'>Xóa module</a>]</p>";
                joc()->set_var('module_name_path', $html_path);
                joc()->set_var('portal_path', APPLICATION_PATH . $portal);
                $html_module .= joc()->output('MODULE');

                $i++;
            }
        }

        joc()->set_var('MODULE', $html_module);

        $html = joc()->output("HOME");

        joc()->reset_var();

        return $html;
    }

    function show($arr, $path = "", $portal_name = "main")
    {
        if ($arr) {
            $fl = $arr['files'];
            $fd = $arr['folders'];

            if (count($fl) > 0)
                foreach ($fl as $f)
                    $this->files[] = array("name" => $f, "portal_name" => $portal_name, "path" => $path);

            if (count($fd) > 0)
                foreach ($fd as $fo)
                    if ($fo['name'] !== ".svn" && $fo['name'] != DS . ".svn" && $fo['name'] != "includes")
                        $this->show($fo, $path . $fo['name'] . DS, $portal_name);
        }
    }
}

?>
