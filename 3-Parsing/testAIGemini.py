import json
import os
import time
import re
from concurrent.futures import ThreadPoolExecutor
from google import genai
from google.genai import types
from pathlib import Path

# Chemin par défaut vers le fichier JSON exporté (depuis le workspace)
# Résout vers: <workspace_root>/2-Extraction/JSONExport/extracted_bibliography.json
# (le script est dans 3-Parsing)
# NOTE: on écrasera plus bas la variable INPUT_FILE si besoin.

# computed default path
DEFAULT_EXTRACTED_JSON = str(
    Path(__file__).resolve().parent.parent / '2-Extraction' / 'JSONExport' / 'extracted_bibliography.json'
)

# ---------------------------------------------------------
# 1. Auth & Config
# ---------------------------------------------------------
try:
    from credentials import googlekey as api_key
except ImportError:
    print("ERREUR : Fichier 'credentials.py' manquant.")
    exit()

client = genai.Client(api_key=api_key)

# Par défaut, utilise le fichier JSON exporté si présent, sinon tombera sur le nom simple
INPUT_FILE = DEFAULT_EXTRACTED_JSON if os.path.exists(DEFAULT_EXTRACTED_JSON) else 'extracted_bibliography.json'
OUTPUT_FILE = 'parsedBibliography.json'

BATCH_SIZE = 5
MAX_WORKERS = 2

# Champs demandés à l'IA (on demande 'entry_type' en plus)
# Note : On utilise 'authors' et 'pages' au pluriel pour l'IA, on renommera après.
SCHEMA_KEYS = [
    "authors", "year", "month", "title", "journal", "volume",
    "issue", "pages", "publisher", "doi", "url", "entry_type"
]

bibliography_schema = {
    "type": types.Type.ARRAY,
    "items": {
        "type": types.Type.OBJECT,
        "properties": {
            k: {"type": types.Type.STRING, "nullable": True} for k in SCHEMA_KEYS
        },
        "required": SCHEMA_KEYS
    }
}


# ---------------------------------------------------------
# 2. Fonctions de Transformation (Le cœur de ta demande)
# ---------------------------------------------------------
def generate_bibtex_key(item):
    """Génère la clé type 'jordan1986an'."""
    try:
        # Auteur (Premier mot)
        author_part = "unknown"
        if item.get("authors"):
            clean_auth = re.sub(r'[^a-zA-Z]', ' ', item["authors"])
            parts = clean_auth.split()
            if parts: author_part = parts[0].lower()

        # Année
        year_part = item.get("year", "0000")
        if not year_part: year_part = "0000"

        # Titre (Premier mot)
        title_part = ""
        if item.get("title"):
            clean_title = re.sub(r'[^a-zA-Z]', ' ', item["title"])
            words = clean_title.split()
            if words: title_part = words[0].lower()

        return f"{author_part}{year_part}{title_part}"
    except:
        return f"ref{int(time.time())}"


def transform_to_target_structure(items):
    """
    Transforme la sortie plate de l'IA en la structure imbriquée demandée :
    {
      "entry_key": "...",
      "original": { ... champs renommés ... },
      "id_type": "...",
      "identifier": "..."
    }
    """
    transformed_list = []

    for item in items:
        # 1. Génération de la clé
        entry_key = generate_bibtex_key(item)

        # 2. Construction de l'objet 'original'
        original_obj = {}

        # Mapping et renommage des champs
        # authors -> author
        if item.get('authors'): original_obj['author'] = item['authors']
        # pages -> page
        if item.get('pages'): original_obj['page'] = item['pages']
        # entry_type -> ENTRYTYPE
        if item.get('entry_type'):
            original_obj['ENTRYTYPE'] = item['entry_type'].lower()  # ex: article
        else:
            original_obj['ENTRYTYPE'] = "misc"  # Valeur par défaut

        # Autres champs directs
        for key in ["year", "month", "journal", "doi", "url", "publisher", "issue", "volume", "title"]:
            if item.get(key):
                original_obj[key] = item[key]

        # Ajout de l'ID dans original
        original_obj['ID'] = entry_key

        # 3. Détermination de id_type et identifier
        id_type = None
        identifier = None

        if item.get('doi'):
            id_type = "doi"
            identifier = item['doi']
        elif item.get('url'):
            id_type = "url"
            identifier = item['url']

        # 4. Assemblage final
        final_entry = {
            "entry_key": entry_key,
            "original": original_obj,
            "id_type": id_type,
            "identifier": identifier
        }

        transformed_list.append(final_entry)

    return transformed_list


# ---------------------------------------------------------
# 3. Tâche Gemini
# ---------------------------------------------------------
def process_batch_task(batch_data):
    try:
        time.sleep(1)
        prompt = """
        Analyse ces références bibliographiques.
        1. Extrais les champs standards.
        2. Pour 'pages', mets l'intervalle (ex: 222-235).
        3. Pour 'issue', mets le numéro.
        4. TRES IMPORTANT : Infère le 'entry_type' BibTeX (ex: article, book, inproceedings, techreport, misc, phdthesis).
        """

        response = client.models.generate_content(
            model="gemini-2.0-flash",
            config=types.GenerateContentConfig(
                system_instruction=prompt,
                response_mime_type="application/json",
                response_schema=bibliography_schema
            ),
            contents=f"Références : {json.dumps(batch_data, ensure_ascii=False)}"
        )
        return json.loads(response.text)
    except Exception as e:
        print(f"\n[!] Erreur lot : {e}")
        return []


# ---------------------------------------------------------
# 4. Main
# ---------------------------------------------------------
def main():
    if not os.path.exists(INPUT_FILE):
        print(f"Fichier '{INPUT_FILE}' introuvable.")
        return

    with open(INPUT_FILE, 'r', encoding='utf-8') as f:
        full_data = json.load(f)

    print(f"Démarrage extraction structurée ({len(full_data)} références)...")

    all_extracted_data = []
    batches = [full_data[i: i + BATCH_SIZE] for i in range(0, len(full_data), BATCH_SIZE)]

    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        results = executor.map(process_batch_task, batches)
        for i, batch_res in enumerate(results):
            if batch_res:
                all_extracted_data.extend(batch_res)
            print(f"Lot {i + 1}/{len(batches)} traité.", end='\r')

    print("\nTransformation des données...")

    # Transformation finale vers ta structure cible
    final_json_structure = transform_to_target_structure(all_extracted_data)

    with open(OUTPUT_FILE, 'w', encoding='utf-8') as f:
        json.dump(final_json_structure, f, indent=2, ensure_ascii=False)

    print(f"Succès ! Résultat enregistré dans '{OUTPUT_FILE}'")
    # Aperçu pour vérification
    if final_json_structure:
        print("Aperçu du format généré :")
        print(json.dumps(final_json_structure[0], indent=2, ensure_ascii=False))


if __name__ == "__main__":
    main()