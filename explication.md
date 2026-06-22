# Guide de Connexion à la Base de Données - AURA Shop (Soutenance de Fin d'Études)

Ce document explique l'architecture et la logique technique mises en œuvre pour connecter l'application **AURA Shop** à sa base de données relationnelle **PostgreSQL** hébergée sur le cloud. L'application est conçue pour fonctionner exclusivement en ligne (Production / Cloud).

---

## 1. Architecture Globale et Technologies

La connexion repose sur trois piliers technologiques standards et professionnels :
*   **Système de Gestion de Base de Données (SGBD) :** PostgreSQL (idéal pour la gestion des relations complexes entre utilisateurs, produits, paniers et commandes en ligne).
*   **Technologie de Connexion :** **PDO (PHP Data Objects)** avec le pilote `pgsql`. PDO est l'API moderne recommandée en PHP, offrant une couche d'abstraction sécurisée et performante.
*   **Gestion de Configuration :** Variables d'environnement pour une séparation stricte entre le code source et les identifiants sensibles de la base de données.

---

## 2. Analyse Détaillée du Code de Connexion (`api/config.php`)

La logique de connexion est centralisée dans le fichier global de configuration [config.php](file:///home/bens/Documents/my%20website/version%20presque%20fin/aurashop-main/api/config.php).

### Étape 1 : Récupération de la configuration de production
Le code commence par récupérer l'URI de connexion de la base de données en ligne :
```php
$dbUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);
```
La variable d'environnement `DATABASE_URL` injectée par la plateforme d'hébergement cloud (par exemple sous forme `postgres://user:password@host:port/dbname`) est automatiquement lue.

### Étape 2 : Analyse de l'URI et Construction du DSN (Data Source Name)
Pour se connecter à l'infrastructure cloud, l'URI de connexion est analysée dynamiquement à l'aide de la fonction PHP `parse_url()` :
```php
if (!$dbUrl) {
    throw new PDOException("Database URL is not defined in the environment variables.");
}

$parsedUrl = parse_url($dbUrl);
$host = $parsedUrl['host'] ?? '';
$port = $parsedUrl['port'] ?? '5432';
$user = rawurldecode($parsedUrl['user'] ?? '');
$pass = rawurldecode($parsedUrl['pass'] ?? '');
$dbname = ltrim($parsedUrl['path'] ?? '', '/');

// Configuration stricte sécurisée (SSL requis pour les échanges cloud)
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
```

### Étape 3 : Instanciation sécurisée de PDO avec options robustes
La connexion est établie à l'intérieur d'un bloc `try-catch` pour capturer proprement les erreurs de liaison et éviter d'exposer les identifiants en cas d'échec :
```php
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Lève des exceptions en cas d'erreur SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Récupère les données sous forme de tableaux associatifs
        PDO::ATTR_EMULATE_PREPARES => false,          // Utilise de vraies requêtes préparées (sécurité accrue)
    ]);
} catch (PDOException $e) {
    $pdo = null;
    $pdoError = $e->getMessage();
}
```

---

## 3. Justifications Académiques (Points clés pour le jury)

Lors de la soutenance, vous pouvez valoriser les choix techniques suivants auprès du jury :

1.  **Sécurité contre les Injections SQL :**
    Grâce à l'utilisation systématique de **requêtes préparées** (via `prepare()` et `execute()`), les entrées utilisateur ne sont jamais directement concaténées dans les chaînes SQL. C'est la protection standard absolue contre les injections.
2.  **Sécurité des Identifiants (Twelve-Factor App) :**
    L'utilisation de la variable d'environnement `DATABASE_URL` évite d'écrire en dur les mots de passe de la base de données dans le code source. Cela évite les fuites accidentelles d'identifiants lors d'un push sur un dépôt Git public (GitHub).
3.  **Architecture Cloud & Sécurité des Transmissions (SSL) :**
    La configuration force l'utilisation de `sslmode=require`. Toutes les requêtes et les données échangées entre l'application et la base de données PostgreSQL hébergée sur le cloud sont chiffrées de bout en bout.
4.  **Options d'Optimisation PDO :**
    *   `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` : Permet l'utilisation de blocs `try/catch` pour gérer proprement les transactions et annuler les requêtes en cas de bug (avec `$pdo->rollBack()`).
    *   `PDO::ATTR_EMULATE_PREPARES => false` : Force le SGBD à compiler la requête séparément des paramètres, ce qui garantit qu'aucune donnée utilisateur ne peut être exécutée comme du code SQL.
