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
$madate = date('YmdHis');
$uploadOk = 0;

if ($_POST['etape'] != "upload") {
    print "<form method=\"POST\" action=\"desherbage.php\" enctype=\"multipart/form-data\">\n";
    print " <div class=\"form-group\">\n";
    print "     <input type=\"hidden\" id=\"etape\" name=\"etape\" value=\"upload\"/>\n";
    print "     <input type=\"hidden\" id=\"site\" name=\"site\" value=\"" . $monsite . "\"/>\n";
    print "     <label>Selectioner le fichier Excel à uploader</label>\n";
    print "     <input type=\"file\" name=\"fileToUpload\" id=\"fileToUpload\"/>\n";
    print "     <br/>\n";
    print "     <p>Choisir les localisations à ajouter :</p>\n";
    print "     <input type=\"checkbox\" id=\"unige\" name=\"unige\" value=\"1\" checked>\n";
    print "     <label for=\"unige\">UNIGE (correspondance par MMSID IZ)</label><br/>\n";
    print "     <input type=\"checkbox\" id=\"slsp\" name=\"slsp\" value=\"1\" checked>\n";
    print "     <label for=\"slsp\">Autres IZs SLSP (correspondance par MMSID NZ)</label><br/>\n";
    print "     <input type=\"checkbox\" id=\"slsp_isbn\" name=\"slsp_isbn\" value=\"1\">\n";
    print "     <label for=\"slsp\">Autres IZs SLSP (correspondance par ISBN)</label><br/>\n";
    print "     <input type=\"checkbox\" id=\"renouvaud\" name=\"renouvaud\" value=\"1\">\n";
    print "     <label for=\"renouvaud\">Renouvaud (correspondance par ISBN)</label><br/>\n";
    print "     <br/>\n";
    print " </div>\n";
    print " <button class=\"btn btn-primary\" id=\"register\" name=\"submit\">Upload</button>\n";
    print "</form>\n";
    print "<script>\n";
    print "var checkbox = document.getElementById(\"titre\");\n";
    print "checkbox.disabled = true;\n";
    print "</script>\n";
    print "</br>\n";
    print "<label>Instructions</label>\n";
    print "<p>Le fichier Excel doit contenir une colonne avec l'intitulé précis <b>Code-barres</b> ou <b>MMSID</b>. L'ordre des colonnes n'a pas d'importance et le fichier peut contenir d'autres colonnes, elles seront ignorées mais attention, le fichier ne doit pas contenir de colonnes avec ces intitulés car ils sont ajoutés par le programme et cela provoque un conflit : <b>LOC<b>, </b>MMSID_NZ<b>, </b>ARVE<b>, </b>BASTIONS<b>, </b>CMU<b>, </b>DBU<b>, </b>MAIL<b>, </b>FHARDT<b>, </b>UNIGE_LOCS<b>. Pour les fichiers avec code-barres uniquement, le script récupère automatiquement sur Alma les MMSID (IZ et NZ) et les ISBN</p>\n";
    print "<div class=\"alert alert-danger\">Attention, la recherche d'occurrences se fait sur la base des MMSIDs. Pour les ouvrages en plusieurs volumes qui ont le même MMSID les chiffres ne s'appliquent pas au volume en particulier, ils correspondent à la notice globale.</div></br>\n";
}


if ($_POST['etape'] == "upload") {
    $target_dir = "./desherbage/uploads/";
    $montype = "desherbage";
    $desherbage_site = $_POST['site'];
    $desherbage_unige = $_POST['unige'];
    $desherbage_slsp = $_POST['slsp'];
    $desherbage_slsp_isbn = $_POST['slsp_isbn'];
    $desherbage_renouvaud = $_POST['renouvaud'];
    $myfilename = basename($_FILES["fileToUpload"]["name"]);

    if ($desherbage_unige != 1 & $desherbage_slsp != 1 & $desherbage_slsp_isbn != 1 & $desherbage_renouvaud != 1) {
        print "    <div class=\"alert alert-danger\">Erreur : Vous devez sélectionner au moins une option</div>\n";
        print "<br/><button onclick=\"history.go(-1)\">Retourner au formulaire</button></br></br>";
    } else {
        // remplacement si le fichier est un produit par le système
        if (strpos($myfilename, "_;_") !== false) {
            $myfilename2 = explode("_;_",$myfilename);
            $myfilename2a = $myfilename2[-1];
            if ($myfilename2a == "") {
                $myfilename2a = $myfilename2[-2];
            }
            if ($myfilename2a != "") {
                $myfilename = myfilename2a;
            }
        }
        $myfilename = str_replace(" ", "_", $myfilename);
        $myfilename = str_replace(";", "_", $myfilename);
        $myfilename = $montype . "_;_" . $madate . "_;_" . $monsite . "_;_" . $user_name . "_;_" . $myfilename;
        $target_file = $target_dir . $myfilename;
        $uploadOk = 1;
        $file_name_original = htmlspecialchars(basename($_FILES["fileToUpload"]["name"]));
        $uploadFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
          echo "Désolé, le fichier existe déjà.</br></br>";
          $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 5000000) {
          echo "Désolé, votre fichier est trop gros (" . $_FILES["fileToUpload"]["size"] . " octets).</br></br>";
          echo "Désolé, votre fichier est trop gros (l'upload est limité à 5Mo). Veillez supprimer des onglets ou des colonnes inutiles et réessayez.</br></br>";
          $uploadOk = 0;
        }

        // Allow certain file formats
        if ($uploadFileType != "xls" && $uploadFileType != "xlsx") {
          echo "Désolé seulement des fichiers Excel sont admis.</br></br>";
          $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
          echo "Désolé, votre fichier n'a pas pu être uploadé.</br></br>";
        // if everything is ok, try to upload file
        } else {
          if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "Le fichier ". $file_name_original . " a bien été uploadé.</br></br>";
          } else {
            echo "Désolé, une erreur est arrivé pendant l'upload de votre fichier</br></br>";
          }
        }
        
        if ($uploadOk == 1) {
            print "<form method=\"POST\" action=\"desherbage_script.php\">\n";
            print " <div class=\"form-group\">\n";
            print "     <input type=\"hidden\" id=\"etape\" name=\"etape\" value=\"exec\"/>\n";
            print "     <input type=\"hidden\" id=\"type\" name=\"type\" value=\"" . $montype . "\"/>\n";
            print "     <input type=\"hidden\" id=\"unige\" name=\"unige\" value=\"" . $desherbage_unige . "\"/>\n";
            print "     <input type=\"hidden\" id=\"slsp\" name=\"slsp\" value=\"" . $desherbage_slsp . "\"/>\n";
            print "     <input type=\"hidden\" id=\"slsp_isbn\" name=\"slsp_isbn\" value=\"" . $desherbage_slsp_isbn . "\"/>\n";
            print "     <input type=\"hidden\" id=\"renouvaud\" name=\"renouvaud\" value=\"" . $desherbage_renouvaud . "\"/>\n";
            print "     <input type=\"hidden\" id=\"site\" name=\"site\" value=\"" . $desherbage_site . "\"/>\n";
            print "     <input type=\"hidden\" id=\"file\" name=\"file\" value=\"". $myfilename . "\"/>\n";
            print " <button class=\"btn btn-primary\" id=\"exec\" name=\"submit\">Lancer le script avec le fichier " . $file_name_original . "</button>\n";
            print " </div>\n";
            print "</form>\n";
        }
    }
}

include "footer.php";
?>