function readCart() {
    const match = document.cookie.match(/^(?:.*;)?\s*aura_cart\s*=\s*([^;]+)(?:.*)?$/);
    if (match) {
        try {
            const parsed = JSON.parse(decodeURIComponent(match[1]));
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }
    return [];
}

function saveCart(cart) {
    document.cookie = 'aura_cart=' + encodeURIComponent(JSON.stringify(cart)) + '; path=/; max-age=' + (86400 * 30);
    updateBadge();
}

function addToCart(product_id, name, color, size, price, image_url, qty) {
    let cart = readCart();
    let existingIndex = cart.findIndex(item => item.product_id === product_id && item.color === color && item.size === size);
    
    if (existingIndex > -1) {
        cart[existingIndex].quantity += parseInt(qty);
    } else {
        cart.push({
            product_id: parseInt(product_id),
            name: name,
            color: color,
            size: size,
            price: parseInt(price),
            image_url: image_url,
            quantity: parseInt(qty)
        });
    }
    saveCart(cart);
}

function updateQty(product_id, color, size, delta) {
    let cart = readCart();
    let index = cart.findIndex(item => item.product_id === product_id && item.color === color && item.size === size);
    if (index > -1) {
        cart[index].quantity += parseInt(delta);
        if (cart[index].quantity < 1) {
            cart[index].quantity = 1;
        }
        saveCart(cart);
    }
}

function removeItem(product_id, color, size) {
    let cart = readCart();
    cart = cart.filter(item => !(item.product_id === product_id && item.color === color && item.size === size));
    saveCart(cart);
}

function getTotal() {
    const items = readCart();
    return items.reduce((sum, item) => sum + (parseInt(item.price) * parseInt(item.quantity)), 0);
}

function getCount() {
    const items = readCart();
    return items.reduce((sum, item) => sum + parseInt(item.quantity), 0);
}

function updateBadge() {
    const badge = document.getElementById('cart-badge');
    if (badge) {
        const count = getCount();
        badge.textContent = count;
        if (count > 0) {
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

function formatDA(amount) {
    return Math.floor(amount).toLocaleString('fr-DZ') + ' DA';
}

function showToast(message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    // Styling from Stitch tokens (forest/sage theme, surface-container with thin border and light on-surface text)
    toast.className = "flex items-center gap-3 bg-[#0f231c] border border-[#b2cdbc]/30 px-6 py-4 rounded-lg shadow-2xl pointer-events-auto transform translate-y-2 opacity-0 transition-all duration-300 text-on-surface font-label-sm text-label-sm uppercase tracking-wider";
    toast.innerHTML = `
        <span class="material-symbols-outlined text-[#b2cdbc]">check_circle</span>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    }, 10);

    // Animate out
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3500);
}

// Ensure the badge is updated on load
document.addEventListener('DOMContentLoaded', () => {
    updateBadge();
});
