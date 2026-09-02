# NOAD

Boutique en ligne de mode/streetwear pour le marché algérien, avec un système de **drops** limités (produits en édition limitée, ouverts par whitelist) — bâtie sur Laravel 13.

## Stack technique

- **Backend** : Laravel 13, PHP 8.4+
- **Base de données** : SQLite en local (par défaut), MySQL 8 en option (nécessaire pour le test de verrouillage pessimiste, voir plus bas)
- **Recherche** : Elasticsearch via Laravel Scout
- **Frontend** : Blade + Vite

## Prérequis

- PHP 8.4+ avec les extensions `pdo_sqlite` (et `pdo_mysql` si tu utilises MySQL)
- Composer
- Node.js 20+ et npm
- Docker (pour Elasticsearch)

## Installation

```bash
git clone https://github.com/DINO-BLZ/noad-project.git
cd noad-project

composer install
npm install

cp .env.example .env
php artisan key:generate
```

### Base de données (SQLite, par défaut)

```bash
touch database/database.sqlite
php artisan migrate --seed
```

`DB_CONNECTION=sqlite` est déjà le défaut dans `.env.example` — aucune configuration supplémentaire n'est nécessaire pour démarrer en local.

### Recherche (Elasticsearch)

Le catalogue produit est indexé via Laravel Scout + Elasticsearch. Démarre le conteneur :

```bash
docker compose up -d
```

Elasticsearch est alors disponible sur `localhost:9200` (config par défaut dans `config/elastic.client.php`, aucune variable `.env` à ajouter).

Indexe les produits existants :

```bash
php artisan scout:import "App\Models\Product"
```

### Frontend

```bash
npm run dev
```

Ou pour un build de production :

```bash
npm run build
```

### Créer un compte admin

Aucune commande dédiée n'existe pour l'instant. Passe par Tinker :

```bash
php artisan tinker
```

```php
$user = \App\Models\User::find(1); // ou User::where('email', 'toi@example.com')->first()
$user->is_admin = true;
$user->save();
```

### Lancer l'application

```bash
php artisan serve
```

L'app est accessible sur `http://localhost:8000`.

## Tests

La suite principale tourne en SQLite en mémoire, sans dépendance externe :

```bash
php artisan test
```

Un test spécifique (`CheckoutPessimisticLockingMysqlTest`) vérifie le verrouillage pessimiste des lignes en conditions réelles MySQL et nécessite une vraie base MySQL — il est automatiquement ignoré (`skipped`) avec la suite par défaut. Pour l'exécuter, avec un MySQL local démarré et une base `noad_test_mysql` créée :

```bash
vendor/bin/phpunit -c phpunit.mysql.xml
```

### Style de code

```bash
vendor/bin/pint
```

## Intégration continue

Chaque push/PR sur `main` déclenche automatiquement, via GitHub Actions (`.github/workflows/tests.yml`) :
- vérification du style (Pint)
- suite de tests SQLite
- suite de tests MySQL (verrouillage pessimiste, avec un vrai conteneur MySQL)

## Notes

- Le paiement par carte (CIB/Edahabia) est temporairement désactivé — seul le paiement à la livraison (COD) est disponible. Voir le commentaire dans `app/Http/Requests/CheckoutRequest.php`.