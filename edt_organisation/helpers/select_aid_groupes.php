<?php

/**
 * @version $Id$
 * @copyright 2008
 *
 * Fichier qui renvoie un select des professeurs de l'�tablissement
 * pour l'int�grer dans un fomulaire
 *
 */
// On r�cup�re les infos utiles pour le fonctionnement des requ�tes sql
$niveau_arbo = 1;
require_once("../lib/initialisations.inc.php");

// S�curit� : �viter que quelqu'un appelle ce fichier seul
$serveur_script = $_SERVER["SCRIPT_NAME"];
$analyse = explode("/", $serveur_script);
$analyse[4] = isset($analyse[4]) ? $analyse[4] : NULL;
	if ($analyse[4] == "select_aid_groupes.php") {
		die();
	}

$increment = isset($nom_select) ? $nom_select : "liste_aid_groupes";

echo '
	<select name ="'.$increment.'">
		<option value="aucun">Liste des AID et des groupes</option>
			<optgroup label="Les AID">';
	// on recherche la liste des AID
	$query = mysql_query("SELECT nom_complet, indice_aid FROM aid_config");
	$nbre = mysql_num_rows($query);
	for($i = 0; $i < $nbre; $i++){
		$nom[$i] = mysql_result($query, $i, "nom_complet");
		$indice_aid[$i] = mysql_result($query, $i, "indice_aid");
		// On r�cup�re le nom pr�cis de cette AID
		$query2 = mysql_query("SELECT nom FROM aid WHERE id = '".$indice_aid[$i]."'");
		$nom_aid = mysql_result($query2, "nom");
		$query3 = mysql_query("SELECT login FROM j_aid_eleves WHERE indice_aid = '".$indice_aid[$i]."'");
		$nbre_eleves = mysql_num_rows($query3);
		echo '
		<option value="'.$indice_aid[$i].'">'.$nom[$i].' ('.$nom_aid.' avec '.$nbre_eleves.' �l�ves)</option>';
	}
	echo '
			</optgroup>
			<optgroup label="Les groupes">';
	$query = mysql_query("SELECT id, description FROM groupes");
	$nbre_groupes = mysql_num_rows($query);
	for($a = 0; $a < $nbre_groupes; $a++){
		$id_groupe[$a]["id"] = mysql_result($query, $a, "id");
		$id_groupe[$a]["description"] = mysql_result($query, $a, "description");

		// On r�cup�re toutes les infos pour l'affichage
		// On n'utilise pas getGroup() car elle est trop longue et r�cup�re trop de choses dont on n'a pas besoin

		$query1 = mysql_query("SELECT classe FROM j_groupes_classes jgc, classes c WHERE jgc.id_classe = c.id AND jgc.id_groupe = '".$id_groupe[$a]["id"]."'");
		$classe = mysql_fetch_array($query1);


		echo '
		<option value="'.$id_groupe[$a]["id"].'">'.$id_groupe[$a]["description"].'('.$classe[0].')</option>';
	}
echo '
			</optgroup>
	</select>';
?>