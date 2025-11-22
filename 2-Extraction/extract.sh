#!/bin/bash

#!/bin/bash

# Script shell pour extraire la bibliographie d'un PDF
# Usage: ./extract.sh <fichier.pdf> [--count-only]

# Vérifier si un argument est fourni
if [ $# -eq 0 ]; then
    echo "❌ Erreur: Aucun fichier PDF spécifié"
    echo ""
    echo "Usage: ./extract.sh <fichier.pdf> [--count-only]"
    echo ""
    echo "Options:"
    echo "  --count-only    Compter seulement le nombre de références (rapide)"
    echo ""
    echo "Exemples:"
    echo "  ./extract.sh document.pdf           # Extraire en JSON"
    echo "  ./extract.sh document.pdf --count   # Compter seulement"
    exit 1
fi

PDF_FILE=$1
OPTION=$2

# Vérifier si le fichier existe
if [ ! -f "$PDF_FILE" ]; then
    echo "❌ Erreur: Le fichier '$PDF_FILE' est introuvable"
    exit 1
fi

# Vérifier si Python est installé
if ! command -v python3 &> /dev/null; then
    echo "❌ Erreur: Python 3 n'est pas installé"
    exit 1
fi

# Exécuter le script Python
if [ "$OPTION" = "--count-only" ] || [ "$OPTION" = "--count" ]; then
    python3 extract_bibliography.py "$PDF_FILE" --count-only
else
    python3 extract_bibliography.py "$PDF_FILE"
fi

