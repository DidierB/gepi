<?php
/*
 * Last modification  : 04/11/2006
 *
 * Copyright 2001, 2005 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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


// Initialisations files
require_once("../lib/initialisations.inc.php");

extract($_GET, EXTR_OVERWRITE);
extract($_POST, EXTR_OVERWRITE);

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
};

if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}

$msg = null;

if (isset($is_posted) and ($is_posted == '1')) {
	if (isset($display_rang)) $display_rang = 'y'; else $display_rang = 'n';
	if (isset($display_address)) $display_address = 'y'; else $display_address = 'n';
	if (isset($display_coef)) $display_coef = 'y'; else $display_coef = 'n';
	if (isset($display_mat_cat)) $display_mat_cat = 'y'; else $display_mat_cat = 'n';
	if (isset($display_nbdev)) $display_nbdev = 'y'; else $display_nbdev = 'n';

	if (isset($display_moy_gen)) $display_moy_gen = 'y'; else $display_moy_gen = 'n';

	if (isset($id_classe)) {
		if ($reg_class_name) {
			//$register_class = mysql_query("UPDATE classes SET classe='$reg_class_name', nom_complet='$reg_nom_complet', suivi_par='$reg_suivi_par', formule= '$reg_formule', format_nom='$reg_format', display_rang='$display_rang', display_address='$display_address', display_coef='$display_coef', display_mat_cat ='$display_mat_cat' WHERE id = '$id_classe'");
			//$register_class = mysql_query("UPDATE classes SET classe='$reg_class_name', nom_complet='$reg_nom_complet', suivi_par='$reg_suivi_par', formule= '$reg_formule', format_nom='$reg_format', display_rang='$display_rang', display_address='$display_address', display_coef='$display_coef', display_mat_cat ='$display_mat_cat', display_nbdev ='$display_nbdev' WHERE id = '$id_classe'");
			$register_class = mysql_query("UPDATE classes SET classe='$reg_class_name', nom_complet='$reg_nom_complet', suivi_par='$reg_suivi_par', formule= '$reg_formule', format_nom='$reg_format', display_rang='$display_rang', display_address='$display_address', display_coef='$display_coef', display_mat_cat ='$display_mat_cat', display_nbdev ='$display_nbdev',display_moy_gen='$display_moy_gen' WHERE id = '$id_classe'");
			if (!$register_class) {
					$msg .= "Une erreur s'est produite lors de la modification de la classe.";
					} else {
					$msg .= "La classe a bien �t� modifi�e.";
			}
			// On enregistre les infos relatives aux cat�gories de mati�res
			$get_cat = mysql_query("SELECT id, nom_court, priority FROM matieres_categories");
			while ($row = mysql_fetch_array($get_cat, MYSQL_ASSOC)) {
				$reg_priority = $_POST['priority_'.$row["id"]];
				if (isset($_POST['moyenne_'.$row["id"]])) {$reg_aff_moyenne = 1;} else { $reg_aff_moyenne = 0;}
				if (!is_numeric($reg_priority)) $reg_priority = 0;
				if (!is_numeric($reg_aff_moyenne)) $reg_aff_moyenne = 0;
				$test = mysql_result(mysql_query("select count(classe_id) FROM j_matieres_categories_classes WHERE (categorie_id = '" . $row["id"] . "' and classe_id = '" . $id_classe . "')"), 0);
				if ($test == 0) {
					// Pas d'entr�e... on cr��
					$res = mysql_query("INSERT INTO j_matieres_categories_classes SET classe_id = '" . $id_classe . "', categorie_id = '" . $row["id"] . "', priority = '" . $reg_priority . "', affiche_moyenne = '" . $reg_aff_moyenne . "'");
				} else {
					// Entr�e existante, on met � jour
					$res = mysql_query("UPDATE j_matieres_categories_classes SET priority = '" . $reg_priority . "', affiche_moyenne = '" . $reg_aff_moyenne . "' WHERE (classe_id = '" . $id_classe . "' and categorie_id = '" . $row["id"] . "')");
				}
				if (!$res) {
					$msg .= "<br/>Une erreur s'est produite lors de l'enregistrement des donn�es de cat�gorie.";
				}
			}

		} else {
		$msg .= "Veuillez pr�ciser le nom de la classe !";
		}
	} else {
		if ($reg_class_name) {
		//$register_class = mysql_query("INSERT INTO classes SET classe = '$reg_class_name', nom_complet = '$reg_nom_complet', suivi_par = '$reg_suivi_par', formule = '$reg_formule', format_nom = '$reg_format', display_rang = '$display_rang', display_address = '$display_address', display_coef = '$display_coef', display_mat_cat = '$display_mat_cat'");
		//$register_class = mysql_query("INSERT INTO classes SET classe = '$reg_class_name', nom_complet = '$reg_nom_complet', suivi_par = '$reg_suivi_par', formule = '$reg_formule', format_nom = '$reg_format', display_rang = '$display_rang', display_address = '$display_address', display_coef = '$display_coef', display_mat_cat = '$display_mat_cat', display_nbdev ='$display_nbdev'");
		$register_class = mysql_query("INSERT INTO classes SET classe = '$reg_class_name', nom_complet = '$reg_nom_complet', suivi_par = '$reg_suivi_par', formule = '$reg_formule', format_nom = '$reg_format', display_rang = '$display_rang', display_address = '$display_address', display_coef = '$display_coef', display_mat_cat = '$display_mat_cat', display_nbdev ='$display_nbdev', display_moy_gen='$display_moy_gen'");
		if (!$register_class) {
			$msg .= "Une erreur s'est produite lors de l'enregistrement de la nouvelle classe.";
		} else {
			$msg .= "La nouvelle classe a bien �t� enregistr�e.";
			$id_classe = mysql_insert_id();

			// On enregistre les infos relatives aux cat�gories de mati�res
			$get_cat = mysql_query("SELECT id, nom_court, priority FROM matieres_categories");
			while ($row = mysql_fetch_array($get_cat, MYSQL_ASSOC)) {
				$reg_priority = $_POST['priority_'.$row["id"]];
				if (isset($_POST['moyenne_'.$row["id"]])) {$reg_aff_moyenne = 1;} else { $reg_aff_moyenne = 0;}
				if (!is_numeric($reg_priority)) $reg_priority = 0;
				if (!is_numeric($reg_aff_moyenne)) $reg_aff_moyenne = 0;

				$res = mysql_query("INSERT INTO j_matieres_categories_classes SET classe_id = '" . $id_classe . "', categorie_id = '" . $row["id"] . "', priority = '" . $reg_priority . "', affiche_moyenne = '" . $reg_aff_moyenne . "'");

				if (!$res) {
					$msg .= "<br/>Une erreur s'est produite lors de l'enregistrement des donn�es de cat�gorie.";
				}
			}
		}

		} else {
		$msg .= "Veuillez pr�ciser le nom de la classe !";
		}
	}
}


//**************** EN-TETE *******************************
$titre_page = "Gestion des classes | Modifier les param�tres";
require_once("../lib/header.inc");
//**************** FIN EN-TETE ***************************
?>
<p class=bold>
|<a href="index.php">Retour</a>|
</p>
<p><b>Remarque : </b>Connectez vous avec un compte ayant le statut "scolarit�" pour �diter les bulletins et avoir acc�s � d'autres param�tres d'affichage.</p>

<?php

if (isset($id_classe)) {
	$call_nom_class = mysql_query("SELECT * FROM classes WHERE id = '$id_classe'");
	$classe = mysql_result($call_nom_class, 0, 'classe');
	$nom_complet = mysql_result($call_nom_class, 0, 'nom_complet');
	$suivi_par = mysql_result($call_nom_class, 0, 'suivi_par');
	$formule = mysql_result($call_nom_class, 0, 'formule');
	$format_nom = mysql_result($call_nom_class, 0, 'format_nom');
	$display_rang = mysql_result($call_nom_class, 0, 'display_rang');
	$display_address = mysql_result($call_nom_class, 0, 'display_address');
	$display_coef = mysql_result($call_nom_class, 0, 'display_coef');
	$display_mat_cat = mysql_result($call_nom_class, 0, 'display_mat_cat');
	$display_nbdev = mysql_result($call_nom_class, 0, 'display_nbdev');
	$display_moy_gen = mysql_result($call_nom_class, 0, 'display_moy_gen');
} else {
	$classe = '';
	$nom_complet = '';
	$suivi_par = '';
	$formule = '';
	$format_nom = 'np';
	$display_rang = 'n';
	$display_address = 'n';
	$display_coef = 'n';
	$display_mat_cat = 'n';
	$display_nbdev = 'n';
	$display_moy_gen = 'n';
}

?>
<form enctype="multipart/form-data" action="modify_nom_class.php" method=post>
<p>Nom court de la classe : <input type=text size=30 name=reg_class_name value = "<?php echo $classe; ?>" /></p>
<p>Nom complet de la classe : <input type=text size=50 name=reg_nom_complet value = "<?php echo $nom_complet; ?>" /></p>
<p>Pr�nom et nom du chef d'�tablissement ou de son repr�sentant apparaissant en bas de chaque bulletin : <br /><input type=text size=30 name=reg_suivi_par value = "<?php echo $suivi_par; ?>" /></p>
<p>Formule � ins�rer sur les bulletins (cette formule sera suivie des nom et pr�nom de la personne d�sign�e ci_dessus :<br /> <input type=text size=80 name=reg_formule value = "<?php echo $formule; ?>" /></p>

<p><b>Formatage de l'identit� des professeurs pour les bulletins :</b>
<br /><br /><input type="radio" name="reg_format" value="<?php echo "np"; ?>" <?php if ($format_nom=="np") echo " checked "; ?>/>Nom Pr�nom (Durand Albert)
<br /><input type="radio" name="reg_format" value="<?php echo "pn"; ?>" <?php if ($format_nom=="pn") echo " checked "; ?>/>Pr�nom Nom (Albert Durand)
<br /><input type="radio" name="reg_format" value="<?php echo "in"; ?>" <?php   if ($format_nom=="in") echo " checked "; ?>/>Initiale-Pr�nom Nom (A. Durand)
<br /><input type="radio" name="reg_format" value="<?php echo "ni"; ?>" <?php   if ($format_nom=="ni") echo " checked "; ?>/>Initiale-Pr�nom Nom (Durand A.)
<br /><input type="radio" name="reg_format" value="<?php echo "cnp"; ?>" <?php   if ($format_nom=="cnp") echo " checked "; ?>/>Civilit� Nom Pr�nom (M. Durand Albert)
<br /><input type="radio" name="reg_format" value="<?php echo "cpn"; ?>" <?php   if ($format_nom=="cpn") echo " checked "; ?>/>Civilit� Pr�nom Nom (M. Albert Durand)
<br /><input type="radio" name="reg_format" value="<?php echo "cin"; ?>" <?php   if ($format_nom=="cin") echo " checked "; ?>/>Civ. initiale-Pr�nom Nom (M. A. Durand)
<br /><input type="radio" name="reg_format" value="<?php echo "cni"; ?>" <?php   if ($format_nom=="cni") echo " checked "; ?>/>Civ. Nom initiale-Pr�nom  (M. Durand A.)

<input type=hidden name=is_posted value=1 />
<?php if (isset($id_classe)) {echo "<input type=hidden name=id_classe value=$id_classe />";} ?>
<br />
<br />
<table style="border: 0;" cellpadding="5" cellspacing="5">
<tr>
    <td style="font-variant: small-caps; width: 35%;">
    Afficher sur le bulletin le rang de chaque �l�ve&nbsp;:
    </td>
    <td><input type="checkbox" value="y" name="display_rang"  <?php   if ($display_rang=="y") echo " checked "; ?> />
    </td>
</tr>
<tr>
    <td style="font-variant: small-caps;">
    Afficher le bloc adresse du responsable de l'�l�ve :
    </td>
    <td><input type="checkbox" value="y" name="display_address"  <?php   if ($display_address=="y") echo " checked "; ?> />
    </td>
</tr>
    <tr>
    <td style="font-variant: small-caps;">
    Afficher les coefficients des mati�res (uniquement si au moins un coef diff�rent de 0) :
    </td>
    <td><input type="checkbox" value="y" name="display_coef"  <?php   if ($display_coef=="y") echo " checked "; ?> />
    </td>
</tr>
</tr>
    <tr>
    <td style="font-variant: small-caps;">
    Afficher les moyennes g�n�rales sur les bulletins (uniquement si au moins un coef diff�rent de 0) :
    </td>
    <td><input type="checkbox" value="y" name="display_moy_gen"  <?php   if ($display_moy_gen=="y") echo " checked "; ?> />
    </td>
</tr>
<tr>
    <td style="font-variant: small-caps;">
    Afficher le nombre de devoirs sur le bulletin :
    </td>
    <td><input type="checkbox" value="y" name="display_nbdev"  <?php   if ($display_nbdev=="y") echo " checked "; ?> />
    </td>
</tr>
<tr>
    <td style="font-variant: small-caps;">
    Afficher les rubriques de mati�res sur le bulletin, les relev�s de notes, et les outils de visualisation :
    </td>
    <td><input type="checkbox" value="y" name="display_mat_cat"  <?php   if ($display_mat_cat=="y") echo " checked "; ?> />
    </td>
</tr>
<tr>
	<td style="font-variant: small-caps;">
	Param�trage des cat�gories de mati�re pour cette classe
	</td>
	<td>
<table style='border: 1px solid black;'>
<tr>
	<td style='width: auto;'>Cat�gorie</td><td style='width: 100px; text-align: center;'>Priorit� d'affichage</td><td style='width: 100px; text-align: center;'>Afficher la moyenne sur le bulletin</td>
</tr>
<?php
$get_cat = mysql_query("SELECT id, nom_court, priority FROM matieres_categories");
while ($row = mysql_fetch_array($get_cat, MYSQL_ASSOC)) {
	// Pour la cat�gorie, on r�cup�re les infos d�j� enregistr�es pour la classe
	if (isset($id_classe)) {
		$infos = mysql_fetch_object(mysql_query("SELECT priority, affiche_moyenne FROM j_matieres_categories_classes WHERE (categorie_id = '" . $row["id"] ."' and classe_id = '" . $id_classe . "')"));
	} else {
		$infos = false;
	}
	if (!$infos) {
		$current_priority = $row["priority"];
		$current_affiche_moyenne = "0";
	} else {
		$current_priority = $infos->priority;
		$current_affiche_moyenne = $infos->affiche_moyenne;
	}

	echo "<tr>\n";
	echo "<td style='padding: 5px;'>".$row["nom_court"]."</td>\n";
	echo "<td style='padding: 5px; text-align: center;'>\n";
			echo "<select name='priority_".$row["id"]."' size='1'>\n";
			for ($i=0;$i<11;$i++) {
				echo "<option value='$i'";
				if ($current_priority == $i) echo " SELECTED";
				echo ">$i</option>\n";
			}
			echo "</select>\n";
	echo "</td>\n";
	echo "<td style='padding: 5px; text-align: center;'>\n";
		echo "<input type='checkbox' name='moyenne_".$row["id"]."'";
		if ($current_affiche_moyenne == '1') echo " CHECKED";
		echo " />\n";
	echo "</td>\n";
	echo "</tr>\n";
}
?>
</table>
</td>
</tr>
</table>
<center><input type=submit value="Enregistrer" style="margin-top: 30px; margin-bottom: 100px;" /></center>
</form>

</body>
</html>