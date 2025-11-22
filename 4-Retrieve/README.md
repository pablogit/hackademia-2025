# hackademia-2025
Repo pour le projet RefCheck de HackademIA 2025

## Librairies utiles

* pyPDF
* scholarly 

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
