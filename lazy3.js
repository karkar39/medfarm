(async () => {
    const container = document.getElementById('products');
    const TRANSPARENT = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAP////yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
    const res  = await fetch('api/products.php?limit=20&offset=0');
    const data = await res.json();
    if (!data.ok) { container.textContent = 'Ошибка загрузки'; return; }
    container.innerHTML = data.items.map(p => renderCard(p, TRANSPARENT)).join('');

    const io = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            const img = entry.target;
            const box = img.closest('.product-card__image');
            img.src = img.dataset.src;
            img.addEventListener('load', () => {
                img.classList.add('loaded');
                box.classList.add('loaded');
            }, { once: true });
            img.addEventListener('error', () => box.classList.add('loaded'), { once: true });
            obs.unobserve(img);
        });
    }, { rootMargin: '200px 0px', threshold: 0.01 });

    container.querySelectorAll('img.lazy').forEach(img => io.observe(img));

    function renderCard(p, transparent) {
        const price = Number(p.price).toLocaleString('ru-RU');
        const promo = p.promo_type ? `<span class="promo-badge">${promoLabel(p.promo_type)} −${p.promo_discount}%</span>` : '';
        const rating = p.avg_rating ? `<div class="product-card__rating">★ ${p.avg_rating} (${p.reviews_count})</div>` : '';
        return `<article class="product-card">
                <div class="product-card__image">
                    <div class="skeleton"></div>
                    ${promo}
                    <img class="lazy" src="${transparent}" data-src="${escapeHtml(p.image_url)}" alt="${escapeHtml(p.alt_text)}" title="${escapeHtml(p.image_title)}" width="${p.width}" height="${p.height}">
                </div>
                <div class="product-card__body">
                    <h3 class="product-card__title">${escapeHtml(p.name)}</h3>
                    <p class="product-card__desc">${escapeHtml(p.description || '')}</p>
                    <div class="product-card__price">${price} ₽</div>
                    ${rating}
                </div>
            </article>`;
    }
    function promoLabel(t) { return { day: 'Акция дня', week: 'Акция недели', month: 'Акция месяца' }[t] || 'Акция'; }
    function escapeHtml(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
})();