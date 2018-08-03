<?php

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
// API Seafile Content
function media_upload_tab_slug_content__iframe()
{
    include 'seawp.php';
}
?>
