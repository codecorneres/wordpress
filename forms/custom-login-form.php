<h4 class="text-center">Login</h4>
<form id="custom-login-form" action="<?php echo site_url("wp-login.php", "login_post"); ?>" method="post">
    <?php $this->render_input_field('log', 'Username or Email', 'text'); ?>
    <span id="log_error" class="error-message"></span>

    <?php $this->render_input_field('pwd', 'Password', 'password'); ?>
    <span id="pwd_error" class="error-message"></span>

    <p>
        <input type="submit" name="wp-submit" value="Login">
        <input type="hidden" name="redirect_to" value="<?php echo home_url(); ?>">
    </p>
</form>