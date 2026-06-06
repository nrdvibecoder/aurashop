<?php

$toastMsg = $_SESSION['newsletter_msg'] ?? null;
unset($_SESSION['newsletter_msg']);
?>
    </main>

    <!-- Subtle Gold Divider -->
    <div class="h-[1px] w-full max-w-container-max mx-auto bg-gradient-to-r from-transparent via-primary/20 to-transparent"></div>

    <!-- Footer -->
    <footer class="bg-surface-container-low dark:bg-surface-container-lowest border-t border-outline-variant/10 transition-colors duration-300">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-gutter px-margin-mobile md:px-margin-desktop py-16 max-w-container-max mx-auto">
            
            <!-- Brand Column -->
            <div class="md:col-span-1 space-y-6">
                <div class="font-headline-lg text-headline-lg text-primary">AURA</div>
                <p class="text-on-surface-variant font-body-md text-body-md max-w-xs">
                    Définie par une précision architecturale et une philosophie de mode intemporelle et durable.
                </p>
                <div class="flex gap-4">
                    <a class="w-10 h-10 rounded-full border border-outline-variant/20 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-all duration-300" href="#">
                        <span class="material-symbols-outlined text-lg">public</span>
                    </a>
                    <a class="w-10 h-10 rounded-full border border-outline-variant/20 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-all duration-300" href="#">
                        <span class="material-symbols-outlined text-lg">photo_camera</span>
                    </a>
                    <a class="w-10 h-10 rounded-full border border-outline-variant/20 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary transition-all duration-300" href="#">
                        <span class="material-symbols-outlined text-lg">mail</span>
                    </a>
                </div>
            </div>
            
            <!-- Shop Links -->
            <div class="space-y-6">
                <h4 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface flex items-center gap-2">
                    <?php echo __('shop'); ?> <span class="w-8 h-[1px] bg-primary/40"></span>
                </h4>
                <ul class="space-y-4">
                    <li><a class="text-on-surface-variant font-body-md hover:text-primary transition-colors" href="shop.php?category=Women"><?php echo __('women'); ?></a></li>
                    <li><a class="text-on-surface-variant font-body-md hover:text-primary transition-colors" href="shop.php?category=Men"><?php echo __('men'); ?></a></li>
                    <li><a class="text-on-surface-variant font-body-md hover:text-primary transition-colors" href="shop.php?category=Accessories"><?php echo __('accessories'); ?></a></li>
                    <li><a class="text-on-surface-variant font-body-md hover:text-primary transition-colors" href="shop.php?category=Unisex"><?php echo __('unisex'); ?></a></li>
                </ul>
            </div>
            
            <!-- Company Links -->
            <div class="space-y-6">
                <h4 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface flex items-center gap-2">
                    Aura <span class="w-8 h-[1px] bg-primary/40"></span>
                </h4>
                <ul class="space-y-4">
                    <li><a class="text-on-surface-variant font-body-md hover:text-primary transition-colors" href="#"><?php echo __('explore'); ?></a></li>
                    <li><a class="text-on-surface-variant font-body-md hover:text-primary transition-colors" href="#"><?php echo __('discover_pieces'); ?></a></li>
                    <li><a class="text-on-surface-variant font-body-md hover:text-primary transition-colors" href="settings.php"><?php echo __('my_account'); ?></a></li>
                </ul>
            </div>
            
            <!-- Newsletter Column -->
            <div class="space-y-6">
                <h4 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface"><?php echo __('promo_code'); ?> / Newsletter</h4>
                <p class="text-on-surface-variant font-body-md"><?php echo __('hero_subtitle'); ?></p>
                <form action="" method="POST" class="relative">
                    <input class="w-full bg-surface-container border-0 border-b border-outline-variant/20 focus:border-primary focus:ring-0 text-on-surface pb-3 pr-10" placeholder="email@example.com" type="email" name="newsletter_email" required>
                    <button class="absolute right-0 bottom-3 text-primary hover:scale-110 transition-transform" type="submit">
                        <span class="material-symbols-outlined">east</span>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Bottom Row -->
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-8 border-t border-outline-variant/10 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-on-surface-variant font-label-sm text-label-sm opacity-60">© 2026 AURA. Architectural Elegance. Algerian Dinar (DA).</p>
            <div class="flex gap-6 text-on-surface-variant font-label-sm text-label-sm opacity-60">
                <span>CIB</span>
                <span>EDAHABIA</span>
                <span>CASH ON DELIVERY</span>
            </div>
        </div>
    </footer>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-8 right-8 z-50 flex flex-col gap-3 pointer-events-none"></div>

    <!-- Micro-interaction Scripts -->
    <script src="assets/js/cart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nav = document.querySelector('nav');
            
            // Shrink navbar on scroll
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    nav.classList.add('h-16');
                    nav.classList.remove('h-20');
                } else {
                    nav.classList.add('h-20');
                    nav.classList.remove('h-16');
                }
            });

            // Show PHP session toasts
            <?php if ($toastMsg): ?>
                if (window.showToast) {
                    window.showToast("<?php echo addslashes($toastMsg); ?>");
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>
