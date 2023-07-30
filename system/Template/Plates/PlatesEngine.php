<?php

namespace Mojo\Template\Plates;

use Mojo\Template\IEngine;
use Mojo\Template\Plates\Engine;

class PlatesEngine implements IEngine {

    private $_vars = [];
    private $_engine = NULL;

    public function __construct() {
        $this->_engine = new Engine();
    }

    public function setTemplateFolder($path) {
        $this->_engine->setDirectory($path);
    }

    /**
     * void setCacheFolder(string $path)
     */
    public function setCacheFolder($path) {
        // Not needed, Plates does not cache
    }


    public function setIgnoreCache($state=TRUE) {
        // There is no cache in plates, it is just php
        return;
    }

    // These should not be used, this is a large overhead, when we can submit an array
    // of variables to the render method
    public function assign($name, $val) {
        $this->_vars[$name] = $val;
    }
    public function assignByRef($name, &$var) {
        $this->_vars[$name] =& $var;
    }


    public function renderString($template_source, $var_bag=NULL, $return_result=FALSE) {

    }

    public function render($template_file, $var_bag=NULL, $return_result=FALSE) {

    }

    public function fetch($template_file, $var_bag=NULL) {
        if (!empty($var_bag)) {
            $vars = array_merge($this->_vars, $var_bag);
            $var_bag = $vars;
        } else {
            $var_bag = $this->_vars;
        }
        
        return $this->_engine->render($template_file, $var_bag);
    }
}