class Thesis:
    FIELDS = [
        "title", "authors", "year",
        "institution", "thesis_type", "department",
        "advisors", "pages", "location"
    ]

    WEIGHTS = {
        "title":        0.50,
        "authors":      0.40,
        "year":         0.10,
        "institution":  0.35,
        "thesis_type":  0.25,
        "department":   0.15,
        "advisors":     0.10,
        "pages":        0.05,
        "location":     0.05
    }

    def __init__(self, **fields):
        self.type = "thesis"
        self.fields = fields

