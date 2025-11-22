"""
Extraction de bibliographie depuis un PDF en utilisant DeepSeek API.
Version SIMPLE et EFFICACE - sans sur-optimisation.
"""

import json
import sys
import os
import pypdf
from openai import OpenAI

# Importer la clé API
try:
    from credentials import deepseekkey as api_key
except ImportError:
    print("⚠️  Erreur: Impossible d'importer la clé API depuis credentials.py")
    api_key = None


def detect_bibliography_start_page(pdf_reader: pypdf.PdfReader) -> int:
    """Détecte simplement la page où commence la bibliographie."""
    keywords = ['references', 'bibliography', 'références', 'bibliographie', 'works cited']

    # D'abord chercher un titre explicite
    for page_idx, page in enumerate(pdf_reader.pages):
        try:
            text = page.extract_text()
            if not text:
                continue

            lines = text.split('\n')
            for line in lines[:15]:  # Premières lignes de la page
                line_lower = line.strip().lower()
                for keyword in keywords:
                    # Mot-clé seul ou au début + ligne courte = titre
                    if (line_lower == keyword or line_lower.startswith(keyword)) and len(line.strip()) < 100:
                        print(f"📍 Bibliographie détectée à la page {page_idx + 1} (titre: '{keyword}')")
                        return page_idx
        except:
            continue

    # Si pas de titre trouvé, chercher une page avec beaucoup de références numérotées
    # (format IEEE: [1], [2], etc.)
    import re
    ref_pattern = re.compile(r'^\[\d+\]')

    for page_idx, page in enumerate(pdf_reader.pages):
        try:
            text = page.extract_text()
            if not text:
                continue

            lines = text.split('\n')
            ref_count = 0

            # Compter les lignes qui commencent par [1], [2], etc.
            for line in lines:
                if ref_pattern.match(line.strip()):
                    ref_count += 1

            # Si > 5 références numérotées sur une page, c'est probablement la biblio
            if ref_count > 5:
                print(f"📍 Bibliographie détectée à la page {page_idx + 1} (format IEEE: {ref_count} références)")
                return page_idx
        except:
            continue

    return -1


def detect_bibliography_end_page(pdf_reader: pypdf.PdfReader, start_page: int) -> int:
    """Détecte la fin de la bibliographie (annexes, etc.)."""
    end_keywords = ['appendix', 'appendices', 'annexe', 'annexes', 'supplementary',
                    'acknowledgement', 'acknowledgments', 'remerciements']

    total_pages = len(pdf_reader.pages)

    for page_idx in range(start_page + 1, total_pages):
        try:
            text = pdf_reader.pages[page_idx].extract_text()
            if not text:
                continue

            lines = text.split('\n')
            for line in lines[:10]:
                line_lower = line.strip().lower()
                for keyword in end_keywords:
                    if keyword in line_lower and len(line.strip()) < 50:
                        print(f"🛑 Fin détectée à la page {page_idx + 1}: '{keyword}'")
                        return page_idx
        except:
            continue

    return total_pages


def extract_bibliography_region(pdf_path: str) -> str:
    """Extrait la zone bibliographie du PDF."""
    with open(pdf_path, 'rb') as file:
        pdf_reader = pypdf.PdfReader(file)
        total_pages = len(pdf_reader.pages)

        print(f"📖 Document: {total_pages} pages")

        # Détecter début
        start_page = detect_bibliography_start_page(pdf_reader)

        if start_page == -1:
            print("⚠️  Bibliographie non détectée → Extraction complète")
            text = ""
            for page in pdf_reader.pages:
                text += page.extract_text() + "\n"
            return text

        # Détecter fin
        end_page = detect_bibliography_end_page(pdf_reader, start_page)

        # Extraire
        bibliography_text = ""
        for page_idx in range(start_page, end_page):
            try:
                bibliography_text += pdf_reader.pages[page_idx].extract_text() + "\n"
            except:
                continue

        pages_count = end_page - start_page
        print(f"✂️  Extrait: {pages_count} pages ({start_page + 1} → {end_page})")
        print(f"📏 Taille: {len(bibliography_text)} caractères")

        return bibliography_text


def create_bibliography_extraction_prompt() -> str:
    """Prompt système optimisé pour DeepSeek."""
    return """Tu es un extracteur de bibliographies. 

Extrait TOUTES les références dans l'ORDRE où elles apparaissent.

Retourne un tableau JSON:
[{"reference": "ref 1"}, {"reference": "ref 2"}]

Règles:
- Garde l'ORDRE original
- Une référence = une entrée (même si multi-lignes)
- Texte EXACT (ne modifie rien)
- UNIQUEMENT le JSON en sortie"""


def extract_bibliography_with_deepseek(pdf_path: str, output_path: str = None) -> list:
    """Extrait la bibliographie avec DeepSeek."""
    print("=" * 80)
    print("EXTRACTION DE BIBLIOGRAPHIE - DEEPSEEK")
    print("=" * 80)

    # Extraire zone biblio
    bibliography_text = extract_bibliography_region(pdf_path)

    if not bibliography_text.strip():
        raise ValueError("Aucun texte extrait")

    # Limiter la taille
    if len(bibliography_text) > 100000:
        print(f"⚠️  Texte trop long ({len(bibliography_text)} chars), troncature")
        bibliography_text = bibliography_text[:100000]

    # Appel DeepSeek
    print("\n🤖 Connexion à DeepSeek...")
    client = OpenAI(api_key=api_key, base_url="https://api.deepseek.com")

    system_prompt = create_bibliography_extraction_prompt()
    user_prompt = f"Bibliographie:\n\n{bibliography_text}\n\nJSON:"

    print("🔍 Analyse en cours...")
    response = client.chat.completions.create(
        model="deepseek-chat",
        messages=[
            {"role": "system", "content": system_prompt},
            {"role": "user", "content": user_prompt}
        ],
        temperature=0,  # 0 = plus rapide
        max_tokens=8192
    )

    # Nettoyer réponse
    response_text = response.choices[0].message.content.strip()
    if response_text.startswith("```json"):
        response_text = response_text[7:]
    if response_text.startswith("```"):
        response_text = response_text[3:]
    if response_text.endswith("```"):
        response_text = response_text[:-3]
    response_text = response_text.strip()

    # Parser JSON
    try:
        bibliography = json.loads(response_text)
    except json.JSONDecodeError as e:
        print(f"⚠️  JSON incomplet (probablement tronqué par max_tokens)")
        print(f"   Tentative de réparation...")

        # Essayer de réparer le JSON tronqué
        # Si le JSON se termine mal, on essaie de le fermer proprement
        try:
            # Trouver le dernier '}' valide et fermer le tableau
            last_brace = response_text.rfind('}')
            if last_brace > 0:
                repaired = response_text[:last_brace + 1] + '\n]'
                bibliography = json.loads(repaired)
                print(f"   ✅ JSON réparé: {len(bibliography)} références récupérées")
            else:
                raise e
        except:
            print(f"❌ Impossible de réparer le JSON")
            print(f"Réponse (500 premiers caractères): {response_text[:500]}")
            raise e

    print(f"\n✅ {len(bibliography)} références extraites")

    # Sauvegarder
    if output_path is None:
        output_path = "JSONExport/extracted_bibliography.json"

    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(bibliography, f, ensure_ascii=False, indent=2)

    print(f"💾 Sauvegardé: {output_path}")
    print("=" * 80)

    return bibliography


def main():
    """Point d'entrée."""
    if len(sys.argv) < 2:
        print("Usage: python extract_simple.py <pdf> [output.json]")
        return

    pdf_path = sys.argv[1]
    output_path = sys.argv[2] if len(sys.argv) > 2 else None

    if not os.path.exists(pdf_path):
        print(f"❌ Fichier introuvable: {pdf_path}")
        return

    try:
        extract_bibliography_with_deepseek(pdf_path, output_path)
    except Exception as e:
        print(f"\n❌ Erreur: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()

