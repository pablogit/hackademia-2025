<?php
$user_name = strtolower($_SERVER['surname']);
$user_mail = strtolower($_SERVER['mail']);

// connexion à la DB
include "db.php";

// droits d'accès
$myapp = "lot";
include "droits.php";

// page
$page = "results";
include "header.php";
$directory = "./results/";
$montitre = "";

if (isset($_GET['type']) && $_GET['type'] == "loc") {
    $montitre = "localisation";
}

if (isset($_GET['type'])  && $_GET['type'] == "classif") {
    $montitre = "classificataion";
}

if (isset($_GET['type']) && $_GET['type'] == "assoc") {
    $montitre = "recherche des holdings associées (catalogage au numéro)";
}

if (isset($_GET['type']) && $_GET['type'] == "holding") {
    $montitre = "852";
}

if (isset($_GET['type']) && $_GET['type'] == "barcode") {
    $montitre = "Codes-barre";
}

// affichage des fichiers si un type est choisi
if ($montitre != "") {
    // boucle des fichiers
    $files = array();
    $dir = opendir($directory);
    while(false != ($file = readdir($dir))) {
        if(($file != ".") and ($file != "..") and ($file != "index.php")) {
                $files[] = $file;
        }
    }

    // print de la liste pour chaque type
    echo "<h4 class=\"text-primary\">Modification de la " . $montitre . "</h4>\n";
    echo "<ul>\n";
    // tri alpha (contraire de natsort)
    rsort($files);
    foreach($files as $file) {
        $mesinfos = explode("_;_",$file);
        $batch_type = $mesinfos[0];
        $batch_date = $mesinfos[1];
        $batch_site = $mesinfos[2];
        $batch_user = strtolower($mesinfos[3]);
        $batch_file = $mesinfos[4];
        $batch_file = str_replace("_xlsx_results", "", $batch_file);
        if (($batch_type == $_GET['type']) || ($batch_type == 'cote' && $_GET['type'] == 'holding')) {
            // affichage pour les super admins
            if ($outil_sadmin == 1) {
                // affichage de toute la liste
                echo "<li><a href=\"" . $directory . $file . "\">";
                echo $batch_file . " - Date : " . substr($batch_date, 0, 4) . "-" . substr($batch_date, 4, 2) . "-" . substr($batch_date, 6, 2) . " " . substr($batch_date, 8, 2) . ":" . substr($batch_date, 10, 2) . ":" . substr($batch_date, 12);
                echo " - Username : " . $batch_user;
                echo "</a></li>\n";
            }
            else {
                // limitation pour les admins du site
                if ($outil_sadmin == 0 && $outil_admin == 1) {
                    // affichage des fichiers de la personne ou du site
                    if (($user_name == $batch_user) || ($monsite == $batch_site)) {
                        echo "<li><a href=\"" . $directory . $file . "\">";
                        echo $batch_file . " - Date : " . substr($batch_date, 0, 4) . "-" . substr($batch_date, 4, 2) . "-" . substr($batch_date, 6, 2) . " " . substr($batch_date, 8, 2) . ":" . substr($batch_date, 10, 2) . ":" . substr($batch_date, 12);
                        echo " - Username : " . $batch_user;
                        echo "</a></li>\n";
                    }
                }
                if  ($outil_sadmin == 0 && $outil_admin == 0) {
                    // affichage des fichiers de la personne seulement
                    if ($user_name == $batch_user) {
                        echo "<li><a href=\"" . $directory . $file . "\">";
                        echo $batch_file . " - Date : " . substr($batch_date, 0, 4) . "-" . substr($batch_date, 4, 2) . "-" . substr($batch_date, 6, 2) . " " . substr($batch_date, 8, 2) . ":" . substr($batch_date, 10, 2) . ":" . substr($batch_date, 12);
                        echo " - Username : " . $batch_user;
                        echo "</a></li>\n";
                    }
                }
            }
        }
    }
    echo "</ul>\n";
}

include "footer.php";
?>