<?php

namespace Mojo\Template;

// Include TemplateLite
require_once dirname(__FILE__) . DS . 'TemplateLite' . DS . 'class.template.php';

class TemplateEngine {

    protected $_engine = NULL;

    protected $template_dir = APP_DIR . 'views'; // Should be defined by the context of request.
    protected $cache_dir    = APP_DIR . 'cache' . DS . 'views' . DS . 'cached';
    protected $compile_dir  = APP_DIR . 'cache' . DS . 'views' . DS . 'compiled';
    
    public function __construct($engine=NULL) {
        //parent::__construct();
        
        if ($engine !== NULL) {
            // Validate $engine is IEngine
            

            if ($engine instanceof IEngine) {
                // receive an already initialized engine instance (possibly useful for custom template enignes, per context)
                $this->_engine = $engine;
            } elseif (is_string($engine)) {
                // Load engine instance from class name
                $class = '\\' . $engine;
                $this->_engine = new $class();
            }

            // Setup defaults for engine?
            //  this will likely need to be removed


            // TODO, remove this, unless dev or debug
            //$this->_engine->setForceCompile(TRUE); //force_compile = true;
            // $this->_engine->compile_check = true;
            // $this->_engine->cache = false;
            // $this->_engine->cache_lifetime = 3600;
            // $this->_engine->config_overwrite = false;

            // $this->template_dir = APP_DIR . 'views'; // Should be defined by the context of request.
            // $this->cache_dir    = APP_DIR . 'cache' . DS . 'views' . DS . 'cached';
            // $this->compile_dir  = APP_DIR . 'cache' . DS . 'views' . DS . 'compiled';

            // $this->_engine->template_dir = $this->template_dir;
            // $this->_engine->cache_dir = $this->cache_dir;
            // $this->_engine->compile_dir = $this->compile_dir;
        }
    }

    public function setTheme($theme) {
        $this->_engine->setTemplateFolder(ROOT_DIR . 'themes' . DS . $theme);
    }

    public function assign($name, $val) {
        $this->_engine->assign($name, $val);
    }
    
    public function fetch($name, $cache_id=NULL, $display=FALSE) {
        return $this->_engine->fetch($name, $cache_id, $display);
    }
}
