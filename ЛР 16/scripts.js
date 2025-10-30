// Общие скрипты для всех заданий

// Задание 2
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, находимся ли мы на странице задания 2
    if (document.getElementById('task2-content')) {
        // Выводим ширину документа через 5 секунд
        setTimeout(function() {
            const width = document.documentElement.scrollWidth;
            const resultDiv = document.getElementById('task2-result');
            resultDiv.innerHTML += `<p>Ширина документа: ${width}px (выведено через 5 секунд)</p>`;
        }, 5000);
        
        // Клонируем узел №3 и вставляем после узла №1
        const node1 = document.getElementById('node1');
        const node3 = document.getElementById('node3');
        const clonedNode = node3.cloneNode(true);
        node1.parentNode.insertBefore(clonedNode, node1.nextSibling);
        
        // Добавляем пояснение
        const explanation = document.createElement('p');
        explanation.textContent = "Узел №3 был скопирован и вставлен после узла №1.";
        explanation.style.color = 'green';
        node1.parentNode.insertBefore(explanation, clonedNode.nextSibling);
    }
    
    // Задание 4
    if (document.getElementById('showProcessorInfo')) {
        document.getElementById('showProcessorInfo').addEventListener('click', function() {
            const cpuInfo = navigator.hardwareConcurrency || "Не удалось определить";
            const newWindow = window.open('', 'ProcessorInfo', 'width=400,height=300');
            
            function updateInfo() {
                if (newWindow.closed) {
                    clearInterval(intervalId);
                    return;
                }
                newWindow.document.body.innerHTML = `
                    <h2>Информация о процессоре</h2>
                    <p>Количество ядер: ${cpuInfo}</p>
                    <p>Обновлено: ${new Date().toLocaleTimeString()}</p>
                    <p>Окно закроется автоматически через 30 секунд</p>
                `;
            }
            
            updateInfo();
            const intervalId = setInterval(updateInfo, 4000);
            
            // Автоматическое закрытие через 30 секунд
            setTimeout(() => {
                if (!newWindow.closed) {
                    newWindow.close();
                }
            }, 30000);
        });
    }
    
    // Задание 5
    if (document.getElementById('deliveryForm')) {
        const form = document.getElementById('deliveryForm');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;
            
            // Сбрасываем предыдущие ошибки
            document.querySelectorAll('.error').forEach(el => {
                el.classList.remove('error');
            });
            document.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
            });
            
            // Проверка имени
            const name = document.getElementById('name');
            if (!name.value.trim()) {
                name.classList.add('error');
                document.getElementById('name-error').textContent = 'Поле обязательно для заполнения';
                isValid = false;
            }
            
            // Проверка страны
            const country = document.getElementById('country');
            if (!country.value) {
                country.classList.add('error');
                document.getElementById('country-error').textContent = 'Пожалуйста, выберите страну';
                isValid = false;
            }
            
            // Проверка адреса
            const address = document.getElementById('address');
            if (!address.value.trim()) {
                address.classList.add('error');
                document.getElementById('address-error').textContent = 'Поле обязательно для заполнения';
                isValid = false;
            }
            
            // Проверка почтового индекса
            const zip = document.getElementById('zip');
            const noZip = document.getElementById('no-zip');
            
            if (!noZip.checked) {
                if (!zip.value.trim()) {
                    zip.classList.add('error');
                    document.getElementById('zip-error').textContent = 'Поле обязательно для заполнения';
                    isValid = false;
                } else if (!/^\d{6}$/.test(zip.value)) {
                    zip.classList.add('error');
                    document.getElementById('zip-error').textContent = 'Индекс должен содержать 6 цифр';
                    isValid = false;
                }
            }
            
            if (isValid) {
                alert('Форма успешно проверена! Данные можно отправлять.');
                // form.submit(); // Раскомментируйте для реальной отправки
            }
        });
    }
});