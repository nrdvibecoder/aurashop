<?php
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header("Location: shop.php");
    exit;
}

$product = null;
$relatedProducts = [];
$error = null;

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            header("Location: shop.php");
            exit;
        }

        
        $stmt2 = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4");
        $stmt2->execute([$product['category_id'], $id]);
        $relatedProducts = $stmt2->fetchAll();
    } catch (PDOException $e) {
        $error = $e->getMessage();
        header("Location: shop.php");
        exit;
    }
} else {
    header("Location: shop.php");
    exit;
}

$pageTitle = sanitize($product['name']);

$colors = !empty($product['colors']) ? json_decode($product['colors'], true) : [];
$sizes = !empty($product['sizes']) ? json_decode($product['sizes'], true) : [];
$colorImages = !empty($product['color_images']) ? json_decode($product['color_images'], true) : [];
$colors = is_array($colors) ? $colors : [];
$sizes = is_array($sizes) ? $sizes : [];
$colorImages = is_array($colorImages) ? $colorImages : [];

$finalPrice = (int)($product['price'] - ($product['price'] * $product['discount'] / 100));
$mainImgSrc = !empty($product['base64_image']) ? $product['base64_image'] : ($product['image_url'] ?: 'assets/cat_all.png');

$firstColorImgs = [];
if (!empty($colors) && !empty($colorImages)) {
    $firstColor = $colors[0]['name'] ?? '';
    $firstColorImgs = $colorImages[$firstColor] ?? [];
}

require_once 'header.php';
?>

<style>
    .size-btn[data-selected="true"] {
        border-color: var(--color-primary, #8B7355);
        color: var(--color-primary, #8B7355);
        background-color: color-mix(in srgb, var(--color-primary, #8B7355) 10%, transparent);
    }
</style>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-8 pb-16">
    
    <nav class="flex gap-2 font-label-sm text-label-sm text-on-surface-variant mb-10 flex-wrap">
        <a href="index.php" class="hover:text-primary transition-colors"><?php echo __('home'); ?></a>
        <span>/</span>
        <a href="shop.php?category=<?php echo urlencode($product['category']); ?>" class="hover:text-primary transition-colors capitalize"><?php echo sanitize($product['category']); ?></a>
        <span>/</span>
        <span class="text-on-surface"><?php echo sanitize($product['name']); ?></span>
    </nav>

    
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16">
        
        
        <div class="lg:col-span-7 space-y-4">
            
            <div class="aspect-[4/5] overflow-hidden rounded-xl bg-surface-container-low relative group cursor-crosshair">
                <img id="main-product-image" 
                     src="<?php echo sanitize(!empty($firstColorImgs) ? $firstColorImgs[0] : $mainImgSrc); ?>" 
                     alt="<?php echo sanitize($product['name']); ?>"
                     class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-105">
                <?php if ($product['stock'] == 0): ?>
                <div class="absolute inset-0 bg-surface/50 flex items-center justify-center">
                    <span class="font-label-sm text-label-sm uppercase tracking-widest border border-outline-variant px-6 py-3 text-on-surface"><?php echo __('out_of_stock'); ?></span>
                </div>
                <?php endif; ?>
            </div>

            
            <div id="thumbnail-container" class="grid grid-cols-4 gap-3">
                <?php 
                $galleryImgs = !empty($firstColorImgs) ? $firstColorImgs : [$mainImgSrc];
                foreach ($galleryImgs as $tIdx => $tSrc): 
                ?>
                <button class="aspect-square overflow-hidden rounded-xl bg-surface-container-low border-2 transition-all <?php echo $tIdx === 0 ? 'border-primary' : 'border-transparent hover:border-outline-variant'; ?>">
                    <img src="<?php echo sanitize($tSrc); ?>" alt="Thumbnail <?php echo $tIdx + 1; ?>" class="w-full h-full object-cover object-top">
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        
        <div class="lg:col-span-5 flex flex-col gap-8">
            
            
            <div>
                <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary block mb-2"><?php echo sanitize($product['category']); ?><?php echo $product['subcategory'] ? ' / ' . sanitize($product['subcategory']) : ''; ?></span>
                <h1 class="font-headline-lg text-headline-lg text-on-surface mb-4"><?php echo sanitize($product['name']); ?></h1>
                <div class="flex items-baseline gap-4">
                    <span class="text-primary font-headline-md text-headline-md"><?php echo format_price($finalPrice); ?></span>
                    <?php if ($product['discount'] > 0): ?>
                    <span class="text-outline font-body-lg line-through"><?php echo format_price($product['price']); ?></span>
                    <span class="bg-error text-on-error font-label-sm text-label-sm px-3 py-1 rounded-full">-<?php echo (int)$product['discount']; ?>%</span>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="h-px bg-gradient-to-r from-transparent via-outline-variant/30 to-transparent"></div>

            
            <?php if (!empty($colors)): ?>
            <div>
                <span class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant block mb-3">
                    <?php echo __('color'); ?>: <span id="color-label" class="text-on-surface"><?php echo sanitize($colors[0]['name'] ?? ''); ?></span>
                </span>
                <div class="flex gap-3 flex-wrap">
                    <?php foreach ($colors as $cIdx => $color): ?>
                    <button class="w-9 h-9 rounded-full border-2 border-outline-variant/30 transition-all hover:scale-110 <?php echo $cIdx === 0 ? 'ring-2 ring-offset-4 ring-offset-surface ring-primary' : 'ring-0 hover:ring-2 ring-offset-2 ring-offset-surface ring-outline-variant'; ?>"
                            style="background-color: <?php echo sanitize($color['hex'] ?? '#ccc'); ?>"
                            data-color-swatch="<?php echo sanitize($color['name']); ?>"
                            title="<?php echo sanitize($color['name']); ?>">
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if (!empty($sizes)): ?>
            <div>
                <div class="flex justify-between items-center mb-3">
                    <span class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant"><?php echo __('select_size'); ?></span>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <?php foreach ($sizes as $sz): ?>
                    <button class="size-btn py-3 px-6 border font-label-sm text-label-sm transition-all rounded-lg border-outline-variant/30 text-on-surface hover:border-primary data-[selected=true]:border-primary data-[selected=true]:text-primary data-[selected=true]:bg-primary/10"
                            data-size-btn="<?php echo sanitize($sz); ?>"
                            data-selected="false">
                        <?php echo sanitize($sz); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <input type="hidden" id="forced-size" value="Unique">
            <?php endif; ?>

            
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <span class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface-variant"><?php echo __('quantity'); ?></span>
                    <div class="flex items-center border border-outline-variant/20 rounded-full bg-surface-container-lowest p-1">
                        <button id="qty-minus" type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container transition-all">
                            <span class="material-symbols-outlined text-base">remove</span>
                        </button>
                        <input id="qty-input" type="number" value="1" min="1" max="<?php echo max(1, (int)$product['stock']); ?>"
                               class="w-12 text-center bg-transparent border-0 font-body-md text-on-surface focus:ring-0 focus:outline-none p-0 select-none">
                        <button id="qty-plus" type="button" class="w-8 h-8 rounded-full flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container transition-all">
                            <span class="material-symbols-outlined text-base">add</span>
                        </button>
                    </div>
                    <span class="font-label-sm text-label-sm text-outline">
                        <?php echo $product['stock']; ?> en stock
                    </span>
                </div>

                <button id="add-to-cart-btn"
                        data-product-id="<?php echo (int)$product['id']; ?>"
                        data-product-name="<?php echo sanitize($product['name']); ?>"
                        data-product-price="<?php echo $finalPrice; ?>"
                        class="w-full py-5 bg-primary text-on-primary font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-colors rounded-none <?php echo $product['stock'] == 0 ? 'opacity-40 cursor-not-allowed' : ''; ?>"
                        <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] == 0 ? __('out_of_stock') : __('add_to_cart'); ?>
                </button>
                <a href="cart.php" class="block w-full py-5 border border-outline-variant/50 text-on-surface font-label-sm text-label-sm uppercase tracking-widest hover:border-on-surface text-center transition-colors rounded-none">
                    <?php echo __('cart'); ?>
                </a>
            </div>

            
            <div class="space-y-4">
                <?php if ($product['description']): ?>
                <details class="group border-b border-outline-variant/10 pb-4" open>
                    <summary class="flex justify-between items-center cursor-pointer list-none py-2">
                        <span class="font-label-sm text-label-sm uppercase tracking-widest"><?php echo __('description'); ?></span>
                        <span class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <p class="font-body-md text-body-md text-on-surface-variant pt-4 leading-relaxed">
                        <?php echo nl2br(sanitize($product['description'])); ?>
                    </p>
                </details>
                <?php endif; ?>
                <details class="group border-b border-outline-variant/10 pb-4">
                    <summary class="flex justify-between items-center cursor-pointer list-none py-2">
                        <span class="font-label-sm text-label-sm uppercase tracking-widest">Livraison & Retours</span>
                        <span class="material-symbols-outlined transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    <p class="font-body-md text-body-md text-on-surface-variant pt-4 leading-relaxed">
                        Livraison dans toute l'Algérie. Paiement à la livraison disponible. Délai estimé: 2–5 jours ouvrés selon la wilaya.
                    </p>
                </details>
            </div>
        </div>
    </section>

    
    <?php if (!empty($relatedProducts)): ?>
    <section class="mt-24">
        <div class="flex justify-between items-end mb-10">
            <h2 class="font-headline-md text-headline-md">Vous aimerez aussi</h2>
            <a href="shop.php?category=<?php echo urlencode($product['category']); ?>" class="font-label-sm text-label-sm text-primary uppercase tracking-widest border-b border-primary pb-1 hover:text-primary-fixed transition-colors">Voir tout</a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-gutter">
            <?php foreach ($relatedProducts as $rel): 
                $relFinalPrice = (int)($rel['price'] - ($rel['price'] * $rel['discount'] / 100));
                $relImg = !empty($rel['base64_image']) ? $rel['base64_image'] : ($rel['image_url'] ?: 'assets/cat_all.png');
            ?>
            <div class="group">
                <div class="aspect-[3/4] overflow-hidden rounded-xl bg-surface-container mb-4 relative">
                    <a href="product.php?id=<?php echo (int)$rel['id']; ?>">
                        <img src="<?php echo sanitize($relImg); ?>" alt="<?php echo sanitize($rel['name']); ?>" 
                             class="w-full h-full object-cover object-top transition-transform duration-500 group-hover:scale-105" loading="lazy">
                    </a>
                    <button class="absolute bottom-4 right-4 bg-surface/80 backdrop-blur w-10 h-10 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="material-symbols-outlined text-sm">add</span>
                    </button>
                </div>
                <h4 class="font-body-md text-on-surface mb-1">
                    <a href="product.php?id=<?php echo (int)$rel['id']; ?>" class="hover:text-primary transition-colors"><?php echo sanitize($rel['name']); ?></a>
                </h4>
                <p class="font-label-sm text-label-sm text-primary"><?php echo format_price($relFinalPrice); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
window.productColors = <?php echo json_encode($colors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
window.colorImages = <?php echo json_encode($colorImages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
window.productSizes = <?php echo json_encode($sizes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<script src="assets/js/product.js"></script>

<?php require_once 'footer.php'; ?>
