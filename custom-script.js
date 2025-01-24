
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



function validateLoginForm() {
    let isValid = true;

    // Clear any previous error messages
    document.getElementById('log_error').textContent = '';
    document.getElementById('pwd_error').textContent = '';

    // Get input values
    const usernameOrEmail = document.getElementById('log').value.trim();
    const password = document.getElementById('pwd').value.trim();

    // Validate Username or Email field
    if (!usernameOrEmail) {
        document.getElementById('log_error').textContent = 'Username or Email is required.';
        isValid = false;
    }

    // Validate Password field
    if (!password) {
        document.getElementById('pwd_error').textContent = 'Password is required.';
        isValid = false;
    }

    return isValid;
}
