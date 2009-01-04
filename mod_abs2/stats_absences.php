<?php
/**
 *
 *
 * @version $Id$
 *
 * Copyright 2001, 2007 Thomas Belliard, Laurent Delineau, Eric Lebrun, Stephane Boireau, Julien Jocal
 *
 * This file is part of GEPI.
 *
 * GEPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GEPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GEPI; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$utiliser_pdo = 'on';
//error_reporting(0);
// Initialisation des feuilles de style apr�s modification pour am�liorer l'accessibilit�
$accessibilite="y";

// Initialisations files
require_once("../lib/initialisations.inc.php");
// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
};
//debug_var();
// ============== traitement des variables ==================


// ============== Code m�tier ===============================
include("../lib/initialisationsPropel.inc.php");


try{

$test = new Utilisateur();
$test = UtilisateurPeer::retrieveByPKs('all');
$test->
$test2 = $test->getGroupes();

  foreach ($test2 as $group){
    echo utf8_decode($group->getDescription());
  }


echo '<pre><br />' . $test3;
print_r($test2);
echo '</pre>';

}catch(exception $e){
  affExceptions($e);
}
//**************** EN-TETE *****************
$titre_page = "Les absences";
require_once("../lib/header.inc");
require("lib/abs_menu.php");
//**************** FIN EN-TETE *****************


?>




<?php require_once("../lib/footer.inc.php"); ?>