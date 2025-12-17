/**
 * ICOGroup Website - Advanced JavaScript
 * Features: Dark Mode, Lazy Loading, Form Validation, Toast, 
 * Skeleton Loading, Sticky Header, Back to Top, Mobile Menu
 * @version 2.0.0
 */

(function () {
    'use strict';

    // ============================================
    // CONFIGURATION
    // ============================================
    const CONFIG = {
        SCROLL_THRESHOLD: 100,
        TOAST_DURATION: 4000,
        ANIMATION_DURATION: 300,
        DEBOUNCE_DELAY: 100,
        LAZY_LOAD_MARGIN: '100px',
        AUTO_SLIDE_INTERVAL: 5000
    };

    // ============================================
    // UTILITY FUNCTIONS
    // ============================================

    /**
     * Debounce function for performance optimization
     */
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

    /**
     * Throttle function for scroll events
     */
    function throttle(func, limit) {
        let inThrottle;
        return function (...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Format number with Vietnamese locale
     */
    function formatNumber(num) {
        return num.toLocaleString('vi-VN');
    }

    /**
     * Check if element is in viewport
     */
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    // ============================================
    // 1. DARK MODE TOGGLE
    // ============================================
    const DarkMode = {
        STORAGE_KEY: 'ico-dark-mode',

        init() {
            this.createToggle();
            this.loadPreference();
            this.bindEvents();
        },

        createToggle() {
            // Check if toggle already exists
            if (document.getElementById('darkModeToggle')) return;

            // Create toggle button
            const toggle = document.createElement('button');
            toggle.id = 'darkModeToggle';
            toggle.className = 'dark-mode-toggle';
            toggle.innerHTML = `
                <span class="dark-mode-icon sun">☀️</span>
                <span class="dark-mode-icon moon">🌙</span>
            `;
            toggle.setAttribute('aria-label', 'Toggle dark mode');
            toggle.title = 'Chế độ sáng/tối';

            document.body.appendChild(toggle);

            // Add styles
            this.addStyles();
        },

        addStyles() {
            if (document.getElementById('darkModeStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'darkModeStyles';
            styles.textContent = `
                /* Dark Mode Toggle Button */
                .dark-mode-toggle {
                    position: fixed;
                    bottom: 100px;
                    right: 30px;
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    border: none;
                    background: linear-gradient(135deg, #2563EB, #3B82F6);
                    color: white;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
                    z-index: 1500;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    overflow: hidden;
                }

                .dark-mode-toggle:hover {
                    transform: scale(1.1);
                    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
                }

                .dark-mode-icon {
                    font-size: 20px;
                    position: absolute;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .dark-mode-icon.sun {
                    opacity: 1;
                    transform: rotate(0deg);
                }

                .dark-mode-icon.moon {
                    opacity: 0;
                    transform: rotate(-90deg);
                }

                /* Dark Mode Active State */
                body.dark-mode .dark-mode-toggle {
                    background: linear-gradient(135deg, #F59E0B, #FBBF24);
                }

                body.dark-mode .dark-mode-icon.sun {
                    opacity: 0;
                    transform: rotate(90deg);
                }

                body.dark-mode .dark-mode-icon.moon {
                    opacity: 1;
                    transform: rotate(0deg);
                }

                /* Dark Mode Styles - Minimal overrides */
                /* Main dark mode styles are in style.css with [data-theme="dark"] */
                body.dark-mode {
                    background-color: var(--bg-primary);
                    color: var(--text-primary);
                }

                /* Smooth transition for dark mode */
                body, body * {
                    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
                }
            `;
            document.head.appendChild(styles);
        },

        loadPreference() {
            const savedMode = localStorage.getItem(this.STORAGE_KEY);

            // Check system preference if no saved preference
            if (savedMode === null) {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.body.classList.add('dark-mode');
                }
            } else if (savedMode === 'true') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.classList.add('dark-mode');
            }
        },

        bindEvents() {
            const toggle = document.getElementById('darkModeToggle');
            if (toggle && !toggle.dataset.bound) {
                toggle.dataset.bound = 'true';
                toggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.toggle();
                });
            }

            // Listen for system preference changes
            if (!this._systemPrefBound) {
                this._systemPrefBound = true;
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    if (localStorage.getItem(this.STORAGE_KEY) === null) {
                        document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : '');
                        document.body.classList.toggle('dark-mode', e.matches);
                    }
                });
            }
        },

        toggle() {
            const html = document.documentElement;
            const isDark = html.getAttribute('data-theme') === 'dark';

            if (isDark) {
                html.removeAttribute('data-theme');
                document.body.classList.remove('dark-mode');
                localStorage.setItem(this.STORAGE_KEY, 'false');
                Toast.show('☀️ Chế độ sáng đã bật', 'info');
            } else {
                html.setAttribute('data-theme', 'dark');
                document.body.classList.add('dark-mode');
                localStorage.setItem(this.STORAGE_KEY, 'true');
                Toast.show('🌙 Chế độ tối đã bật', 'info');
            }
        }
    };

    // ============================================
    // 2. LAZY LOADING IMAGES
    // ============================================
    const LazyLoad = {
        init() {
            this.observeImages();
            this.addPlaceholderStyles();
        },

        addPlaceholderStyles() {
            if (document.getElementById('lazyLoadStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'lazyLoadStyles';
            styles.textContent = `
                .lazy-image {
                    opacity: 0;
                    transition: opacity 0.5s ease;
                }

                .lazy-image.loaded {
                    opacity: 1;
                }

                .lazy-placeholder {
                    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                    background-size: 200% 100%;
                    animation: shimmer 1.5s infinite;
                }

                body.dark-mode .lazy-placeholder {
                    background: linear-gradient(90deg, #1E293B 25%, #334155 50%, #1E293B 75%);
                    background-size: 200% 100%;
                }

                @keyframes shimmer {
                    0% { background-position: -200% 0; }
                    100% { background-position: 200% 0; }
                }
            `;
            document.head.appendChild(styles);
        },

        observeImages() {
            const images = document.querySelectorAll('img[data-src], img:not([loading])');

            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.loadImage(entry.target);
                            imageObserver.unobserve(entry.target);
                        }
                    });
                }, {
                    rootMargin: CONFIG.LAZY_LOAD_MARGIN
                });

                images.forEach(img => {
                    if (img.dataset.src) {
                        img.classList.add('lazy-image', 'lazy-placeholder');
                        imageObserver.observe(img);
                    }
                });
            } else {
                // Fallback for older browsers
                images.forEach(img => this.loadImage(img));
            }
        },

        loadImage(img) {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            }

            img.onload = () => {
                img.classList.remove('lazy-placeholder');
                img.classList.add('loaded');
            };
        }
    };

    // ============================================
    // 3. FORM VALIDATION REAL-TIME
    // ============================================
    const FormValidation = {
        init() {
            this.addStyles();
            this.bindValidation();
        },

        addStyles() {
            if (document.getElementById('validationStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'validationStyles';
            styles.textContent = `
                .form-group {
                    position: relative;
                }

                .form-group input.valid {
                    border-color: #10B981 !important;
                }

                .form-group input.invalid {
                    border-color: #EF4444 !important;
                }

                .form-group input.valid + .validation-icon,
                .form-group input.valid ~ .validation-icon {
                    display: block;
                }

                .validation-icon {
                    position: absolute;
                    right: 12px;
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 18px;
                    display: none;
                }

                .validation-icon.success {
                    color: #10B981;
                }

                .validation-icon.error {
                    color: #EF4444;
                }

                .validation-message {
                    font-size: 12px;
                    margin-top: 4px;
                    display: none;
                }

                .validation-message.show {
                    display: block;
                }

                .validation-message.error {
                    color: #EF4444;
                }

                .validation-message.success {
                    color: #10B981;
                }

                /* Phone number formatting */
                .form-group input[type="tel"] {
                    letter-spacing: 1px;
                }
            `;
            document.head.appendChild(styles);
        },

        bindValidation() {
            const form = document.getElementById('userRegistrationForm');
            if (!form) return;

            // Add validation to each input
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                // Create validation icon
                const icon = document.createElement('span');
                icon.className = 'validation-icon';

                // Create validation message
                const message = document.createElement('span');
                message.className = 'validation-message';

                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', debounce(() => this.validateField(input), 300));
            });

            // Phone number auto-format
            const phoneInput = document.getElementById('sdt');
            if (phoneInput) {
                phoneInput.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 11) value = value.slice(0, 11);
                    e.target.value = value;
                });
            }

            // Year validation
            const yearInput = document.getElementById('nam_sinh');
            if (yearInput) {
                yearInput.addEventListener('input', (e) => {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 4) value = value.slice(0, 4);
                    e.target.value = value;
                });
            }
        },

        validateField(input) {
            const value = input.value.trim();
            let isValid = true;
            let message = '';

            switch (input.id) {
                case 'ho_ten':
                    isValid = value.length >= 2;
                    message = isValid ? '' : 'Họ tên phải có ít nhất 2 ký tự';
                    break;
                case 'nam_sinh':
                    const year = parseInt(value);
                    const currentYear = new Date().getFullYear();
                    isValid = year >= 1950 && year <= currentYear;
                    message = isValid ? '' : 'Năm sinh không hợp lệ';
                    break;
                case 'sdt':
                    isValid = /^0[0-9]{9,10}$/.test(value);
                    message = isValid ? '' : 'Số điện thoại phải bắt đầu bằng 0 và có 10-11 số';
                    break;
                case 'dia_chi':
                    isValid = value.length >= 3;
                    message = isValid ? '' : 'Địa chỉ phải có ít nhất 3 ký tự';
                    break;
                default:
                    isValid = value.length > 0;
            }

            // Update visual state
            input.classList.remove('valid', 'invalid');
            if (value.length > 0) {
                input.classList.add(isValid ? 'valid' : 'invalid');
            }

            return isValid;
        }
    };

    // ============================================
    // 4. TOAST NOTIFICATIONS
    // ============================================
    const Toast = {
        container: null,

        init() {
            this.createContainer();
            this.addStyles();
        },

        addStyles() {
            if (document.getElementById('toastStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'toastStyles';
            styles.textContent = `
                .toast-container {
                    position: fixed;
                    bottom: 32px;
                    left: 50%;
                    transform: translateX(-50%);
                    z-index: 10000;
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    pointer-events: none;
                }

                .toast {
                    padding: 16px 24px;
                    border-radius: 12px;
                    color: white;
                    font-weight: 500;
                    font-size: 14px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                    animation: toastIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                    pointer-events: auto;
                    max-width: 400px;
                }

                .toast.success {
                    background: linear-gradient(135deg, #10B981, #34D399);
                }

                .toast.error {
                    background: linear-gradient(135deg, #EF4444, #F87171);
                }

                .toast.info {
                    background: linear-gradient(135deg, #2563EB, #3B82F6);
                }

                .toast.warning {
                    background: linear-gradient(135deg, #F59E0B, #FBBF24);
                    color: #1E293B;
                }

                .toast.removing {
                    animation: toastOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
                }

                .toast-icon {
                    font-size: 20px;
                }

                .toast-close {
                    margin-left: auto;
                    background: rgba(255, 255, 255, 0.2);
                    border: none;
                    color: inherit;
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    transition: background 0.2s;
                }

                .toast-close:hover {
                    background: rgba(255, 255, 255, 0.3);
                }

                @keyframes toastIn {
                    from {
                        opacity: 0;
                        transform: translateY(20px) scale(0.9);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }

                @keyframes toastOut {
                    to {
                        opacity: 0;
                        transform: translateY(-20px) scale(0.9);
                    }
                }

                @media (max-width: 480px) {
                    .toast-container {
                        left: 16px;
                        right: 16px;
                        transform: none;
                    }

                    .toast {
                        max-width: 100%;
                    }
                }
            `;
            document.head.appendChild(styles);
        },

        createContainer() {
            if (document.getElementById('toastContainer')) {
                this.container = document.getElementById('toastContainer');
                return;
            }

            this.container = document.createElement('div');
            this.container.id = 'toastContainer';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        },

        show(message, type = 'info', duration = CONFIG.TOAST_DURATION) {
            if (!this.container) this.createContainer();

            const icons = {
                success: '✓',
                error: '✕',
                info: 'ℹ',
                warning: '⚠'
            };

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <span class="toast-icon">${icons[type] || icons.info}</span>
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
            `;

            this.container.appendChild(toast);

            // Auto remove
            setTimeout(() => {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }, duration);

            return toast;
        }
    };

    // Make Toast globally available
    window.Toast = Toast;

    // ============================================
    // 5. SKELETON LOADING STATES
    // ============================================
    const SkeletonLoading = {
        init() {
            this.addStyles();
        },

        addStyles() {
            if (document.getElementById('skeletonStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'skeletonStyles';
            styles.textContent = `
                .skeleton {
                    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                    background-size: 200% 100%;
                    animation: skeleton-shimmer 1.5s infinite;
                    border-radius: 8px;
                }

                body.dark-mode .skeleton {
                    background: linear-gradient(90deg, #1E293B 25%, #334155 50%, #1E293B 75%);
                    background-size: 200% 100%;
                }

                .skeleton-text {
                    height: 16px;
                    margin-bottom: 8px;
                }

                .skeleton-text.short {
                    width: 60%;
                }

                .skeleton-title {
                    height: 24px;
                    margin-bottom: 12px;
                    width: 80%;
                }

                .skeleton-image {
                    width: 100%;
                    height: 200px;
                }

                .skeleton-card {
                    padding: 20px;
                    border-radius: 15px;
                    background: white;
                }

                body.dark-mode .skeleton-card {
                    background: #1E293B;
                }

                @keyframes skeleton-shimmer {
                    0% { background-position: -200% 0; }
                    100% { background-position: 200% 0; }
                }
            `;
            document.head.appendChild(styles);
        },

        createCard() {
            return `
                <div class="skeleton-card">
                    <div class="skeleton skeleton-image"></div>
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text short"></div>
                </div>
            `;
        },

        createRow() {
            return `
                <div style="display: flex; gap: 16px; padding: 16px;">
                    <div class="skeleton" style="width: 50px; height: 50px; border-radius: 50%;"></div>
                    <div style="flex: 1;">
                        <div class="skeleton skeleton-title" style="width: 40%;"></div>
                        <div class="skeleton skeleton-text"></div>
                    </div>
                </div>
            `;
        }
    };

    // ============================================
    // 6. STICKY HEADER WITH SCROLL EFFECT
    // ============================================
    const StickyHeader = {
        nav: null,
        topBar: null,
        lastScrollY: 0,
        ticking: false,

        init() {
            this.nav = document.querySelector('nav');
            this.topBar = document.querySelector('.top-bar');
            if (!this.nav) return;

            this.addStyles();
            this.bindScroll();
        },

        addStyles() {
            if (document.getElementById('stickyHeaderStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'stickyHeaderStyles';
            styles.textContent = `
                nav {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                nav.scrolled {
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                    backdrop-filter: blur(10px);
                }

                nav.scrolled .nav-logo {
                    transform: scale(0.9);
                }

                nav.hidden {
                    transform: translateY(-100%);
                }

                .top-bar {
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .top-bar.hidden {
                    transform: translateY(-100%);
                    opacity: 0;
                }

                body.scrolled {
                    padding-top: 60px;
                }
            `;
            document.head.appendChild(styles);
        },

        bindScroll() {
            window.addEventListener('scroll', throttle(() => {
                this.onScroll();
            }, 100), { passive: true });
        },

        onScroll() {
            const scrollY = window.scrollY;

            // Add scrolled class
            if (scrollY > CONFIG.SCROLL_THRESHOLD) {
                this.nav.classList.add('scrolled');
                if (this.topBar) {
                    this.topBar.classList.add('hidden');
                    this.nav.style.top = '0';
                    document.body.style.paddingTop = '60px';
                }
            } else {
                this.nav.classList.remove('scrolled');
                if (this.topBar) {
                    this.topBar.classList.remove('hidden');
                    this.nav.style.top = '40px';
                    document.body.style.paddingTop = '100px';
                }
            }

            this.lastScrollY = scrollY;
        }
    };

    // ============================================
    // 7. IMPROVED BACK TO TOP BUTTON
    // ============================================
    const BackToTop = {
        button: null,
        progressRing: null,

        init() {
            this.createButton();
            this.addStyles();
            this.bindEvents();
        },

        createButton() {
            // Remove existing button
            const existing = document.getElementById('backToTop');
            if (existing) existing.remove();

            this.button = document.createElement('button');
            this.button.id = 'backToTop';
            this.button.className = 'back-to-top-enhanced';
            this.button.innerHTML = `
                <svg class="progress-ring" width="50" height="50">
                    <circle class="progress-ring-bg" cx="25" cy="25" r="22" fill="none" stroke-width="3"/>
                    <circle class="progress-ring-fill" cx="25" cy="25" r="22" fill="none" stroke-width="3"/>
                </svg>
                <span class="back-to-top-icon">↑</span>
            `;
            this.button.setAttribute('aria-label', 'Scroll to top');
            this.button.title = 'Về đầu trang';

            document.body.appendChild(this.button);
            this.progressRing = this.button.querySelector('.progress-ring-fill');
        },

        addStyles() {
            if (document.getElementById('backToTopStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'backToTopStyles';
            styles.textContent = `
                .back-to-top-enhanced {
                    position: fixed;
                    bottom: 30px;
                    right: 30px;
                    width: 50px;
                    height: 50px;
                    border-radius: 50%;
                    border: none;
                    background: linear-gradient(135deg, #F59E0B, #FBBF24);
                    color: white;
                    cursor: pointer;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    z-index: 1500;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
                    padding: 0;
                }

                .back-to-top-enhanced.show {
                    display: flex;
                }

                .back-to-top-enhanced:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.5);
                }

                .progress-ring {
                    position: absolute;
                    top: 0;
                    left: 0;
                    transform: rotate(-90deg);
                }

                .progress-ring-bg {
                    stroke: rgba(255, 255, 255, 0.3);
                }

                .progress-ring-fill {
                    stroke: white;
                    stroke-dasharray: 138.2;
                    stroke-dashoffset: 138.2;
                    transition: stroke-dashoffset 0.1s;
                }

                .back-to-top-icon {
                    font-size: 20px;
                    font-weight: bold;
                    z-index: 1;
                }
            `;
            document.head.appendChild(styles);
        },

        bindEvents() {
            // Scroll listener
            window.addEventListener('scroll', throttle(() => {
                this.updateProgress();
            }, 50), { passive: true });

            // Click listener
            this.button.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        },

        updateProgress() {
            const scrollTop = window.scrollY;
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercent = (scrollTop / docHeight) * 100;

            // Show/hide button
            if (scrollTop > 300) {
                this.button.classList.add('show');
            } else {
                this.button.classList.remove('show');
            }

            // Update progress ring
            if (this.progressRing) {
                const circumference = 138.2; // 2 * PI * 22
                const offset = circumference - (scrollPercent / 100) * circumference;
                this.progressRing.style.strokeDashoffset = offset;
            }
        }
    };

    // ============================================
    // 8. IMPROVED MOBILE MENU
    // ============================================
    const MobileMenu = {
        menu: null,
        overlay: null,
        isOpen: false,

        init() {
            this.menu = document.getElementById('mobileMenu');
            if (!this.menu) return;

            this.createOverlay();
            this.addStyles();
            this.bindEvents();
        },

        createOverlay() {
            if (document.getElementById('mobileMenuOverlay')) return;

            this.overlay = document.createElement('div');
            this.overlay.id = 'mobileMenuOverlay';
            this.overlay.className = 'mobile-menu-overlay';
            document.body.appendChild(this.overlay);
        },

        addStyles() {
            if (document.getElementById('mobileMenuStyles')) return;

            const styles = document.createElement('style');
            styles.id = 'mobileMenuStyles';
            styles.textContent = `
                .mobile-menu-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    backdrop-filter: blur(4px);
                    z-index: 2999;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                }

                .mobile-menu-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }

                .mobile-menu {
                    transform: translateX(-100%);
                    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                    box-shadow: 10px 0 40px rgba(0, 0, 0, 0.2);
                }

                .mobile-menu.active {
                    transform: translateX(0);
                }

                .mobile-menu ul li a {
                    transition: all 0.2s ease;
                    position: relative;
                    overflow: hidden;
                }

                .mobile-menu ul li a::before {
                    content: '';
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 4px;
                    height: 100%;
                    background: #F59E0B;
                    transform: scaleY(0);
                    transition: transform 0.2s ease;
                }

                .mobile-menu ul li a:hover::before {
                    transform: scaleY(1);
                }

                .mobile-menu ul li a:hover {
                    padding-left: 28px;
                    background: #F0F7FF;
                }

                body.dark-mode .mobile-menu {
                    background: #1E293B !important;
                }

                body.dark-mode .mobile-menu ul li a {
                    color: #F1F5F9 !important;
                    border-color: #334155 !important;
                }

                body.dark-mode .mobile-menu ul li a:hover {
                    background: #334155 !important;
                }
            `;
            document.head.appendChild(styles);
        },

        bindEvents() {
            // Toggle function
            window.toggleMobileMenu = () => {
                this.toggle();
            };

            // Close on overlay click
            if (this.overlay) {
                this.overlay.addEventListener('click', () => this.close());
            }

            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });

            // Close on link click
            this.menu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => this.close());
            });
        },

        toggle() {
            this.isOpen = !this.isOpen;
            this.menu.classList.toggle('active', this.isOpen);
            if (this.overlay) {
                this.overlay.classList.toggle('active', this.isOpen);
            }
            document.body.style.overflow = this.isOpen ? 'hidden' : '';
        },

        close() {
            this.isOpen = false;
            this.menu.classList.remove('active');
            if (this.overlay) {
                this.overlay.classList.remove('active');
            }
            document.body.style.overflow = '';
        }
    };

    // ============================================
    // HERO SLIDER (Enhanced)
    // ============================================
    const HeroSlider = {
        slider: null,
        slides: null,
        dots: null,
        currentSlide: 0,
        slideInterval: null,

        init() {
            this.slider = document.querySelector('.hero-slider');
            this.slides = document.querySelectorAll('.hero-slide');
            this.dots = document.querySelectorAll('.hero-dot');

            if (!this.slider || this.slides.length === 0) return;

            this.bindEvents();
            this.startAutoSlide();
            this.addTouchSupport();
        },

        goToSlide(index) {
            if (index >= this.slides.length) index = 0;
            if (index < 0) index = this.slides.length - 1;

            this.currentSlide = index;
            this.slider.style.transform = `translateX(-${this.currentSlide * 100}%)`;

            this.dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === this.currentSlide);
            });
        },

        nextSlide() {
            this.goToSlide(this.currentSlide + 1);
        },

        prevSlide() {
            this.goToSlide(this.currentSlide - 1);
        },

        startAutoSlide() {
            if (this.slideInterval) clearInterval(this.slideInterval);
            this.slideInterval = setInterval(() => this.nextSlide(), CONFIG.AUTO_SLIDE_INTERVAL);
        },

        stopAutoSlide() {
            if (this.slideInterval) clearInterval(this.slideInterval);
        },

        bindEvents() {
            const nextBtn = document.querySelector('.hero-nav.next');
            const prevBtn = document.querySelector('.hero-nav.prev');

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    this.nextSlide();
                    this.startAutoSlide();
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    this.prevSlide();
                    this.startAutoSlide();
                });
            }

            this.dots.forEach((dot, i) => {
                dot.addEventListener('click', () => {
                    this.goToSlide(i);
                    this.startAutoSlide();
                });
            });
        },

        addTouchSupport() {
            let touchStartX = 0;
            let touchEndX = 0;

            this.slider.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                this.stopAutoSlide();
            }, { passive: true });

            this.slider.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
                this.startAutoSlide();
            }, { passive: true });
        },

        handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        }
    };

    // ============================================
    // STATISTICS COUNTER (Enhanced)
    // ============================================
    const StatsCounter = {
        init() {
            const statNumbers = document.querySelectorAll('.stat-number');
            if (statNumbers.length === 0) return;

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                        entry.target.classList.add('animated');
                        this.animateCounter(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            statNumbers.forEach(stat => observer.observe(stat));
        },

        animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const duration = 2000;
            const startTime = performance.now();

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function (ease out cubic)
                const easeOutCubic = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(target * easeOutCubic);

                element.textContent = formatNumber(current) + '+';

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    element.textContent = formatNumber(target) + '+';
                }
            };

            requestAnimationFrame(animate);
        }
    };

    // ============================================
    // SMOOTH SCROLL
    // ============================================
    const SmoothScroll = {
        init() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', (e) => {
                    const targetId = anchor.getAttribute('href');
                    if (targetId === '#') return;

                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        e.preventDefault();
                        const headerOffset = 110;
                        const elementPosition = targetElement.getBoundingClientRect().top;
                        const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                        window.scrollTo({
                            top: offsetPosition,
                            behavior: 'smooth'
                        });

                        // Close mobile menu if open
                        MobileMenu.close();
                    }
                });
            });
        }
    };

    // ============================================
    // REGISTRATION FORM (Enhanced)
    // ============================================
    const RegistrationForm = {
        init() {
            const form = document.getElementById('userRegistrationForm');
            if (!form) return;

            this.bindCountryToggle();
            this.bindFormSubmit(form);
        },

        bindCountryToggle() {
            const quocGiaSelect = document.getElementById('quoc_gia');
            const quocGiaKhacBox = document.getElementById('quoc_gia_khac_box');

            if (quocGiaSelect && quocGiaKhacBox) {
                quocGiaSelect.addEventListener('change', function () {
                    quocGiaKhacBox.style.display = this.value === 'Khác' ? 'block' : 'none';
                });
            }
        },

        bindFormSubmit(form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = '⏳ Đang gửi...';
                submitBtn.disabled = true;

                let selectedQuocGia = document.getElementById('quoc_gia').value;
                const quocGiaKhacInput = document.getElementById('quoc_gia_khac');

                if (selectedQuocGia === 'Khác') {
                    const customVal = quocGiaKhacInput ? quocGiaKhacInput.value.trim() : '';
                    if (customVal !== '') {
                        selectedQuocGia = customVal;
                    } else {
                        Toast.show('Vui lòng nhập tên quốc gia mong muốn', 'error');
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                        if (quocGiaKhacInput) quocGiaKhacInput.focus();
                        return;
                    }
                }

                const data = {
                    ho_ten: document.getElementById('ho_ten').value.trim(),
                    nam_sinh: document.getElementById('nam_sinh').value.trim(),
                    dia_chi: document.getElementById('dia_chi').value.trim(),
                    chuong_trinh: document.getElementById('chuong_trinh').value,
                    quoc_gia: selectedQuocGia,
                    sdt: document.getElementById('sdt').value.trim()
                };

                try {
                    const res = await fetch('/web8s/backend_api/insert.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await res.json();

                    if (result.status) {
                        Toast.show('✅ Đăng ký thành công! Chúng tôi sẽ liên hệ sớm.', 'success');
                        form.reset();
                        const quocGiaKhacBox = document.getElementById('quoc_gia_khac_box');
                        if (quocGiaKhacBox) quocGiaKhacBox.style.display = 'none';
                    } else {
                        Toast.show('❌ Lỗi: ' + result.message, 'error');
                    }
                } catch (err) {
                    console.error(err);
                    Toast.show('❌ Không thể kết nối đến máy chủ!', 'error');
                }

                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }
    };

    // ============================================
    // INITIALIZE ALL MODULES
    // ============================================
    document.addEventListener('DOMContentLoaded', function () {
        // Core features
        DarkMode.init();
        Toast.init();
        LazyLoad.init();
        FormValidation.init();
        SkeletonLoading.init();
        StickyHeader.init();
        BackToTop.init();
        MobileMenu.init();

        // Page specific features
        HeroSlider.init();
        StatsCounter.init();
        SmoothScroll.init();
        RegistrationForm.init();

        console.log('🚀 ICOGroup Advanced Features Loaded');
    });

})();
