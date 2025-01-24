<?php
class CustomUserLogin {
public function __construct() {
add_shortcode("custom_user_login", [$this, "render_login_form"]);
add_action("wp_login_failed", [$this, "handle_login_failed"]);
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

    public function handle_login_failed($username) {
        $referrer = wp_get_referer();
        $user = get_user_by("login", $username);
        if ($user) :
            if (!wp_check_password($_POST['pwd'], $user->user_pass, $user->ID)) :
                wp_redirect(add_query_arg("login", "incorrect_password", $referrer));
                exit();
            endif;

            $account_status = get_user_meta($user->ID, "account_status", true);
            if ($account_status === "pending") :
                wp_redirect(add_query_arg("login", "pending", $referrer));
                exit();
            endif;
        endif;

        wp_redirect(add_query_arg("login", "failed", $referrer));
        exit();
    }
}

new CustomUserLogin();