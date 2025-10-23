// JavaScript principal - Enhanced Sell & Buy Marketplace

// Countdown for auction
(function(){
  const endsEl = document.getElementById('auction-ends-at');
  const countdownEl = document.getElementById('countdown');
  if (!endsEl || !countdownEl) return;
  const endsAt = new Date(endsEl.dataset.ends.replace(' ', 'T'));
  function leftPad(n){ return n.toString().padStart(2,'0'); }
  function tick(){
    const now = new Date();
    let diff = Math.max(0, Math.floor((endsAt - now) / 1000));
    const d = Math.floor(diff / 86400); diff -= d*86400;
    const h = Math.floor(diff / 3600); diff -= h*3600;
    const m = Math.floor(diff / 60); diff -= m*60;
    const s = diff;
    countdownEl.textContent = d+"j "+leftPad(h)+":"+leftPad(m)+":"+leftPad(s);
    if ((endsAt - now) <= 0) {
      countdownEl.textContent = 'Terminée';
      clearInterval(timer);
    }
  }
  tick();
  const timer = setInterval(tick, 1000);
})();

// Auto-dismiss flash messages with animation
(function(){
  const alerts = document.querySelectorAll('.alert');
  if (!alerts.length) return;
  
  alerts.forEach(alert => {
    // Add entrance animation
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
      alert.style.transition = 'all 0.3s ease';
      alert.style.opacity = '1';
      alert.style.transform = 'translateY(0)';
    }, 100);
    
    // Auto dismiss after 4 seconds
    setTimeout(() => {
      alert.style.transition = 'all 0.3s ease';
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-20px)';
      setTimeout(() => alert.remove(), 300);
    }, 4000);
  });
})();

// Confirm destructive actions
(function(){
  document.addEventListener('click', function(e){
    const t = e.target.closest('[data-confirm]');
    if (t) {
      if (!confirm(t.getAttribute('data-confirm'))) {
        e.preventDefault();
      }
    }
  });
})();

// Enhanced Image Lazy Loading
(function(){
  const images = document.querySelectorAll('img[data-src]');
  if (!images.length) return;
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.remove('lazy');
        img.classList.add('loaded');
        observer.unobserve(img);
      }
    });
  });
  
  images.forEach(img => imageObserver.observe(img));
})();

// Smooth Scroll for anchor links
(function(){
  document.addEventListener('click', function(e) {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;
    
    e.preventDefault();
    const targetId = link.getAttribute('href').substring(1);
    const targetElement = document.getElementById(targetId);
    
    if (targetElement) {
      targetElement.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
})();

// Enhanced Form Validation
(function(){
  const forms = document.querySelectorAll('form[data-validate]');
  if (!forms.length) return;
  
  forms.forEach(form => {
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
      input.addEventListener('blur', validateField);
      input.addEventListener('input', clearFieldError);
    });
    
    form.addEventListener('submit', function(e) {
      let isValid = true;
      
      inputs.forEach(input => {
        if (!validateField({ target: input })) {
          isValid = false;
        }
      });
      
      if (!isValid) {
        e.preventDefault();
        showFormError('Veuillez corriger les erreurs avant de soumettre le formulaire.');
      }
    });
  });
  
  function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      errorMessage = 'Ce champ est obligatoire.';
    }
    
    // Email validation
    if (type === 'email' && value && !isValidEmail(value)) {
      isValid = false;
      errorMessage = 'Veuillez entrer une adresse email valide.';
    }
    
    // Number validation
    if (type === 'number' && value) {
      const min = field.getAttribute('min');
      const max = field.getAttribute('max');
      const numValue = parseFloat(value);
      
      if (min && numValue < parseFloat(min)) {
        isValid = false;
        errorMessage = `La valeur doit être supérieure ou égale à ${min}.`;
      }
      
      if (max && numValue > parseFloat(max)) {
        isValid = false;
        errorMessage = `La valeur doit être inférieure ou égale à ${max}.`;
      }
    }
    
    // URL validation
    if (type === 'url' && value && !isValidUrl(value)) {
      isValid = false;
      errorMessage = 'Veuillez entrer une URL valide.';
    }
    
    showFieldError(field, isValid ? null : errorMessage);
    return isValid;
  }
  
  function clearFieldError(e) {
    const field = e.target;
    showFieldError(field, null);
  }
  
  function showFieldError(field, message) {
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
      existingError.remove();
    }
    
    field.classList.toggle('error', !!message);
    
    if (message) {
      const errorDiv = document.createElement('div');
      errorDiv.className = 'field-error';
      errorDiv.textContent = message;
      field.parentNode.appendChild(errorDiv);
    }
  }
  
  function showFormError(message) {
    const existingError = document.querySelector('.form-error');
    if (existingError) {
      existingError.remove();
    }
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert error form-error';
    errorDiv.textContent = message;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(errorDiv, container.firstChild);
    
    setTimeout(() => errorDiv.remove(), 5000);
  }
  
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
  
  function isValidUrl(url) {
    try {
      new URL(url);
      return true;
    } catch {
      return false;
    }
  }
})();

// Enhanced Search with Debouncing
(function(){
  const searchInputs = document.querySelectorAll('input[data-search]');
  if (!searchInputs.length) return;
  
  searchInputs.forEach(input => {
    let timeout;
    
    input.addEventListener('input', function() {
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        performSearch(input.value.trim());
      }, 300);
    });
  });
  
  function performSearch(query) {
    if (query.length < 2) return;
    
    // Add loading state
    const searchContainer = document.querySelector('[data-search-results]');
    if (searchContainer) {
      searchContainer.innerHTML = '<div class="search-loading">Recherche en cours...</div>';
    }
    
    // Simulate search (replace with actual AJAX call)
    setTimeout(() => {
      if (searchContainer) {
        searchContainer.innerHTML = `<div class="search-results">Résultats pour "${query}"</div>`;
      }
    }, 500);
  }
})();

// Enhanced Card Interactions
(function(){
  const cards = document.querySelectorAll('.card');
  if (!cards.length) return;
  
  cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.style.transform = 'translateY(-8px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
      this.style.transform = 'translateY(0) scale(1)';
    });
    
    // Add click ripple effect
    card.addEventListener('click', function(e) {
      const ripple = document.createElement('div');
      ripple.className = 'ripple';
      
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      
      this.appendChild(ripple);
      
      setTimeout(() => ripple.remove(), 600);
    });
  });
})();

// Enhanced Button Interactions
(function(){
  const buttons = document.querySelectorAll('.btn');
  if (!buttons.length) return;
  
  buttons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      // Add click animation
      this.style.transform = 'scale(0.95)';
      setTimeout(() => {
        this.style.transform = 'scale(1)';
      }, 150);
      
      // Add loading state for form submissions
      if (this.type === 'submit') {
        const form = this.closest('form');
        if (form) {
          this.disabled = true;
          this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
          
          setTimeout(() => {
            this.disabled = false;
            this.innerHTML = this.dataset.originalText || 'Envoyer';
          }, 2000);
        }
      }
    });
  });
})();

// Enhanced Navigation
(function(){
  const navLinks = document.querySelectorAll('.nav-links a');
  if (!navLinks.length) return;
  
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // Add active state
      navLinks.forEach(l => l.classList.remove('active'));
      this.classList.add('active');
    });
  });
})();

// Enhanced Product Grid with Masonry-like Layout
(function(){
  const grids = document.querySelectorAll('.grid');
  if (!grids.length) return;
  
  grids.forEach(grid => {
    const items = grid.querySelectorAll('.card');
    if (items.length === 0) return;
    
    // Add staggered animation
    items.forEach((item, index) => {
      item.style.animationDelay = `${index * 0.1}s`;
      item.classList.add('animate-in');
    });
  });
})();

// Enhanced Image Gallery
(function(){
  const galleries = document.querySelectorAll('[data-gallery]');
  if (!galleries.length) return;
  
  galleries.forEach(gallery => {
    const images = gallery.querySelectorAll('img');
    images.forEach((img, index) => {
      img.addEventListener('click', function() {
        openImageModal(this.src, index, images);
      });
    });
  });
  
  function openImageModal(src, index, images) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
      <div class="modal-content">
        <span class="modal-close">&times;</span>
        <img src="${src}" alt="Image ${index + 1}">
        <div class="modal-nav">
          <button class="modal-prev">‹</button>
          <button class="modal-next">›</button>
        </div>
        <div class="modal-counter">${index + 1} / ${images.length}</div>
      </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Event listeners
    modal.querySelector('.modal-close').addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
      if (e.target === modal) closeModal();
    });
    
    function closeModal() {
      modal.remove();
      document.body.style.overflow = 'auto';
    }
  }
})();

// Enhanced Accessibility
(function(){
  // Skip to main content
  const skipLink = document.createElement('a');
  skipLink.href = '#main-content';
  skipLink.textContent = 'Aller au contenu principal';
  skipLink.className = 'skip-link';
  document.body.insertBefore(skipLink, document.body.firstChild);
  
  // Enhanced focus management
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
      document.body.classList.add('keyboard-navigation');
    }
  });
  
  document.addEventListener('mousedown', function() {
    document.body.classList.remove('keyboard-navigation');
  });
})();

// Performance Monitoring
(function(){
  if ('performance' in window) {
    window.addEventListener('load', function() {
      setTimeout(() => {
        const perfData = performance.getEntriesByType('navigation')[0];
        console.log('Page load time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
      }, 0);
    });
  }
})();