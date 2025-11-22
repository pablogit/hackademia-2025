# hackademia-2025
Repo pour le projet RefCheck de HackademIA 2025

## Bases de données pour obtenir les métadonnées

* CrossRef : DOI
* PubMed : PMID
* ArXiv : ArXivID
* OpenAlex : PID 

## CrossRef structure :
DOI
type
publisher
prefix
container-title (nom du journal)
volume, issue
ISSN, issn-type
URL

## paths
        titre : $.message.title
        auteur : $.message.author
        journal : $.message.container-title
        annee : message.issued.date-parts
        page : $.message.page (but not in the example)
        volume : $.message.volume
        DOI : $.message.DOI

## Arxives
