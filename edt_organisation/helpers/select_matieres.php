<?php

/**
 *
 * @version $Id$
 * @copyright 2008
 *
 * Fichier qui renvoie un select des classes de l'�tablissement
 * pour l'int�grer dans un fomulaire
 */
// On r�cup�re les infos utiles pour le fonctionnement des requ�tes sql
$niveau_arbo = 1;
require_once("../lib/initialisations.inc.php");
// S�curit� : �viter que quelqu'un appelle ce fichier seul
$serveur_script = $_SERVER["SCRIPT_NAME"];
$analyse = explode("/", $serveur_script);
$analyse[4] = isset($analyse[4]) ? $analyse[4] : null;
if ($analyse[4] == "select_matieres.php") {
    die();
}

$increment = isset($nom_select) ? $nom_select : "liste_matieres";
$matiere_selected = isset($nom_matiere) ? strtoupper($nom_matiere) : (isset($nom_selected) ? strtoupper($nom_selected) : null);

echo '
	<select name ="' . $increment . '">
		<option value="aucun">Liste des mati�res</option>';
// on recherche la liste des mati�res
$query = mysql_query("SELECT matiere, nom_complet FROM matieres ORDER BY nom_complet");
$nbre = mysql_num_rows($query);
for($i = 0; $i < $nbre; $i++) {
    $matiere[$i] = mysql_result($query, $i, "matiere");
    $nom[$i] = mysql_result($query, $i, "nom_complet");
    if (strtoupper(trim(remplace_accents($nom[$i]))) == $matiere_selected OR strtoupper(trim(remplace_accents($matiere[$i], 'all'))) == $matiere_selected) {
        $selected = ' selected="selected"';
    } else {
        $selected = '';
    }

    echo '
		<option value="' . $matiere[$i] . '"' . $selected . '>' . $nom[$i] . '</option>';
}
echo '</select>';

?>