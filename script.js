document.addEventListener('DOMContentLoaded', () => {
    const formContainer = document.getElementById('form-container');
    const toSignup = document.getElementById('to-signup');
    const toLogin = document.getElementById('to-login');

    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');

    // 1. Check if user is already logged in
    if (localStorage.getItem('currentUser')) {
        window.location.href = 'welcome.html';
    }

    // 2. Toggle between Forms
    toSignup.addEventListener('click', () => {
        formContainer.classList.add('show-signup');
        hideAlerts();
    });

    toLogin.addEventListener('click', () => {
        formContainer.classList.remove('show-signup');
        hideAlerts();
    });

    function hideAlerts() {
        document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
    }

    function showAlert(id, message, type) {
        const alertEl = document.getElementById(id);
        alertEl.textContent = message;
        alertEl.className = `alert alert-${type}`;
        alertEl.style.display = 'block';
    }

    // 3. Handle Sign Up
    signupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const username = document.getElementById('signup-username').value.trim();
        const password = document.getElementById('signup-password').value;

        let users = JSON.parse(localStorage.getItem('users')) || {};

        if (users[username]) {
            showAlert('signup-alert', 'Username already exists. Please login.', 'error');
        } else {
            users[username] = password;
            localStorage.setItem('users', JSON.stringify(users));
            showAlert('login-alert', 'Account created! You can now login.', 'success');
            formContainer.classList.remove('show-signup');
            signupForm.reset();
        }
    });

    // 4. Handle Login
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const username = document.getElementById('login-username').value.trim();
        const password = document.getElementById('login-password').value;

        let users = JSON.parse(localStorage.getItem('users')) || {};

        if (users[username] && users[username] === password) {
            localStorage.setItem('currentUser', username);
            window.location.href = 'welcome.html';
        } else {
            showAlert('login-alert', 'Invalid username or password.', 'error');
        }
    });
});