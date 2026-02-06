// Main JavaScript for Courier Management System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize modals
    initModals();
    
    // Initialize status change functionality
    initStatusChange();
    
    // Initialize search
    initSearch();
    
    // Initialize notifications
    initNotifications();
});

// Tooltips
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.position = 'fixed';
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
            tooltip.style.left = (rect.left + (rect.width - tooltip.offsetWidth) / 2) + 'px';
            tooltip.style.backgroundColor = 'var(--sg-dark)';
            tooltip.style.color = 'white';
            tooltip.style.padding = '6px 12px';
            tooltip.style.borderRadius = '4px';
            tooltip.style.fontSize = '12px';
            tooltip.style.zIndex = '10000';
            
            this._tooltip = tooltip;
        });
        
        el.addEventListener('mouseleave', function() {
            if (this._tooltip) {
                this._tooltip.remove();
            }
        });
    });
}

// Modals
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloses = document.querySelectorAll('[data-modal-close]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    modalCloses.forEach(close => {
        close.addEventListener('click', () => {
            const modal = close.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close modal on backdrop click
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
}

// Status Change
function initStatusChange() {
    const statusButtons = document.querySelectorAll('.status-change-btn');
    
    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const currentStatus = this.getAttribute('data-current-status');
            
            // Show status change modal
            const modal = document.getElementById('statusChangeModal');
            if (modal) {
                // Populate status options based on workflow
                const statusSelect = modal.querySelector('#newStatus');
                if (statusSelect) {
                    // Clear existing options
                    statusSelect.innerHTML = '';
                    
                    // Get next possible statuses based on current status
                    const nextStatuses = getNextStatuses(currentStatus);
                    
                    nextStatuses.forEach(status => {
                        const option = document.createElement('option');
                        option.value = status;
                        option.textContent = status;
                        statusSelect.appendChild(option);
                    });
                }
                
                // Set order ID in hidden field
                const orderIdInput = modal.querySelector('#orderId');
                if (orderIdInput) {
                    orderIdInput.value = orderId;
                }
                
                // Show modal
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
}

// Get next possible statuses based on workflow
function getNextStatuses(currentStatus) {
    const workflow = {
        'NOUVEAU_COLIS': ['RECEIVED', 'PICKED_UP', 'CANCELED'],
        'RECEIVED': ['PICKED_UP', 'IN_PROGRESS', 'BV', 'NOANSWER'],
        'PICKED_UP': ['IN_PROGRESS', 'EN_VOYAGE'],
        'IN_PROGRESS': ['DISTRIBUTION', 'DELIVERED', 'UNREACHABLE', 'REFUSE'],
        'DISTRIBUTION': ['DELIVERED', 'UNREACHABLE', 'REFUSE', 'RETURNE'],
        'DELIVERED': [], // End state
        'RETURNE': ['RETURNED', 'RETURN_BY_AMANA'],
        'CANCELED': [] // End state
    };
    
    return workflow[currentStatus] || [];
}

// Search functionality
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            
            // If we're on orders list page
            const table = document.querySelector('.table tbody');
            if (table) {
                const rows = table.querySelectorAll('tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
        
        // Add search keyboard shortcut
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }
}

// Notifications
function initNotifications() {
    const notificationBtn = document.querySelector('.notification-btn');
    
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // Fetch and show notifications
            fetchNotifications();
        });
    }
}

function fetchNotifications() {
    // In a real app, this would fetch from an API
    const notifications = [
        { id: 1, text: 'New order #TRK-2024-001 created', time: '2 min ago', type: 'info' },
        { id: 2, text: 'Order #TRK-2024-002 status changed to DELIVERED', time: '1 hour ago', type: 'success' },
        { id: 3, text: 'COD payment of MAD 1500 received', time: '3 hours ago', type: 'warning' }
    ];
    
    showNotificationDropdown(notifications);
}

function showNotificationDropdown(notifications) {
    // Remove existing dropdown if any
    const existingDropdown = document.querySelector('.notifications-dropdown');
    if (existingDropdown) {
        existingDropdown.remove();
    }
    
    // Create dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'notifications-dropdown';
    dropdown.style.position = 'absolute';
    dropdown.style.top = '60px';
    dropdown.style.right = '20px';
    dropdown.style.width = '320px';
    dropdown.style.backgroundColor = 'white';
    dropdown.style.borderRadius = '12px';
    dropdown.style.boxShadow = '0 10px 40px rgba(0,0,0,0.2)';
    dropdown.style.zIndex = '1000';
    
    // Add header
    const header = document.createElement('div');
    header.style.padding = '16px';
    header.style.borderBottom = '1px solid var(--sg-light)';
    header.innerHTML = '<h4 style="margin: 0;">Notifications</h4>';
    dropdown.appendChild(header);
    
    // Add notifications
    const list = document.createElement('div');
    notifications.forEach(notif => {
        const item = document.createElement('div');
        item.style.padding = '12px 16px';
        item.style.borderBottom = '1px solid var(--sg-light)';
        item.style.cursor = 'pointer';
        item.style.transition = 'background 0.2s';
        
        item.innerHTML = `
            <div style="font-size: 14px;">${notif.text}</div>
            <div style="font-size: 12px; color: var(--sg-secondary); margin-top: 4px;">${notif.time}</div>
        `;
        
        item.addEventListener('mouseenter', () => {
            item.style.background = 'var(--sg-light)';
        });
        
        item.addEventListener('mouseleave', () => {
            item.style.background = 'white';
        });
        
        list.appendChild(item);
    });
    
    dropdown.appendChild(list);
    
    // Add footer
    const footer = document.createElement('div');
    footer.style.padding = '12px 16px';
    footer.style.textAlign = 'center';
    footer.style.borderTop = '1px solid var(--sg-light)';
    footer.innerHTML = '<a href="#" style="color: var(--sg-primary); text-decoration: none; font-size: 14px;">View all notifications</a>';
    dropdown.appendChild(footer);
    
    document.body.appendChild(dropdown);
    
    // Close dropdown when clicking outside
    setTimeout(() => {
        document.addEventListener('click', function closeDropdown(e) {
            if (!dropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
                dropdown.remove();
                document.removeEventListener('click', closeDropdown);
            }
        });
    }, 0);
}

// Form validation
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'var(--sg-primary)';
            isValid = false;
            
            // Add error message
            if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                const error = document.createElement('div');
                error.className = 'error-message';
                error.textContent = 'This field is required';
                error.style.fontSize = '12px';
                error.style.color = 'var(--sg-primary)';
                error.style.marginTop = '4px';
                field.parentNode.insertBefore(error, field.nextSibling);
            }
        } else {
            field.style.borderColor = 'var(--sg-light)';
            
            // Remove error message
            if (field.nextElementSibling && field.nextElementSibling.classList.contains('error-message')) {
                field.nextElementSibling.remove();
            }
        }
    });
    
    return isValid;
}

// Export functions for global use
window.CMS = {
    validateForm,
    showNotification: function(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '12px 24px';
        notification.style.backgroundColor = type === 'success' ? '#10b981' : 
                                           type === 'error' ? '#ef4444' : 
                                           type === 'warning' ? '#f59e0b' : '#3b82f6';
        notification.style.color = 'white';
        notification.style.borderRadius = '8px';
        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        notification.style.zIndex = '10000';
        notification.style.transform = 'translateX(120%)';
        notification.style.transition = 'transform 0.3s ease';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(120%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
        
        // Allow manual close
        notification.style.cursor = 'pointer';
        notification.addEventListener('click', () => {
            notification.style.transform = 'translateX(120%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
    }
};
