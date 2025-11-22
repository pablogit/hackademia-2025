"""
Exemple d'utilisation de pdf2bib pour extraire des métadonnées BibTeX.
Version corrigée (syntaxe Python correcte).
"""

import pdf2bib
import os

# Configuration (optionnel - peut être ignoré si la méthode n'existe pas)
try:
    if hasattr(pdf2bib.config, 'set'):
        pdf2bib.config.set('verbeux', False)
except:
    pass  # Continuer même si la configuration échoue

# Chemin vers le PDF dans le répertoire
chemin = "./rapport.pdf"  # Fichier PDF du répertoire courant

# Vérifier si le chemin existe
if not os.path.exists(chemin):
    print(f"❌ Le chemin '{chemin}' est introuvable")
    print("Veuillez remplacer 'chemin' par le chemin vers un PDF ou un dossier de PDFs")
else:
    # Extraire les métadonnées
    try:
        resultat = pdf2bib.pdf2bib(chemin)
        
        # Traiter les résultats
        if isinstance(resultat, list):
            # Plusieurs PDFs
            for i, res in enumerate(resultat):
                print(f"\n{'='*80}")
                print(f"PDF {i+1}:")
                print(f"{'='*80}")
                
                if 'métadonnées' in res:
                    print("\n📋 Métadonnées:")
                    print(res['métadonnées'])
                
                if 'bibtex' in res:
                    print("\n📄 BibTeX:")
                    print(res['bibtex'])
                    
        elif isinstance(resultat, dict):
            # Un seul PDF
            print(f"\n{'='*80}")
            print("Résultat:")
            print(f"{'='*80}")
            
            if 'métadonnées' in resultat:
                print("\n📋 Métadonnées:")
                print(resultat['métadonnées'])
            
            if 'bibtex' in resultat:
                print("\n📄 BibTeX:")
                print(resultat['bibtex'])
        else:
            print("⚠️  Format de résultat inattendu")
            
    except Exception as e:
        print(f"❌ Erreur lors de l'extraction: {e}")
        import traceback
        traceback.print_exc()

