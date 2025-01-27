<?php

add_action("wp_enqueue_scripts", "hello_elementor_child_style");
function hello_elementor_child_style()
{
    wp_enqueue_style(
        "parent-style",
        get_template_directory_uri() . "/style.css"
    );
    wp_enqueue_style(
        "child-style",
        get_stylesheet_directory_uri() . "/style.css",
        ["parent-style"]
    );
    wp_enqueue_script(
        "custom-script",
        get_stylesheet_directory_uri() . "/js/custom-script.js",
        [],
        rand(1, 100),
        true
    );
}

/**
 * Your code goes below.
*/
//include login and regitser form functionality
include get_stylesheet_directory() . '/includes/custom-login.php';
include get_stylesheet_directory() . '/includes/custom-register.php';

//include Hide admin bar for non-admin users and login/logout toggle button
include get_stylesheet_directory().'/includes/custom-functions.php';