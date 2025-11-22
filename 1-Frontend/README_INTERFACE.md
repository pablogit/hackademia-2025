# RefCheck - Interface Web du Vérificateur de Références IA

![RefCheck](https://img.shields.io/badge/Version-1.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![License](https://img.shields.io/badge/License-MIT-green)

## 📌 À propos

**RefCheck** est une interface web moderne pour vérifier et analyser les hallucinations de références bibliographiques dans les textes générés par l'intelligence artificielle.

L'interface utilise une mise en page **split-view** intuitive permettant aux utilisateurs de :

- 📤 Soumettre des fichiers PDF ou du texte brut
- ✅ Valider les entrées en temps réel
- 📊 Visualiser les résultats avec un code couleur sémantique
- 🧠 Obtenir des justifications générées par IA

---

## 🎨 Caractéristiques

### Interface Utilisateur

- ✨ Design moderne avec gradient et animations fluides
- 📱 Entièrement responsive (desktop, tablet, mobile)
- 🎯 Expérience utilisateur intuitive et guidée
- ♿ Accessibilité optimisée

### Fonctionnalités

- 📁 Double mode d'entrée (Upload PDF + Texte brut)
- ✔️ Validation instantanée avec feedback visuel
- 🎨 Code couleur intelligent (Vert/Jaune/Rouge)
- 🔍 Détails expansibles pour chaque référence
- 📈 Statistiques en temps réel

### Technologie

- 🐘 **Backend** : PHP 7.4+
- 🎭 **Frontend** : HTML5 + CSS3 + Vanilla JavaScript
- 🔗 **API** : REST avec Python backend
- 📦 **Architecture** : Modulaire et extensible

---

## 📂 Structure du projet

```
1-Frontend/
├── REFCHECK_INTERFACE.md          # Documentation interface
├── DEPLOYMENT_GUIDE.md            # Guide de déploiement
├── exemple_biblio/
│   ├── refcheck.php               # 🎯 Interface principale
│   ├── welcome_refcheck.php       # 🏠 Page d'accueil
│   ├── api_integration.php        # 🔗 Intégration backend
│   ├── config.php                 # ⚙️ Configuration
│   ├── header.php                 # 📄 En-tête
│   ├── footer.php                 # 📄 Pied de page
│   ├── tests.html                 # 🧪 Suite de tests
│   ├── css/
│   │   └── refcheck.css           # 🎨 Styles principaux
│   └── js/
│       └── refcheck-utils.js      # 🛠️ Utilitaires JS
└── README.md                      # Ce fichier
```

---

## 🚀 Démarrage rapide

### Prérequis

- PHP 7.4 ou supérieur
- Serveur web (Apache/Nginx)
- cURL activé
- Backend Python démarré

### Installation (5 minutes)

```bash
# 1. Cloner le repo
git clone https://github.com/pablogit/hackademia-2025.git
cd hackademia-2025/1-Frontend/exemple_biblio

# 2. Créer les dossiers
mkdir -p uploads logs

# 3. Donner les permissions
chmod -R 777 uploads logs

# 4. Démarrer le serveur PHP
php -S localhost:8000

# 5. Ouvrir dans le navigateur
# http://localhost:8000/welcome_refcheck.php
```

### Configuration (optionnel)

Éditer `config.php` :

```php
// Mode debug
define('DEBUG_MODE', true); // false en production

// URL du backend
define('BACKEND_URL', 'http://localhost:5000/api');
```

---

## 💻 Utilisation

### Pour l'utilisateur final

1. **Accéder au site** : `welcome_refcheck.php`
2. **Choisir l'entrée** : PDF ou texte
3. **Soumettre** : Upload/coller les données
4. **Analyser** : Cliquer sur "Lancer l'Analyse"
5. **Explorer** : Cliquer sur "+" pour les détails
6. **Comprendre** : Lire la justification IA

### Pour le développeur

#### Appels API basiques

```javascript
// Validation
const validation = ValidationUtils.validatePDF(file);

// Affichage
UIUtils.showValidation("container", true, "Message");

// Formatage
const formatted = FormatUtils.formatScore(85);

// Stockage
StorageUtils.set("key", { data: "value" });
```

#### Intégration backend

```php
// Utiliser api_integration.php
$api = new RefCheckAPI(BACKEND_URL, UPLOAD_DIR);
$result = $api->processPDF($file_path);
```

---

## 📊 Format des résultats

L'interface attend un JSON structuré comme suit :

```json
{
  "success": true,
  "data": [
    {
      "score": 95,
      "title": "Smith, J. (2021). Title",
      "authors": "John Smith",
      "year": 2021,
      "journal": "Journal Name",
      "justification": "La référence existe et tous les détails correspondent."
    }
  ],
  "stats": {
    "total": 10,
    "excellent": 7,
    "uncertain": 2,
    "hallucination": 1
  }
}
```

---

## 🧪 Tests

Accéder à la suite de tests :

```
http://localhost:8000/tests.html
```

La suite inclut :

- ✅ Tests de validation
- ✅ Tests UI/DOM
- ✅ Tests de formatage
- ✅ Tests de storage

---

## 🔧 Configuration avancée

### Nginx

```nginx
server {
    listen 80;
    root /var/www/html/refcheck;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Docker

```dockerfile
FROM php:7.4-apache
RUN docker-php-ext-install curl json
COPY ./ /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
```

### Variables d'environnement

```bash
export BACKEND_URL="http://localhost:5000/api"
export DB_HOST="localhost"
export DB_USER="refcheck"
export DB_PASS="password"
```

---

## 📈 Performance

### Optimisations

- Cache des assets (CSS/JS)
- Lazy loading des résultats
- Compression gzip activée
- Nettoyage automatique des uploads

### Limites

- Fichiers PDF : Max 50MB
- Texte brut : Max 1MB
- Références : Max 1000
- Timeout API : 30 secondes

---

## 🔐 Sécurité

### Mesures de sécurité

- ✅ Validation stricte des fichiers
- ✅ Sanitization des entrées
- ✅ Headers de sécurité
- ✅ Blocage des fichiers sensibles
- ✅ Rate limiting (recommandé)

### Considérations HTTPS

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Strict Transport Security
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

---

## 🐛 Troubleshooting

### Erreur : "Permission denied"

```bash
sudo chown -R www-data:www-data ./
chmod -R 755 ./
chmod -R 777 uploads/ logs/
```

### Erreur : "Cannot connect to backend"

```bash
# Vérifier que le backend s'exécute
curl http://localhost:5000/api

# Éditer config.php
define('BACKEND_URL', 'http://votre-ip:5000/api');
```

### Erreur : "File too large"

```php
// config.php
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB
```

---

## 📚 Documentation

- 📖 [Interface Guide](./REFCHECK_INTERFACE.md) - Documentation complète
- 🚀 [Deployment Guide](./DEPLOYMENT_GUIDE.md) - Guide de déploiement
- 🔗 [API Integration](./exemple_biblio/api_integration.php) - Intégration backend
- ⚙️ [Configuration](./exemple_biblio/config.php) - Configuration

---

## 🤝 Intégration avec les modules

RefCheck s'intègre avec les modules suivants :

| Module       | Description                         | Endpoint    |
| ------------ | ----------------------------------- | ----------- |
| 2-Extraction | Extrait les références du PDF/texte | `/extract`  |
| 3-Parsing    | Parse et normalise les références   | `/parse`    |
| 4-Retrieve   | Récupère les métadonnées DOI        | `/retrieve` |
| 5-Compare    | Compare et score les références     | `/compare`  |

---

## 📝 Checklist de déploiement

- [ ] Backend Python démarré
- [ ] Permissions des dossiers correctes
- [ ] Configuration.php mise à jour
- [ ] SSL/HTTPS configuré
- [ ] Logs activés et surveillés
- [ ] Sauvegardes configurées
- [ ] Tests de charge effectués
- [ ] Équipe formée

---

## 🎓 Exemples de code

### Afficher une validation

```javascript
const file = document.getElementById("fileInput").files[0];
const result = ValidationUtils.validatePDF(file);

if (result.valid) {
  UIUtils.showValidation("container", true, result.message);
} else {
  UIUtils.showValidation("container", false, result.message);
}
```

### Formater un score

```javascript
const score = 85;
const formatted = FormatUtils.formatScore(score);

console.log(formatted.status); // "Incertain"
console.log(formatted.color); // "#ffc107"
console.log(formatted.percentage); // "85%"
```

### Sauvegarder les résultats

```javascript
StorageUtils.set("last_analysis", {
  date: new Date(),
  results: data,
  stats: stats,
});
```

---

## 🌟 Fonctionnalités futures

- 📊 Dashboard avec historique
- 💾 Export PDF des rapports
- 🔄 Historique des analyses
- 👥 Gestion des utilisateurs
- 📧 Notifications par email
- 🔌 Plugins personnalisés

---

## 📞 Support et contribution

### Signaler un bug

Créez une issue sur GitHub avec :

- Description du problème
- Étapes de reproduction
- Logs pertinents

### Proposer une amélioração

1. Fork le repo
2. Créer une branche : `git checkout -b feature/nom-feature`
3. Commit : `git commit -am 'Add feature'`
4. Push : `git push origin feature/nom-feature`
5. Pull Request

---

## 📄 License

MIT © 2025 HackademIA

---

## 🙏 Remerciements

Créé pour le projet **HackademIA 2025** par l'équipe RefCheck.

---

## 📧 Contact

- **Email** : support@hackademia.edu
- **Issues** : https://github.com/pablogit/hackademia-2025/issues
- **Discussions** : https://github.com/pablogit/hackademia-2025/discussions

---

**Dernière mise à jour** : novembre 2025
**Version** : 1.0.0
**Statut** : Production Ready ✅
