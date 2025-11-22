# 📚 Module d'Extraction de Bibliographies

**HackademIA 2025 - Projet RefCheck**

Ce module permet d'extraire automatiquement les références bibliographiques depuis des documents PDF académiques en utilisant l'intelligence artificielle (DeepSeek API).

# 📚 Module d'Extraction de Bibliographies

**HackademIA 2025 - Projet RefCheck**

Ce module permet d'extraire automatiquement les références bibliographiques depuis des documents PDF académiques en utilisant l'intelligence artificielle (DeepSeek API).

---

## 📁 Structure du Projet

```
2-Extraction/
│
├── extract_simple.py              # ⭐ Script principal d'extraction (RECOMMANDÉ)
├── app.py                         # Application autonome
├── credentials.py                 # Configuration de la clé API
├── requirements.txt               # Dépendances Python
│
├── PDFExamples/                   # 📄 Exemples de PDFs pour tester
│   ├── exemple_article.pdf
│   ├── exemple_rapport.pdf
│   └── exemple_TB_long.pdf
│
├── JSONExport/                    # 📤 Fichiers JSON générés
│   └── extracted_bibliography.json
│
├── ExampleJson/                   # 📋 Exemples de sorties
│   └── test_sample_output.json
│
└── SETUPdeepseek/                 # ⚙️ Configuration et utilitaires
    ├── check_deepseek_config.py   # Test de connexion API
    └── extract_with_deepseek.py   # Version alternative
```

---

## 🚀 Installation et Configuration

### 1. Prérequis

- Python 3.12 ou supérieur
- Connexion Internet
- Compte DeepSeek API (gratuit)

### 2. Installation des dépendances

```bash
cd 2-Extraction
pip install -r requirements.txt
```

Les packages installés :
- `openai` - Client API pour DeepSeek
- `pypdf` - Extraction de texte depuis PDF

### 3. Configuration de la clé API DeepSeek

#### a. Obtenir une clé API

1. Allez sur [https://platform.deepseek.com](https://platform.deepseek.com)
2. Créez un compte (gratuit)
3. Générez une clé API dans votre espace utilisateur
4. Copiez la clé (format : `sk-...`)

#### b. Configurer le fichier `credentials.py`

Ouvrez `credentials.py` et ajoutez votre clé :

```python
# Clé API DeepSeek
deepseekkey = "sk-votre_clé_api_ici"
```

⚠️ **Important** : Ne partagez jamais votre clé API publiquement !

#### c. Tester la connexion

```bash
python SETUPdeepseek/check_deepseek_config.py
```

Si la configuration est correcte, vous verrez :
```
✅ CONFIGURATION VALIDE - PRÊT À UTILISER
```

---

## 📖 Utilisation

### Méthode Simple (Recommandée)

```bash
python extract_simple.py PDFExamples/exemple_article.pdf
```

Le fichier `JSONExport/extracted_bibliography.json` sera créé avec toutes les références extraites.

### Avec nom de fichier personnalisé

```bash
python extract_simple.py PDFExamples/exemple_rapport.pdf JSONExport/mes_references.json
```

---

## 🎯 Fonctionnement

### Détection Intelligente

Le script détecte automatiquement :

1. **Le début de la bibliographie**
   - Cherche les titres : "References", "Bibliographie", "Bibliography", etc.
   - Si pas de titre : détecte le format IEEE (`[1]`, `[2]`, etc.)

2. **La fin de la bibliographie**
   - Détecte les sections suivantes : "Annexe", "Appendix", "Remerciements", etc.
   - Évite d'inclure du contenu non-pertinent

3. **Extraction optimisée**
   - Envoie uniquement la zone bibliographie à l'API (pas tout le document)
   - Réduction de 85-95% de la taille des données envoyées
   - Temps d'exécution : ~10-15 secondes par document

---

## 📊 Format de Sortie

Le fichier JSON généré contient un tableau d'objets avec les références :

```json
[
  {
    "reference": "[1] A. Einstein, Sitzungsber. K. Preuss. Akad. Wiss.1, 688 (1916)."
  },
  {
    "reference": "[2] A. Einstein, Sitzungsber. K. Preuss. Akad. Wiss.1, 154 (1918)."
  },
  {
    "reference": "[3] P. R. Saulson, Gen. Relativ. Gravit.43, 3289 (2011)."
  }
]
```

**Caractéristiques** :
- ✅ Ordre original préservé
- ✅ Texte exact (non modifié)
- ✅ Format prêt pour le parsing (module suivant)

---

## 🔧 Options Avancées

### Gestion des Erreurs

Si le JSON est tronqué (document très long), le script répare automatiquement :

```
⚠️  JSON incomplet (probablement tronqué par max_tokens)
   Tentative de réparation...
   ✅ JSON réparé: 108 références récupérées
```

### Fallback Automatique

Si la bibliographie n'est pas détectée :
```
⚠️  Bibliographie non détectée → Extraction complète
```

Le script traite alors tout le document.

---

## 📋 Exemples d'Utilisation

### Exemple 1 : Article scientifique

```bash
python extract_simple.py PDFExamples/exemple_article.pdf
```

**Sortie :**
```
📖 Document: 16 pages
📍 Bibliographie détectée à la page 9 (format IEEE: 49 références)
✂️  Extrait: 8 pages (9 → 16)
📏 Taille: 36123 caractères

🤖 Connexion à DeepSeek...
🔍 Analyse en cours...

✅ 108 références extraites
💾 Sauvegardé: JSONExport/extracted_bibliography.json
```

### Exemple 2 : Rapport long

```bash
python extract_simple.py PDFExamples/exemple_rapport.pdf JSONExport/rapport_refs.json
```

**Sortie :**
```
📖 Document: 65 pages
📍 Bibliographie détectée à la page 57 (titre: 'bibliographie')
🛑 Fin détectée à la page 63: 'annexe'
✂️  Extrait: 6 pages (57 → 63)
📏 Taille: 21108 caractères

✅ 45 références extraites
💾 Sauvegardé: JSONExport/rapport_refs.json
```

---

## ⚙️ Fichiers de Configuration

### `credentials.py`

Contient la clé API DeepSeek.

```python
deepseekkey = "sk-votre_clé_api_ici"
```

### `requirements.txt`

Liste des dépendances Python nécessaires.

```
pypdf
openai>=1.0.0
```

---

## 🛠️ Utilitaires

### Test de Configuration

Vérifie que votre clé API fonctionne :

```bash
python SETUPdeepseek/check_deepseek_config.py
```

### Version Alternative

Script avec plus d'options (dans `SETUPdeepseek/`) :

```bash
python SETUPdeepseek/extract_with_deepseek.py PDFExamples/exemple_article.pdf
```

---

## 💡 Conseils d'Utilisation

### Pour de meilleurs résultats

1. **Utilisez des PDFs avec texte extractible**
   - Évitez les scans d'images (utilisez l'OCR d'abord)
   
2. **Documents bien formatés**
   - Les bibliographies avec titres clairs sont mieux détectées
   
3. **Vérifiez le JSON généré**
   - Ouvrez le fichier pour confirmer que toutes les références sont présentes

### En cas de problème

#### La bibliographie n'est pas détectée
- Le script utilisera le document complet (fallback)
- Vérifiez que le titre de section est standard ("References", etc.)

#### Références manquantes
- Le JSON peut être tronqué pour les très gros documents
- Le script tente une réparation automatique

#### Erreur de connexion API
- Vérifiez votre clé dans `credentials.py`
- Testez avec `check_deepseek_config.py`
- Vérifiez votre connexion Internet

---

## 📈 Performance

| Type de document | Pages | Temps d'exécution | Références typiques |
|------------------|-------|-------------------|---------------------|
| Article court | 10-20 | ~8-12s | 20-50 |
| Article standard | 20-40 | ~10-15s | 50-100 |
| Rapport/Thèse | 50-200 | ~15-20s | 30-150 |

**Note** : Le temps dépend principalement de l'API DeepSeek, pas du script.

---

## 🔄 Workflow Complet

```
1. Placer le PDF → PDFExamples/
                    ↓
2. Extraire      → python extract_simple.py PDFExamples/mon_doc.pdf
                    ↓
3. JSON généré   → JSONExport/extracted_bibliography.json
                    ↓
4. Parser (étape suivante) → Module 3-Parsing
```

---

## 📞 Support

Pour toute question ou problème :

1. Vérifiez que `requirements.txt` est installé
2. Testez la connexion avec `check_deepseek_config.py`
3. Consultez les exemples dans `PDFExamples/` et `ExampleJson/`

---

## 📜 Licence

Projet HackademIA 2025 - RefCheck

---

**🎉 Prêt à extraire des bibliographies ! Lancez votre premier test :**

```bash
python extract_simple.py PDFExamples/exemple_article.pdf
```

---

#### 3. Test d'optimisation 🧪

Pour comparer les gains sur votre document:

```bash
python test_optimization.py
```

Affiche:
- Taille originale du document
- Taille après détection de zone
- Taille après filtrage
- % de réduction

---

### Comparaison des performances

| Document | Taille originale | Après détection | Après filtrage | Réduction |
|----------|------------------|-----------------|----------------|-----------|
| Article 20p | 60k chars | 8k chars | 4k chars | 93% |
| Rapport 50p | 150k chars | 20k chars | 10k chars | 93% |
| Thèse 200p | 500k chars | 60k chars | 25k chars | 95% |

---

### Quelle version utiliser ?

| Type de document | Commande recommandée |
|------------------|---------------------|
| Article court (< 30 pages) | `python extract_with_deepseek.py doc.pdf` |
| Rapport moyen (30-80 pages) | `python extract_optimized.py doc.pdf` |
| Long document (80+ pages) | `python extract_optimized.py doc.pdf --filter` |

**Format de sortie:**
```json
[
  {
    "reference": "texte complet de la référence 1"
  },
  {
    "reference": "texte complet de la référence 2"
  }
]
```

#### 2. Extraction avec Gemini AI (Alternative)

Le script `extract_with_gemini.py` est disponible pour ceux qui préfèrent utiliser Google Gemini.

**Note:** Peut avoir des problèmes de quota avec la version gratuite.

#### 3. Extraction avec regex (Méthode classique)

Le script `extract_bibliography.py` utilise des expressions régulières pour extraire les références.

## Librairies utiles

* pypdf - Extraction de texte depuis PDF
* openai - Client compatible pour DeepSeek API
* pdfplumber - Extraction avancée de PDF
* scholarly - Recherche académique
* pdf2bib : https://pypi.org/project/pdf2bib/

## Installation

```bash
pip install -r requirements.txt
```

## Configuration

### Option 1: Fichier credentials.py (Recommandé)

Dans `credentials.py` (ou `../3-Parsing/credentials.py`):
```python
deepseekkey = "sk-votre_clé_deepseek"
googlekey = "votre_clé_gemini"  # Optionnel
```

### Option 2: Modifier app.py directement

Dans `app.py`, ligne 12:
```python
DEEPSEEK_API_KEY = "sk-votre_clé_deepseek"
```

## Obtenir une clé API DeepSeek

1. Créer un compte sur https://platform.deepseek.com
2. Aller dans API Keys
3. Créer une nouvelle clé
4. Copier la clé dans credentials.py

