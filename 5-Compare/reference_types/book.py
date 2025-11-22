class Book:
    FIELDS = [
        "title", "authors", "year",
        "publisher", "isbn", "place"
    ]

    WEIGHTS = {
        "title":     0.40,
        "authors":   0.35,
        "year":      0.10,
        "publisher": 0.25,
        "isbn":      0.30,
        "place":     0.05
    }

    def __init__(self, **fields):
        self.type = "book"
        self.fields = fields

