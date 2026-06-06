<?php
require_once 'config.php';
$auth = requireAdmin();

$pageTitle = 'Gestion des Catégories – AURA';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'create') {
        $name = sanitize($_POST['name'] ?? '');
        $slug = sanitize($_POST['slug'] ?? '');
        $image_url = sanitize($_POST['image_url'] ?? '');

        if ($name && $slug) {
            
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $slug));
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, image_url, is_active) VALUES (?, ?, ?, TRUE)");
                $stmt->execute([$name, $slug, $image_url ?: '/assets/cat_all.png']);
                $success = 'Catégorie créée avec succès.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de la création : ' . $e->getMessage();
            }
        } else {
            $error = 'Le nom et le slug sont obligatoires.';
        }
    } elseif ($action === 'delete') {
        $categoryId = (int)$_POST['category_id'];
        
        if ($categoryId && $pdo) {
            try {
                
                $stmt = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
                $stmt->execute([$categoryId]);
                $cat = $stmt->fetch();
                
                if ($cat) {
                    $slug = $cat['slug'];
                    
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category = ?");
                    $stmt->execute([$slug]);
                    $prodCount = (int)$stmt->fetchColumn();

                    if ($prodCount > 0) {
                        $error = 'Impossible de supprimer cette catégorie car elle contient ' . $prodCount . ' produit(s).';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                        $stmt->execute([$categoryId]);
                        $success = 'Catégorie supprimée avec succès.';
                    }
                } else {
                    $error = 'Catégorie introuvable.';
                }
            } catch (PDOException $e) {
                $error = 'Erreur lors de la suppression : ' . $e->getMessage();
            }
        }
    }
}

$categories = [];
if ($pdo) {
    try {
        
        $stmt = $pdo->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM products WHERE category = c.slug) as products_count 
            FROM categories c 
            ORDER BY c.name ASC
        ");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = 'Erreur lors du chargement des catégories : ' . $e->getMessage();
    }
}

require_once 'header.php';
?>

<div class="min-h-screen py-24 px-margin-mobile max-w-container-max mx-auto">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">Gestion des Catégories</h1>
            <p class="font-body-md text-on-surface-variant">Gérez les rayons et classifications de votre boutique.</p>
        </div>
        <a href="admin.php" class="bg-surface-container border border-outline-variant/20 text-on-surface px-5 py-3 rounded-lg font-label-sm text-label-sm uppercase tracking-wider flex items-center gap-2 hover:bg-surface-container/80 transition-colors">
            <span class="material-symbols-outlined text-lg">arrow_back</span>
            Retour Dashboard
        </a>
    </div>

    <!-- Admin Sub-Navigation tabs -->
    <div class="flex flex-wrap gap-2 mb-10 border-b border-outline-variant/10 pb-4">
        <a href="admin.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Tableau de bord</a>
        <a href="admin_orders.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Commandes</a>
        <a href="admin_customers.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Clients</a>
        <a href="admin_categories.php" class="px-4 py-2.5 rounded-lg bg-surface-container text-primary font-bold font-label-sm text-label-sm uppercase tracking-wider">Catégories</a>
        <a href="admin_promo.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Codes Promo</a>
        <a href="admin_settings.php" class="px-4 py-2.5 rounded-lg text-on-surface-variant hover:bg-surface-container/50 font-label-sm text-label-sm uppercase tracking-wider">Paramètres</a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Category Form -->
        <div class="lg:col-span-1 bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm h-fit">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_circle</span>
                Ajouter une Catégorie
            </h2>
            
            <form method="POST" class="space-y-5">
                <input type="hidden" name="action" value="create">
                
                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Nom de la Catégorie</label>
                    <input type="text" name="name" required placeholder="ex: Nouveautés"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Slug (Identifiant URL)</label>
                    <input type="text" name="slug" required placeholder="ex: new-arrivals"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-label-sm text-label-sm uppercase text-on-surface-variant opacity-75">Image URL (Optionnel)</label>
                    <input type="text" name="image_url" placeholder="/assets/cat_all.png"
                           class="bg-surface border border-outline-variant/20 rounded-lg px-4 py-3 font-body-md text-on-surface focus:border-primary focus:ring-0 focus:outline-none transition-colors">
                </div>

                <button type="submit" class="w-full bg-primary text-on-primary py-3 font-label-sm text-label-sm uppercase tracking-widest hover:bg-primary-fixed transition-all rounded-lg hover:scale-102">
                    Créer la Catégorie
                </button>
            </form>
        </div>

        <!-- Categories List (2 cols) -->
        <div class="lg:col-span-2 bg-surface-container border border-outline-variant/10 rounded-xl p-8 shadow-sm">
            <h2 class="font-headline-md text-headline-md text-on-surface mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">list</span>
                Liste des Catégories
            </h2>

            <?php if (empty($categories)): ?>
            <p class="font-body-md text-on-surface-variant text-center py-10">Aucune catégorie enregistrée.</p>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left font-body-md">
                    <thead>
                        <tr class="border-b border-outline-variant/20 text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                            <th class="pb-4">Nom</th>
                            <th class="pb-4">Slug</th>
                            <th class="pb-4">Image</th>
                            <th class="pb-4">Produits</th>
                            <th class="pb-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        <?php foreach ($categories as $cat): ?>
                        <tr class="text-on-surface">
                            <td class="py-4 font-semibold"><?php echo sanitize($cat['name']); ?></td>
                            <td class="py-4 font-mono text-sm"><?php echo sanitize($cat['slug']); ?></td>
                            <td class="py-4">
                                <img src="<?php echo sanitize($cat['image_url']); ?>" alt="" class="w-10 h-10 object-cover rounded bg-surface border border-outline-variant/20">
                            </td>
                            <td class="py-4"><?php echo $cat['products_count']; ?></td>
                            <td class="py-4 text-right">
                                <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="text-error hover:underline font-label-sm text-label-sm uppercase tracking-wider <?php echo $cat['products_count'] > 0 ? 'opacity-30 cursor-not-allowed' : ''; ?>" <?php echo $cat['products_count'] > 0 ? 'disabled' : ''; ?>>
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
