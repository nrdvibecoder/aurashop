<?php
require_once 'config.php';

$auth = get_auth();
if ($auth) {
    $redirect = sanitize($_GET['redirect'] ?? 'index.php');
    header("Location: " . $redirect);
    exit;
}

$pageTitle = __('login');
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
                    $error = __('ban_error');
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
            $error = __('login_db_error');
        }
    } else {
        $error = __('login_fields_error');
    }
}

require_once 'header.php';
?>

<div class="h-screen flex items-center justify-center px-margin-mobile relative overflow-hidden bg-black -mt-24">
    
    <div class="absolute inset-0 bg-cover bg-center scale-105 filter blur-2xl opacity-40 brightness-50" style="background-image: url('assets/hero3.png');"></div>

    <div class="w-full max-w-md relative z-10">
        
        <div class="backdrop-blur-xl bg-surface/50 border border-outline-variant/20 rounded-2xl p-8 md:p-10 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)]">
            <div class="text-center mb-8">
                <a href="index.php" class="font-headline-lg text-headline-lg text-primary tracking-widest block mb-4">AURA</a>
                <h1 class="font-headline-md text-headline-md text-on-surface font-light uppercase tracking-wider"><?php echo __('login'); ?></h1>
            </div>

            <?php if ($error): ?>
            <div class="bg-error-container/80 backdrop-blur-sm text-on-error-container px-5 py-3 rounded-lg mb-6 font-label-sm text-label-sm flex items-center gap-3 border border-error/20">
                <span class="material-symbols-outlined text-lg">error</span>
                <?php echo sanitize($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant opacity-80"><?php echo __('email'); ?></label>
                    <input type="email" name="email" required autocomplete="email"
                           value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                           class="bg-surface/30 border border-outline-variant/30 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors placeholder:text-on-surface/30">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant opacity-80"><?php echo __('password'); ?></label>
                    <input type="password" name="password" required autocomplete="current-password"
                           class="bg-surface/30 border border-outline-variant/30 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <button type="submit" class="w-full bg-primary text-on-primary py-4 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 rounded-lg shadow-lg">
                    <?php echo __('login'); ?>
                </button>
            </form>

            <div class="text-center mt-8 font-body-md text-on-surface-variant">
                <?php echo __('dont_have_account'); ?>
                <a href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="text-primary hover:text-primary-fixed transition-colors ml-1 underline font-medium">
                    <?php echo __('register'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
