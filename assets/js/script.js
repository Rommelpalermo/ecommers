// E-Commerce Platform JavaScript

// Global variables
let cart = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize cart
    updateCartCount();
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize alerts auto-dismiss
    initializeAlerts();
}

function initializeEventListeners() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', handleAddToCart);
    });
    
    // Remove from cart buttons
    document.querySelectorAll('.remove-from-cart').forEach(button => {
        button.addEventListener('click', handleRemoveFromCart);
    });
    
    // Update quantity buttons
    document.querySelectorAll('.update-quantity').forEach(input => {
        input.addEventListener('change', handleUpdateQuantity);
    });
    
    // Search form
    const searchForm = document.querySelector('#search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', handleSearch);
    }
    
    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', handlePaymentMethodSelect);
    });
}

function initializeTooltips() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function initializeAlerts() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
}

// Cart functionality
function handleAddToCart(event) {
    event.preventDefault();
    
    const button = event.target.closest('.add-to-cart');
    const productId = button.dataset.productId;
    const quantity = button.dataset.quantity || 1;
    
    addToCart(productId, quantity);
}

function handleRemoveFromCart(event) {
    event.preventDefault();
    
    const button = event.target.closest('.remove-from-cart');
    const productId = button.dataset.productId;
    
    removeFromCart(productId);
}

function handleUpdateQuantity(event) {
    const input = event.target;
    const productId = input.dataset.productId;
    const quantity = parseInt(input.value);
    
    updateCartQuantity(productId, quantity);
}

function addToCart(productId, quantity = 1) {
    showLoading();
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            updateCartCount();
            showAlert(data.message || 'Product added to cart successfully!', 'success');
        } else {
            showAlert(data.message || 'Failed to add product to cart', 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('An error occurred while adding product to cart', 'danger');
        console.error('Error:', error);
    });
}

function removeFromCart(productId) {
    showLoading();
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            updateCartCount();
            location.reload(); // Reload to update cart display
        } else {
            showAlert(data.message || 'Failed to remove product from cart', 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('An error occurred while removing product from cart', 'danger');
        console.error('Error:', error);
    });
}

function updateCartQuantity(productId, quantity) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    showLoading();
    
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: 'update',
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            updateCartCount();
            updateCartTotal();
        } else {
            showAlert(data.message || 'Failed to update cart', 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        showAlert('An error occurred while updating cart', 'danger');
        console.error('Error:', error);
    });
}

function updateCartCount() {
    fetch('api/cart.php?action=count')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cart-count').textContent = data.count;
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
    });
}

function updateCartTotal() {
    fetch('api/cart.php?action=total')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const totalElement = document.getElementById('cart-total');
            if (totalElement) {
                totalElement.textContent = '₱' + data.total.toFixed(2);
            }
        }
    })
    .catch(error => {
        console.error('Error updating cart total:', error);
    });
}

// Search functionality
function handleSearch(event) {
    const form = event.target;
    const query = form.querySelector('input[name="q"]').value.trim();
    
    if (!query) {
        event.preventDefault();
        showAlert('Please enter a search term', 'warning');
    }
}

// Payment method selection
function handlePaymentMethodSelect(event) {
    const method = event.target.closest('.payment-method');
    
    // Remove active class from all methods
    document.querySelectorAll('.payment-method').forEach(m => {
        m.classList.remove('selected');
    });
    
    // Add active class to selected method
    method.classList.add('selected');
    
    // Update hidden input if exists
    const hiddenInput = document.querySelector('input[name="payment_method"]');
    if (hiddenInput) {
        hiddenInput.value = method.dataset.method;
    }
}

// Utility functions
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-dismiss after duration
    setTimeout(() => {
        const alerts = alertContainer.querySelectorAll('.alert');
        if (alerts.length > 0) {
            const bsAlert = new bootstrap.Alert(alerts[alerts.length - 1]);
            bsAlert.close();
        }
    }, duration);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1050';
    document.body.appendChild(container);
    return container;
}

function showLoading() {
    let loadingOverlay = document.getElementById('loading-overlay');
    
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loading-overlay';
        loadingOverlay.className = 'spinner-overlay';
        loadingOverlay.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }
    
    loadingOverlay.style.display = 'flex';
}

function hideLoading() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Form validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Image lazy loading
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Initialize lazy loading when DOM is ready
document.addEventListener('DOMContentLoaded', initializeLazyLoading);

// Export functions for use in other scripts
window.ECommerce = {
    addToCart,
    removeFromCart,
    updateCartQuantity,
    updateCartCount,
    showAlert,
    showLoading,
    hideLoading,
    formatCurrency,
    validateForm
};