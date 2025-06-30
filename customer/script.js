// Clean Hotel Booking System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeMobileMenu();
    initializeDateInputs();
    initializeFormValidation();
    initializeSearchForm();
    hideAlertsAfterDelay();
});

// Mobile Menu
function initializeMobileMenu() {
    const mobileToggle = document.createElement('button');
    mobileToggle.className = 'mobile-menu-toggle';
    mobileToggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
    mobileToggle.setAttribute('aria-label', 'Toggle navigation menu');
    
    document.body.insertBefore(mobileToggle, document.body.firstChild);
    
    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;
    
    mobileToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        const icon = this.querySelector('i');
        icon.className = sidebar.classList.contains('show') ? 
            'fa-solid fa-times' : 'fa-solid fa-bars';
    });
    
    // Close on outside click
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !mobileToggle.contains(e.target)) {
            sidebar.classList.remove('show');
            mobileToggle.querySelector('i').className = 'fa-solid fa-bars';
        }
    });
    
    // Handle resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            mobileToggle.querySelector('i').className = 'fa-solid fa-bars';
        }
    });
}

// Date Input Handling
function initializeDateInputs() {
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    
    // Set minimum dates
    const checkInInputs = document.querySelectorAll('input[name="check_in"]');
    const checkOutInputs = document.querySelectorAll('input[name="check_out"]');
    
    checkInInputs.forEach(input => {
        input.min = today;
        input.addEventListener('change', updateCheckOutDate);
    });
    
    checkOutInputs.forEach(input => {
        input.min = tomorrow;
    });
    
    function updateCheckOutDate() {
        const checkInValue = this.value;
        if (!checkInValue) return;
        
        const checkInDate = new Date(checkInValue);
        const nextDay = new Date(checkInDate.getTime() + 86400000);
        const minCheckOut = nextDay.toISOString().split('T')[0];
        
        checkOutInputs.forEach(output => {
            output.min = minCheckOut;
            if (output.value && output.value <= checkInValue) {
                output.value = '';
            }
        });
    }
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const checkIn = form.querySelector('input[name="check_in"]');
            const checkOut = form.querySelector('input[name="check_out"]');
            
            if (checkIn && checkOut && checkIn.value && checkOut.value) {
                if (checkIn.value >= checkOut.value) {
                    e.preventDefault();
                    showNotification('Check-out date must be after check-in date', 'error');
                    return false;
                }
                
                const today = new Date().toISOString().split('T')[0];
                if (checkIn.value < today) {
                    e.preventDefault();
                    showNotification('Check-in date cannot be in the past', 'error');
                    return false;
                }
            }
            
            // Add loading state to submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && form.checkValidity()) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Reset after 10 seconds as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            }
        });
    });
}

// Search Form Enhancement
function initializeSearchForm() {
    const searchForm = document.querySelector('.search-dates');
    if (!searchForm) return;
    
    // Wrap inputs in divs for better styling
    const inputs = searchForm.querySelectorAll('input[type="date"]');
    inputs.forEach(input => {
        if (!input.parentElement.querySelector('label')) {
            const wrapper = document.createElement('div');
            const label = document.createElement('label');
            
            label.textContent = input.name === 'check_in' ? 'Check-in Date' : 'Check-out Date';
            label.setAttribute('for', input.name);
            
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(label);
            wrapper.appendChild(input);
        }
    });
}

// Notification System
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existing = document.querySelectorAll('.notification');
    existing.forEach(n => n.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fa-solid fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Hide alerts after delay
function hideAlertsAfterDelay() {
    const alerts = document.querySelectorAll('.alert-success');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

// Add notification styles
const notificationStyles = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        border: 1px solid #e2e8f0;
        min-width: 320px;
        max-width: 400px;
        opacity: 1;
        transition: opacity 0.3s ease;
        animation: slideInRight 0.3s ease;
    }
    
    .notification-content {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
    }
    
    .notification-success {
        border-left: 4px solid #10b981;
    }
    
    .notification-error {
        border-left: 4px solid #ef4444;
    }
    
    .notification-warning {
        border-left: 4px solid #f59e0b;
    }
    
    .notification-info {
        border-left: 4px solid #3b82f6;
    }
    
    .notification-success .fa-check-circle {
        color: #10b981;
    }
    
    .notification-error .fa-exclamation-circle {
        color: #ef4444;
    }
    
    .notification-warning .fa-exclamation-triangle {
        color: #f59e0b;
    }
    
    .notification-info .fa-info-circle {
        color: #3b82f6;
    }
    
    .notification-close {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: #64748b;
        margin-left: auto;
    }
    
    .notification-close:hover {
        color: #1e293b;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @media (max-width: 480px) {
        .notification {
            right: 10px;
            left: 10px;
            min-width: auto;
        }
    }
`;

// Add styles to head
const styleSheet = document.createElement('style');
styleSheet.textContent = notificationStyles;
document.head.appendChild(styleSheet);