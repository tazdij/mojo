<?php

namespace Mojo\Template;

require_once dirname(__FILE__) . DS . 'class.template.php';

class TemplateEngine extends \Template_Lite {
    
    public function __construct() {
        parent::__construct();
        
        
        // TODO, remove this, unless dev or debug
        $this->force_compile = true;
        $this->compile_check = true;
        $this->cache = false;
        $this->cache_lifetime = 3600;
        $this->config_overwrite = false;
        
        $this->template_dir = APP_DIR . 'views';
        $this->cache_dir = APP_DIR . 'cache' . DS . 'views' . DS . 'cached';
        $this->compile_dir = APP_DIR . 'cache' . DS . 'views' . DS . 'compiled';
    }
    
    public function render() {
    
    }
}
