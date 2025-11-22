#!/usr/bin/env python3
"""
Integration script to compare original references with trusted data.
Reads from 4-Retrieve output and generates comparison results.
"""

import json
import os
import sys
from compare_engine import compare_references


def normalize_reference(ref_data, is_trusted=False):
    """
    Normalize reference data to a common format for comparison.

    Args:
        ref_data: Reference data dictionary
        is_trusted: Whether this is from trusted source

    Returns:
        Normalized dictionary
    """
    normalized = {}

    # Handle title
    if 'title' in ref_data:
        title = ref_data['title']
        # If title is a list, take first element
        normalized['title'] = title[0] if isinstance(title, list) else title

    # Handle authors
    if 'author' in ref_data:
        authors = ref_data['author']
        if isinstance(authors, str):
            # Split by 'and' for BibTeX format
            normalized['authors'] = [a.strip() for a in authors.split(' and ')]
        elif isinstance(authors, list):
            normalized['authors'] = authors

    # Handle year
    if 'year' in ref_data:
        year = ref_data['year']
        normalized['year'] = int(year) if isinstance(year, str) else year

    # Handle journal/publisher/institution
    if 'journal' in ref_data:
        journal = ref_data['journal']
        normalized['journal'] = journal[0] if isinstance(journal, list) else journal

    if 'publisher' in ref_data:
        normalized['publisher'] = ref_data['publisher']

    if 'institution' in ref_data:
        normalized['institution'] = ref_data['institution']

    # Handle volume, issue, pages
    if 'volume' in ref_data:
        normalized['volume'] = str(ref_data['volume'])

    if 'issue' in ref_data:
        normalized['issue'] = str(ref_data['issue'])

    if 'page' in ref_data:
        page = ref_data['page']
        # Handle None or null values
        if page is not None:
            normalized['pages'] = str(page)

    if 'pages' in ref_data:
        normalized['pages'] = str(ref_data['pages'])

    # Handle DOI
    if 'doi' in ref_data:
        normalized['doi'] = ref_data['doi']

    # Handle ISBN
    if 'isbn' in ref_data:
        normalized['isbn'] = ref_data['isbn']

    # Handle thesis-specific fields
    if 'thesis_type' in ref_data:
        normalized['thesis_type'] = ref_data['thesis_type']

    if 'department' in ref_data:
        normalized['department'] = ref_data['department']

    return normalized


def load_json_file(filepath):
    """Load JSON file and return data."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            return json.load(f)
    except FileNotFoundError:
        print(f"Error: File not found: {filepath}")
        return None
    except json.JSONDecodeError as e:
        print(f"Error: Invalid JSON in {filepath}: {e}")
        return None


def main():
    # Paths
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    original_path = os.path.join(base_dir, "4-Retrieve", "original.json")
    trusted_path = os.path.join(base_dir, "4-Retrieve", "trusted.json")
    output_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "comparison_results.json")

    print(f"Loading original references from: {original_path}")
    print(f"Loading trusted references from: {trusted_path}")
    print(f"Output will be saved to: {output_path}\n")

    # Load data
    original_data = load_json_file(original_path)
    trusted_data = load_json_file(trusted_path)

    if original_data is None or trusted_data is None:
        print("Failed to load input files. Exiting.")
        sys.exit(1)

    # Remove old output file if exists
    if os.path.exists(output_path):
        os.remove(output_path)
        print(f"Removed old output file: {output_path}\n")

    # Create mapping of identifiers to trusted data
    trusted_map = {}
    for trusted_ref in trusted_data:
        identifier = trusted_ref.get('identifier')
        if identifier:
            trusted_map[identifier] = trusted_ref

    # Compare each original reference with its trusted counterpart
    total = len(original_data)
    compared = 0
    skipped = 0

    print(f"Starting comparison of {total} references...\n")

    for i, original_entry in enumerate(original_data, 1):
        # Get identifier
        identifier = original_entry.get('identifier')

        if not identifier:
            print(f"[{i}/{total}] Skipped (no identifier): {original_entry.get('entry_key', 'unknown')}")
            skipped += 1
            continue

        # Find corresponding trusted reference
        trusted_ref = trusted_map.get(identifier)

        if not trusted_ref:
            print(f"[{i}/{total}] Skipped (no trusted data): {identifier}")
            skipped += 1
            continue

        if not trusted_ref.get('found', False):
            print(f"[{i}/{total}] Skipped (not found in trusted source): {identifier}")
            skipped += 1
            continue

        # Get original data
        original_ref = original_entry.get('original', {})

        # Normalize both references
        original_normalized = normalize_reference(original_ref)
        trusted_normalized = normalize_reference(trusted_ref, is_trusted=True)

        # Compare
        try:
            result = compare_references(
                original_normalized,
                trusted_normalized,
                output_json_path=output_path
            )

            status = result.get('status', 'UNKNOWN')
            score = result.get('final_score', 0)
            title = original_normalized.get('title', 'Unknown')[:50]

            print(f"[{i}/{total}] {status} (score: {score:.2f}): {title}...")
            compared += 1

        except Exception as e:
            print(f"[{i}/{total}] Error comparing {identifier}: {e}")
            skipped += 1
            continue

    # Summary
    print(f"\n{'='*60}")
    print(f"Comparison complete!")
    print(f"Total references: {total}")
    print(f"Successfully compared: {compared}")
    print(f"Skipped: {skipped}")
    print(f"\nResults saved to: {output_path}")
    print(f"{'='*60}")


if __name__ == "__main__":
    main()
