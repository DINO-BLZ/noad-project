# NOAD

Boutique en ligne de mode/streetwear pour le marché algérien, avec un système de **drops** limités (produits en édition limitée, ouverts par whitelist) — bâtie sur Laravel 13.

## Stack technique

- **Backend** : Laravel 13, PHP 8.3+
- **Base de données** : SQLite en local (par défaut), MySQL 8 en option (nécessaire pour le test de verrouillage pessimiste, voir plus bas)
- **Frontend** : Blade + Vite

## Prérequis

- PHP 8.3+ avec les extensions `pdo_sqlite` (et `pdo_mysql` si tu utilises MySQL)
- Composer
- Node.js 20+ et npm
- Docker (pour les services complémentaires si nécessaire)

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

### Frontend

```bash
npm run dev
```

Ou pour un build de production :

```bash
npm run build
```

### Créer un compte admin

```bash
php artisan app:create-admin {email} {name}
```

### Worker de queue

Les e-mails sont envoyés via la file de traitement (`QUEUE_CONNECTION=database`).
Pour qu'ils soient réellement délivrés en local ou en environnement de prod, il faut laisser un worker actif en permanence :

```bash
php artisan queue:work --tries=3
```

En production, il est recommandé d'utiliser Supervisor pour garder ce worker constamment en vie.

### Lancer l'application

```bash
php artisan serve
```

L'app est accessible sur `http://localhost:8000`.

## Planificateur

Les jobs suivants sont enregistrés dans le scheduler Laravel :

- chaque minute : `drops:notify-opened`
- chaque minute : `drops:expire-pending-whitelists`
- chaque jour à 03:30 : `carts:purge-old-guests`
- chaque jour à 04:00 : `auth:purge-old-password-reset-tokens`
- chaque jour à 07:00 : `orders:send-daily-digest` (destinataires dans `DIGEST_MAIL_RECIPIENTS`)

En production, ajoute une crontab :

```bash
* * * * * cd /chemin/vers/noad && php artisan schedule:run >> /dev/null 2>&1
```

Sans cette ligne, les commandes existent mais ne se déclenchent pas toutes seules.

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

Chaque push/PR (toutes branches) déclenche automatiquement, via GitHub Actions (`.github/workflows/tests.yml`) :
- vérification du style (Pint)
- suite de tests SQLite
- suite de tests MySQL (verrouillage pessimiste, avec un vrai conteneur MySQL)

## Notes

- Le paiement par carte (CIB/Edahabia) est temporairement désactivé — seul le paiement à la livraison (COD) est disponible. Voir le commentaire dans `app/Http/Requests/CheckoutRequest.php`.