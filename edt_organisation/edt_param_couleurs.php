<?php

/**
 * Fichiers qui permet de param�trer les couleurs de chaque mati�re des emplois du temps
 *
 * @version $Id$
 * @copyright 2007
 */

$titre_page = "Param�trer les couleurs des mati�res";
$affiche_connexion = 'yes';
$niveau_arbo = 1;

// Initialisations files
require_once("../lib/initialisations.inc.php");

// fonctions edt
require_once("./fonctions_edt.php");

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
   header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
   die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}

// S�curit�
if (!checkAccess()) {
    header("Location: ../logout.php?auto=2");
    die();
}
// CSS et js particulier � l'EdT
$javascript_specifique = "edt_organisation/script/fonctions_edt";
$style_specifique = "edt_organisation/style_edt";
$utilisation_jsdivdrag = "";
//==============PROTOTYPE===============
$utilisation_prototype = "";
//============fin PROTOTYPE=============
// On ins�re l'ent�te de Gepi
require_once("../lib/header.inc");

// On ajoute le menu EdT
require_once("./menu.inc.php");
?>


<br />
<!-- la page du corps de l'EdT -->

	<div id="lecorps">

Ce fichier est encore en chantier.



<?php
// inclusion du footer
require("../lib/footer.inc.php");
?>