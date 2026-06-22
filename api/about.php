<?php
require_once 'config.php';
$pageTitle = __('about');
require_once 'header.php';
?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-20 space-y-16">
    
    
    <div class="text-center max-w-3xl mx-auto space-y-6">
        <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-primary">AURA</span>
        <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg uppercase tracking-tight text-on-surface">
            <?php echo __('about_title'); ?>
        </h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
            <?php echo __('about_tagline'); ?>
        </p>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center pt-8">
        <div class="aspect-[4/3] rounded-xl overflow-hidden bg-surface-container border border-outline-variant/10">
            <img src="assets/aboutus.png" alt="AURA Design Philosophy" class="w-full h-full object-cover object-top brightness-90">
        </div>
        <div class="space-y-6">
            <h2 class="font-headline-lg text-headline-lg text-on-surface uppercase tracking-wider"><?php echo __('about_narrative_title'); ?></h2>
            <p class="text-on-surface-variant font-body-md leading-relaxed">
                 <?php echo __('about_narrative_p1'); ?>
            </p>
            <p class="text-on-surface-variant font-body-md leading-relaxed">
                <?php echo __('about_narrative_p2'); ?>
            </p>
            <div class="pt-4">
                <a href="shop.php" class="inline-flex items-center gap-2 px-8 py-3 bg-primary text-on-primary font-label-sm text-label-sm uppercase tracking-widest rounded-full hover:bg-primary-fixed transition-colors">
                    <?php echo __('about_explore'); ?>
                </a>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-12 border-t border-outline-variant/10">
        <div class="space-y-4">
            <span class="material-symbols-outlined text-primary text-4xl">architecture</span>
            <h3 class="font-headline-md text-headline-md text-on-surface"><?php echo __('about_val1_title'); ?></h3>
            <p class="text-on-surface-variant font-body-md text-sm">
                <?php echo __('about_val1_desc'); ?>
            </p>
        </div>
        <div class="space-y-4">
            <span class="material-symbols-outlined text-primary text-4xl">eco</span>
            <h3 class="font-headline-md text-headline-md text-on-surface"><?php echo __('about_val2_title'); ?></h3>
            <p class="text-on-surface-variant font-body-md text-sm">
                <?php echo __('about_val2_desc'); ?>
            </p>
        </div>
        <div class="space-y-4">
            <span class="material-symbols-outlined text-primary text-4xl">explore</span>
            <h3 class="font-headline-md text-headline-md text-on-surface"><?php echo __('about_val3_title'); ?></h3>
            <p class="text-on-surface-variant font-body-md text-sm">
                <?php echo __('about_val3_desc'); ?>
            </p>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
