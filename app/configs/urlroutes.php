<?php


return array(
    array(
        'title' => 'Product URLs',
        'pattern' => '/p/(?<slug>[a-zA-Z0-9\-]+)/?',
        'type' => 'entity',
        'entity_type' => 'Product',
        'entity_query' => 'SELECT ProductEntity WHERE slug = :slug',
        'template' => 'ProductView',
        'controller' => 'Centro\\Controllers\\EntityController',
        'controller_action' => 'index'
    ),
    array(
        'title' => 'Asset Download',
        'pattern' => '/media/(?<filename>[a-zA-Z0-9\-]+(?:\.[a-zA-Z0-9\-]+)+)',
        'type' => 'asset',
        'asset_filename' => 'filename',
        'action' => 'countDownload'
    ),
    
);
