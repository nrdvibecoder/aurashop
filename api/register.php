<?php
require_once 'config.php';

$auth = get_auth();
if ($auth) {
    $redirect = sanitize($_GET['redirect'] ?? 'index.php');
    header("Location: " . $redirect);
    exit;
}

$pageTitle = __('register');
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($pdo && $fullname && $email && $password) {
        if ($password !== $confirm_password) {
            $error = __('password_mismatch');
        } elseif (strlen($password) < 6) {
            $error = __('password_length_error');
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
                        $error = __('reg_error');
                    }
                }
            } catch (PDOException $e) {
                $error = __('login_db_error');
            }
        }
    } else {
        $error = __('reg_fields_error');
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
                <h1 class="font-headline-md text-headline-md text-on-surface font-light uppercase tracking-wider"><?php echo __('register'); ?></h1>
            </div>

            <?php if ($error): ?>
            <div class="bg-error-container/80 backdrop-blur-sm text-on-error-container px-5 py-3 rounded-lg mb-6 font-label-sm text-label-sm flex items-center gap-3 border border-error/20">
                <span class="material-symbols-outlined text-lg">error</span>
                <?php echo sanitize($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant opacity-80"><?php echo __('fullname'); ?></label>
                    <input type="text" name="fullname" required autocomplete="name"
                           value="<?php echo sanitize($_POST['fullname'] ?? ''); ?>"
                           class="bg-surface/30 border border-outline-variant/30 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors placeholder:text-on-surface/30">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant opacity-80"><?php echo __('email'); ?></label>
                    <input type="email" name="email" required autocomplete="email"
                           value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                           class="bg-surface/30 border border-outline-variant/30 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors placeholder:text-on-surface/30">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant opacity-80"><?php echo __('password'); ?></label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="bg-surface/30 border border-outline-variant/30 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant opacity-80"><?php echo $language === 'fr' ? 'Confirmer le mot de passe' : 'Confirm Password'; ?></label>
                    <input type="password" name="confirm_password" required autocomplete="new-password"
                           class="bg-surface/30 border border-outline-variant/30 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
                <button type="submit" class="w-full bg-primary text-on-primary py-4 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed hover:scale-[1.01] active:scale-[0.99] transition-all duration-300 rounded-lg shadow-lg">
                    <?php echo __('register'); ?>
                </button>
            </form>

            <div class="text-center mt-8 font-body-md text-on-surface-variant">
                <?php echo __('already_have_account'); ?>
                <a href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="text-primary hover:text-primary-fixed transition-colors ml-1 underline font-medium">
                    <?php echo __('login'); ?>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
