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