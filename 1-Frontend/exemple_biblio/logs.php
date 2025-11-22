<?php
$user_name = strtolower($_SERVER['surname']);
$user_mail = strtolower($_SERVER['mail']);

// connexion à la DB
include "db.php";

// droits d'accès
$myapp = "logs";
include "droits.php";

// page
$page = "logs";
include "header.php";
$directory = "./logs/";
$montitre = "";

// boucle des fichiers
$files = array();
$dir = opendir($directory);
while(false != ($file = readdir($dir))) {
    if(($file != ".") and ($file != "..") and ($file != "index.php") and ($file != "acq")) {
            $files[] = $file;
    }
}

// print de la liste pour chaque type
echo "<h4 class=\"text-primary\">Liste des logs</h4>\n";
echo "<ul>\n";
// tri alpha (contraire de natsort)
rsort($files);
foreach($files as $file) {
    // affichage de toute la liste
    echo "<li><a href=\"" . $directory . $file . "\">";
    echo $file;
    echo "</a></li>\n";
}
echo "</ul>\n";

include "footer.php";
?>