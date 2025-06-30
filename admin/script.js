// script.js

const sidebar = document.querySelector('.sidebar');
const sidebarOverlay = document.querySelector('.sidebar-overlay');
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

function toggleMobileMenu() {
  sidebar.classList.toggle('mobile-open');
  sidebarOverlay.classList.toggle('show');
}

// Open sidebar on mobile when clicking the menu button
if (mobileMenuBtn) {
  mobileMenuBtn.addEventListener('click', toggleMobileMenu);
}

// Close sidebar when clicking on the overlay
if (sidebarOverlay) {
  sidebarOverlay.addEventListener('click', toggleMobileMenu);
}

// Optional: close sidebar when a nav link is clicked (mobile)
document.querySelectorAll('.sidebar nav a').forEach(link => {
  link.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
      sidebar.classList.remove('mobile-open');
      sidebarOverlay.classList.remove('show');
    }
  });
});
