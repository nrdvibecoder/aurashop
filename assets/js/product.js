window.productColors = window.productColors || [];
window.colorImages = window.colorImages || {};
window.productSizes = window.productSizes || [];
window.selectedColor = null;
window.selectedSize = null;

document.addEventListener('DOMContentLoaded', () => {
    const colorSwatches = document.querySelectorAll('[data-color-swatch]');
    const sizeButtons = document.querySelectorAll('[data-size-btn]');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const mainImage = document.getElementById('main-product-image');
    const thumbnailContainer = document.getElementById('thumbnail-container');
    const qtyInput = document.getElementById('qty-input');
    const qtyPlus = document.getElementById('qty-plus');
    const qtyMinus = document.getElementById('qty-minus');

    // ── Color Swatch Selection ──
    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', () => {
            const color = swatch.dataset.colorSwatch;
            window.selectedColor = color;

            // Update active state
            colorSwatches.forEach(s => {
                s.classList.remove('ring-2', 'ring-offset-4', 'ring-offset-surface', 'ring-primary');
                s.classList.add('ring-0');
            });
            swatch.classList.add('ring-2', 'ring-offset-4', 'ring-offset-surface', 'ring-primary');
            swatch.classList.remove('ring-0');

            // Update color label
            const colorLabel = document.getElementById('color-label');
            if (colorLabel) colorLabel.textContent = color;

            // Swap gallery images
            const images = window.colorImages[color];
            if (images && images.length > 0) {
                if (mainImage) {
                    mainImage.src = images[0];
                }
                if (thumbnailContainer) {
                    thumbnailContainer.innerHTML = '';
                    images.forEach((imgSrc, idx) => {
                        const thumb = document.createElement('button');
                        thumb.className = `aspect-square overflow-hidden rounded-xl bg-surface-container-low border-2 transition-all ${idx === 0 ? 'border-primary' : 'border-transparent hover:border-outline-variant'}`;
                        thumb.innerHTML = `<img src="${imgSrc}" class="w-full h-full object-cover" alt="Color variant ${idx + 1}">`;
                        thumb.addEventListener('click', () => {
                            if (mainImage) mainImage.src = imgSrc;
                            thumbnailContainer.querySelectorAll('button').forEach(t => {
                                t.classList.remove('border-primary');
                                t.classList.add('border-transparent');
                            });
                            thumb.classList.add('border-primary');
                            thumb.classList.remove('border-transparent');
                        });
                        thumbnailContainer.appendChild(thumb);
                    });
                }
            }
        });
    });

    // Pre-select first color on page load
    const firstColorBtn = document.querySelector('[data-color-swatch]');
    if (firstColorBtn) {
        window.selectedColor = firstColorBtn.dataset.colorSwatch;
    }

    // Set forced size if sizes are not defined (e.g., accessories)
    const forcedSizeEl = document.getElementById('forced-size');
    if (forcedSizeEl) {
        window.selectedSize = forcedSizeEl.value;
    }

    // ── Size Button Selection ──
    sizeButtons.forEach(btn => {
        if (btn.dataset.disabled === 'true') return;
        btn.addEventListener('click', () => {
            window.selectedSize = btn.dataset.sizeBtn;
            // Deselect all
            sizeButtons.forEach(b => b.setAttribute('data-selected', 'false'));
            // Select clicked
            btn.setAttribute('data-selected', 'true');
        });
    });

    // Pre-select first size on page load if size buttons exist
    const firstSizeBtn = document.querySelector('[data-size-btn]:not([data-disabled="true"])');
    if (firstSizeBtn) {
        firstSizeBtn.click();
    }

    // ── Quantity Stepper ──
    if (qtyPlus) {
        qtyPlus.addEventListener('click', () => {
            const cur = parseInt(qtyInput.value);
            qtyInput.value = cur + 1;
        });
    }
    if (qtyMinus) {
        qtyMinus.addEventListener('click', () => {
            const cur = parseInt(qtyInput.value);
            if (cur > 1) qtyInput.value = cur - 1;
        });
    }

    // ── Add to Cart ──
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', () => {
            if (!window.selectedColor) {
                showInlineError('Veuillez sélectionner une couleur. / Please select a color.');
                shakeBtn(addToCartBtn);
                return;
            }
            if (!window.selectedSize) {
                showInlineError('Veuillez sélectionner une taille. / Please select a size.');
                shakeBtn(addToCartBtn);
                return;
            }

            const productId = parseInt(addToCartBtn.dataset.productId);
            const productName = addToCartBtn.dataset.productName;
            const productPrice = parseInt(addToCartBtn.dataset.productPrice);
            const productImage = mainImage ? mainImage.src : '';
            const qty = parseInt(qtyInput ? qtyInput.value : 1);

            addToCart(productId, productName, window.selectedColor, window.selectedSize, productPrice, productImage, qty);
            showToast('Ajouté au panier ! / Added to cart!');
        });
    }

    // ── Thumbnail click on initial load ──
    const firstThumb = thumbnailContainer ? thumbnailContainer.querySelector('button') : null;
    if (firstThumb) firstThumb.classList.add('border-primary');
});

function showInlineError(msg) {
    let errEl = document.getElementById('product-error-msg');
    if (!errEl) {
        errEl = document.createElement('p');
        errEl.id = 'product-error-msg';
        errEl.className = 'text-error font-label-sm text-label-sm mt-2 transition-all';
        document.getElementById('add-to-cart-btn').parentNode.appendChild(errEl);
    }
    errEl.textContent = msg;
    setTimeout(() => { if (errEl) errEl.textContent = ''; }, 4000);
}

function shakeBtn(btn) {
    btn.classList.add('animate-shake');
    setTimeout(() => btn.classList.remove('animate-shake'), 500);
}
