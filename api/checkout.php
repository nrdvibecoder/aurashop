<?php
require_once 'config.php';
$auth = requireAuth();

$pageTitle = __('checkout');

$deliveryZones = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM delivery_zones ORDER BY wilaya_code ASC");
        $deliveryZones = $stmt->fetchAll();
    } catch (PDOException $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $cartItems = get_cart();
    if (empty($cartItems)) {
        header("Location: cart.php");
        exit;
    }

    $fullname = sanitize($_POST['fullname'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $wilaya = sanitize($_POST['wilaya'] ?? '');
    $commune = sanitize($_POST['commune'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $delivery_method = in_array($_POST['delivery_method'] ?? '', ['home', 'relay']) ? $_POST['delivery_method'] : 'home';
    $delivery_fee = (int)($_POST['delivery_fee'] ?? 0);
    $notes = sanitize($_POST['notes'] ?? '');

    
    $storedPromo = isset($_COOKIE['aura_promo']) ? json_decode($_COOKIE['aura_promo'], true) : null;
    $promoCode = $storedPromo['code'] ?? '';
    $promoType = $storedPromo['type'] ?? '';
    $promoValue = (int)($storedPromo['value'] ?? 0);

    
    $subtotal = array_reduce($cartItems, fn($sum, $i) => $sum + (int)$i['price'] * (int)$i['quantity'], 0);
    $discount = 0;
    if ($promoCode) {
        if ($promoType === 'percentage') {
            $discount = (int)($subtotal * $promoValue / 100);
        } else {
            $discount = $promoValue;
        }
    }
    $total = max(0, $subtotal + $delivery_fee - $discount);

    if ($pdo && $fullname && $phone && $wilaya && $commune && $address) {
        try {
            $pdo->beginTransaction();

            $orderNumber = generate_order_number();

            
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, fullname, phone, wilaya, commune, address, delivery_method, total_amount, discount_amount, delivery_fee, promo_code, status, notes) 
                                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'Pending',?)");
            $stmt->execute([$orderNumber, $auth['id'], $fullname, $phone, $wilaya, $commune, $address, $delivery_method, $total, $discount, $delivery_fee, $promoCode ?: null, $notes]);
            $orderId = $pdo->lastInsertId();

            
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, size, color, image_url) VALUES (?,?,?,?,?,?,?,?)");
            $stockStmt = $pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?");

            foreach ($cartItems as $item) {
                $itemStmt->execute([
                    $orderId,
                    (int)$item['product_id'],
                    sanitize($item['name']),
                    (int)$item['quantity'],
                    (int)$item['price'],
                    sanitize($item['size'] ?? ''),
                    sanitize($item['color'] ?? ''),
                    $item['image_url'] ?? ''
                ]);
                $stockStmt->execute([(int)$item['quantity'], (int)$item['product_id']]);
            }

            
            $histStmt = $pdo->prepare("INSERT INTO order_status_history (order_id, status, note) VALUES (?, 'Pending', 'Commande créée')");
            $histStmt->execute([$orderId]);

            
            if ($promoCode) {
                $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = ?")->execute([$promoCode]);
            }

            $pdo->commit();

            
            setcookie('aura_cart', '', time() - 3600, '/');
            setcookie('aura_promo', '', time() - 3600, '/');

            header("Location: confirmation.php?order=" . urlencode($orderNumber));
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $checkoutError = "Une erreur est survenue lors de la commande. Veuillez réessayer.";
        }
    } else {
        $checkoutError = "Veuillez remplir tous les champs obligatoires.";
    }
}

$storedPromo = isset($_COOKIE['aura_promo']) ? json_decode($_COOKIE['aura_promo'], true) : null;
$promoCode = $storedPromo['code'] ?? '';
$promoType = $storedPromo['type'] ?? '';
$promoValue = (int)($storedPromo['value'] ?? 0);

require_once 'header.php';
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-8 pb-16">
    <?php if (!empty($checkoutError)): ?>
    <div class="bg-error-container text-on-error-container px-6 py-4 rounded-xl mb-8 font-label-sm text-label-sm flex items-center gap-3">
        <span class="material-symbols-outlined">error</span>
        <?php echo sanitize($checkoutError); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <!-- ═══ CHECKOUT FORM ═══ -->
        <div class="lg:col-span-8 space-y-12">
            <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg uppercase tracking-tight"><?php echo __('checkout'); ?></h1>

            <form method="POST" id="checkout-form" class="space-y-12">
                <!-- STEP 1: Shipping -->
                <div data-step="1" class="space-y-8">
                    <div class="flex items-center gap-4">
                        <span data-step-indicator="1" class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-label-sm text-label-sm">1</span>
                        <h2 class="font-headline-md text-headline-md uppercase tracking-widest">Informations de Livraison</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('fullname'); ?> *</label>
                            <input id="input-fullname" name="fullname" type="text" required
                                   value="<?php echo sanitize($_POST['fullname'] ?? ''); ?>"
                                   class="bg-surface-container border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('phone'); ?> * (05/06/07XXXXXXXX)</label>
                            <input id="input-phone" name="phone" type="tel" required pattern="^(05|06|07)\d{8}$"
                                   value="<?php echo sanitize($_POST['phone'] ?? ''); ?>"
                                   placeholder="0612345678"
                                   class="bg-surface-container border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('wilaya'); ?> *</label>
                            <select id="wilaya-select" name="wilaya" required
                                    class="bg-surface-container border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors appearance-none">
                                <option value="">— Choisir la wilaya —</option>
                                <?php foreach ($deliveryZones as $zone): ?>
                                <option value="<?php echo sanitize($zone['wilaya_name']); ?>" 
                                        <?php echo (($_POST['wilaya'] ?? '') === $zone['wilaya_name']) ? 'selected' : ''; ?>>
                                    <?php echo sprintf('%02d', (int)$zone['wilaya_code']); ?> – <?php echo sanitize($zone['wilaya_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('commune'); ?> *</label>
                            <input id="input-commune" name="commune" type="text" required
                                   value="<?php echo sanitize($_POST['commune'] ?? ''); ?>"
                                   class="bg-surface-container border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                        </div>
                        <div class="md:col-span-2 flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('address'); ?> *</label>
                            <input id="input-address" name="address" type="text" required
                                   value="<?php echo sanitize($_POST['address'] ?? ''); ?>"
                                   class="bg-surface-container border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                        </div>
                        <div class="md:col-span-2 flex flex-col gap-2">
                            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('notes'); ?></label>
                            <textarea name="notes" rows="2"
                                      class="bg-surface-container border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors resize-none"><?php echo sanitize($_POST['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <button type="button" data-step-next="2" class="bg-primary text-on-primary font-label-sm text-label-sm uppercase px-10 py-4 rounded-full tracking-widest hover:bg-primary-fixed transition-colors">
                        Continuer →
                    </button>
                </div>

                <!-- STEP 2: Delivery Method -->
                <div data-step="2" class="space-y-8" style="display:none">
                    <div class="flex items-center gap-4">
                        <span data-step-indicator="2" class="w-8 h-8 rounded-full border border-outline-variant/40 text-on-surface-variant flex items-center justify-center font-label-sm text-label-sm">2</span>
                        <h2 class="font-headline-md text-headline-md uppercase tracking-widest">Mode de Livraison</h2>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-6 bg-surface-container border border-primary/40 rounded-xl cursor-pointer hover:bg-surface-container-high transition-colors">
                            <div class="flex items-center gap-4">
                                <input id="delivery-home" type="radio" name="delivery_method" value="home" checked class="text-primary focus:ring-primary">
                                <div>
                                    <span class="font-body-md text-on-surface font-medium block"><?php echo __('delivery_home'); ?></span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">Livré directement chez vous</span>
                                </div>
                            </div>
                            <span id="home-fee-display" class="font-body-md text-primary">— DA</span>
                        </label>

                        <label class="flex items-center justify-between p-6 bg-surface-container border border-outline-variant/20 rounded-xl cursor-pointer hover:bg-surface-container-high transition-colors">
                            <div class="flex items-center gap-4">
                                <input id="delivery-relay" type="radio" name="delivery_method" value="relay" class="text-primary focus:ring-primary">
                                <div>
                                    <span class="font-body-md text-on-surface font-medium block"><?php echo __('delivery_relay'); ?></span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">Récupérez au point le plus proche</span>
                                </div>
                            </div>
                            <span id="relay-fee-display" class="font-body-md text-primary">— DA</span>
                        </label>
                    </div>

                    <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-4 flex items-center gap-4 text-on-surface-variant font-label-sm text-label-sm">
                        <span class="material-symbols-outlined text-primary">schedule</span>
                        <span>Délai estimé: <span id="delivery-days-display" class="text-on-surface font-medium">— jours</span></span>
                    </div>

                    <input type="hidden" id="delivery-fee-input" name="delivery_fee" value="0">

                    <div class="flex gap-4">
                        <button type="button" data-step-back="1" class="border border-outline-variant/30 text-on-surface-variant font-label-sm text-label-sm uppercase px-8 py-4 rounded-full tracking-widest hover:border-primary hover:text-primary transition-colors">
                            ← Retour
                        </button>
                        <button type="button" data-step-next="3" class="bg-primary text-on-primary font-label-sm text-label-sm uppercase px-10 py-4 rounded-full tracking-widest hover:bg-primary-fixed transition-colors">
                            Continuer →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Payment & Summary -->
                <div data-step="3" class="space-y-8" style="display:none">
                    <div class="flex items-center gap-4">
                        <span data-step-indicator="3" class="w-8 h-8 rounded-full border border-outline-variant/40 text-on-surface-variant flex items-center justify-center font-label-sm text-label-sm">3</span>
                        <h2 class="font-headline-md text-headline-md uppercase tracking-widest"><?php echo __('payment'); ?></h2>
                    </div>

                    <div class="bg-surface-container border border-primary/20 rounded-xl p-6 flex items-center gap-6">
                        <span class="material-symbols-outlined text-5xl text-primary">payments</span>
                        <div>
                            <h3 class="font-body-md text-on-surface font-medium">Paiement à la Livraison</h3>
                            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mt-1">Cash on delivery – Le seul mode disponible</p>
                        </div>
                    </div>

                    <input type="hidden" id="final-total-input" name="final_total" value="0">
                    <input type="hidden" name="place_order" value="1">

                    <div class="flex gap-4">
                        <button type="button" data-step-back="2" class="border border-outline-variant/30 text-on-surface-variant font-label-sm text-label-sm uppercase px-8 py-4 rounded-full tracking-widest hover:border-primary hover:text-primary transition-colors">
                            ← Retour
                        </button>
                        <button type="submit" class="flex-1 bg-primary text-on-primary font-label-sm text-label-sm uppercase py-5 rounded-full tracking-widest hover:bg-primary-fixed transition-all hover:scale-105 active:scale-95">
                            <?php echo __('place_order'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ═══ ORDER SUMMARY ═══ -->
        <aside class="lg:col-span-4 lg:sticky lg:top-28">
            <div class="bg-surface-container-high rounded-2xl p-8 border border-outline-variant/10 space-y-8">
                <h2 class="font-headline-md text-headline-md uppercase tracking-widest border-b border-outline-variant/20 pb-4">Votre Commande</h2>
                <div id="checkout-cart-items" class="space-y-4">
                    <!-- Rendered by JS -->
                </div>
                <div class="h-px bg-gradient-to-r from-transparent via-outline-variant/30 to-transparent"></div>
                <div class="space-y-3 font-body-md">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Sous-total</span>
                        <span id="summary-subtotal" data-amount="0">— DA</span>
                    </div>
                    <div class="flex justify-between text-primary">
                        <span><?php echo __('delivery'); ?></span>
                        <span id="delivery-fee-display">— DA</span>
                    </div>
                    <?php if ($promoCode): ?>
                    <div class="flex justify-between text-error">
                        <span>Remise (<?php echo sanitize($promoCode); ?>)</span>
                        <span id="summary-discount" data-amount="0">— DA</span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="h-px bg-gradient-to-r from-transparent via-outline-variant/30 to-transparent"></div>
                <div class="flex justify-between items-baseline">
                    <span class="font-headline-md text-headline-md uppercase tracking-widest"><?php echo __('total'); ?></span>
                    <span id="summary-total" data-amount="0" class="font-headline-lg text-headline-lg text-primary">— DA</span>
                </div>
                <p class="text-center font-label-sm text-[10px] text-on-surface-variant uppercase tracking-widest opacity-60">
                    Paiement 100% sécurisé. Traité à la livraison.
                </p>
            </div>
        </aside>
    </div>
</div>

<script>
window.deliveryZones = <?php echo json_encode(array_values($deliveryZones), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const PROMO_CODE = <?php echo json_encode($promoCode); ?>;
const PROMO_TYPE = <?php echo json_encode($promoType); ?>;
const PROMO_VALUE = <?php echo (int)$promoValue; ?>;
</script>
<script src="assets/js/cart.js"></script>
<script>
// Render cart items in the sidebar
document.addEventListener('DOMContentLoaded', () => {
    const items = readCart();
    const container = document.getElementById('checkout-cart-items');
    
    if (items.length === 0) {
        window.location.href = 'cart.php';
        return;
    }

    let subtotal = 0;
    let html = '';
    items.forEach(item => {
        const itemTotal = parseInt(item.price) * parseInt(item.quantity);
        subtotal += itemTotal;
        html += `
        <div class="flex gap-3 items-center">
            <div class="w-14 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container-lowest border border-outline-variant/10">
                ${item.image_url ? `<img src="${item.image_url}" class="w-full h-full object-cover">` : '<span class="material-symbols-outlined text-2xl text-outline flex items-center justify-center h-full">image</span>'}
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-body-md text-on-surface text-sm font-medium truncate">${item.name}</p>
                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wide mt-0.5">${item.color ? item.color + ' · ' : ''}${item.size} × ${item.quantity}</p>
            </div>
            <span class="font-body-md text-primary text-sm flex-shrink-0">${formatDA(itemTotal)}</span>
        </div>`;
    });

    container.innerHTML = html;

    const subtotalEl = document.getElementById('summary-subtotal');
    if (subtotalEl) {
        subtotalEl.textContent = formatDA(subtotal);
        subtotalEl.dataset.amount = subtotal;
    }

    // Apply promo for display
    let discount = 0;
    if (PROMO_CODE) {
        if (PROMO_TYPE === 'percentage') discount = Math.floor(subtotal * PROMO_VALUE / 100);
        else discount = PROMO_VALUE;
        const discEl = document.getElementById('summary-discount');
        if (discEl) { discEl.textContent = '-' + formatDA(discount); discEl.dataset.amount = discount; }
    }

    const total = Math.max(0, subtotal - discount);
    const totalEl = document.getElementById('summary-total');
    if (totalEl) { totalEl.textContent = formatDA(total); totalEl.dataset.amount = total; }

    // Update hidden final total
    const finalInput = document.getElementById('final-total-input');
    if (finalInput) finalInput.value = total;
});
</script>
<script src="assets/js/checkout.js"></script>

<?php require_once 'footer.php'; ?>
