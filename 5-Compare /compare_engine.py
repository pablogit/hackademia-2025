# compare_engine.py

import json
import os
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

    # Thesis detection (highest priority)
    if {"institution", "thesis_type"} & keys:
        return "thesis"

    # Article detection (check before book, as articles can have publisher too)
    # Articles are characterized by journal, volume, issue, or doi
    if "journal" in keys or "doi" in keys or ("volume" in keys and "issue" in keys):
        return "article"

    # Book detection
    if "isbn" in keys or "publisher" in keys:
        return "book"

    return None


# -----------------------------------------------------
# MAIN COMPARISON ENGINE
# -----------------------------------------------------

def compare_references(original: Dict[str, Any],
                       trusted: Dict[str, Any],
                       output_json_path: str = None) -> Dict[str, Any]:

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

    # Add original title for identification
    result["original_title"] = original.get("title", "Unknown")

    # 5. Save to JSON if path is provided
    if output_json_path:
        save_comparison_to_json(result, output_json_path, original, trusted)

    return result


# -----------------------------------------------------
# JSON OUTPUT FUNCTION
# -----------------------------------------------------

def save_comparison_to_json(result: Dict[str, Any], output_path: str,
                           original: Dict[str, Any], trusted: Dict[str, Any]) -> None:
    """
    Save comparison result to a JSON file as a list.
    Appends to existing list or creates new one.

    Args:
        result: The comparison result dictionary
        output_path: Path where JSON file will be saved
        original: The original reference data
        trusted: The trusted reference data
    """
    # Read existing results if file exists
    results_list = []
    if os.path.exists(output_path):
        try:
            with open(output_path, 'r', encoding='utf-8') as f:
                results_list = json.load(f)
                # Ensure it's a list
                if not isinstance(results_list, list):
                    results_list = [results_list]
        except (json.JSONDecodeError, FileNotFoundError):
            results_list = []

    # Create complete output with original, trusted, and result
    complete_result = {
        "original": original,
        "trusted": trusted,
        "comparison_result": result
    }

    # Append new result
    results_list.append(complete_result)

    # Write back to file
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(results_list, f, indent=2, ensure_ascii=False)

