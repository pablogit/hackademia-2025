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


if ($_POST['etape'] == "exec") {
    $myfile = $_POST['file'];
    $myfileOk = 0;
    $mesinfos = explode("_;_",$myfile);
    $mydesherbage_type = $mesinfos[0];
    $mydesherbage_date = $mesinfos[1];
    $mydesherbage_user = strtolower($mesinfos[3]);
    $mydesherbage_user = str_replace("-", "", $batch_user);
    $mydesherbage_log = $mydesherbage_date . "_" . $mydesherbage_user . "_" . $mydesherbage_type . ".log";
    $mydesherbage_site = $_POST['site'];
    $mydesherbage_unige = $_POST['unige'];
    $mydesherbage_slsp = $_POST['slsp'];
    $mydesherbage_slsp_isbn = $_POST['slsp_isbn'];
    $mydesherbage_renouvaud = $_POST['renouvaud'];
    if ($mydesherbage_unige != 1) {
        $mydesherbage_unige = 0;
    }
    if ($mydesherbage_slsp != 1) {
        $mydesherbage_slsp = 0;
    }
    if ($mydesherbage_slsp_isbn != 1) {
        $mydesherbage_slsp_isbn = 0;
    }
    if ($mydesherbage_renouvaud != 1) {
        $mydesherbage_renouvaud = 0;
    }
    
    print "<h4 class=\"text-primary\">Execution du script</h4>\n";
    // Check if file already exists
    $target_dir = "desherbage/uploads/";
    $target_file = $target_dir . $myfile;
    if (file_exists($target_file)) {
        // print "Fichier OK";
        $myfileOk = 1;
    }
    else {
        print "Désolé, le fichier n'existe pas.</br></br>";
    }
    print "</br>\n";
    if ($myfileOk == 1) {
        print "<b>Le script a été lancé</b>. Le traitement peut prendre un certain temps selon la taille des fichiers. Vous pouvez fermer cette fenêtre ou effectuer une autre action, <b>vous serez informé par email à la fin du traitement</b></br></br>";
        // print("python3 desherbage.py '" . $myfile . "' " . $user_mail . " " . $mydesherbage_site . " "  . $mydesherbage_unige . " " . $mydesherbage_slsp . " " . $mydesherbage_slsp_isbn . " " . $mydesherbage_renouvaud . " >> /home/www/slsp/outils/desherbage/logs/" . $mydesherbage_log . " 2>&1 &");
        shell_exec("python3 desherbage.py '" . $myfile . "' " . $user_mail . " " . $mydesherbage_site . " "  . $mydesherbage_unige . " " . $mydesherbage_slsp . " " . $mydesherbage_slsp_isbn . " " . $mydesherbage_renouvaud . " >> /home/www/slsp/outils/desherbage/logs/" . $mydesherbage_log . " 2>&1 &");
    }
}
else {
    print "Désolé, une erreur s'est produite, cette page ne peut pas être affichée.</br></br>";
}

include "footer.php";
?>