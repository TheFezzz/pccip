// localStorage.js
class LocalStorageManager {
    // Set item
    static setItem(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (error) {
            console.error('Error saving to localStorage:', error);
            return false;
        }
    }

    // Get item
    static getItem(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error('Error reading from localStorage:', error);
            return null;
        }
    }

    // Remove item
    static removeItem(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Error removing from localStorage:', error);
            return false;
        }
    }

    // Clear all
    static clear() {
        try {
            localStorage.clear();
            return true;
        } catch (error) {
            console.error('Error clearing localStorage:', error);
            return false;
        }
    }

    // Get all keys
    static getAllKeys() {
        try {
            return Object.keys(localStorage);
        } catch (error) {
            console.error('Error getting localStorage keys:', error);
            return [];
        }
    }
}

class UserProfileLocalStorage {
    constructor() {
        this.storageKey = 'userProfileLS';
        this.init();
    }

    init() {
        this.loadFromLocalStorage();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Save button
        document.getElementById('saveProfileLSBtn')?.addEventListener('click', () => {
            this.saveProfile();
        });

        // Load button
        document.getElementById('loadProfileLSBtn')?.addEventListener('click', () => {
            this.loadProfile();
        });

        // Clear button
        document.getElementById('clearProfileLSBtn')?.addEventListener('click', () => {
            this.clearProfile();
        });

        // Export button
        document.getElementById('exportProfileBtn')?.addEventListener('click', () => {
            this.exportProfile();
        });

        // Import button
        document.getElementById('importProfileBtn')?.addEventListener('click', () => {
            this.importProfile();
        });
    }

    saveProfile() {
        const profile = {
            fullName: document.getElementById('fullNameLS').value,
            email: document.getElementById('emailLS').value,
            birthDate: document.getElementById('birthDateLS').value,
            birthPlace: document.getElementById('birthPlaceLS').value,
            hobbies: document.getElementById('hobbiesLS').value,
            phone: document.getElementById('phoneLS').value,
            address: document.getElementById('addressLS').value,
            lastUpdated: new Date().toISOString()
        };

        if (LocalStorageManager.setItem(this.storageKey, profile)) {
            this.showNotification('Profile saved to Local Storage!', 'success');
            this.updateLastSaved();
        } else {
            this.showNotification('Error saving profile!', 'error');
        }
    }

    loadProfile() {
        const profile = LocalStorageManager.getItem(this.storageKey);
        
        if (profile) {
            document.getElementById('fullNameLS').value = profile.fullName || '';
            document.getElementById('emailLS').value = profile.email || '';
            document.getElementById('birthDateLS').value = profile.birthDate || '';
            document.getElementById('birthPlaceLS').value = profile.birthPlace || '';
            document.getElementById('hobbiesLS').value = profile.hobbies || '';
            document.getElementById('phoneLS').value = profile.phone || '';
            document.getElementById('addressLS').value = profile.address || '';
            
            this.showNotification('Profile loaded from Local Storage!', 'success');
            this.updateLastSaved(profile.lastUpdated);
        } else {
            this.showNotification('No profile data found in Local Storage!', 'error');
        }
    }

    clearProfile() {
        if (LocalStorageManager.removeItem(this.storageKey)) {
            document.getElementById('fullNameLS').value = '';
            document.getElementById('emailLS').value = '';
            document.getElementById('birthDateLS').value = '';
            document.getElementById('birthPlaceLS').value = '';
            document.getElementById('hobbiesLS').value = '';
            document.getElementById('phoneLS').value = '';
            document.getElementById('addressLS').value = '';
            
            this.showNotification('Profile data cleared from Local Storage!', 'success');
            this.updateLastSaved();
        } else {
            this.showNotification('Error clearing profile!', 'error');
        }
    }

    exportProfile() {
        const profile = LocalStorageManager.getItem(this.storageKey);
        
        if (profile) {
            const dataStr = JSON.stringify(profile, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = 'user-profile.json';
            link.click();
            
            this.showNotification('Profile exported successfully!', 'success');
        } else {
            this.showNotification('No profile data to export!', 'error');
        }
    }

    importProfile() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';
        
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    try {
                        const profile = JSON.parse(event.target.result);
                        
                        if (LocalStorageManager.setItem(this.storageKey, profile)) {
                            this.loadProfile();
                            this.showNotification('Profile imported successfully!', 'success');
                        } else {
                            this.showNotification('Error importing profile!', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Invalid file format!', 'error');
                    }
                };
                reader.readAsText(file);
            }
        };
        
        input.click();
    }

    loadFromLocalStorage() {
        // Auto-load profile when page loads
        this.loadProfile();
    }

    updateLastSaved(timestamp = null) {
        const lastSavedElement = document.getElementById('lastSaved');
        if (lastSavedElement) {
            if (timestamp) {
                const date = new Date(timestamp);
                lastSavedElement.textContent = `Last saved: ${date.toLocaleString()}`;
                lastSavedElement.style.display = 'block';
            } else {
                lastSavedElement.style.display = 'none';
            }
        }
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `ls-notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 80px;
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.userProfileLS = new UserProfileLocalStorage();
});