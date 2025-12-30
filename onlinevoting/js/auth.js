// ========================================
// Authentication Module - PHP Backend Integration
// ========================================

var API_BASE = 'backend';

const Auth = {
    // Register new user
    async register(userData) {
        try {
            const response = await fetch(`${API_BASE}/auth/register.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(userData)
            });

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Registration error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },

    // Login user
    async login(email, password) {
        try {
            const response = await fetch(`${API_BASE}/auth/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include', // Important for session cookies
                body: JSON.stringify({ email, password })
            });

            const result = await response.json();

            if (result.success) {
                // Store user data locally for quick access
                localStorage.setItem('current_user', JSON.stringify(result.user));
            }

            return result;
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, message: 'Network error. Please try again.' };
        }
    },

    // Logout user
    async logout() {
        try {
            const response = await fetch(`${API_BASE}/auth/logout.php`, {
                credentials: 'include'
            });

            const result = await response.json();

            // Clear local storage
            localStorage.removeItem('current_user');

            return result;
        } catch (error) {
            console.error('Logout error:', error);
            return { success: false, message: 'Logout failed.' };
        }
    },

    // Check authentication status
    async checkAuth() {
        try {
            const response = await fetch(`${API_BASE}/auth/check.php`, {
                credentials: 'include'
            });

            const result = await response.json();

            if (result.success && result.user) {
                localStorage.setItem('current_user', JSON.stringify(result.user));
                return result.user;
            } else {
                localStorage.removeItem('current_user');
                return null;
            }
        } catch (error) {
            console.error('Auth check error:', error);
            return null;
        }
    },

    // Get current logged-in user (from local storage)
    getCurrentUser() {
        const userData = localStorage.getItem('current_user');
        return userData ? JSON.parse(userData) : null;
    },

    // Check if user is authenticated
    isAuthenticated() {
        return this.getCurrentUser() !== null;
    },

    // Check if user is admin
    isAdmin() {
        const user = this.getCurrentUser();
        return user && user.role === 'admin';
    },

    // Protect page - redirect if not authenticated
    async protectPage(adminOnly = false) {
        const user = await this.checkAuth();

        if (!user) {
            window.location.href = 'login.html';
            return false;
        }

        if (adminOnly && user.role !== 'admin') {
            window.location.href = 'voter-dashboard.html';
            return false;
        }

        return true;
    }
};
