"""
Module d'extraction de bibliographie depuis un PDF et conversion en blocs Python.
"""

import re
import json
import pdfplumber
from typing import List, Dict, Optional


def extract_bibliography(pdf_path: str, start_keywords: Optional[List[str]] = None) -> List[str]:
    """
    Extrait les références bibliographiques d'un document PDF.
    
    Args:
        pdf_path: Chemin vers le fichier PDF
        start_keywords: Mots-clés pour identifier le début de la bibliographie
                       (par défaut: ["références", "bibliographie", "references", "bibliography"])
    
    Returns:
        Liste des références bibliographiques extraites
    """
    if start_keywords is None:
        start_keywords = ["références", "bibliographie", "references", "bibliography"]
    
    bibliography_started = False
    bibliography_lines = []
    
    with pdfplumber.open(pdf_path) as pdf:
        for page in pdf.pages:
            text = page.extract_text()
            if not text:
                continue
            
            lines = text.split('\n')
            
            for line in lines:
                line = line.strip()
                if not line:
                    continue
                
                # Détecter le début de la bibliographie
                if not bibliography_started:
                    if any(keyword.lower() in line.lower() for keyword in start_keywords):
                        bibliography_started = True
                        continue
                
                # Si la bibliographie a commencé, collecter les lignes
                if bibliography_started:
                    # Ignorer les lignes qui sont probablement des titres de section suivants
                    if line and not line[0].isdigit() and len(line) < 50:
                        # Vérifier si c'est un titre de section (toute la ligne en majuscules, ou court)
                        if line.isupper() or (len(line.split()) <= 3):
                            continue
                    
                    bibliography_lines.append(line)
    
    # Rejoindre les lignes et séparer les références
    # On garde les sauts de ligne pour mieux identifier le début des références
    full_text = '\n'.join(bibliography_lines)
    
    # Si aucune ligne n'a été trouvée, essayer d'extraire depuis toutes les pages
    if not bibliography_lines:
        # Méthode de secours: chercher dans tout le document
        all_text_lines = []
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                text = page.extract_text()
                if text:
                    all_text_lines.extend(text.split('\n'))
        full_text = '\n'.join(all_text_lines)
    
    # Diviser en références individuelles
    references = split_into_references(full_text)
    
    return references


def split_into_references(text: str) -> List[str]:
    """
    Divise le texte en références bibliographiques individuelles.
    
    Args:
        text: Texte contenant les références
    
    Returns:
        Liste des références individuelles
    """
    references = []
    
    # Pattern pour détecter le début d'une référence (numéro, lettre, ou auteur)
    # On cherche les numéros au début des lignes ou après un saut de ligne
    # Pattern principal: numéro suivi d'un point/parenthèse ou crochet
    pattern = r'(?:^|\n)(?:\[?\d+\]?[\.\)]?|^\d+\.)\s+'
    
    # Diviser le texte en références potentielles
    splits = re.split(pattern, text, flags=re.MULTILINE)
    
    # Le premier élément peut être vide ou contenir du texte avant la première référence
    for ref_text in splits[1:]:  # Ignorer le premier split qui peut être vide
        ref_text = ref_text.strip()
        if ref_text and len(ref_text) > 10:  # Ignorer les textes trop courts
            # Nettoyer la référence
            ref_text = re.sub(r'\s+', ' ', ref_text)  # Normaliser les espaces
            references.append(ref_text)
    
    # Si aucune référence n'a été trouvée avec le pattern, essayer une autre méthode
    if not references:
        # Méthode alternative: diviser par lignes qui commencent par un numéro
        lines = text.split('\n')
        current_ref = []
        
        for line in lines:
            line = line.strip()
            if not line:
                continue
            
            # Vérifier si c'est le début d'une nouvelle référence
            is_new_ref = bool(re.match(r'^\d+[\.\)]?\s+', line))
            
            if is_new_ref and current_ref:
                # Sauvegarder la référence précédente
                ref_text = ' '.join(current_ref).strip()
                if ref_text and len(ref_text) > 10:
                    references.append(ref_text)
                current_ref = [line]
            elif current_ref or is_new_ref:
                current_ref.append(line)
        
        # Ajouter la dernière référence
        if current_ref:
            ref_text = ' '.join(current_ref).strip()
            if ref_text and len(ref_text) > 10:
                references.append(ref_text)
    
    return references


def parse_reference(reference: str) -> Dict[str, Optional[str]]:
    """
    Parse une référence bibliographique en composants.
    
    Args:
        reference: Texte de la référence
    
    Returns:
        Dictionnaire avec les composants de la référence
    """
    # Supprimer les numéros de référence au début
    reference = re.sub(r'^\[?\d+\]?[\.\)]\s*', '', reference).strip()
    
    # Pattern pour différents types de références
    parsed = {
        'raw': reference,
        'authors': None,
        'year': None,
        'title': None,
        'journal': None,
        'volume': None,
        'pages': None,
        'publisher': None,
        'doi': None,
        'url': None
    }
    
    # Extraire l'année
    year_match = re.search(r'\b(19|20)\d{2}\b', reference)
    if year_match:
        parsed['year'] = year_match.group()
    
    # Extraire DOI
    doi_match = re.search(r'doi[:\s]+([^\s,]+)', reference, re.IGNORECASE)
    if doi_match:
        parsed['doi'] = doi_match.group(1)
    
    # Extraire URL
    url_match = re.search(r'https?://[^\s]+', reference)
    if url_match:
        parsed['url'] = url_match.group()
    
    # Extraire les pages
    pages_match = re.search(r'pp?\.\s*(\d+(?:[-–]\d+)?)', reference)
    if pages_match:
        parsed['pages'] = pages_match.group(1)
    
    # Extraire le volume
    volume_match = re.search(r'vol\.?\s*(\d+)', reference, re.IGNORECASE)
    if volume_match:
        parsed['volume'] = volume_match.group(1)
    
    # Extraire les auteurs (généralement au début, avant l'année)
    if year_match:
        authors_text = reference[:year_match.start()].strip()
        # Nettoyer
        authors_text = re.sub(r'^[A-Z][a-z]+,\s*', '', authors_text)  # Retirer le premier mot si c'est un nom
        authors_text = re.sub(r'[^a-zA-Z\s,\.]', '', authors_text)
        if authors_text:
            parsed['authors'] = authors_text
    
    return parsed


def convert_to_json_output(references: List[str]) -> str:
    """
    Convertit les références en format JSON.
    
    Args:
        references: Liste des références bibliographiques
    
    Returns:
        JSON formaté représentant les références
    """
    parsed_refs = [parse_reference(ref) for ref in references]
    return convert_to_json(parsed_refs)


def convert_to_json(parsed_refs: List[Dict]) -> str:
    """Convertit en format JSON."""
    # Convertir None en null pour JSON
    json_data = []
    for ref in parsed_refs:
        json_ref = {}
        for key, value in ref.items():
            json_ref[key] = value if value is not None else None
        json_data.append(json_ref)
    
    # Formater avec indentation pour lisibilité
    return json.dumps(json_data, ensure_ascii=False, indent=2)




# Exemple d'utilisation
if __name__ == "__main__":
    import sys
    
    if len(sys.argv) < 2:
        print("Usage: python extract_bibliography.py <pdf_file>")
        sys.exit(1)
    
    pdf_file = sys.argv[1]
    
    print(f"Extraction de la bibliographie depuis {pdf_file}...")
    references = extract_bibliography(pdf_file)
    
    print(f"\n{len(references)} références trouvées.\n")
    print("=" * 80)
    print("JSON généré:\n")
    print("=" * 80)
    
    json_output = convert_to_json_output(references)
    print(json_output)

