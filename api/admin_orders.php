<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Gestion des Commandes';
$error = '';

$statusFilter = db_clean($_GET['status'] ?? '');
$searchQuery = db_clean($_GET['search'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$orders = [];
$totalOrders = 0;

if ($pdo) {
    try {
        
        $params = [];
        $where = [];

        if ($statusFilter) {
            $where[] = "o.status = ?";
            $params[] = $statusFilter;
        }

        if ($searchQuery) {
            $where[] = "(o.order_number ILIKE ? OR u.fullname ILIKE ? OR o.shipping_address ILIKE ?)";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        
        $countQuery = "
            SELECT COUNT(*) 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id
            $whereClause
        ";
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute($params);
        $totalOrders = (int)$stmt->fetchColumn();

        
        $orderQuery = "
            SELECT o.*, u.fullname as customer_name, u.email as customer_email 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id
            $whereClause 
            ORDER BY o.order_date DESC 
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $pdo->prepare($orderQuery);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération des commandes : ' . $e->getMessage();
    }
}

$totalPages = ceil($totalOrders / $limit);

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Gestion des Commandes</h1>
            <p class="font-body-md text-on-surface-variant">Consultez et gérez les commandes des clients.</p>
        </div>
        <a href="admin.php" class="group flex items-center gap-2.5 px-6 py-3 bg-surface-container-high/50 hover:bg-primary/10 border border-outline-variant/20 hover:border-primary/40 text-on-surface hover:text-primary rounded-xl font-label-sm text-label-sm uppercase tracking-wider transition-all duration-300 active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform duration-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
            <span>Retour Dashboard</span>
        </a>
    </div>

    
    <div class="flex flex-wrap gap-2 mb-10 border-b border-outline-variant/10 pb-4">
        <a href="admin.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Tableau de bord</a>
        <a href="admin_orders.php" class="px-4 py-2.5 rounded-lg bg-surface-container text-primary font-bold font-label-sm text-label-sm uppercase tracking-wider">Commandes</a>
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

    
    <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-6 mb-8 shadow-sm">
        <form method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 flex items-center bg-surface border border-outline-variant/20 rounded-lg px-4 py-2.5">
                <span class="material-symbols-outlined text-on-surface-variant mr-3">search</span>
                <input type="text" name="search" placeholder="Rechercher par n° de commande, client, adresse..." 
                       value="<?php echo sanitize($searchQuery); ?>"
                       class="w-full bg-transparent border-none p-0 focus:ring-0 focus:outline-none text-on-surface font-body-md">
            </div>
            
            <div class="w-full md:w-64">
                <select name="status" onchange="this.form.submit()"
                        class="w-full bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                    <option value="">Tous les statuts</option>
                    <option value="pending" <?php echo ($statusFilter === 'pending') ? 'selected' : ''; ?>>En attente</option>
                    <option value="confirmed" <?php echo ($statusFilter === 'confirmed') ? 'selected' : ''; ?>>Confirmée</option>
                    <option value="shipped" <?php echo ($statusFilter === 'shipped') ? 'selected' : ''; ?>>Expédiée</option>
                    <option value="delivered" <?php echo ($statusFilter === 'delivered') ? 'selected' : ''; ?>>Livrée</option>
                    <option value="cancelled" <?php echo ($statusFilter === 'cancelled') ? 'selected' : ''; ?>>Annulée</option>
                </select>
            </div>
            
            <button type="submit" class="bg-primary text-on-primary px-8 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider hover:bg-primary-fixed transition-colors">
                Filtrer
            </button>
            
            <?php if ($statusFilter || $searchQuery): ?>
            <a href="admin_orders.php" class="bg-surface-container border border-outline-variant/20 text-on-surface px-6 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center justify-center gap-2 hover:bg-surface-container/80 transition-colors">
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
        <?php if (empty($orders)): ?>
        <p class="font-body-md text-on-surface-variant text-center py-10">Aucune commande ne correspond aux critères.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left font-body-md">
                <thead>
                    <tr class="border-b border-outline-variant/20 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                        <th class="pb-4">N° Commande</th>
                        <th class="pb-4">Date</th>
                        <th class="pb-4">Client</th>
                        <th class="pb-4">Total</th>
                        <th class="pb-4">Statut</th>
                        <th class="pb-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    <?php foreach ($orders as $order): ?>
                    <tr class="text-on-surface">
                        <td class="py-4 font-mono text-sm"><?php echo sanitize($order['order_number']); ?></td>
                        <td class="py-4"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                        <td class="py-4">
                            <div class="font-semibold"><?php echo sanitize($order['customer_name'] ?? 'Client Invité'); ?></div>
                            <div class="text-xs text-on-surface-variant opacity-75"><?php echo sanitize($order['customer_email'] ?? $order['phone']); ?></div>
                        </td>
                        <td class="py-4 font-semibold"><?php echo format_price($order['total_amount']); ?></td>
                        <td class="py-4">
                            <?php
                            $statusKey = strtolower($order['status']);
                            $statusClass = 'bg-surface-container text-on-surface';
                            if ($statusKey === 'pending') $statusClass = 'bg-yellow-500/10 text-yellow-500';
                            elseif ($statusKey === 'confirmed') $statusClass = 'bg-blue-500/10 text-blue-500';
                            elseif ($statusKey === 'shipped') $statusClass = 'bg-indigo-500/10 text-indigo-500';
                            elseif ($statusKey === 'delivered') $statusClass = 'bg-green-500/10 text-green-500';
                            elseif ($statusKey === 'cancelled') $statusClass = 'bg-red-500/10 text-red-500';
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $statusClass; ?>">
                                <?php echo __($statusKey); ?>
                            </span>
                        </td>
                        <td class="py-4 text-right">
                            <a href="admin_order_detail.php?id=<?php echo $order['id']; ?>" class="bg-primary/10 text-primary px-4 py-2 rounded-lg font-label-sm text-label-sm uppercase tracking-wider hover:bg-primary/20 transition-colors">
                                Gérer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-8">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="admin_orders.php?page=<?php echo $i; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($searchQuery); ?>"
               class="w-10 h-10 flex items-center justify-center rounded-lg border <?php echo ($i === $page) ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant/20 text-on-surface hover:bg-surface-container-high'; ?> font-label-sm text-label-sm">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>
