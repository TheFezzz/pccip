// ===== 1. СОБЫТИЯ МЫШИ =====
document.addEventListener('DOMContentLoaded', function() {
    // Эффект при наведении на карточки услуг
    const serviceItems = document.querySelectorAll('.service__item');
    
    serviceItems.forEach(item => {
        // Событие mouseenter
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        // Событие mouseleave
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        // Событие mousedown (нажатие кнопки мыши)
        item.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(-5px) scale(0.98)';
        });
        
        // Событие mouseup (отпускание кнопки мыши)
        item.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(-10px) scale(1)';
        });
    });
    
    // Контекстное меню (событие contextmenu)
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        alert('Контекстное меню отключено на этом сайте');
    });
});

// ===== 2. СОБЫТИЯ КЛАВИАТУРЫ =====
document.addEventListener('keydown', function(e) {
    // Навигация по сайту с помощью клавиш
    switch(e.key) {
        case 'ArrowUp':
            window.scrollBy(0, -100);
            break;
        case 'ArrowDown':
            window.scrollBy(0, 100);
            break;
        case 'Home':
            window.scrollTo(0, 0);
            break;
        case 'End':
            window.scrollTo(0, document.body.scrollHeight);
            break;
        case 's': // Поиск при нажатии S
            if (e.ctrlKey) {
                e.preventDefault();
                const search = prompt('Введите текст для поиска:');
                if (search) {
                    highlightText(search);
                }
            }
            break;
    }
});

// Функция подсветки текста
function highlightText(searchText) {
    const bodyText = document.body.innerHTML;
    const regex = new RegExp(searchText, 'gi');
    const highlightedText = bodyText.replace(regex, 
        match => `<span style="background-color: yellow;">${match}</span>`);
    document.body.innerHTML = highlightedText;
}

// ===== 3. DRAG&DROP СОБЫТИЯ =====
// Создадим перетаскиваемую галерею изображений
function initDragDrop() {
    const gallery = document.querySelector('.project-3__list');
    if (!gallery) return;
    
    let draggedItem = null;
    
    // Создаем перетаскиваемые элементы
    gallery.querySelectorAll('.project-3__items').forEach(item => {
        item.setAttribute('draggable', 'true');
        
        // Событие начала перетаскивания
        item.addEventListener('dragstart', function(e) {
            draggedItem = this;
            setTimeout(() => this.style.opacity = '0.5', 0);
            e.dataTransfer.effectAllowed = 'move';
        });
        
        // Событие окончания перетаскивания
        item.addEventListener('dragend', function() {
            this.style.opacity = '1';
            draggedItem = null;
        });
        
        // Событие при наведении на зону drop
        item.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        });
        
        // Событие входа в зону drop
        item.addEventListener('dragenter', function(e) {
            e.preventDefault();
            this.style.backgroundColor = 'rgba(255, 90, 48, 0.1)';
        });
        
        // Событие выхода из зоны drop
        item.addEventListener('dragleave', function() {
            this.style.backgroundColor = '';
        });
        
        // Событие drop
        item.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.backgroundColor = '';
            
            if (draggedItem && draggedItem !== this) {
                const allItems = Array.from(gallery.querySelectorAll('.project-3__items'));
                const draggedIndex = allItems.indexOf(draggedItem);
                const targetIndex = allItems.indexOf(this);
                
                if (draggedIndex < targetIndex) {
                    gallery.insertBefore(draggedItem, this.nextSibling);
                } else {
                    gallery.insertBefore(draggedItem, this);
                }
            }
        });
    });
}

// ===== 4. СОБЫТИЯ УКАЗАТЕЛЯ =====
// Кастомный курсор
function initCustomCursor() {
    const cursor = document.createElement('div');
    cursor.style.cssText = `
        position: fixed;
        width: 20px;
        height: 20px;
        background: rgba(255, 90, 48, 0.7);
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        transition: transform 0.1s ease;
        mix-blend-mode: difference;
    `;
    document.body.appendChild(cursor);
    
    document.addEventListener('pointermove', function(e) {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
    });
    
    // Изменение курсора при наведении на кликабельные элементы
    document.addEventListener('pointerover', function(e) {
        if (e.target.matches('a, button, .service__item, .project-3__items')) {
            cursor.style.transform = 'scale(1.5)';
        }
    });
    
    document.addEventListener('pointerout', function(e) {
        if (e.target.matches('a, button, .service__item, .project-3__items')) {
            cursor.style.transform = 'scale(1)';
        }
    });
}

// ===== 5. СОБЫТИЯ ПОЛОСЫ ПРОКРУТКИ =====
// Параллакс-эффект при прокрутке
function initScrollEffects() {
    const parallaxElements = document.querySelectorAll('.bg-hero, .service__item');
    
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        parallaxElements.forEach(el => {
            el.style.transform = `translateY(${rate}px)`;
        });
        
        // Изменение прозрачности хедера при прокрутке
        const header = document.querySelector('header');
        if (scrolled > 100) {
            header.style.backgroundColor = 'rgba(30, 33, 44, 0.9)';
            header.style.backdropFilter = 'blur(10px)';
        } else {
            header.style.backgroundColor = '';
            header.style.backdropFilter = '';
        }
        
        // Прогресс-бар прокрутки
        updateScrollProgress();
    });
    
    // Прогресс-бар прокрутки
    function updateScrollProgress() {
        let progressBar = document.getElementById('scroll-progress');
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.id = 'scroll-progress';
            progressBar.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                height: 3px;
                background: #ff5a30;
                z-index: 10000;
                transition: width 0.1s ease;
            `;
            document.body.appendChild(progressBar);
        }
        
        const winHeight = window.innerHeight;
        const docHeight = document.documentElement.scrollHeight;
        const scrollTop = window.pageYOffset;
        const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
        
        progressBar.style.width = scrollPercent + '%';
    }
}

// ===== 6. СОБЫТИЯ СЕНСОРНЫХ ЭКРАНОВ =====
// Свайп-навигация для мобильных устройств
function initTouchEvents() {
    let startX = 0;
    let startY = 0;
    let endX = 0;
    let endY = 0;
    
    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchmove', function(e) {
        endX = e.touches[0].clientX;
        endY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchend', function() {
        const diffX = startX - endX;
        const diffY = startY - endY;
        
        // Горизонтальный свайп (навигация)
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
            if (diffX > 0) {
                // Свайп влево - следующая страница/слайд
                navigateSwipe('next');
            } else {
                // Свайп вправо - предыдущая страница/слайд
                navigateSwipe('prev');
            }
        }
        
        // Вертикальный свайп (скролл к секциям)
        if (Math.abs(diffY) > Math.abs(diffX) && Math.abs(diffY) > 100) {
            if (diffY > 0) {
                // Свайп вверх
                scrollToSection('down');
            } else {
                // Свайп вниз
                scrollToSection('up');
            }
        }
    });
    
    function navigateSwipe(direction) {
        // Здесь можно добавить логику навигации по слайдам
        console.log('Свайп:', direction);
    }
    
    function scrollToSection(direction) {
        const sections = document.querySelectorAll('section');
        const currentScroll = window.pageYOffset;
        
        if (direction === 'down') {
            // Прокрутка к следующей секции
            for (let section of sections) {
                if (section.offsetTop > currentScroll + 100) {
                    window.scrollTo({top: section.offsetTop, behavior: 'smooth'});
                    break;
                }
            }
        } else {
            // Прокрутка к предыдущей секции
            for (let i = sections.length - 1; i >= 0; i--) {
                if (sections[i].offsetTop < currentScroll - 100) {
                    window.scrollTo({top: sections[i].offsetTop, behavior: 'smooth'});
                    break;
                }
            }
        }
    }
}

// ===== 7. СОБЫТИЯ, СВЯЗАННЫЕ С ТАЙМЕРОМ =====
// Автоматическая прокрутка слайдера
function initAutoSlider() {
    const slider = document.querySelector('.slider');
    if (!slider) return;
    
    let currentSlide = 0;
    const slides = slider.querySelectorAll('.slider__item');
    const totalSlides = slides.length;
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
    }
    
    function updateSlider() {
        const offset = -currentSlide * 100;
        slider.style.transform = `translateX(${offset}%)`;
    }
    
    // Автопрокрутка каждые 5 секунд
    setInterval(nextSlide, 5000);
    
    // Пауза при наведении
    slider.addEventListener('mouseenter', function() {
        clearInterval(sliderInterval);
    });
    
    slider.addEventListener('mouseleave', function() {
        sliderInterval = setInterval(nextSlide, 5000);
    });
    
    let sliderInterval = setInterval(nextSlide, 5000);
}

// Таймер обратного отсчета для акции
function initCountdownTimer() {
    const timerContainer = document.createElement('div');
    timerContainer.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: #ff5a30;
        color: white;
        padding: 15px;
        border-radius: 5px;
        z-index: 1000;
        font-family: Ubuntu, sans-serif;
    `;
    timerContainer.innerHTML = '<h3>Специальное предложение!</h3><div id="countdown">10:00</div>';
    document.body.appendChild(timerContainer);
    
    let timeLeft = 10 * 60; // 10 минут в секундах
    
    const countdown = setInterval(function() {
        timeLeft--;
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        document.getElementById('countdown').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerContainer.innerHTML = '<p>Акция завершена!</p>';
        }
    }, 1000);
}

// ===== ИНИЦИАЛИЗАЦИЯ ВСЕХ СКРИПТОВ =====
document.addEventListener('DOMContentLoaded', function() {
    initDragDrop();
    initCustomCursor();
    initScrollEffects();
    initTouchEvents();
    initAutoSlider();
    initCountdownTimer();
    
    // Добавляем кнопку "Наверх" с плавной прокруткой
    const gotopBtn = document.getElementById('gotop-1');
    if (gotopBtn) {
        gotopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});