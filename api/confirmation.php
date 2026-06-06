<?php
require_once 'config.php';
$auth = requireAuth();

$orderNumber = sanitize($_GET['order'] ?? '');
$order = null;
$orderItems = [];
$statusHistory = [];
$error = null;

if (!$orderNumber || !$pdo) {
    header("Location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
    $stmt->execute([$orderNumber, $auth['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        header("Location: index.php");
        exit;
    }

    $stmt2 = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt2->execute([$order['id']]);
    $orderItems = $stmt2->fetchAll();

    $stmt3 = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY changed_at ASC");
    $stmt3->execute([$order['id']]);
    $statusHistory = $stmt3->fetchAll();
} catch (PDOException $e) {
    $error = $e->getMessage();
}

$pageTitle = __('order_success') . ' – AURA';
require_once 'header.php';
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-8 pb-24">
    
    <!-- Success Header -->
    <div class="text-center mb-16">
        <div class="w-20 h-20 rounded-full bg-primary/10 border border-primary/30 flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-5xl text-primary">check_circle</span>
        </div>
        <span class="font-label-sm text-label-sm uppercase tracking-[0.4em] text-primary block mb-4">Commande Validée</span>
        <h1 class="font-display-lg text-display-lg-mobile md:text-headline-lg text-on-surface mb-4"><?php echo __('thank_you'); ?></h1>
        <div class="bg-surface-container border border-outline-variant/20 rounded-xl inline-block px-8 py-3">
            <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-widest">N° </span>
            <span class="font-headline-md text-headline-md text-primary"><?php echo sanitize($order['order_number']); ?></span>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="bg-error-container text-on-error-container p-6 rounded-xl mb-8"><?php echo sanitize($error); ?></div>
    <?php else: ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Left: Items + Shipping -->
        <div class="lg:col-span-8 space-y-8">
            <!-- Order Items -->
            <div class="bg-surface-container rounded-2xl border border-outline-variant/10 p-8">
                <h2 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant mb-6">Articles commandés</h2>
                <div class="space-y-6">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="flex gap-4 items-start">
                        <?php if ($item['image_url']): ?>
                        <div class="w-16 h-20 rounded-lg overflow-hidden flex-shrink-0 bg-surface-container-high">
                            <img src="<?php echo sanitize($item['image_url']); ?>" alt="<?php echo sanitize($item['product_name']); ?>" class="w-full h-full object-cover">
                        </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <p class="font-body-md text-on-surface font-medium"><?php echo sanitize($item['product_name']); ?></p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mt-1">
                                <?php if ($item['color']): echo sanitize($item['color']) . ' · '; endif; ?>
                                <?php if ($item['size']): echo sanitize($item['size']) . ' · '; endif; ?>
                                Qté: <?php echo (int)$item['quantity']; ?>
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-body-md text-primary"><?php echo format_price((int)$item['price'] * (int)$item['quantity']); ?></span>
                            <span class="block font-label-sm text-label-sm text-on-surface-variant"><?php echo format_price($item['price']); ?> / unité</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="bg-surface-container rounded-2xl border border-outline-variant/10 p-8">
                <h2 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant mb-6">Adresse de livraison</h2>
                <div class="space-y-2 font-body-md text-on-surface">
                    <p class="font-medium"><?php echo sanitize($order['fullname']); ?></p>
                    <p class="text-on-surface-variant"><?php echo sanitize($order['address']); ?>, <?php echo sanitize($order['commune']); ?></p>
                    <p class="text-on-surface-variant"><?php echo sanitize($order['wilaya']); ?></p>
                    <p class="text-on-surface-variant"><?php echo sanitize($order['phone']); ?></p>
                    <span class="inline-block mt-2 px-3 py-1 border border-outline-variant/30 rounded-full font-label-sm text-label-sm uppercase text-on-surface-variant">
                        <?php echo $order['delivery_method'] === 'home' ? __('delivery_home') : __('delivery_relay'); ?>
                    </span>
                </div>
            </div>

            <!-- Status History -->
            <?php if (!empty($statusHistory)): ?>
            <div class="bg-surface-container rounded-2xl border border-outline-variant/10 p-8">
                <h2 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant mb-6">Historique du statut</h2>
                <div class="space-y-4">
                    <?php foreach ($statusHistory as $h): ?>
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-3 h-3 rounded-full bg-primary flex-shrink-0 mt-1"></div>
                            <div class="flex-1 w-px bg-outline-variant/20 mt-1"></div>
                        </div>
                        <div class="pb-4">
                            <span class="font-label-sm text-label-sm text-primary uppercase tracking-wider"><?php echo sanitize($h['status']); ?></span>
                            <?php if ($h['note']): ?>
                            <p class="font-body-md text-on-surface-variant text-sm mt-1"><?php echo sanitize($h['note']); ?></p>
                            <?php endif; ?>
                            <p class="font-label-sm text-label-sm text-outline mt-1"><?php echo date('d/m/Y H:i', strtotime($h['changed_at'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: Summary -->
        <aside class="lg:col-span-4">
            <div class="bg-surface-container-high rounded-2xl border border-outline-variant/10 p-8 space-y-6">
                <h2 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant">Récapitulatif</h2>
                <div class="space-y-3 font-body-md">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Sous-total</span>
                        <span><?php echo format_price((int)$order['total_amount'] - (int)$order['delivery_fee'] + (int)$order['discount_amount']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant"><?php echo __('delivery'); ?></span>
                        <span><?php echo format_price($order['delivery_fee']); ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="flex justify-between text-primary">
                        <span>Remise (<?php echo sanitize($order['promo_code'] ?? ''); ?>)</span>
                        <span>-<?php echo format_price($order['discount_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="h-px bg-gradient-to-r from-transparent via-outline-variant/30 to-transparent"></div>
                <div class="flex justify-between items-baseline">
                    <span class="font-headline-md text-headline-md uppercase"><?php echo __('total'); ?></span>
                    <span class="font-headline-lg text-headline-lg text-primary"><?php echo format_price($order['total_amount']); ?></span>
                </div>
                <div class="space-y-3 pt-4">
                    <a href="settings.php#orders" class="block w-full py-4 bg-primary text-on-primary text-center font-label-sm text-label-sm uppercase tracking-widest rounded-lg hover:bg-primary-fixed transition-colors">
                        <?php echo __('my_account'); ?>
                    </a>
                    <a href="shop.php" class="block w-full py-4 border border-outline-variant/30 text-on-surface-variant text-center font-label-sm text-label-sm uppercase tracking-widest rounded-lg hover:border-primary hover:text-primary transition-colors">
                        <?php echo __('continue_shopping'); ?>
                    </a>
                </div>
            </div>
        </aside>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
