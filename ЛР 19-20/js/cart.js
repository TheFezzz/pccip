// cart.js
class ShoppingCart {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('createxCart')) || [];
        this.services = [];
        this.init();
    }

    init() {
        this.loadServices();
        this.renderCatalog();
        this.renderCart();
        this.setupEventListeners();
    }

    async loadServices() {
        try {
            // Загружаем данные из JSON файла
            const response = await fetch('./services.json');
            const data = await response.json();
            this.services = data.services;
            this.renderCatalog();
        } catch (error) {
            console.error('Error loading services:', error);
            // Если файл не загружается, используем тестовые данные
            this.services = this.getDefaultServices();
            this.renderCatalog();
        }
    }

    getDefaultServices() {
        return [
            {
                id: 1,
                name: "Construction",
                description: "Full construction services from planning to completion",
                price: 50000,
                duration: "3-6 months",
                image: "./img/our1.svg",
                category: "construction"
            },
            {
                id: 2,
                name: "Project Development",
                description: "Professional project management and development",
                price: 35000,
                duration: "2-4 months",
                image: "./img/our2.svg",
                category: "development"
            },
            {
                id: 3,
                name: "Interior Design",
                description: "Creative interior design solutions",
                price: 25000,
                duration: "1-2 months",
                image: "./img/our3.svg",
                category: "design"
            },
            {
                id: 4,
                name: "Repairs",
                description: "Quality repair and maintenance services",
                price: 15000,
                duration: "2-8 weeks",
                image: "./img/our1.svg",
                category: "repairs"
            },
            {
                id: 5,
                name: "Consultation",
                description: "Expert construction consultation",
                price: 5000,
                duration: "1-2 weeks",
                image: "./img/our2.svg",
                category: "consultation"
            },
            {
                id: 6,
                name: "Renovation",
                description: "Complete building renovation services",
                price: 45000,
                duration: "2-5 months",
                image: "./img/our3.svg",
                category: "renovation"
            }
        ];
    }

    renderCatalog() {
        const grid = document.getElementById('servicesGrid');
        if (!grid) return;

        grid.innerHTML = this.services.map(service => `
            <div class="service-card" data-category="${service.category}">
                <img src="${service.image}" alt="${service.name}" onerror="this.src='./img/our1.svg'">
                <h3>${service.name}</h3>
                <p>${service.description}</p>
                <div class="service-price">$${service.price.toLocaleString()}</div>
                <div class="service-duration">Duration: ${service.duration}</div>
                <button class="add-to-cart" data-id="${service.id}">
                    Add to Cart
                </button>
            </div>
        `).join('');
    }

    renderCart() {
        this.updateCartCount();
        this.renderCartItems();
        this.updateCartTotal();
    }

    renderCartItems() {
        const cartItems = document.getElementById('cartItems');
        if (!cartItems) return;

        if (this.cart.length === 0) {
            cartItems.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
            return;
        }

        cartItems.innerHTML = this.cart.map(item => {
            const service = this.services.find(s => s.id === item.id);
            if (!service) return '';
            
            return `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <h4>${service.name}</h4>
                        <div class="price">$${service.price.toLocaleString()}</div>
                    </div>
                    <div class="cart-item-actions">
                        <button class="quantity-btn minus" data-id="${item.id}">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="quantity-btn plus" data-id="${item.id}">+</button>
                        <button class="remove-btn" data-id="${item.id}">Remove</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    addToCart(serviceId) {
        const service = this.services.find(s => s.id === serviceId);
        if (!service) {
            console.error('Service not found:', serviceId);
            return;
        }

        const existingItem = this.cart.find(item => item.id === serviceId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            this.cart.push({
                id: serviceId,
                quantity: 1,
                name: service.name,
                price: service.price
            });
        }

        this.saveCart();
        this.renderCart();
        this.showNotification(`"${service.name}" added to cart!`);
        
        // Анимация добавления в корзину
        this.animateAddToCart(serviceId);
    }

    removeFromCart(serviceId) {
        const service = this.services.find(s => s.id === serviceId);
        this.cart = this.cart.filter(item => item.id !== serviceId);
        this.saveCart();
        this.renderCart();
        
        if (service) {
            this.showNotification(`"${service.name}" removed from cart!`);
        }
    }

    updateQuantity(serviceId, change) {
        const item = this.cart.find(item => item.id === serviceId);
        if (item) {
            item.quantity += change;
            if (item.quantity <= 0) {
                this.removeFromCart(serviceId);
            } else {
                this.saveCart();
                this.renderCart();
            }
        }
    }

    updateCartCount() {
        const cartCount = document.getElementById('cartCount');
        if (cartCount) {
            const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
            cartCount.textContent = totalItems;
            
            // Анимация счетчика
            if (totalItems > 0) {
                cartCount.style.display = 'flex';
                cartCount.style.animation = 'pulse 0.5s ease-in-out';
                setTimeout(() => {
                    cartCount.style.animation = '';
                }, 500);
            } else {
                cartCount.style.display = 'flex';
            }
        }
    }

    updateCartTotal() {
        const cartTotal = document.getElementById('cartTotal');
        if (cartTotal) {
            const total = this.cart.reduce((sum, item) => {
                const service = this.services.find(s => s.id === item.id);
                return sum + (service ? service.price * item.quantity : 0);
            }, 0);
            cartTotal.textContent = total.toLocaleString();
        }
    }

    clearCart() {
        if (this.cart.length === 0) {
            this.showNotification('Cart is already empty!', 'info');
            return;
        }
        
        this.cart = [];
        this.saveCart();
        this.renderCart();
        this.showNotification('Cart cleared!', 'info');
    }

    saveCart() {
        localStorage.setItem('createxCart', JSON.stringify(this.cart));
    }

    showNotification(message, type = 'success') {
        // Удаляем существующие уведомления
        document.querySelectorAll('.notification').forEach(notif => notif.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#03CEA4' : type === 'error' ? '#F52F6E' : '#5A87FC'};
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            z-index: 1003;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }

    animateAddToCart(serviceId) {
        const serviceCard = document.querySelector(`.add-to-cart[data-id="${serviceId}"]`);
        if (!serviceCard) return;

        // Анимация кнопки
        serviceCard.style.transform = 'scale(0.95)';
        serviceCard.style.backgroundColor = '#03CEA4';
        serviceCard.textContent = 'Added!';
        
        setTimeout(() => {
            serviceCard.style.transform = 'scale(1)';
            serviceCard.style.backgroundColor = '';
            serviceCard.textContent = 'Add to Cart';
        }, 600);
    }

    setupEventListeners() {
        // Добавление в корзину
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart')) {
                const serviceId = parseInt(e.target.dataset.id);
                this.addToCart(serviceId);
            }

            // Открытие/закрытие корзины
            if (e.target.closest('#cartIcon')) {
                this.toggleCart();
            }

            if (e.target.id === 'cartClose' || e.target.closest('#cartOverlay')) {
                this.closeCart();
            }

            // Управление количеством
            if (e.target.classList.contains('plus')) {
                const serviceId = parseInt(e.target.dataset.id);
                this.updateQuantity(serviceId, 1);
            }

            if (e.target.classList.contains('minus')) {
                const serviceId = parseInt(e.target.dataset.id);
                this.updateQuantity(serviceId, -1);
            }

            // Удаление из корзины
            if (e.target.classList.contains('remove-btn')) {
                const serviceId = parseInt(e.target.dataset.id);
                this.removeFromCart(serviceId);
            }

            // Очистка корзины
            if (e.target.id === 'clearCartBtn') {
                if (confirm('Are you sure you want to clear your cart?')) {
                    this.clearCart();
                }
            }

            // Оформление заказа
            if (e.target.id === 'checkoutBtn') {
                this.checkout();
            }

            // Фильтры каталога
            if (e.target.classList.contains('filter-btn')) {
                this.filterServices(e.target.dataset.filter);
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                e.target.classList.add('active');
            }
        });

        // Закрытие корзины по ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeCart();
            }
        });
    }

    toggleCart() {
        const cartSidebar = document.getElementById('cartSidebar');
        const cartOverlay = document.getElementById('cartOverlay');
        
        cartSidebar.classList.toggle('active');
        cartOverlay.classList.toggle('active');
        
        // Блокировка прокрутки body при открытой корзине
        document.body.style.overflow = cartSidebar.classList.contains('active') ? 'hidden' : '';
    }

    closeCart() {
        const cartSidebar = document.getElementById('cartSidebar');
        const cartOverlay = document.getElementById('cartOverlay');
        
        cartSidebar.classList.remove('active');
        cartOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    filterServices(category) {
        const cards = document.querySelectorAll('.service-card');
        cards.forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'block';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });
    }

    checkout() {
        if (this.cart.length === 0) {
            this.showNotification('Your cart is empty!', 'error');
            return;
        }

        const total = this.cart.reduce((sum, item) => {
            const service = this.services.find(s => s.id === item.id);
            return sum + (service ? service.price * item.quantity : 0);
        }, 0);

        const orderDetails = this.cart.map(item => {
            const service = this.services.find(s => s.id === item.id);
            return `${service.name} x${item.quantity} - $${(service.price * item.quantity).toLocaleString()}`;
        }).join('\n');

        alert(`Thank you for your order!\n\nOrder Summary:\n${orderDetails}\n\nTotal: $${total.toLocaleString()}\n\nWe will contact you shortly to discuss your project.`);
        
        // Очистка корзины после оформления заказа
        this.clearCart();
        this.closeCart();
    }
}

// Инициализация корзины при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    window.shoppingCart = new ShoppingCart();
});