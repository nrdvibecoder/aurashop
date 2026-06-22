# AURA SHOP v2.0
### Plateforme E-Commerce Algérienne — Développée en PHP / PostgreSQL / JavaScript

> **Aura Shop** est une boutique en ligne complète et bilingue (Français / Anglais) ciblant le marché algérien. Elle permet la vente de vêtements en ligne avec un panneau d'administration complet, un système de livraison couvrant les 69 wilayas, et un tunnel de commande en plusieurs étapes.

---

## Table des Matières

1. [Présentation Générale](#1-présentation-générale)
2. [Stack Technique — Toutes les Technologies Utilisées](#2-stack-technique--toutes-les-technologies-utilisées)
3. [Architecture Globale du Projet](#3-architecture-globale-du-projet)
4. [Structure des Fichiers](#4-structure-des-fichiers)
5. [La Base de Données — schema.sql](#5-la-base-de-données--schemasql)
6. [Le Fichier Central — config.php](#6-le-fichier-central--configphp)
7. [Le Système de Traduction — lang.php](#7-le-système-de-traduction--langphp)
8. [Les Pages Publiques (Client)](#8-les-pages-publiques-client)
9. [Le Système de Panier — cart.js](#9-le-système-de-panier--cartjs)
10. [La Page Produit — product.js](#10-la-page-produit--productjs)
11. [Le Tunnel de Commande — checkout.js](#11-le-tunnel-de-commande--checkoutjs)
12. [Le Panneau d'Administration](#12-le-panneau-dadministration)
13. [Gestion des Images — admin-product.js](#13-gestion-des-images--admin-productjs)
14. [Le Système d'Authentification](#14-le-système-dauthentification)
15. [Le Système de Codes Promo](#15-le-système-de-codes-promo)
16. [Les Zones de Livraison (69 Wilayas)](#16-les-zones-de-livraison-69-wilayas)
17. [L'Internationalisation (Bilingue FR/EN)](#17-linternationalisation-bilingue-fren)
18. [La Configuration Vercel — vercel.json](#18-la-configuration-vercel--verceljson)
19. [Sécurité](#19-sécurité)
20. [Déploiement pas à pas](#20-déploiement-pas-à-pas)

---

## 1. Présentation Générale

**Aura Shop** est une application web e-commerce pensée pour le marché algérien. Voici ses grandes fonctionnalités :

| Côté Client | Côté Administrateur |
|---|---|
| Catalogue produits avec filtres et recherche | Tableau de bord avec statistiques |
| Fiche produit avec galerie couleurs/tailles | Gestion des produits (ajout, modification) |
| Panier persistant sans connexion | Gestion des commandes et statuts |
| Tunnel de commande en 3 étapes | Gestion des clients et bannissement |
| Paiement à la livraison (COD) | Codes promo (montant fixe ou pourcentage) |
| 69 wilayas avec frais de livraison | Paramétrage des frais par wilaya |
| Bilingue Français / Anglais | Gestion des catégories |
| Pages À Propos et Contact | — |
| Inscription et espace client | — |

---

## 2. Stack Technique — Toutes les Technologies Utilisées

| Couche | Technologie | Rôle |
|---|---|---|
| **Backend** | PHP 8.x | Génération des pages, logique métier |
| **Base de données** | PostgreSQL 15 (Supabase) | Stockage de toutes les données |
| **Pilote DB** | PDO (PHP Data Objects) | Communication sécurisée PHP ↔ PostgreSQL |
| **Frontend** | HTML5 sémantique | Structure des pages |
| **Styles** | Tailwind CSS (via CDN) | Classes utilitaires pour le design responsive |
| **Icônes** | Google Material Symbols | Icônes vectorielles (panier, profil, etc.) |
| **Polices** | Google Fonts — Inter | Typographie premium |
| **Interactivité** | JavaScript Vanilla (ES6+) | Panier, formulaires dynamiques, galerie |
| **Hébergement** | Vercel (Serverless PHP) | Déploiement cloud gratuit |
| **Stockage images** | Base64 dans PostgreSQL | Pas de service externe nécessaire |
| **Sessions** | Cookies HTTP | Authentification, panier, langue, promo |

---

## 3. Architecture Globale du Projet

Voici comment une requête d'un visiteur est traitée de bout en bout :

```
┌─────────────────┐
│  Navigateur     │  → L'utilisateur tape l'URL (ex: aurashop.vercel.app/shop)
└────────┬────────┘
         │ Requête HTTP GET
         ▼
┌─────────────────┐
│  Réseau Vercel  │  → Le réseau edge de Vercel reçoit la requête
└────────┬────────┘
         │ Correspond à une route dans vercel.json
         ▼
┌─────────────────────────────┐
│  Fonction Serverless        │  → ex: api/shop.php est exécuté comme une
│  api/shop.php               │    fonction Lambda isolée
└────────┬────────────────────┘
         │ require_once 'config.php'  ← PREMIÈRE LIGNE de tout fichier PHP
         ▼
┌─────────────────────────────┐
│  config.php                 │
│  ├─ Connexion PDO→PostgreSQL│  → Lit DATABASE_URL depuis les variables Vercel
│  ├─ Définit les fonctions   │  → get_auth(), get_cart(), format_price()...
│  └─ Charge lang.php         │  → Définit $translations et la fonction __()
└────────┬────────────────────┘
         │ Requêtes SQL préparées
         ▼
┌─────────────────────────────┐
│  PostgreSQL (Supabase)      │  → Exécute les requêtes, renvoie les données
└────────┬────────────────────┘
         │ Données PHP (tableaux associatifs)
         ▼
┌─────────────────────────────┐
│  PHP génère le HTML         │  → Mélange PHP + HTML + classes Tailwind
│  + header.php + footer.php  │
└────────┬────────────────────┘
         │ Réponse HTML complète
         ▼
┌─────────────────┐
│  Navigateur     │  → Affiche la page, charge les fichiers JS
│  + cart.js      │  → Gère le panier en cookies
│  + product.js   │  → Gère la galerie et les sélecteurs
│  + checkout.js  │  → Gère le formulaire multi-étapes
└─────────────────┘
```

> **Point clé — Architecture Serverless :** Sur Vercel, chaque fichier PHP est une fonction indépendante. Il n'y a **pas de mémoire partagée** entre deux requêtes. C'est pourquoi le panier, la connexion et les préférences sont stockés dans des **cookies** plutôt que dans des sessions serveur traditionnelles.

---

## 4. Structure des Fichiers

```
aurashop-main/
│
├── vercel.json              ← Règles de routage et runtime PHP
├── schema.sql               ← Tout le schéma PostgreSQL + données initiales
├── README.md                ← Ce fichier
│
├── api/                     ← Tous les fichiers PHP (fonctions serverless)
│   │
│   ├── config.php           ← Fichier central : DB, fonctions, i18n
│   ├── lang.php             ← Toutes les traductions FR/EN
│   ├── header.php           ← Barre de navigation (incluse sur chaque page)
│   ├── footer.php           ← Pied de page + newsletter
│   │
│   ├── ── Pages Client ──
│   ├── index.php            ← Page d'accueil (hero, produits vedettes)
│   ├── shop.php             ← Catalogue avec filtres
│   ├── product.php          ← Fiche produit (couleurs, tailles, galerie)
│   ├── cart.php             ← Page panier + code promo
│   ├── checkout.php         ← Commande en 3 étapes
│   ├── confirmation.php     ← Page de succès après commande
│   ├── about.php            ← Page À Propos
│   ├── contact.php          ← Formulaire de contact
│   │
│   ├── ── Authentification ──
│   ├── login.php            ← Connexion (clients + admins)
│   ├── register.php         ← Inscription client
│   ├── logout.php           ← Déconnexion (efface le cookie)
│   ├── settings.php         ← Espace client (profil, commandes)
│   ├── set_lang.php         ← Changer la langue (cookie)
│   │
│   ├── ── Administration ──
│   ├── admin.php                ← Tableau de bord
│   ├── admin_orders.php         ← Liste des commandes
│   ├── admin_order_detail.php   ← Détail + changement de statut
│   ├── admin_add_product.php    ← Formulaire ajout produit
│   ├── admin_edit_product.php   ← Formulaire modification produit
│   ├── admin_categories.php     ← Gestion des catégories
│   ├── admin_customers.php      ← Gestion des clients
│   ├── admin_promo.php          ← Gestion des codes promo
│   ├── admin_settings.php       ← Frais de livraison par wilaya
│   └── create_admin.php         ← Outil unique de création du premier admin
│
└── assets/
    ├── favicon.png
    ├── hero1.png / hero2.png / hero3.png      ← Images du carrousel (desktop)
    ├── hero10.png                              ← Image mobile du carrousel
    ├── cat_women.png / cat_men.png / ...      ← Images des cartes catégories
    └── js/
        ├── cart.js             ← Toute la logique du panier (cookies)
        ├── product.js          ← Galerie, sélecteurs couleur/taille
        ├── checkout.js         ← Navigation multi-étapes + calcul livraison
        └── admin-product.js    ← Gestion couleurs/images en admin
```

---

## 5. La Base de Données — schema.sql

Le fichier `schema.sql` contient l'intégralité du schéma de la base de données. Il est **idempotent** (peut être exécuté plusieurs fois sans risque grâce aux clauses `IF NOT EXISTS` et `ON CONFLICT`).

### Tables créées

#### Table `users` — Les comptes utilisateurs
```sql
CREATE TABLE IF NOT EXISTS users (
    id            SERIAL PRIMARY KEY,       -- Identifiant unique auto-incrémenté
    fullname      VARCHAR(255) NOT NULL,    -- Prénom + Nom de l'utilisateur
    email         VARCHAR(255) UNIQUE NOT NULL, -- Email de connexion (unique)
    password_hash VARCHAR(255) NOT NULL,    -- Mot de passe chiffré avec bcrypt
    role          VARCHAR(50) DEFAULT 'customer', -- 'customer' ou 'admin'
    is_banned     BOOLEAN DEFAULT FALSE,    -- Si TRUE → connexion bloquée
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
> Explication : Un seul `role` détermine si l'utilisateur est client ou admin. Les admins ont accès à tout le panneau `/admin*`. Le champ `is_banned` permet à un admin de bloquer un compte sans le supprimer.

#### Table `products` — Le catalogue
```sql
CREATE TABLE IF NOT EXISTS products (
    id           SERIAL PRIMARY KEY,
    name         VARCHAR(255) NOT NULL,
    category     VARCHAR(100) NOT NULL,    -- Correspond à categories.name
    subcategory  VARCHAR(100),             -- Sous-libellé libre (optionnel)
    description  TEXT,
    price        INT NOT NULL,             -- Prix entier en Dinars Algériens
    discount     INT DEFAULT 0,            -- Remise en pourcentage (0 à 100)
    stock        INT DEFAULT 0,
    colors       JSONB DEFAULT '[]',       -- Ex: [{"name":"Noir","hex":"#000"}]
    sizes        JSONB DEFAULT '[]',       -- Ex: ["S","M","L","XL"]
    base64_image TEXT,                     -- Image principale encodée en Base64
    color_images JSONB DEFAULT '{}',       -- Ex: {"Noir": ["data:image/jpeg;base64,..."]}
    is_new_arrival BOOLEAN DEFAULT FALSE,
    is_featured    BOOLEAN DEFAULT FALSE,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
> Explication : Les colonnes `colors`, `sizes` et `color_images` utilisent le type **JSONB** de PostgreSQL. Cela permet de stocker des structures de données complexes (tableaux, objets) directement dans la base de données, sans créer de tables supplémentaires.

#### Table `orders` — Les commandes
```sql
CREATE TABLE IF NOT EXISTS orders (
    id              SERIAL PRIMARY KEY,
    order_number    VARCHAR(100) UNIQUE NOT NULL, -- Ex: ORD-20260617-A3F8C1
    user_id         INT REFERENCES users(id) ON DELETE SET NULL,
    fullname        VARCHAR(255) NOT NULL,
    phone           VARCHAR(50) NOT NULL,         -- Format algérien 05/06/07XXXXXXXX
    wilaya          VARCHAR(100) NOT NULL,
    commune         VARCHAR(100) NOT NULL,
    address         TEXT NOT NULL,
    delivery_method VARCHAR(50) NOT NULL,          -- 'home' ou 'relay'
    total_amount    INT NOT NULL,                  -- Total final en DA
    discount_amount INT DEFAULT 0,                 -- Réduction du code promo
    delivery_fee    INT DEFAULT 0,
    promo_code      VARCHAR(100),
    status          VARCHAR(50) DEFAULT 'Pending', -- Pending/Confirmed/Shipped/Delivered/Cancelled
    notes           TEXT,
    order_date      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Table `order_items` — Le détail de chaque commande
```sql
CREATE TABLE IF NOT EXISTS order_items (
    id           SERIAL PRIMARY KEY,
    order_id     INT REFERENCES orders(id) ON DELETE CASCADE,
    product_id   INT REFERENCES products(id) ON DELETE SET NULL,
    product_name VARCHAR(255) NOT NULL,  -- Capture du nom au moment de l'achat
    quantity     INT NOT NULL,
    price        INT NOT NULL,           -- Prix au moment de l'achat (snapshot)
    size         VARCHAR(50),
    color        VARCHAR(100),
    image_url    TEXT
);
```
> Explication : Les données produit (nom, prix, image) sont **copiées** dans `order_items` au moment de la commande. Ainsi, si un produit est modifié ou supprimé, l'historique des commandes reste intact.

#### Table `order_status_history` — L'audit des statuts
```sql
CREATE TABLE IF NOT EXISTS order_status_history (
    id         SERIAL PRIMARY KEY,
    order_id   INT REFERENCES orders(id) ON DELETE CASCADE,
    status     VARCHAR(50) NOT NULL,
    note       TEXT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Table `promo_codes` — Les codes de réduction
```sql
CREATE TABLE IF NOT EXISTS promo_codes (
    id         SERIAL PRIMARY KEY,
    code       VARCHAR(100) UNIQUE NOT NULL,
    type       VARCHAR(50) NOT NULL,   -- 'fixed' (montant en DA) ou 'percentage'
    value      INT NOT NULL,
    min_order  INT DEFAULT 0,          -- Sous-total minimum requis
    max_uses   INT,                    -- NULL = utilisations illimitées
    used_count INT DEFAULT 0,
    expires_at TIMESTAMP,              -- NULL = pas d'expiration
    is_active  BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Table `delivery_zones` — Les 69 wilayas
```sql
CREATE TABLE IF NOT EXISTS delivery_zones (
    id             SERIAL PRIMARY KEY,
    wilaya_code    INT UNIQUE NOT NULL,
    wilaya_name    VARCHAR(100) UNIQUE NOT NULL,
    home_fee       INT NOT NULL,      -- Frais livraison domicile (DA)
    relay_fee      INT NOT NULL,      -- Frais point relais (DA)
    estimated_days INT DEFAULT 3      -- Délai estimé en jours
);
```

#### Données initiales automatiques
Le fichier `schema.sql` insère automatiquement les 4 catégories de base et les 69 wilayas :
```sql
-- 4 catégories par défaut
INSERT INTO categories (name) VALUES ('Women'), ('Men'), ('Accessories'), ('Unisex')
ON CONFLICT (name) DO NOTHING;  -- Si elles existent déjà, ne rien faire

-- 69 wilayas avec leurs tarifs
INSERT INTO delivery_zones (wilaya_code, wilaya_name, home_fee, relay_fee, estimated_days)
VALUES (16, 'Alger', 350, 150, 1), (9, 'Blida', 400, 200, 1), ...
ON CONFLICT (wilaya_code) DO UPDATE SET  -- Si déjà présent, mettre à jour les tarifs
    home_fee = EXCLUDED.home_fee,
    relay_fee = EXCLUDED.relay_fee;
```

---

## 6. Le Fichier Central — config.php

`api/config.php` est inclus en **première ligne** de chaque page PHP (`require_once 'config.php'`). Il fait trois choses essentielles.

### Étape 1 : Connexion à la base de données

```php
// Lire l'URL de connexion depuis la variable d'environnement Vercel
$dbUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);

// Décomposer l'URL postgresql://user:pass@host:port/dbname
$parsedUrl = parse_url($dbUrl);
$host   = $parsedUrl['host'];
$port   = $parsedUrl['port'];
$user   = rawurldecode($parsedUrl['user']);
$pass   = rawurldecode($parsedUrl['pass']);
$dbname = ltrim($parsedUrl['path'], '/');

// Créer la connexion PDO (PHP Data Objects)
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lève une exception en cas d'erreur SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retourne les résultats en tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Utilise les vraies requêtes préparées
]);
```

### Étape 2 : Fonctions utilitaires globales

Toutes les fonctions définies ici sont disponibles sur toutes les pages.

```php
// ── Panier ──
function get_cart() {
    // Lit le cookie 'aura_cart', le décode depuis JSON, retourne un tableau
    if (isset($_COOKIE['aura_cart'])) {
        $cart = json_decode($_COOKIE['aura_cart'], true);
        return is_array($cart) ? $cart : [];
    }
    return [];
}

function save_cart($cart) {
    // Encode le tableau en JSON et l'écrit dans le cookie pour 30 jours
    setcookie('aura_cart', json_encode($cart), time() + (86400 * 30), '/');
}

// ── Authentification ──
function get_auth() {
    // Lit le cookie 'aura_auth', retourne ['id' => 42, 'role' => 'customer'] ou null
    if (isset($_COOKIE['aura_auth'])) {
        $auth = json_decode($_COOKIE['aura_auth'], true);
        return is_array($auth) ? $auth : null;
    }
    return null;
}

function set_auth($id, $role) {
    // Crée le cookie de connexion pour 30 jours
    setcookie('aura_auth', json_encode(['id' => (int)$id, 'role' => $role]), time() + (86400 * 30), '/');
}

function clear_auth() {
    // Efface le cookie (expiration dans le passé)
    setcookie('aura_auth', '', time() - 3600, '/');
}

function requireAuth() {
    // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
    $auth = get_auth();
    if (!$auth) {
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    return $auth;
}

function requireAdmin() {
    // Redirige vers l'accueil si ce n'est pas un admin
    $auth = get_auth();
    if (!$auth || $auth['role'] !== 'admin') {
        header("Location: index.php");
        exit;
    }
    return $auth;
}

// ── Formatage ──
function format_price($amount) {
    return number_format((int)$amount, 0, '.', ' ') . ' DA'; // Ex: "4 500 DA"
}

function generate_order_number() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)); // Ex: "ORD-20260617-A3F8C1"
}

// ── Sécurité ──
function sanitize($v) {
    // Nettoie toute entrée utilisateur avant de l'afficher (protection XSS)
    return htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8');
}
```

### Étape 3 : Chargement de la langue

```php
require_once 'lang.php';                          // Charge le tableau $translations
$language = $_COOKIE['lang'] ?? 'fr';            // Langue par défaut : français
if (!in_array($language, ['en', 'fr'])) {
    $language = 'fr';                             // Sécurité : valeur inconnue → français
}

function __($key) {
    global $translations, $language;
    return $translations[$language][$key] ?? $key; // Retourne la traduction ou la clé si manquante
}
```

---

## 7. Le Système de Traduction — lang.php

`lang.php` définit un grand tableau PHP associatif avec toutes les traductions.

```php
$translations = [
    'fr' => [
        'home'        => 'Accueil',
        'shop'        => 'Boutique',
        'add_to_cart' => 'Ajouter au Panier',
        'pending'     => 'En attente',
        'confirmed'   => 'Confirmée',
        'shipped'     => 'Expédiée',
        'delivered'   => 'Livrée',
        'cancelled'   => 'Annulée',
        // ... 109 clés au total
    ],
    'en' => [
        'home'        => 'Home',
        'shop'        => 'Shop',
        'add_to_cart' => 'Add to Cart',
        'pending'     => 'Pending',
        // ... même structure en anglais
    ]
];
```

**Utilisation dans les pages PHP :**
```php
<!-- Affiche "Boutique" en français, "Shop" en anglais -->
<a href="shop.php"><?= __('shop') ?></a>

<!-- Affiche "Ajouter au Panier" ou "Add to Cart" selon la langue active -->
<button><?= __('add_to_cart') ?></button>
```

**Changement de langue :** Un lien pointe vers `set_lang.php?lang=en`. Ce fichier définit le cookie `lang` et redirige vers la page précédente.

---

## 8. Les Pages Publiques (Client)

### `index.php` — Page d'Accueil

La page d'accueil fait 4 choses principales :
1. **Carrousel Hero** : 3 images défilantes (`hero1.png`, `hero2.png`, `hero3.png`) avec animation CSS automatique.
2. **Raccourcis catégories** : 5 cartes image (Femmes, Hommes, Accessoires, Unisexe, Tout voir) avec liens vers `shop.php?category=...`
3. **Produits vedettes** : Requête SQL `WHERE is_featured = TRUE`
4. **Nouvelles arrivées** : Requête SQL `WHERE is_new_arrival = TRUE`

```php
// Exemple de requête pour les produits vedettes
$stmt = $pdo->prepare("SELECT * FROM products WHERE is_featured = TRUE ORDER BY created_at DESC LIMIT 8");
$stmt->execute();
$featured_products = $stmt->fetchAll();
```

### `shop.php` — La Boutique / Catalogue

Gère les filtres dynamiques passés via l'URL (`GET`) :
```php
// Récupération des filtres depuis l'URL
$category = $_GET['category'] ?? '';     // Ex: ?category=Women
$size      = $_GET['size'] ?? '';        // Ex: ?size=M
$min_price = $_GET['min_price'] ?? 0;
$max_price = $_GET['max_price'] ?? 50000;
$search    = $_GET['search'] ?? '';
$sort      = $_GET['sort'] ?? 'newest';

// Construction dynamique de la requête SQL
$conditions = ["1=1"];  // Toujours vrai (base)
$params     = [];

if ($category) {
    $conditions[] = "category = ?";
    $params[]     = $category;
}
if ($search) {
    $conditions[] = "(name ILIKE ? OR description ILIKE ?)";
    $params[]     = "%$search%";
    $params[]     = "%$search%";
}
if ($size) {
    $conditions[] = "sizes::text ILIKE ?";  // Cherche dans le JSONB converti en texte
    $params[]     = "%\"$size\"%";
}

$conditions[] = "price BETWEEN ? AND ?";
$params[]     = $min_price;
$params[]     = $max_price;

$where = implode(" AND ", $conditions);

// Tri dynamique
$order = match($sort) {
    'price_asc'  => "price ASC",
    'price_desc' => "price DESC",
    default      => "created_at DESC"
};

$stmt = $pdo->prepare("SELECT * FROM products WHERE $where ORDER BY $order");
$stmt->execute($params);
```

### `product.php` — La Fiche Produit

Charge un produit depuis son ID et passe les données au JavaScript via `window.*` :
```php
// Charger le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$_GET['id']]);
$product = $stmt->fetch();

// Décoder le JSONB depuis PostgreSQL
$colors      = json_decode($product['colors'], true) ?? [];
$sizes       = json_decode($product['sizes'], true) ?? [];
$color_images = json_decode($product['color_images'], true) ?? {};
```

Ces données sont ensuite passées à JavaScript pour l'interactivité :
```html
<script>
    window.productColors = <?= json_encode($colors) ?>;
    window.colorImages   = <?= json_encode($color_images) ?>;
    window.productSizes  = <?= json_encode($sizes) ?>;
</script>
```

### `cart.php` — Le Panier

Le panier lit le cookie `aura_cart` côté PHP pour l'affichage :
```php
$cart = get_cart();  // Décode le cookie JSON

// Calculer le sous-total
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Lire le code promo appliqué
$promo = isset($_COOKIE['aura_promo'])
    ? json_decode($_COOKIE['aura_promo'], true)
    : null;

// Calculer la remise
$discount = 0;
if ($promo) {
    if ($promo['type'] === 'percentage') {
        $discount = (int)($subtotal * $promo['value'] / 100);
    } else {
        $discount = $promo['value'];
    }
}
```

### `checkout.php` — La Commande (3 étapes)

Lors de la soumission du formulaire (`POST`), PHP crée la commande en **transaction** :
```php
$pdo->beginTransaction();  // Démarrer la transaction

try {
    // 1. Générer un numéro de commande unique
    $order_number = generate_order_number(); // Ex: "ORD-20260617-A3F8C1"

    // 2. Insérer la commande dans 'orders'
    $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, fullname, phone, wilaya,
        commune, address, delivery_method, total_amount, discount_amount, delivery_fee,
        promo_code, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([...]);
    $order_id = $pdo->lastInsertId();

    // 3. Insérer chaque article dans 'order_items'
    foreach ($cart as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name,
            quantity, price, size, color, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, ...]);

        // 4. Décrémenter le stock (jamais négatif grâce à GREATEST)
        $pdo->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?")
            ->execute([$item['quantity'], $item['product_id']]);
    }

    // 5. Créer la première entrée dans l'historique des statuts
    $pdo->prepare("INSERT INTO order_status_history (order_id, status, note) VALUES (?, 'Pending', ?)")
        ->execute([$order_id, 'Commande créée']);

    // 6. Incrémenter le compteur du code promo si utilisé
    if ($promo_code) {
        $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = ?")
            ->execute([$promo_code]);
    }

    $pdo->commit();  // Valider toutes les écritures en une seule fois

    // 7. Vider le panier et la promo
    setcookie('aura_cart', '', time() - 3600, '/');
    setcookie('aura_promo', '', time() - 3600, '/');

    // 8. Rediriger vers la page de confirmation
    header("Location: confirmation.php?order=" . $order_number);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();  // En cas d'erreur : annuler TOUTES les écritures
    $error = "Erreur lors de la commande : " . $e->getMessage();
}
```

---

## 9. Le Système de Panier — cart.js

`assets/js/cart.js` gère entièrement le panier côté navigateur en utilisant les **cookies**.

### Lire le panier
```javascript
function readCart() {
    // Regex pour extraire le cookie 'aura_cart' depuis document.cookie
    const match = document.cookie.match(/^(?:.*;)?\s*aura_cart\s*=\s*([^;]+)(?:.*)?$/);
    if (match) {
        try {
            const parsed = JSON.parse(decodeURIComponent(match[1]));
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) { return []; }  // Si JSON corrompu → panier vide
    }
    return [];
}
```

### Sauvegarder le panier
```javascript
function saveCart(cart) {
    // Encode le tableau en JSON, puis en URL-encoded, valide 30 jours
    document.cookie = 'aura_cart=' + encodeURIComponent(JSON.stringify(cart))
        + '; path=/; max-age=' + (86400 * 30);
    updateBadge();  // Met à jour le compteur dans le header
}
```

### Ajouter un article
```javascript
function addToCart(product_id, name, color, size, price, image_url, qty) {
    let cart = readCart();
    let existingIndex = cart.findIndex(item =>
        item.product_id === product_id && item.color === color && item.size === size
    );

    let finalImageUrl = image_url;
    if (image_url && image_url.startsWith('data:')) {
        finalImageUrl = '';
    }

    if (existingIndex > -1) {
        cart[existingIndex].quantity += parseInt(qty);
    } else {
        cart.push({
            product_id: parseInt(product_id),
            name: name,
            color: color,
            size: size,
            price: parseInt(price),
            image_url: finalImageUrl,
            quantity: parseInt(qty)
        });
    }
    saveCart(cart);
}
```

### Notification Toast
```javascript
function showToast(message) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = "..."; // Classes Tailwind pour le style
    toast.innerHTML = `<span class="material-symbols-outlined">check_circle</span><span>${message}</span>`;
    container.appendChild(toast);

    // Animation d'apparition (10ms de délai pour déclencher la transition CSS)
    setTimeout(() => toast.classList.remove('translate-y-2', 'opacity-0'), 10);

    // Disparition après 3,5 secondes
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
```

---

## 10. La Page Produit — product.js

`assets/js/product.js` gère toute l'interactivité de la fiche produit.

### Sélection des couleurs (swatches)
```javascript
const colorSwatches = document.querySelectorAll('[data-color-swatch]');

colorSwatches.forEach(swatch => {
    swatch.addEventListener('click', () => {
        const color = swatch.dataset.colorSwatch;  // Ex: "Noir"
        window.selectedColor = color;

        // Retirer le style actif de tous les swatches, l'appliquer au cliqué
        colorSwatches.forEach(s => s.classList.remove('ring-2', 'ring-primary'));
        swatch.classList.add('ring-2', 'ring-primary');

        // Mettre à jour le label "Couleur : Noir"
        document.getElementById('color-label').textContent = color;

        // Changer les images de la galerie
        const images = window.colorImages[color];  // Tableau d'images Base64
        if (images && images.length > 0) {
            document.getElementById('main-product-image').src = images[0];  // Image principale
            // Reconstruire les miniatures
            thumbnailContainer.innerHTML = '';
            images.forEach((imgSrc, idx) => {
                const thumb = document.createElement('button');
                thumb.innerHTML = `<img src="${imgSrc}" class="w-full h-full object-cover">`;
                thumb.addEventListener('click', () => {
                    mainImage.src = imgSrc;  // Clic sur miniature → change l'image principale
                });
                thumbnailContainer.appendChild(thumb);
            });
        }
    });
});
```

### Sélection de la taille
```javascript
const sizeButtons = document.querySelectorAll('[data-size-btn]');

sizeButtons.forEach(btn => {
    if (btn.dataset.disabled === 'true') return;

    btn.addEventListener('click', () => {
        window.selectedSize = btn.dataset.sizeBtn;  // Ex: "M"

        // Retirer le style actif de tous les boutons
        sizeButtons.forEach(b => {
            if (b.dataset.disabled !== 'true') {
                b.classList.remove('border-primary', 'text-primary', 'bg-primary/10');
                b.classList.add('border-outline-variant/30', 'text-on-surface');
            }
        });
        // Appliquer le style au bouton sélectionné
        btn.classList.add('border-primary', 'text-primary', 'bg-primary/10');
    });
});

// Pré-sélection automatique de la première taille disponible au chargement de la page
const firstSizeBtn = document.querySelector('[data-size-btn]:not([data-disabled="true"])');
if (firstSizeBtn) {
    firstSizeBtn.click();
}
```

### Bouton "Ajouter au Panier"
```javascript
addToCartBtn.addEventListener('click', () => {
    // Validation : vérifier qu'une couleur et une taille sont sélectionnées
    if (!window.selectedColor) {
        showInlineError('Veuillez sélectionner une couleur.');
        shakeBtn(addToCartBtn);  // Animation de secousse sur le bouton
        return;
    }
    if (!window.selectedSize) {
        showInlineError('Veuillez sélectionner une taille.');
        shakeBtn(addToCartBtn);
        return;
    }

    // Récupérer les données depuis les attributs data- du bouton
    const productId    = parseInt(addToCartBtn.dataset.productId);
    const productName  = addToCartBtn.dataset.productName;
    const productPrice = parseInt(addToCartBtn.dataset.productPrice);
    const productImage = mainImage ? mainImage.src : '';
    const qty          = parseInt(qtyInput.value);

    // Appeler la fonction du cart.js
    addToCart(productId, productName, window.selectedColor, window.selectedSize, productPrice, productImage, qty);
    showToast('Ajouté au panier !');
});
```

---

## 11. Le Tunnel de Commande — checkout.js

`assets/js/checkout.js` gère le formulaire de commande en **3 étapes** sans rechargement de page.

### Navigation multi-étapes
```javascript
const steps = document.querySelectorAll('[data-step]');  // 3 sections dans le HTML
let currentStep = 1;

function showStep(stepNum) {
    steps.forEach(s => {
        // Afficher uniquement l'étape courante, masquer les autres
        s.style.display = (parseInt(s.dataset.step) === stepNum) ? 'block' : 'none';
    });
    // Mettre à jour les indicateurs visuels (1 ● 2 ● 3)
    stepIndicators.forEach(ind => {
        const indN = parseInt(ind.dataset.stepIndicator);
        ind.classList.toggle('bg-primary', indN <= stepNum);  // Coloré si étape passée
    });
    currentStep = stepNum;
    window.scrollTo({ top: 0, behavior: 'smooth' });  // Remonter en haut de page
}

// Boutons "Suivant"
nextBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        if (currentStep === 1 && !validateStep1()) return;  // Valider avant de passer
        showStep(parseInt(btn.dataset.stepNext));
    });
});
```

### Calcul des frais de livraison
```javascript
// window.deliveryZones est injecté par PHP : json_encode($delivery_zones)
function updateDeliveryFee() {
    const selectedWilaya = wilayaSelect.value;  // Ex: "Alger"
    const zone = window.deliveryZones.find(z => z.wilaya_name === selectedWilaya);

    if (!zone) return;

    const isRelay = deliveryMethodRelay.checked;
    const fee     = isRelay ? parseInt(zone.relay_fee) : parseInt(zone.home_fee);
    const days    = zone.estimated_days;

    // Mettre à jour l'affichage et le champ caché (envoyé au serveur)
    deliveryFeeDisplay.textContent = formatDA(fee);
    deliveryFeeInput.value         = fee;  // Champ hidden dans le formulaire
    deliveryDaysDisplay.textContent = `${days} jour${days > 1 ? 's' : ''}`;

    updateOrderTotal();  // Recalculer le total
}

function updateOrderTotal() {
    const subtotal  = parseInt(subtotalEl.dataset.amount);
    const fee       = parseInt(deliveryFeeInput.value);
    const discount  = parseInt(discountEl?.dataset.amount ?? 0);
    const total     = subtotal + fee - discount;

    totalEl.textContent   = formatDA(Math.max(0, total));  // Jamais négatif
    finalTotalInput.value = Math.max(0, total);             // Valeur envoyée au serveur
}
```

### Validation des champs (Étape 1)
```javascript
function validateStep1() {
    const fields = [fullname, phone, wilaya, commune, address];
    let valid = true;

    fields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-error');  // Bordure rouge si vide
            valid = false;
        } else {
            field.classList.remove('border-error');
        }
    });

    // Validation spécifique du numéro algérien : 05/06/07 suivi de 8 chiffres
    if (phone.value && !/^(05|06|07)\d{8}$/.test(phone.value.replace(/\s/g, ''))) {
        phone.classList.add('border-error');
        showToast('Format téléphone invalide (ex: 0612345678)');
        valid = false;
    }

    return valid;
}
```

---

## 12. Le Panneau d'Administration

Toutes les pages `admin_*.php` commencent par `requireAdmin()` qui redirige vers la page d'accueil si l'utilisateur n'est pas administrateur.

### `admin.php` — Tableau de bord

```php
// Statistiques globales en une seule requête
$stats = $pdo->query("
    SELECT
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'Cancelled') AS total_revenue,
        (SELECT COUNT(*) FROM orders)           AS total_orders,
        (SELECT COUNT(*) FROM users WHERE role = 'customer') AS total_customers,
        (SELECT COUNT(*) FROM products)         AS total_products
")->fetch();

// 10 dernières commandes
$recent_orders = $pdo->query("
    SELECT o.*, u.fullname AS customer_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
    LIMIT 10
")->fetchAll();

// Produits avec stock faible (moins de 5 unités)
$low_stock = $pdo->query("SELECT * FROM products WHERE stock < 5 ORDER BY stock ASC")->fetchAll();
```

### `admin_orders.php` — Gestion des commandes

Filtre par statut via un paramètre GET :
```php
$status_filter = $_GET['status'] ?? '';
$conditions = $status_filter ? ["status = ?"] : ["1=1"];
$params = $status_filter ? [$status_filter] : [];
```

### `admin_add_product.php` & `admin_edit_product.php` — Gestion des produits

À la soumission du formulaire, PHP reçoit les données sérialisées par `admin-product.js` :
```php
// Les champs hidden 'colors-input' et 'color-images-input' contiennent du JSON
$colors_json       = $_POST['colors'] ?? '[]';
$color_images_json = $_POST['color_images'] ?? '{}';
$sizes_json        = $_POST['sizes'] ?? '[]';

// Valider et nettoyer le JSON
$colors       = json_decode($colors_json, true) ?? [];
$color_images = json_decode($color_images_json, true) ?? [];
$sizes        = json_decode($sizes_json, true) ?? [];

// Insérer dans PostgreSQL
$stmt = $pdo->prepare("
    INSERT INTO products (name, category, subcategory, price, discount, stock,
        description, colors, sizes, base64_image, color_images, is_new_arrival, is_featured)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?::jsonb, ?::jsonb, ?, ?::jsonb, ?, ?)
");
$stmt->execute([
    $name, $category, $subcategory, $price, $discount, $stock,
    $description,
    json_encode($colors),        // Converti en JSONB
    json_encode($sizes),         // Converti en JSONB
    $base64_image,
    json_encode($color_images),  // Converti en JSONB
    $is_new_arrival ? 'true' : 'false',
    $is_featured ? 'true' : 'false'
]);
```

### `admin_order_detail.php` — Mise à jour du statut

Utilise l'historique pour un audit complet :
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'];
    $note       = sanitize($_POST['note'] ?? '');

    // Valider le statut (liste blanche)
    $valid_statuses = ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
    if (!in_array($new_status, $valid_statuses)) {
        $error = "Statut invalide.";
    } else {
        // Mettre à jour la commande
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")
            ->execute([$new_status, $order_id]);

        // Ajouter une entrée dans l'historique
        $pdo->prepare("INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)")
            ->execute([$order_id, $new_status, $note]);
    }
}
```

---

## 13. Gestion des Images — admin-product.js

Ce fichier gère le formulaire d'ajout/modification de produit de façon entièrement dynamique.

### Ajout d'un bloc couleur
```javascript
function addColorBlock() {
    colorIndex++;
    const idx = colorIndex;
    colorBlocks[idx] = { files: [] };  // Initialiser le tableau d'images pour cet index

    const container = document.getElementById('color-blocks');
    const block = document.createElement('div');
    block.dataset.idx = idx;
    block.innerHTML = `
        <!-- Champ "Nom de la couleur" → name="color_name_${idx}" -->
        <!-- Sélecteur hexadécimal + champ texte synchronisés -->
        <!-- Zone de dépôt d'images avec aperçu miniatures -->
    `;
    container.appendChild(block);

    // Synchronisation du sélecteur couleur ↔ champ texte hexadécimal
    const picker  = block.querySelector(`[name="color_hex_${idx}"]`);
    const hexText = block.querySelector(`[name="color_hex_text_${idx}"]`);
    picker.addEventListener('input', () => { hexText.value = picker.value; });
    hexText.addEventListener('input', () => {
        if (/^#[0-9A-Fa-f]{6}$/.test(hexText.value)) {
            picker.value = hexText.value;  // Synchroniser uniquement si hex valide
        }
    });
}
```

### Compression et redimensionnement des images
```javascript
// Solution pour contourner la limite de 4,5 Mo de Vercel
function compressAndResizeImage(file, callback) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            // Calculer les nouvelles dimensions (max 1200px)
            const maxDimension = 1200;
            let width = img.width;
            let height = img.height;
            if (width > height) {
                if (width > maxDimension) { height = Math.round(height * maxDimension / width); width = maxDimension; }
            } else {
                if (height > maxDimension) { width = Math.round(width * maxDimension / height); height = maxDimension; }
            }

            // Dessiner l'image redimensionnée sur un canvas
            const canvas = document.createElement('canvas');
            canvas.width  = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(img, 0, 0, width, height);

            // Exporter en JPEG avec qualité 80% → réduction significative de la taille
            const compressedBase64 = canvas.toDataURL('image/jpeg', 0.8);
            callback(compressedBase64);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);  // Lire le fichier comme Data URL
}
```

### Sérialisation avant soumission du formulaire
```javascript
form.addEventListener('submit', function(e) {
    const colorBlockEls = document.querySelectorAll('.color-block');
    const colors        = [];
    const colorImages   = {};

    // Parcourir tous les blocs couleur créés dynamiquement
    colorBlockEls.forEach(block => {
        const idx  = block.dataset.idx;
        const name = block.querySelector(`[name="color_name_${idx}"]`).value.trim();
        const hex  = block.querySelector(`[name="color_hex_${idx}"]`).value;

        if (name) {
            colors.push({ name, hex });          // Ex: { name: "Noir", hex: "#000000" }
            if (colorBlocks[idx].files.length > 0) {
                colorImages[name] = colorBlocks[idx].files; // Ex: { "Noir": ["data:image/jpeg;base64,..."] }
            }
        }
    });

    // Écrire dans les champs hidden du formulaire
    document.getElementById('colors-input').value       = JSON.stringify(colors);
    document.getElementById('color-images-input').value = JSON.stringify(colorImages);

    // Collecter les tailles cochées
    const sizes = Array.from(document.querySelectorAll('[name="size[]"]:checked'))
        .map(cb => cb.value);
    document.getElementById('sizes-input').value = JSON.stringify(sizes);
    // Le formulaire se soumet normalement avec ces valeurs sérialisées
});
```

---

## 14. Le Système d'Authentification

L'authentification utilise des **cookies HTTP** (et non des sessions PHP) car les fonctions serverless Vercel ne partagent pas de mémoire entre les requêtes.

### Processus de connexion (`login.php`)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']);
    $password = $_POST['password'];

    // 1. Chercher l'utilisateur par email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // 2. Vérifier le mot de passe (bcrypt)
    if ($user && password_verify($password, $user['password_hash'])) {

        // 3. Vérifier si le compte est banni
        if ($user['is_banned']) {
            $error = "Votre compte a été suspendu.";
        } else {
            // 4. Créer le cookie de session
            set_auth($user['id'], $user['role']);
            // Cookie créé: {"id": 42, "role": "customer"}, valide 30 jours

            // 5. Rediriger selon le rôle
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                $redirect = $_GET['redirect'] ?? 'settings.php';
                header("Location: " . urldecode($redirect));
            }
            exit;
        }
    } else {
        $error = __('invalid_credentials');  // Identifiants invalides
    }
}
```

### Processus d'inscription (`register.php`)
```php
// Vérifier si l'email existe déjà
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $error = __('email_exists');
} else {
    // Hasher le mot de passe avec bcrypt
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Créer l'utilisateur
    $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$fullname, $email, $hash]);
    $new_id = $pdo->lastInsertId();

    // Connecter automatiquement l'utilisateur
    set_auth($new_id, 'customer');
    header("Location: index.php");
    exit;
}
```

---

## 15. Le Système de Codes Promo

### Validation du code (dans `cart.php`)
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_code'])) {
    $code = strtoupper(trim($_POST['promo_code']));

    $stmt = $pdo->prepare("
        SELECT * FROM promo_codes
        WHERE code = ?
          AND is_active = TRUE
          AND (expires_at IS NULL OR expires_at > NOW())
          AND (max_uses IS NULL OR used_count < max_uses)
    ");
    $stmt->execute([$code]);
    $promo = $stmt->fetch();

    if (!$promo) {
        $promo_error = __('promo_invalid');
    } elseif ($subtotal < $promo['min_order']) {
        $promo_error = "Sous-total minimum requis : " . format_price($promo['min_order']);
    } else {
        // Stocker le code promo dans un cookie
        setcookie('aura_promo', json_encode([
            'code'  => $promo['code'],
            'type'  => $promo['type'],
            'value' => $promo['value']
        ]), time() + (86400 * 1), '/');
        $promo_success = __('promo_applied');
    }
}
```

### Calcul de la remise
```
Si type = 'percentage' :   remise = sous-total × valeur / 100
Si type = 'fixed' :        remise = valeur (montant fixe en DA)

Total final = MAX(0, sous-total + frais_livraison - remise)
```

---

## 16. Les Zones de Livraison (69 Wilayas)

Le tarif varie selon la distance géographique par rapport à Alger :

| Zone | Exemples | Domicile | Point Relais | Délai |
|---|---|---|---|---|
| **Centre** | Alger, Blida, Boumerdès | 350–400 DA | 150–200 DA | 1 jour |
| **Nord** | Béjaïa, Constantine, Oran | 500–650 DA | 300–400 DA | 2–3 jours |
| **Hauts Plateaux** | Sétif, M'Sila, Médéa | 550–650 DA | 350–400 DA | 2–3 jours |
| **Sud** | Ouargla, El Oued, Ghardaïa | 750 DA | 450 DA | 4 jours |
| **Grand Sud** | Tamanrasset, Illizi, Tindouf | 1 200–1 500 DA | 800–1 000 DA | 6–7 jours |

Les frais sont chargés depuis PostgreSQL et transmis au JavaScript :
```php
// Dans checkout.php
$zones = $pdo->query("SELECT * FROM delivery_zones ORDER BY wilaya_code")->fetchAll();
```
```html
<script>
    window.deliveryZones = <?= json_encode($zones) ?>;
    <!-- checkout.js lit ce tableau pour calculer les frais en temps réel -->
</script>
```

---

## 17. L'Internationalisation (Bilingue FR/EN)

### Fonctionnement complet du système i18n

```
1. Visiteur arrive sur le site
         ↓
2. config.php lit $_COOKIE['lang'] (défaut: 'fr')
         ↓
3. lang.php charge le tableau $translations
         ↓
4. La fonction __('clé') retourne la bonne traduction
         ↓
5. L'utilisateur clique sur le drapeau EN ou FR dans le header
         ↓
6. Requête vers set_lang.php?lang=en
         ↓
7. setcookie('lang', 'en', time() + (86400 * 365), '/')  ← valide 1 an
         ↓
8. header("Location: " . $_SERVER['HTTP_REFERER'])  ← retour à la même page
         ↓
9. La page se recharge en anglais
```

Toutes les chaînes de texte des pages PHP utilisent `__()` :
```php
<!-- Navigation -->
<a href="index.php"><?= __('home') ?></a>      <!-- "Accueil" / "Home" -->
<a href="shop.php"><?= __('shop') ?></a>        <!-- "Boutique" / "Shop" -->

<!-- Statuts commande -->
<span><?= __($order['status'] === 'Pending' ? 'pending' : $order['status']) ?></span>

<!-- Bouton panier -->
<button><?= __('add_to_cart') ?></button>       <!-- "Ajouter au Panier" / "Add to Cart" -->
```

---

## 18. La Configuration Vercel — vercel.json

```json
{
  "functions": {
    "api/*.php": { "runtime": "vercel-php@0.9.0" }
  },
  "routes": [
    { "src": "/assets/(.*)", "dest": "/assets/$1" },
    { "src": "/(.*)\\.php",  "dest": "/api/$1.php"  },
    { "src": "/",             "dest": "/api/index.php" },
    { "src": "/(.*)",         "dest": "/api/$1.php"  }
  ]
}
```

**Explication règle par règle :**

| Règle | Exemple | Destination |
|---|---|---|
| `"api/*.php"` → runtime PHP | Tout fichier dans `api/` | Exécuté comme Lambda serverless |
| `/assets/(.*)` | `/assets/js/cart.js` | Servi directement (fichier statique) |
| `/(.*)\\.php` | `/shop.php` | → `api/shop.php` |
| `/` | `aurashop.vercel.app/` | → `api/index.php` |
| `(.*)` | `/shop` (sans extension) | → `api/shop.php` |

---

## 19. Sécurité

| Menace | Protection Implémentée |
|---|---|
| **Injection SQL** | 100% des requêtes utilisent `$pdo->prepare()` avec des paramètres liés `?`. Aucune interpolation directe de variables utilisateur. |
| **XSS (Cross-Site Scripting)** | Toutes les sorties HTML passent par `sanitize()` → `htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8')` |
| **Mots de passe** | Stockés avec `password_hash($pass, PASSWORD_BCRYPT)`. Vérifiés avec `password_verify()`. Jamais en clair. |
| **Accès admin non autorisé** | `requireAdmin()` est appelé en première ligne de chaque page admin. Redirige vers l'accueil si le cookie ne contient pas `role: admin`. |
| **Validation des données** | Téléphone : regex `^(05\|06\|07)\d{8}$`. Email : `FILTER_VALIDATE_EMAIL`. Mode livraison : liste blanche PHP. Statut commande : liste blanche PHP. |
| **Stock négatif** | `UPDATE products SET stock = GREATEST(stock - ?, 0)` empêche le stock de passer en dessous de 0. |
| **Intégrité des commandes** | Transaction PostgreSQL : `beginTransaction()` / `commit()` / `rollBack()`. Si une étape échoue, aucune écriture n'est enregistrée. |
| **Dépassement limite Vercel** | Images compressées côté client via Canvas HTML5 avant envoi. Limite Vercel : 4,5 Mo par requête. |
| **Comptes bannis** | Vérification `is_banned` après `password_verify()`. Un compte banni ne peut pas se connecter même avec le bon mot de passe. |

---

## 20. Déploiement pas à pas

### Prérequis
- Un compte [GitHub](https://github.com)
- Un compte [Vercel](https://vercel.com)
- Un compte [Supabase](https://supabase.com)

### Étape 1 — Créer la base de données

1. Créer un nouveau projet sur [Supabase](https://supabase.com)
2. Aller dans **SQL Editor**
3. Coller le contenu de `schema.sql` et l'exécuter
4. La base est initialisée avec les 69 wilayas et les 4 catégories

### Étape 2 — Récupérer l'URL de connexion

1. Supabase → **Project Settings** → **Database**
2. Onglet **Connection string** → Mode **URI**
3. Copier l'URL complète (format: `postgresql://postgres.[REF]:[PASS]@...`)

### Étape 3 — Déployer sur Vercel

1. Pousser le projet sur GitHub
2. Sur Vercel → **Add New Project** → Importer le dépôt GitHub
3. Dans **Environment Variables**, ajouter :
   - **Nom :** `DATABASE_URL`
   - **Valeur :** l'URI PostgreSQL copiée à l'étape 2
4. Cliquer sur **Deploy**

### Étape 4 — Créer le premier compte admin

1. Visiter `https://votre-domaine.vercel.app/create_admin.php`
2. Le script crée automatiquement :
   - Email : `admin@aurashop.com`
   - Mot de passe : `AuraAdmin2026!`
3. ⚠️ **Supprimer ou sécuriser ce fichier après utilisation**

### Étape 5 — Se connecter en admin

1. Aller sur `https://votre-domaine.vercel.app/login.php`
2. Entrer les identifiants admin
3. Redirection automatique vers le tableau de bord `/admin.php`

### Mises à jour

Chaque `git push` sur la branche principale déclenche un redéploiement automatique sur Vercel.

---

*Aura Shop v2.0 — Projet universitaire e-commerce — PHP / PostgreSQL / Vercel*
