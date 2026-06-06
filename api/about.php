<?php
$pageTitle = 'À Propos – AURA';
require_once 'config.php';
require_once 'header.php';
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-20 space-y-16">
    
    <!-- Hero Header Section -->
    <div class="text-center max-w-3xl mx-auto space-y-6">
        <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-primary">NOTRE PHILOSOPHIE</span>
        <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg uppercase tracking-tight text-on-surface">L'ÉLÉGANCE ARCHITECTURALE</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
            Fondée sur l'harmonie des lignes et la pureté des coupes, AURA conçoit un vestiaire minimaliste haut de gamme, pensé pour s'intégrer avec précision dans le quotidien moderne.
        </p>
    </div>

    <!-- Image + Narrative block -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center pt-8">
        <div class="aspect-[4/3] rounded-xl overflow-hidden bg-surface-container border border-outline-variant/10">
            <!-- Simulated Architectural Fashion visual -->
            <img src="assets/hero3.png" alt="AURA Design Philosophy" class="w-full h-full object-cover object-top brightness-90">
        </div>
        <div class="space-y-6">
            <h2 class="font-headline-lg text-headline-lg text-on-surface uppercase tracking-wider">Durabilité & Précision</h2>
            <p class="text-on-surface-variant font-body-md leading-relaxed">
                Chaque pièce est dessinée en suivant des proportions précises, inspirées de l'architecture brutaliste et moderniste. Nous favorisons des matières nobles, durables et agréables à porter, capables de résister à l'épreuve du temps.
            </p>
            <p class="text-on-surface-variant font-body-md leading-relaxed">
                Pas de superflu, pas d'artifices. Nous croyons en la force du détail discret : une surpiqûre parfaitement alignée, un col structuré, une texture rigoureusement sélectionnée.
            </p>
            <div class="pt-4">
                <a href="shop.php" class="inline-flex items-center gap-2 px-8 py-3 bg-primary text-on-primary font-label-sm text-label-sm uppercase tracking-widest rounded-full hover:bg-primary-fixed transition-colors">
                    Explorer la collection
                </a>
            </div>
        </div>
    </div>

    <!-- Core Values Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-12 border-t border-outline-variant/10">
        <div class="space-y-4">
            <span class="material-symbols-outlined text-primary text-4xl">architecture</span>
            <h3 class="font-headline-md text-headline-md text-on-surface">Structure</h3>
            <p class="text-on-surface-variant font-body-md text-sm">
                Des lignes nettes, des silhouettes affirmées et un ajustement rigoureux qui structurent l'allure au quotidien.
            </p>
        </div>
        <div class="space-y-4">
            <span class="material-symbols-outlined text-primary text-4xl">eco</span>
            <h3 class="font-headline-md text-headline-md text-on-surface">Responsabilité</h3>
            <p class="text-on-surface-variant font-body-md text-sm">
                Une production réfléchie en séries limitées pour limiter le gaspillage et garantir l'exclusivité de chaque modèle.
            </p>
        </div>
        <div class="space-y-4">
            <span class="material-symbols-outlined text-primary text-4xl">explore</span>
            <h3 class="font-headline-md text-headline-md text-on-surface">Identité Locale</h3>
            <p class="text-on-surface-variant font-body-md text-sm">
                Une vision internationale adaptée aux sensibilités et aux exigences esthétiques de la scène mode algérienne.
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
