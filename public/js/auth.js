// public/js/auth.js

document.addEventListener('DOMContentLoaded', () => {
    const loginFormContainer = document.getElementById('login-form');
    const signupFormContainer = document.getElementById('signup-form');
    const showSignupLink = document.getElementById('show-signup');
    const showLoginLink = document.getElementById('show-login');

    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    const loginMessage = document.getElementById('login-message');
    const signupMessage = document.getElementById('signup-message');

    // Define the base URL for your backend API
    // Adjust this based on your WampServer setup.
    // If your project is at C:\wamp64\www\medical-health-records, then:
    const API_BASE_URL = 'http://localhost/medical-health-records/backend/public';

    // Function to show a message (for success/error)
    const showMessage = (element, message, type) => {
        element.textContent = message;
        element.className = `mt-4 text-center text-sm font-medium ${type === 'success' ? 'text-green-600' : 'text-red-600'}`;
        setTimeout(() => {
            element.textContent = ''; // Clear message after some time
        }, 5000);
    };

    // --- Form Toggling ---
    showSignupLink.addEventListener('click', (e) => {
        e.preventDefault();
        loginFormContainer.classList.add('hidden');
        signupFormContainer.classList.remove('hidden');
        loginMessage.textContent = ''; // Clear any previous messages
    });

    showLoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        signupFormContainer.classList.add('hidden');
        loginFormContainer.classList.remove('hidden');
        signupMessage.textContent = ''; // Clear any previous messages
    });

    // --- Login Form Submission Handler ---
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;

        // --- Client-side validation (basic) ---
        if (!email || !password) {
            showMessage(loginMessage, 'Please fill in all fields.', 'error');
            return;
        }

        loginMessage.textContent = 'Logging in...';
        loginMessage.className = 'mt-4 text-center text-sm font-medium text-gray-500';

        try {
            const response = await fetch(`${API_BASE_URL}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });

            const result = await response.json();

            if (response.ok) { // Check if HTTP status code is 2xx
                // Login successful
                sessionStorage.setItem('user', JSON.stringify(result.user));
                showMessage(loginMessage, result.message, 'success');

                // Redirect based on department/admin status
                if (result.user.isAdmin) {
                    window.location.href = 'admin-home.html';
                } else {
                    window.location.href = 'department-page.html';
                }
            } else {
                // Login failed (e.g., 401 Unauthorized, 400 Bad Request)
                showMessage(loginMessage, result.message || 'Login failed. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            showMessage(loginMessage, 'An unexpected error occurred during login. Please try again.', 'error');
        }
    });

    // --- Signup Form Submission Handler ---
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault(); // Prevent default form submission

        const firstName = document.getElementById('signup-first-name').value.trim();
        const middleName = document.getElementById('signup-middle-name').value.trim();
        const lastName = document.getElementById('signup-last-name').value.trim();
        const employeeId = document.getElementById('signup-employee-id').value.trim();
        const email = document.getElementById('signup-email').value.trim();
        const department = document.getElementById('signup-department').value;
        const password = document.getElementById('signup-password').value;
        const confirmPassword = document.getElementById('signup-confirm-password').value;

        // --- Client-side validation ---
        if (!firstName || !lastName || !employeeId || !email || !department || !password || !confirmPassword) {
            showMessage(signupMessage, 'Please fill in all required fields.', 'error');
            return;
        }

        if (password.length < 6) {
            showMessage(signupMessage, 'Password must be at least 6 characters long.', 'error');
            return;
        }

        if (password !== confirmPassword) {
            showMessage(signupMessage, 'Passwords do not match.', 'error');
            return;
        }

        signupMessage.textContent = 'Registering...';
        signupMessage.className = 'mt-4 text-center text-sm font-medium text-gray-500';

        try {
            const response = await fetch(`${API_BASE_URL}/auth/signup`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    firstName,
                    middleName,
                    lastName,
                    employeeId,
                    email,
                    department,
                    password,
                    confirmPassword
                })
            });

            const result = await response.json();

            if (response.ok) { // Check if HTTP status code is 2xx
                showMessage(signupMessage, result.message, 'success');
                signupForm.reset();
                setTimeout(() => {
                    signupFormContainer.classList.add('hidden');
                    loginFormContainer.classList.remove('hidden');
                }, 2000); // Give user time to read success message
            } else {
                showMessage(signupMessage, result.message || 'Registration failed. Please try again.', 'error');
            }
        } catch (error) {
            console.error('Signup error:', error);
            showMessage(signupMessage, 'An unexpected error occurred during signup. Please try again.', 'error');
        }
    });
});

// Global logout function
function logout() {
    sessionStorage.removeItem('user'); // Clear user session
    window.location.href = 'index.html'; // Redirect to login page
}
