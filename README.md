# AURA SHOP v2.0

> Application web e-commerce complète pour la vente de vêtements sur le marché algérien, développée en PHP et PostgreSQL, déployée sur Vercel.

---

## Table des Matières

1. [Vue d'ensemble du projet](#1-vue-densemble-du-projet)
2. [Stack Technique](#2-stack-technique)
3. [Architecture](#3-architecture)
4. [Structure du Projet](#4-structure-du-projet)
5. [Schéma de la Base de Données](#5-schéma-de-la-base-de-données)
6. [Pages & Fonctionnalités](#6-pages--fonctionnalités)
7. [Panneau d'Administration](#7-panneau-dadministration)
8. [Système d'Authentification](#8-système-dauthentification)
9. [Système de Panier](#9-système-de-panier)
10. [Processus de Commande (Checkout)](#10-processus-de-commande-checkout)
11. [Zones de Livraison](#11-zones-de-livraison)
12. [Système de Codes Promo](#12-système-de-codes-promo)
13. [Internationalisation (i18n)](#13-internationalisation-i18n)
14. [Modules JavaScript](#14-modules-javascript)
15. [Déploiement sur Vercel](#15-déploiement-sur-vercel)
16. [Variables d'Environnement](#16-variables-denvironnement)
17. [Initialisation de la Base de Données](#17-initialisation-de-la-base-de-données)
18. [Création du Compte Administrateur](#18-création-du-compte-administrateur)
19. [Mesures de Sécurité](#19-mesures-de-sécurité)
20. [Référence des Fonctions Utilitaires](#20-référence-des-fonctions-utilitaires)
21. [Guide de Génération des Images (IA)](#21-guide-de-génération-des-images-ia)

---

## 1. Vue d'ensemble du projet

Aura Shop est une boutique en ligne bilingue (Français / Anglais) ciblant le marché algérien. Elle offre :

- Une vitrine client complète avec navigation de produits, filtres, et un tunnel de commande en plusieurs étapes
- Un tableau de bord administrateur complet pour gérer les produits, commandes, clients, codes promo et paramètres du site
- Le paiement à la livraison comme unique mode de paiement (standard en Algérie)
- 69 zones de livraison couvrant toutes les wilayas algériennes avec des tarifs individualisés
- Un panier basé sur les cookies, fonctionnel sans connexion jusqu'au moment de la commande

---

## 2. Stack Technique

| Couche | Technologie |
|---|---|
| **Backend** | PHP 8.x (sans serveur via Vercel Functions) |
| **Base de données** | PostgreSQL 15 sur Supabase |
| **Pilote DB** | PDO (PHP Data Objects) |
| **Frontend** | HTML / CSS / JavaScript vanilla |
| **Style** | Tailwind CSS (via CDN) + jetons de design personnalisés |
| **Icônes** | Google Material Symbols |
| **Polices** | Google Fonts (Inter) |
| **Hébergement** | Vercel (runtime `vercel-php@0.9.0`) |
| **Stockage images** | Images encodées en Base64 et stockées dans PostgreSQL |

---

## 3. Architecture

```
Navigateur (Client)
      │
      │  Requête HTTP (ex : GET /shop.php)
      ▼
Réseau Edge Vercel
      │
      │  Route correspondante via vercel.json
      ▼
Fonction Serverless Vercel
  api/shop.php   ◄──── Chaque fichier PHP est une fonction indépendante
      │
      │  require_once 'config.php'  (toujours la première ligne)
      ▼
config.php
  ├── Démarre / reprend la session PHP
  ├── Connexion à PostgreSQL via PDO (variable DATABASE_URL)
  ├── Définit toutes les fonctions utilitaires globales
  └── Charge lang.php → définit $translations et la fonction __()
      │
      ▼
PostgreSQL (Supabase)
  ├── Exécute les requêtes SQL préparées
  └── Retourne les résultats en tableaux associatifs
      │
      ▼
PHP génère le HTML → renvoyé au navigateur
```

**Contrainte architecturale importante :** Vercel exécute chaque fichier PHP comme une fonction serverless sans état. Il n'y a pas de mémoire partagée entre les requêtes. `$_SESSION` PHP est utilisé uniquement pour les messages flash (confirmation newsletter). Tout l'état utilisateur persistant (authentification, panier, promo) est stocké dans des **cookies**.

---

## 4. Structure du Projet

```
aurashopv2.0/
│
├── vercel.json          # Configuration Vercel et règles de routage
├── schema.sql           # Schéma PostgreSQL complet + données initiales (69 wilayas)
├── README.md            # Ce fichier
│
├── api/                 # Tous les fichiers PHP (fonctions serverless Vercel)
│   ├── config.php           # Connexion DB, fonctions utilitaires, chargement i18n
│   ├── lang.php             # Chaînes de traduction (fr / en)
│   │
│   ├── index.php            # Page d'accueil (hero, produits vedettes, catégories)
│   ├── shop.php             # Liste des produits avec filtres et recherche
│   ├── product.php          # Page de détail d'un produit
│   ├── cart.php             # Page du panier
│   ├── checkout.php         # Formulaire de commande en plusieurs étapes
│   ├── confirmation.php     # Page de confirmation après commande réussie
│   │
│   ├── login.php            # Page de connexion (clients et admins partagent cette page)
│   ├── register.php         # Page d'inscription client
│   ├── logout.php           # Efface le cookie d'authentification et redirige
│   ├── settings.php         # Espace client : profil, commandes, zone de danger
│   │
│   ├── admin.php                # Tableau de bord admin (statistiques, commandes récentes)
│   ├── admin_orders.php         # Liste de gestion des commandes
│   ├── admin_order_detail.php   # Détail d'une commande + mise à jour du statut
│   ├── admin_add_product.php    # Formulaire d'ajout de produit
│   ├── admin_edit_product.php   # Formulaire de modification de produit
│   ├── admin_categories.php     # Gestion des catégories de produits
│   ├── admin_customers.php      # Liste clients + bannissement/débannissement
│   ├── admin_promo.php          # Gestion des codes promo
│   ├── admin_settings.php       # Éditeur des frais de livraison par wilaya
│   │
│   ├── header.php           # En-tête de navigation partagé (inclus par toutes les pages)
│   ├── footer.php           # Pied de page partagé avec formulaire newsletter
│   │
│   ├── set_lang.php         # Point d'entrée du sélecteur de langue (définit le cookie)
│   └── create_admin.php     # Outil de création du premier compte administrateur
│
└── assets/
    ├── favicon.png
    ├── hero1.png / hero2.png / hero3.png   # Images du carrousel hero (Desktop)
    ├── hero1_mobile.png / ...              # Images du carrousel hero (Mobile)
    ├── cat_all.png / cat_women.png / ...   # Images des cartes de catégories
    └── js/
        ├── cart.js           # Lecture/écriture du panier (basé sur cookies)
        ├── checkout.js       # Formulaire multi-étapes + calcul des frais de livraison
        ├── product.js        # Sélecteur couleur/taille, galerie d'images
        └── admin-product.js  # Gestion dynamique des couleurs/images en admin
```

---

## 5. Schéma de la Base de Données

La base de données comporte **8 tables**. Voici le détail complet de chacune.

### `users`
Stocke tous les comptes inscrits (clients et administrateurs).

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Clé primaire auto-incrémentée |
| `fullname` | VARCHAR(255) | Nom complet de l'utilisateur |
| `email` | VARCHAR(255) UNIQUE | Adresse email de connexion |
| `password_hash` | VARCHAR(255) | Hash bcrypt généré par `password_hash()` |
| `role` | VARCHAR(50) | `'customer'` ou `'admin'` |
| `is_banned` | BOOLEAN | Si vrai, la connexion est bloquée |
| `created_at` | TIMESTAMP | Date d'inscription |

### `categories`
Table de référence simple pour les catégories de produits.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Clé primaire auto-incrémentée |
| `name` | VARCHAR(100) UNIQUE | Nom de la catégorie (ex. `Women`, `Men`) |

Catégories par défaut : `Women`, `Men`, `Accessories`, `Unisex`

### `products`
Le catalogue produits principal.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Clé primaire auto-incrémentée |
| `name` | VARCHAR(255) | Nom du produit |
| `category` | VARCHAR(100) | Nom de la catégorie (correspond à `categories.name`) |
| `subcategory` | VARCHAR(100) | Sous-libellé optionnel libre |
| `description` | TEXT | Description longue du produit |
| `price` | INT | Prix en Dinars Algériens (DA), stocké en entier |
| `discount` | INT | Remise en pourcentage (0–100) |
| `stock` | INT | Quantité disponible |
| `colors` | JSONB | Tableau d'objets couleur : `[{"name": "Noir", "hex": "#000000"}]` |
| `sizes` | JSONB | Tableau de tailles : `["S", "M", "L", "XL"]` |
| `image_url` | TEXT | URL externe pour l'image principale |
| `base64_image` | TEXT | Image principale encodée en Base64 (téléversée côté client) |
| `color_images` | JSONB | Correspondance couleur → tableau d'images Base64 : `{"Noir": ["data:image/..."]}` |
| `is_new_arrival` | BOOLEAN | Affiche le badge « Nouveau » sur la carte produit |
| `is_featured` | BOOLEAN | Apparaît dans la section Vedette de la page d'accueil |
| `created_at` | TIMESTAMP | Date de création |

> **Note sur le stockage des images :** Les images sont téléversées par l'admin, converties en Base64 dans le navigateur via l'API JavaScript `FileReader`, puis stockées comme texte dans la base de données. Cela évite le recours à un service de stockage de fichiers séparé.

### `orders`
Un enregistrement par commande passée.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Identifiant interne de la commande |
| `order_number` | VARCHAR(100) UNIQUE | Numéro lisible (ex. `ORD-20260606-A3F8C1`) |
| `user_id` | INT FK→users | Client ayant passé la commande (SET NULL si supprimé) |
| `fullname` | VARCHAR(255) | Nom de livraison |
| `phone` | VARCHAR(50) | Téléphone de contact (format algérien : 05/06/07XXXXXXXX) |
| `wilaya` | VARCHAR(100) | Wilaya de livraison |
| `commune` | VARCHAR(100) | Commune de livraison |
| `address` | TEXT | Adresse complète |
| `delivery_method` | VARCHAR(50) | `'home'` (domicile) ou `'relay'` (point relais) |
| `total_amount` | INT | Total final en DA (après remise + frais de livraison) |
| `discount_amount` | INT | Montant déduit par le code promo |
| `delivery_fee` | INT | Frais de livraison en DA |
| `promo_code` | VARCHAR(100) | Code promo utilisé (si applicable) |
| `status` | VARCHAR(50) | `Pending`, `Confirmed`, `Shipped`, `Delivered`, ou `Cancelled` |
| `notes` | TEXT | Notes du client |
| `order_date` | TIMESTAMP | Date et heure de la commande |

### `order_items`
Lignes de commande pour chaque commande. Capture les détails produit au moment de l'achat.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Auto-incrémenté |
| `order_id` | INT FK→orders | Commande parente (suppression en cascade) |
| `product_id` | INT FK→products | Produit source (SET NULL si supprimé) |
| `product_name` | VARCHAR(255) | Capture du nom du produit au moment de l'achat |
| `quantity` | INT | Nombre d'unités commandées |
| `price` | INT | Prix unitaire au moment de l'achat |
| `size` | VARCHAR(50) | Taille sélectionnée |
| `color` | VARCHAR(100) | Nom de la couleur sélectionnée |
| `image_url` | TEXT | Image du produit au moment de l'achat |

### `order_status_history`
Journal d'audit de chaque changement de statut d'une commande.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Auto-incrémenté |
| `order_id` | INT FK→orders | Commande parente (suppression en cascade) |
| `status` | VARCHAR(50) | Nouvelle valeur de statut |
| `note` | TEXT | Note de l'admin ou du système |
| `changed_at` | TIMESTAMP | Date et heure du changement |

### `promo_codes`
Codes de réduction que les clients peuvent appliquer lors du paiement.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Auto-incrémenté |
| `code` | VARCHAR(100) UNIQUE | La chaîne que le client saisit |
| `type` | VARCHAR(50) | `'fixed'` (montant fixe en DA) ou `'percentage'` (pourcentage) |
| `value` | INT | Montant à déduire (DA ou %) |
| `min_order` | INT | Sous-total minimum pour utiliser le code |
| `max_uses` | INT | Nombre maximum total d'utilisations (NULL = illimité) |
| `used_count` | INT | Nombre de fois que le code a été utilisé |
| `expires_at` | TIMESTAMP | Date d'expiration (NULL = pas d'expiration) |
| `is_active` | BOOLEAN | L'admin peut activer/désactiver les codes |
| `created_at` | TIMESTAMP | Date de création |

### `delivery_zones`
Un enregistrement par wilaya algérienne avec les tarifs de livraison.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Auto-incrémenté |
| `wilaya_code` | INT UNIQUE | Numéro officiel de la wilaya (1–69) |
| `wilaya_name` | VARCHAR(100) UNIQUE | Nom officiel de la wilaya |
| `home_fee` | INT | Frais de livraison à domicile en DA |
| `relay_fee` | INT | Frais de livraison en point relais en DA |
| `estimated_days` | INT | Délai de livraison estimé en jours |

### `newsletter`
Adresses email collectées via le formulaire d'abonnement en pied de page.

| Colonne | Type | Description |
|---|---|---|
| `id` | SERIAL PK | Auto-incrémenté |
| `email` | VARCHAR(255) UNIQUE | Email de l'abonné |
| `created_at` | TIMESTAMP | Date d'abonnement |

---

## 6. Pages & Fonctionnalités

### Page d'accueil — `index.php`
- Carrousel hero animé réactif qui commute d'image selon le support (Desktop vs Mobile)
- Raccourcis rapides vers les catégories (Femmes, Hommes, Accessoires, Unisexe, Tout voir)
- Section produits vedettes (produits avec `is_featured = TRUE`)
- Section nouvelles arrivées (produits avec `is_new_arrival = TRUE`)
- Formulaire d'abonnement à la newsletter (traité par `config.php` via POST, stocké en DB)

### Boutique — `shop.php`
- Affiche tous les produits actifs dans une grille responsive
- **Filtres disponibles :**
  - Par catégorie (Femmes / Hommes / Accessoires / Unisexe)
  - Par taille (dynamique, basé sur les tailles disponibles en DB)
  - Par plage de prix (curseur)
  - Par recherche textuelle (cherche dans `name` et `description`)
- **Tri :** Plus récents, Prix croissant, Prix décroissant
- Les cartes produit affichent le nom, le prix, la remise active, le badge « Nouveau » et le badge « Vedette »
- Prix remisé calculé comme : `prix - (prix × remise / 100)`

### Détail Produit — `product.php`
- Description complète du produit
- Sélecteur de couleur (pastilles rendues depuis la colonne JSONB `colors`)
- Sélecteur de taille (boutons rendus depuis la colonne JSONB `sizes`)
- Galerie d'images qui change selon la couleur sélectionnée (utilise `color_images` JSONB)
- Sélecteur de quantité
- Bouton « Ajouter au Panier » (géré par `cart.js`, écrit dans le cookie)
- Affichage du stock / état Rupture de stock

### Panier — `cart.php`
- Lit le panier depuis le cookie `aura_cart`
- Affiche tous les articles avec image, nom, couleur, taille, quantité, prix
- Ajustement de la quantité (met à jour le cookie via JS)
- Bouton Retirer un article
- Champ de saisie du code promo (validé en DB via POST AJAX)
- Calcul du sous-total, remise et total
- Bouton « Passer la commande » (requiert une connexion)

### À Propos — `about.php`
- Nouvelle page éditoriale élégante décrivant la philosophie de design architectural, la qualité de production et les valeurs de marque.

### Contact — `contact.php`
- Formulaire de contact complet et moderne (Nom complet, email, message) avec retour d'état visuel et validation des champs.

### Confirmation — `confirmation.php`
- Affichée après qu'une commande est passée avec succès
- Affiche le numéro de commande (ex. `ORD-20260606-A3F8C1`)
- Affiche tous les articles commandés, l'adresse de livraison, le mode de livraison et le total
- Lien pour continuer les achats

### Connexion — `login.php`
- Formulaire email + mot de passe
- En cas de succès : définit le cookie `aura_auth` et redirige
  - Admins → `admin.php`
  - Clients → `settings.php` ou destination d'origine (paramètre `?redirect=`)
- Les utilisateurs bannis voient une erreur et ne peuvent pas se connecter

### Inscription — `register.php`
- Champs : Nom complet, email, mot de passe
- Le mot de passe est hashé avec `password_hash()` de PHP (bcrypt)
- Vérification des doublons d'email avant insertion
- En cas de succès : connexion automatique et redirection vers la page d'accueil

### Espace Client — `settings.php`
- **Onglet Profil :** Modifier le nom complet, l'email et le mot de passe
- **Onglet Historique des commandes :** Liste toutes les commandes passées avec statut, montant et lien vers le détail
- **Onglet Zone de Danger :** Suppression du compte (supprime l'enregistrement utilisateur de la DB)

---

## 7. Panneau d'Administration

Toutes les pages admin requièrent `role = 'admin'` dans le cookie d'authentification. Tout accès non autorisé redirige vers `index.php`.

### Tableau de bord — `admin.php`
- Cartes de statistiques récapitulatives : chiffre d'affaires total, nombre de commandes, nombre de clients, nombre de produits
- Tableau des commandes récentes (10 dernières commandes)
- Alerte de stock faible (produits avec `stock < 5`)

### Commandes — `admin_orders.php`
- Liste paginée de toutes les commandes
- Filtrer par statut (En attente / Confirmée / Expédiée / Livrée / Annulée)
- Badges de statut colorés
- Cliquer sur une ligne ouvre `admin_order_detail.php`

### Détail Commande — `admin_order_detail.php`
- Détail complet de la commande : informations client, adresse de livraison, articles, totaux
- Liste déroulante de mise à jour du statut avec bouton de sauvegarde
- Chronologie de l'historique des statuts montrant tous les changements précédents avec horodatages

### Produits — via `admin_add_product.php` et `admin_edit_product.php`
- Ajouter/modifier le nom, catégorie, sous-catégorie, prix, remise, stock, description du produit
- Activer/désactiver les drapeaux `is_new_arrival` et `is_featured`
- **Gestion des couleurs (dynamique via JS) :**
  - Ajouter plusieurs couleurs nommées avec sélecteur de couleur hexadécimal
  - Téléverser plusieurs images par couleur (converties en Base64 dans le navigateur)
  - Supprimer des couleurs individuelles et leurs images associées
- Téléversement de l'image principale (convertie en Base64)
- Tout est géré côté client par `assets/js/admin-product.js`

### Catégories — `admin_categories.php`
- Afficher, ajouter, renommer et supprimer des catégories de produits
- La suppression est bloquée si des produits font référence à cette catégorie

### Clients — `admin_customers.php`
- Liste de tous les clients inscrits avec nom, email, date d'inscription et statut
- Bouton bascule Bannir / Débannir (modifie la colonne `is_banned`)
- Les utilisateurs bannis ne peuvent pas se connecter

### Codes Promo — `admin_promo.php`
- Créer de nouveaux codes promo (remise fixe ou en pourcentage)
- Définir la valeur minimale de commande, la limite d'utilisation et la date d'expiration
- Activer/désactiver le statut
- Voir le compteur d'utilisation de chaque code

### Paramètres — `admin_settings.php`
- Modifier les frais de livraison (`home_fee`, `relay_fee`) et les délais estimés pour chaque wilaya
- Les modifications sont enregistrées immédiatement dans la table `delivery_zones`

---

## 8. Système d'Authentification

L'authentification dans Aura Shop utilise des **cookies HTTP** plutôt que les sessions PHP traditionnelles, car les fonctions serverless Vercel ne peuvent pas partager des fichiers de session entre les invocations.

### Cookie : `aura_auth`
```json
{"id": 42, "role": "customer"}
```
- Défini pour **30 jours** après une connexion réussie
- Effacé à la déconnexion (`logout.php`)
- **Non chiffré** — pour un déploiement en production, ce cookie devrait être signé avec HMAC

### Fonctions (définies dans `config.php`)

```php
get_auth()              // Retourne le tableau d'auth décodé, ou null si non connecté
set_auth($id, $role)    // Définit le cookie aura_auth après connexion
clear_auth()            // Fait expirer le cookie à la déconnexion
requireAuth()           // Redirige vers login.php si non authentifié, retourne le tableau d'auth
requireAdmin()          // Redirige vers index.php si non admin, retourne le tableau d'auth
```

### Processus de Connexion
1. L'utilisateur soumet email + mot de passe à `login.php`
2. PHP récupère l'enregistrement utilisateur par email depuis la DB
3. `password_verify()` vérifie le mot de passe soumis contre le hash stocké
4. `is_banned` est vérifié — si vrai, la connexion est refusée
5. En cas de succès : `set_auth($user['id'], $user['role'])` définit le cookie
6. Redirection selon le rôle (`admin.php` ou `settings.php`)

---

## 9. Système de Panier

Le panier est entièrement stocké **côté client** dans un cookie appelé `aura_cart`.

### Cookie : `aura_cart`
```json
[
  {
    "product_id": "12",
    "name": "Chemise Lin Beige",
    "price": "4500",
    "quantity": 2,
    "size": "M",
    "color": "Beige",
    "image_url": "data:image/jpeg;base64,..."
  }
]
```
- Défini pour **30 jours**
- Lu et écrit par `assets/js/cart.js`
- Également lisible par PHP via `get_cart()` dans `config.php`

### Fonctions PHP du Panier
```php
get_cart()          // Décode le cookie aura_cart, retourne un tableau d'articles
save_cart($cart)    // Encode en JSON et écrit le cookie aura_cart
```

### Fonctions JavaScript du Panier (`cart.js`)
```js
readCart()                    // Retourne le tableau du panier parsé depuis le cookie
writeCart(items)              // Sérialise et sauvegarde le panier dans le cookie
addToCart(item)               // Ajoute ou incrémente un article
removeFromCart(index)         // Retire un article par son index
updateQuantity(index, qty)    // Définit la quantité d'un article
formatDA(amount)              // Formate un entier en "4 500 DA"
```

---

## 10. Processus de Commande (Checkout)

Le checkout est un **formulaire multi-étapes** contenu dans un seul fichier PHP (`checkout.php`). Les transitions entre étapes sont gérées en JavaScript sans rechargement de page.

### Étape 1 : Informations de Livraison
Champs : Nom complet, Téléphone (validé par regex `^(05|06|07)\d{8}$`), Wilaya (sélection parmi 69 options), Commune, Adresse, Notes

La liste déroulante des wilayas est alimentée depuis la table `delivery_zones` et transmise à JavaScript via `window.deliveryZones`.

### Étape 2 : Mode de Livraison
- Livraison à Domicile ou en Point Relais
- Les frais sont récupérés depuis `window.deliveryZones` selon la wilaya sélectionnée
- Le délai de livraison estimé est affiché
- Les frais sélectionnés sont écrits dans un champ caché `delivery_fee`

### Étape 3 : Paiement et Récapitulatif
- Seul le paiement à la livraison est disponible
- Affiche le récapitulatif de commande (articles, sous-total, frais de livraison, remise, total)
- La soumission déclenche le gestionnaire PHP `POST`

### Création de Commande PHP (lors du POST)
1. Validation de tous les champs
2. Démarrage d'une **transaction base de données** (`$pdo->beginTransaction()`)
3. Génération d'un numéro de commande unique : `ORD-YYYYMMDD-XXXXXX`
4. Insertion d'un enregistrement dans `orders`
5. Insertion de chaque article du panier dans `order_items`
6. Décrémentation du `stock` dans `products` pour chaque article (en utilisant `GREATEST(stock - ?, 0)` pour éviter les valeurs négatives)
7. Insertion de la première entrée dans `order_status_history` avec le statut `Pending`
8. Incrémentation de `used_count` sur le code promo (si utilisé)
9. Validation de la transaction (commit)
10. Suppression des cookies `aura_cart` et `aura_promo`
11. Redirection vers `confirmation.php?order=ORD-...`

En cas d'échec à n'importe quelle étape, `$pdo->rollBack()` est appelé et un message d'erreur est affiché.

---

## 11. Zones de Livraison

L'application couvre les **69 wilayas algériennes officielles** (incluant les 11 nouvelles wilayas ajoutées par la réforme administrative de 2019).

### Logique de Tarification
Les frais varient selon la distance géographique par rapport à Alger :

| Zone | Wilayas | Domicile | Point Relais | Délai estimé |
|---|---|---|---|---|
| Centre (Alger, Blida, Boumerdès) | 9, 16, 34 | 350–400 DA | 150–200 DA | 1 jour |
| Nord | La plupart des wilayas du nord | 500–650 DA | 300–400 DA | 2–3 jours |
| Sud | Wilayas sahariennes | 750–1000 DA | 450–600 DA | 4–5 jours |
| Grand Sud (Tamanrasset, Illizi, etc.) | 11, 32, 36, 52, 53 | 1200–1500 DA | 800–1000 DA | 6–7 jours |

### Nouvelles Wilayas Ajoutées (59–69)
| Code | Nom |
|---|---|
| 59 | Aflou |
| 60 | El Abiodh Sidi Cheikh |
| 61 | El Aricha |
| 62 | El Kantara |
| 63 | Barika |
| 64 | Bou Saâda |
| 65 | Bir El Ater |
| 66 | Ksar El Boukhari |
| 67 | Ksar Chellala |
| 68 | Aïn Oussara |
| 69 | Messaad |

---

## 12. Système de Codes Promo

### Fonctionnement
1. Le client saisit un code sur la page panier
2. Un POST est envoyé à `cart.php`
3. PHP valide le code contre la table `promo_codes` :
   - Le code doit exister et `is_active = TRUE`
   - Ne doit pas être expiré (`expires_at IS NULL OR expires_at > NOW()`)
   - L'utilisation ne doit pas dépasser la limite (`max_uses IS NULL OR used_count < max_uses`)
   - Le sous-total de la commande doit atteindre `min_order`
4. Si valide, les détails du code sont stockés dans le cookie `aura_promo`
5. Lors du checkout, le cookie est lu et la remise est appliquée côté serveur
6. Après la commande réussie, `used_count` est incrémenté

### Cookie : `aura_promo`
```json
{"code": "ETE20", "type": "percentage", "value": 20}
```

### Calcul de la Remise
```
Si type = 'percentage' :  remise = sous-total × valeur / 100
Si type = 'fixed' :       remise = valeur (montant fixe en DA)
Total final = MAX(0, sous-total + frais de livraison - remise)
```

---

## 13. Internationalisation (i18n)

Le site prend en charge deux langues : **Français (fr)** et **Anglais (en)**.

### Détection de la Langue
La langue est stockée dans le cookie `lang`. La valeur par défaut est `fr`.

```php
$language = $_COOKIE['lang'] ?? 'fr';
```

### Changement de Langue
Une requête GET vers `set_lang.php?lang=en` (or `?lang=fr`) définit le cookie et redirige vers la page précédente.

### Fonction de Traduction
Définie dans `config.php` après le chargement de `lang.php` :

```php
function __($key) {
    global $translations, $language;
    return $translations[$language][$key] ?? $key;
}
```
Si une clé est manquante, la clé elle-même est retournée comme valeur de secours.

### Clés de Traduction (sélection)
| Clé | Français | Anglais |
|---|---|---|
| `home` | Accueil | Home |
| `shop` | Boutique | Shop |
| `cart` | Panier | Cart |
| `checkout` | Passer la commande | Checkout |
| `pending` | En attente | Pending |
| `confirmed` | Confirmée | Confirmed |
| `shipped` | Expédiée | Shipped |
| `delivered` | Livrée | Delivered |
| `cancelled` | Annulée | Cancelled |
| `add_to_cart` | Ajouter au Panier | Add to Cart |
| `delivery_home` | Livraison à Domicile | Home Delivery |
| `delivery_relay` | Livraison en Point Relais | Relay Point Delivery |

Les 103 clés de traduction complètes sont définies dans `api/lang.php`.

---

## 14. Modules JavaScript

### `assets/js/cart.js`
Utilitaires fondamentaux du panier. Chargé sur toutes les pages nécessitant l'accès au panier (panier, checkout, en-tête).

- `readCart()` — parse le cookie `aura_cart`
- `writeCart(items)` — sauvegarde le tableau du panier dans le cookie
- `addToCart(item)` — fusionne ou insère un article (correspond par `product_id + size + color`)
- `removeFromCart(i)` — retire par index
- `updateQuantity(i, qty)` — met à jour la quantité, retire si qté ≤ 0
- `formatDA(n)` — formate le nombre en `"4 500 DA"`
- Met à jour le badge du compteur du panier dans l'en-tête au chargement

### `assets/js/checkout.js`
Contrôle l'interface utilisateur du checkout en 3 étapes et la logique des frais de livraison.

- Navigation entre les étapes : affiche/masque les sections `[data-step]` au clic des boutons
- Lit `window.deliveryZones` (JSON intégré par PHP)
- Au changement de wilaya : cherche les frais et délais dans le tableau des zones
- Met à jour `#home-fee-display`, `#relay-fee-display`, `#delivery-days-display`
- Écrit les frais sélectionnés dans le champ caché `#delivery-fee-input`
- Met à jour `#delivery-fee-display` dans le récapitulatif latéral
- Recalcule et met à jour `#summary-total` au changement de mode de livraison

### `assets/js/product.js`
Contrôle l'interactivité de la page de détail produit.

- Sélection des pastilles de couleur — met en surbrillance la couleur choisie, met à jour les images affichées
- Galerie d'images — cliquer sur une miniature met à jour l'image principale
- Sélection des boutons de taille — mémorise la taille sélectionnée
- Gestionnaire « Ajouter au Panier » — valide la sélection couleur/taille, appelle `addToCart()`, affiche une notification toast
- État Rupture de stock — désactive le bouton d'ajout au panier quand le stock est 0

### `assets/js/admin-product.js`
Alimente le formulaire produit dynamique dans le panneau d'administration.

- Ajouter une couleur : insère un nouveau bloc couleur avec champ de nom, sélecteur hexadécimal et zone de téléversement d'images
- Supprimer une couleur : retire le bloc couleur complet
- Téléversement d'image : lit les fichiers avec `FileReader`, convertit en Base64, affiche les miniatures de prévisualisation
- Retirer une image : supprime une image individuelle de la prévisualisation
- Sérialisation du formulaire : avant soumission, collecte toutes les couleurs dans une chaîne JSON pour le champ caché `colors`, et toutes les images de couleur dans une chaîne JSON pour le champ `color_images`
- Utilisé par `admin_add_product.php` et `admin_edit_product.php`

---

## 15. Déploiement sur Vercel

### Comment Vercel Exécute PHP

Vercel ne supporte pas PHP nativement. Le runtime communautaire `vercel-php` compile chaque fichier `.php` dans `api/` en une fonction Lambda serverless individuelle.

Chaque requête est totalement isolée — pas de mémoire partagée, pas d'état persistant entre les appels.

### `vercel.json` — Règles de Routage

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

**Fonctionnement des routes :**
- `/assets/...` → sert les fichiers statiques directement (images, JS)
- `/shop.php` → pointe vers `api/shop.php`
- `/` (racine) → pointe vers `api/index.php`
- `/shop` (sans extension) → pointe vers `api/shop.php`

### Déploiement

1. Pousser le dépôt vers un référentiel GitHub
2. Sur [vercel.com](https://vercel.com), importer le dépôt
3. Ajouter la variable d'environnement `DATABASE_URL` (voir Section 16)
4. Déployer — Vercel construit et assigne automatiquement une URL publique
5. Chaque `git push` sur la branche principale déclenche un redéploiement automatique

---

## 16. Variables d'Environnement

Une seule variable d'environnement est nécessaire :

| Variable | Description |
|---|---|
| `DATABASE_URL` | Chaîne de connexion PostgreSQL complète depuis Supabase |

### Format
```
postgresql://postgres.[REF_PROJET]:[MOT_DE_PASSE]@aws-0-[REGION].pooler.supabase.com:6543/postgres
```

### Où la Trouver (Supabase)
1. Ouvrir [supabase.com](https://supabase.com) → Votre Projet
2. Aller dans **Project Settings** → **Database**
3. Sous **Connection string**, sélectionner le mode **URI**
4. Copier la chaîne complète et remplacer `[YOUR-PASSWORD]` par votre mot de passe DB

### Comment la Définir sur Vercel
1. Ouvrir votre projet Vercel → **Settings** → **Environment Variables**
2. Ajouter : Nom = `DATABASE_URL`, Valeur = l'URI complète ci-dessus
3. Redéployer pour que la variable prenne effet

### Utilisation dans le Code (`config.php`)
```php
$dbUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);
$parsedUrl = parse_url($dbUrl);
$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
$pdo = new PDO($dsn, $user, $pass, [...]);
```

---

## 17. Initialisation de la Base de Données

### Mise en Place Initiale (à exécuter une seule fois sur une DB vierge)
1. Ouvrir votre projet Supabase
2. Aller dans l'**Éditeur SQL**
3. Coller le contenu complet de `schema.sql` et l'exécuter

`schema.sql` effectue dans l'ordre :
1. Crée les 8 tables avec `CREATE TABLE IF NOT EXISTS` (sans risque de ré-exécution)
2. Insère les 4 catégories par défaut
3. Insère les 69 wilayas algériennes avec leur tarification via `ON CONFLICT DO UPDATE` (sans risque de ré-exécution)

### Ré-exécution du Schéma
Le schéma est **idempotent** — l'exécuter plusieurs fois ne créera pas de données dupliquées ni ne supprimera les données existantes. Les clauses `IF NOT EXISTS` et `ON CONFLICT` garantissent une ré-exécution sécurisée.

---

## 18. Création du Compte Administrateur

Le fichier `api/create_admin.php` crée le premier compte administrateur.

### Utilisation
1. Visiter : `https://votre-domaine.com/create_admin.php`
2. Le script crée :
   - **Email :** `admin@aurashop.com`
   - **Mot de passe :** `AuraAdmin2026!`
   - **Rôle :** `admin`
3. Après l'avoir exécuté une seule fois, cet endpoint doit être **supprimé ou protégé par mot de passe** pour des raisons de sécurité

### Connexion Admin
1. Aller sur `https://votre-domaine.com/login.php`
2. Saisir les identifiants admin ci-dessus
3. Vous serez redirigé vers `admin.php` (le tableau de bord)

> **La page de connexion est partagée entre les clients et les admins.** Après la connexion, le système vérifie le `role` dans la base de données et redirige en conséquence.

---

## 19. Mesures de Sécurité

| Mesure | Implémentation |
|---|---|
| **Prévention des injections SQL** | Toutes les requêtes DB utilisent des requêtes préparées PDO avec des paramètres liés |
| **Prévention XSS** | Toutes les sorties alimentées par l'utilisateur passent par `sanitize()` qui appelle `htmlspecialchars(strip_tags(trim($v)), ENT_QUOTES, 'UTF-8')` |
| **Hashage des mots de passe** | Mots de passe stockés avec `password_hash($pass, PASSWORD_BCRYPT)` et vérifiés avec `password_verify()` |
| **Garde d'authentification** | `requireAuth()` and `requireAdmin()` sont appelés en haut de chaque page protégée |
| **Validation des entrées** | Téléphone validé par regex ; email validé avec `FILTER_VALIDATE_EMAIL` ; mode de livraison validé contre une liste blanche |
| **Intégrité transactionnelle** | La commande utilise `beginTransaction()` / `commit()` / `rollBack()` pour éviter les écritures partielles |
| **Protection du plancher de stock** | `GREATEST(stock - ?, 0)` empêche le stock de devenir négatif |
| **CSRF** | ⚠️ Non implémenté. En production, ajouter un jeton CSRF à tous les formulaires POST |
| **Signature du cookie** | ⚠️ Le cookie d'auth n'est pas signé HMAC. En production, signer le contenu du cookie |

---

## 20. Référence des Fonctions Utilitaires

Toutes les fonctions utilitaires globales sont définies dans `api/config.php` et disponibles sur toutes les pages.

```php
// Utilitaires panier
get_cart()               // → tableau d'articles du panier depuis le cookie
save_cart(array $cart)   // Écrit le tableau du panier dans le cookie (expiration 30 jours)

// Utilitaires d'authentification
get_auth()               // → ['id' => int, 'role' => string] or null
set_auth(int $id, string $role)   // Définit le cookie aura_auth
clear_auth()             // Fait expirer le cookie aura_auth
requireAuth()            // Redirige vers login si non connecté, retourne le tableau d'auth
requireAdmin()           // Redirige vers index.php si non admin, retourne le tableau d'auth

// Formatage
format_price(int $amount)    // → "4 500 DA"
generate_order_number()      // → "ORD-20260606-A3F8C1" (date + suffixe aléatoire)

// Sécurité
sanitize(string $v)      // htmlspecialchars + strip_tags + trim

// i18n
__(string $key)          // Retourne la chaîne traduite pour la langue courante
```

---

## 21. Guide de Génération des Images (IA)

Ces prompts pour GPT/DALL-E 3 sont conçus pour correspondre à la palette de couleurs d'Aura (Vert forêt profond `#031710`, Vert sauge `#b8ccb6`, Gris, Noir, Beige) et intègrent des marges de sécurité sur les côtés pour la compatibilité mobile, tablette et PC.

### 🖼️ Bannières Hero (Format Paysage Widescreen 16:9)

Pour chaque bannière, prévoyez **deux fichiers** :
* Un fichier de base paysage (ex : `hero1.png`) pour les ordinateurs.
* Un fichier portrait (ex : `hero1_mobile.png`) recadré à un format mobile 2:3 ou 3:4 que vous chargerez dans le même répertoire.

#### Hero 1 : Streetwear Minimaliste (Homme)
* **Vêtement :** Hoodie oversize sauge, jogging noir.
* **Décor :** Mur brutaliste de nuit, éclairage néon vert tamisé.
* **Prompt :**
  > `A widescreen (16:9) premium streetwear fashion photograph. A male model wearing a high-end minimalist oversized sage green hoodie and matching clean black jogger pants. Shot outdoors at night next to a dark concrete brutalist wall illuminated by a soft neon green light. The overall mood is moody, matching the deep forest green (#031710) website theme. Wide shot, camera pulled back, model centered with 25% clean empty space on the left and right margins.`

#### Hero 2 : Casual Chic Contemporain (Femme)
* **Vêtement :** Veste zippée vert forêt, pantalon large crème.
* **Décor :** Studio moderne, blocs géométriques, contrastes de lumière marqués.
* **Prompt :**
  > `A widescreen (16:9) modern casual fashion campaign. A female model wearing a premium dark forest green zip-up track jacket and wide-leg cream pants, posing leaning against a minimal dark green column. Dramatic low-key studio lighting with high contrast. The color scheme is deep forest green, black, and beige. Wide shot, zoomed out, centering the model with plenty of blank negative space on the left and right sides.`

#### Hero 3 : Duo Chic & Relax (Mélange Habillé / Hoodie)
* **Vêtement :** Hoodie noir sous manteau droit, sweat sauge et pantalon tailleur.
* **Décor :** Loft moderne en béton, lumière de fin de journée.
* **Prompt :**
  > `A widescreen (16:9) lifestyle fashion editorial showing a male model in a minimal black hoodie under a structured coat, and a female model in an oversized sage green sweatshirt and tailored trousers. They are standing in a modern concrete loft. A dark forest green textured wall is behind them, with soft golden hour light coming from a side window. Wide angle shot, models centered with ample dark green negative space on the margins.`

---

### 📦 Catégories (Format Carré 1:1)

#### 👩‍💼 Women (`cat_women.png`)
> **Prompt :**
> `A square (1:1) editorial fashion photo of a female model facing sideways in a sharp, structured cream blazer. Solid deep forest green (#031710) studio background. Minimalist aesthetic, clean shadows, high contrast, elegant and luxury clothing vibe.`

#### 👨‍💼 Men (`cat_men.png`)
> **Prompt :**
> `A square (1:1) studio fashion shot of a male model wearing a premium minimalist dark grey utility jacket and black t-shirt. Standing in front of a solid dark forest green wall. Clean lighting, modern look, focusing on fabric texture.`

#### 🧥 Unisex (`cat_unisex.png`)
> **Prompt :**
> `A square (1:1) minimalist fashion campaign photograph. A male model and a female model standing close together, wearing matching oversized sage green hoodies and clean black joggers. Solid deep forest green (#031710) studio background. Soft natural light, high-end editorial look, highlighting a gender-neutral, modern streetwear capsule collection.`

#### 🕶️ Accessories (`cat_accessories.png`)
> **Prompt :**
> `A square (1:1) high-end flat lay product photography. A premium black cap, a minimalist sleek black backpack, a clean silver chain necklace, a matching silver bracelet, dark luxury sunglasses, and an elegant glass perfume bottle. All items are arranged artistically on a dark concrete surface. The background is a solid deep forest green (#031710) wall. Low-key dramatic lighting, sharp shadows, luxury brand catalog aesthetic.`

#### 📦 Shop All / Tout Voir (`cat_all.png`)
> **Prompt :**
> `A square (1:1) high-fashion catalog cover photo representing a complete brand collection. A group composition: a female model in a sharp structured cream blazer, a male model in a dark grey utility jacket, and a model in a sage green unisex hoodie. Nearby on a concrete block, a black backpack, a cap, and sunglasses are artistically displayed. Solid deep forest green (#031710) studio background. Soft dramatic lighting, luxury clothing store aesthetic, clean and cohesive look.`

---

*Aura Shop v2.0 — Développé dans le cadre d'un projet universitaire e-commerce.*
