# hackademia-2025

Repo pour le projet RefCheck de HackademIA 2025

## Librairies utiles

- pyPDF
- scholarly

# Interface Utilisateur : Vérificateur d'Hallucinations de Références IA

## 🎯 Vue d'ensemble du projet

Ce document décrit les ambitions et les spécifications fonctionnelles de l'interface utilisateur (UI) de notre outil de vérification de références.

L'objectif principal de cet outil est d'analyser des textes générés par des intelligences artificielles (sous forme de PDF ou de texte brut) qui contiennent des citations bibliographiques. Le système compare ces citations avec des bases de données de références réelles pour déterminer si l'IA a "halluciné" (inventé) une référence ou si elle est véridique.

L'interface utilisateur est le point de contact crucial qui doit rendre ce processus technique complexe simple, intuitif et visuellement clair pour l'utilisateur final.

---

## 🖥️ Concept et Architecture de l'Interface

Nous visons une interface web moderne, épurée, basée sur une **mise en page en écran partagé (Split-View Layout)**. L'écran est divisé verticalement en deux zones distinctes :

1.  **Zone Gauche (Input) :** L'espace de travail de l'utilisateur pour soumettre les données.
2.  **Zone Droite (Output) :** L'espace de visualisation des résultats interactifs.

### Zone Gauche : Soumission et Validation

L'objectif de cette zone est de guider l'utilisateur dans la soumission de son document et de valider les données avant traitement.

**Fonctionnalités clés :**

- **Double Mode d'Entrée :**
  - **Upload de Fichier :** Une zone de "Drag-and-Drop" (glisser-déposer) claire pour les fichiers PDF.
  - **Zone de Texte :** Un champ de texte (textarea) alternatif pour coller directement du contenu brut.
- **Validation Préalable Instantanée :**
  - Dès qu'un fichier est déposé ou du texte collé, le système effectue une vérification rapide (le PDF est-il lisible ? Le texte contient-il des structures ressemblant à des références ?).
  - Des indicateurs visuels (✔️ check vert ou ❌ croix rouge avec message d'erreur) informent immédiatement l'utilisateur de la validité de son entrée.
- **Bouton d'Action Conditionnel :**
  - Le bouton **"Lancer l'Analyse"** est initialement caché ou désactivé.
  - Il n'apparaît (ou ne devient cliquable) que _si et seulement si_ l'étape de validation préalable est réussie. Cela évite de lancer des calculs inutiles sur des données corrompues.

---

### Zone Droite : Visualisation Interactive des Résultats

Cette zone affiche le résultat du traitement (provenant du JSON backend). L'objectif est de transformer des données brutes en un tableau de bord lisible d'un seul coup d'œil, avec une capacité d'exploration progressive.

**Fonctionnalités clés :**

- **Liste Ligne par Ligne :** Les références extraites sont affichées sous forme de liste verticale. Chaque élément de la liste correspond à une référence trouvée dans le document source.
- **Code Couleur Semantique (Score de Ressemblance) :**
  Pour une interprétation immédiate, chaque référence est colorée selon son score de véracité :
  - 🟢 **Vert (> 90%) :** Référence excellente/vérifiée. Très forte probabilité qu'elle soit réelle.
  - 🟡 **Jaune (Entre 60% et 89%) :** Référence incertaine ou partiellement correcte. Nécessite une vérification humaine.
  - 🔴 **Rouge (< 60%) :** Hallucination probable. La référence semble inventée ou très incorrecte.
- **Détails Expansibles (Le "Petit +") :**
  - À côté de chaque référence, un petit bouton interactif (icône `+` ou chevron) permet d'en savoir plus.
  - Au clic, l'élément s'étend pour révéler un panneau d'explication.
- **Justification par IA :**
  - Le panneau étendu contient une explication générée par une IA secondaire. Elle justifie pourquoi le score est bon ou mauvais (ex: _"Cette référence existe bien dans PubMed mais l'année citée est 2023 alors que la réelle est 2021"_, ou _"Auteur inconnu dans ce domaine de recherche"_).

---

## 🎨 Expérience Utilisateur (UX) souhaitée

L'expérience doit être fluide :

1.  L'utilisateur arrive : la droite est vide, la gauche l'invite à déposer un fichier.
2.  Il dépose un PDF : un ✔️ vert apparaît.
3.  Le bouton "Lancer l'Analyse" apparaît. Il clique.
4.  Des indicateurs de chargement (spinners) apparaissent à droite.
5.  Les résultats apparaissent progressivement, colorés, prêts à être explorés via les boutons "+".
