<?php
require_once 'config.php';
$pageTitle = __('cart') . ' – AURA';

$promoMsg = '';
$promoDiscount = 0;
$promoCode = '';
$promoType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_code'])) {
    $code = strtoupper(sanitize($_POST['promo_code']));
    if ($pdo && $code) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM promo_codes WHERE code = ? AND is_active = TRUE AND (expires_at IS NULL OR expires_at > NOW()) AND (max_uses IS NULL OR used_count < max_uses)");
            $stmt->execute([$code]);
            $promo = $stmt->fetch();
            if ($promo) {
                setcookie('aura_promo', json_encode(['code' => $code, 'type' => $promo['type'], 'value' => (int)$promo['value'], 'min_order' => (int)$promo['min_order']]), time() + 86400, '/');
                $promoMsg = 'success';
            } else {
                setcookie('aura_promo', '', time() - 3600, '/');
                $promoMsg = 'error';
            }
        } catch (PDOException $e) {
            $promoMsg = 'error';
        }
    }
    header("Location: cart.php?promo=" . urlencode($promoMsg));
    exit;
}

$storedPromo = isset($_COOKIE['aura_promo']) ? json_decode($_COOKIE['aura_promo'], true) : null;
if ($storedPromo && is_array($storedPromo)) {
    $promoCode = $storedPromo['code'] ?? '';
    $promoType = $storedPromo['type'] ?? '';
    $promoValue = (int)($storedPromo['value'] ?? 0);
    $promoMinOrder = (int)($storedPromo['min_order'] ?? 0);
}

$promoMsgParam = sanitize($_GET['promo'] ?? '');

require_once 'header.php';
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-10">
    <h1 class="font-headline-lg text-headline-lg text-on-surface mb-10"><?php echo __('cart'); ?></h1>
    
    <?php if ($promoMsgParam === 'success'): ?>
    <div class="bg-surface-container-high border border-primary/30 text-primary px-6 py-4 rounded-xl mb-6 font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-3">
        <span class="material-symbols-outlined">check_circle</span>
        <?php echo __('promo_applied'); ?>
    </div>
    <?php elseif ($promoMsgParam === 'error'): ?>
    <div class="bg-error-container text-on-error-container px-6 py-4 rounded-xl mb-6 font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-3">
        <span class="material-symbols-outlined">error</span>
        <?php echo __('promo_invalid'); ?>
    </div>
    <?php endif; ?>

    <!-- Main Cart Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start" id="cart-container">
        
        <!-- ═══ Cart Items ═══ -->
        <div class="lg:col-span-8 space-y-6" id="cart-items-list">
            <!-- JS will render this -->
            <div id="cart-loading" class="space-y-4">
                <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="animate-pulse flex gap-4 bg-surface-container rounded-xl p-4">
                    <div class="w-20 h-24 bg-surface-container-high rounded-lg flex-shrink-0"></div>
                    <div class="flex-1 space-y-3 pt-2">
                        <div class="h-3 bg-surface-container-high rounded w-2/3"></div>
                        <div class="h-3 bg-surface-container rounded w-1/3"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- ═══ Order Summary ═══ -->
        <aside class="lg:col-span-4 lg:sticky lg:top-28">
            <div class="bg-surface-container-high rounded-2xl p-8 border border-outline-variant/10 space-y-8">
                <h2 class="font-headline-md text-headline-md uppercase tracking-widest border-b border-outline-variant/20 pb-4">Résumé</h2>
                
                <!-- Promo Code -->
                <form method="POST" action="cart.php" class="flex gap-2">
                    <input type="text" name="promo_code" 
                           value="<?php echo sanitize($promoCode); ?>"
                           placeholder="<?php echo __('promo_code'); ?>"
                           class="flex-1 bg-surface border border-outline-variant/20 rounded-lg px-4 py-2.5 font-body-md text-on-surface text-sm focus:border-primary focus:ring-0 focus:outline-none uppercase">
                    <button type="submit" class="bg-primary text-on-primary font-label-sm text-label-sm uppercase px-4 py-2.5 rounded-lg hover:bg-primary-fixed transition-colors tracking-wider">
                        OK
                    </button>
                </form>
                <?php if ($promoCode): ?>
                <div class="flex items-center justify-between text-primary font-label-sm text-label-sm bg-primary/10 border border-primary/20 rounded-lg px-4 py-2">
                    <span>🏷️ <?php echo sanitize($promoCode); ?></span>
                    <a href="#" onclick="document.cookie='aura_promo=; path=/; max-age=-1'; window.location.reload();" class="text-error hover:text-on-error-container transition-colors">✕</a>
                </div>
                <?php endif; ?>

                <!-- Summary lines -->
                <div class="space-y-3 font-body-md">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Sous-total</span>
                        <span id="summary-subtotal">— DA</span>
                    </div>
                    <div class="flex justify-between text-primary" id="promo-line" style="display:none!important">
                        <span>Remise (<?php echo sanitize($promoCode); ?>)</span>
                        <span id="summary-discount">— DA</span>
                    </div>
                    <div class="flex justify-between text-on-surface-variant">
                        <span><?php echo __('delivery'); ?></span>
                        <span class="text-outline italic text-sm">Calculé au checkout</span>
                    </div>
                </div>
                
                <div class="h-px bg-gradient-to-r from-transparent via-outline-variant/30 to-transparent"></div>
                
                <div class="flex justify-between items-baseline">
                    <span class="font-headline-md text-headline-md uppercase tracking-widest"><?php echo __('total'); ?></span>
                    <span id="summary-total" class="font-headline-lg text-headline-lg text-primary">— DA</span>
                </div>

                <a href="checkout.php" id="checkout-btn" class="block w-full py-5 bg-primary text-on-primary text-center font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-colors rounded-none">
                    <?php echo __('checkout'); ?>
                </a>
                <a href="shop.php" class="block text-center font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest">
                    <?php echo __('continue_shopping'); ?>
                </a>
            </div>
        </aside>
    </div>
</div>

<script src="assets/js/cart.js"></script>
<script>
const PROMO_CODE = <?php echo json_encode($promoCode); ?>;
const PROMO_TYPE = <?php echo json_encode($promoType); ?>;
const PROMO_VALUE = <?php echo (int)($promoValue ?? 0); ?>;
const PROMO_MIN_ORDER = <?php echo (int)($promoMinOrder ?? 0); ?>;

document.addEventListener('DOMContentLoaded', () => {
    renderCart();
});

function renderCart() {
    const items = readCart();
    const listEl = document.getElementById('cart-items-list');
    const loadingEl = document.getElementById('cart-loading');
    
    // Remove skeleton
    if (loadingEl) loadingEl.remove();

    if (items.length === 0) {
        listEl.innerHTML = `
            <div class="flex flex-col items-center justify-center py-24 text-center col-span-full">
                <span class="material-symbols-outlined text-7xl text-outline mb-6">shopping_bag</span>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-4"><?php echo __('empty_cart'); ?></h2>
                <p class="text-on-surface-variant font-body-md mb-8">Explorez notre boutique et ajoutez vos pièces favorites.</p>
                <a href="shop.php" class="bg-primary text-on-primary px-10 py-4 rounded-full font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-colors">
                    <?php echo __('shop'); ?>
                </a>
            </div>`;
        document.getElementById('summary-subtotal').textContent = formatDA(0);
        document.getElementById('summary-total').textContent = formatDA(0);
        document.getElementById('checkout-btn').classList.add('opacity-40', 'pointer-events-none');
        return;
    }

    let html = '';
    items.forEach(item => {
        const subtotalItem = parseInt(item.price) * parseInt(item.quantity);
        const imgHtml = item.image_url ? `<img src="${item.image_url}" alt="${item.name}" class="w-full h-full object-cover">` : `<span class="material-symbols-outlined text-4xl text-outline">image</span>`;
        
        html += `
        <div class="flex gap-4 bg-surface-container rounded-xl p-5 border border-outline-variant/10 hover:border-outline-variant/30 transition-colors">
            <div class="w-20 h-24 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container-high flex items-center justify-center">
                ${imgHtml}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-body-md text-on-surface font-medium truncate">${item.name}</h3>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mt-1">
                    ${item.color ? item.color + ' · ' : ''}${item.size ? item.size : ''}
                </p>
                <div class="flex items-center justify-between mt-4 flex-wrap gap-3">
                    <div class="flex items-center border border-outline-variant/30 rounded-lg overflow-hidden">
                        <button onclick="changeQty(${item.product_id}, '${item.color}', '${item.size}', -1)" 
                                class="w-9 h-9 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">
                            <span class="material-symbols-outlined text-lg">remove</span>
                        </button>
                        <span class="w-10 text-center font-body-md text-on-surface">${item.quantity}</span>
                        <button onclick="changeQty(${item.product_id}, '${item.color}', '${item.size}', 1)"
                                class="w-9 h-9 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high transition-colors">
                            <span class="material-symbols-outlined text-lg">add</span>
                        </button>
                    </div>
                    <span class="font-body-md text-primary font-medium">${formatDA(subtotalItem)}</span>
                    <button onclick="deleteItem(${item.product_id}, '${item.color}', '${item.size}')"
                            class="material-symbols-outlined text-on-surface-variant hover:text-error transition-colors text-xl">delete</button>
                </div>
            </div>
        </div>`;
    });

    listEl.innerHTML = html;
    updateSummary(items);
}

function updateSummary(items) {
    const subtotal = items.reduce((sum, i) => sum + (parseInt(i.price) * parseInt(i.quantity)), 0);
    let discount = 0;

    if (PROMO_CODE && subtotal >= PROMO_MIN_ORDER) {
        if (PROMO_TYPE === 'percentage') {
            discount = Math.floor(subtotal * PROMO_VALUE / 100);
        } else {
            discount = PROMO_VALUE;
        }
        const promoLine = document.getElementById('promo-line');
        if (promoLine) promoLine.style.display = 'flex';
        const discountEl = document.getElementById('summary-discount');
        if (discountEl) discountEl.textContent = '-' + formatDA(discount);
    }

    const total = Math.max(0, subtotal - discount);
    document.getElementById('summary-subtotal').textContent = formatDA(subtotal);
    document.getElementById('summary-total').textContent = formatDA(total);
}

function changeQty(product_id, color, size, delta) {
    updateQty(product_id, color, size, delta);
    renderCart();
}

function deleteItem(product_id, color, size) {
    removeItem(product_id, color, size);
    renderCart();
}
</script>

<?php require_once 'footer.php'; ?>
