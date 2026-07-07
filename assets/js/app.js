/**
 * PROJECT: CLK Apple Store
 * FILE: assets/js/app.js
 * Upgraded: Animations, Toast System, Scroll-to-top, Chatbot
 */

/* ══════════════════════════════════════
   1. TOAST NOTIFICATION SYSTEM
   (success / error / warning)
══════════════════════════════════════ */
let _toastTimer = null;

function showToastJS(message, type = 'success') {
    // Xóa toast cũ
    const old = document.getElementById('clk-toast');
    if (old) {
        old.remove();
        clearTimeout(_toastTimer);
    }

    const cfg = {
        success: { icon: 'fas fa-check-circle', color: '#16a34a', bg: '#f0fdf4', border: '#bbf7d0', text: '#166534' },
        error:   { icon: 'fas fa-times-circle',  color: '#dc2626', bg: '#fef2f2', border: '#fecaca', text: '#991b1b' },
        warning: { icon: 'fas fa-exclamation-triangle', color: '#d97706', bg: '#fffbeb', border: '#fde68a', text: '#92400e' },
        info:    { icon: 'fas fa-info-circle',   color: '#2563eb', bg: '#eff6ff', border: '#bfdbfe', text: '#1e40af' },
    };

    const c = cfg[type] || cfg.success;

    const toast = document.createElement('div');
    toast.id = 'clk-toast';
    toast.innerHTML = `
        <div class="clk-toast-icon" style="color:${c.color}"><i class="${c.icon}"></i></div>
        <div class="clk-toast-msg" style="color:${c.text}">${message}</div>
        <button class="clk-toast-close" onclick="this.closest('#clk-toast').remove()" title="Đóng">
            <i class="fas fa-times"></i>
        </button>
        <div class="clk-toast-bar" style="background:${c.color}"></div>
    `;
    toast.style.cssText = `
        position:fixed; top:20px; right:20px; z-index:999999;
        min-width:280px; max-width:360px;
        background:${c.bg}; border:1px solid ${c.border};
        border-left:4px solid ${c.color};
        border-radius:12px; padding:14px 16px;
        display:flex; align-items:center; gap:12px;
        box-shadow:0 8px 24px rgba(0,0,0,0.12);
        font-family:'Inter',sans-serif; font-size:14px;
        overflow:hidden; cursor:default;
        transform:translateX(120%); opacity:0;
        transition:transform 0.35s cubic-bezier(0.34,1.56,0.64,1), opacity 0.35s ease;
    `;
    document.body.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    // Progress bar animation
    const bar = toast.querySelector('.clk-toast-bar');
    if (bar) {
        bar.style.cssText = `
            position:absolute; bottom:0; left:0; height:3px;
            width:100%; transform-origin:left;
            animation:toastProgress 3.5s linear forwards;
        `;
    }

    // Auto dismiss
    _toastTimer = setTimeout(() => dismissToast(toast), 3500);

    // Click to dismiss
    toast.addEventListener('click', (e) => {
        if (!e.target.closest('.clk-toast-close')) return;
    });
}

function dismissToast(toast) {
    if (!toast || !document.body.contains(toast)) return;
    toast.style.transform = 'translateX(120%)';
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 350);
}

// Inject toast progress keyframe once
(function injectToastCSS() {
    if (document.getElementById('clk-toast-style')) return;
    const style = document.createElement('style');
    style.id = 'clk-toast-style';
    style.textContent = `
        @keyframes toastProgress {
            from { transform: scaleX(1); }
            to   { transform: scaleX(0); }
        }
        .clk-toast-icon { font-size:20px; flex-shrink:0; }
        .clk-toast-msg  { flex:1; line-height:1.4; }
        .clk-toast-close {
            background:none; border:none; padding:0;
            color:#999; font-size:13px; cursor:pointer;
            flex-shrink:0; line-height:1;
        }
        .clk-toast-close:hover { color:#333; }
        @media (max-width:480px) {
            #clk-toast { right:12px; left:12px; max-width:calc(100vw - 24px); min-width:0; }
        }
    `;
    document.head.appendChild(style);
})();


/* ══════════════════════════════════════
   2. PAGE FADE-IN ON LOAD
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.45s ease';
    requestAnimationFrame(() => {
        document.body.style.opacity = '1';
    });
});


/* ══════════════════════════════════════
   3. SCROLL REVEAL (IntersectionObserver)
   Thêm class .clk-reveal vào bất kỳ phần tử nào để có animation
══════════════════════════════════════ */
(function initScrollReveal() {
    const style = document.createElement('style');
    style.id = 'clk-scroll-reveal-style';
    style.textContent = `
        .clk-reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.55s ease, transform 0.55s ease;
        }
        .clk-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .clk-reveal-left  { opacity:0; transform:translateX(-32px); transition:opacity 0.55s ease,transform 0.55s ease; }
        .clk-reveal-left.revealed  { opacity:1; transform:translateX(0); }
        .clk-reveal-right { opacity:0; transform:translateX(32px); transition:opacity 0.55s ease,transform 0.55s ease; }
        .clk-reveal-right.revealed { opacity:1; transform:translateX(0); }
    `;
    if (!document.getElementById('clk-scroll-reveal-style')) {
        document.head.appendChild(style);
    }
})();

/* ══════════════════════════════════════
   4. AUTO-APPLY REVEAL + OBSERVE (gom 1 handler)
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    /* --- Bước 1: Thêm class vào các phần tử --- */
    const revealSelectors = [
        '.section-wrapper',
        '.product-card-v2',
        '.cat-product-card',
        '.blog-card-v2',
        '.brand-shortcuts',
        '.card.shadow-sm',
        '.card.mb-3',
        '.card.mb-4',
        '.card.mb-5',
    ];

    revealSelectors.forEach(sel => {
        document.querySelectorAll(sel).forEach((el, i) => {
            if (!el.classList.contains('clk-reveal')) {
                el.classList.add('clk-reveal');
                el.dataset.delay = Math.min(i * 60, 300);
            }
        });
    });

    /* --- Bước 2: Observe sau khi đã gán class --- */
    const targets = document.querySelectorAll('.clk-reveal, .clk-reveal-left, .clk-reveal-right');
    if (!targets.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const delay = parseInt(el.dataset.delay || 0);
                setTimeout(() => el.classList.add('revealed'), delay);
                observer.unobserve(el);
            }
        });
    }, {
        threshold: 0.05,            /* chỉ cần 5% hiện là trigger */
        rootMargin: '0px 0px 40px 0px'  /* trigger sớm hơn 40px */
    });

    targets.forEach(el => observer.observe(el));

    /* --- Safety net: sau 900ms force reveal tất cả còn ẩn --- */
    setTimeout(() => {
        document.querySelectorAll('.clk-reveal:not(.revealed), .clk-reveal-left:not(.revealed), .clk-reveal-right:not(.revealed)')
            .forEach(el => el.classList.add('revealed'));
    }, 900);
});



/* ══════════════════════════════════════
   5. SCROLL-TO-TOP (bên TRÁI màn hình)
══════════════════════════════════════ */
(function initScrollTop() {
    // Inject CSS
    const style = document.createElement('style');
    style.textContent = `
        #clk-scroll-top {
            position: fixed;
            bottom: 80px;
            left: 18px;
            z-index: 9990;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary, #001C30);
            color: var(--accent, #DAA520);
            border: 2px solid var(--accent, #DAA520);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            opacity: 0;
            pointer-events: none;
            transform: translateY(12px) scale(0.85);
            transition: opacity 0.3s ease, transform 0.3s ease, background 0.2s, color 0.2s;
        }
        #clk-scroll-top.visible {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }
        #clk-scroll-top:hover {
            background: var(--accent, #DAA520);
            color: var(--primary, #001C30);
            transform: scale(1.1);
        }
        @media (max-width: 767px) {
            #clk-scroll-top { bottom: 70px; left: 12px; width:40px; height:40px; font-size:14px; }
        }
    `;
    document.head.appendChild(style);

    // Create button
    const btn = document.createElement('button');
    btn.id = 'clk-scroll-top';
    btn.title = 'Lên đầu trang';
    btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
    document.body.appendChild(btn);

    // Show/hide on scroll
    window.addEventListener('scroll', () => {
        if (window.scrollY > 320) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    }, { passive: true });

    // Click: smooth scroll to top
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();


/* ══════════════════════════════════════
   6. CART OPERATIONS
══════════════════════════════════════ */
function addToCart(itemId, btnElement = null, qty = 1) {
    if (btnElement) {
        btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btnElement.disabled = true;
    }

    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', item_id: itemId, qty: qty })
    })
    .then(response => response.json())
    .then(data => {
        if (btnElement) {
            btnElement.innerHTML = '<i class="fas fa-check"></i> Đã thêm';
            btnElement.classList.remove('btn-buy-now');
            btnElement.classList.add('btn-bought');
            btnElement.disabled = false;
        }

        if (data.status === 'success') {
            showToastJS(data.message, 'success');
            const badge = document.getElementById('cart-count-badge');
            if (badge) badge.innerText = data.cart_count;
        } else {
            if (data.message === 'Vui lòng đăng nhập trước!') {
                window.location.href = 'login.php';
            } else {
                showToastJS(data.message, 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (btnElement) { btnElement.innerHTML = 'MUA HÀNG'; btnElement.disabled = false; }
        showToastJS('Có lỗi xảy ra, vui lòng thử lại!', 'error');
    });
}

function toggleWishlist(itemId, btnElement = null) {
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', item_id: itemId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToastJS(data.message, data.action === 'added' ? 'success' : 'warning');
            if (btnElement) {
                btnElement.style.color = data.action === 'added' ? '#ff4757' : '#ccc';
            }
            const badge = document.getElementById('wishlist-count-badge');
            if (badge) badge.innerText = data.wishlist_count;
        } else {
            if (data.message === 'Vui lòng đăng nhập trước!') {
                window.location.href = 'login.php';
            } else {
                showToastJS(data.message, 'error');
            }
        }
    })
    .catch(() => showToastJS('Có lỗi xảy ra, vui lòng thử lại!', 'error'));
}

function updateCartQty(itemId, delta) {
    let inputElement = document.querySelector(`input[data-id="${itemId}"]`);
    if (!inputElement) return;

    let newQty = Math.max(1, parseInt(inputElement.value) + delta);
    inputElement.value = newQty;

    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'update_qty', item_id: itemId, qty: newQty })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const priceSpan = document.querySelector(`.product_price[data-id="${itemId}"]`);
            if (priceSpan) priceSpan.innerText = data.item_total;
            const dealPrice = document.getElementById('deal-price');
            if (dealPrice) dealPrice.innerText = data.subtotal;
        }
    });
}

function removeCartItem(itemId, rowElement) {
    fetch('api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', item_id: itemId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToastJS(data.message, 'warning');
            rowElement.style.opacity = '0';
            rowElement.style.transform = 'translateX(-20px)';
            rowElement.style.transition = '0.3s ease';
            setTimeout(() => {
                rowElement.remove();
                const badge = document.getElementById('cart-count-badge');
                if (badge) badge.innerText = data.cart_count;
                const dealPrice = document.getElementById('deal-price');
                if (dealPrice) dealPrice.innerText = data.subtotal;
                if (data.cart_count == 0) window.location.reload();
            }, 300);
        }
    });
}


/* ══════════════════════════════════════
   7. PRODUCT FILTER / SEARCH / CATEGORY AJAX
══════════════════════════════════════ */
function filterProducts(brandId, btnElement) {
    const tabs = document.querySelectorAll('.filter-tab');
    tabs.forEach(t => t.classList.remove('active'));
    if (btnElement) btnElement.classList.add('active');

    const grid = document.querySelector('.product-grid');
    if (!grid) return;

    grid.style.opacity = '0.4';
    grid.style.transition = '0.3s';

    fetch(`api/products.php?brand=${brandId}`)
    .then(res => res.text())
    .then(html => {
        grid.innerHTML = html;
        grid.style.opacity = '1';
        // Re-apply scroll reveal
        grid.querySelectorAll('.product-card-v2').forEach((el, i) => {
            el.classList.add('clk-reveal');
            el.dataset.delay = Math.min(i * 60, 300);
        });
        const sect = document.getElementById('special-price');
        if (sect) sect.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

function searchProducts(event) {
    event.preventDefault();
    const form = event.target;
    const keyword = form.querySelector('input[name="keyword"]').value;

    const grid = document.querySelector('.product-grid');
    if (!grid) {
        const catArea = document.getElementById('category-content-area');
        if (catArea) {
            loadCategoryPage({ keyword: keyword });
            const overlay = document.getElementById('mobileSearchOverlay');
            if (overlay) overlay.classList.remove('show');
            return;
        }
        form.submit();
        return;
    }

    grid.style.opacity = '0.4';
    fetch(`api/products.php?keyword=${encodeURIComponent(keyword)}`)
    .then(res => res.text())
    .then(html => {
        grid.innerHTML = html;
        grid.style.opacity = '1';
        const overlay = document.getElementById('mobileSearchOverlay');
        if (overlay) overlay.classList.remove('show');
        const sect = document.getElementById('special-price');
        if (sect) sect.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const title = document.querySelector('.section-title-bar h3');
        if (title) title.innerHTML = `🔍 Kết quả cho: "${keyword}"`;
    });
}

function loadCategoryPage(params = {}) {
    const area = document.getElementById('category-content-area');
    if (!area) return;

    area.style.opacity = '0.4';
    area.style.transition = '0.3s';

    const urlParams = new URLSearchParams(window.location.search);
    for (const [key, value] of Object.entries(params)) {
        if (value === null || value === '') urlParams.delete(key);
        else urlParams.set(key, value);
    }

    const fetchParams = new URLSearchParams(urlParams);
    fetchParams.set('ajax', '1');

    fetch(`api/category_ajax.php?${fetchParams.toString()}`)
    .then(res => res.text())
    .then(html => {
        area.innerHTML = html;
        area.style.opacity = '1';

        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.history.pushState({ path: newUrl }, '', newUrl);
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Re-apply reveal + force visible (IntersectionObserver won't track newly injected elements)
        area.querySelectorAll('.cat-product-card').forEach((el, i) => {
            el.classList.add('clk-reveal');
            el.dataset.delay = Math.min(i * 60, 300);
            // Force reveal — element won't be picked up by the observer set at DOMContentLoaded
            setTimeout(() => el.classList.add('revealed'), Math.min(i * 60, 300) + 80);
        });

        // ── Update sidebar brand active state ──────────────────────
        if (params.brand_id !== undefined) {
            const brandId = parseInt(params.brand_id) || 0;

            // Sidebar desktop (.sidebar-menu li)
            document.querySelectorAll('.sidebar-menu li').forEach(li => {
                li.classList.remove('active');
                const a = li.querySelector('a');
                if (a) {
                    const onclickStr = a.getAttribute('onclick') || '';
                    const match = onclickStr.match(/brand_id:\s*(\d+)/);
                    const liId = match ? parseInt(match[1]) : 0;
                    if (liId === brandId) li.classList.add('active');
                }
            });

            // Subnav header
            document.querySelectorAll('.subnav-links a').forEach(a => {
                a.classList.remove('active');
                const onclickStr = a.getAttribute('onclick') || '';
                const hrefStr = a.getAttribute('href') || '';
                const match = onclickStr.match(/brand_id:\s*(\d+)/) || hrefStr.match(/brand_id=(\d+)/);
                const aId = match ? parseInt(match[1]) : 0;
                if (aId === brandId) a.classList.add('active');
            });
        }
    });
}


function removeFromWishlist(itemId, rowElement) {
    fetch('api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'remove', item_id: itemId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showToastJS(data.message, 'warning');
            rowElement.style.opacity = '0';
            rowElement.style.transform = 'scale(0.95)';
            rowElement.style.transition = '0.3s';
            setTimeout(() => {
                rowElement.remove();
                const badge = document.getElementById('wishlist-count-badge');
                if (badge) badge.innerText = data.wishlist_count;
                if (data.wishlist_count == 0) window.location.reload();
            }, 300);
        }
    });
}


/* ══════════════════════════════════════
   8. CHATBOT LOGIC
══════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn    = document.getElementById('chatbot-toggle');
    const closeBtn     = document.getElementById('chatbot-close');
    const container    = document.getElementById('chatbot-container');
    const chatForm     = document.getElementById('chatbot-input-area');
    const chatInput    = document.getElementById('chatbot-input');
    const chatMessages = document.getElementById('chatbot-messages');

    if (!toggleBtn || !container) return;

    // Mở/Đóng
    toggleBtn.onclick = () => {
        container.classList.toggle('show');
        if (container.classList.contains('show')) {
            setTimeout(() => chatInput && chatInput.focus(), 300);
        }
    };
    if (closeBtn) closeBtn.onclick = () => container.classList.remove('show');

    // ══════════════════════════════════════════════════════════════
    // CHATBOT — Rule-based thông minh + Gemini fallback
    // ══════════════════════════════════════════════════════════════
    if (chatForm) {

        // ── Normalize helper ──────────────────────────────────────
        function norm(str) {
            return str.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/j\b/g, 'gi')   // "j" là slang của "gì"
                .trim();
        }

        // Khớp toàn từ (word boundary) — tránh false positive
        function wordMatch(text, keyword) {
            if (keyword.includes(' ')) return text.includes(keyword); // cụm từ: dùng includes bình thường
            // Từ đơn ngắn: phải khớp toàn từ
            const re = new RegExp('(^|\\s|[^a-z])' + keyword.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + '(\\s|[^a-z]|$)');
            return re.test(' ' + text + ' ');
        }

        // ── 1. Knowledge Base (rule-based) ───────────────────────
        const KB = [
            {
                // Câu hỏi về sản phẩm / shop bán gì — ĐẶT ĐẦU để ưu tiên
                keys: ['ban gi','co gi','co nhung gi','nhung gi','shop ban','shop co','san pham gi','may gi','dien thoai nao','co loai nao','hang gi','phu kien','phu kien gi','co ban','mua gi','ban cai gi'],
                exact: false,
                reply: '🏪 <strong>CLK Store có các dòng:</strong><br>• 🍎 <strong>iPhone, Samsung, Redmi, Oppo</strong><br>• 🎧 <strong>Tai nghe</strong> (Bluetooth, có dây)<br>• 📱 <strong>Ốp lưng & Cáp sạc</strong><br><br>Bạn quan tâm đến điện thoại hay phụ kiện nào? Mình tư vấn thêm nhé!'
            },
            {
                keys: ['xin chao','hello','hi','chao','alo','hey','chao ban','co ai khong','e oi','ad oi','shop oi','bot oi'],
                exact: true,
                reply: '👋 Xin chào! Mình là <strong>CLK Assistant</strong>. Mình có thể tư vấn điện thoại, phụ kiện và hỗ trợ đặt hàng cho bạn!'
            },
            {
                keys: ['iphone','ip13','ip14','ip15','ip16','ip ','apple','ip x','ip12'],
                exact: false,
                reply: '🍎 <strong>iPhone tại CLK Store:</strong><br>• iPhone X – 82.000đ<br>• iPhone 12 Pro – 122.000đ<br>• iPhone 13 ProMax – 142.000đ<br>Bạn quan tâm dòng nào? Mình tư vấn thêm!'
            },
            {
                keys: ['samsung','galaxy','sam sung','ss '],
                exact: false,
                reply: '📱 <strong>Samsung tại CLK Store:</strong><br>• Galaxy A23 – 122.000đ<br>• Galaxy S6 – 122.000đ<br>• Galaxy S7 – 132.000đ<br>• Galaxy S6 Edge – 220.000đ<br>Dòng nào bạn muốn tìm hiểu?'
            },
            {
                keys: ['redmi','xiaomi','xao mi','mi '],
                exact: false,
                reply: '📱 <strong>Redmi / Xiaomi tại CLK Store:</strong><br>• Redmi Note 4 – 82.000đ<br>• Redmi Note 7 – 122.000đ<br>• Redmi Note 9 – 142.000đ<br>Giá tốt, pin trâu!'
            },
            {
                keys: ['oppo','reno','op po'],
                exact: false,
                reply: '📱 <strong>Oppo tại CLK Store:</strong><br>• Oppo Reno 8 – 155.000đ<br>Camera đẹp, thiết kế sang!'
            },
            {
                keys: ['tai nghe','headphone','airpod','earpod','bluetooth','tai nghe bluetooth','tws','nghe nhac','am thanh'],
                exact: false,
                reply: '🎧 <strong>Tai nghe & Phụ kiện âm thanh:</strong><br>Bên mình có đa dạng các loại tai nghe có dây, tai nghe Bluetooth (Airpods, Galaxy Buds...) chất lượng cao. Bạn đang tìm tai nghe cho dòng máy nào?'
            },
            {
                keys: ['op lung','op dien thoai','case','bao da','op ','kinh cuong luc','cuong luc','dan man hinh','dan dien thoai'],
                exact: false,
                reply: '📱 <strong>Ốp lưng & Cường lực:</strong><br>Bên mình có đầy đủ ốp lưng, bao da chống sốc và kính cường lực cho các dòng iPhone, Samsung, Oppo. Bạn đang dùng máy gì để mình check mẫu nhé!'
            },
            {
                keys: ['cap sac','cu sac','sac dien thoai','sac nhanh','day sac','pin du phong','sac du phong','cuc sac'],
                exact: false,
                reply: '🔋 <strong>Sạc & Pin dự phòng:</strong><br>Shop có sẵn cáp sạc nhanh 20W, 65W, và sạc dự phòng 10000mAh/20000mAh chính hãng. Bạn cần mua cho máy gì ạ?'
            },
            {
                keys: ['gia','bao nhieu','tien','re','dat','mac','tam gia','ngan sach','budget','gia sao','gia the nao','nhieu tien'],
                exact: false,
                reply: '💰 <strong>Bảng giá tham khảo:</strong><br>• Dưới 100k: iPhone X, Redmi Note 4, Ốp lưng, Cáp sạc<br>• 100k–130k: iPhone 12, Samsung A23, Tai nghe Bluetooth<br>• Trên 130k: iPhone 13 ProMax, Galaxy S6 Edge<br>Bạn có ngân sách bao nhiêu?'
            },
            {
                keys: ['dat hang','mua hang','order','gio hang','checkout','thanh toan','mua sao','cach mua','mua o dau','muon mua','lam sao de mua'],
                exact: false,
                reply: '🛒 <strong>Cách đặt hàng:</strong><br>1. Chọn sản phẩm → Thêm vào giỏ<br>2. Vào <a href="cart.php" style="color:#0084ff">Giỏ hàng</a> → Thanh toán<br>3. Điền địa chỉ → Xác nhận<br>Hỗ trợ COD, MoMo, chuyển khoản!'
            },
            {
                keys: ['giao hang','van chuyen','ship','phi ship','bao lau','nhan hang','chuyen phat','tien ship','freeship','mien phi ship','giao tan noi'],
                exact: false,
                reply: '🚚 <strong>Vận chuyển & Giao hàng:</strong><br>• Tiêu chuẩn (3–5 ngày): 20.000đ<br>• Giao nhanh (1–2 ngày): 40.000đ<br>📦 Đặc biệt: Freeship cho đơn hàng trên 500k!'
            },
            {
                keys: ['bao hanh','doi tra','loi may','hong may','hu may','sua chua','tra hang','hoan tien','bao hanh bao lau','che do bao hanh'],
                exact: false,
                reply: '🛡️ <strong>Chính sách Bảo hành:</strong><br>• Bảo hành chính hãng 12 tháng<br>• Đổi trả/Hoàn tiền 7 ngày nếu lỗi NSX<br>• Phụ kiện bảo hành 1 đổi 1 trong 3 tháng<br>📞 Hotline hỗ trợ: 0389 *** ***'
            },
            {
                keys: ['don hang','trang thai','theo doi','kiem tra don','don cua toi','chua nhan duoc','khi nao giao'],
                exact: false,
                reply: '📦 Xem chi tiết và theo dõi đơn hàng tại phần <a href="history.php" style="color:#0084ff">Lịch sử đơn hàng</a> nhé!<br>Trạng thái: Chờ xác nhận → Đóng gói → Đang giao → Đã nhận.'
            },
            {
                keys: ['camera','chup anh','selfie','quay phim','video','may anh','chup hinh'],
                exact: false,
                reply: '📸 <strong>Tư vấn điện thoại chụp ảnh đẹp:</strong><br>• Selfie mượt: iPhone 13 ProMax, Oppo Reno 8<br>• Camera chính sắc nét: Samsung S7, iPhone 12 Pro<br>• Chụp cơ bản: Redmi Note 9, Samsung A23'
            },
            {
                keys: ['pin','sac nhanh','xai lau','dung luong pin','pin trau','pin khoe'],
                exact: false,
                reply: '🔋 <strong>Tư vấn điện thoại Pin trâu:</strong><br>• Redmi Note 9 – 5020mAh, hỗ trợ sạc nhanh<br>• Samsung A23 – 5000mAh<br>• iPhone 13 ProMax – Tối ưu iOS, dùng thoải mái cả ngày'
            },
            {
                keys: ['lien he','hotline','so dien thoai','dia chi','zalo','email','sdt','cua hang o dau','toi cua hang'],
                exact: false,
                reply: '📞 <strong>Thông tin liên hệ CLK Store:</strong><br>• Hotline/Zalo: 0389 *** ***<br>• Email: clkstore@gmail.com<br>• Địa chỉ: Quận 1, TP. Hồ Chí Minh<br>• Giờ mở cửa: 8h00 – 21h30'
            },
            {
                keys: ['choi game','muot','game','cày game','chơi pubg','chơi liên quân','giật lag'],
                exact: false,
                reply: '🎮 <strong>Điện thoại chơi game mượt:</strong><br>Để chơi mượt PUBG, Liên Quân, bạn nên tham khảo iPhone 13 ProMax hoặc Oppo Reno 8. Các dòng Redmi Note cũng chiến game cơ bản rất ổn định trong tầm giá!'
            },
            {
                keys: ['cam on','thanks','thank you','tuyet','hay qua','tot qua','ok','da hieu','vâng','dza','dạ'],
                exact: false,
                reply: '😊 Cảm ơn bạn! Nếu cần hỗ trợ thêm gì, bạn cứ nhắn mình nhé!'
            },
            {
                keys: ['tam biet','bye','goodbye','chao tam biet'],
                exact: false,
                reply: '👋 Tạm biệt bạn! Chúc bạn một ngày vui vẻ và hẹn gặp lại tại CLK Store!'
            }
        ];

        function ruleBasedReply(msg) {
            const n = norm(msg);
            for (const rule of KB) {
                for (const key of rule.keys) {
                    const nk = norm(key);
                    const matched = rule.exact ? wordMatch(n, nk) : n.includes(nk);
                    if (matched) return rule.reply;
                }
            }
            return null;
        }

        // ── 2. AI Backend connection (Gemini via PHP) ──────────────────
        async function aiReply(msg) {
            try {
                const res = await fetch('api/chatbot_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: msg })
                });
                if (!res.ok) return null;
                const data = await res.json();
                return data.status === 'success' ? data.reply : null;
            } catch { return null; }
        }

        // ── 3. Submit handler ─────────────────────────────────────
        chatForm.onsubmit = async (e) => {
            e.preventDefault();
            const msg = chatInput.value.trim();
            if (!msg) return;

            appendMessage('user', msg);
            chatInput.value = '';
            chatInput.disabled = true;

            const loadingId = 'loading-' + Date.now();
            appendLoading(loadingId);
            scrollToBottom();

            let reply = ruleBasedReply(msg);
            if (!reply) reply = await aiReply(msg);
            if (!reply) reply = '🤖 Mình chưa hiểu câu hỏi này. Bạn có thể hỏi về <strong>Điện thoại, Tai nghe, Ốp lưng, giá cả, đặt hàng</strong> hoặc gọi <strong>0389 *** ***</strong>!';

            removeLoading(loadingId);
            appendMessage('bot', reply);

            chatInput.disabled = false;
            chatInput.focus();
            scrollToBottom();
        };
    }


    // ── Messenger-style appendMessage ────────────────────────────
    function appendMessage(role, text) {
        const msgs = document.getElementById('chatbot-messages');
        if (!msgs) return;

        // Ẩn avatar của tin nhắn cùng role trước đó (Messenger style)
        const rows = msgs.querySelectorAll('.chat-msg-row.' + role);
        if (rows.length > 0) {
            const prev = rows[rows.length - 1];
            const prevAv = prev.querySelector('.chat-avatar');
            if (prevAv) prevAv.style.visibility = 'hidden';
        }

        const wrapper = document.createElement('div');
        wrapper.className = `chat-msg-row ${role}`;

        const avatar = document.createElement('div');
        avatar.className = 'chat-avatar';
        avatar.innerHTML = role === 'bot'
            ? '<img src="assets/phone.png" alt="Bot" onerror="this.style.display=\'none\'">'
            : '<i class="fas fa-user" style="font-size:12px"></i>';

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble';
        bubble.innerHTML = text.replace(/\n/g, '<br>');

        if (role === 'bot') {
            wrapper.appendChild(avatar);
            wrapper.appendChild(bubble);
        } else {
            // user: không có avatar (Messenger style — ẩn hoàn toàn)
            wrapper.appendChild(bubble);
        }

        msgs.appendChild(wrapper);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function appendLoading(id) {
        const msgs = document.getElementById('chatbot-messages');
        if (!msgs) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'chat-msg-row bot';
        wrapper.id = id;
        wrapper.innerHTML = `
            <div class="chat-avatar"><img src="assets/phone.png" alt="Bot" onerror="this.style.display='none'"></div>
            <div class="chat-bubble loading">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        `;
        msgs.appendChild(wrapper);
    }

    function removeLoading(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function scrollToBottom() {
        const msgs = document.getElementById('chatbot-messages');
        if (msgs) msgs.scrollTop = msgs.scrollHeight;
    }
});


/* ══════════════════════════════════════
   9. LIVE SEARCH (Tìm kiếm thông minh)
   Dropdown gợi ý tức thì khi gõ
══════════════════════════════════════ */
(function initLiveSearch() {
    document.addEventListener('DOMContentLoaded', () => {
        const input    = document.getElementById('main-search-input');
        const dropdown = document.getElementById('live-search-dropdown');
        if (!input || !dropdown) return;

        // Inject CSS
        const style = document.createElement('style');
        style.textContent = `
            #live-search-dropdown {
                position: absolute;
                top: calc(100% + 6px);
                left: 0; right: 0;
                background: #fff;
                border-radius: 14px;
                box-shadow: 0 12px 40px rgba(0,0,0,0.18);
                z-index: 9999;
                max-height: 460px;
                overflow-y: auto;
                border: 1px solid #f0f0f0;
                animation: lsDropIn 0.18s ease;
            }
            @keyframes lsDropIn {
                from { opacity:0; transform:translateY(-8px); }
                to   { opacity:1; transform:translateY(0); }
            }
            .ls-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px 14px;
                text-decoration: none;
                color: #111;
                border-bottom: 1px solid #f5f5f5;
                transition: background 0.15s;
            }
            .ls-item:last-child { border-bottom: none; }
            .ls-item:hover { background: #f8f9ff; }
            .ls-img {
                width: 46px; height: 46px;
                object-fit: contain;
                border-radius: 8px;
                background: #f5f5f7;
                padding: 4px;
                flex-shrink: 0;
            }
            .ls-info { flex: 1; min-width: 0; }
            .ls-name {
                font-size: 13px; font-weight: 700;
                white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            }
            .ls-brand { font-size: 11px; color: #888; }
            .ls-price { font-size: 13px; font-weight: 800; color: #ef4444; white-space: nowrap; }
            .ls-footer {
                display: block; text-align: center;
                padding: 10px; font-size: 12px;
                color: #001C30; font-weight: 600;
                border-top: 1px solid #f0f0f0;
                text-decoration: none;
                transition: background 0.15s;
            }
            .ls-footer:hover { background: #f0f4ff; }
            .ls-empty { padding: 20px; text-align: center; color: #888; font-size: 13px; }
        `;
        document.head.appendChild(style);

        let debounceTimer = null;

        function closeDrop() { dropdown.style.display = 'none'; dropdown.innerHTML = ''; }

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const q = input.value.trim();
            if (q.length < 2) { closeDrop(); return; }

            debounceTimer = setTimeout(() => {
                fetch(`api/live_search.php?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(items => {
                        if (!items.length) {
                            dropdown.innerHTML = '<div class="ls-empty">Không tìm thấy sản phẩm nào 😕</div>';
                            dropdown.style.display = 'block';
                            return;
                        }
                        let html = '';
                        items.forEach(p => {
                            const price = Number(p.price).toLocaleString('vi-VN') + 'đ';
                            html += `
                            <a href="details.php?id=${p.id}" class="ls-item">
                                <img src="${p.image}" alt="${p.name}" class="ls-img"
                                     onerror="this.src='assets/products/no-image.png'">
                                <div class="ls-info">
                                    <div class="ls-name">${p.name}</div>
                                    <div class="ls-brand">${p.brand_name}</div>
                                </div>
                                <div class="ls-price">${price}</div>
                            </a>`;
                        });
                        html += `<a href="search.php?keyword=${encodeURIComponent(q)}" class="ls-footer">
                            <i class="fas fa-search me-1"></i>Xem tất cả kết quả cho "${q}"
                        </a>`;
                        dropdown.innerHTML = html;
                        dropdown.style.display = 'block';
                    })
                    .catch(() => closeDrop());
            }, 300);
        });

        // Đóng dropdown khi click ra ngoài
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.search-wrapper')) closeDrop();
        });

        // Submit form → vào search.php bình thường
        const form = document.getElementById('main-search-form');
        if (form) {
            form.addEventListener('submit', () => closeDrop());
        }
    });
})();


/* ══════════════════════════════════════
   10. SO SÁNH SẢN PHẨM (Compare)
   Floating bar + localStorage
══════════════════════════════════════ */
(function initCompare() {
    // Inject CSS
    const style = document.createElement('style');
    style.textContent = `
        #compare-float-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            z-index: 9998;
            background: #001C30;
            box-shadow: 0 -4px 24px rgba(0,0,0,0.3);
            animation: compareSlideUp 0.3s ease;
        }
        @keyframes compareSlideUp {
            from { transform: translateY(100%); }
            to   { transform: translateY(0); }
        }
        .compare-bar-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .compare-bar-items {
            display: flex;
            gap: 8px;
            flex: 1;
            align-items: center;
            flex-wrap: wrap;
        }
        .compare-bar-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 4px 8px 4px 6px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            max-width: 160px;
        }
        .compare-bar-chip img {
            width: 30px; height: 30px;
            object-fit: contain;
            border-radius: 5px;
            background: rgba(255,255,255,0.1);
            flex-shrink: 0;
        }
        .compare-bar-chip span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .compare-bar-chip button {
            background: none; border: none;
            color: rgba(255,255,255,0.6);
            font-size: 11px; cursor: pointer;
            padding: 0; line-height: 1; flex-shrink: 0;
        }
        .compare-bar-chip button:hover { color: #ff4757; }
        .compare-bar-slot-empty {
            display: flex; align-items: center; justify-content: center;
            width: 46px; height: 46px;
            border: 2px dashed rgba(255,255,255,0.25);
            border-radius: 8px;
            color: rgba(255,255,255,0.3);
            font-size: 18px;
        }
        .compare-bar-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .btn-compare-go {
            background: #DAA520; color: #001C30;
            border: none; border-radius: 8px;
            padding: 9px 18px; font-size: 13px; font-weight: 700;
            text-decoration: none; white-space: nowrap;
            transition: background 0.2s;
        }
        .btn-compare-go:hover { background: #ffd700; color: #001C30; }
        .btn-compare-clear {
            background: transparent;
            color: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px; padding: 9px 14px;
            font-size: 12px; cursor: pointer;
            transition: 0.2s;
        }
        .btn-compare-clear:hover { color: #ff4757; border-color: #ff4757; }
        .btn-compare-mini {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            background: #f0f4ff;
            color: #001C30;
            border: 1px solid #d0d8f0;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            flex-shrink: 0;
            transition: 0.2s;
        }
        .btn-compare-mini:hover { background: #001C30; color: #DAA520; }
        .btn-compare-mini.in-compare { background: #001C30; color: #DAA520; }
    `;
    document.head.appendChild(style);

    const MAX = 3;
    const KEY = 'compare_ids_v2'; // v2 để tránh conflict với dữ liệu cũ

    function getIds()         { return JSON.parse(localStorage.getItem(KEY) || '[]'); }
    function saveIds(arr)     { localStorage.setItem(KEY, JSON.stringify(arr)); }
    function getItems()       { return JSON.parse(localStorage.getItem(KEY + '_data') || '[]'); }
    function saveItems(arr)   { localStorage.setItem(KEY + '_data', JSON.stringify(arr)); }

    function renderBar() {
        const bar      = document.getElementById('compare-float-bar');
        const container = document.getElementById('compare-bar-items');
        const goBtn    = document.getElementById('compare-bar-go-btn');
        if (!bar || !container) return;

        const items = getItems();
        const ids   = getIds();

        if (!ids.length) { bar.style.display = 'none'; return; }

        bar.style.display = 'block';

        // Chips
        let html = '';
        items.forEach(item => {
            html += `
            <div class="compare-bar-chip">
                <img src="${item.image}" onerror="this.src='assets/products/no-image.png'">
                <span>${item.name}</span>
                <button onclick="removeFromCompare(${item.id})" title="Xóa">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        });
        // Slots trống
        for (let i = items.length; i < MAX; i++) {
            html += '<div class="compare-bar-slot-empty"><i class="fas fa-plus"></i></div>';
        }
        container.innerHTML = html;

        // Cập nhật link nút So sánh ngay
        if (goBtn) {
            goBtn.href = `compare.php?ids=${ids.join(',')}`;
        }

        // Highlight các nút compare-mini đang active
        document.querySelectorAll('.btn-compare-mini').forEach(btn => {
            const onclick = btn.getAttribute('onclick') || '';
            const match = onclick.match(/addToCompare\((\d+)/);
            if (match && ids.includes(parseInt(match[1]))) {
                btn.classList.add('in-compare');
                btn.title = 'Đã thêm vào so sánh';
            } else {
                btn.classList.remove('in-compare');
                btn.title = 'So sánh';
            }
        });
    }

    window.addToCompare = function(id, name, image) {
        let ids   = getIds();
        let items = getItems();

        if (ids.includes(id)) {
            // Đã có → xóa ra
            ids   = ids.filter(x => x !== id);
            items = items.filter(x => x.id !== id);
            saveIds(ids);
            saveItems(items);
            showToastJS(`Đã xóa "${name}" khỏi danh sách so sánh`, 'warning');
        } else {
            if (ids.length >= MAX) {
                showToastJS(`Chỉ so sánh tối đa ${MAX} sản phẩm! Hãy bỏ bớt 1 sản phẩm.`, 'error');
                return;
            }
            ids.push(id);
            items.push({ id, name, image: image || 'assets/products/no-image.png' });
            saveIds(ids);
            saveItems(items);
            showToastJS(`Đã thêm "${name}" vào so sánh`, 'success');
        }
        renderBar();
    };

    window.removeFromCompare = function(id) {
        let ids   = getIds().filter(x => x !== id);
        let items = getItems().filter(x => x.id !== id);
        saveIds(ids);
        saveItems(items);
        renderBar();
        // Nếu đang trên trang compare → reload
        if (window.location.pathname.includes('compare.php')) {
            if (!ids.length) { window.location.href = 'products.php'; return; }
            if (typeof loadCompare === 'function') loadCompare();
        }
    };

    window.clearCompare = function() {
        localStorage.removeItem(KEY);
        localStorage.removeItem(KEY + '_data');
        renderBar();
    };

    // Khởi tạo bar khi load trang
    document.addEventListener('DOMContentLoaded', renderBar);
})();

