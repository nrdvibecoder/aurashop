<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Détails de Commande – AURA';
$error = '';
$success = '';
$order = null;
$items = [];
$history = [];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_orders.php");
    exit;
}

$orderId = (int)$_GET['id'];

if ($pdo) {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $newStatus = sanitize($_POST['status'] ?? '');
        $note = sanitize($_POST['note'] ?? '');

        if (in_array($newStatus, ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'])) {
            try {
                $pdo->beginTransaction();

                
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $orderId]);

                
                $stmt = $pdo->prepare("INSERT INTO order_status_history (order_id, status, note, changed_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$orderId, $newStatus, $note ?: 'Mise à jour du statut par l\'administrateur.']);

                $pdo->commit();
                $success = 'Statut mis à jour avec succès !';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            }
        } else {
            $error = 'Statut invalide.';
        }
    }

    try {
        
        $stmt = $pdo->prepare("
            SELECT o.*, u.fullname as customer_name, u.email as customer_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll();

            
            $stmt = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY changed_at DESC");
            $stmt->execute([$orderId]);
            $history = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération des détails : ' . $e->getMessage();
    }
}

if (!$order) {
    header("Location: admin_orders.php");
    exit;
}

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Commande #<?php echo sanitize($order['order_number']); ?></h1>
            <p class="font-body-md text-on-surface-variant">Créée le <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
        </div>
        <div class="flex gap-3">
            <a href="admin_orders.php" class="bg-surface-container border border-outline-variant/20 text-on-surface px-5 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-2 hover:bg-surface-container/80 transition-colors">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Toutes les Commandes
            </a>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="bg-error-container text-on-error-container px-6 py-4 rounded-lg mb-8 font-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-xl">error</span>
        <?php echo sanitize($error); ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="bg-primary-container text-on-primary-container px-6 py-4 rounded-lg mb-8 font-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-xl">check_circle</span>
        <?php echo sanitize($success); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Details (2 cols) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Order Items Card -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
                <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">shopping_bag</span>
                    Articles commandés
                </h2>

                <div class="divide-y divide-outline-variant/10">
                    <?php foreach ($items as $item): ?>
                    <div class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                        <div class="w-16 h-20 bg-surface rounded-lg overflow-hidden flex-shrink-0 border border-outline-variant/10">
                            <?php if ($item['image_url']): ?>
                            <img src="<?php echo $item['image_url']; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-surface-container-high text-on-surface-variant">
                                <span class="material-symbols-outlined">image</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow min-w-0">
                            <h4 class="font-body-md text-on-surface font-semibold truncate leading-tight"><?php echo sanitize($item['product_name']); ?></h4>
                            <p class="font-label-sm text-label-sm text-on-surface-variant opacity-75 mt-1">
                                <?php if ($item['color']): ?>Couleur: <?php echo sanitize($item['color']); ?><?php endif; ?>
                                <?php if ($item['size']): ?><?php echo $item['color'] ? ' | ' : ''; ?>Taille: <?php echo sanitize($item['size']); ?><?php endif; ?>
                            </p>
                            <p class="font-label-sm text-label-sm text-on-surface-variant opacity-70 mt-0.5">Quantité: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-body-md text-on-surface font-bold"><?php echo format_price($item['price'] * $item['quantity']); ?></span>
                            <div class="text-xs text-on-surface-variant opacity-70 mt-1"><?php echo format_price($item['price']); ?> / u</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">local_shipping</span>
                        Informations Livraison
                    </h3>
                    <div class="space-y-3 font-body-md text-on-surface-variant">
                        <p><strong class="text-on-surface">Client:</strong> <?php echo sanitize($order['fullname']); ?></p>
                        <p><strong class="text-on-surface">Téléphone:</strong> <?php echo sanitize($order['phone']); ?></p>
                        <p><strong class="text-on-surface">Wilaya:</strong> <?php echo sanitize($order['wilaya']); ?></p>
                        <p><strong class="text-on-surface">Commune:</strong> <?php echo sanitize($order['commune']); ?></p>
                        <p><strong class="text-on-surface">Adresse:</strong> <?php echo nl2br(sanitize($order['address'])); ?></p>
                        <p><strong class="text-on-surface">Mode:</strong> <?php echo ($order['delivery_method'] === 'home') ? 'Domicile' : 'Point Relais'; ?></p>
                    </div>
                </div>

                <div>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">payments</span>
                        Résumé Financier
                    </h3>
                    <div class="space-y-3 font-body-md text-on-surface-variant">
                        <div class="flex justify-between">
                            <span>Sous-total articles</span>
                            <span class="text-on-surface font-semibold"><?php echo format_price($order['total_amount'] - $order['delivery_fee'] + $order['discount_amount']); ?></span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="flex justify-between text-error">
                            <span>Remise <?php echo $order['promo_code'] ? '(' . sanitize($order['promo_code']) . ')' : ''; ?></span>
                            <span>- <?php echo format_price($order['discount_amount']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <span>Frais de livraison</span>
                            <span class="text-on-surface font-semibold"><?php echo format_price($order['delivery_fee']); ?></span>
                        </div>
                        <div class="h-px bg-outline-variant/10 my-2"></div>
                        <div class="flex justify-between text-lg font-bold">
                            <span class="text-on-surface">Total payé</span>
                            <span class="text-primary"><?php echo format_price($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <?php if ($order['notes']): ?>
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
                <h3 class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75 mb-3">Notes de commande client</h3>
                <p class="font-body-md text-on-surface italic">" <?php echo nl2br(sanitize($order['notes'])); ?> "</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Management Panel & History (1 col) -->
        <div class="space-y-8">
            <!-- Update Status Form -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm space-y-6">
                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">edit_square</span>
                    Mettre à jour le Statut
                </h2>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Statut de la commande</label>
                        <select name="status" required
                                class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            <option value="pending" <?php echo (strtolower($order['status']) === 'pending') ? 'selected' : ''; ?>>En attente</option>
                            <option value="confirmed" <?php echo (strtolower($order['status']) === 'confirmed') ? 'selected' : ''; ?>>Confirmée</option>
                            <option value="shipped" <?php echo (strtolower($order['status']) === 'shipped') ? 'selected' : ''; ?>>Expédiée</option>
                            <option value="delivered" <?php echo (strtolower($order['status']) === 'delivered') ? 'selected' : ''; ?>>Livrée</option>
                            <option value="cancelled" <?php echo (strtolower($order['status']) === 'cancelled') ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Note de statut (Interne / Historique)</label>
                        <textarea name="note" rows="3" placeholder="ex: Colis remis au transporteur Yalidine. N° de suivi: ..."
                                  class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-primary text-on-primary py-3.5 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-colors rounded-lg">
                        Mettre à jour
                    </button>
                </form>
            </div>

            <!-- Status Timeline History -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
                <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Historique Statuts
                </h2>

                <?php if (empty($history)): ?>
                <p class="font-body-md text-on-surface-variant opacity-70">Aucun historique disponible.</p>
                <?php else: ?>
                <div class="relative pl-6 border-l-2 border-outline-variant/30 space-y-6">
                    <?php foreach ($history as $hist): ?>
                    <div class="relative">
                        <!-- Dot -->
                        <span class="absolute -left-[31px] top-1.5 w-3 h-3 rounded-full bg-primary border-4 border-surface-container"></span>
                        
                        <div>
                            <span class="font-label-sm text-label-sm uppercase text-primary font-bold">
                                <?php echo __(strtolower($hist['status'])); ?>
                            </span>
                            <span class="text-xs text-on-surface-variant opacity-70 block">
                                <?php echo date('d/m/Y H:i', strtotime($hist['changed_at'])); ?>
                            </span>
                            <?php if ($hist['note']): ?>
                            <p class="font-body-md text-on-surface-variant text-sm mt-1 bg-surface/50 p-2.5 rounded border border-outline-variant/10">
                                <?php echo sanitize($hist['note']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
