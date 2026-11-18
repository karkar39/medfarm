// js/main.js

function openQuickView(productId) {
    const modal = document.getElementById('quickViewModal');
    const body  = document.getElementById('quickViewBody');
    body.innerHTML = 'Загрузка...';
    modal.classList.add('open');

    fetch('reviews.php?action=quickview&id=' + productId)
        .then(r => r.text())
        .then(html => { body.innerHTML = html; initTabs(); initStarInput(); })
        .catch(() => { body.innerHTML = 'Ошибка загрузки'; });
}

function closeQuickView() {
    document.getElementById('quickViewModal').classList.remove('open');
}

function initTabs() {
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const parent = tab.closest('.tabs').parentElement;
            parent.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            parent.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            parent.querySelector('#' + tab.dataset.target).classList.add('active');
        });
    });
}

function initStarInput() {
    document.querySelectorAll('.star-input').forEach(block => {
        const stars = block.querySelectorAll('span');
        const hidden = block.parentElement.querySelector('input[name="rating"]');
        stars.forEach((star, idx) => {
            star.addEventListener('click', () => {
                hidden.value = idx + 1;
                stars.forEach((s, i) => s.classList.toggle('on', i <= idx));
            });
            star.addEventListener('mouseenter', () => {
                stars.forEach((s, i) => s.classList.toggle('on', i <= idx));
            });
        });
        block.addEventListener('mouseleave', () => {
            const v = parseInt(hidden.value) || 0;
            stars.forEach((s, i) => s.classList.toggle('on', i < v));
        });
    });
}

function submitReview(form) {
    const msg = form.querySelector('.msg');
    const rating = form.querySelector('input[name="rating"]').value;
    const text   = form.querySelector('textarea[name="text"]').value.trim();
    const productId = form.querySelector('input[name="product_id"]').value;

    msg.className = 'msg';
    msg.textContent = '';

    if (!rating || rating < 1 || rating > 5) {
        msg.classList.add('error');
        msg.textContent = 'Поставьте оценку от 1 до 5.';
        return false;
    }
    if (text.length < 3) {
        msg.classList.add('error');
        msg.textContent = 'Текст отзыва — минимум 3 символа.';
        return false;
    }

    const fd = new FormData();
    fd.append('product_id', productId);
    fd.append('rating', rating);
    fd.append('text', text);

    fetch('api/submit_review.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                msg.classList.add('ok');
                msg.textContent = data.message || 'Отзыв сохранён!';
                setTimeout(() => location.reload(), 800);
            } else {
                msg.classList.add('error');
                msg.textContent = data.error || 'Ошибка';
            }
        })
        .catch(() => {
            msg.classList.add('error');
            msg.textContent = 'Ошибка сети';
        });

    return false;
}

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initStarInput();
});