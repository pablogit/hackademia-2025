<?php
echo "<!DOCTYPE html>\n";
echo "<html lang=\"en\">\n";
echo "	<head>\n";
echo "		<meta charset=\"UTF-8\" name=\"viewport\" content=\"width=device-width, initial-scale=1\"/>\n";
echo "      <link rel=\"icon\" type=\"image/png\" href=\"favicon.ico\" />\n";
echo "		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/bootstrap.css\"/>\n";
echo "		<link rel=\"stylesheet\" type=\"text/css\" href=\"css/unige.css\"/>\n";
echo "        <title>Outils Alma</title>\n";
echo "	</head>\n";
echo "<body>\n";
echo "	<nav class=\"navbar navbar-default\">\n";
echo "		<div class=\"container-fluid\">\n";
echo "			<a class=\"navbar-item\" href=\"./index.php\"><img src=\"logo.png\" class=\"siteTitleLogo\"></a> &nbsp;&nbsp;Outils Alma\n";
echo "		</div>\n";
echo "	</nav>\n";
echo "	<div class=\"col-md-3\"></div>\n";
echo "	<div class=\"col-md-6 well\">\n";

if ($page == 'batch') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./batch.php\">Modification des notices par lot</a>\n";
    echo "		<h3 class=\"text-primary\">Modifications disponibles</h3>\n";
    echo "        <ul>\n";
    echo "          <li><b><a href=\"batch.php?type=loc\">Localisations</a></b></li>\n";
    echo "          <li><b><a href=\"batch.php?type=classif\">Classifications</a></b></li>\n";
    echo "          <li><b><a href=\"batch.php?type=holding\">Holdings</a></b></li>\n";
    // modif des cotes uniquement pour le CODIS
    if ($monsite == 'CODIS') {
        // echo "          <li><b><a href=\"batch.php?type=classe\">Classes d'exemplaire</a></b></li>\n";
        echo "          <li><b><a href=\"batch.php?type=item\">Items</a></b></li>\n";
        echo "          <li><b><a href=\"batch.php?type=barcode\">Code-barre</a></b></li>\n";
    }
    echo "          <li><b><a href=\"batch.php?type=assoc\">Recherche des holdings associées (catalogage au numéro)</a></b></li>\n";
    echo "        </ul>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
    echo "        <ul>\n";
    echo "          <li><b><a href=\"results.php\">Résultat des modifications par lot</a></b></li>\n";
    // logs uniquement pour les admins
    if ($outil_admin == 1) {
        echo "        <li><b><a href=\"logs.php\">Logs</a></b></li>\n";
    }
    echo "        </ul>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'results') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./batch.php\">Modification par lot</a> > <a href=\"./results.php\">Résultats des modifications</a>\n";
    echo "		<h3 class=\"text-primary\">Résultat des modifications par lot</h3>\n";
    echo "        <ul>\n";
    echo "        <li><b><a href=\"results.php?type=loc\">Modification de la localisation</a></b></li>\n";
    echo "        <li><b><a href=\"results.php?type=classif\">Modification de la classification</a></b></li>\n";
    // modif des cotes uniquement pour le CODIS et Arve
    if ($monsite == 'CODIS' || $monsite == 'ARVE') {
        echo "        <li><b><a href=\"results.php?type=holding\">Modification de la 852 du holding</a></b></li>\n";
    }
    echo "          <li><b><a href=\"results.php?type=assoc\">Recherche des holdings associées (catalogage au numéro)</a></b></li>\n";
    echo "          <li><b><a href=\"results.php?type=barcode\">Modification des Codes-barre</a></b></li>\n";
    echo "        </ul>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'logs') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./logs.php\">Logs</a>\n";
    echo "		<h3 class=\"text-primary\">Log des traitements</h3>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'etiquettes') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./etiquettes.php\">Générateur d'étiquettes</a>\n";
    echo "		<h3 class=\"text-primary\">Générateur d'étiquettes</h3>\n";
    echo "        <ul>\n";
    echo "        <li><b><a href=\"etiquettes.php?site=bastions\">Bastions</a></b></li>\n";
    echo "        <li><b><a href=\"etiquettes.php?site=arve\">Arve</a></b></li>\n";
    echo "        <li><b><a href=\"etiquettes.php?site=dbu\">DBU</a></b></li>\n";
    echo "        </ul>\n";
    echo "      <a href=\"labels/nimbus-sans-l.zip\" target=\"_blank\">Télécharger la police Nimbus Sans L</a><br/>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
    echo "        <ul>\n";
    echo "          <li><b><a href=\"labels/script/results.php?type=pdf\">Fichiers PDF générés</a></b></li>\n";
    echo "          <li><b><a href=\"labels/script/results.php?type=done\">Fichiers Excel uploadés</a></b></li>\n";
    echo "          <li><b><a href=\"labels/script/results.php?type=excel\">Fichiers Excel générés</a></b></li>\n";
    // logs uniquement pour les admins
    if ($outil_admin == 1) {
        echo "        <li><b><a href=\"logs.php\">Logs</a></b></li>\n";
    }
    echo "        </ul>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'vge') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./vge.php\">Demandes d'accès aux ressources numériques de la Ville de Genève</a>\n";
    echo "      <h3 class=\"text-primary\">Liste de personnes avec accord actif</h3>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'acq') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./acq.php\">Factures Alma -> SAP</a>\n";
    echo "      <h3 class=\"text-primary\">Copie des PDFs des factures (Alma -> SAP)</h3>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'usersinfo') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./usersinfo.php\">Données des utilisateurs (rappels)</a>\n";
    echo "		<h4 class=\"text-primary\">Extraction des données des utilisateurs</h4>\n";
    echo "        <ul>\n";
    echo "          <li><b><a href=\"usersinfo_uploads.php\">Fichiers Excel uploadés et traités</a></b></li>\n";
    echo "          <li><b><a href=\"usersinfo_temp.php\">Fichiers Excel uploadés et traitement en cours</a></b></li>\n";
    echo "          <li><b><a href=\"usersinfo_results.php\">Fichiers Excel générés avec succès</a></b></li>\n";
    // logs uniquement pour les sadmins
    if ($outil_sadmin == 1) {
        echo "        <li><b><a href=\"usersinfo_logs.php\">Logs</a></b></li>\n";
    }
    echo "        </ul>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'desherbage') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./desherbage.php\">Assistance au désherbage</a>\n";
    echo "		<h4 class=\"text-primary\">Assistance au désherbage : Ajout des localisations de l'IZ UNIGE, la NZ et Renouvaud</h4>\n";
    echo "        <ul>\n";
    echo "          <li><b><a href=\"desherbage_uploads.php\">Fichiers Excel uploadés et traités</a></b></li>\n";
    echo "          <li><b><a href=\"desherbage_temp.php\">Fichiers Excel uploadés et traitement en cours</a></b></li>\n";
    echo "          <li><b><a href=\"desherbage_results.php\">Fichiers Excel des résultats générés avec succès</a></b></li>\n";
    // logs uniquement pour les sadmins
    if ($outil_sadmin == 1) {
        echo "        <li><b><a href=\"desherbage_logs.php\">Logs</a></b></li>\n";
    }
    echo "        </ul>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'rfid') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./rfid.php\">Assistance aux inventaires RFID</a>\n";
    echo "		<h4 class=\"text-primary\">Comparaison des inventaires avec les données sur Alma</h4>\n";
    echo "        <ul>\n";
    echo "          <li><b><a href=\"rfid_uploads.php\">Fichiers CSV uploadés et traités</a></b></li>\n";
    echo "          <li><b><a href=\"rfid_temp.php\">Fichiers CSV uploadés et traitement en cours</a></b></li>\n";
    echo "          <li><b><a href=\"rfid_results.php\">Fichiers Excel des résultats générés avec succès</a></b></li>\n";
    // logs uniquement pour les sadmins
    if ($outil_sadmin == 1) {
        echo "        <li><b><a href=\"rfid_logs.php\">Logs</a></b></li>\n";
    }
    echo "        </ul>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

if ($page == 'refcheck') {
    echo "		<a href=\"./index.php\">Accueil</a> > <a href=\"./refcheck.php\">RefCheck - Vérificateur de Références</a>\n";
    echo "		<hr style=\"border-top:1px dotted #ccc;\"/>\n";
}

echo "		<div class=\"col-md-12\">\n";
?>