<?php

namespace Ext\Centro\System\SiteTree;

/**
 * Node
 * the Node is the base class of the SiteTree Node types, which will be loaded during the routing
 * Centro provides, via the SiteTree node graph. The NodeController will receive the `req`, and
 * using the request data, it will find the sitetree entry, matching the 
 */

class Node {

    protected $_node = NULL;

    public function __construct($node=NULL) {
        $this->_node = $node;
    }
    
    public function render() {
    
    }

}
