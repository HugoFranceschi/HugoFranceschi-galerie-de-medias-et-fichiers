# Galerie de médias et fichiers

## Déploiement sous Linux (Apache)

Le `DocumentRoot` doit pointer vers le dossier `public/`, jamais vers la racine du projet.

```apache
<VirtualHost *:80>
    ServerName galerie.local
    DocumentRoot /var/www/galerie/public

    <Directory /var/www/galerie/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Identifiants de la base (à adapter)
    SetEnv DB_HOST 127.0.0.1
    SetEnv DB_NAME galeriedephoto
    SetEnv DB_USER galerie
    SetEnv DB_PASSWORD "mot_de_passe"

    # Laisser vide si le site est à la racine du domaine.
    # Mettre /galerie si l'URL est https://exemple.com/galerie/
    SetEnv BASE_URL ""
</VirtualHost>
```

Avec PHP-FPM, les variables `SetEnv` ne sont pas toujours transmises à PHP.
Dans ce cas, ajoute-les plutôt dans le pool FPM :

```ini
env[DB_HOST] = 127.0.0.1
env[DB_NAME] = galeriedephoto
env[DB_USER] = galerie
env[DB_PASSWORD] = mot_de_passe
env[BASE_URL] =
```

### Alternative : `src/config/local.php`

Si les variables d'environnement ne sont pas disponibles, copier
`src/config/local.example.php` en `src/config/local.php`, renseigner les valeurs,
puis limiter les droits :

```bash
chmod 600 src/config/local.php
```

Ce fichier est ignoré par git et doit rester hors de `public/`.

Puis :

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2

# Dépendances PHP
composer install --no-dev --optimize-autoloader

# Droits d'écriture pour les uploads
sudo chown -R www-data:www-data public/assets/Img
sudo chmod -R 775 public/assets/Img
```

Le fichier `public/.htaccess` force HTTPS, ajoute les en-têtes de sécurité, redirige les URLs vers `index.php` et empêche le listage des dossiers.

## Déploiement sur Alwaysdata

### 1. Créer la base MySQL

Dans l'administration Alwaysdata : **Bases de données > MySQL > Ajouter une base**.

Noter les valeurs exactes affichées :

- hôte : `mysql-[compte].alwaysdata.net` ;
- base : `[compte]_galerie` ;
- utilisateur : `[compte]` ou `[compte]_galerie` ;
- mot de passe : celui choisi.

Toujours utiliser l'hôte fourni par Alwaysdata, pas `localhost`.

### 2. Envoyer les fichiers

Activer SSH/SFTP dans **Accès distant > SSH/SFTP**, puis se connecter :

```bash
ssh [compte]@ssh-[compte].alwaysdata.net
mkdir -p ~/galerie
```

Envoyer le projet dans `/home/[compte]/galerie` avec FileZilla ou WinSCP (SFTP, port 22). Ne pas envoyer `vendor/` ni `.git/` : Composer installera les dépendances sur le serveur. Activer l'affichage des fichiers cachés pour que `public/.htaccess` soit bien envoyé.

> Le plan gratuit Alwaysdata a un quota d'environ 100 Mo (à vérifier selon ton offre). Les médias actuels de `public/assets/Img` pèsent environ 135 Mo : ils ne rentrent pas. Utiliser une offre payante ou ne pas envoyer les anciens médias.

### 3. Installer les dépendances

```bash
cd ~/galerie
composer install --no-dev --optimize-autoloader
chmod -R u+rwX,go+rX .
mkdir -p public/assets/Img
chmod -R u+rwX public/assets/Img
```

Alwaysdata exécute PHP avec le compte utilisateur : inutile de faire `chown www-data`.

### 4. Importer la base

> **Important :** sur une base déjà utilisée, ne jamais réimporter `galeriedephoto.sql` : cela écraserait les comptes et les médias existants. Le dump ne sert qu'à une installation neuve.

Dans **Bases de données > MySQL > phpMyAdmin**, sélectionner la base et importer `galeriedephoto.sql`.

Le dump contient les anciens comptes et médias. Pour repartir de zéro :

```sql
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE images;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;
```

Sinon, envoyer aussi les fichiers médias correspondants.

### 5. Créer le site PHP

Dans **Web > Sites** :

- si un site existe déjà sur `[compte].alwaysdata.net`, le modifier ; sinon **Ajouter un site** ;
- adresse : `[compte].alwaysdata.net` ou ton domaine ;
- type : **PHP** ;
- répertoire racine / path : `galerie/public` (chemin relatif à `/home/[compte]`) ;
- version PHP : 8.5 (ou 8.4) ;
- SSL : activer Let's Encrypt, puis forcer HTTPS.

Le chemin `galerie/public` est un chemin **disque**, pas une URL. Le site est donc servi à la racine `https://[compte].alwaysdata.net/`. Laisser `BASE_URL` vide. Si le site est configuré en Alias dans un sous-dossier, comme `/galerie`, définir `BASE_URL=/galerie` (variable d'environnement ou `src/config/local.php`).

Pour l'installation actuelle, le projet est dans `~/www/HugoFranceschi-galerie-de-medias-et-fichiers` et l'alias `/galerie` pointe vers son dossier `public`. Le fichier `~/www/.htaccess` redirige `/` et `/index.html` vers `/galerie/`, et `~/www/index.html` sert de page de secours.

### 6. Variables d'environnement

Dans la configuration du site, champ **Environment / Variables d'environnement** :

```text
DB_HOST=mysql-[compte].alwaysdata.net DB_NAME=[compte]_galerie DB_USER=[compte]_galerie DB_PASSWORD=motdepasse APP_DEBUG=0
```

Utiliser les valeurs exactes de l'écran MySQL. Choisir un mot de passe sans espace pour ce champ. Si le site est réellement dans un sous-dossier URL, ajouter `BASE_URL=/sous-dossier`.

### 7. Tester

- `https://[compte].alwaysdata.net/` doit rediriger vers `/galerie/` (ou vers `/login` si le site est à la racine) ;
- `/register` crée un compte, `/login` connecte et `/logout` (en POST) déconnecte ;
- l'envoi accepte plusieurs fichiers à la fois (20 maximum par envoi) ;
- vérifier que HTTP redirige vers HTTPS et que les en-têtes de sécurité sont présents ;
- en cas d'erreur :
  - logs PHP : `/home/[compte]/admin/logs/php/php.log` ;
  - logs Apache/.htaccess : `/home/[compte]/admin/logs/apache/apache.log` ;
  - logs HTTP : `/home/[compte]/admin/logs/http/`.

### 8. Après l'import

- supprimer `galeriedephoto.sql` du serveur ;
- ne jamais placer le mot de passe de la base dans `public/` ;
- garder `APP_DEBUG=0` en production.

## Sécurité

- jeton CSRF vérifié sur tous les formulaires POST (connexion, inscription, envoi, suppression, déconnexion) ;
- sessions régénérées après connexion et cookie `HttpOnly`, `Secure` et `SameSite=Lax` ;
- limitation à 5 échecs de connexion par session, puis blocage de 15 minutes ;
- envois validés par extension et type MIME réel, renommés aléatoirement et stockés hors de tout exécutable PHP ;
- en-têtes CSP, HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` et `Permissions-Policy` ;
- `src/`, `vendor/`, les configurations et les dumps SQL ne sont pas accessibles depuis le web.

## Remarques

- `BASE_URL` vaut `''` par défaut (site à la racine). Pour un sous-dossier, définir `BASE_URL=/galerie` : les liens, les assets et les redirections passent tous par `baseUrl()`.
- En local uniquement, `APP_DEBUG=1` permet d'afficher les erreurs PHP. En production, laisser la variable non définie.
- Les chemins PHP sont calculés avec `__DIR__` et `DIRECTORY_SEPARATOR` dans `src/config/config.php` : ils fonctionnent sous Linux comme sous Windows.
