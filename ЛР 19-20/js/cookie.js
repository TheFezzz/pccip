// cookie.js
class CookieManager {
    // Set cookie
    static setCookie(name, value, days = 365) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires.toUTCString()};path=/`;
    }

    // Get cookie
    static getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
        }
        return null;
    }

    // Delete cookie
    static deleteCookie(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
    }

    // Clear all cookies
    static clearAllCookies() {
        const cookies = document.cookie.split(";");
        for (let i = 0; i < cookies.length; i++) {
            const cookie = cookies[i];
            const eqPos = cookie.indexOf("=");
            const name = eqPos > -1 ? cookie.substr(0, eqPos).trim() : cookie.trim();
            this.deleteCookie(name);
        }
    }
}

class UserProfile {
    constructor() {
        this.init();
    }
    
    init() {
        this.loadFromCookie();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Save button
        document.getElementById('saveProfileBtn')?.addEventListener('click', () => {
            this.saveProfile();
        });

        // Load button
        document.getElementById('loadProfileBtn')?.addEventListener('click', () => {
            this.loadProfile();
        });

        // Clear button
        document.getElementById('clearProfileBtn')?.addEventListener('click', () => {
            this.clearProfile();
        });
    }

    saveProfile() {
        const profile = {
            fullName: document.getElementById('fullName').value,
            email: document.getElementById('email').value,
            birthDate: document.getElementById('birthDate').value,
            birthPlace: document.getElementById('birthPlace').value,
            hobbies: document.getElementById('hobbies').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value
        };

        // Save to cookie
        CookieManager.setCookie('userProfile', JSON.stringify(profile));
        
        this.showNotification('Profile saved to cookies!', 'success');
    }

    loadProfile() {
        const profileData = CookieManager.getCookie('userProfile');
        
        if (profileData) {
            const profile = JSON.parse(profileData);
            
            document.getElementById('fullName').value = profile.fullName || '';
            document.getElementById('email').value = profile.email || '';
            document.getElementById('birthDate').value = profile.birthDate || '';
            document.getElementById('birthPlace').value = profile.birthPlace || '';
            document.getElementById('hobbies').value = profile.hobbies || '';
            document.getElementById('phone').value = profile.phone || '';
            document.getElementById('address').value = profile.address || '';
            
            this.showNotification('Profile loaded from cookies!', 'success');
        } else {
            this.showNotification('No profile data found in cookies!', 'error');
        }
    }

    clearProfile() {
        CookieManager.deleteCookie('userProfile');
        
        // Clear form
        document.getElementById('fullName').value = '';
        document.getElementById('email').value = '';
        document.getElementById('birthDate').value = '';
        document.getElementById('birthPlace').value = '';
        document.getElementById('hobbies').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('address').value = '';
        
        this.showNotification('Profile data cleared from cookies!', 'success');
    }

    loadFromCookie() {
        // Auto-load profile when page loads
        this.loadProfile();
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `cookie-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 12px 24px;
            border-radius: 4px;
            color: white;
            z-index: 1004;
            font-weight: 600;
            animation: slideDown 0.3s ease;
        `;

        if (type === 'success') {
            notification.style.background = '#03CEA4';
        } else if (type === 'error') {
            notification.style.background = '#F52F6E';
        } else {
            notification.style.background = '#5A87FC';
        }

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Add CSS for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideDown {
        from { transform: translate(-50%, -100%); opacity: 0; }
        to { transform: translate(-50%, 0); opacity: 1; }
    }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(notificationStyles);

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.userProfile = new UserProfile();
});