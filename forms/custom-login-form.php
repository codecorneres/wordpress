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