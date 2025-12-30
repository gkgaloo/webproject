// ========================================
// Main JavaScript - Page Initialization
// ========================================

// Utility function to show alerts
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;

    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);

    setTimeout(() => {
        alertDiv.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => alertDiv.remove(), 300);
    }, 3000);
}

// Utility function to show form errors
function showFormError(inputElement, message) {
    const errorElement = inputElement.parentElement.querySelector('.form-error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
        inputElement.style.borderColor = '#f87171';
    }
}

// Utility function to clear form errors
function clearFormError(inputElement) {
    const errorElement = inputElement.parentElement.querySelector('.form-error');
    if (errorElement) {
        errorElement.classList.remove('show');
        inputElement.style.borderColor = '';
    }
}

// Utility function to clear all form errors
function clearAllFormErrors(form) {
    form.querySelectorAll('.form-error').forEach(error => error.classList.remove('show'));
    form.querySelectorAll('.form-input').forEach(input => input.style.borderColor = '');
}

// Add navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// Update user menu in navbar
function updateNavbar() {
    const user = Auth.getCurrentUser();
    const navbarMenu = document.querySelector('.navbar-menu');

    if (!navbarMenu) return;

    // Clear existing auth links
    navbarMenu.querySelectorAll('.auth-link').forEach(link => link.remove());

    if (user) {
        // User is logged in
        const userItem = document.createElement('li');
        userItem.className = 'auth-link';
        userItem.innerHTML = `<a href="${user.role === 'admin' ? 'admin-dashboard.html' : 'voter-dashboard.html'}">${user.name}</a>`;
        navbarMenu.appendChild(userItem);

        const logoutItem = document.createElement('li');
        logoutItem.className = 'auth-link';
        logoutItem.innerHTML = `<a href="#" id="logoutBtn">Logout</a>`;
        navbarMenu.appendChild(logoutItem);

        // Add logout handler
        document.getElementById('logoutBtn').addEventListener('click', (e) => {
            e.preventDefault();
            Auth.logout();
            window.location.href = 'index.html';
        });
    } else {
        // User is not logged in
        const loginItem = document.createElement('li');
        loginItem.className = 'auth-link';
        loginItem.innerHTML = `<a href="login.html">Login</a>`;
        navbarMenu.appendChild(loginItem);

        const registerItem = document.createElement('li');
        registerItem.className = 'auth-link';
        registerItem.innerHTML = `<a href="register.html">Register</a>`;
        navbarMenu.appendChild(registerItem);
    }
}

// Initialize navbar on page load
document.addEventListener('DOMContentLoaded', () => {
    updateNavbar();
});

// Format date helper
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Countdown timer helper
function startCountdown(endDate, elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const end = new Date(endDate).getTime();

    const timer = setInterval(() => {
        const now = new Date().getTime();
        const distance = end - now;

        if (distance < 0) {
            clearInterval(timer);
            element.textContent = 'Voting has ended';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        element.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
    }, 1000);
}
