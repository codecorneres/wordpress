<?php
class Rest_API {
    public function __construct() {
        add_action('rest_api_init', [$this, 'custom_rest_login_endpoint']);
        add_action('rest_api_init', [$this, 'custom_rest_register_endpoint']);
    }

    // Login API
    public function custom_rest_login_endpoint() {
        register_rest_route('custom/', '/login', array(
            'methods'  => 'POST',
            'callback' => [$this, 'custom_rest_login'],
        ));
    }

    public function custom_rest_login($request) {
        $parameters = $request->get_json_params();
        $username   = $parameters['username'];
        $password   = $parameters['password'];

        if (empty($username) || empty($password)) :
            return new WP_Error('missing_fields', 'Username and password are required', array('status' => 400));
        endif;

        $user = get_user_by('login', $username);
        if (!$user) :
            return new WP_Error('invalid_user', 'User does not exist', array('status' => 404));
        endif;

        if (!wp_check_password($password, $user->user_pass, $user->ID)) :
            return new WP_Error('incorrect_password', 'Incorrect password.', array('status' => 403));
        endif;

        $account_status = get_user_meta($user->ID, 'account_status', true);
        if ($account_status === 'pending') :
            return new WP_Error('account_pending', 'Your account is pending approval. Please wait for activation.', array('status' => 403));
        endif;

        return rest_ensure_response(array(
            'user_id'  => $user->ID,
            'username' => $user->user_login,
            'email'    => $user->user_email,
            'data'   => array('status' => 200),

        ));
    }

    // Register API
    public function custom_rest_register_endpoint() {
        register_rest_route('custom/', '/register', array(
            'methods'  => 'POST',
            'callback' => [$this, 'custom_rest_register'],
        ));
    }

    public function custom_rest_register($request) {
        $parameters = $request->get_json_params();
        $username   = $parameters['username'];
        $email      = $parameters['email'];
        $password   = $parameters['password'];

        if (empty($username) || empty($email) || empty($password)) :
            return new WP_Error('missing_fields', 'Required fields are missing.', array('status' => 400));
        endif;

        if (username_exists($username) || email_exists($email)) :
            return new WP_Error('user_exists', 'Username or email already exists.',array('status' => 400));
        endif;

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) :
            return new WP_Error('registration_failed', 'User registration failed.', array('status' => 500));
        endif;

        return rest_ensure_response(array(
            'message' => 'User registered successfully',
            'user_id' => $user_id
        ));
    }
}

new Rest_API();