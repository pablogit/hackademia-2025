#!/bin/bash

# Script shell pour extraire la bibliographie d'un PDF
# Usage: ./extract.sh <fichier.pdf> [format]

# Vérifier si un argument est fourni
if [ $# -eq 0 ]; then
    echo "❌ Erreur: Aucun fichier PDF spécifié"
    echo ""
    echo "Usage: ./extract.sh <fichier.pdf>"
    echo ""
    echo "Exemple:"
    echo "  ./extract.sh document.pdf"
    exit 1
fi

PDF_FILE=$1

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
python3 extract_bibliography.py "$PDF_FILE"

