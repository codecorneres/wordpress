<?php
class CustomUserRegistration {
    public function __construct() {
        add_shortcode("custom_user_registration", [$this, "render_registration_form"]);
        add_action("wp_ajax_nopriv_handle_registration", [$this, "handle_registration"]);
        add_filter('wp_authenticate_user', [$this, "check_user_account_status"], 10, 1);
        add_filter('manage_users_columns', [$this, "add_account_status_column"]);
        add_action('manage_users_custom_column', [$this, "show_account_status_column"], 10, 3);
        add_action('admin_init', [$this, "handle_account_status_action"]);
        add_action("wp_enqueue_scripts", [$this, "enqueue_scripts"]);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('custom-registration-script', get_stylesheet_directory_uri() . '/js/custom-registeration.js', ['jquery'], null, true);
        wp_localize_script('custom-registration-script', 'ajaxurl', admin_url('admin-ajax.php'));
    }

    public function render_registration_form() {
        if (is_user_logged_in()) :
            return "<p>You are already logged in.</p>";
        endif;
    include get_stylesheet_directory() . '/forms/custom-register-form.php';
    }

    public function render_input_field($name, $label, $type, $attributes = []) {
        $additional_attrs = '';
        foreach ($attributes as $key => $val) {
            $additional_attrs .= "$key={$val}";
        }
        ?>
<p>
    <label for="<?php echo $name; ?>"><?php echo $label; ?></label><span>*</span>
    <input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="input-field"
        <?php echo $additional_attrs; ?>>
    <span class="error-message" id="<?php echo $name . '_error'; ?>"></span>
</p>
<?php
    }

    public function handle_registration() {

        $email = $_POST["email"];
        $first_name = $_POST["first_name"];
        $last_name = $_POST["last_name"];
        $phone = $_POST["phone"];
        $address = $_POST["address"];

        if (empty($first_name) || empty($last_name) || empty($address) || !is_email($email) || !preg_match('/^\d{10}$/', $phone)
        ) :
            wp_send_json_error(['message' => 'All Fields Are Required']);
         endif;
        if(email_exists($email)):
            wp_send_json_error(['message'=>'Email is Already Exist.']);
        endif;
        

        $username = explode("@", $email)[0];
        $password = wp_generate_password(12, true);
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) :
            wp_send_json_error(['message' => "Error creating user: " . $user_id->get_error_message()]);
        endif;

        update_user_meta($user_id, "first_name", $first_name);
        update_user_meta($user_id, "last_name", $last_name);
        update_user_meta($user_id, "phone", $phone);
        update_user_meta($user_id, "address", $address);
        update_user_meta($user_id, "temporary_password", $password);
        update_user_meta($user_id, "account_status", "pending");

        $this->notify_admin($first_name, $last_name, $email);
        $this->notify_user($email, $username, $password);

        wp_send_json_success(['message' => "Registration successful! Your account is pending admin approval."]);
    }

    public function notify_admin($first_name, $last_name, $email) {
        $admin_email = get_option('admin_email');
        $subject = "New User Registration Pending Approval";
        $message = "A new user has registered and is awaiting approval.\n\nName: $first_name $last_name\nEmail: $email\n\nApprove or reject the user in the admin panel.";
        wp_mail($admin_email, $subject, $message);
    }

    public function notify_user($email, $username, $password) {
        $subject = "Your account is pending approval";
        $message = "Your account is pending approval. Please wait for the admin to approve your account.\n\nUsername: $username\nPassword: $password\n";
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