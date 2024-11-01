<?php
/*
  Plugin Name: Site Button by Extension Factory
  Plugin URI: http://builder.extensionfactory.com/wordpress/
  Description: The Extension Factory Site Reader brings your RSS feeds on any platform through browser extensions and mobile applications.
  Author: Slice Factory - Extension Factory
  Version: 1.0
  Author URI: http://slicefactory.com/
 */

require_once('extensionfactory-config.php');
add_action('plugins_loaded', 'loaded');

register_activation_hook(__FILE__, 'activated');

/**
 * This function is called when plugin is activated, its aim is to store embed code into a
 * text file that will be read on each page impression later.
 * @return <boolean>  true if everythong was fine, false otherwise
 */
function activated() {
    _store_embed_code();
    return true;
}

/**
 * This function is called on each page impression to inject embed code into the page
 * by calling sf_head_inject
 */
function loaded() {
    global $fields;
    global $ef_config;

    $fields = array('head', 'foot');

    define('EMBED_FILE', $ef_config['embed_file']);
    define('ERROR_LOG', $ef_config['error_log']);
    add_action('wp_footer', 'sf_inject');
}

/**
 * Inserts the embed code into the page
 * @global  $wp
 */
function sf_inject() {
    global $wp;

    $embed_code = _retrieve_embed_code();
    $output = $embed_code;
    echo $output;
}

/**
 *
 * @access private
 * @global <type> $wp
 * @return <string> embed_code for this site
 *
 */
function _store_embed_code() {
    // can not include config file here, dont ask me why
    global $ef_config;
    $embed_code = false;
    $error_msg = false;
    
    // Make a GET request to SF server to check that we can deliver a valid embed code
    if (!class_exists('WP_Http')) {
        include_once( ABSPATH . WPINC . '/class-http.php' );
    }
    $request = new WP_Http;

    if (!$ef_config or !array_key_exists('embed_url', $ef_config)) {
        $error_msg = 'Config info is empty!';        
    } else {
        $result = $request->request($ef_config['embed_url']);

        if ($result->errors) {
            $error_msg = 'Error accessing extensionfactory servers at ' . $ef_config['embed_url'];
            //$error_msg += '\n '.$result->errors;
            // die('Error accessing extensionfactory servers at ' . $ef_config['embed_url']);
        } elseif ((int) $result['response']['code'] == 200) {
            $embed_code = $result['body'];
            if (file_exists(EMBED_FILE)) {
                unlink(EMBED_FILE);
            }
            touch(EMBED_FILE);
            chmod(EMBED_FILE, 0644);
            $fp = fopen(EMBED_FILE, 'w');
            /*
             * use for debug:
             */
            //fwrite($fp, '<script>/* (C) Slice Factory - 2011 \n');
            //fwrite($fp, print_r($ef_config, true));
            //fwrite($fp, '*/</script>');
            fwrite($fp, $embed_code);
            fclose($fp);
            return true;
        } else {
            $error_msg = 'http respone error: ' . $result['response']['code'];
        }
    }
    // some debug info:
    $fp = fopen(ERROR_LOG, 'w');
    fwrite($fp, print_r($ef_config, true));
    fwrite($fp, $error_msg);
    fclose($fp);
    
    return false;
}

/**
 * This function makes a GET request on SF server to retrieve the right embed code
 * @return <type>
 */
function _retrieve_embed_code() {
    global $ef_config;
    $embed_code = '';
    if (file_exists(EMBED_FILE)) {
        $embed_code = @file_get_contents(EMBED_FILE); // it does happen.. so I suppress warning
        if (!$embed_code) {
            _store_embed_code();
        }
        else {
            // check file last modification time and retrieve new code if older than X hours, see settings
            $time_elapsed = time() - filemtime(EMBED_FILE);
            if ($time_elapsed > $ef_config['periodical_update'] ) {
                _store_embed_code();
            }
        }
    }
    else {
        _store_embed_code();
    }
    return $embed_code;
}
?>