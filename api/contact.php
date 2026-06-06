<?php
$pageTitle = 'Contact – AURA';
require_once 'config.php';

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $message = sanitize($_POST['message'] ?? '');

    if ($name && $email && $message) {
        // Since we are serverless, we mock email submission or insert in contact log table if desired.
        // For universities/demos, storing in database or sending success message is ideal.
        $successMsg = ($language === 'en') 
            ? "Your message has been received! We will get back to you shortly." 
            : "Votre message a été bien transmis ! Nous reviendrons vers vous sous peu.";
    } else {
        $errorMsg = ($language === 'en')
            ? "Please fill in all fields correctly."
            : "Veuillez remplir correctement tous les champs requis.";
    }
}

require_once 'header.php';
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-20">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        
        <!-- Left Side: contact info -->
        <div class="lg:col-span-5 space-y-8">
            <div class="space-y-4">
                <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-primary">CONTACTEZ-NOUS</span>
                <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg uppercase tracking-tight text-on-surface">À VOTRE ÉCOUTE</h1>
            </div>
            
            <p class="text-on-surface-variant font-body-md leading-relaxed">
                Une question sur une taille, sur les délais de livraison à domicile dans votre wilaya, ou un besoin d'assistance ? Nos équipes sont là pour vous aider.
            </p>

            <div class="space-y-6 pt-6 border-t border-outline-variant/10">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary">mail</span>
                    <div>
                        <h4 class="font-label-sm text-label-sm uppercase text-on-surface-variant">Email</h4>
                        <p class="text-on-surface font-body-md mt-1">support@aurashop.com</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary">call</span>
                    <div>
                        <h4 class="font-label-sm text-label-sm uppercase text-on-surface-variant">Téléphone</h4>
                        <p class="text-on-surface font-body-md mt-1">+213 (0) 555 12 34 56</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary">location_on</span>
                    <div>
                        <h4 class="font-label-sm text-label-sm uppercase text-on-surface-variant">Showroom</h4>
                        <p class="text-on-surface font-body-md mt-1">Alger, Algérie</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Contact Form -->
        <div class="lg:col-span-7 bg-surface-container border border-outline-variant/10 rounded-2xl p-8 md:p-10">
            <?php if ($successMsg): ?>
                <div class="bg-primary/10 text-primary border border-primary/20 px-6 py-4 rounded-xl mb-6 font-body-md flex items-center gap-3">
                    <span class="material-symbols-outlined">check_circle</span>
                    <?php echo $successMsg; ?>
                </div>
            <?php endif; ?>

            <?php if ($errorMsg): ?>
                <div class="bg-error-container text-on-error-container border border-error/20 px-6 py-4 rounded-xl mb-6 font-body-md flex items-center gap-3">
                    <span class="material-symbols-outlined">error</span>
                    <?php echo $errorMsg; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70">Nom Complet *</label>
                    <input type="text" name="name" required placeholder="Votre nom"
                           class="bg-surface-container-high border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70">Adresse Email *</label>
                    <input type="email" name="email" required placeholder="nom@exemple.com"
                           class="bg-surface-container-high border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-70">Message *</label>
                    <textarea name="message" rows="5" required placeholder="Écrivez votre message ici..."
                              class="bg-surface-container-high border border-outline-variant/20 rounded-lg p-4 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors resize-none"></textarea>
                </div>

                <button type="submit" name="submit_contact"
                        class="w-full bg-primary text-on-primary font-label-sm text-label-sm uppercase py-4 rounded-full tracking-widest hover:bg-primary-fixed hover:scale-[1.01] active:scale-[0.99] transition-all">
                    Envoyer le message
                </button>
            </form>
        </div>

    </div>
</div>

<?php require_once 'footer.php'; ?>
