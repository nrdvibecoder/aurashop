<?php
require_once 'config.php';
$cart = get_cart();
$auth = get_auth();

$cartCount = 0;
foreach ($cart as $item) {
    $cartCount += (int)($item['quantity'] ?? 1);
}

$currentLang = $language;
$toggleLang = ($currentLang === 'en') ? 'fr' : 'en';
$toggleLangLabel = ($currentLang === 'en') ? 'FR' : 'EN';
?>
<!DOCTYPE html>
<html class="dark overflow-x-hidden" lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>AURA | <?php echo isset($pageTitle) ? sanitize($pageTitle) : 'Architectural Elegance'; ?></title>
    
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
        body {
            background-color: #031710;
            color: #d0e8dc;
            -webkit-font-smoothing: antialiased;
        }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .gold-accent {
            background-color: #b8ccb6; /* Sage green accent from Stitch */
        }
        /* Page fade-in */
        .page-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                "background": "#031710",
                "on-background": "#d0e8dc",
                "surface": "#031710",
                "surface-dim": "#031710",
                "surface-bright": "#293d35",
                "surface-container-lowest": "#00120b",
                "surface-container-low": "#0b1f18",
                "surface-container": "#0f231c",
                "surface-container-high": "#1a2e26",
                "surface-container-highest": "#253930",
                "on-surface": "#d0e8dc",
                "on-surface-variant": "#c2c8c2",
                "inverse-surface": "#d0e8dc",
                "inverse-on-surface": "#20342c",
                "outline": "#8c928d",
                "outline-variant": "#424844",
                "surface-tint": "#b2cdbc",
                "primary": "#b2cdbc",
                "on-primary": "#1e352a",
                "primary-container": "#051c12",
                "on-primary-container": "#6e8778",
                "inverse-primary": "#4c6456",
                "secondary": "#c9c6c2",
                "on-secondary": "#31302d",
                "secondary-container": "#474743",
                "on-secondary-container": "#b7b5b0",
                "tertiary": "#b8ccb6",
                "on-tertiary": "#243425",
                "tertiary-container": "#0c1b0e",
                "on-tertiary-container": "#748672",
                "error": "#ffb4ab",
                "on-error": "#690005",
                "error-container": "#93000a",
                "on-error-container": "#ffdad6",
                "primary-fixed": "#cee9d8",
                "primary-fixed-dim": "#b2cdbc",
                "on-primary-fixed": "#082015",
                "on-primary-fixed-variant": "#344c3f",
                "secondary-fixed": "#e5e2dd",
                "secondary-fixed-dim": "#c9c6c2",
                "on-secondary-fixed": "#1c1c19",
                "on-secondary-fixed-variant": "#474743",
                "tertiary-fixed": "#d4e8d1",
                "tertiary-fixed-dim": "#b8ccb6",
                "on-tertiary-fixed": "#0f1f11",
                "on-tertiary-fixed-variant": "#3a4b3a"
              },
              "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "24px",
                "xl": "32px",
                "full": "9999px"
              },
              "spacing": {
                "container-max": "1440px",
                "margin-mobile": "24px",
                "section-gap": "120px",
                "gutter": "24px",
                "margin-desktop": "80px"
              },
              "fontFamily": {
                "headline-md": ["Playfair Display"],
                "body-md": ["Inter"],
                "headline-lg": ["Playfair Display"],
                "body-lg": ["Inter"],
                "display-lg": ["Playfair Display"],
                "display-lg-mobile": ["Playfair Display"],
                "label-sm": ["Inter"]
              },
              "fontSize": {
                "headline-md": ["24px", {"lineHeight": "1.3", "fontWeight": "400"}],
                "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                "headline-lg": ["32px", {"lineHeight": "1.2", "letterSpacing": "0.05em", "fontWeight": "400"}],
                "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "300"}],
                "display-lg": ["64px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "400"}],
                "display-lg-mobile": ["40px", {"lineHeight": "1.2", "fontWeight": "400"}],
                "label-sm": ["12px", {"lineHeight": "1", "letterSpacing": "0.1em", "fontWeight": "500"}]
              }
            }
          }
        }
    </script>
</head>
<body class="font-body-md text-body-md bg-background text-on-surface selection:bg-primary selection:text-on-primary min-h-screen flex flex-col overflow-x-hidden">
    <!-- Top Navigation Bar -->
    <nav class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-xl border-b border-outline-variant/10 h-20 transition-all duration-300">
        <div class="flex justify-between items-center px-margin-mobile md:px-margin-desktop h-full max-w-container-max mx-auto relative">
            
            <!-- Left: Burger Menu (Mobile Only) & Desktop Links -->
            <div class="flex items-center gap-6">
                <!-- Mobile Burger Button (only visible on mobile/tablet < md) -->
                <button id="mobile-menu-trigger" class="md:hidden flex items-center text-on-surface-variant hover:text-primary transition-colors focus:outline-none">
                    <span class="material-symbols-outlined text-2xl">menu</span>
                </button>

                <!-- Desktop navigation options (hidden on mobile < md) -->
                <div class="hidden md:flex items-center gap-6">
                    <!-- Dropdown Trigger Container -->
                    <div class="relative group">
                        <button class="flex items-center gap-1 text-on-surface-variant hover:text-primary transition-colors font-label-sm text-label-sm uppercase tracking-widest py-2">
                            <span><?php echo __('categories'); ?></span>
                            <span class="material-symbols-outlined text-sm transition-transform group-hover:rotate-180">expand_more</span>
                        </button>
                        <!-- Dropdown Content -->
                        <div class="absolute left-0 mt-1 w-56 bg-surface-container border border-outline-variant/20 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 py-2">
                            <a href="shop.php?category=Women" class="block px-6 py-3 text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><?php echo __('women'); ?></a>
                            <a href="shop.php?category=Men" class="block px-6 py-3 text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><?php echo __('men'); ?></a>
                            <a href="shop.php?category=Accessories" class="block px-6 py-3 text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><?php echo __('accessories'); ?></a>
                            <a href="shop.php?category=Unisex" class="block px-6 py-3 text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><?php echo __('unisex'); ?></a>
                            <div class="border-t border-outline-variant/10 my-2"></div>
                            <a href="shop.php" class="block px-6 py-3 text-body-md text-on-surface hover:bg-surface-container-high hover:text-primary transition-colors font-medium"><?php echo __('all'); ?></a>
                        </div>
                    </div>
                    <!-- Main Shop Link -->
                    <a href="shop.php" class="text-on-surface-variant hover:text-primary transition-colors font-label-sm text-label-sm uppercase tracking-widest"><?php echo __('shop'); ?></a>
                    <!-- About & Contact links -->
                    <a href="about.php" class="text-on-surface-variant hover:text-primary transition-colors font-label-sm text-label-sm uppercase tracking-widest"><?php echo __('about'); ?></a>
                    <a href="contact.php" class="text-on-surface-variant hover:text-primary transition-colors font-label-sm text-label-sm uppercase tracking-widest"><?php echo __('contact'); ?></a>
                </div>
            </div>

            <!-- Center: Logo -->
            <div class="absolute left-1/2 -translate-x-1/2 flex items-center">
                <a href="index.php" class="font-headline-lg text-headline-lg tracking-widest text-on-surface hover:text-primary transition-colors">AURA</a>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-4 md:gap-6">
                <!-- Search bar -->
                <form action="shop.php" method="GET" class="hidden md:flex items-center relative">
                    <input type="text" name="q" placeholder="<?php echo __('search'); ?>..." class="bg-surface-container border border-outline-variant/20 rounded-full py-1.5 pl-4 pr-10 text-label-sm text-on-surface placeholder:text-outline/40 w-44 focus:w-60 transition-all duration-300">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-on-surface-variant hover:text-primary text-xl">search</button>
                </form>
                
                <!-- Search Icon (Mobile) -->
                <a href="shop.php" class="md:hidden material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors">search</a>

                <!-- Language toggle -->
                <a href="set_lang.php?lang=<?php echo $toggleLang; ?>" class="font-label-sm text-label-sm text-on-surface-variant hover:text-primary transition-colors uppercase tracking-widest"><?php echo $toggleLangLabel; ?></a>

                <!-- Cart icon with badge -->
                <a href="cart.php" class="relative group">
                    <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors">shopping_bag</span>
                    <span id="cart-badge" class="<?php echo ($cartCount > 0) ? '' : 'hidden'; ?> absolute -top-1.5 -right-1.5 w-4.5 h-4.5 bg-primary text-on-primary text-[10px] font-bold flex items-center justify-center rounded-full border border-surface transition-all">
                        <?php echo $cartCount; ?>
                    </span>
                </a>

                <!-- User Account / Admin Icon -->
                <?php if ($auth): ?>
                    <div class="relative group hidden md:block">
                        <button class="flex items-center gap-1.5 text-on-surface-variant hover:text-primary transition-colors py-2">
                            <span class="material-symbols-outlined text-2xl">account_circle</span>
                        </button>
                        <div class="absolute right-0 mt-1 w-48 bg-surface-container border border-outline-variant/20 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 py-2">
                            <span class="block px-6 py-2 text-label-sm text-outline uppercase border-b border-outline-variant/10 truncate font-semibold"><?php echo sanitize($auth['role']); ?></span>
                            <?php if ($auth['role'] === 'admin'): ?>
                                <a href="admin.php" class="block px-6 py-3 text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><?php echo __('admin_dashboard'); ?></a>
                            <?php endif; ?>
                            <a href="settings.php" class="block px-6 py-3 text-body-md text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors"><?php echo __('settings'); ?></a>
                            <div class="border-t border-outline-variant/10 my-2"></div>
                            <a href="logout.php" class="block px-6 py-3 text-body-md text-error hover:bg-surface-container-high transition-colors"><?php echo __('logout'); ?></a>
                        </div>
                    </div>
                    <!-- Mobile Account shortcut directly to settings -->
                    <a href="settings.php" class="md:hidden material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors">account_circle</a>
                <?php else: ?>
                    <a href="login.php" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors" title="<?php echo __('login'); ?>">account_circle</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Drawer -->
    <div id="mobile-drawer" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 md:hidden">
        <div id="mobile-drawer-content" class="w-80 h-full bg-surface-container-lowest border-r border-outline-variant/10 flex flex-col justify-between p-8 -translate-x-full transition-transform duration-300 ease-out">
            <div>
                <!-- Header of Drawer -->
                <div class="flex justify-between items-center mb-8">
                    <span class="font-headline-lg text-headline-lg tracking-widest text-primary">AURA</span>
                    <button id="mobile-drawer-close" class="material-symbols-outlined text-on-surface-variant hover:text-primary transition-colors focus:outline-none">close</button>
                </div>

                <!-- Navigation List -->
                <nav class="space-y-6">
                    <!-- Boutique Section -->
                    <div class="space-y-2">
                        <span class="block font-label-sm text-[10px] text-outline uppercase tracking-[0.2em] mb-1"><?php echo __('shop'); ?></span>
                        <a href="shop.php?category=Women" class="block font-headline-md text-headline-md text-on-surface hover:text-primary transition-colors py-1 pl-2 border-l border-primary/20"><?php echo __('women'); ?></a>
                        <a href="shop.php?category=Men" class="block font-headline-md text-headline-md text-on-surface hover:text-primary transition-colors py-1 pl-2 border-l border-primary/20"><?php echo __('men'); ?></a>
                        <a href="shop.php?category=Accessories" class="block font-headline-md text-headline-md text-on-surface hover:text-primary transition-colors py-1 pl-2 border-l border-primary/20"><?php echo __('accessories'); ?></a>
                        <a href="shop.php?category=Unisex" class="block font-headline-md text-headline-md text-on-surface hover:text-primary transition-colors py-1 pl-2 border-l border-primary/20"><?php echo __('unisex'); ?></a>
                        <a href="shop.php" class="block font-body-md text-on-surface-variant hover:text-primary transition-colors py-1 pl-2 border-l border-outline-variant/20"><?php echo __('all'); ?></a>
                    </div>

                    <!-- Compte Section -->
                    <div class="space-y-2 pt-4">
                        <span class="block font-label-sm text-[10px] text-outline uppercase tracking-[0.2em] mb-1"><?php echo __('account'); ?></span>
                        <?php if ($auth): ?>
                            <a href="settings.php" class="block font-body-md text-on-surface hover:text-primary transition-colors"><?php echo __('profile'); ?></a>
                            <a href="settings.php?tab=orders" class="block font-body-md text-on-surface hover:text-primary transition-colors"><?php echo __('order_tracking'); ?></a>
                            <?php if ($auth['role'] === 'admin'): ?>
                                <a href="admin.php" class="block font-body-md text-primary hover:underline transition-colors"><?php echo __('admin_dashboard'); ?></a>
                            <?php endif; ?>
                            <a href="logout.php" class="block font-body-md text-error hover:text-error/80 transition-colors"><?php echo __('logout'); ?></a>
                        <?php else: ?>
                            <a href="login.php" class="block font-body-md text-on-surface hover:text-primary transition-colors"><?php echo __('login'); ?> / <?php echo __('register'); ?></a>
                        <?php endif; ?>
                    </div>

                    <!-- Infos Section -->
                    <div class="space-y-2 pt-4">
                        <span class="block font-label-sm text-[10px] text-outline uppercase tracking-[0.2em] mb-1">Information</span>
                        <a href="about.php" class="block font-body-md text-on-surface-variant hover:text-primary transition-colors"><?php echo __('about'); ?></a>
                        <a href="contact.php" class="block font-body-md text-on-surface-variant hover:text-primary transition-colors"><?php echo __('contact'); ?></a>
                    </div>
                </nav>
            </div>

            <!-- Footer of Drawer -->
            <div class="border-t border-outline-variant/10 pt-6">
                <p class="font-label-sm text-[10px] text-on-surface-variant uppercase tracking-widest opacity-60">© 2026 AURA Algérie</p>
            </div>
        </div>
    </div>

    <!-- Drawer toggling script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trigger = document.getElementById('mobile-menu-trigger');
            const drawer = document.getElementById('mobile-drawer');
            const drawerContent = document.getElementById('mobile-drawer-content');
            const closeBtn = document.getElementById('mobile-drawer-close');

            function openDrawer() {
                drawer.classList.remove('opacity-0', 'pointer-events-none');
                drawer.classList.add('opacity-100');
                drawerContent.classList.remove('-translate-x-full');
            }

            function closeDrawer() {
                drawer.classList.remove('opacity-100');
                drawer.classList.add('opacity-0', 'pointer-events-none');
                drawerContent.classList.add('-translate-x-full');
            }

            if (trigger) trigger.addEventListener('click', openDrawer);
            if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
            if (drawer) {
                drawer.addEventListener('click', (e) => {
                    if (e.target === drawer) closeDrawer();
                });
            }
        });
    </script>

    <main class="flex-grow pt-24 pb-12 page-fade-in">
