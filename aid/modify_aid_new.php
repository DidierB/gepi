<?php

/**
 * Document destin� � constituer les AID (�l�ves) en partant d'un lot de classes.
 *
 * @version $Id$
 * @copyright 2007
 */

// Initialisation
require_once("../lib/initialisations.inc.php");

// Les fonctions de Gepi
require_once("../lib/share.inc.php");

// Resume session

$resultat_session = resumeSession();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
};

/*/ En attente de la gestion des droits
// INSERT INTO droits SET
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}*/

//Initialisation des variables
$id_aid = isset($_GET["id_aid"]) ? $_GET["id_aid"] : (isset($_POST["id_aid"]) ? $_POST["id_aid"] : NULL);
$indice_aid = isset($_GET["indice_aid"]) ? $_GET["indice_aid"] : (isset($_POST["indice_aid"]) ? $_POST["indice_aid"] : NULL);
$aff_liste_m = isset($_GET["classe"]) ? $_GET["classe"] : (isset($_POST["classe"]) ? $_POST["classe"] : NULL);
$choix_aid = isset($_GET["choix_aid"]) ? $_GET["choix_aid"] : (isset($_POST["choix_aid"]) ? $_POST["choix_aid"] : NULL);
$id_eleve = isset($_GET["id_eleve"]) ? $_GET["id_eleve"] : (isset($_POST["id_eleve"]) ? $_POST["id_eleve"] : NULL);
$aff_infos_g = "";
$aff_classes_g = "";
$aff_aid_d = "";
$aff_classes_m = "";

//+++++++++++++++++ CSS AID++++++++
$style_specifique = "aid/style_aid";
//+++++++++++++++++ AJAX AID ++++++
	// En attente de fonctionnement
//$javascript_specifique = "aid/aid_ajax";

//**************** EN-TETE **************************************
$titre_page = "Gestion des �l�ves dans les AID";
require_once("../lib/header.inc");
//**************** FIN EN-TETE **********************************

	//================ TRAITEMENT des entr�es ===================
	if (isset($aff_liste_m) AND isset($id_aid) AND isset($id_eleve) AND isset($indice_aid)) {
		// On int�gre cet �l�ve dans la bse s'il n'y est pas d�j�
		// Pour l'instant on r�cup�re son login � partir de id_eleve
		$rep_log_eleve = mysql_fetch_array(mysql_query("SELECT DISTINCT login FROM eleves WHERE id_eleve = '".$id_eleve."'"));
		// On v�rifie s'il n'est pas d�j� memndre de cet aid
		// Par cette m�thode, on ne peut enregistrer deux fois le m�me
		$req_ajout = mysql_query("INSERT INTO j_aid_eleves SET login='".$rep_log_eleve["login"]."', id_aid='".$id_aid."', indice_aid='".$indice_aid."'");

	}

	/*/================= TRAITEMENT des sorties =======================
	// Attention de penser � sorir les lignes des notes et appr�ciations si elles existent
	    $test_nb[0] = "SELECT * FROM j_aid_eleves WHERE login='$cible1' and id_aid = '$cible2' and indice_aid='$cible3'";
    $req[0] = "DELETE FROM j_aid_eleves WHERE login='$cible1' and id_aid = '$cible2' and indice_aid='$cible3'";
    $mess[1] = "Table des appr�ciations aid";
    $test_nb[1] = "SELECT * FROM aid_appreciations WHERE login='$cible1' and id_aid = '$cible2' and indice_aid='$cible3'";
    $req[1] = "DELETE FROM aid_appreciations WHERE login='$cible1' and id_aid = '$cible2' and indice_aid='$cible3'";
*/
// Affichage du retour
	// On r�cup�re l'indice de l'aid en question
	$aff_infos_g .= "<span class=\"aid_a\"><a href=\"modify_aid.php?flag=eleve&amp;aid_id=".$id_aid."&amp;indice_aid=".$indice_aid."\"><img src='../images/icons/back.png' alt='Retour' class='back_link' /> Retour</a></span>";


//Affichage du nom et des pr�cisions sur l'AID en question
	$req_aid = mysql_query("SELECT nom FROM aid WHERE id = '".$id_aid."'");
	$rep_aid = mysql_fetch_array($req_aid);
	$aff_infos_g .= "<p class=\"bold\">Liste des classes</p>\n";

// Affichage de la liste des classes par $aff_classes_g

	$req_liste_classe = mysql_query("SELECT id, classe FROM classes ORDER BY classe");
	$nbre_classe = mysql_num_rows($req_liste_classe);

	for($a=0; $a<$nbre_classe; $a++) {
		$liste_classe[$a]["id"] = mysql_result($req_liste_classe, $a, "id");
		$liste_classe[$a]["classe"] = mysql_result($req_liste_classe, $a, "classe");

		$aff_classes_g .= "<tr><td><a href=\"./modify_aid_new.php?id_aid=".$id_aid."&amp;classe=".$liste_classe[$a]["id"]."&amp;indice_aid=".$indice_aid."\">El�ves de la ".$liste_classe[$a]["classe"]."</a></td></tr>";
	}

// Affichage de la liste des �l�ves de la classe choisie (au milieu) par $aff_classes_m

if (isset($aff_liste_m)) {

	$aff_nom_classe = mysql_fetch_array(mysql_query("SELECT classe FROM classes WHERE id = '".$aff_liste_m."'"));

	// R�cup�rer la liste des �l�ves de la classe en question
	$req_ele = mysql_query("SELECT DISTINCT login FROM j_eleves_classes WHERE id_classe = '".$aff_liste_m."'");
	$nbre_ele_m = mysql_num_rows($req_ele);

	$aff_classes_m .= "
		<p class=\"red\">Classe de ".$aff_nom_classe["classe"]." : </p>

	<table class=\"aid_tableau\">
	";


	for($b=0; $b<$nbre_ele_m; $b++) {
		$aff_ele_m[$b]["login"] = mysql_result($req_ele, $b, "login") OR DIE('Erreur requ�te liste_eleves : '.mysql_error());
		// On r�cup�re toutes les infos sur l'�l�ve avec son id_eleve
		$req = mysql_query("SELECT nom, prenom, sexe, id_eleve FROM eleves WHERE login = '".$aff_ele_m[$b]["login"]."'");
		$nbre_req = mysql_num_rows($req);
		for($c=0; $c<$nbre_req; $c++) {
			$aff_ele_m[$c]["id_eleve"] = mysql_result($req, $c, "id_eleve");
			$aff_ele_m[$c]["nom"] = mysql_result($req, $c, "nom");
			$aff_ele_m[$c]["prenom"] = mysql_result($req, $c, "prenom");
			$aff_ele_m[$c]["sexe"] = mysql_result($req, $c, "sexe");

			// Ligne paire, ligne impaire (inutile dans un premier temps)
			$aff_tr_css = "lignepaire";
			// On v�rifie que cet �l�ve n'est pas d�j� membre de l'AID
			$req_verif = mysql_query("SELECT login FROM j_aid_eleves WHERE login = '".$aff_ele_m[$b]["login"]."' AND indice_aid = '".$indice_aid."'");
			$nbre_verif = mysql_num_rows($req_verif);
				if ($nbre_verif >> 0) {
					$aff_classes_m .= "
					<tr class=\"ligneimpaire\">
					<td></td></tr>
					";
				}
				else {
					$aff_classes_m .= "
					<tr class=\"".$aff_tr_css."\">
					<td><a href=\"modify_aid_new.php?classe=".$aff_liste_m."&amp;id_eleve=".$aff_ele_m[$c]["id_eleve"]."&amp;id_aid=".$id_aid."&amp;indice_aid=".$indice_aid."\"><img src=\"../images/icons/add_user.png\" /> ".$aff_ele_m[$c]["nom"]." ".$aff_ele_m[$c]["prenom"]."</a></td></tr>
					";
				}
		}// for $c...
	}// for $b
	$aff_classes_m .= "</table>\n";
}// if isset...

// Dans le div de droite, on affiche la liste des �l�ves de l'AID
		$aff_aid_d .= "<p style=\"color: brown; border: 1px solid brown; padding: 2px;\">".$rep_aid["nom"]." :</p>\n";
		// mais aussi le nom des profs de l'AID
		$req_prof = mysql_query("SELECT id_utilisateur FROM j_aid_utilisateurs WHERE id_aid = '".$id_aid."'");
		$nbre_prof = mysql_num_rows($req_prof);
		for($p=0; $p<$nbre_prof; $p++) {
			$prof[$p]["id_utilisateur"] = mysql_result($req_prof, $p, "id_utilisateur");
			// On r�cup�re le nom et la civilit� de tous les profs
			$rep_nom = mysql_fetch_array(mysql_query("SELECT nom, civilite FROM utilisateurs WHERE login = '".$prof[$p]["id_utilisateur"]."'"));
			$aff_aid_d .= "".$rep_nom["civilite"].$rep_nom["nom"]." ";
		}

	$req_ele_aid = mysql_query("SELECT DISTINCT login FROM j_aid_eleves WHERE id_aid = '".$id_aid."'");
	$nbre = mysql_num_rows($req_ele_aid);

		$aff_aid_d .= "\n<br />".$nbre." �l�ves.<br />";

	for($d=0; $d<$nbre; $d++){
		$rep_ele_aid[$d]["login"] = mysql_result($req_ele_aid, $d, "login");
		// On r�cup�re ses noms et pr�noms, puis la classe
			$recup_noms = mysql_fetch_array(mysql_query("SELECT nom, prenom FROM eleves WHERE login = '".$rep_ele_aid[$d]["login"]."'"));
			$recup_id_classe = mysql_fetch_array(mysql_query("SELECT DISTINCT id_classe FROM j_eleves_classes WHERE login = '".$rep_ele_aid[$d]["login"]."'"));
			$recup_classe = mysql_fetch_array(mysql_query("SELECT classe FROM classes WHERE id = '".$recup_id_classe[0]."'"));
		$aff_aid_d .= "<br /><a href='../lib/confirm_query.php?liste_cible=".$rep_ele_aid[$d]["login"]."&amp;liste_cible2=$id_aid&amp;liste_cible3=$indice_aid&amp;action=del_eleve_aid'><img src=\"../images/icons/delete.png\" title=\"Supprimer cet �l�ve\" alt=\"Supprimer\" /></a>".$recup_noms["nom"]." ".$recup_noms["prenom"]." ".$recup_classe["classe"]."\n";
	}

?>



	<div id="aid_gauche">

<?php // Affichage des infos sur la partie gauche
	echo $aff_infos_g;
?>

		<table class="aid_tableau">
<?php // Afichage de la liste des classes � gauche
	echo $aff_classes_g;
?>
		</table>
	</div>

	<div id="aid_droite">

<?php // Affichage � droite
	echo $aff_aid_d;
?>

	</div>

	<div id="aid_centre">

<?php // Affichage au centre
	echo $aff_classes_m;
?>
	</div>


<?php
//require_once("../lib/footer.inc.php");
echo "</div></body></html>";
?>