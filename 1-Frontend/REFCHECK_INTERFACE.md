# RefCheck - Interface Web PHP

## 📋 Vue d'ensemble

RefCheck est une interface web moderne pour vérifier les hallucinations de références dans les textes générés par l'IA. Cette interface offre une expérience utilisateur intuitive avec une mise en page en écran partagé (split-view).

## 🗂️ Structure des fichiers

```
1-Frontend/
├── exemple_biblio/
│   ├── refcheck.php              # Interface principale (split-view)
│   ├── welcome_refcheck.php      # Page d'accueil
│   ├── header.php                # En-tête common (modifié)
│   ├── footer.php                # Pied de page
│   ├── css/
│   │   └── refcheck.css          # Styles personnalisés
│   └── js/
│       └── refcheck-utils.js     # Utilitaires JavaScript
```

## 🎨 Fonctionnalités principales

### Zone Gauche (Input)

- **Double Mode d'Entrée**

  - Upload de fichiers PDF (drag & drop)
  - Zone de texte pour saisie directe
  - Validation en temps réel

- **Validation Préalable**

  - Vérification du format de fichier
  - Vérification de la taille (max 50MB)
  - Vérification du contenu (minimum 10 mots)
  - Détection de structures de références

- **Bouton d'Action Conditionnel**
  - N'apparaît que si validation réussie
  - Désactivé lors du traitement

### Zone Droite (Output)

- **Affichage Dynamique des Résultats**

  - Liste des références extraites
  - Code couleur sémantique (vert/jaune/rouge)
  - Détails expansibles par élément

- **Code Couleur**

  - 🟢 **Vert (>90%)** : Référence vérifiée
  - 🟡 **Jaune (60-89%)** : Incertaine
  - 🔴 **Rouge (<60%)** : Hallucination probable

- **Statistiques**
  - Total de références
  - Nombre de références vérifiées
  - Nombre d'incertaines
  - Nombre d'hallucinations

## 🚀 Utilisation

### Pour accéder à l'interface

1. **Page d'accueil** : `welcome_refcheck.php`

   - Présentation générale du projet
   - Statistiques et cas d'usage
   - Bouton pour lancer l'analyse

2. **Interface principale** : `refcheck.php`
   - Zone gauche pour soumettre les données
   - Zone droite pour voir les résultats
   - Validation en temps réel

### Flux utilisateur

```
1. Arrive sur le site
2. Choisit: Upload PDF ou Texte
3. Valide son entrée
4. Bouton "Lancer l'Analyse" apparaît
5. Clique sur le bouton
6. Spinner de chargement
7. Résultats apparaissent avec couleurs
8. Clique sur "+" pour voir les détails
9. Voit la justification IA
```

## 💻 Technologie

### Frontend

- **PHP** : Logique serveur
- **HTML5** : Structure
- **CSS3** : Styles modernes avec gradients et animations
- **Vanilla JavaScript** : Interactions sans dépendances

### Design

- **Palette de couleurs** : Dégradé moderne (violet/bleu)
- **Animations** : Transitions fluides et spinners
- **Responsive** : Fonctionne sur desktop et mobile
- **Accessibilité** : Contraste adéquat et navigation claire

## 📱 Responsive Design

L'interface s'adapte automatiquement :

- **Desktop** : Split-view horizontal (gauche/droite)
- **Tablet** : Zones empilées verticalement
- **Mobile** : Interface optimisée en colonne

## 🔧 Intégration avec le backend

### Points d'intégration

1. **Extraction des références** (Module 2)

   - Récupère le JSON extrait
   - Affiche les données dans la zone droite

2. **Parsing des références** (Module 3)

   - Normalise les références
   - Prépare le format pour la comparaison

3. **Récupération des métadonnées** (Module 4)

   - Obtient les informations DOI
   - Enrichit les résultats

4. **Comparaison et scoring** (Module 5)
   - Calcule les scores de ressemblance
   - Génère les justifications IA

## 📊 Format des résultats attendus

```json
[
  {
    "score": 95,
    "title": "Smith, J. (2021). Machine Learning Fundamentals",
    "authors": "John Smith, Alice Brown",
    "year": 2021,
    "journal": "Nature Reviews",
    "justification": "Référence valide..."
  }
]
```

## 🎯 Prochaines étapes

1. **Intégration API** : Connecter avec les modules backend
2. **Authentification** : Ajouter login/register si nécessaire
3. **Historique** : Sauvegarden les analyses précédentes
4. **Export** : Générer rapports PDF
5. **Analytics** : Tracker les usages

## 🛠️ Utilitaires JavaScript

Fichier `refcheck-utils.js` fournit des helpers:

### ValidationUtils

- `validatePDF(file)` : Valide un fichier PDF
- `validateText(text)` : Valide du texte brut
- `validateEmail(email)` : Valide une adresse email

### UIUtils

- `showValidation(containerId, isValid, message)` : Affiche un message
- `clearValidation(containerId)` : Efface les messages
- `showLoading(elementId, show)` : Affiche/cache le chargement
- `toggle(elementId)` : Bascule visibilité
- `showToast(message, type, duration)` : Notification toast

### FormatUtils

- `formatScore(score)` : Formate le score avec couleur
- `formatDate(date)` : Formate la date
- `formatBytes(bytes)` : Formate la taille de fichier
- `truncate(text, length)` : Tronque le texte

### APIUtils

- `fetch(url, options)` : Appel API avec gestion d'erreur
- `uploadFile(file, endpoint, onProgress)` : Upload avec progress bar

### StorageUtils

- `set(key, value)` : Sauvegarde en localStorage
- `get(key)` : Récupère depuis localStorage
- `remove(key)` : Supprime une clé

## 📄 Fichiers CSS/JS

### refcheck.css

- Variables CSS personnalisées
- Animations réutilisables
- Système de grille et flexbox
- Styles pour tous les composants

### refcheck-utils.js

- ~300 lignes de code utile
- Pas de dépendances externes
- Facile à intégrer et personnaliser

## 🎓 Exemple de code

### Utiliser les validation

```javascript
// Valider un PDF
const file = document.getElementById("fileInput").files[0];
const validation = ValidationUtils.validatePDF(file);

if (validation.valid) {
  UIUtils.showValidation("validationContainer", true, validation.message);
} else {
  UIUtils.showValidation("validationContainer", false, validation.message);
}
```

### Afficher une notification

```javascript
UIUtils.showToast("Analyse réussie!", "success", 3000);
```

### Formater un score

```javascript
const formatted = FormatUtils.formatScore(85);
console.log(formatted.status); // "Incertain"
console.log(formatted.color); // "#ffc107"
```

## 📝 Notes d'implémentation

- L'interface actuellement affiche des **résultats mock** dans `refcheck.php`
- Remplacer la fonction `displayMockResults()` par un appel API réel
- Les styles utilisent des **variables CSS** pour faciliter la personnalisation
- Le code JavaScript est **vanilla** (pas de jQuery/React) pour une performance optimale
- L'interface est **entièrement responsive** sans dépendances externes

## 🔗 Liens utiles

- **Frontend**: `/1-Frontend/`
- **Extraction**: `/2-Extraction/`
- **Parsing**: `/3-Parsing/`
- **Retrieve**: `/4-Retrieve/`
- **Compare**: `/5-Compare/`

---

**Créé pour HackademIA 2025** | Version 1.0
