<?php

/**
 *
 * @version $Id$
 *
 * Copyright 2001, 2008 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Julien Jocal
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

$titre_page = "G�rer les groupes de l'EdT<br />Professeurs";
$affiche_connexion = "oui";
$niveau_arbo = 1;

// Initialisations files
require_once("../lib/initialisations.inc.php");

// fonctions compl�mentaires et/ou librairies utiles


// Resume session
$resultat_session = resumeSession();
if ($resultat_session == "c") {
   header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
   die();
} else if ($resultat_session == "0") {
    header("Location: ../logout.php?auto=1");
    die();
}

// S�curit�
if (!checkAccess()) {
    header("Location: ../logout.php?auto=2");
    die();
}

// ======================= Initialisation des variables ================
$id_gr = isset($_GET["id_gr"]) ? $_GET["id_gr"] : (isset($_POST["id_gr"]) ? $_POST["id_gr"] : NULL);
$classe_e = isset($_GET["cla"]) ? $_GET["cla"] : NULL;
$action = isset($_POST["action"]) ? $_POST["action"] : NULL;
$choix_prof = isset($_POST["choix_prof"]) ? $_POST["choix_prof"] : (isset($_GET["choix_prof"]) ? $_GET["choix_prof"] : NULL);
$msg = $aff_liste_profs = $aff_select_profs = $titre = NULL;

// ============================ Traitement des donn�es ========================== //

	// Les renseignements sur cet edt_gr
	$sql_t = "SELECT * FROM edt_gr_nom WHERE id = '".$id_gr."'";
	$query_t = mysql_query($sql_t) OR trigger_error('Erreur lors du traitement de cet edt_gr.', E_USER_ERROR);
	$rep = mysql_fetch_array($query_t);

	$titre .= '<p>EDT : '.$rep["nom"].'&nbsp;('.$rep["nom_long"].')&nbsp;-&nbsp;Liste des professeurs.</p>';

	// La liste des professeurs de l'�tablissement
$query_p = mysql_query("SELECT login, nom, prenom FROM utilisateurs WHERE statut = 'professeur' AND etat = 'actif' ORDER BY nom, prenom")
						OR trigger_error('Impossible de lire la liste des professeurs.', E_USER_ERROR);
	$nbre_p = mysql_num_rows($query_p);


	$aff_select_profs .= '
	<select name="choix_prof" onchange=\'document.ch_profs.submit();\'>
		<option value="plusieurs">Plusieurs professeurs</option>
	';

	for($i = 0 ; $i < $nbre_p ; $i++){

		$login_p[$i] = mysql_result($query_p, $i, "login");
		$nom_p[$i] = mysql_result($query_p, $i, "nom");
		$prenom_p[$i] = mysql_result($query_p, $i, "prenom");

		$aff_select_profs .= '
		<option value="'.$login_p[$i].'">'.$nom_p[$i].' '.$prenom_p[$i].'</option>';

	}
	$aff_select_profs .= '</select>';

	// On ajoute un prof si c'est demand�
	if ($action == "ajouter") {

	}

	// On enl�ve un prof si c'est demand�
	if ($action == "effacer") {

	}

	// La liste des profs de cet edt_gr
	$sql_l = "SELECT login, nom, prenom FROM utilisateurs u, edt_gr_profs egp
										WHERE u.login = egp.id_utilisateurs
										AND egp.id_gr_nom = '".$id_gr."'
										ORDER BY nom, prenom";
	$query_l = mysql_query($sql_l) OR trigger_error('Impossible de lister les professeurs de ce groupe', E_USER_WARNING);

	while($rep = mysql_fetch_array($query_l)){

		$aff_liste_profs .= '<br /><a href="./edt_liste_profs.php?action=effacer&amp;choix_prof='.$rep["login"].'&amp;id_gr='.$id_gr.'">
		<img src="../images/icons/delete.png" />'.$rep["nom"].' '.$rep["prenom"].'</a>'."\n";

	}


// =========================== Fin du traitement des donn�es ==================== //


// ======================== CSS et js particuliers ========================
$utilisation_win = "oui";
$utilisation_jsdivdrag = "non";
$javascript_specifique = "edt_gestion_gr/script/fonctions_edt2.js";
$style_specifique = "edt_gestion_gr/style2_edt.css";

// ===================== entete Gepi ======================================//
require_once("../lib/header.inc");
// ===================== fin entete =======================================//

?>

<hr />
<?php echo $titre; ?>

<hr />

<div id="liste_p">

	<?php echo $aff_liste_profs; ?>
<br /><br />

</div>

<form name="ch_profs" action="edt_liste_profs.php" method="post">

	<fieldset id="choix_prof" style="width: 600px;">

		<legend>Ajouter un professeur</legend>

			<input type="hidden" name="action" value="ajouter" />
			<input type="hidden" name="id_gr" value="<?php echo $id_gr; ?>" />

			<?php echo $aff_select_profs; ?>

	</fieldset>
		<?php echo $msg; ?>

</form>

<?php
// Inclusion du bas de page
require_once("../lib/footer.inc.php");
?>
