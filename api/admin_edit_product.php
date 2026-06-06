<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Modifier le Produit – AURA';
$error = '';
$success = '';
$product = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$productId = (int)$_GET['id'];

$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = TRUE ORDER BY name ASC");
        $categories = $stmt->fetchAll();

        
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement des données.';
    }
}

if (!$product) {
    header("Location: admin.php");
    exit;
}

$existingColors = json_decode($product['colors'] ?? '[]', true);
$existingSizes = json_decode($product['sizes'] ?? '[]', true);
$existingColorImages = json_decode($product['color_images'] ?? '{}', true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $subcategory = sanitize($_POST['subcategory'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
    $description = sanitize($_POST['description'] ?? '');

    
    $colors = $_POST['colors'] ?? '[]';
    $sizes = $_POST['sizes'] ?? '[]';
    $color_images_raw = $_POST['color_images'] ?? '{}';
    $base64_image = $_POST['base64_image'] ?? '';

    
    if (empty($name) || empty($category) || $price <= 0) {
        $error = 'Le nom, la catégorie et le prix sont obligatoires.';
    } else {
        try {
            
            
            $newColorImages = json_decode($color_images_raw, true);
            if (!is_array($newColorImages)) {
                $newColorImages = [];
            }
            
            $mergedColorImages = $existingColorImages;
            foreach ($newColorImages as $colorName => $imagesList) {
                if (!empty($imagesList)) {
                    $mergedColorImages[$colorName] = $imagesList; 
                }
            }

            
            $finalBase64 = !empty($base64_image) ? $base64_image : $product['base64_image'];

            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, category = ?, subcategory = ?, price = ?, discount = ?, stock = ?, 
                    is_featured = ?, is_new_arrival = ?, description = ?, colors = ?::jsonb, 
                    sizes = ?::jsonb, color_images = ?::jsonb, base64_image = ?, image_url = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $name, $category, $subcategory, $price, $discount, $stock, 
                $is_featured ? 'true' : 'false', $is_new_arrival ? 'true' : 'false', 
                $description, $colors, $sizes, json_encode($mergedColorImages), $finalBase64, $finalBase64,
                $productId
            ]);

            $success = 'Produit mis à jour avec succès !';
            
            
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            $existingColors = json_decode($product['colors'] ?? '[]', true);
            $existingSizes = json_decode($product['sizes'] ?? '[]', true);
            $existingColorImages = json_decode($product['color_images'] ?? '{}', true);
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Modifier le Produit</h1>
            <p class="font-body-md text-on-surface-variant">Modifiez la pièce #<?php echo $product['id']; ?> dans le catalogue.</p>
        </div>
        <a href="admin.php" class="bg-surface-container border border-outline-variant/20 text-on-surface px-5 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-2 hover:bg-surface-container/80 transition-colors">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Retour Dashboard
        </a>
    </div>

    <?php if ($error): ?>
    <div class="bg-error-container text-on-error-container px-6 py-4 rounded-lg mb-8 font-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-xl">error</span>
        <?php echo sanitize($error); ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="bg-primary-container text-on-primary-container px-6 py-4 rounded-lg mb-8 font-body-md flex items-center gap-3">
        <span class="material-symbols-outlined text-xl">check_circle</span>
        <?php echo sanitize($success); ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="product-form" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Hidden elements populated by JS -->
        <input type="hidden" id="colors-input" name="colors">
        <input type="hidden" id="color-images-input" name="color_images">
        <input type="hidden" id="sizes-input" name="sizes">
        <input type="hidden" id="base64-image-input" name="base64_image">

        <!-- Form fields (2 cols) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Basic Details -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm space-y-6">
                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">info</span>
                    Informations Générales
                </h2>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Nom du Produit</label>
                    <input type="text" name="name" required placeholder="ex: Manteau Aura en Laine Mérinos"
                           value="<?php echo sanitize($product['name'] ?? ''); ?>"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Catégorie</label>
                        <select name="category" required
                                class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                            <option value="">Sélectionner...</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo sanitize($cat['slug']); ?>" <?php echo ($product['category'] === $cat['slug']) ? 'selected' : ''; ?>>
                                <?php echo sanitize($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Sous-catégorie (Optionnel)</label>
                        <input type="text" name="subcategory" placeholder="ex: Outerwear, Tricot"
                               value="<?php echo sanitize($product['subcategory'] ?? ''); ?>"
                               class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Description</label>
                    <textarea name="description" rows="5" placeholder="Description détaillée du produit..."
                              class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors"><?php echo sanitize($product['description'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Colors Swatches and Color Images -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">palette</span>
                        Variantes de Couleurs
                    </h2>
                    <button type="button" onclick="addColorBlock()" class="bg-primary/10 text-primary px-4 py-2 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-2 hover:bg-primary/20 transition-all">
                        <span class="material-symbols-outlined text-sm font-bold">add</span>
                        Ajouter Couleur
                    </button>
                </div>

                <!-- Color blocks container -->
                <div id="color-blocks" class="space-y-6">
                    <?php
                    
                    $idx = 0;
                    foreach ($existingColors as $color):
                        $idx++;
                        $colorName = $color['name'];
                        $colorHex = $color['hex'] ?? '#b2cdbc';
                        $imgs = $existingColorImages[$colorName] ?? [];
                    ?>
                    <div class="color-block border border-outline-variant/20 rounded-xl p-6 space-y-4 bg-surface-container relative" data-idx="<?php echo $idx; ?>">
                        <button type="button" onclick="removeColorBlock(this)" class="absolute top-4 right-4 text-error hover:text-on-error-container transition-colors material-symbols-outlined text-xl">close</button>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant block mb-2">Nom de couleur / Color Name</label>
                                <input type="text" name="color_name_<?php echo $idx; ?>" value="<?php echo sanitize($colorName); ?>" placeholder="ex: Sage Green" 
                                    class="w-full bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 text-on-surface font-body-md focus:border-primary focus:ring-0 focus:outline-none" required>
                            </div>
                            <div>
                                <label class="font-label-sm text-label-sm uppercase text-on-surface-variant block mb-2">Couleur Hex / Hex Color</label>
                                <div class="flex items-center gap-3">
                                    <input type="color" name="color_hex_<?php echo $idx; ?>" value="<?php echo sanitize($colorHex); ?>" 
                                        class="w-12 h-12 rounded-lg border border-outline-variant/20 bg-transparent cursor-pointer">
                                    <input type="text" name="color_hex_text_<?php echo $idx; ?>" value="<?php echo sanitize($colorHex); ?>" placeholder="#b2cdbc"
                                        class="flex-1 bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 text-on-surface font-body-md focus:border-primary focus:ring-0 focus:outline-none">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="font-label-sm text-label-sm uppercase text-on-surface-variant block mb-2">Images pour cette couleur / Images for this color</label>
                            <div class="border-2 border-dashed border-outline-variant/30 rounded-xl p-6 text-center hover:border-primary transition-colors cursor-pointer" 
                                onclick="document.getElementById('color-image-input-<?php echo $idx; ?>').click()">
                                <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-2 block">add_photo_alternate</span>
                                <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">Cliquez pour ajouter de nouvelles images</p>
                            </div>
                            <input type="file" id="color-image-input-<?php echo $idx; ?>" multiple accept="image/*" 
                                class="hidden" onchange="handleColorImages(event, <?php echo $idx; ?>)">
                            
                            <!-- Display existing thumbnails -->
                            <div id="color-thumbnails-<?php echo $idx; ?>" class="flex flex-wrap gap-3 mt-4">
                                <?php foreach ($imgs as $imgBase64): ?>
                                <div class="relative w-20 h-20 rounded-lg overflow-hidden border border-outline-variant/20">
                                    <img src="<?php echo $imgBase64; ?>" class="w-full h-full object-cover">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar options (1 col) -->
        <div class="space-y-8">
            <!-- Inventory & Price -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm space-y-6">
                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">sell</span>
                    Tarification & Stock
                </h2>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Prix (DA)</label>
                    <input type="number" name="price" required min="1" placeholder="12500"
                           value="<?php echo sanitize($product['price'] ?? ''); ?>"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Remise (%)</label>
                    <input type="number" name="discount" min="0" max="100" placeholder="0"
                           value="<?php echo sanitize($product['discount'] ?? '0'); ?>"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Quantité en Stock</label>
                    <input type="number" name="stock" required min="0" placeholder="50"
                           value="<?php echo sanitize($product['stock'] ?? '10'); ?>"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>
            </div>

            <!-- Image Principale & Sizes -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm space-y-6">
                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">image</span>
                    Image Principale
                </h2>

                <div class="flex flex-col gap-2">
                    <div class="border-2 border-dashed border-outline-variant/30 rounded-xl p-6 text-center hover:border-primary transition-colors cursor-pointer"
                         onclick="document.getElementById('main-image-file').click()">
                        <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-2 block">cloud_upload</span>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase">Remplacer l'Image Principale</p>
                    </div>
                    <input type="file" id="main-image-file" accept="image/*" class="hidden" onchange="handleMainImage(event)">
                    
                    <!-- Preview -->
                    <?php
                    $mainImgSrc = !empty($product['base64_image']) ? $product['base64_image'] : ($product['image_url'] ?: '');
                    ?>
                    <div id="main-image-preview-container" class="<?php echo empty($mainImgSrc) ? 'hidden' : ''; ?> relative mt-4 aspect-[3/4] rounded-lg overflow-hidden border border-outline-variant/20 bg-surface">
                        <img id="main-image-preview" src="<?php echo $mainImgSrc; ?>" class="w-full h-full object-cover">
                    </div>
                </div>

                <h2 class="font-headline-md text-headline-md text-on-surface flex items-center gap-2 pt-4 border-t border-outline-variant/10">
                    <span class="material-symbols-outlined text-primary">aspect_ratio</span>
                    Tailles Disponibles
                </h2>
                <div class="grid grid-cols-3 gap-3">
                    <?php
                    $availableSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'TU'];
                    foreach ($availableSizes as $sz):
                        $checked = in_array($sz, $existingSizes) ? 'checked' : '';
                    ?>
                    <label class="flex items-center justify-center border border-outline-variant/20 rounded-lg p-3 hover:bg-surface-container-high cursor-pointer transition-colors">
                        <input type="checkbox" name="size[]" value="<?php echo $sz; ?>" <?php echo $checked; ?> class="hidden peer">
                        <span class="font-label-sm text-label-sm text-on-surface peer-checked:text-primary peer-checked:font-bold"><?php echo $sz; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Visibility Settings -->
            <div class="bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline-variant/30 text-primary focus:ring-0 bg-surface">
                    <span class="font-body-md text-on-surface">Mettre en Vedette (Featured)</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_new_arrival" <?php echo $product['is_new_arrival'] ? 'checked' : ''; ?> class="w-5 h-5 rounded border-outline-variant/30 text-primary focus:ring-0 bg-surface">
                    <span class="font-body-md text-on-surface">Nouvelle Arrivée (New Arrival)</span>
                </label>

                <button type="submit" class="w-full bg-primary text-on-primary py-4 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-all duration-300 rounded-lg hover:scale-[1.02] active:scale-95 mt-6">
                    Mettre à jour le Produit
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Define global index before loading js -->
<script>
window.colorIndex = <?php echo $idx; ?>;
// Initialize files list with existing images so they aren't lost
window.colorBlocks = {};
<?php
$idxJS = 0;
foreach ($existingColors as $color):
    $idxJS++;
    $colorName = $color['name'];
    $imgs = $existingColorImages[$colorName] ?? [];
?>
window.colorBlocks[<?php echo $idxJS; ?>] = {
    files: <?php echo json_encode($imgs); ?>
};
<?php endforeach; ?>
</script>

<!-- Load scripts -->
<script src="assets/js/admin-product.js"></script>

<style>
/* CSS Peer Styling for Size Buttons */
input[type="checkbox"]:checked + span {
    color: var(--md-sys-color-primary, #b2cdbc);
    font-weight: 700;
}
input[type="checkbox"]:checked ~ parent, 
input[type="checkbox"]:checked {
    accent-color: var(--md-sys-color-primary, #b2cdbc);
}
</style>

<?php require_once 'footer.php'; ?>
