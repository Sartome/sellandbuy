// Enhanced JavaScript - Search, Filters, and Pagination

/**
 * Advanced search functionality
 */
class ProductSearch {
    constructor() {
        this.form = document.querySelector('#searchForm');
        this.searchInput = document.querySelector('#searchInput');
        this.categoryFilter = document.querySelector('#categoryFilter');
        this.priceMinInput = document.querySelector('#priceMin');
        this.priceMaxInput = document.querySelector('#priceMax');
        this.resultsContainer = document.querySelector('#searchResults');
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        // Real-time search with debounce
        let searchTimeout;
        this.searchInput?.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.performSearch();
            }, 500);
        });
        
        // Filter changes
        this.categoryFilter?.addEventListener('change', () => this.performSearch());
        this.priceMinInput?.addEventListener('change', () => this.performSearch());
        this.priceMaxInput?.addEventListener('change', () => this.performSearch());
        
        // Form submit
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });
    }
    
    async performSearch() {
        const searchQuery = this.searchInput?.value || '';
        const category = this.categoryFilter?.value || '';
        const priceMin = this.priceMinInput?.value || '';
        const priceMax = this.priceMaxInput?.value || '';
        
        // Show loading state
        if (this.resultsContainer) {
            this.resultsContainer.innerHTML = '<div class="loading">Recherche en cours...</div>';
        }
        
        try {
            const params = new URLSearchParams({
                controller: 'product',
                action: 'search',
                q: searchQuery,
                category: category,
                price_min: priceMin,
                price_max: priceMax
            });
            
            const response = await fetch(`index.php?${params}`);
            const html = await response.text();
            
            if (this.resultsContainer) {
                this.resultsContainer.innerHTML = html;
            }
        } catch (error) {
            console.error('Search error:', error);
            if (this.resultsContainer) {
                this.resultsContainer.innerHTML = '<div class="error">Erreur lors de la recherche</div>';
            }
        }
    }
}

/**
 * Pagination handler
 */
class Pagination {
    constructor() {
        this.container = document.querySelector('.pagination');
        if (this.container) {
            this.init();
        }
    }
    
    init() {
        this.container.addEventListener('click', (e) => {
            const pageLink = e.target.closest('[data-page]');
            if (pageLink) {
                e.preventDefault();
                const page = pageLink.dataset.page;
                this.loadPage(page);
            }
        });
    }
    
    async loadPage(page) {
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        
        try {
            const response = await fetch(url);
            const html = await response.text();
            
            // Update URL without reload
            window.history.pushState({}, '', url);
            
            // Update content (you'll need to adjust selectors)
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('.product-grid');
            const currentContent = document.querySelector('.product-grid');
            
            if (newContent && currentContent) {
                currentContent.innerHTML = newContent.innerHTML;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        } catch (error) {
            console.error('Pagination error:', error);
        }
    }
}

/**
 * Form validation
 */
class FormValidator {
    constructor(form) {
        this.form = form;
        this.errors = {};
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        this.form.addEventListener('submit', (e) => {
            if (!this.validate()) {
                e.preventDefault();
                this.displayErrors();
            }
        });
        
        // Real-time validation
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
                this.displayErrors();
            });
        });
    }
    
    validate() {
        this.errors = {};
        const inputs = this.form.querySelectorAll('[required]');
        
        inputs.forEach(input => {
            this.validateField(input);
        });
        
        return Object.keys(this.errors).length === 0;
    }
    
    validateField(input) {
        const name = input.name;
        const value = input.value.trim();
        
        // Remove previous error
        delete this.errors[name];
        
        // Required validation
        if (input.hasAttribute('required') && !value) {
            this.errors[name] = 'Ce champ est requis';
            return;
        }
        
        // Email validation
        if (input.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.errors[name] = 'Email invalide';
                return;
            }
        }
        
        // Min length validation
        if (input.hasAttribute('minlength')) {
            const minLength = parseInt(input.getAttribute('minlength'));
            if (value.length < minLength) {
                this.errors[name] = `Minimum ${minLength} caractères requis`;
                return;
            }
        }
        
        // Password confirmation
        if (input.name === 'password_confirm') {
            const password = this.form.querySelector('[name="password"]');
            if (password && value !== password.value) {
                this.errors[name] = 'Les mots de passe ne correspondent pas';
                return;
            }
        }
        
        // Number validation
        if (input.type === 'number' && value) {
            const min = input.getAttribute('min');
            const max = input.getAttribute('max');
            const numValue = parseFloat(value);
            
            if (min && numValue < parseFloat(min)) {
                this.errors[name] = `La valeur minimum est ${min}`;
                return;
            }
            
            if (max && numValue > parseFloat(max)) {
                this.errors[name] = `La valeur maximum est ${max}`;
                return;
            }
        }
    }
    
    displayErrors() {
        // Remove all previous error messages
        this.form.querySelectorAll('.error-message').forEach(el => el.remove());
        this.form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        
        // Display new errors
        Object.keys(this.errors).forEach(name => {
            const input = this.form.querySelector(`[name="${name}"]`);
            if (input) {
                input.classList.add('error');
                const errorEl = document.createElement('div');
                errorEl.className = 'error-message';
                errorEl.textContent = this.errors[name];
                input.parentNode.appendChild(errorEl);
            }
        });
    }
}

/**
 * Image preview for file uploads
 */
class ImagePreview {
    constructor(input, previewContainer) {
        this.input = input;
        this.previewContainer = previewContainer;
        
        if (this.input && this.previewContainer) {
            this.init();
        }
    }
    
    init() {
        this.input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                this.showPreview(file);
            }
        });
    }
    
    showPreview(file) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
            this.previewContainer.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="image-preview">
                <button type="button" class="remove-image" onclick="this.parentNode.innerHTML = ''">
                    Supprimer
                </button>
            `;
        };
        
        reader.readAsDataURL(file);
    }
}

/**
 * Toast notifications
 */
class Toast {
    static show(message, type = 'info', duration = 3000) {
        const container = document.querySelector('.toast-container') || this.createContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${this.getIcon(type)}</span>
                <span class="toast-message">${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentNode.remove()">×</button>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
    
    static createContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
    
    static getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }
}

// Initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize search
    new ProductSearch();
    
    // Initialize pagination
    new Pagination();
    
    // Initialize form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        new FormValidator(form);
    });
    
    // Initialize image previews
    const imageInput = document.querySelector('input[type="file"][accept*="image"]');
    const previewContainer = document.querySelector('#imagePreview');
    if (imageInput && previewContainer) {
        new ImagePreview(imageInput, previewContainer);
    }
    
    // Show flash messages as toasts
    const flashMessage = document.querySelector('[data-flash-message]');
    if (flashMessage) {
        const message = flashMessage.dataset.flashMessage;
        const type = flashMessage.dataset.flashType || 'info';
        Toast.show(message, type);
        flashMessage.remove();
    }
});

// Export for global use
window.Toast = Toast;
