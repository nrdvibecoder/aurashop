<?php
require_once 'config.php';

$auth = get_auth();
if ($auth) {
    $redirect = sanitize($_GET['redirect'] ?? 'index.php');
    header("Location: " . $redirect);
    exit;
}

$pageTitle = __('login') . ' – AURA';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($pdo && $email && $password) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['is_banned']) {
                    $error = 'Votre compte est suspendu. Contactez le support.';
                } else {
                    set_auth($user['id'], $user['role']);
                    $redirect = sanitize($_GET['redirect'] ?? ($user['role'] === 'admin' ? 'admin.php' : 'index.php'));
                    header("Location: " . $redirect);
                    exit;
                }
            } else {
                $error = __('invalid_credentials');
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion. Veuillez réessayer.';
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
                <h1 class="font-headline-md text-headline-md text-on-surface"><?php echo __('login'); ?></h1>
                <p class="font-body-md text-on-surface-variant mt-2">Accédez à votre espace personnel.</p>
            </div>

            <?php if ($error): ?>
            <div class="bg-error-container text-on-error-container px-5 py-3 rounded-lg mb-6 font-label-sm text-label-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-lg">error</span>
                <?php echo sanitize($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('email'); ?></label>
                    <input type="email" name="email" required autocomplete="email"
                           value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70"><?php echo __('password'); ?></label>
                    <input type="password" name="password" required autocomplete="current-password"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <button type="submit" class="w-full bg-primary text-on-primary py-4 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-all duration-300 rounded-lg hover:scale-[1.02] active:scale-95">
                    <?php echo __('login'); ?>
                </button>
            </form>

            <div class="text-center mt-8 font-body-md text-on-surface-variant">
                <?php echo __('dont_have_account'); ?>
                <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="text-primary hover:text-primary-fixed transition-colors ml-1 underline">
                    <?php echo __('register'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
