from typing import Dict, Any


# Normalize function
from common.normalize import normalize_weight_map

# Reference type weight maps
from reference_types.article import JournalArticle
from reference_types.book import Book
from reference_types.thesis import Thesis

# Try RapidFuzz first, fallback difflib
try:
    from rapidfuzz import fuzz
    def string_similarity(a: str, b: str) -> float:
        if not a or not b:
            return 0.0
        return fuzz.token_set_ratio(a, b) / 100.0
except ImportError:
    from difflib import SequenceMatcher
    def string_similarity(a: str, b: str) -> float:
        if not a or not b:
            return 0.0
        return SequenceMatcher(None, a.lower(), b.lower()).ratio()


# ------------------------------------------------------
# FIELD-LEVEL SIMILARITY FUNCTIONS
# ------------------------------------------------------

def similarity_title(a: Any, b: Any) -> float:
    return string_similarity(str(a or ""), str(b or ""))


def similarity_authors(a_list: Any, b_list: Any) -> float:
    if not a_list or not b_list:
        return 0.0

    a_list = [str(x) for x in a_list]
    b_list = [str(x) for x in b_list]

    scores = []
    for a in a_list:
        best = 0.0
        for b in b_list:
            s = string_similarity(a, b)
            if s > best:
                best = s
        scores.append(best)

    return sum(scores) / len(scores) if scores else 0.0


def similarity_year(a: Any, b: Any) -> float:
    if a is None or b is None:
        return 0.0
    try:
        a = int(a)
        b = int(b)
    except ValueError:
        return 0.0

    if a == b:
        return 1.0
    if abs(a - b) == 1:
        return 0.7
    return 0.0


def _parse_pages(val):
    if val is None:
        return None, None
    s = str(val).replace("–", "-").strip()
    if "-" in s:
        parts = s.split("-")
        if len(parts) >= 2:
            try:
                return int(parts[0]), int(parts[1])
            except ValueError:
                return None, None
    else:
        try:
            p = int(s)
            return p, p
        except ValueError:
            return None, None
    return None, None


def similarity_pages(a, b):
    if not a or not b:
        return 0.0

    sa, ea = _parse_pages(a)
    sb, eb = _parse_pages(b)

    if sa is None or sb is None:
        return string_similarity(str(a), str(b))

    if sa == sb and ea == eb:
        return 1.0

    # Range intersects
    if not (ea < sb or eb < sa):
        return 0.7

    return 0.0


def similarity_exact(a, b):
    if not a or not b:
        return 0.0
    return 1.0 if str(a).strip().lower() == str(b).strip().lower() else 0.0


def similarity_generic(a, b):
    return string_similarity(str(a or ""), str(b or ""))


# ------------------------------------------------------
# FIELD DISPATCHER
# ------------------------------------------------------

def get_field_similarity(field: str, a_val: Any, b_val: Any) -> float:
    field = field.lower()

    if field == "title":
        return similarity_title(a_val, b_val)

    if field == "authors":
        return similarity_authors(a_val, b_val)

    if field == "year":
        return similarity_year(a_val, b_val)

    if field in {"pages"}:
        return similarity_pages(a_val, b_val)

    if field in {"doi", "isbn"}:
        return similarity_exact(a_val, b_val)

    if field in {
        "journal", "venue", "publisher",
        "institution", "thesis_type", "department",
        "location", "place"
    }:
        return similarity_generic(a_val, b_val)

    return similarity_generic(a_val, b_val)


# ------------------------------------------------------
# MAIN FUNCTION: weighted similarity computation
# ------------------------------------------------------

def compute_reference_similarity(original: Dict[str, Any],
                                 trusted: Dict[str, Any],
                                 weights: Dict[str, float]):
    """
    original: parsed from reference (dict)
    trusted: metadata from DB (dict)
    weights: weight map for that reference type
    """
    norm_weights = normalize_weight_map(weights)

    per_field_scores = {}
    final_score = 0.0

    for field, w in norm_weights.items():
        if w <= 0:
            continue

        a_val = original.get(field)
        b_val = trusted.get(field)

        if (a_val is None or a_val == "") and (b_val is None or b_val == ""):
            continue

        s = get_field_similarity(field, a_val, b_val)
        per_field_scores[field] = s
        final_score += s * w

    return {
        "final_score": final_score,
        "per_field": per_field_scores,
        "weights": norm_weights
    }


# ------------------------------------------------------
# WRAPPER FUNCTIONS PER TYPE
# ------------------------------------------------------

def compare_journal_article(original, trusted):
    return compute_reference_similarity(
        original,
        trusted,
        JournalArticle.WEIGHTS
    )


def compare_book(original, trusted):
    return compute_reference_similarity(
        original,
        trusted,
        Book.WEIGHTS
    )


def compare_thesis(original, trusted):
    return compute_reference_similarity(
        original,
        trusted,
        Thesis.WEIGHTS
    )
