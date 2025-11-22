"""
Extraction de bibliographie depuis un PDF en utilisant DeepSeek API.
DeepSeek utilise une API compatible OpenAI.
"""

import json
import sys
import os
import re
import pypdf
from openai import OpenAI

# Importer la clé API depuis le fichier credentials
try:
    from credentials import deepseekkey as api_key
except ImportError:
    print("⚠️  Erreur: Impossible d'importer la clé API depuis credentials.py")
    print("Assurez-vous que le fichier credentials.py existe et contient 'deepseekkey'")
    api_key = None


def detect_bibliography_start_page(pdf_reader: pypdf.PdfReader) -> int:
    """
    Détecte la page où commence VRAIMENT la bibliographie (avec critères stricts).

    Args:
        pdf_reader: Objet PdfReader du PDF

    Returns:
        Index de la page de début de la bibliographie, ou -1 si non trouvée
    """
    # Mots-clés de début de bibliographie (minuscules)
    keywords = [
        'references',
        'bibliography',
        'références',
        'références bibliographiques',
        'works cited',
        'bibliographie',
        'literatura citada',
        'referências'
    ]

    # Patterns pour identifier une vraie référence bibliographique
    reference_patterns = [
        r'^\d+\.',  # 1. 2. 3.
        r'^\[\d+\]',  # [1] [2]
        r'^[A-Z][a-z]+,\s*[A-Z]\.?',  # Nom, I. ou Nom, Initiale
        r'^[A-Z][a-z]+\s+\([12]\d{3}\)',  # Nom (2020)
        r'\(\d{4}\)',  # (2020) n'importe où dans la ligne
        r'\d{4}[,;.]',  # 2020, ou 2020; ou 2020.
        r'^[A-Z][A-Z\s]+,',  # NOM COMPLET, (majuscules)
    ]

    for page_idx, page in enumerate(pdf_reader.pages):
        try:
            text = page.extract_text()
            if not text:
                continue

            lines = text.split('\n')

            # Chercher les mots-clés dans les 20 premières lignes
            for line_idx, line in enumerate(lines[:20]):
                line_stripped = line.strip()
                line_lower = line_stripped.lower()

                for keyword in keywords:
                    # Critère 1: La ligne doit être courte (titre de section)
                    if len(line_stripped) > 60:
                        continue

                    # Critère 2: Le mot-clé doit être seul ou au début
                    is_exact_match = line_lower == keyword
                    is_start_match = line_lower.startswith(keyword) and len(line_stripped) < 40

                    if not (is_exact_match or is_start_match):
                        continue

                    # Critère 3: Vérifier qu'il y a des références APRÈS
                    references_found = 0

                    for next_line_idx in range(line_idx + 1, min(line_idx + 30, len(lines))):
                        next_line = lines[next_line_idx].strip()

                        # Ignorer lignes vides ou courtes
                        if not next_line or len(next_line) < 15:
                            continue

                        # Vérifier si c'est une référence
                        for pattern in reference_patterns:
                            if re.search(pattern, next_line):
                                references_found += 1
                                break

                        # Au moins 1 référence = c'est probablement la vraie section
                        # (réduit de 2 à 1 pour plus de flexibilité)
                        if references_found >= 1:
                            print(f"📍 Section bibliographie détectée à la page {page_idx + 1}")
                            print(f"   Mot-clé trouvé: '{keyword}'")
                            print(f"   ✅ Validé: {references_found} référence(s) trouvée(s) après le titre")
                            return page_idx
        except Exception as e:
            # Ignorer les erreurs d'extraction de page
            continue

    return -1


def detect_bibliography_end_page(pdf_reader: pypdf.PdfReader, start_page: int) -> int:
    """
    Détecte la page où se termine la bibliographie (début des annexes, etc.).

    Args:
        pdf_reader: Objet PdfReader du PDF
        start_page: Index de la page de début de la bibliographie

    Returns:
        Index de la dernière page de la bibliographie (exclusif), ou total_pages si pas de fin détectée
    """
    # Mots-clés indiquant la fin de la bibliographie
    end_keywords = [
        'appendix',
        'appendices',
        'annexe',
        'annexes',
        'supplementary',
        'supplement',
        'supplémentaire',
        'acknowledgement',
        'acknowledgments',
        'remerciements',
        'about the author',
        'author biography',
        'index',
        'glossary',
        'glossaire',
        'notes',
        'endnotes'
    ]

    total_pages = len(pdf_reader.pages)

    # Chercher à partir de la page suivant le début de la biblio
    for page_idx in range(start_page + 1, total_pages):
        try:
            text = pdf_reader.pages[page_idx].extract_text()
            if not text:
                continue

            # Prendre les 500 premiers caractères pour vérifier l'en-tête
            header = text[:500].lower()
            lines = text.split('\n')

            # Chercher les mots-clés de fin
            for keyword in end_keywords:
                if keyword in header:
                    # Vérifier que c'est bien un titre de section (ligne courte, isolée)
                    for i, line in enumerate(lines[:10]):
                        line_stripped = line.strip().lower()
                        # Si la ligne est courte et contient le mot-clé, c'est probablement un titre
                        if len(line_stripped) < 50 and keyword in line_stripped:
                            print(f"🛑 Fin de bibliographie détectée à la page {page_idx + 1}")
                            print(f"   Mot-clé trouvé: '{keyword}'")
                            return page_idx
        except Exception as e:
            continue

    # Pas de fin détectée, prendre jusqu'à la fin du document
    return total_pages


def extract_bibliography_region(pdf_path: str) -> str:
    """
    Extrait uniquement la région bibliographie d'un PDF.

    Args:
        pdf_path: Chemin vers le fichier PDF

    Returns:
        Texte de la zone bibliographie uniquement
    """
    with open(pdf_path, 'rb') as file:
        pdf_reader = pypdf.PdfReader(file)
        total_pages = len(pdf_reader.pages)

        print(f"📖 Document PDF: {total_pages} pages")

        # Détecter la page de début de bibliographie
        start_page = detect_bibliography_start_page(pdf_reader)

        if start_page == -1:
            print("⚠️  Aucune section bibliographie détectée")
            print("   → Extraction du document complet (méthode de secours)")
            # Fallback: tout extraire
            text = ""
            for page in pdf_reader.pages:
                text += page.extract_text() + "\n"
            return text

        # Détecter la page de fin de bibliographie
        end_page = detect_bibliography_end_page(pdf_reader, start_page)

        # Extraire depuis la page de début jusqu'à la page de fin
        bibliography_text = ""
        pages_extracted = 0

        for page_idx in range(start_page, end_page):
            try:
                page_text = pdf_reader.pages[page_idx].extract_text()
                bibliography_text += page_text + "\n"
                pages_extracted += 1
            except Exception as e:
                print(f"⚠️  Erreur extraction page {page_idx + 1}: {e}")
                continue

        if end_page < total_pages:
            print(f"✂️  Zone extraite: {pages_extracted} pages (pages {start_page + 1} à {end_page})")
        else:
            print(f"✂️  Zone extraite: {pages_extracted} pages (depuis page {start_page + 1} jusqu'à la fin)")

        print(f"📏 Taille de la zone: {len(bibliography_text)} caractères")

        return bibliography_text


def extract_text_from_pdf(pdf_path: str) -> str:
    """
    Extrait le texte du PDF (version optimisée: zone bibliographie uniquement).

    Args:
        pdf_path: Chemin vers le fichier PDF

    Returns:
        Texte extrait (zone bibliographie si détectée, sinon document complet)
    """
    return extract_bibliography_region(pdf_path)


def create_bibliography_extraction_prompt() -> str:
    """
    Crée le prompt système avec des exemples de bibliographies.

    Returns:
        Prompt système pour DeepSeek
    """
    return """Tu es un expert en extraction de bibliographies depuis des documents académiques.

Ton rôle est d'extraire TOUTES les références bibliographiques du texte fourni.

EXEMPLES DE FORMATS DE BIBLIOGRAPHIE:

1. Format APA:
Smith, J., & Johnson, M. (2020). Title of the article. Journal Name, 15(3), 245-260. https://doi.org/10.xxxx

2. Format IEEE:
[1] J. Smith and M. Johnson, "Title of the article," Journal Name, vol. 15, no. 3, pp. 245-260, 2020.

3. Format Vancouver:
1. Smith J, Johnson M. Title of the article. Journal Name. 2020;15(3):245-260.

4. Format Harvard:
Smith, J. and Johnson, M. (2020) 'Title of the article', Journal Name, 15(3), pp. 245-260.

5. Format Livre:
Auteur, A. (2019). Titre du livre. Éditeur, Ville.

6. Format Thèse:
Auteur, A. (2018). Titre de la thèse. Thèse de doctorat, Université.

7. Format Web:
Auteur, A. (2021). Titre de la page. Disponible sur: URL [Accédé le: date].

INSTRUCTIONS:
1. Le texte fourni contient la section bibliographie
2. Extrait CHAQUE référence individuellement
3. Ne modifie PAS le texte des références, garde-les telles quelles
4. Ignore les titres de section (comme "References", "Bibliography", etc.)
5. Retourne UNIQUEMENT un JSON au format suivant:

[
  {
    "reference": "texte complet de la référence 1"
  },
  {
    "reference": "texte complet de la référence 2"
  }
]

IMPORTANT:
- Ne retourne QUE le JSON, rien d'autre
- Ne parse PAS les références, garde-les complètes
- Inclus toutes les références, même si elles sont sur plusieurs lignes
- Si une référence s'étend sur plusieurs lignes, regroupe-la en une seule entrée
- Ignore les numéros de page, en-têtes et pieds de page"""


def extract_bibliography_with_deepseek(pdf_path: str, output_path: str = None) -> list:
    """
    Extrait la bibliographie d'un PDF en utilisant DeepSeek API.

    Args:
        pdf_path: Chemin vers le fichier PDF
        output_path: Chemin optionnel pour sauvegarder le JSON (par défaut: extracted_bibliography.json)

    Returns:
        Liste des références extraites
    """
    print(f"📄 Extraction du texte depuis: {pdf_path}")

    # Extraire le texte du PDF
    pdf_text = extract_text_from_pdf(pdf_path)

    if not pdf_text.strip():
        raise ValueError("Le PDF ne contient pas de texte extractible")

    print(f"✅ Texte extrait ({len(pdf_text)} caractères)")

    # Initialiser le client DeepSeek (compatible OpenAI)
    print("🤖 Connexion à DeepSeek API...")
    client = OpenAI(
        api_key=api_key,
        base_url="https://api.deepseek.com"
    )

    # Créer le prompt
    system_prompt = create_bibliography_extraction_prompt()
    user_prompt = f"""Voici la section bibliographie d'un document académique. Extrait TOUTES les références au format JSON spécifié:

{pdf_text}

Retourne uniquement le JSON avec les références."""

    # Appeler DeepSeek
    print("🔍 Analyse du document par DeepSeek...")
    response = client.chat.completions.create(
        model="deepseek-chat",
        messages=[
            {"role": "system", "content": system_prompt},
            {"role": "user", "content": user_prompt}
        ],
        temperature=0.1,  # Faible température pour plus de précision
        max_tokens=8000
    )

    # Extraire la réponse
    response_text = response.choices[0].message.content.strip()

    # Nettoyer la réponse (enlever les balises markdown si présentes)
    if response_text.startswith("```json"):
        response_text = response_text[7:]  # Enlever ```json
    if response_text.startswith("```"):
        response_text = response_text[3:]  # Enlever ```
    if response_text.endswith("```"):
        response_text = response_text[:-3]  # Enlever ```

    response_text = response_text.strip()

    # Parser le JSON
    try:
        bibliography = json.loads(response_text)
    except json.JSONDecodeError as e:
        print(f"❌ Erreur lors du parsing JSON: {e}")
        print(f"Réponse brute de DeepSeek:\n{response_text}")
        raise

    print(f"✅ {len(bibliography)} références extraites")

    # Sauvegarder le résultat
    if output_path is None:
        output_path = "../JSONExport/extracted_bibliography.json"

    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(bibliography, f, ensure_ascii=False, indent=2)

    print(f"💾 Bibliographie sauvegardée dans: {output_path}")

    return bibliography


def main():
    """Fonction principale d'exemple."""
    if len(sys.argv) < 2:
        print("Usage: python extract_with_deepseek.py <chemin_pdf> [chemin_sortie_json]")
        print("\nExemples:")
        print("  python extract_with_deepseek.py exemple_article.pdf")
        print("  python extract_with_deepseek.py exemple_rapport.pdf ma_biblio.json")
        return

    pdf_path = sys.argv[1]
    output_path = sys.argv[2] if len(sys.argv) > 2 else None

    if not os.path.exists(pdf_path):
        print(f"❌ Erreur: Le fichier '{pdf_path}' n'existe pas")
        return

    try:
        print("=" * 80)
        print("EXTRACTION DE BIBLIOGRAPHIE AVEC DEEPSEEK AI")
        print("=" * 80)

        bibliography = extract_bibliography_with_deepseek(pdf_path, output_path)

        print("\n" + "=" * 80)
        print("APERÇU DES RÉFÉRENCES EXTRAITES")
        print("=" * 80)

        for i, ref in enumerate(bibliography[:5], 1):
            ref_text = ref.get('reference', '')
            preview = ref_text[:150] + "..." if len(ref_text) > 150 else ref_text
            print(f"\n{i}. {preview}")

        if len(bibliography) > 5:
            print(f"\n... et {len(bibliography) - 5} autres références")

        print("\n" + "=" * 80)
        print("✅ EXTRACTION TERMINÉE AVEC SUCCÈS")
        print("=" * 80)

    except Exception as e:
        print(f"\n❌ Erreur: {str(e)}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()

