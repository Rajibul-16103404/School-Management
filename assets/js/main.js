/**
 * Main Public Javascript
 * School Management Website
 */

document.addEventListener('DOMContentLoaded', () => {
    // Responsive navigation bar toggle
    const toggleBtn = document.querySelector('.nav-toggle');
    const navList = document.querySelector('.nav-list');
    
    if (toggleBtn && navList) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            navList.classList.toggle('show');
            
            // Toggle icon
            const icon = toggleBtn.querySelector('i');
            if (navList.classList.contains('show')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!navList.contains(e.target) && !toggleBtn.contains(e.target)) {
                navList.classList.remove('show');
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');
                }
            }
        });
    }

    // Notice ticker pause on hover
    const tickerText = document.querySelector('.ticker-text');
    if (tickerText) {
        tickerText.addEventListener('mouseenter', () => {
            tickerText.style.animationPlayState = 'paused';
        });
        tickerText.addEventListener('mouseleave', () => {
            tickerText.style.animationPlayState = 'running';
        });
    }
});
