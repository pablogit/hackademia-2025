import json
import re
import requests
import xmltodict
import bibtexparser

# ======================================================
# ID DETECTION
# ======================================================

def detect_doi(text):
    m = re.search(r"\b10\.\d{4,9}/[-._;()/:A-Za-z0-9]+\b", text)
    return m.group(0) if m else None

def detect_pmid(text):
    m = re.search(r"\b\d{8}\b", text)
    return m.group(0) if m else None

def detect_arxiv(text):
    m = re.search(r"\d{4}\.\d{4,5}(v\d+)?", text)
    return m.group(0) if m else None

def detect_identifier(text):
    if detect_doi(text): return ("doi", detect_doi(text))
    if detect_pmid(text): return ("pmid", detect_pmid(text))
    if detect_arxiv(text): return ("arxiv", detect_arxiv(text))
    return (None, None)

# ======================================================
# RETRIEVERS
# ======================================================

def retrieve_crossref(doi):
    url = f"https://api.crossref.org/works/{doi}"
    r = requests.get(url)
    if r.status_code != 200:
        return None

    data = r.json().get("message", {})

    # Sécurise tous les champs potentiellement manquants
    title_list = data.get("title") or [""]
    title = title_list[0] if isinstance(title_list, list) else title_list

    author_list = data.get("author") or []
    authors = []
    for a in author_list:
        if isinstance(a, dict):
            authors.append(f"{a.get('given', '')} {a.get('family', '')}".strip())
        else:
            authors.append(str(a))

    journal_list = data.get("container-title") or [""]
    journal = journal_list[0] if isinstance(journal_list, list) else journal_list

    year = None
    if "issued" in data and "date-parts" in data["issued"]:
        year = data["issued"]["date-parts"][0][0]

    return {
        "id_type": "doi",
        "identifier": doi,
        "title": title,
        "author": authors,
        "journal": journal,
        "year": year,
        "page": data.get("page"),
        "volume": data.get("volume"),
        "doi": data.get("DOI", doi),
        "source": "crossref",
        "found": True
    }

def retrieve_pubmed(pmid):
    url = f"https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&id={pmid}&retmode=json"
    r = requests.get(url)

    if r.status_code != 200:
        return None
    
    entry = r.json()["result"].get(pmid)
    if not entry:
        return None

    doi = ""
    for idinfo in entry.get("articleids", []):
        if idinfo.get("idtype") == "doi":
            doi = idinfo.get("value")

    return {
        "id_type": "pmid",
        "identifier": pmid,
        "title": entry.get("title", ""),
        "author": [a.get("name") for a in entry.get("authors", [])],
        "journal": entry.get("fulljournalname", ""),
        "year": entry.get("pubdate", "").split()[0],
        "page": entry.get("pages", None),
        "volume": entry.get("volume", None),
        "doi": doi,
        "source": "pubmed",
        "found": True
    }

def retrieve_arxiv(arxiv_id):
    url = f"http://export.arxiv.org/api/query?id_list={arxiv_id}"
    r = requests.get(url)

    if r.status_code != 200:
        return None

    xml = xmltodict.parse(r.text)
    entry = xml.get("feed", {}).get("entry", {})

    if not entry:
        return None

    authors = entry.get("author", [])
    if isinstance(authors, dict):
        authors = [authors]

    return {
        "id_type": "arxiv",
        "identifier": arxiv_id,
        "title": entry.get("title", ""),
        "author": [a.get("name") for a in authors],
        "journal": entry.get("arxiv:journal_ref", None),
        "year": entry.get("published", "")[:4],
        "page": None,
        "volume": None,
        "doi": entry.get("arxiv:doi", None),
        "source": "arxiv",
        "found": True
    }

# ======================================================
# MAIN PIPELINE
# ======================================================

def retrieve_from_bibtex(input_bib, original_out="original.json", trusted_out="trusted.json"):

    # ---- load bibtex ----
    with open(input_bib, "r", encoding="utf8") as f:
        bib_database = bibtexparser.load(f)

    entries = bib_database.entries

    original_list = []
    trusted_list = []

    for e in entries:
        raw_text = json.dumps(e)  # serialisation facile
        id_type, identifier = detect_identifier(raw_text)

        # Save ORIGINAL
        original_list.append({
            "entry_key": e.get("ID", None),
            "original": e,
            "id_type": id_type,
            "identifier": identifier,
        })

        # Retrieve TRUSTED
        if id_type == "doi":
            trusted = retrieve_crossref(identifier)
        elif id_type == "pmid":
            trusted = retrieve_pubmed(identifier)
        elif id_type == "arxiv":
            trusted = retrieve_arxiv(identifier)
        else:
            trusted = {
                "found": False,
                "error": "identifier_not_detected",
                "entry_key": e.get("ID")
            }

        trusted_list.append(trusted)

    # ---- write outputs ----
    with open(original_out, "w", encoding="utf8") as f:
        json.dump(original_list, f, indent=2)

    with open(trusted_out, "w", encoding="utf8") as f:
        json.dump(trusted_list, f, indent=2)

    print(f"✔️ Fichiers générés : {original_out}, {trusted_out}")


if __name__ == "__main__":
    retrieve_from_bibtex("original2.bib")
