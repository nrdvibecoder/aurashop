<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Dashboard Admin – AURA';
$error = '';

$kpi = [
    'revenue' => 0,
    'orders' => 0,
    'products' => 0,
    'customers' => 0
];
$recentOrders = [];
$lowStockProducts = [];

if ($pdo) {
    try {
        
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'");
        $kpi['revenue'] = (int)($stmt->fetch()['total'] ?? 0);

        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
        $kpi['orders'] = (int)($stmt->fetch()['total'] ?? 0);

        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $kpi['products'] = (int)($stmt->fetch()['total'] ?? 0);

        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
        $kpi['customers'] = (int)($stmt->fetch()['total'] ?? 0);

        
        $stmt = $pdo->query("SELECT o.*, u.fullname as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 10");
        $recentOrders = $stmt->fetchAll();

        
        $stmt = $pdo->query("SELECT * FROM products WHERE stock < 5 ORDER BY stock ASC LIMIT 10");
        $lowStockProducts = $stmt->fetchAll();

    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération des données : ' . $e->getMessage();
    }
}

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-12">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Administration</h1>
            <p class="font-body-md text-on-surface-variant">Vue d'ensemble de votre boutique AURA.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="admin_add_product.php" class="bg-primary text-on-primary px-5 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-2 hover:scale-102 transition-transform">
                <span class="material-symbols-outlined text-lg">add</span>
                Nouveau Produit
            </a>
            <a href="admin_orders.php" class="bg-surface-container border border-outline-variant/20 text-on-surface px-5 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-2 hover:bg-surface-container/80 transition-colors">
                <span class="material-symbols-outlined text-lg">shopping_cart</span>
                Commandes
            </a>
        </div>
    </div>

    <!-- Admin Sub-Navigation tabs -->
    <div class="flex flex-wrap gap-2 mb-10 border-b border-outline-variant/10 pb-4">
        <a href="admin.php" class="px-4 py-2.5 rounded-lg bg-surface-container text-primary font-bold font-label-sm text-label-sm uppercase tracking-wider">Tableau de bord</a>
        <a href="admin_orders.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Commandes</a>
        <a href="admin_customers.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Clients</a>
        <a href="admin_categories.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Catégories</a>
        <a href="admin_promo.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Codes Promo</a>
        <a href="admin_settings.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Paramètres</a>
    </div>

    <?php if ($error): ?>
    <div class="bg-error-container text-on-error-container px-6 py-4 rounded-lg mb-8 font-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-xl">error</span>
        <?php echo sanitize($error); ?>
    </div>
    <?php endif; ?>

    <!-- KPI Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Revenue KPI -->
        <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-6 flex items-center justify-between shadow-sm hover:border-primary/25 transition-colors">
            <div>
                <p class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75 mb-1">Revenu Total</p>
                <h3 class="font-headline-md text-headline-md text-on-surface font-bold"><?php echo format_price($kpi['revenue']); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
        </div>

        <!-- Orders KPI -->
        <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-6 flex items-center justify-between shadow-sm hover:border-primary/25 transition-colors">
            <div>
                <p class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75 mb-1">Commandes</p>
                <h3 class="font-headline-md text-headline-md text-on-surface font-bold"><?php echo number_format($kpi['orders']); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-lg bg-secondary/10 flex items-center justify-center text-secondary">
                <span class="material-symbols-outlined text-2xl">shopping_bag</span>
            </div>
        </div>

        <!-- Products KPI -->
        <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-6 flex items-center justify-between shadow-sm hover:border-primary/25 transition-colors">
            <div>
                <p class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75 mb-1">Produits</p>
                <h3 class="font-headline-md text-headline-md text-on-surface font-bold"><?php echo number_format($kpi['products']); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-lg bg-tertiary/10 flex items-center justify-center text-tertiary">
                <span class="material-symbols-outlined text-2xl">inventory_2</span>
            </div>
        </div>

        <!-- Customers KPI -->
        <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-6 flex items-center justify-between shadow-sm hover:border-primary/25 transition-colors">
            <div>
                <p class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75 mb-1">Clients</p>
                <h3 class="font-headline-md text-headline-md text-on-surface font-bold"><?php echo number_format($kpi['customers']); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined text-2xl">group</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders (2 cols) -->
        <div class="lg:col-span-2 bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">history</span>
                    Commandes Récentes
                </h2>
                <a href="admin_orders.php" class="text-primary hover:underline font-label-sm text-label-sm uppercase tracking-wider">Voir tout</a>
            </div>

            <?php if (empty($recentOrders)): ?>
            <p class="font-body-md text-on-surface-variant text-center py-10">Aucune commande enregistrée.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left font-body-md">
                    <thead>
                        <tr class="border-b border-outline-variant/20 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                            <th class="pb-4">N° Commande</th>
                            <th class="pb-4">Client</th>
                            <th class="pb-4">Total</th>
                            <th class="pb-4">Statut</th>
                            <th class="pb-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        <?php foreach ($recentOrders as $order): ?>
                        <tr class="text-on-surface">
                            <td class="py-4 font-mono text-sm"><?php echo sanitize($order['order_number']); ?></td>
                            <td class="py-4"><?php echo sanitize($order['customer_name'] ?? 'Client Invité / Supprimé'); ?></td>
                            <td class="py-4 font-semibold"><?php echo format_price($order['total_amount']); ?></td>
                            <td class="py-4">
                                <?php
                                $statusClass = 'bg-surface-container text-on-surface';
                                if ($order['status'] === 'pending') $statusClass = 'bg-yellow-500/10 text-yellow-500';
                                elseif ($order['status'] === 'confirmed') $statusClass = 'bg-blue-500/10 text-blue-500';
                                elseif ($order['status'] === 'shipped') $statusClass = 'bg-indigo-500/10 text-indigo-500';
                                elseif ($order['status'] === 'delivered') $statusClass = 'bg-green-500/10 text-green-500';
                                elseif ($order['status'] === 'cancelled') $statusClass = 'bg-red-500/10 text-red-500';
                                ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $statusClass; ?>">
                                    <?php echo __($order['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                <a href="admin_order_detail.php?id=<?php echo $order['id']; ?>" class="text-primary hover:underline font-label-sm text-label-sm uppercase tracking-wider">Gérer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Low Stock Alerts (1 col) -->
        <div class="lg:col-span-1 bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-error">warning</span>
                Stock Faible
            </h2>

            <?php if (empty($lowStockProducts)): ?>
            <p class="font-body-md text-on-surface-variant text-center py-10">Aucun produit en rupture ou stock faible.</p>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($lowStockProducts as $prod): ?>
                <div class="flex items-center justify-between p-3.5 bg-surface border border-outline-variant/10 rounded-lg">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <?php if ($prod['image_url']): ?>
                        <img src="<?php echo $prod['image_url']; ?>" class="w-10 h-10 object-cover rounded bg-surface-container-high flex-shrink-0" alt="">
                        <?php else: ?>
                        <div class="w-10 h-10 bg-surface-container-high rounded flex items-center justify-center flex-shrink-0 text-on-surface-variant">
                            <span class="material-symbols-outlined">image</span>
                        </div>
                        <?php endif; ?>
                        <div class="truncate">
                            <h4 class="font-body-md text-on-surface font-semibold truncate leading-snug"><?php echo sanitize($prod['name']); ?></h4>
                            <p class="font-label-sm text-label-sm text-on-surface-variant opacity-75"><?php echo sanitize($prod['category']); ?></p>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="px-2 py-1 rounded text-xs font-bold bg-error/10 text-error">
                            <?php echo $prod['stock']; ?> restants
                        </span>
                        <a href="admin_edit_product.php?id=<?php echo $prod['id']; ?>" class="block text-primary hover:underline text-xs mt-1">Éditer</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
