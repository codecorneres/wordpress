<?php


class CustomUserRegistration {
    public function __construct() {
        add_shortcode("custom_user_registration", [$this, "render_registration_form"]);
        add_action("init", [$this, "handle_registration"]);
        add_filter('wp_authenticate_user', [$this, "check_user_account_status"], 10, 1);
        add_filter('manage_users_columns', [$this, "add_account_status_column"]);
        add_action('manage_users_custom_column', [$this, "show_account_status_column"], 10, 3);
        add_action('admin_init', [$this, "handle_account_status_action"]);
    }

    public function render_registration_form() {
        if (is_user_logged_in()) :
            return "<p>You are already logged in.</p>";
        endif;
        ?>
<form id="custom-registration-form" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="post">
    <h4 class="text-center">Sign Up</h4>

    <?php if (!empty($_SESSION["custom_registration_message"])): ?>
    <p class="<?php echo $_SESSION["custom_registration_message_type"]; ?>">
        <?php echo $_SESSION["custom_registration_message"]; ?>
    </p>
    <?php unset($_SESSION["custom_registration_message"], $_SESSION["custom_registration_message_type"]); ?>
    <?php endif; ?>

    <?php $this->render_input_field('first_name', 'First Name', 'text'); ?>
    <?php $this->render_input_field('last_name', 'Last Name', 'text'); ?>
    <?php $this->render_input_field('phone', 'Phone Number', 'tel', ['maxlength' => 10]); ?>
    <?php $this->render_input_field('address', 'Address', 'text'); ?>
    <?php $this->render_input_field('email', 'Email', 'email'); ?>

    <p>
        <input type="submit" name="submit_registration" value="Register">
    </p>
</form>
<?php
        return;
    }

    public function render_input_field($name, $label, $type, $attributes = []) {
        $value = isset($_POST[$name]) ? $_POST[$name] : '';
        $additional_attrs = '';
        foreach ($attributes as $key => $val) {
            $additional_attrs .= "$key={$val}";
        }
        ?>
<p>
    <label for="<?php echo $name; ?>"><?php echo $label; ?></label><span>*</span>
    <input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>"
        value="<?php echo $value; ?>" class="input-field" <?php echo $additional_attrs; ?>>
    <span class="error-message" id="<?php echo $name . '_error'; ?>"></span>
</p>
<?php
    }

    public function handle_registration() {
        if (!isset($_POST["submit_registration"])) {
            return;
        }

        session_start();
        $email = $_POST["email"];
        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $phone = $_POST["phone"];
        $address = $_POST["address"];

        if (!is_email($email)) :
            $this->set_session_message("Invalid email address.", "error text-danger");
            return;
        endif;

        if (email_exists($email)) :
            $this->set_session_message("Email already exists.", "error text-danger");
            return;
        endif;

        if (!preg_match('/^\d{10}$/', $phone)) :
            $this->set_session_message("Invalid phone number. Please enter a 10-digit phone number.", "error text-danger");
            return;
        endif;

        $username = explode("@", $email)[0];
        $password = wp_generate_password(12, true);
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) :
            $this->set_session_message("Error creating user: " . $user_id->get_error_message(), "error text-danger");
            return;
        endif;

        update_user_meta($user_id, "first_name", $first_name);
        update_user_meta($user_id, "last_name", $last_name);
        update_user_meta($user_id, "phone", $phone);
        update_user_meta($user_id, "address", $address);
        update_user_meta($user_id, "temporary_password", $password);
        update_user_meta($user_id, "account_status", "pending");

        $this->notify_admin($first_name, $last_name, $email);
        $this->notify_user($email, $username, $password);

        $this->set_session_message("Registration successful! Your account is pending approval by the admin.", "success text-success");
    }

    public function set_session_message($message, $type) {
        $_SESSION["custom_registration_message"] = $message;
        $_SESSION["custom_registration_message_type"] = $type;
    }

    public function notify_admin($first_name, $last_name, $email) {
        $admin_email = get_option('admin_email');
        $subject = "New User Registration Pending Approval";
        $message = "A new user has registered and is awaiting approval.\n\n";
        $message .= "Name: $first_name $last_name\n";
        $message .= "Email: $email\n\n";
        $message .= "Approve or reject the user in the admin panel.";
        wp_mail($admin_email, $subject, $message);
    }

    public function notify_user($email, $username, $password) {
        $subject = "Your account is pending approval";
        $message = "Your account is pending approval. Please wait for the admin to approve your account.\n\n";
        $message .= "Username: $username\n";
        $message .= "Password: $password\n";
        wp_mail($email, $subject, $message);
    }

    public function check_user_account_status($user) {
        if (is_wp_error($user)) :
            return $user;
        endif;
        $account_status = get_user_meta($user->ID, 'account_status', true);
        if ($account_status === 'pending') :
            return new WP_Error('account_pending', __('Your account is pending approval. Please wait for the admin to approve your account.'));
        endif;
        return $user;
    }

    public function add_account_status_column($columns) {
        $columns['account_status'] = 'Account Status';
        return $columns;
    }

    public function show_account_status_column($value, $column_name, $user_id) {
        if ($column_name !== 'account_status') :
            return $value;
        endif;

        $account_status = get_user_meta($user_id, 'account_status', true);
        $statuses = ['pending' => 'Pending', 'approved' => 'Approved'];
        $current_status = isset($statuses[$account_status]) ? $statuses[$account_status] : 'Unknown';

            if ($account_status === 'pending') :
                return $current_status . 
                    "<br><a href='" . admin_url("users.php?action=approve&user_id=$user_id") . "'>Approve</a>";
            elseif ($account_status === 'approved') :
                return $current_status . 
                    "<br><a href='" . admin_url("users.php?action=unapprove&user_id=$user_id") . "'>Unapprove</a>";
            endif;

        

        return $current_status;
    }

    public function handle_account_status_action() {
        if (!isset($_GET['action'], $_GET['user_id'])) :
            return;
        endif;

        $action = $_GET['action'];
        $user_id = $_GET['user_id'];
        $user_info = get_userdata($user_id);

        if (!$user_info) :
            return;
        endif;

        $email = $user_info->user_email;
        $username = $user_info->user_login;

        if ($action === 'approve') :
            update_user_meta($user_id, 'account_status', 'approved');
            $password = get_user_meta($user_id, "temporary_password", true);
            wp_mail($email, "Your Account is Approved", "Your account has been approved.\n\nUsername: $username\nPassword: $password");
        elseif ($action === 'unapprove') :
            update_user_meta($user_id, 'account_status', 'pending');
            wp_mail($email, "Your Account is Unapproved", "Your account status has been changed to 'Pending'.");
        endif;
        

        wp_redirect(admin_url('users.php'));
        exit;
    }
}

new CustomUserRegistration();

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
        ?>
<h4 class="text-center">Login</h4>
<?php if ($error_message) { echo $error_message; } ?>
<form id="custom-login-form" action="<?php echo site_url("wp-login.php", "login_post"); ?>" method="post">
    <?php $this->render_input_field('log', 'Username or Email', 'text'); ?>
    <?php $this->render_input_field('pwd', 'Password', 'password'); ?>
    <p>
        <input type="submit" name="wp-submit" value="Login">
        <input type="hidden" name="redirect_to" value="<?php echo home_url(); ?>">
    </p>
</form>
<?php
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