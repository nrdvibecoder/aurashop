<?php
require_once 'config.php';
$pageTitle = __('shop') . ' – AURA';

$category = sanitize($_GET['category'] ?? '');
$q = sanitize($_GET['q'] ?? '');
$sort = sanitize($_GET['sort'] ?? 'newest');
$filterSize = sanitize($_GET['size'] ?? '');
$minPrice = (int)($_GET['min_price'] ?? 0);
$maxPrice = (int)($_GET['max_price'] ?? 999999);
$filterTag = sanitize($_GET['filter'] ?? '');

require_once 'header.php';

$categoryImages = [
    'Women' => 'assets/cat_women.png',
    'Men' => 'assets/cat_men.png',
    'Accessories' => 'assets/cat_accessories.png',
    'Unisex' => 'assets/cat_unisex.png',
];

$products = [];
$error = null;

if ($pdo) {
    try {
        $conditions = ['p.price >= ?', 'p.price <= ?'];
        $params = [$minPrice, $maxPrice];

        if ($category) {
            $conditions[] = 'LOWER(c.name) = LOWER(?)';
            $params[] = $category;
        }

        if ($q) {
            $conditions[] = "(p.name ILIKE ? OR p.description ILIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        if ($filterSize) {
            $conditions[] = "p.sizes::jsonb ? ?";
            $params[] = $filterSize;
        }

        if ($filterTag === 'new') {
            $conditions[] = 'p.is_new_arrival = TRUE';
        } elseif ($filterTag === 'featured') {
            $conditions[] = 'p.is_featured = TRUE';
        }

        $orderBy = match($sort) {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            default      => 'p.id DESC',
        };

        $where = implode(' AND ', $conditions);
        $sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $where ORDER BY $orderBy";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
} else {
    $error = $pdoError ?? 'Database not connected.';
}

$hasBanner = $category && isset($categoryImages[$category]);
?>

<!-- Category Banner -->
<?php if ($hasBanner): ?>
<div class="relative h-64 md:h-80 overflow-hidden -mt-0 mb-0">
    <img src="<?php echo $categoryImages[$category]; ?>" alt="<?php echo sanitize($category); ?>" class="w-full h-full object-cover brightness-50">
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
        <span class="font-label-sm text-label-sm uppercase tracking-widest text-primary mb-3">AURA</span>
        <h1 class="font-headline-lg text-headline-lg md:text-display-lg-mobile text-on-surface uppercase tracking-wider">
            <?php echo sanitize($category === 'Women' ? __('women') : ($category === 'Men' ? __('men') : ($category === 'Accessories' ? __('accessories') : __('unisex')))); ?>
        </h1>
    </div>
</div>
<?php else: ?>
<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-8 pb-4">
    <h1 class="font-headline-lg text-headline-lg text-on-surface">
        <?php echo $q ? 'Résultats: ' . sanitize($q) : __('shop'); ?>
    </h1>
</div>
<?php endif; ?>

<div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-10">
    <?php if ($error): ?>
    <div class="bg-error-container text-on-error-container p-6 rounded-xl mb-8 font-body-md">
        Erreur de base de données. Veuillez réessayer plus tard.
    </div>
    <?php endif; ?>

    <div class="flex flex-col lg:flex-row gap-10">
        
        <!-- ═══════════ SIDEBAR FILTERS ═══════════ -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <form action="shop.php" method="GET" id="filter-form">
                <?php if ($category): ?>
                <input type="hidden" name="category" value="<?php echo sanitize($category); ?>">
                <?php endif; ?>
                <?php if ($q): ?>
                <input type="hidden" name="q" value="<?php echo sanitize($q); ?>">
                <?php endif; ?>
                
                <div class="bg-surface-container rounded-xl p-6 space-y-8">
                    <!-- Sort -->
                    <div>
                        <h3 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface mb-4"><?php echo __('sort'); ?></h3>
                        <div class="space-y-2">
                            <?php foreach (['newest' => 'Plus récent', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant'] as $val => $label): ?>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="sort" value="<?php echo $val; ?>" <?php echo $sort === $val ? 'checked' : ''; ?> class="text-primary focus:ring-primary">
                                <span class="font-body-md text-on-surface-variant group-hover:text-primary transition-colors"><?php echo $label; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sizes -->
                    <div>
                        <h3 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface mb-4"><?php echo __('size'); ?></h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach (['XS','S','M','L','XL','XXL'] as $s): ?>
                            <label class="cursor-pointer">
                                <input type="radio" name="size" value="<?php echo $s; ?>" <?php echo $filterSize === $s ? 'checked' : ''; ?> class="sr-only peer">
                                <span class="inline-block px-3 py-1.5 border font-label-sm text-label-sm rounded-lg transition-all peer-checked:border-primary peer-checked:text-primary peer-checked:bg-primary/10 border-outline-variant/30 text-on-surface-variant hover:border-primary hover:text-primary">
                                    <?php echo $s; ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <h3 class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface mb-4">Prix (DA)</h3>
                        <div class="flex gap-3 items-center">
                            <input type="number" name="min_price" value="<?php echo $minPrice ?: ''; ?>" placeholder="Min" min="0"
                                class="w-full bg-surface border border-outline-variant/20 rounded-lg px-3 py-2 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none text-sm">
                            <span class="text-outline">–</span>
                            <input type="number" name="max_price" value="<?php echo $maxPrice < 999999 ? $maxPrice : ''; ?>" placeholder="Max" min="0"
                                class="w-full bg-surface border border-outline-variant/20 rounded-lg px-3 py-2 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none text-sm">
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-primary text-on-primary font-label-sm text-label-sm uppercase py-3 rounded-lg hover:bg-primary-fixed transition-colors tracking-wider">
                            Filtrer
                        </button>
                        <a href="shop.php<?php echo $category ? '?category=' . urlencode($category) : ''; ?>" 
                           class="flex-1 border border-outline-variant/30 text-on-surface-variant font-label-sm text-label-sm uppercase py-3 rounded-lg hover:border-primary hover:text-primary transition-colors text-center tracking-wider">
                            Effacer
                        </a>
                    </div>
                </div>
            </form>
        </aside>

        <!-- ═══════════ PRODUCT GRID ═══════════ -->
        <main class="flex-1">
            <?php if (empty($products) && !$error): ?>
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <span class="material-symbols-outlined text-6xl text-outline mb-4">search_off</span>
                <h2 class="font-headline-md text-headline-md text-on-surface mb-3"><?php echo __('no_products'); ?></h2>
                <p class="text-on-surface-variant font-body-md mb-8">Essayez d'autres filtres ou catégories.</p>
                <a href="shop.php" class="bg-primary text-on-primary px-8 py-3 rounded-full font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-colors">
                    <?php echo __('all'); ?>
                </a>
            </div>
            <?php else: ?>
            <p class="font-label-sm text-label-sm text-outline uppercase mb-8"><?php echo count($products); ?> produit<?php echo count($products) > 1 ? 's' : ''; ?></p>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-gutter">
                <?php foreach ($products as $product): 
                    $finalPrice = (int)($product['price'] - ($product['price'] * $product['discount'] / 100));
                    $imgSrc = !empty($product['base64_image']) ? $product['base64_image'] : ($product['image_url'] ?: 'assets/cat_all.png');
                ?>
                <div class="group">
                    <div class="relative aspect-[3/4] bg-surface-container rounded-lg overflow-hidden mb-4">
                        <a href="product.php?id=<?php echo (int)$product['id']; ?>">
                            <img src="<?php echo sanitize($imgSrc); ?>" 
                                 alt="<?php echo sanitize($product['name']); ?>"
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                 loading="lazy">
                        </a>
                        <?php if ($product['is_new_arrival']): ?>
                        <span class="absolute top-4 left-4 bg-primary text-on-primary font-label-sm text-label-sm uppercase tracking-wider px-3 py-1 rounded-full"><?php echo __('new_label'); ?></span>
                        <?php endif; ?>
                        <?php if ($product['discount'] > 0): ?>
                        <span class="absolute top-4 right-4 bg-error text-on-error font-label-sm text-label-sm px-3 py-1 rounded-full">-<?php echo (int)$product['discount']; ?>%</span>
                        <?php endif; ?>
                        <?php if ($product['stock'] == 0): ?>
                        <div class="absolute inset-0 bg-surface/60 flex items-center justify-center">
                            <span class="font-label-sm text-label-sm uppercase tracking-widest text-on-surface border border-outline-variant px-4 py-2"><?php echo __('out_of_stock'); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="absolute inset-x-0 bottom-0 p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                            <a href="product.php?id=<?php echo (int)$product['id']; ?>" 
                               class="block w-full py-3 bg-primary text-on-primary text-center font-label-sm text-label-sm uppercase tracking-widest rounded-lg hover:bg-primary-fixed transition-colors">
                                <?php echo __('explore'); ?>
                            </a>
                        </div>
                    </div>
                    <span class="text-on-surface-variant font-label-sm text-label-sm uppercase tracking-widest block mb-1"><?php echo sanitize($product['category']); ?></span>
                    <h3 class="font-body-md text-body-md text-on-surface mb-2">
                        <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="hover:text-primary transition-colors"><?php echo sanitize($product['name']); ?></a>
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="text-primary font-body-md font-medium"><?php echo format_price($finalPrice); ?></span>
                        <?php if ($product['discount'] > 0): ?>
                        <span class="text-outline font-label-sm line-through"><?php echo format_price($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once 'footer.php'; ?>
