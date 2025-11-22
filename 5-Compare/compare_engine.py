# compare_engine.py

from typing import Dict, Any

# Similarity functions
from similarity.similarity import (
    compare_journal_article,
    compare_book,
    compare_thesis
)

# Reference type classes (just to know weights exist)
from reference_types.article import JournalArticle
from reference_types.book import Book
from reference_types.thesis import Thesis


# -----------------------------------------------------
# TYPE DETECTION (simple but robust rule-based)
# -----------------------------------------------------

def detect_reference_type(parsed: Dict[str, Any]):
    keys = {k.lower() for k in parsed.keys()}

    # Thesis detection
    if {"institution", "thesis_type"} & keys:
        return "thesis"

    # Book detection
    if {"publisher", "isbn"} & keys:
        return "book"

    # Article detection
    if {"journal", "volume", "issue", "doi"} & keys:
        return "article"

    # Additional heuristic for books
    if "publisher" in keys:
        return "book"

    return None


# -----------------------------------------------------
# MAIN COMPARISON ENGINE
# -----------------------------------------------------

def compare_references(original: Dict[str, Any],
                       trusted: Dict[str, Any]) -> Dict[str, Any]:

    # 1. Find reference type from parsed fields
    ref_type = detect_reference_type(original)

    if ref_type is None:
        raise ValueError(f"Could not detect reference type: {original}")

    # 2. Dispatch to the correct similarity method
    if ref_type == "article":
        result = compare_journal_article(original, trusted)

    elif ref_type == "book":
        result = compare_book(original, trusted)

    elif ref_type == "thesis":
        result = compare_thesis(original, trusted)

    else:
        raise ValueError(f"Unknown reference type: {ref_type}")

    # 3. Add status decision (hallucination detection)
    final_score = result["final_score"]

    if final_score >= 0.90:
        status = "OK"
    elif final_score >= 0.60:
        status = "WARNING"
    else:
        status = "ERROR"

    # 4. Add status and hallucination score
    result["status"] = status
    result["hallucination_score"] = 1 - final_score
    result["reference_type"] = ref_type

    return result

