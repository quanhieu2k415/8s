// ICOGroup Website - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // 1. MOBILE MENU
    // ============================================
    window.toggleMobileMenu = function() {
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenu) {
            mobileMenu.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        }
    };

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuBtn = document.querySelector('.mobile-menu-btn');
        if (mobileMenu && mobileMenu.classList.contains('active')) {
            if (!mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });

    // ============================================
    // 2. HERO SLIDER
    // ============================================
    const heroSlider = document.querySelector('.hero-slider');
    const heroSlides = document.querySelectorAll('.hero-slide');
    const heroDots = document.querySelectorAll('.hero-dot');
    const prevBtn = document.querySelector('.hero-nav.prev');
    const nextBtn = document.querySelector('.hero-nav.next');
    
    if (heroSlider && heroSlides.length > 0) {
        let currentSlide = 0;
        let slideInterval;

        function goToSlide(index) {
            if (index >= heroSlides.length) index = 0;
            if (index < 0) index = heroSlides.length - 1;
            currentSlide = index;
            heroSlider.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Update dots
            heroDots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentSlide);
            });
        }

        function nextSlide() {
            goToSlide(currentSlide + 1);
        }

        function prevSlide() {
            goToSlide(currentSlide - 1);
        }

        function startAutoSlide() {
            if (slideInterval) clearInterval(slideInterval);
            slideInterval = setInterval(nextSlide, 5000);
        }

        // Event listeners
        if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startAutoSlide(); });
        if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startAutoSlide(); });
        
        heroDots.forEach((dot, i) => {
            dot.addEventListener('click', () => { goToSlide(i); startAutoSlide(); });
        });

        // Start auto slide
        startAutoSlide();
    }

    // ============================================
    // 3. STATISTICS COUNTER ANIMATION
    // ============================================
    const statNumbers = document.querySelectorAll('.stat-number');
    
    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'));
        const duration = 2000;
        const increment = target / (duration / 16);
        let current = 0;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString('vi-VN') + '+';
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString('vi-VN') + '+';
            }
        }, 16);
    }

    // Intersection Observer for stats
    if (statNumbers.length > 0) {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                    entry.target.classList.add('animated');
                    animateCounter(entry.target);
                }
            });
        }, { threshold: 0.5 });

        statNumbers.forEach(stat => statsObserver.observe(stat));
    }

    // ============================================
    // 4. BACK TO TOP BUTTON
    // ============================================
    const backToTopBtn = document.getElementById('backToTop');
    
    window.scrollToTop = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.addEventListener('scroll', function() {
        if (backToTopBtn) {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        }
    });

    // ============================================
    // 5. SMOOTH SCROLL FOR ANCHOR LINKS
    // ============================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
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
                const mobileMenu = document.getElementById('mobileMenu');
                if (mobileMenu && mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
    });

    // ============================================
    // 6. NAVBAR SCROLL EFFECT
    // ============================================
    const nav = document.querySelector('nav');
    let lastScrollY = window.scrollY;

    window.addEventListener('scroll', function() {
        if (nav) {
            if (window.scrollY > 100) {
                nav.style.boxShadow = '0 5px 20px rgba(0,0,0,0.15)';
            } else {
                nav.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
            }
        }
        lastScrollY = window.scrollY;
    });

    // ============================================
    // 7. REGISTRATION FORM
    // ============================================
    const registrationForm = document.getElementById('userRegistrationForm');
    
    if (registrationForm) {
        // Country other field toggle
        const quocGiaSelect = document.getElementById('quoc_gia');
        const quocGiaKhacBox = document.getElementById('quoc_gia_khac_box');
        
        if (quocGiaSelect && quocGiaKhacBox) {
            quocGiaSelect.addEventListener('change', function() {
                quocGiaKhacBox.style.display = this.value === 'Khác' ? 'block' : 'none';
            });
        }

        // Validate year and phone (numbers only)
        const numericInputs = ['nam_sinh', 'sdt'];
        numericInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });

        // Form submission
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const messageDisplay = document.getElementById('message');
            if (messageDisplay) {
                messageDisplay.textContent = '⏳ Đang gửi thông tin...';
                messageDisplay.style.color = '#0f75bd';
                messageDisplay.style.background = '#e6f3ff';
            }

            // Get country value
            let selectedQuocGia = document.getElementById('quoc_gia').value;
            const quocGiaKhacInput = document.getElementById('quoc_gia_khac');
            
            if (selectedQuocGia === 'Khác') {
                const customVal = quocGiaKhacInput ? quocGiaKhacInput.value.trim() : '';
                if (customVal !== '') {
                    selectedQuocGia = customVal;
                } else {
                    if (messageDisplay) {
                        messageDisplay.textContent = '❌ Vui lòng nhập tên quốc gia mong muốn.';
                        messageDisplay.style.color = '#dc3545';
                        messageDisplay.style.background = '#f8d7da';
                    }
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

            // Send to API
            fetch('/web8s/backend_api/insert.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if (messageDisplay) {
                    if (result.status) {
                        messageDisplay.textContent = '✅ Đăng ký thành công! Chúng tôi sẽ liên hệ sớm.';
                        messageDisplay.style.color = '#155724';
                        messageDisplay.style.background = '#d4edda';
                        registrationForm.reset();
                        if (quocGiaKhacBox) quocGiaKhacBox.style.display = 'none';
                    } else {
                        messageDisplay.textContent = '❌ Lỗi: ' + result.message;
                        messageDisplay.style.color = '#dc3545';
                        messageDisplay.style.background = '#f8d7da';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (messageDisplay) {
                    messageDisplay.textContent = '❌ Không thể kết nối đến máy chủ. Vui lòng kiểm tra XAMPP!';
                    messageDisplay.style.color = '#dc3545';
                    messageDisplay.style.background = '#f8d7da';
                }
            });
        });
    }

    // ============================================
    // 8. FADE IN ANIMATION ON SCROLL
    // ============================================
    const fadeElements = document.querySelectorAll('.fade-in');
    
    if (fadeElements.length > 0) {
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        fadeElements.forEach(el => fadeObserver.observe(el));
    }

    // ============================================
    // 9. IMAGE LAZY LOADING FALLBACK
    // ============================================
    const lazyImages = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window && lazyImages.length > 0) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }

});

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Format number with Vietnamese locale
function formatNumber(num) {
    return num.toLocaleString('vi-VN');
}

// Debounce function for performance
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
