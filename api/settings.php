<?php
require_once 'config.php';

$auth = requireAuth();
$userId = $auth['id'];

$pageTitle = __('my_account') . ' – AURA';
$error = '';
$success = '';

$user = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement des données.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'update_profile') {
        $fullname = sanitize($_POST['fullname'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if ($fullname && $email) {
            try {
                
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $error = __('email_exists');
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
                    $stmt->execute([$fullname, $email, $userId]);
                    $user['fullname'] = $fullname;
                    $user['email'] = $email;
                    $success = $language === 'fr' ? 'Profil mis à jour avec succès.' : 'Profile updated successfully.';
                }
            } catch (PDOException $e) {
                $error = 'Erreur lors de la mise à jour.';
            }
        } else {
            $error = 'Tous les champs de profil sont requis.';
        }
    } elseif ($action === 'change_password') {
        $current_pw = $_POST['current_password'] ?? '';
        $new_pw = $_POST['new_password'] ?? '';
        $confirm_pw = $_POST['confirm_password'] ?? '';

        if ($current_pw && $new_pw && $confirm_pw) {
            if (password_verify($current_pw, $user['password_hash'])) {
                if ($new_pw !== $confirm_pw) {
                    $error = $language === 'fr' ? 'Les nouveaux mots de passe ne correspondent pas.' : 'New passwords do not match.';
                } elseif (strlen($new_pw) < 6) {
                    $error = $language === 'fr' ? 'Le nouveau mot de passe doit faire au moins 6 caractères.' : 'New password must be at least 6 characters.';
                } else {
                    try {
                        $new_hash = password_hash($new_pw, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$new_hash, $userId]);
                        $success = $language === 'fr' ? 'Mot de passe modifié avec succès.' : 'Password updated successfully.';
                    } catch (PDOException $e) {
                        $error = 'Erreur lors de la modification.';
                    }
                }
            } else {
                $error = $language === 'fr' ? 'Mot de passe actuel incorrect.' : 'Incorrect current password.';
            }
        } else {
            $error = 'Tous les champs de mot de passe sont requis.';
        }
    } elseif ($action === 'delete_account') {
        try {
            
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $pdo->commit();
            clear_auth();
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Impossible de supprimer le compte. Contactez le support.';
        }
    }
}

$orders = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        
    }
}

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <div class="mb-12">
        <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2"><?php echo __('my_account'); ?></h1>
        <p class="font-body-md text-on-surface-variant">Gérez votre profil, vos commandes et vos préférences.</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1 flex flex-col gap-2">
            <button onclick="switchTab('profile-tab')" id="profile-tab-btn" class="tab-btn w-full text-left px-5 py-4 rounded-lg font-label-sm text-label-sm uppercase tracking-wider transition-all duration-200 flex items-center gap-3 bg-surface-container text-primary font-bold">
                <span class="material-symbols-outlined">person</span>
                <?php echo __('profile'); ?>
            </button>
            <button onclick="switchTab('orders-tab')" id="orders-tab-btn" class="tab-btn w-full text-left px-5 py-4 rounded-lg font-label-sm text-label-sm uppercase tracking-wider transition-all duration-200 flex items-center gap-3 text-on-surface-variant hover:bg-surface-container/50">
                <span class="material-symbols-outlined">shopping_bag</span>
                <?php echo __('order_history'); ?>
            </button>
            <button onclick="switchTab('danger-tab')" id="danger-tab-btn" class="tab-btn w-full text-left px-5 py-4 rounded-lg font-label-sm text-label-sm uppercase tracking-wider transition-all duration-200 flex items-center gap-3 text-on-surface-variant hover:bg-surface-container/50">
                <span class="material-symbols-outlined">warning</span>
                <?php echo __('danger_zone'); ?>
            </button>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-3">
            <!-- Profile Tab -->
            <div id="profile-tab" class="tab-content space-y-8">
                <!-- Info Section -->
                <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">badge</span>
                        <?php echo $language === 'fr' ? 'Informations personnelles' : 'Personal Information'; ?>
                    </h2>
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('fullname'); ?></label>
                                <input type="text" name="fullname" required value="<?php echo sanitize($user['fullname'] ?? ''); ?>"
                                       class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('email'); ?></label>
                                <input type="email" name="email" required value="<?php echo sanitize($user['email'] ?? ''); ?>"
                                       class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary text-on-primary px-8 py-3.5 font-label-sm text-label-sm uppercase tracking-wider rounded-lg hover:scale-102 transition-all hover:bg-primary-fixed">
                                <?php echo __('save_changes'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Section -->
                <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">lock</span>
                        <?php echo $language === 'fr' ? 'Modifier le mot de passe' : 'Change Password'; ?>
                    </h2>
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="change_password">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col gap-2">
                                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo $language === 'fr' ? 'Mot de passe actuel' : 'Current Password'; ?></label>
                                <input type="password" name="current_password" required
                                       class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo $language === 'fr' ? 'Nouveau mot de passe' : 'New Password'; ?></label>
                                <input type="password" name="new_password" required
                                       class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo $language === 'fr' ? 'Confirmer' : 'Confirm'; ?></label>
                                <input type="password" name="confirm_password" required
                                       class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-primary text-on-primary px-8 py-3.5 font-label-sm text-label-sm uppercase tracking-wider rounded-lg hover:scale-102 transition-all hover:bg-primary-fixed">
                                <?php echo $language === 'fr' ? 'Modifier' : 'Update Password'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Tab -->
            <div id="orders-tab" class="tab-content hidden space-y-6">
                <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
                    <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">shopping_bag</span>
                        <?php echo __('order_history'); ?>
                    </h2>
                    
                    <?php if (empty($orders)): ?>
                    <p class="font-body-md text-on-surface-variant text-center py-10">
                        <?php echo $language === 'fr' ? 'Vous n\'avez pas encore passé de commande.' : 'You have not placed any orders yet.'; ?>
                    </p>
                    <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left font-body-md">
                            <thead>
                                <tr class="border-b border-outline-variant/20 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                                    <th class="pb-4"><?php echo __('order_number'); ?></th>
                                    <th class="pb-4">Date</th>
                                    <th class="pb-4"><?php echo __('total'); ?></th>
                                    <th class="pb-4"><?php echo __('status'); ?></th>
                                    <th class="pb-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                <?php foreach ($orders as $order): ?>
                                <tr class="text-on-surface">
                                    <td class="py-4 font-mono text-sm"><?php echo sanitize($order['order_number']); ?></td>
                                    <td class="py-4"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
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
                                        <a href="confirmation.php?order_number=<?php echo urlencode($order['order_number']); ?>" class="text-primary hover:underline font-label-sm text-label-sm uppercase tracking-wider">
                                            Détails
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Danger Zone Tab -->
            <div id="danger-tab" class="tab-content hidden">
                <div class="bg-surface-container border border-error/20 rounded-xl p-8 shadow-sm">
                    <h2 class="font-headline-md text-headline-md text-error mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-error">warning</span>
                        <?php echo __('danger_zone'); ?>
                    </h2>
                    <p class="font-body-md text-on-surface-variant mb-8">
                        <?php echo $language === 'fr' ? 'La suppression de votre compte est irréversible. Toutes vos données personnelles et l\'historique de vos commandes seront définitivement effacés.' : 'Account deletion is irreversible. All of your personal data and order history will be permanently deleted.'; ?>
                    </p>
                    <form method="POST" id="delete-form" class="flex items-center gap-4">
                        <input type="hidden" name="action" value="delete_account">
                        <button type="button" onclick="confirmDelete()" class="bg-error text-on-error px-8 py-3.5 font-label-sm text-label-sm uppercase tracking-wider rounded-lg hover:bg-red-600 transition-colors">
                            <?php echo $language === 'fr' ? 'Supprimer définitivement mon compte' : 'Delete my account permanently'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    // Remove active class from buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('bg-surface-container', 'text-primary', 'font-bold');
        btn.classList.add('text-on-surface-variant');
    });

    // Show active tab
    document.getElementById(tabId).classList.remove('hidden');
    // Set button active
    const btn = document.getElementById(tabId + '-btn');
    btn.classList.add('bg-surface-container', 'text-primary', 'font-bold');
    btn.classList.remove('text-on-surface-variant');
}

function confirmDelete() {
    const msg = <?php echo json_encode($language === 'fr' ? 'Êtes-vous absolument sûr de vouloir supprimer définitivement votre compte ? Cette action est irréversible.' : 'Are you absolutely sure you want to permanently delete your account? This action cannot be undone.'); ?>;
    if (confirm(msg)) {
        document.getElementById('delete-form').submit();
    }
}
</script>

<?php require_once 'footer.php'; ?>
