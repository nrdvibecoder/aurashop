<?php
$pageTitle = 'Accueil';
require_once 'config.php';
require_once 'header.php';

$newArrivals = [];
$featuredProducts = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM products WHERE is_new_arrival = TRUE ORDER BY id DESC LIMIT 4");
        $newArrivals = $stmt->fetchAll();
        $stmt2 = $pdo->query("SELECT * FROM products WHERE is_featured = TRUE ORDER BY id DESC LIMIT 4");
        $featuredProducts = $stmt2->fetchAll();
    } catch (PDOException $e) {
        
    }
}

$heroSlides = [
    ['img' => 'assets/hero1.png', 'title' => __('hero_title'), 'sub' => __('hero_subtitle')],
    ['img' => 'assets/hero2.png', 'title' => 'LA COLLECTION AUTOMNE', 'sub' => __('discover_pieces')],
    ['img' => 'assets/hero3.png', 'title' => 'PIECES D\'EXCEPTION', 'sub' => __('Laissez-vous Inspirer.')],
];

$categories = [
    ['slug' => 'Women',      'label' => __('women'),       'img' => 'assets/cat_women.png',      'sub' => 'Élégance Intemporelle'],
    ['slug' => 'Men',        'label' => __('men'),         'img' => 'assets/cat_men.png',        'sub' => 'Style Essentiel'],
    ['slug' => 'Accessories','label' => __('accessories'), 'img' => 'assets/cat_accessories.png','sub' => 'La Touche Finale'],
    ['slug' => 'Unisex',     'label' => __('unisex'),      'img' => 'assets/cat_unisex.png',     'sub' => 'Pour Tous'],
];
?>

<!-- ============ HERO CAROUSEL ============ -->
<section class="relative h-[70vh] max-h-[650px] w-full overflow-hidden -mt-24" id="hero">
    <div class="carousel-inner w-full h-full relative">
        <?php foreach ($heroSlides as $idx => $slide): ?>
        <div class="carousel-slide absolute inset-0 transition-all duration-1000 <?php echo $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>">
            <img src="<?php echo $slide['img']; ?>" 
                 alt="AURA Hero" 
                 class="w-full h-full object-cover object-top brightness-[0.55] scale-105 transition-transform duration-[10000ms]">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-surface/20 to-surface/60"></div>
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-margin-mobile z-10">
                <p class="font-label-sm text-label-sm tracking-[0.4em] uppercase text-primary mb-6 opacity-0 transition-all duration-700 delay-200" 
                   data-hero-animate>AURA</p>
                <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-on-surface mb-8 tracking-tight max-w-3xl opacity-0 transition-all duration-700 delay-300 translate-y-4"
                    data-hero-animate>
                    <?php echo sanitize($slide['title']); ?>
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-xl mb-10 opacity-0 transition-all duration-700 delay-400"
                   data-hero-animate>
                    <?php echo sanitize($slide['sub']); ?>
                </p>
                <a href="shop.php" 
                   class="inline-flex items-center gap-3 px-10 py-4 bg-primary text-on-primary font-label-sm text-label-sm uppercase tracking-widest rounded-full hover:bg-primary-fixed transition-all duration-300 hover:scale-105 opacity-0 transition-all duration-700 delay-500"
                   data-hero-animate>
                    <?php echo __('explore'); ?>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Dot controls -->
    <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-3 z-20">
        <?php foreach ($heroSlides as $idx => $slide): ?>
        <button class="carousel-dot w-2 h-2 rounded-full transition-all duration-300 <?php echo $idx === 0 ? 'bg-primary w-8' : 'bg-primary/30 hover:bg-primary/60'; ?>" 
                data-dot="<?php echo $idx; ?>"></button>
        <?php endforeach; ?>
    </div>

    <!-- Scroll indicator -->
    <div class="absolute bottom-10 right-12 flex flex-col items-center gap-2 opacity-50">
        <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-on-surface-variant" style="writing-mode: vertical-rl;">Scroll</span>
        <div class="w-[1px] h-12 bg-primary"></div>
    </div>
</section>

<!-- Divider -->
<div class="h-px w-full max-w-container-max mx-auto bg-gradient-to-r from-transparent via-primary/20 to-transparent my-0"></div>

<!-- ============ NEW ARRIVALS ============ -->
<section class="py-section-gap max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop" id="new-arrivals">
    <div class="flex justify-between items-end mb-16">
        <div>
            <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-primary mb-4 block"><?php echo __('new_arrival'); ?></span>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Nouvelles Pièces</h2>
        </div>
        <a href="shop.php?filter=new" class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant hover:text-primary border-b border-outline-variant pb-1 transition-all"><?php echo __('all'); ?></a>
    </div>
    
    <?php if (empty($newArrivals)): ?>
    <!-- Skeleton loaders -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="animate-pulse">
            <div class="aspect-[3/4] bg-surface-container-high rounded-lg mb-4"></div>
            <div class="h-3 bg-surface-container-high rounded mb-2 w-3/4"></div>
            <div class="h-3 bg-surface-container rounded w-1/2"></div>
        </div>
        <?php endfor; ?>
    </div>
    <p class="text-center text-on-surface-variant font-body-md mt-8"><?php echo __('no_products'); ?></p>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <?php foreach ($newArrivals as $product): 
            $finalPrice = (int)($product['price'] - ($product['price'] * $product['discount'] / 100));
            $imgSrc = !empty($product['base64_image']) ? $product['base64_image'] : ($product['image_url'] ?: 'assets/cat_all.png');
        ?>
        <div class="group hover-lift">
            <div class="relative aspect-[3/4] bg-surface-container rounded-lg overflow-hidden mb-4">
                <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                    <img src="<?php echo sanitize($imgSrc); ?>" 
                         alt="<?php echo sanitize($product['name']); ?>"
                         class="w-full h-full object-contain transition-transform duration-700 group-hover:scale-105">
                </a>
                <?php if ($product['is_new_arrival']): ?>
                <span class="absolute top-4 left-4 bg-primary text-on-primary font-label-sm text-label-sm uppercase tracking-widest px-3 py-1 rounded-full"><?php echo __('new_label'); ?></span>
                <?php endif; ?>
                <?php if ($product['discount'] > 0): ?>
                <span class="absolute top-4 right-4 bg-error text-on-error font-label-sm text-label-sm px-3 py-1 rounded-full">-<?php echo (int)$product['discount']; ?>%</span>
                <?php endif; ?>
                <div class="absolute inset-x-0 bottom-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                    <a href="product.php?id=<?php echo (int)$product['id']; ?>" 
                       class="block w-full py-3 bg-primary text-on-primary text-center font-label-sm text-label-sm uppercase tracking-widest rounded-lg hover:bg-primary-fixed transition-colors">
                        <?php echo __('explore'); ?>
                    </a>
                </div>
            </div>
            <h3 class="font-body-md text-body-md text-on-surface mb-1">
                <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="hover:text-primary transition-colors"><?php echo sanitize($product['name']); ?></a>
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-primary font-body-md"><?php echo format_price($finalPrice); ?></span>
                <?php if ($product['discount'] > 0): ?>
                <span class="text-outline font-label-sm text-label-sm line-through"><?php echo format_price($product['price']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Divider -->
<div class="h-px w-full max-w-container-max mx-auto bg-gradient-to-r from-transparent via-primary/20 to-transparent"></div>

<!-- ============ COLLECTIONS GRID ============ -->
<section class="py-section-gap max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop" id="collections">
    <div class="mb-12">
        <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-on-surface-variant"><?php echo __('categories'); ?></span>
        <div class="h-px w-24 bg-primary mt-4"></div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <?php foreach ($categories as $cat): ?>
        <div class="relative h-[280px] sm:h-[400px] md:h-[500px] rounded-lg overflow-hidden group border border-transparent hover:border-primary/20 transition-colors duration-500">
            <img src="<?php echo $cat['img']; ?>" 
                 alt="<?php echo sanitize($cat['label']); ?>"
                 class="absolute inset-0 w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-110">
            <div class="absolute inset-0 bg-gradient-to-t from-background/90 via-background/20 to-transparent"></div>
            <div class="absolute bottom-4 left-4 md:bottom-8 md:left-8 right-4">
                <h3 class="font-headline-md text-lg md:text-headline-md text-on-surface mb-0.5 md:mb-1 truncate"><?php echo sanitize($cat['label']); ?></h3>
                <p class="text-on-surface-variant font-label-sm text-[10px] md:text-label-sm tracking-widest uppercase truncate"><?php echo sanitize($cat['sub']); ?></p>
            </div>
            <a class="absolute inset-0 z-10" href="shop.php?category=<?php echo urlencode($cat['slug']); ?>"></a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Divider -->
<div class="h-px w-full max-w-container-max mx-auto bg-gradient-to-r from-transparent via-primary/20 to-transparent"></div>

<!-- ============ FEATURED PIECES ============ -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-section-gap max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
    <div class="flex justify-between items-end mb-16">
        <div>
            <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-primary mb-4 block"><?php echo __('featured'); ?></span>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Pièces en Vedette</h2>
        </div>
        <a href="shop.php?filter=featured" class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant hover:text-primary border-b border-outline-variant pb-1 transition-all"><?php echo __('all'); ?></a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <?php foreach ($featuredProducts as $product): 
            $finalPrice = (int)($product['price'] - ($product['price'] * $product['discount'] / 100));
            $imgSrc = !empty($product['base64_image']) ? $product['base64_image'] : ($product['image_url'] ?: 'assets/cat_all.png');
        ?>
        <div class="group hover-lift">
            <div class="relative aspect-[3/4] bg-surface-container rounded-lg overflow-hidden mb-4">
                <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                    <img src="<?php echo sanitize($imgSrc); ?>" 
                         alt="<?php echo sanitize($product['name']); ?>"
                         class="w-full h-full object-contain transition-transform duration-700 group-hover:scale-105">
                </a>
                <?php if ($product['discount'] > 0): ?>
                <span class="absolute top-4 right-4 bg-error text-on-error font-label-sm text-label-sm px-3 py-1 rounded-full">-<?php echo (int)$product['discount']; ?>%</span>
                <?php endif; ?>
                <div class="absolute inset-x-0 bottom-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                    <a href="product.php?id=<?php echo (int)$product['id']; ?>" 
                       class="block w-full py-3 bg-primary text-on-primary text-center font-label-sm text-label-sm uppercase tracking-widest rounded-lg hover:bg-primary-fixed transition-colors">
                        <?php echo __('explore'); ?>
                    </a>
                </div>
            </div>
            <h3 class="font-body-md text-body-md text-on-surface mb-1">
                <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="hover:text-primary transition-colors"><?php echo sanitize($product['name']); ?></a>
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-primary font-body-md"><?php echo format_price($finalPrice); ?></span>
                <?php if ($product['discount'] > 0): ?>
                <span class="text-outline font-label-sm text-label-sm line-through"><?php echo format_price($product['price']); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============ NEWSLETTER SECTION ============ -->
<section class="py-section-gap px-margin-mobile md:px-margin-desktop max-w-container-max mx-auto">
    <div class="relative bg-surface-container rounded-xl overflow-hidden py-24 px-8 text-center border border-primary/10">
        <div class="absolute inset-0 opacity-5 pointer-events-none overflow-hidden">
            <div class="absolute top-[-50%] left-[-10%] w-[120%] h-[120%] bg-gradient-to-br from-primary via-transparent to-transparent rotate-12"></div>
        </div>
        <div class="relative z-10 max-w-2xl mx-auto">
            <span class="font-label-sm text-label-sm tracking-[0.3em] uppercase text-primary block mb-6">Newsletter</span>
            <h2 class="font-headline-lg text-headline-lg text-on-surface mb-4 tracking-widest uppercase">Rejoindre le Club</h2>
            <p class="text-on-surface-variant font-body-lg text-body-lg mb-10">Accédez en avant-première aux nouvelles collections et à nos événements saisonniers.</p>
            <form action="" method="POST" class="flex flex-col md:flex-row gap-4">
                <input class="flex-grow bg-transparent border-0 border-b-2 border-outline-variant/30 focus:border-primary focus:ring-0 text-on-surface placeholder:text-outline py-4 transition-all duration-300 text-body-md" 
                       placeholder="votremail@xxx.com" 
                       type="email" 
                       name="newsletter_email"
                       required>
                <button class="bg-primary text-on-primary px-12 py-4 rounded-full font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-all duration-300 hover:scale-105" 
                        type="submit">
                    S'inscrire
                </button>
            </form>
        </div>
    </div>
</section>

<style>
.hover-lift { transition: transform 0.4s cubic-bezier(0.2, 0, 0.2, 1); }
.hover-lift:hover { transform: translateY(-8px); }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Hero Carousel
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    let current = 0;

    function goToSlide(idx) {
        slides[current].classList.remove('opacity-100', 'z-10');
        slides[current].classList.add('opacity-0', 'z-0');
        dots[current].classList.remove('bg-primary', 'w-8');
        dots[current].classList.add('bg-primary/30', 'w-2');

        current = idx;
        slides[current].classList.remove('opacity-0', 'z-0');
        slides[current].classList.add('opacity-100', 'z-10');
        dots[current].classList.remove('bg-primary/30', 'w-2');
        dots[current].classList.add('bg-primary', 'w-8');

        // Animate text in the new slide
        const animEls = slides[current].querySelectorAll('[data-hero-animate]');
        animEls.forEach(el => {
            el.classList.remove('opacity-0', 'translate-y-4');
            el.classList.add('opacity-100', 'translate-y-0');
        });
    }

    dots.forEach((dot, idx) => dot.addEventListener('click', () => goToSlide(idx)));
    
    // Animate first slide on load
    setTimeout(() => {
        const firstAnimEls = slides[0].querySelectorAll('[data-hero-animate]');
        firstAnimEls.forEach(el => {
            el.classList.remove('opacity-0', 'translate-y-4');
            el.classList.add('opacity-100', 'translate-y-0');
        });
    }, 100);

    // Auto-play
    setInterval(() => {
        goToSlide((current + 1) % slides.length);
    }, 5000);

    // Intersection Observer for fade-in sections
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('opacity-100', 'translate-y-0');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('section:not(#hero)').forEach(section => {
        section.classList.add('opacity-0', 'translate-y-6', 'transition-all', 'duration-700');
        observer.observe(section);
    });
});
</script>

<?php require_once 'footer.php'; ?>
