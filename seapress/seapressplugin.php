<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// Seafile Tab in add Media

add_filter('media_upload_tabs', 'media_upload_tabs__tab_slug');

function media_upload_tabs__tab_slug($tabs)
{
    $newtab = array(
        'tab_slug' => ("Aus Seafile hinzufügen")
    );
    return array_merge($tabs, $newtab);
}

add_action('media_upload_tab_slug', 'media_upload_tab_slug__content');

function media_upload_tab_slug__content()
{
    wp_iframe('media_upload_tab_slug_content__iframe');
}

// API CONTENT OWN SESSION

function register_session(){
    if(!session_id()) session_start();
}
add_action('init','register_session');

// API Seafile Content

function media_upload_tab_slug_content__iframe()
{

    include 'seawp.php';
}

// Get Media Files

function load_wp_media_files() {
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'load_wp_media_files' );

//add custom js

function wptuts_scripts_basic()
{
    // Register the script like this for a plugin:
    wp_register_script( 'seapress-script', plugins_url( '/js/upload.js', __FILE__ ) );

    // For either a plugin or a theme, you can then enqueue the script:
    wp_enqueue_script( 'seapress-script' );
}
add_action( 'admin_enqueue_scripts', 'wptuts_scripts_basic' );







?>
