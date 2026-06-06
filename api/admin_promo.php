<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Gestion des Codes Promo – AURA';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create') {
        $code = strtoupper(sanitize($_POST['code'] ?? ''));
        $type = sanitize($_POST['type'] ?? '');
        $value = (int)($_POST['value'] ?? 0);
        $min_order = (int)($_POST['min_order'] ?? 0);
        $max_uses = (int)($_POST['max_uses'] ?? 0);
        $expires_at = sanitize($_POST['expires_at'] ?? '');

        if ($code && $type && $value > 0) {
            try {
                $expiryVal = !empty($expires_at) ? $expires_at : null;
                $stmt = $pdo->prepare("
                    INSERT INTO promo_codes 
                    (code, type, value, min_order, max_uses, used_count, expires_at, is_active)
                    VALUES (?, ?, ?, ?, ?, 0, ?, TRUE)
                ");
                $stmt->execute([$code, $type, $value, $min_order, $max_uses ?: null, $expiryVal]);
                $success = 'Code promo créé avec succès.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de la création : ' . $e->getMessage();
            }
        } else {
            $error = 'Le code, le type et la valeur sont requis.';
        }
    } elseif ($action === 'toggle') {
        $promoId = (int)$_POST['promo_id'];
        $newStatus = isset($_POST['active']) ? 1 : 0;

        if ($promoId && $pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE promo_codes SET is_active = ? WHERE id = ?");
                $stmt->execute([$newStatus ? 'true' : 'false', $promoId]);
                $success = 'Statut mis à jour avec succès.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $promoId = (int)$_POST['promo_id'];
        if ($promoId && $pdo) {
            try {
                $stmt = $pdo->prepare("DELETE FROM promo_codes WHERE id = ?");
                $stmt->execute([$promoId]);
                $success = 'Code promo supprimé avec succès.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de la suppression : ' . $e->getMessage();
            }
        }
    }
}

$promos = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM promo_codes ORDER BY created_at DESC");
        $promos = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement des codes promo : ' . $e->getMessage();
    }
}

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Gestion des Codes Promo</h1>
            <p class="font-body-md text-on-surface-variant">Créez et gérez des promotions et remises.</p>
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
        <a href="admin_customers.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Clients</a>
        <a href="admin_categories.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Catégories</a>
        <a href="admin_promo.php" class="px-4 py-2.5 rounded-lg bg-surface-container text-primary font-bold font-label-sm text-label-sm uppercase tracking-wider">Codes Promo</a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Promo Code Form -->
        <div class="lg:col-span-1 bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm h-fit">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_circle</span>
                Ajouter un Code Promo
            </h2>
            
            <form method="POST" class="space-y-5">
                <input type="hidden" name="action" value="create">
                
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Code</label>
                    <input type="text" name="code" required placeholder="ex: AURA10, SUMMER26"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Type</label>
                        <select name="type" required
                                class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            <option value="percentage">Pourcentage</option>
                            <option value="fixed">Montant fixe (DA)</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Valeur</label>
                        <input type="number" name="value" required min="1" placeholder="10 ou 1000"
                               class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Panier Minimum Requis (DA)</label>
                    <input type="number" name="min_order" min="0" placeholder="0"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Nombre Max d'utilisations (Optionnel)</label>
                    <input type="number" name="max_uses" min="0" placeholder="ex: 100"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Date d'Expiration (Optionnel)</label>
                    <input type="datetime-local" name="expires_at"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <button type="submit" class="w-full bg-primary text-on-primary py-3 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-all rounded-lg hover:scale-102">
                    Créer le Code Promo
                </button>
            </form>
        </div>

        <!-- Promo Codes List (2 cols) -->
        <div class="lg:col-span-2 bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">sell</span>
                Codes Promo Existants
            </h2>

            <?php if (empty($promos)): ?>
            <p class="font-body-md text-on-surface-variant text-center py-10">Aucun code promo créé.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left font-body-md">
                    <thead>
                        <tr class="border-b border-outline-variant/20 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                            <th class="pb-4">Code</th>
                            <th class="pb-4">Remise</th>
                            <th class="pb-4">Min Panier</th>
                            <th class="pb-4">Utilisé</th>
                            <th class="pb-4">Expiration</th>
                            <th class="pb-4">Statut</th>
                            <th class="pb-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        <?php foreach ($promos as $promo): ?>
                        <tr class="text-on-surface">
                            <td class="py-4 font-mono font-bold"><?php echo sanitize($promo['code']); ?></td>
                            <td class="py-4">
                                <?php echo $promo['value']; ?><?php echo ($promo['type'] === 'percentage') ? '%' : ' DA'; ?>
                            </td>
                            <td class="py-4">
                                <?php echo $promo['min_order'] > 0 ? format_price($promo['min_order']) : 'Aucun'; ?>
                            </td>
                            <td class="py-4">
                                <?php echo $promo['used_count']; ?><?php echo $promo['max_uses'] ? ' / ' . $promo['max_uses'] : ''; ?>
                            </td>
                            <td class="py-4 text-sm">
                                <?php echo $promo['expires_at'] ? date('d/m/Y H:i', strtotime($promo['expires_at'])) : 'Jamais'; ?>
                            </td>
                            <td class="py-4">
                                <?php if ($promo['is_active']): ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500/10 text-green-500">Actif</span>
                                <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-surface-container text-on-surface-variant">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 text-right flex justify-end gap-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="promo_id" value="<?php echo $promo['id']; ?>">
                                    <?php if ($promo['is_active']): ?>
                                    <button type="submit" class="text-on-surface-variant hover:text-on-surface font-label-sm text-label-sm uppercase tracking-wider">Désactiver</button>
                                    <?php else: ?>
                                    <input type="hidden" name="active" value="1">
                                    <button type="submit" class="text-primary hover:text-primary-fixed font-label-sm text-label-sm uppercase tracking-wider">Activer</button>
                                    <?php endif; ?>
                                </form>
                                <form method="POST" class="inline" onsubmit="return confirm('Supprimer ce code promo définitivement ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="promo_id" value="<?php echo $promo['id']; ?>">
                                    <button type="submit" class="text-error hover:underline font-label-sm text-label-sm uppercase tracking-wider ml-2">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
