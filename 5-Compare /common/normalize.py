def normalize_weight_map(weight_map: dict) -> dict:
    total = sum(weight_map.values())
    if total == 0:
        return {k: 0 for k in weight_map}
    return {k: v / total for k, v in weight_map.items()}
