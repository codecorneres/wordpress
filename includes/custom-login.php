<?php
class CustomUserLogin {
    public function __construct() {
       add_shortcode("custom_user_login", [$this, "render_login_form"]);
       // add_action("wp_login_failed", [$this, "handle_login_failed"]);
       add_action('wp_ajax_nopriv_custom_user_login', [$this, 'handle_ajax_login']);
       add_action('wp_ajax_custom_user_login', [$this, 'handle_ajax_login']);
       add_action('wp_enqueue_scripts',[$this,'wp_enqueue_login_script']);
       }
       
       
       public function wp_enqueue_login_script(){
           wp_enqueue_script('custom-login-script', get_stylesheet_directory_uri() . '/js/custom-login.js', ['jquery'], null, true);
           wp_localize_script('custom-login-script', 'ajaxurl', admin_url('admin-ajax.php'));
       }
       
       public function render_login_form() {
       if (is_user_logged_in()) :
       return "<p>You are already logged in.</p>";
       endif;
       
       $error_message = $this->get_login_error_message();
       //include custom login form 
       include get_stylesheet_directory() . '/forms/custom-login-form.php';
               return;
           }
       
           public function render_input_field($name, $label, $type) {
               ?>
<p>
    <label for="<?php echo $name; ?>"><?php echo $label; ?></label><span>*</span>
    <input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>"
        value="<?php echo isset($_POST[$name]) ? $_POST[$name] : ''; ?>" class="input-field">
    <span id="<?php echo $name . '_error'; ?>" class="error-message"></span>
</p>
<?php
        }
    
        public function get_login_error_message() {
            if (isset($_GET["login"])) :
                switch ($_GET["login"]) {
                    case "failed":
                        return '<p class="login-error text-danger">Invalid username or password.</p>';
                    case "pending":
                        return '<p class="login-error text-warning">Your account is pending approval. Please wait for the admin to approve your account.</p>';
                    case "incorrect_password":
                        return '<p class="login-error text-danger">Your password is incorrect.</p>';
                }
            endif;
            return "";
        }
    
    
        public function handle_ajax_login() {
            $username_or_email = $_POST['log'];
            $password = $_POST['pwd'];
            $redirect_to = $_POST['redirect_to'];
        
            if (is_email($username_or_email)) :
                $user = get_user_by('email', $username_or_email);
                if ($user) :
                    $username = $user->user_login;
                 else :
                    wp_send_json_error([
                        'errors' => ['log' => 'Invalid email address.'],
                    ]);
                endif;
             else :
                $username = $username_or_email;
             endif;
        
            $user = wp_signon([
                'user_login'    => $username,
                'user_password' => $password,
            ]);
        
            if (is_wp_error($user)) :
                $error_code = $user->get_error_code();
                $error_message = $user->get_error_message($error_code);
        
                $errors = [];
                if ($error_code === 'invalid_username') :
                    $errors['log'] = 'Invalid username or email.';
                endif;
                if ($error_code === 'incorrect_password') :
                    $errors['pwd'] = 'Incorrect password.';
                endif;
        
                wp_send_json_error([
                    'errors' => $errors,
                ]);
            endif;
        
            wp_send_json_success([
                'redirect_to' => home_url(),
            ]);
        }
    }
    
    new CustomUserLogin();