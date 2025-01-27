function validateLoginForm(e) {
    let isValid = true;
    document.getElementById('log_error').textContent = '';
    document.getElementById('pwd_error').textContent = '';

    const usernameOrEmail = document.getElementById('log').value.trim();
    const password = document.getElementById('pwd').value.trim();


    if (!usernameOrEmail && !password) {
        document.getElementById('log_error').textContent = 'Username or Email is required.';
        document.getElementById('pwd_error').textContent = 'Password is required.';
        isValid = false;
        e.preventDefault();
        return false;
    }

    if (!usernameOrEmail) {
        document.getElementById('log_error').textContent = 'Username or Email is required.';
        isValid = false;
    }
    if (!password) {
        document.getElementById('pwd_error').textContent = 'Password is required.';
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault();
    }

    return isValid;
}

document.getElementById('custom-login-form').addEventListener('submit', function (e) {
    validateLoginForm(e);
});

jQuery(document).ready(function (jQuery) {
    jQuery("#custom-login-form").on("submit", function (e) {
        e.preventDefault();

        const formData = {
            action: "custom_user_login", 
            log: jQuery("#log").val(),   
            pwd: jQuery("#pwd").val(),   
            redirect_to: jQuery("input[name='redirect_to']").val()
        };

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            data: formData,
            success: function (response) {
        
                if (response.success) {
                    window.location.href = response.data.redirect_to;
                } else {
                    if (response.data.errors) {
                        jQuery.each(response.data.errors, function (field, message) {
                            jQuery(`#${field}_error`).text(message);
                        });
                    } else {
                        alert(response.data.message || "An error occurred.");
                    }
                }
            },
            error: function () {
                alert("An unexpected error occurred. Please try again.");
            }
        });
    });

});






