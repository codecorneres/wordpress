<?php
// Hide admin bar for non-admin users
add_action("after_setup_theme", "remove_admin_bar");
function remove_admin_bar() {
    if (!current_user_can("administrator") && !is_admin()):
        show_admin_bar(false);
    endif;
}

// Shortcode for login/logout toggle button
function login_toggle_shortcode() {
    if (is_user_logged_in()) :
        $logout_url = wp_logout_url(home_url());
        return '<a href="' . $logout_url . '" class="logout-btn">Logout</a>';
    else:
        $login_url = "/wordpress/login-2/";
        $signup_url = "/wordpress/sign-up/";

        return '<a href="' . $signup_url . '" class="signup">Sign Up</a>
                <a href="' . $login_url . '" class="login-btn">Login</a>';
    endif;
}

 add_shortcode("login_toggle", "login_toggle_shortcode");