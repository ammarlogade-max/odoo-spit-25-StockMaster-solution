// ========================================
// GLOBAL CONFIGURATION
// ========================================
const API_BASE_URL = 'http://localhost/odoo-spit-25-stockmaster/backend/api';
let currentUser = null;
let productsData = [];

// ========================================
// UTILITY FUNCTIONS
// ========================================
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    toast.innerHTML = `
        <span class="toast-icon">${icons[type]}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
    `;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// ========================================
// AUTHENTICATION
// ========================================
async function login(username, password) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php?action=login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentUser = data.data.user;
            localStorage.setItem('stockmaster_user', JSON.stringify(currentUser));
            showToast('Login successful!', 'success');
            window.location.href = 'index.html';
        } else {
            showToast(data.message || 'Login failed', 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showToast('Connection error. Please check if XAMPP is running.', 'error');
    }
}

async function signup(username, email, password, confirmPassword) {
    if (password !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php?action=signup`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Account created successfully! Redirecting to login...', 'success');
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 2000);
        } else {
            showToast(data.message || 'Signup failed', 'error');
        }
    } catch (error) {
        console.error('Signup error:', error);
        showToast('Connection error. Please try again.', 'error');
    }
}

function logout() {
    localStorage.removeItem('stockmaster_user');
    currentUser = null;
    showToast('Logged out successfully', 'success');
    window.location.href = 'login.html';
}

function checkAuth() {
    const user = localStorage.getItem('stockmaster_user');
    
    if (!user && !window.location.pathname.includes('login') && !window.location.pathname.includes('signup')) {
        window.location.href = 'login.html';
        return false;
    }
    
    if (user) {
        currentUser = JSON.parse(user);
        return true;
    }
    
    return false;
}

// ========================================
// DASHBOARD FUNCTIONS
// ========================================
async function loadDashboard() {
    try {
        const response = await fetch(`${API_BASE_URL}/dashboard.php`);
        const data = await response.json();
        
        if (data.success) {
            const kpis = data.data;
            
            document.getElementById('total-products').textContent = kpis.total_products || 0;
            document.getElementById('low-stock').textContent = kpis.low_stock_count || 0;
            document.getElementById('stock-value').textContent = formatCurrency(kpis.total_value || 0);
            document.getElementById('today-products').textContent = kpis.today_products || 0;
            
            const lowStockBadge = document.getElementById('low-stock-badge');
            if (lowStockBadge) {
                if (kpis.low_stock_count > 0) {
                    lowStockBadge.textContent = kpis.low_stock_count;
                    lowStockBadge.style.display = 'block';
                } else {
                    lowStockBadge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Dashboard load error:', error);
        showToast('Failed to load dashboard data', 'error');
    }
}

// ========================================
// PRODUCT FUNCTIONS
// ========================================
async function loadProducts() {
    try {
        const response = await fetch(`${API_BASE_URL}/products.php`);
        const data = await response.json();
        
        if (data.success) {
            productsData = data.data;
            renderProductsTable(productsData);
        }
    } catch (error) {
        console.error('Products load error:', error);
        showToast('Failed to load products', 'error');
    }
}

function renderProductsTable(products) {
    const tbody = document.getElementById('products-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:2rem;">No products found</td></tr>';
        return;
    }
    
    products.forEach(product => {
        const row = document.createElement('tr');
        
        const isLowStock = parseInt(product.quantity) <= parseInt(product.low_stock_threshold);
        const statusBadge = isLowStock 
            ? '<span class="badge badge-danger">Low Stock</span>' 
            : '<span class="badge badge-success">In Stock</span>';
        
        row.innerHTML = `
            <td>${product.id}</td>
            <td><strong>${product.name}</strong></td>
            <td><code>${product.sku}</code></td>
            <td>${product.category}</td>
            <td>${product.quantity}</td>
            <td>${formatCurrency(product.price)}</td>
            <td>${statusBadge}</td>
            <td class="table-actions">
                <button class="icon-btn edit" onclick="editProduct(${product.id})" title="Edit">✏️</button>
                <button class="icon-btn delete" onclick="deleteProduct(${product.id})" title="Delete">🗑️</button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

async function deleteProduct(id) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/products.php?id=${id}`, {
            method: 'DELETE'
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Product deleted successfully!', 'success');
            loadProducts();
            loadDashboard();
        } else {
            showToast(data.message || 'Failed to delete product', 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast('Failed to delete product', 'error');
    }
}

// ========================================
// RECEIPTS FUNCTIONS
// ========================================
async function loadReceipts() {
    try {
        const response = await fetch(`${API_BASE_URL}/receipts.php`);
        const data = await response.json();
        
        if (data.success) {
            renderReceiptsTable(data.data);
        }
    } catch (error) {
        console.error('Receipts load error:', error);
        showToast('Failed to load receipts', 'error');
    }
}

function renderReceiptsTable(receipts) {
    const tbody = document.getElementById('receipts-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (receipts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;">No receipts found</td></tr>';
        return;
    }
    
    receipts.forEach(receipt => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${receipt.id}</td>
            <td><strong>${receipt.product_name}</strong></td>
            <td>${receipt.quantity}</td>
            <td>${receipt.reference || '-'}</td>
            <td>${formatDate(receipt.created_at)}</td>
        `;
        tbody.appendChild(row);
    });
}

// ========================================
// DELIVERIES FUNCTIONS
// ========================================
async function loadDeliveries() {
    try {
        const response = await fetch(`${API_BASE_URL}/deliveries.php`);
        const data = await response.json();
        
        if (data.success) {
            renderDeliveriesTable(data.data);
        }
    } catch (error) {
        console.error('Deliveries load error:', error);
        showToast('Failed to load deliveries', 'error');
    }
}

function renderDeliveriesTable(deliveries) {
    const tbody = document.getElementById('deliveries-table-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (deliveries.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;">No deliveries found</td></tr>';
        return;
    }
    
    deliveries.forEach(delivery => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${delivery.id}</td>
            <td><strong>${delivery.product_name}</strong></td>
            <td>${delivery.quantity}</td>
            <td>${delivery.reference || '-'}</td>
            <td>${formatDate(delivery.created_at)}</td>
        `;
        tbody.appendChild(row);
    });
}

// ========================================
// EVENT LISTENERS
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    checkAuth();
    
    // Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            login(username, password);
        });
    }
    
    // Signup form
    const signupForm = document.getElementById('signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('signup-username').value;
            const email = document.getElementById('signup-email').value;
            const password = document.getElementById('signup-password').value;
            const confirm = document.getElementById('signup-confirm').value;
            signup(username, email, password, confirm);
        });
    }
    
    // Logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', logout);
    }
    
    // Load page-specific data
    if (window.location.pathname.includes('index.html')) {
        loadDashboard();
    } else if (window.location.pathname.includes('products.html')) {
        loadProducts();
    } else if (window.location.pathname.includes('receipts.html')) {
        loadReceipts();
    } else if (window.location.pathname.includes('deliveries.html')) {
        loadDeliveries();
    }
});

// Make functions global for inline onclick handlers
window.editProduct = editProduct;
window.deleteProduct = deleteProduct;
window.logout = logout;
