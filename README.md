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
Le fuseau applicatif est `Africa/Algiers`. Pour MySQL/MariaDB, la session DB utilise `DB_TIMEZONE=+01:00` afin de garder cohérents les timestamps sans réécrire les données existantes.

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
php artisan queue:work --tries=3 --backoff=5
```

En production, il est recommandé d'utiliser Supervisor pour garder ce worker constamment en vie.
Les échecs sont conservés dans `failed_jobs` (`QUEUE_FAILED_DRIVER=database-uuids`). Pour les inspecter et relancer un job :

```bash
php artisan queue:failed
php artisan queue:retry <uuid>
php artisan queue:retry all
```

Surveille les jobs échoués et la disponibilité du worker; le job d'ouverture de Drop réessaie trois fois. Après l'échec final, il n'est pas relancé automatiquement par le scheduler; utilise `queue:retry <uuid>` après avoir corrigé la cause.

### Variables d'environnement de production

Avant le déploiement, configure au minimum `APP_ENV=production`, `APP_DEBUG=false`, une `APP_KEY` privée, `APP_URL` en HTTPS, `APP_TIMEZONE=Africa/Algiers`, l'accès MySQL (`DB_*`, `DB_TIMEZONE=+01:00`), un fournisseur SMTP (`MAIL_*`), `QUEUE_CONNECTION=database`, `CACHE_STORE`, `SESSION_DRIVER`, `SESSION_SECURE_COOKIE=true`, `FILESYSTEM_DISK` et l'accès Elasticsearch (`SCOUT_DRIVER=elastic`, `ELASTIC_HOST`). Ne publie jamais le fichier `.env` ni ses valeurs dans les logs ou le dépôt.

L'exemple utilise des valeurs locales sans secret. En production, `APP_DEBUG` doit rester désactivé et le niveau de logs doit être adapté à l'exploitation.

### Préparation du déploiement

Après configuration des services et sauvegarde de la base, le déploiement standard comprend :

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ne lance pas `migrate:fresh` sur une base contenant des données. Vérifie ensuite `/up`, `php artisan queue:failed`, `php artisan schedule:list` et `/sitemap.xml`.

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
- chaque jour à 03:00 : `search:reindex-products`
- chaque jour à 07:00 : `orders:send-daily-digest` (destinataires dans `DIGEST_MAIL_RECIPIENTS`)

Ces heures sont interprétées en `Africa/Algiers`.

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

Les tests `*MysqlTest` vérifient les verrous et la concurrence en conditions réelles MySQL; ils nécessitent une vraie base MySQL et sont automatiquement ignorés avec la suite SQLite. Pour les exécuter, avec un MySQL local démarré et une base `noad_test_mysql` créée :

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
- Le total enregistré et affiché couvre les articles; les frais de livraison sont explicitement indiqués comme à confirmer avant expédition. Aucun tarif n'est codé tant qu'une règle commerciale de livraison n'est pas décidée.
- Les inscriptions newsletter sont enregistrées dans `newsletter_subscribers`; la réponse distingue une nouvelle inscription d'une adresse déjà inscrite.
- Le sitemap public est disponible sur `/sitemap.xml`; les pages d'administration portent `noindex, nofollow`.