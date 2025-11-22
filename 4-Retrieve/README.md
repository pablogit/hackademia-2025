# hackademia-2025
Repo pour le projet RefCheck de HackademIA 2025

## Bases de données pour obtenir les métadonnées

* CrossRef : DOI
* PubMed : PMID
* ArXiv : ArXivID
* OpenAlex : PID 

## Librairies

* https://github.com/fabiobatalha/crossrefapi

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

http://arxiv.org/abs/hep-ex/0307015   --> return XML, see after how to for a json



## Pubmed
https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&id=PMID&retmode=json
example : https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=pubmed&id=31452104&retmode=json

titre : $.result[PMID].title
auteur : $.result[PMID].authors[0].name
journal : $.result[PMID].fulljournalname
annee : $.result[PMID].pubdate
page : $.result[PMID].pages
volume : $.result[PMID].volume
DOI : $.result[PMID].doi  -> si pas de DOI ce champs n'existe pas

## dependencies to install 
pip install requests xmltodict
pip install bibtexparser
to load : python retrieve.py
