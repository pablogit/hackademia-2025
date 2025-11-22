"""
Exemple d'utilisation du module d'extraction de bibliographie.
"""

from extract_bibliography import extract_bibliography, convert_to_json_output


def main():
    """Exemple d'utilisation principale."""
    # Chemin vers votre fichier PDF
    pdf_file = "document.pdf"  # Remplacez par le chemin de votre PDF
    
    print("=" * 80)
    print("Extraction de la bibliographie depuis le PDF...")
    print("=" * 80)
    
    try:
        # Extraire les références
        references = extract_bibliography(pdf_file)
        
        if not references:
            print("\n⚠️  Aucune référence trouvée dans le document.")
            print("Vérifiez que le PDF contient une section 'Bibliographie' ou 'Références'.")
            return
        
        print(f"\n✅ {len(references)} références trouvées.\n")
        
        # Afficher les références extraites
        print("=" * 80)
        print("Références extraites:")
        print("=" * 80)
        for i, ref in enumerate(references[:5], 1):  # Afficher les 5 premières
            print(f"\n{i}. {ref[:150]}...")  # Afficher les 150 premiers caractères
        
        if len(references) > 5:
            print(f"\n... et {len(references) - 5} autres références")
        
        # Convertir en JSON
        print("\n" + "=" * 80)
        print("JSON généré:")
        print("=" * 80)
        json_output = convert_to_json_output(references)
        print(json_output)
        
        # Optionnel: Sauvegarder dans un fichier
        output_file = "bibliography.json"
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write(json_output)
        
        print(f"\n✅ JSON sauvegardé dans '{output_file}'")
        
    except FileNotFoundError:
        print(f"\n❌ Erreur: Le fichier '{pdf_file}' est introuvable.")
        print("Veuillez fournir un chemin valide vers un fichier PDF.")
    except Exception as e:
        print(f"\n❌ Erreur lors de l'extraction: {str(e)}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()

