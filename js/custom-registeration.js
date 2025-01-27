

document.getElementById('custom-registration-form').addEventListener('submit', function(event) {
    let isValid = true;
    const fields = [
        { id: 'first_name', error: 'First Name is required.', regex: null },
        { id: 'last_name', error: 'Last Name is required.', regex: null },
        { id: 'phone', error: 'Phone Number must be 10 digits.', regex: /^\d{10}$/ },
        { id: 'address', error: 'Address is required.', regex: null },
        { id: 'email', error: 'Please enter a valid email address.', regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ }
    ];

    document.querySelectorAll('.error-message').forEach(span => span.textContent = '');

    fields.forEach(field => {
        const value = document.getElementById(field.id).value.trim();
        if (!value || (field.regex && !field.regex.test(value))) {
            document.getElementById(`${field.id}_error`).textContent = field.error;
            isValid = false;
        }
    });

    if (!isValid) event.preventDefault();
});


['first_name', 'last_name', 'phone', 'address', 'email'].forEach(id => {
    document.getElementById(id).addEventListener('input', function() {
        document.getElementById(`${id}_error`).textContent = '';
    });
});

jQuery(document).ready(function (jQuery) {
    jQuery("#custom-registration-form").on("submit", function (e) {
        e.preventDefault();

        let formData = {
            action: "handle_registration",
        };

        jQuery(this).serializeArray().forEach((field) => {
            formData[field.name] = field.value;
        });

        jQuery.ajax({
            url: ajaxurl, 
            method: "POST", 
            data: formData, 
            dataType: "json",
            success: function (response) {
                let messageBox = jQuery("#registration-response");
                messageBox.removeClass().addClass(response.success ? "success" : "error");
                messageBox.text(response.data.message);
            },
            error: function ( textStatus, errorThrown) {
                let messageBox = jQuery("#registration-response");
                messageBox.removeClass().addClass("error");
                messageBox.text("An error occurred: " + textStatus + ". Please try again.");
                console.error("Error details:", errorThrown);
            },
        });
    });
});


