<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Gestion des Clients – AURA';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_ban'])) {
    $customerId = (int)$_POST['customer_id'];
    $banStatus = isset($_POST['ban']) ? 1 : 0;

    if ($pdo && $customerId) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ? AND role = 'customer'");
            $stmt->execute([$banStatus ? 'true' : 'false', $customerId]);
            $success = $banStatus ? 'Client banni avec succès.' : 'Client débanni avec succès.';
        } catch (PDOException $e) {
            $error = 'Erreur lors du changement de statut : ' . $e->getMessage();
        }
    }
}

$searchQuery = sanitize($_GET['search'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$customers = [];
$totalCustomers = 0;

if ($pdo) {
    try {
        $params = [];
        $where = ["role = 'customer'"];

        if ($searchQuery) {
            $where[] = "(fullname ILIKE ? OR email ILIKE ?)";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users $whereClause");
        $stmt->execute($params);
        $totalCustomers = (int)$stmt->fetchColumn();

        
        $stmt = $pdo->prepare("
            SELECT id, fullname, email, is_banned, created_at,
                   (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as orders_count,
                   (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = users.id AND status != 'cancelled') as total_spent
            FROM users 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement des clients : ' . $e->getMessage();
    }
}

$totalPages = ceil($totalCustomers / $limit);

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Gestion des Clients</h1>
            <p class="font-body-md text-on-surface-variant">Consultez la liste des clients et gérez leur accès.</p>
        </div>
        <a href="admin.php" class="bg-surface-container border border-outline-variant/20 text-on-surface px-5 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-2 hover:bg-surface-container/80 transition-colors">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Retour Dashboard
        </a>
    </div>

    <!-- Admin Sub-Navigation tabs -->
    <div class="flex flex-wrap gap-2 mb-10 border-b border-outline-variant/10 pb-4">
        <a href="admin.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Tableau de bord</a>
        <a href="admin_orders.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Commandes</a>
        <a href="admin_customers.php" class="px-4 py-2.5 rounded-lg bg-surface-container text-primary font-bold font-label-sm text-label-sm uppercase tracking-wider">Clients</a>
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

    <?php if ($success): ?>
    <div class="bg-primary-container text-on-primary-container px-6 py-4 rounded-lg mb-8 font-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-xl">check_circle</span>
        <?php echo sanitize($success); ?>
    </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-6 mb-8 shadow-sm">
        <form method="GET" class="flex gap-4">
            <div class="flex-1 flex items-center bg-surface border border-outline-variant/20 rounded-lg px-4 py-2.5">
                <span class="material-symbols-outlined text-on-surface-variant mr-3">search</span>
                <input type="text" name="search" placeholder="Rechercher par nom ou adresse email..." 
                       value="<?php echo sanitize($searchQuery); ?>"
                       class="w-full bg-transparent border-none p-0 focus:ring-0 focus:outline-none text-on-surface font-body-md">
            </div>
            
            <button type="submit" class="bg-primary text-on-primary px-8 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider hover:bg-primary-fixed transition-colors">
                Rechercher
            </button>
            
            <?php if ($searchQuery): ?>
            <a href="admin_customers.php" class="bg-surface-container border border-outline-variant/20 text-on-surface px-6 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center justify-center gap-2 hover:bg-surface-container/80 transition-colors">
                Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Customers Table -->
    <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
        <?php if (empty($customers)): ?>
        <p class="font-body-md text-on-surface-variant text-center py-10">Aucun client trouvé.</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left font-body-md">
                <thead>
                    <tr class="border-b border-outline-variant/20 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                        <th class="pb-4">Nom</th>
                        <th class="pb-4">Email</th>
                        <th class="pb-4">Inscrit le</th>
                        <th class="pb-4">Commandes</th>
                        <th class="pb-4">Total Dépensé</th>
                        <th class="pb-4">Statut</th>
                        <th class="pb-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    <?php foreach ($customers as $cust): ?>
                    <tr class="text-on-surface">
                        <td class="py-4 font-semibold"><?php echo sanitize($cust['fullname']); ?></td>
                        <td class="py-4"><?php echo sanitize($cust['email']); ?></td>
                        <td class="py-4"><?php echo date('d/m/Y', strtotime($cust['created_at'])); ?></td>
                        <td class="py-4"><?php echo $cust['orders_count']; ?></td>
                        <td class="py-4 font-semibold"><?php echo format_price($cust['total_spent']); ?></td>
                        <td class="py-4">
                            <?php if ($cust['is_banned']): ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-error-container text-on-error-container">Banni</span>
                            <?php else: ?>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-500">Actif</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 text-right">
                            <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir modifier le statut d\'accès de ce client ?');">
                                <input type="hidden" name="toggle_ban" value="1">
                                <input type="hidden" name="customer_id" value="<?php echo $cust['id']; ?>">
                                <?php if ($cust['is_banned']): ?>
                                <button type="submit" name="unban" class="text-primary hover:underline font-label-sm text-label-sm uppercase tracking-wider">Débannir</button>
                                <?php else: ?>
                                <button type="submit" name="ban" class="text-error hover:underline font-label-sm text-label-sm uppercase tracking-wider">Bannir</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 mt-8">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="admin_customers.php?page=<?php echo $i; ?>&search=<?php echo urlencode($searchQuery); ?>"
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
