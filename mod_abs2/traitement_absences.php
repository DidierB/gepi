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

// L'utilisation d'un observeur javascript
$use_observeur = 'ok';


// Initialisation des feuilles de style apr�s modification pour am�liorer l'accessibilit�
$accessibilite="y";

// Initialisations files
include("../lib/initialisationsPropel.inc.php");
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
include("lib/erreurs.php");
include("../orm/helpers/CreneauHelper.php");


try{

  // On r�cup�re toutes les absences dont le traitement n'est pas clos
  $c = new Criteria();
/**
 * Le code qui suit devrait ordonner la liste par ordre alphab�tique des noms d'�l�ves absents mais renvoie les absences en double.
  $c->addJoin(AbsenceTraitementPeer::ID, JTraitementSaisiePeer::A_TRAITEMENT_ID, Criteria::LEFT_JOIN);
  $c->addJoin(JTraitementSaisiePeer::A_SAISIE_ID, AbsenceSaisiePeer::ID, Criteria::LEFT_JOIN);
  $c->addJoin(AbsenceSaisiePeer::ELEVE_ID, ElevePeer::ID_ELEVE, Criteria::LEFT_JOIN);
  $c->addAscendingOrderByColumn(ElevePeer::NOM);
  $c->addAscendingOrderByColumn(ElevePeer::PRENOM);
*/
  $liste_traitements_en_cours = AbsenceTraitementPeer::doSelect($c);

}catch(exception $e){
  affExceptions($e);
}
//**************** EN-TETE *****************
$javascript_specifique = "mod_abs2/lib/absences_ajax";
$style_specifique = "mod_abs2/lib/abs_style";
$utilisation_win = 'oui';
$titre_page = "Le traitement des absences";
require_once("../lib/header.inc");
require("lib/abs_menu.php");
//**************** FIN EN-TETE *****************

foreach ($liste_traitements_en_cours as $traitements){
  //aff_debug($traitements->getJTraitementSaisies());
  foreach($traitements->getJTraitementSaisies() as $saisies){
    //aff_debug($saisies->getAbsenceSaisie());
    echo($saisies->getAbsenceSaisie()->getEleve()->getLogin()) . ' ' . date("d/m/Y H:i", $saisies->getAbsenceSaisie()->getDebutAbs()) . ' - ' .date("d/m/Y H:i", $saisies->getAbsenceSaisie()->getFinAbs()) . '<br />';
  }
  echo '<hr />';
}

?>




<?php require_once("../lib/footer.inc.php"); ?>