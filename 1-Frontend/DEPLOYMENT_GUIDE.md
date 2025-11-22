# Guide de Déploiement - RefCheck Interface

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Déploiement](#déploiement)
5. [Troubleshooting](#troubleshooting)

---

## 🔧 Prérequis

### Serveur

- **PHP** >= 7.4
- **Serveur Web** : Apache ou Nginx
- **cURL** : Pour les appels API
- **Espace disque** : Minimum 1GB

### Accès

- Accès SSH/FTP au serveur
- Permissions d'écriture pour les dossiers uploads et logs
- Port disponible (généralement 80 ou 443)

### Backend (Python)

- API backend démarrée sur `http://localhost:5000`
- Modules actifs :
  - 2-Extraction
  - 3-Parsing
  - 4-Retrieve
  - 5-Compare

---

## 💻 Installation

### Étape 1 : Copier les fichiers

```bash
# Sur votre serveur
cd /var/www/html  # ou votre répertoire web

# Copier le dossier Frontend
cp -r 1-Frontend/exemple_biblio /var/www/html/refcheck

# Créer les dossiers nécessaires
mkdir -p /var/www/html/refcheck/uploads
mkdir -p /var/www/html/refcheck/logs
```

### Étape 2 : Configurer les permissions

```bash
# Permissions en lecture/écriture pour uploads et logs
chmod -R 755 /var/www/html/refcheck
chmod -R 777 /var/www/html/refcheck/uploads
chmod -R 777 /var/www/html/refcheck/logs

# Appartenance au serveur web
sudo chown -R www-data:www-data /var/www/html/refcheck
```

### Étape 3 : Vérifier l'installation PHP

```bash
# Tester les extensions requises
php -m | grep curl
php -m | grep json

# Si curl manque (Debian/Ubuntu)
sudo apt-get install php-curl

# Si json manque (Debian/Ubuntu)
sudo apt-get install php-json

# Redémarrer Apache
sudo systemctl restart apache2
```

---

## ⚙️ Configuration

### Fichier config.php

Éditez `/var/www/html/refcheck/config.php` :

```php
// Mode debug (désactiver en production!)
define('DEBUG_MODE', false); // true pour dev, false pour prod

// URL du backend Python
define('BACKEND_URL', 'http://localhost:5000/api');

// Environnement
define('ENVIRONMENT', 'production'); // ou 'development'

// Taille maximale de fichier
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
```

### Configuration Apache

Créez `/etc/apache2/sites-available/refcheck.conf` :

```apache
<VirtualHost *:80>
    ServerName refcheck.yourdomain.com
    DocumentRoot /var/www/html/refcheck

    <Directory /var/www/html/refcheck>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Rediriger uploads vers uploads/
    Alias /uploads /var/www/html/refcheck/uploads

    # Désactiver l'accès aux fichiers sensibles
    <FilesMatch "config.php|\.env">
        Require all denied
    </FilesMatch>

    # Logs d'accès/erreur
    ErrorLog ${APACHE_LOG_DIR}/refcheck-error.log
    CustomLog ${APACHE_LOG_DIR}/refcheck-access.log combined
</VirtualHost>
```

### Activation du site

```bash
# Activer le site
sudo a2ensite refcheck.conf

# Vérifier la configuration
sudo apache2ctl configtest

# Si OK, redémarrer Apache
sudo systemctl restart apache2
```

### Configuration Nginx (alternative)

```nginx
server {
    listen 80;
    server_name refcheck.yourdomain.com;

    root /var/www/html/refcheck;
    index index.php;

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Bloquer l'accès aux fichiers sensibles
    location ~ /config\.php {
        deny all;
    }

    # Logs
    access_log /var/log/nginx/refcheck-access.log;
    error_log /var/log/nginx/refcheck-error.log;
}
```

---

## 🚀 Déploiement

### Version 1 : Déploiement local (dev)

```bash
# Cloner le repo
git clone https://github.com/pablogit/hackademia-2025.git

# Aller au dossier
cd hackademia-2025/1-Frontend/exemple_biblio

# Démarrer un serveur PHP simple
php -S localhost:8000

# Accéder via navigateur
# http://localhost:8000/welcome_refcheck.php
```

### Version 2 : Déploiement avec Docker

Créez `Dockerfile` :

```dockerfile
FROM php:7.4-apache

# Installer les extensions requises
RUN docker-php-ext-install curl json

# Copier les fichiers
COPY ./ /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Activer mod_rewrite
RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
```

Build et run :

```bash
# Builder l'image
docker build -t refcheck:latest .

# Lancer le container
docker run -d \
  -p 80:80 \
  -e BACKEND_URL="http://backend:5000/api" \
  --name refcheck \
  --link backend:backend \
  refcheck:latest
```

### Version 3 : Déploiement en production

```bash
# 1. Cloner en tag spécifique
git clone -b v1.0 https://github.com/pablogit/hackademia-2025.git

# 2. Configurer
cp .env.example .env
nano .env  # Éditer les variables

# 3. Déployer
./deploy.sh

# 4. Vérifier
curl https://refcheck.yourdomain.com/welcome_refcheck.php
```

---

## 🔍 Vérification

### Test 1 : Frontend accessible

```bash
curl -I http://localhost:8000/welcome_refcheck.php

# Doit retourner HTTP 200
```

### Test 2 : Backend API

```bash
curl http://localhost:5000/api/health

# Doit retourner une réponse valide
```

### Test 3 : Upload fonctionnel

```bash
# Tester l'upload
curl -F "file=@test.pdf" http://localhost:8000/api_integration.php
```

### Test 4 : Permissions

```bash
ls -la /var/www/html/refcheck/uploads/
ls -la /var/www/html/refcheck/logs/

# Doivent avoir les permissions 777
```

---

## 📊 Monitoring

### Logs à surveiller

```bash
# Logs Apache
tail -f /var/log/apache2/refcheck-error.log

# Logs PHP
tail -f /var/www/html/refcheck/logs/error.log

# Logs système
tail -f /var/log/syslog | grep refcheck
```

### Performance

```bash
# Vérifier l'utilisation disque
df -h /var/www/html/refcheck

# Nettoyer les anciens uploads
find /var/www/html/refcheck/uploads -type f -mtime +7 -delete
```

---

## 🐛 Troubleshooting

### Problème : "Permission denied" uploads

```bash
# Solution
sudo chown -R www-data:www-data /var/www/html/refcheck/uploads
sudo chmod -R 777 /var/www/html/refcheck/uploads
```

### Problème : "Cannot connect to backend"

```bash
# Vérifier que le backend s'exécute
curl http://localhost:5000/api

# Vérifier le firewall
sudo ufw allow 5000

# Éditer config.php avec la bonne URL
BACKEND_URL = 'http://votre-ip:5000/api'
```

### Problème : "404 Not Found"

```bash
# Vérifier que les fichiers existent
ls -la /var/www/html/refcheck/refcheck.php

# Vérifier la configuration Apache
sudo apache2ctl configtest

# Vérifier les alias
sudo apache2ctl -S
```

### Problème : Fichiers volumineux (timeout)

```php
// Éditer config.php
define('API_TIMEOUT', 60); // Augmenter de 30 à 60 secondes
```

### Problème : Accès refusé

```bash
# Vérifier les permissions
sudo chmod -R 755 /var/www/html/refcheck

# Vérifier l'ownership
sudo chown -R www-data:www-data /var/www/html/refcheck
```

---

## 🔐 Sécurité

### Checklist de sécurité

- [ ] Désactiver DEBUG_MODE en production
- [ ] Configurer HTTPS (certificat SSL)
- [ ] Masquer les fichiers sensibles (.env, config.php)
- [ ] Valider les uploads (type, taille)
- [ ] Rate limiting sur API
- [ ] Logs d'audit activés
- [ ] Sauvegardes régulières

### Fichier .htaccess (Apache)

```apache
# Bloquer l'accès aux fichiers sensibles
<FilesMatch "config\.php|\.env|\.log">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Activer la compression
mod_gzip_on Yes

# Sécurité headers
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
```

---

## 📈 Scaling

Pour supporter plus de trafic :

1. **Cache** : Ajouter Redis pour les résultats
2. **Queue** : Utiliser une queue (RabbitMQ) pour les analyses
3. **Load Balancer** : Nginx load balancer
4. **Database** : Ajouter PostgreSQL pour persistence
5. **CDN** : Servir les assets via CDN

---

## 📝 Checklist avant production

- [ ] Backend API déployée et testée
- [ ] Certificat SSL installé
- [ ] Base de données configurée (si utilisée)
- [ ] Sauvegardes activées
- [ ] Monitoring en place
- [ ] Logs configurés
- [ ] Permissions correctes
- [ ] Tests de charge effectués
- [ ] Documentation mise à jour
- [ ] Équipe formée

---

## 🆘 Support

Pour l'aide :

1. Vérifier les logs : `/var/www/html/refcheck/logs/error.log`
2. Tester la configuration : `php -l config.php`
3. Tester la connectivité : `curl http://backend:5000/api`
4. Contacter : support@hackademia.edu

---

**Créé pour HackademIA 2025** | v1.0
