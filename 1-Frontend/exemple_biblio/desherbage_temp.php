<?php
$user_name = strtolower($_SERVER['surname']);
$user_mail = strtolower($_SERVER['mail']);

// connexion à la DB
include "db.php";

// droits d'accès
$myapp = "desherbage";
include "droits.php";

// page
$page = "desherbage";
include "header.php";
$directory = "./desherbage/temp/";

// boucle des fichiers
$files = array();
$dir = opendir($directory);
while(false != ($file = readdir($dir))) {
    if(($file != ".") and ($file != "..") and ($file != "index.php")) {
            $files[] = $file;
    }
}

// print de la liste
echo "<h4 class=\"text-primary\">Liste des fichiers uploadés et en traitement (ou dont le traitement a été arrêté par une erreur du script)</h4>\n";
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
    // $batch_file = str_replace("_xlsx_results", "", $batch_file);
    echo "<li><a href=\"" . $directory . $file . "\">";
    echo $batch_file . " - Date : " . substr($batch_date, 0, 4) . "-" . substr($batch_date, 4, 2) . "-" . substr($batch_date, 6, 2) . " " . substr($batch_date, 8, 2) . ":" . substr($batch_date, 10, 2) . ":" . substr($batch_date, 12);
    echo " - Username : " . $batch_user;
    echo "</a></li>\n";
}
echo "</ul>\n";

include "footer.php";
?>