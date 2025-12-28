/**
 * ================================================
 * MAIN JAVASCRIPT FILE
 * Home Service Booking Platform
 * ================================================
 */

// ==================== SESSION MANAGEMENT ====================

/**
 * Session Manager - Handles login state using localStorage
 */
const SessionManager = {
    
    // Storage keys
    STORAGE_KEY: 'homeserve_user',
    
    /**
     * Save user session to localStorage
     */
    saveSession: function(userData) {
        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(userData));
    },
    
    /**
     * Get current session from localStorage
     */
    getSession: function() {
        const data = localStorage.getItem(this.STORAGE_KEY);
        return data ? JSON.parse(data) : null;
    },
    
    /**
     * Clear session from localStorage
     */
    clearSession: function() {
        localStorage.removeItem(this.STORAGE_KEY);
    },
    
    /**
     * Check if user is logged in
     */
    isLoggedIn: function() {
        return this.getSession() !== null;
    },
    
    /**
     * Check if user is a customer
     */
    isUser: function() {
        const session = this.getSession();
        return session && session.type === 'user';
    },
    
    /**
     * Check if user is a provider
     */
    isProvider: function() {
        const session = this.getSession();
        return session && session.type === 'provider';
    },
    
    /**
     * Get user name
     */
    getUserName: function() {
        const session = this.getSession();
        return session ? session.name : '';
    },
    
    /**
     * Get user type
     */
    getUserType: function() {
        const session = this.getSession();
        return session ? session.type : null;
    }
};

// ==================== NAVIGATION UPDATE ====================

/**
 * Update navigation based on login state
 */
function updateNavigation() {
    const navButtons = document.getElementById('navButtons');
    
    if (!navButtons) return;
    
    if (SessionManager.isLoggedIn()) {
        const userName = SessionManager.getUserName();
        const userType = SessionManager.getUserType();
        const dashboardUrl = userType === 'provider' ? 'provider-dashboard.html' : 'dashboard.html';
        
        navButtons.innerHTML = `
            <span style="margin-right: 15px; color: #374151;">Hi, ${escapeHtml(userName)}</span>
            <a href="${dashboardUrl}" class="btn btn-outline">Dashboard</a>
            <a href="#" onclick="logout(); return false;" class="btn btn-primary">Logout</a>
        `;
    } else {
        navButtons.innerHTML = `
            <a href="login.html" class="btn btn-outline">Login</a>
            <a href="register.html" class="btn btn-primary">Sign Up</a>
        `;
    }
}

/**
 * Protect page - redirect if not logged in
 */
function protectPage(requiredType = null) {
    if (!SessionManager.isLoggedIn()) {
        window.location.href = 'login.html';
        return false;
    }
    
    if (requiredType === 'user' && !SessionManager.isUser()) {
        window.location.href = 'login.html';
        return false;
    }
    
    if (requiredType === 'provider' && !SessionManager.isProvider()) {
        window.location.href = 'login.html';
        return false;
    }
    
    return true;
}

/**
 * Redirect if already logged in (for login/register pages)
 */
function redirectIfLoggedIn() {
    if (SessionManager.isLoggedIn()) {
        const userType = SessionManager.getUserType();
        if (userType === 'provider') {
            window.location.href = 'provider-dashboard.html';
        } else {
            window.location.href = 'dashboard.html';
        }
        return true;
    }
    return false;
}

/**
 * Logout function
 */
function logout() {
    // Clear localStorage
    SessionManager.clearSession();
    
    // Call server to destroy PHP session
    fetch('php/logout.php')
        .then(() => {
            window.location.href = 'index.html';
        })
        .catch(() => {
            window.location.href = 'index.html';
        });
}

// ==================== UTILITY FUNCTIONS ====================

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Generate star rating HTML
 */
function generateStarRating(rating) {
    let stars = '';
    rating = parseFloat(rating) || 0;
    
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            stars += '<i class="fas fa-star"></i>';
        } else if (i - rating < 1 && i - rating > 0) {
            stars += '<i class="fas fa-star-half-alt"></i>';
        } else {
            stars += '<i class="far fa-star"></i>';
        }
    }
    
    return stars;
}

/**
 * Show alert message
 */
function showAlert(type, message, containerId = 'alertContainer') {
    const container = document.getElementById(containerId);
    
    if (container) {
        container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            container.innerHTML = '';
        }, 5000);
    }
}

/**
 * Format date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// ==================== DATA LOADING FUNCTIONS ====================

/**
 * Load categories from JSON
 */
function loadCategoriesFromJSON() {
    const container = document.getElementById('categoriesGrid');
    if (!container) return;
    
    fetch('data/services.json')
        .then(response => response.json())
        .then(data => {
            displayCategories(data.services);
        })
        .catch(error => {
            console.error('Error loading categories:', error);
            container.innerHTML = '<p style="text-align: center; color: red;">Error loading categories</p>';
        });
}

/**
 * Display categories in grid
 */
function displayCategories(categories) {
    const container = document.getElementById('categoriesGrid');
    if (!container) return;
    
    let html = '';
    
    categories.forEach(category => {
        html += `
            <div class="category-card">
                <div class="category-card-inner" onclick="window.location.href='services.html?category=${category.id}'" style="cursor: pointer;">
                    <div class="category-icon">
                        <i class="fas ${category.icon}"></i>
                    </div>
                    <h3>${category.name}</h3>
                    <p>${category.description}</p>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Load providers from server
 */
function loadProviders(categoryId = null) {
    const container = document.getElementById('providersGrid');
    if (!container) return;
    
    let url = 'php/get-providers.php';
    if (categoryId) {
        url += '?category=' + categoryId;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                displayProviders(data.data);
            } else {
                showNoResults();
            }
        })
        .catch(error => {
            console.error('Error loading providers:', error);
            showNoResults();
        });
}

/**
 * Display providers in grid
 */
function displayProviders(providers) {
    const container = document.getElementById('providersGrid');
    const noResults = document.getElementById('noResults');
    
    if (!container) return;
    
    if (providers.length === 0) {
        container.innerHTML = '';
        if (noResults) noResults.style.display = 'block';
        return;
    }
    
    if (noResults) noResults.style.display = 'none';
    
    let html = '';
    
    providers.forEach(provider => {
        const stars = generateStarRating(provider.rating);
        
        html += `
            <div class="provider-card">
                <div class="provider-card-inner">
                    <div class="provider-image">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="provider-info">
                        <h4>${escapeHtml(provider.full_name)}</h4>
                        <p class="provider-category">${escapeHtml(provider.category_name || 'Service Provider')}</p>
                        <div class="provider-rating">
                            ${stars} (${provider.rating || '0.0'})
                        </div>
                        <p class="provider-rate">$${provider.hourly_rate}/hour</p>
                        <a href="booking.html?provider=${provider.provider_id}" class="btn btn-primary btn-block" style="margin-top: 10px;">Book Now</a>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

/**
 * Show no results message
 */
function showNoResults() {
    const container = document.getElementById('providersGrid');
    const noResults = document.getElementById('noResults');
    
    if (container) container.innerHTML = '';
    if (noResults) noResults.style.display = 'block';
}

// ==================== INITIALIZE ====================

/**
 * Initialize on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Update navigation on all pages
    updateNavigation();
    
    // Check current page and initialize accordingly
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    
    switch(currentPage) {
        case 'index.html':
        case '':
            loadCategoriesFromJSON();
            loadProviders();
            break;
            
        case 'login.html':
        case 'register.html':
            redirectIfLoggedIn();
            break;
            
        case 'dashboard.html':
            if (protectPage('user')) {
                initDashboard();
            }
            break;
            
        case 'provider-dashboard.html':
            if (protectPage('provider')) {
                initProviderDashboard();
            }
            break;
            
        case 'booking.html':
            if (protectPage('user')) {
                initBookingPage();
            }
            break;
            
        case 'services.html':
            initServicesPage();
            break;
    }
    
    // Handle search form on homepage
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const category = document.getElementById('serviceCategory')?.value;
            if (category) {
                window.location.href = 'services.html?category=' + category;
            } else {
                window.location.href = 'services.html';
            }
        });
    }
});

// Make functions globally available
window.logout = logout;
window.SessionManager = SessionManager;
window.showAlert = showAlert;
window.escapeHtml = escapeHtml;
window.generateStarRating = generateStarRating;
window.formatDate = formatDate;
window.updateNavigation = updateNavigation;
window.protectPage = protectPage;
window.loadProviders = loadProviders;
window.displayProviders = displayProviders;