<?php
// duplicate values, also defined in main file CAREFULL
$ef_config = array();
$lang = WPLANG ? WPLANG : 'en'; //wordpress defaults to English
$ef_config['embed_url'] = 'http://builder.extensionfactory.com/minisite/embed/wordpressplugin/' .
    '?url=http://' . $_SERVER['SERVER_NAME'] .
    '&lang=' . $lang .
    '&name=' . urlencode(get_option('blogname'));
$ef_config['embed_file'] = dirname(__FILE__) . '/embedcode.txt';
$ef_config['error_log'] =  dirname(__FILE__) . '/error.log';
$ef_config['periodical_update'] =  86400; // in seconds / 86400=24h
?>