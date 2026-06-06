<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Paramètres de Livraison – AURA';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_update'])) {
    if ($pdo && isset($_POST['fees'])) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                UPDATE delivery_zones 
                SET home_fee = ?, relay_fee = ?, estimated_days = ? 
                WHERE wilaya_code = ?
            ");

            foreach ($_POST['fees'] as $code => $data) {
                $home = (int)($data['home_fee'] ?? 0);
                $relay = (int)($data['relay_fee'] ?? 0);
                $days = (int)($data['estimated_days'] ?? 3);
                $stmt->execute([$home, $relay, $days, (int)$code]);
            }

            $pdo->commit();
            $success = 'Frais de livraison mis à jour avec succès.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}

$zones = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM delivery_zones ORDER BY wilaya_code ASC");
        $zones = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement des wilayas : ' . $e->getMessage();
    }
}

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Frais de Livraison par Wilaya</h1>
            <p class="font-body-md text-on-surface-variant">Modifiez les tarifs de livraison à domicile et en point relais pour les 58 wilayas.</p>
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
        <a href="admin_promo.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Codes Promo</a>
        <a href="admin_settings.php" class="px-4 py-2.5 rounded-lg bg-surface-container text-primary font-bold font-label-sm text-label-sm uppercase tracking-wider">Paramètres</a>
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

    <!-- Form -->
    <form method="POST" class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
        <input type="hidden" name="bulk_update" value="1">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">local_shipping</span>
                Zones de Livraison (58 Wilayas)
            </h2>
            <button type="submit" class="bg-primary text-on-primary px-8 py-3.5 font-label-sm text-label-sm uppercase tracking-wider hover:bg-primary-fixed transition-transform hover:scale-102 rounded-lg">
                Enregistrer tous les Tarifs
            </button>
        </div>

        <div class="overflow-x-auto max-h-[600px] border border-outline-variant/10 rounded-lg">
            <table class="w-full text-left font-body-md relative">
                <thead class="sticky top-0 bg-surface-container border-b border-outline-variant/20 z-10">
                    <tr class="text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                        <th class="p-4 w-24">Code</th>
                        <th class="p-4">Wilaya</th>
                        <th class="p-4 w-48">Livraison Domicile (DA)</th>
                        <th class="p-4 w-48">Livraison Relais (DA)</th>
                        <th class="p-4 w-48">Délai Estimé (Jours)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    <?php foreach ($zones as $zone): 
                        $code = $zone['wilaya_code'];
                    ?>
                    <tr class="text-on-surface hover:bg-surface-container-high/40 transition-colors">
                        <td class="p-4 font-mono font-bold"><?php echo sprintf('%02d', $code); ?></td>
                        <td class="p-4 font-semibold"><?php echo sanitize($zone['wilaya_name']); ?></td>
                        <td class="p-2">
                            <input type="number" name="fees[<?php echo $code; ?>][home_fee]" 
                                   value="<?php echo (int)$zone['home_fee']; ?>" required min="0" step="50"
                                   class="w-full bg-surface border border-outline-variant/20 rounded-lg px-3 py-2 text-on-surface font-body-md focus:border-primary focus:ring-0 focus:outline-none">
                        </td>
                        <td class="p-2">
                            <input type="number" name="fees[<?php echo $code; ?>][relay_fee]" 
                                   value="<?php echo (int)$zone['relay_fee']; ?>" required min="0" step="50"
                                   class="w-full bg-surface border border-outline-variant/20 rounded-lg px-3 py-2 text-on-surface font-body-md focus:border-primary focus:ring-0 focus:outline-none">
                        </td>
                        <td class="p-2">
                            <input type="number" name="fees[<?php echo $code; ?>][estimated_days]" 
                                   value="<?php echo (int)$zone['estimated_days']; ?>" required min="1" max="15"
                                   class="w-full bg-surface border border-outline-variant/20 rounded-lg px-3 py-2 text-on-surface font-body-md focus:border-primary focus:ring-0 focus:outline-none">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="flex justify-end mt-8">
            <button type="submit" class="bg-primary text-on-primary px-8 py-3.5 font-label-sm text-label-sm uppercase tracking-wider hover:bg-primary-fixed transition-transform hover:scale-102 rounded-lg">
                Enregistrer tous les Tarifs
            </button>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>
