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
}

// ==================== VARIABLES ===============================
$type_req = isset($_POST["type"]) ? $_POST["type"] : NULL;
$_table = $_champ = NULL;
$_id = isset($_POST["_id"]) ? $_POST["_id"] : NULL;
$prefix = 'abs_';
$ajouter = $modifier = $effacer = NULL;
$action = 'ajouter';

// +++++++++++++++++++++ Code m�tier ++++++++++++++++++++++++++++
include("lib/erreurs.php");
include("classes/abs_gestion.class.php");


try{

  // On v�rifie l'�tat de la variable $type_req
  if (substr($type_req, 0, 7) == "effacer") {
    // Alors on explose $_id en deux et on requalifie $type_req pour la suite du script
    $testons_id = explode("|||", $_id);

    if (count($testons_id) == 2 AND is_numeric($testons_id[1])) {

      $type_req = $testons_id[0];
      $del_id   = $testons_id[1];
      $action = "effacer";
      //echo "<br /> _id = " . $_id . "<br />del_id = " . $del_id . "<br />type_req = " . $type_req; exit();

    }else{

      // On ne fait rien mais une exception sera lev�e car $type_req ne passera pas le switch
      throw new Exception("Il manque des informations pour aller au bout de la demande : impossible de supprimer cette entr&eacute;e.||" . $testons_id[1] . "+" . $testons_id[0]);

    }
  }

  switch($type_req){
    case 'types':
    $_table = $prefix . $type_req;
    $_champ = 'type_absence';
      break;
    case 'motifs':
    $_table = $prefix . $type_req;
    $_champ = 'type_motif';
      break;
    case 'actions':
    $_table = $prefix . $type_req;
    $_champ = 'type_action';
      break;
    case 'justifications':
    $_table = $prefix . $type_req;
    $_champ = 'type_justification';
      break;
    default:
      $_table = $_champ = NULL;
  } // switch

  $test = new abs_gestion();
  $test->setChamps($_champ); // on donne le nom du champ de cette table (d�finie ci-dessous)
  $test->setTable($_table); // On choisit la bonne table
  $test->setEncodage("utf8"); // On pr�cise l'encodage s'il est diff�rent de l'ISO-8859-1

  if ($type_req != $_id AND $action == 'ajouter') {

    // On est dans le cas d'une demande d'ajout dans la base
    if ($test->_saveNew($_id)) {
      $ajouter = 'ok';
    }else{
      $ajouter = 'no';
    }

  }elseif($type_req != $_id AND $action == 'effacer'){

    if ($test->_deleteById($del_id)) {
      $effacer = 'ok';
    }else{
      $effacer = 'no';
    }

  }

  $tout = $test->voirTout(); // on liste toutes les entr�es de la table $_table.

/*
  echo '<pre>';
  print_r($tout);
  echo '</pre>';
*/


}catch(exception $e){
  // Cette fonction est pr�sente dans /lib/erreurs.php
  affExceptions($e);
}
// On pr�cise l'ent�te HTML pour que le navigateur ne se perde pas .
header('Content-Type: text/html; charset:utf-8');

?>
<table id="presentations">

  <tr>
    <th><?php echo $type_req; ?></th>
    <th>Effacer</th>
  </tr>

  <?php foreach($tout as $aff): ?>
    <?php $effacer_id = 'effacer' . $aff->id ; ?>
    <tr>
      <td><?php echo $aff->$_champ; ?></td>
      <td>
        <input type="hidden" name="effacer" id="<?php echo $effacer_id; ?>" value="<?php echo $type_req.'|||'.$aff->id ; ?>" />
        <img src="../images/icons/delete.png" alt="effacer" title="Effacer" onclick="gestionaffAbs('aff_result', '<?php echo $effacer_id; ?>', 'parametrage_ajax.php');" /><?php ?></td>
    </tr>

  <?php endforeach; ?>

</table>

<?php
  // Cas o� l'utilisateur vient de choisir ce qu'il veut afficher : types, actions, motifs et justifications

  echo '

  <p onclick="afficherDiv(\'ajouterEntree\');" class="lienWeb">Ajouter des ' .$type_req . '</p>

<div id="ajouterEntree" style="display: none;">

  <p>Ajouter une entr&eacute;e dans la base</p>



    <label for="' . $type_req . '">Entrez un ' . str_replace('s', '', $type_req) . ' :</label>
    <input onkeydown="func_KeyDown(event, \'1\', \'' . $type_req . '\');" type="text" id="' . $type_req . '" name="nom" value="" />
    <p onclick="gestionaffAbs(\'aff_result\', \'' . $type_req . '\', \'parametrage_ajax.php\');" class="lienWeb">Enregistrer</p>

' . $effacer . '

</div>
  ';

if ($ajouter == 'ok') {
  // Cas o� l'utilisateur veut ajouter une entr�e dans la base

  echo '<p class="ok">' . $type_req . ' en plus : ' . utf8_encode($_id) . '<p>';

}elseif($ajouter == 'no'){

  echo '<p class="no">Impossible d\'enregistrer "' . utf8_encode($_id) . '"<p>';

}

?>








