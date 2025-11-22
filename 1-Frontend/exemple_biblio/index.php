<?php
$user_name = isset($_SERVER['surname']) ? strtolower($_SERVER['surname']) : '';
$user_mail = isset($_SERVER['mail']) ? strtolower($_SERVER['mail']) : '';

// connexion à la DB
include "db.php";

// page
$page = "home";
include "header.php";

print "<h3 class=\"text-primary\">Liste d'outils disponibles pour Alma</h3>\n";
print "<ul>\n";

// requêtes
$sql0  = "SELECT * FROM outils WHERE id > 0";
// $sql1  = "SELECT * FROM outils WHERE code = '" . $row0['code'] . "'";
$sql1  = "SELECT * FROM outils";
// obtention du nom de l'application
$myapp_nom = "";
if ($result1 = $conn -> query($sql1)) {
    while ($row1 = $result1 -> fetch_assoc()) {
        $myapp_nom = $row1['nom'];
    }
}   

if ($result0 = $conn -> query($sql0)) {
    while ($row0 = $result0 -> fetch_assoc()) {
        // obtention de l'ouverture ou non de l'outil
        $outil_open = 0;
        $sql1  = "SELECT * FROM outils WHERE code = '" . $row0['code'] . "'";
        if ($result1 = $conn -> query($sql1)) {
            while ($row1 = $result1 -> fetch_assoc()) {
                if ($row1['open'] == 1) {
                    $outil_open = 1;
                }
            }
        }
        // obtention des droits de sur chaque application
        $outil_acces = 0;
        $outil_admin = 0;
        $outil_sadmin = 0;
        $sql1  = "SELECT * FROM outils_droits WHERE email = '" . $user_mail . "' AND outil = '" . $row0['code'] . "'";
        if ($result1 = $conn -> query($sql1)) {
            while ($row1 = $result1 -> fetch_assoc()) {
                if ($row1['droits'] == 1) {
                    $outil_acces = 1;
                }
                if ($row1['droits'] == 2) {
                    $outil_acces = 1;
                    $outil_admin = 1;
                }
                if ($row1['droits'] == 3) {
                    $outil_acces = 1;
                    $outil_admin = 1;
                    $outil_sadmin = 1;
                }
            }
        }
        if ($outil_acces == 1 || $outil_open == 1) {
            echo "    <li><b><a href=\"" . $row0['url'] . "\"  title=\"" . $row0['commentaire'] . "\">" . $row0['nom'] . "</a></b>";
            if ($outil_admin == 1) {
                echo " <a href=\"admin.php?outil=" . $row0['code'] . "\" title=\"gérer les droits sur cet outil\"><img src=\"permissions.png\" height=\"15\"></img></a></li>\n";
            }
        } else {
            echo "    <li><b>" . $row0['nom'] . "</b></li>\n";
        }
    }
}

print "</ul>\n";
print "<br/>\n";
print "<hr/>\n";

include "footer.php";
?>