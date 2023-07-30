<?php

namespace Mojo\Template\TemplateLite;

use Mojo\Template\IEngine;


// We need to include the needed files for template_lite
$cur_path = dirname(__FILE__);

require_once($cur_path . DS . 'class.config.php');
require_once($cur_path . DS . 'class.template.php');


class TemplateLite implements IEngine {

    protected $eng = NULL;

    public function __construct() {
        $this->eng = new \Template_Lite();
    }

    public function setTemplateFolder($path) {

    }

    /**
     * void setCacheFolder(string $path)
     */
    public function setCacheFolder($path) {

    }


    public function setIgnoreCache($state=TRUE) {

    }

    public function assign($name, $val) {

    }

    public function assignByRef($name, &$var) {

    }

    public function renderString($template_source, $var_bag=NULL, $return_result=FALSE) {

    }

    public function render($template_file, $var_bag=NULL, $return_result=FALSE) {

    }
}