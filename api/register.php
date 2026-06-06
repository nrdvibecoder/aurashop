<?php
require_once 'config.php';

$auth = get_auth();
if ($auth) {
    $redirect = sanitize($_GET['redirect'] ?? 'index.php');
    header("Location: " . $redirect);
    exit;
}

$pageTitle = __('register') . ' – AURA';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($pdo && $fullname && $email && $password) {
        if ($password !== $confirm_password) {
            $error = $language === 'fr' ? 'Les mots de passe ne correspondent pas.' : 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = $language === 'fr' ? 'Le mot de passe doit faire au moins 6 caractères.' : 'Password must be at least 6 characters.';
        } else {
            try {
                
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = __('email_exists');
                } else {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password_hash, role) VALUES (?, ?, ?, 'customer') RETURNING id, role");
                    $stmt->execute([$fullname, $email, $password_hash]);
                    $user = $stmt->fetch();

                    if ($user) {
                        set_auth($user['id'], $user['role']);
                        $redirect = sanitize($_GET['redirect'] ?? 'index.php');
                        header("Location: " . $redirect);
                        exit;
                    } else {
                        $error = 'Une erreur est survenue lors de la création du compte.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Erreur de connexion. Veuillez réessayer.';
            }
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}

require_once 'header.php';
?>

<div class="min-h-screen flex items-center justify-center py-16 px-margin-mobile">
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-surface-container border border-outline-variant/20 rounded-xl p-10 shadow-2xl">
            <div class="text-center mb-10">
                <a href="index.php" class="font-headline-lg text-headline-lg text-primary tracking-widest block mb-6">AURA</a>
                <h1 class="font-headline-md text-headline-md text-on-surface"><?php echo __('register'); ?></h1>
                <p class="font-body-md text-on-surface-variant mt-2">Rejoignez-nous pour une expérience personnalisée.</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-error-container text-on-error-container px-5 py-3 rounded-lg mb-6 font-label-sm text-label-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-lg">error</span>
                <?php echo sanitize($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('fullname'); ?></label>
                    <input type="text" name="fullname" required autocomplete="name"
                           value="<?php echo sanitize($_POST['fullname'] ?? ''); ?>"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('email'); ?></label>
                    <input type="email" name="email" required autocomplete="email"
                           value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('password'); ?></label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70">
                        <?php echo $language === 'fr' ? 'Confirmer le mot de passe' : 'Confirm Password'; ?>
                    </label>
                    <input type="password" name="confirm_password" required autocomplete="new-password"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <button type="submit" class="w-full bg-primary text-on-primary py-4 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-all duration-300 rounded-lg hover:scale-[1.02] active:scale-95">
                    <?php echo __('register'); ?>
                </button>
            </form>

            <div class="text-center mt-8 font-body-md text-on-surface-variant">
                <?php echo __('already_have_account'); ?>
                <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="text-primary hover:text-primary-fixed transition-colors ml-1 underline">
                    <?php echo __('login'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
