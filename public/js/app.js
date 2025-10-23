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

/**
 * VALIDATION DES FORMULAIRES
 * 
 * Ce module gère la validation côté client pour les formulaires avec l'attribut 'data-validate'.
 * Il applique une validation en temps réel et empêche la soumission si des erreurs sont détectées.
 * 
 * ATTRIBUTS SUPPORTÉS :
 * - data-validate : Active la validation pour ce formulaire
 * - data-loading : Active l'animation de chargement sur les boutons submit
 * 
 * FONCTIONNALITÉS :
 * - Validation en temps réel (blur, input)
 * - Messages d'erreur contextuels
 * - Prévention de soumission si erreurs
 * - Support des types : email, number, url, required
 */
(function(){
  // Sélectionner uniquement les formulaires avec l'attribut data-validate
  const forms = document.querySelectorAll('form[data-validate]');
  if (!forms.length) {
    return; // Aucun formulaire à valider
  }
  
  // Traiter chaque formulaire individuellement
  forms.forEach(form => {
    // Sélectionner tous les champs obligatoires du formulaire
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    
    // Ajouter les event listeners pour la validation en temps réel
    inputs.forEach(input => {
      input.addEventListener('blur', validateField);      // Validation lors de la perte de focus
      input.addEventListener('input', clearFieldError);  // Effacer les erreurs lors de la saisie
    });
    
    // Intercepter la soumission du formulaire pour validation finale
    form.addEventListener('submit', function(e) {
      let isValid = true;
      
      // Valider tous les champs obligatoires
      inputs.forEach(input => {
        if (!validateField({ target: input })) {
          isValid = false;
        }
      });
      
      // Empêcher la soumission si des erreurs sont détectées
      if (!isValid) {
        e.preventDefault();
        showFormError('Veuillez corriger les erreurs avant de soumettre le formulaire.');
      }
    });
  });
  
  /**
   * VALIDATION D'UN CHAMP INDIVIDUEL
   * 
   * @param {Event} e - L'événement de validation (blur, input, etc.)
   * @returns {boolean} - true si le champ est valide, false sinon
   */
  function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let errorMessage = '';
    
    // VALIDATION OBLIGATOIRE
    // Vérifier si le champ est requis et s'il a une valeur
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      errorMessage = 'Ce champ est obligatoire.';
    }
    
    // VALIDATION EMAIL
    // Vérifier le format de l'adresse email si le champ a une valeur
    if (type === 'email' && value && !isValidEmail(value)) {
      isValid = false;
      errorMessage = 'Veuillez entrer une adresse email valide.';
    }
    
    // VALIDATION NUMÉRIQUE
    // Vérifier les contraintes min/max pour les champs numériques
    if (type === 'number' && value) {
      const min = field.getAttribute('min');
      const max = field.getAttribute('max');
      const numValue = parseFloat(value);
      
      // Vérifier la valeur minimale
      if (min && numValue < parseFloat(min)) {
        isValid = false;
        errorMessage = `La valeur doit être supérieure ou égale à ${min}.`;
      }
      
      // Vérifier la valeur maximale
      if (max && numValue > parseFloat(max)) {
        isValid = false;
        errorMessage = `La valeur doit être inférieure ou égale à ${max}.`;
      }
    }
    
    // VALIDATION URL
    // Vérifier le format de l'URL si le champ a une valeur
    if (type === 'url' && value && !isValidUrl(value)) {
      isValid = false;
      errorMessage = 'Veuillez entrer une URL valide.';
    }
    
    // Afficher l'erreur ou la masquer selon le résultat
    showFieldError(field, isValid ? null : errorMessage);
    return isValid;
  }
  
  /**
   * EFFACER LES ERREURS D'UN CHAMP
   * 
   * @param {Event} e - L'événement de saisie (input)
   */
  function clearFieldError(e) {
    const field = e.target;
    showFieldError(field, null); // Effacer l'erreur en passant null
  }

  /**
   * AFFICHER OU MASQUER UNE ERREUR SUR UN CHAMP
   * 
   * @param {HTMLElement} field - Le champ de formulaire
   * @param {string|null} message - Le message d'erreur (null pour masquer)
   */
  function showFieldError(field, message) {
    // Supprimer l'erreur existante s'il y en a une
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
      existingError.remove();
    }
    
    // Ajouter/supprimer la classe CSS d'erreur
    field.classList.toggle('error', !!message);
    
    // Créer et afficher le message d'erreur si nécessaire
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
      // Clear existing content
      searchContainer.textContent = '';
      
      // Create loading element using DOM methods
      const loadingDiv = document.createElement('div');
      loadingDiv.className = 'search-loading';
      loadingDiv.textContent = 'Recherche en cours...';
      searchContainer.appendChild(loadingDiv);
    }
    
    // Simulate search (replace with actual AJAX call)
    setTimeout(() => {
      if (searchContainer) {
        // Clear loading content
        searchContainer.textContent = '';
        
        // Create results element using DOM methods
        const resultsDiv = document.createElement('div');
        resultsDiv.className = 'search-results';
        resultsDiv.textContent = `Résultats pour "${query}"`;
        searchContainer.appendChild(resultsDiv);
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
      
      // Ne pas empêcher l'exécution des fonctions onclick
      // Laisser le comportement par défaut se produire
      
      // GESTION DES BOUTONS DE SOUMISSION
      // Ajouter l'état de chargement uniquement pour les formulaires avec l'attribut 'data-loading'
      if (this.type === 'submit') {
        const form = this.closest('form');
        if (form && form.hasAttribute('data-loading')) {
          // Ne pas désactiver immédiatement, laisser le formulaire se soumettre
          // L'animation sera gérée par l'événement submit du formulaire
        }
      }
    });
  });
})();

/**
 * GESTION DES FORMULAIRES AVEC LOADING
 * 
 * Ce module gère l'état de chargement pour les formulaires avec l'attribut 'data-loading'.
 * Il désactive le bouton submit et affiche une animation de chargement.
 */
(function(){
  const forms = document.querySelectorAll('form[data-loading]');
  if (!forms.length) return;
  
  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        // Désactiver le bouton pour éviter les double-soumissions
        submitBtn.disabled = true;
        
        // Store original content
        const originalContent = submitBtn.innerHTML;
        submitBtn.dataset.originalText = originalContent;
        
        // Clear and add loading content using DOM methods
        submitBtn.textContent = '';
        const spinner = document.createElement('i');
        spinner.className = 'fas fa-spinner fa-spin';
        const text = document.createTextNode(' Envoi...');
        submitBtn.appendChild(spinner);
        submitBtn.appendChild(text);
        
        // Réactiver le bouton après un délai pour permettre la soumission
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = '';
          submitBtn.innerHTML = originalContent;
        }, 3000);
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
    
    // Create modal content using DOM methods instead of innerHTML
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    
    const closeButton = document.createElement('span');
    closeButton.className = 'modal-close';
    closeButton.textContent = '×';
    
    const img = document.createElement('img');
    img.src = src;
    img.alt = `Image ${index + 1}`;
    
    const modalNav = document.createElement('div');
    modalNav.className = 'modal-nav';
    
    const prevButton = document.createElement('button');
    prevButton.className = 'modal-prev';
    prevButton.textContent = '‹';
    
    const nextButton = document.createElement('button');
    nextButton.className = 'modal-next';
    nextButton.textContent = '›';
    
    const counter = document.createElement('div');
    counter.className = 'modal-counter';
    counter.textContent = `${index + 1} / ${images.length}`;
    
    // Assemble the modal
    modalNav.appendChild(prevButton);
    modalNav.appendChild(nextButton);
    modalContent.appendChild(closeButton);
    modalContent.appendChild(img);
    modalContent.appendChild(modalNav);
    modalContent.appendChild(counter);
    modal.appendChild(modalContent);
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Event listeners
    closeButton.addEventListener('click', closeModal);
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

// Admin Functions for Analytics Page
function optimizeAllImages() {
  showLoading('Optimisation des images en cours...');
  
  // Simuler l'optimisation (remplacer par un appel AJAX réel)
  setTimeout(() => {
    hideLoading();
    alert('Optimisation terminée ! Toutes les images ont été optimisées.');
  }, 2000);
}

function cleanupOrphanedImages() {
  if (confirm('Êtes-vous sûr de vouloir nettoyer les images orphelines ? Cette action est irréversible.')) {
    showLoading('Nettoyage des images orphelines...');
    
    // Simuler le nettoyage (remplacer par un appel AJAX réel)
    setTimeout(() => {
      hideLoading();
      alert('Nettoyage terminé ! Les images orphelines ont été supprimées.');
    }, 1500);
  }
}

function generateImageReport() {
  showLoading('Génération du rapport...');
  
  // Simuler la génération du rapport (remplacer par un appel AJAX réel)
  setTimeout(() => {
    hideLoading();
    alert('Rapport généré avec succès ! Le fichier a été téléchargé.');
  }, 1000);
}

function showLoading(message) {
  const loadingDiv = document.createElement('div');
  loadingDiv.className = 'admin-loading';
  loadingDiv.innerHTML = `
    <div class="loading-content">
      <div class="loading-spinner"></div>
      <p>${message}</p>
    </div>
  `;
  document.body.appendChild(loadingDiv);
}

function hideLoading() {
  const loadingDiv = document.querySelector('.admin-loading');
  if (loadingDiv) {
    loadingDiv.remove();
  }
}