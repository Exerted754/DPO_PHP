document.getElementById('feedbackForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    // Сброс ошибок
    document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    
    // Получение данных из формы
    let fullName = document.getElementById('fullName').value.trim();
    let email = document.getElementById('email').value.trim();
    let phone = document.getElementById('phone').value.trim();
    let comment = document.getElementById('comment').value.trim();
    
    // Валидация
    let errors = [];
    if (!fullName) errors.push('fullName');
    if (!email || !validateEmail(email)) errors.push('email');
    if (!phone || !validatePhone(phone)) errors.push('phone');
    if (!comment) errors.push('comment');
    
    // Если есть ошибки, подсвечиваем поля
    if (errors.length > 0) {
        errors.forEach(id => {
            document.getElementById(id).classList.add('error');
            let errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = 'Это поле обязательно для заполнения.';
            document.getElementById(id).after(errorMessage);
        });
        return;
    }
    
    // Отправка данных на сервер
    let formData = new FormData(this);
    fetch('submit_feedback.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Разделяем ФИО на части
            let nameParts = fullName.split(' ');
            let contactTime = new Date();
            contactTime.setHours(contactTime.getHours() + 1, contactTime.getMinutes() + 30); 

            // Формируем HTML для сообщения об успешной отправке
            document.getElementById('feedback-message').innerHTML = `
                <h2>Спасибо!</h2>
                <p>Ваше сообщение успешно отправлено.</p>
                <p>Имя: ${nameParts[0] || ''}</p>
                <p>Фамилия: ${nameParts[1] || ''}</p>
                <p>Отчество: ${nameParts[2] || ''}</p>
                <p>E-mail: ${email}</p>
                <p>Телефон: ${phone}</p>
                <p>Мы свяжемся с вами после ${contactTime.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })} ${contactTime.toLocaleDateString('ru-RU')}.</p>
            `;
            
            // Скрываем форму и показываем сообщение
            document.getElementById('feedback-form').style.display = 'none';
            document.getElementById('feedback-message').style.display = 'block';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при отправке формы');
    });
});

// Валидация email
function validateEmail(email) {
    let re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Валидация телефона
function validatePhone(phone) {
    phone = phone.replace(/[^0-9+]/g, '');
    let re = /^(\+7|8)[\d]{10}$/;
    return re.test(phone);
}