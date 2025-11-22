class JournalArticle:
    FIELDS = [
        "title", "authors", "year",
        "journal", "volume", "issue",
        "pages", "doi"
    ]

    WEIGHTS = {
        "title":   0.45,
        "authors": 0.35,
        "year":    0.10,
        "journal": 0.20,
        "volume":  0.08,
        "issue":   0.05,
        "pages":   0.05,
        "doi":     0.25
    }

    def __init__(self, **fields):
        self.type = "article"
        self.fields = fields  # parsed dict (title, authors, ...)

