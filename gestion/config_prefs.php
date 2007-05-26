<?php
/*
 * $Id : $
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

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();};

// INSERT INTO droits VALUES ('/gestion/config_prefs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'D�finition des pr�f�rences d utilisateurs', '');
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}


function getPref($login,$item,$default){
	$sql="SELECT value FROM preferences WHERE login='$login' AND name='$item'";
	$res_prefs=mysql_query($sql);

	if(mysql_num_rows($res_prefs)>0){
		$ligne=mysql_fetch_object($res_prefs);
		return $ligne->value;
	}
	else{
		return $default;
	}
}



$prof=isset($_POST['prof']) ? $_POST['prof'] : NULL;
$page=isset($_POST['page']) ? $_POST['page'] : NULL;
$enregistrer=isset($_POST['enregistrer']) ? $_POST['enregistrer'] : NULL;
$msg="";

if(isset($enregistrer)){

	for($i=0;$i<count($prof);$i++){
		if($page=='add_modif_dev'){
			$tab=array('add_modif_dev_simpl','add_modif_dev_nom_court','add_modif_dev_nom_complet','add_modif_dev_description','add_modif_dev_coef','add_modif_dev_date','add_modif_dev_boite');
			for($j=0;$j<count($tab);$j++){
				unset($valeur);
				$valeur=isset($_POST[$tab[$j]]) ? $_POST[$tab[$j]] : NULL;

				if(isset($valeur)){
					$sql="DELETE FROM preferences WHERE login='".$prof[$i]."' AND name='".$tab[$j]."'";
					//echo $sql."<br />\n";
					$res_suppr=mysql_query($sql);
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='$valeur'";
					//echo $sql."<br />\n";
					if($res_insert=mysql_query($sql)){
					}
					else{
						$msg.="Erreur lors de l'enregistrement de $tab[$j] pour $prof[$i]<br />\n";
					}
				}
			}
		}
		elseif($page=='add_modif_conteneur'){
			$tab=array('add_modif_conteneur_simpl','add_modif_conteneur_nom_court','add_modif_conteneur_nom_complet','add_modif_conteneur_description','add_modif_conteneur_coef','add_modif_conteneur_boite','add_modif_conteneur_aff_display_releve_notes','add_modif_conteneur_aff_display_bull');
			for($j=0;$j<count($tab);$j++){
				unset($valeur);
				$valeur=isset($_POST[$tab[$j]]) ? $_POST[$tab[$j]] : NULL;

				if(isset($valeur)){
					$sql="DELETE FROM preferences WHERE login='".$prof[$i]."' AND name='".$tab[$j]."'";
					//echo $sql."<br />\n";
					$res_suppr=mysql_query($sql);
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='$valeur'";
					//echo $sql."<br />\n";
					if($res_insert=mysql_query($sql)){
					}
					else{
						$msg.="Erreur lors de l'enregistrement de $tab[$j] pour $prof[$i]<br />\n";
					}
				}
			}
		}
	}

	if($msg==""){
		$msg="Enregistrement r�ussi.";
	}

	unset($page);
}



//**************** EN-TETE *****************
$titre_page = "Configuration des interfaces simplifi�es";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

echo "<form enctype=\"multipart/form-data\" name= \"formulaire\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
echo "<div class='norme'><p class=bold><a href='index.php'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>\n";

if(!isset($prof)){
	echo "</div>\n";

	echo "<h2>Choix des professeurs</h2>\n";

	echo "<p>Choisissez les professeurs dont vous souhaitez param�trer les interfaces simplifi�es.</p>\n";
	echo "<p>Tout <a href='javascript:modif_coche(true)'>cocher</a> / <a href='javascript:modif_coche(false)'>d�cocher</a>.</p>";

	$sql="SELECT login,nom,prenom FROM utilisateurs WHERE (statut='professeur'AND etat='actif') ORDER BY nom,prenom";
	$res_profs=mysql_query($sql);
	$nb_prof=mysql_num_rows($res_profs);
	if($nb_prof==0){
		echo "<p>ERREUR: Il semble qu'aucun professeur ne soit encore d�fini.</p>\n";
		echo "</form>\n";
		echo "</body>\n";
		echo "</html>\n";
		die();
	}
	// Affichage sur 3 colonnes
	$nb_prof_par_colonne=round($nb_prof/3);

	echo "<table width='100%'>\n";
	echo "<tr valign='top' align='center'>\n";

	$i = 0;

	echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
	echo "<td align='left'>\n";

	while ($i < $nb_prof) {

		if(($i>0)&&(round($i/$nb_prof_par_colonne)==$i/$nb_prof_par_colonne)){
			echo "</td>\n";
			echo "<td align='left'>\n";
		}

		$lig_prof=mysql_fetch_object($res_profs);

		echo "<input type='checkbox' id='prof".$i."' name='prof[]' value='$lig_prof->login' /> ".ucfirst(strtolower($lig_prof->prenom))." ".strtoupper($lig_prof->nom)."<br />\n";

		$i++;
	}
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";

	echo "<script type='text/javascript'>
		function modif_coche(statut){
			for(k=0;k<$i;k++){
				if(document.getElementById('prof'+k)){
					document.getElementById('prof'+k).checked=statut;
				}
			}
			//changement();
		}
	</script>\n";

	echo "<center><input type=\"submit\" name='ok' value=\"Valider\" style=\"font-variant: small-caps;\" /></center>\n";

}
else{
	echo " | <a href='".$_SERVER['PHP_SELF']."'>Choisir d'autres professeurs</a>";
	echo "</div>\n";

	if(!isset($page)){
		echo "<h2>Choix de la page</h2>\n";

		if(count($prof)==0){
			echo "<p>ERREUR: Il semble qu'aucun professeur n'ait �t� s�lectionn�.</p>\n";
			echo "</form>\n";
			echo "</body>\n";
			echo "</html>\n";
			die();
		}

		echo "<p>Vous allez param�trer des pr�f�rences pour ";
		$chaine_profs="";
		for($i=0;$i<count($prof);$i++){
			echo "<input type='hidden' name='prof[$i]' value='$prof[$i]' />\n";
			$sql="SELECT nom,prenom FROM utilisateurs WHERE login='$prof[$i]' ORDER BY nom,prenom";
			$res_prof=mysql_query($sql);
			$lig_prof=mysql_fetch_object($res_prof);
			$chaine_profs.=ucfirst(strtolower($lig_prof->prenom))." ".strtoupper($lig_prof->nom).", ";
		}
		$chaine_profs=substr($chaine_profs,0,strlen($chaine_profs)-2);
		echo $chaine_profs.".</p>\n";

		/*
		echo "<p>Vous devez maintenant choisir la page pour laquelle vous souhaitez param�trer les items de l'interface simplifi�e.</p>\n";
		echo "<p><input type='radio' name='page' value='add_modif_dev' checked /> Cr�ation d'�valuation<br />\n";
		echo "<input type='radio' name='page' value='add_modif_conteneur' /> Cr�ation de ".strtolower(getSettingValue("gepi_denom_boite"))."</p>\n";
		*/
		echo "<table border='0'>\n";
		echo "<tr><td valign='top'>Param�trage de l'interface simplifi�e pour :</td>\n";
		echo "<td>";
		echo "<input type='hidden' name='page' id='id_page' />\n";
		echo "<input type='button' name='choix1' value=\"Cr�ation d'�valuation\" onclick=\"document.getElementById('id_page').value='add_modif_dev';document.forms['formulaire'].submit();\" /> <br />\n";
		echo "<input type='button' name='choix1' value=\"Cr�ation de ".strtolower(getSettingValue("gepi_denom_boite"))."\" onclick=\"document.getElementById('id_page').value='add_modif_conteneur';document.forms['formulaire'].submit();\" />";
		echo "</td></tr>\n";
		echo "</table>\n";
	}
	else{
		$chaine_profs="";
		for($i=0;$i<count($prof);$i++){
			echo "<input type='hidden' name='prof[$i]' value='$prof[$i]' />\n";
			$sql="SELECT nom,prenom FROM utilisateurs WHERE login='$prof[$i]' ORDER BY nom,prenom";
			$res_prof=mysql_query($sql);
			$lig_prof=mysql_fetch_object($res_prof);
			$chaine_profs.=ucfirst(strtolower($lig_prof->prenom))." ".strtoupper($lig_prof->nom).", ";
		}
		$chaine_profs=substr($chaine_profs,0,strlen($chaine_profs)-2);

		echo "<input type='hidden' name='page' value='$page' />\n";

		if($page=='add_modif_dev'){
			echo "<h2>Choix des items de la page: Cr�ation d'�valuation</h2>\n";

			echo "<p>Vous allez param�trer des pr�f�rences pour $chaine_profs.</p>\n";

			// R�cup�ration des valeurs.
			/*
			$aff_nom_court=getPref($_SESSION['login'],'add_modif_dev_nom_court','y');
			$aff_nom_complet=getPref($_SESSION['login'],'add_modif_dev_nom_complet','n');
			$aff_description=getPref($_SESSION['login'],'add_modif_dev_description','n');
			$aff_coef=getPref($_SESSION['login'],'add_modif_dev_coef','y');
			$aff_date=getPref($_SESSION['login'],'add_modif_dev_date','y');
			$aff_boite=getPref($_SESSION['login'],'add_modif_dev_boite','y');
			*/

			echo "<p>Pour ce(s) professeur(s), utiliser l'interface simplifi�e par d�faut: Oui <input type='radio' name='add_modif_dev_simpl' value='y' checked /> / <input type='radio' name='add_modif_dev_simpl' value='n' /> Non</p>\n";

			echo "<p>Pour fixer les valeurs ci-dessous, validez le formulaire.</p>\n";
			echo "<table border='1'>\n";
			echo "<tr>\n";
			echo "<td style='font-weight: bold; text-align:left;'>Item</td>\n";
			echo "<td style='font-weight: bold; text-align:center;'>Afficher</td>\n";
			echo "<td style='font-weight: bold; text-align:center;'>Cacher</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Nom_court:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_nom_court' value='y' checked /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_nom_court' value='n' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Nom_complet:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_nom_complet' value='y' /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_nom_complet' value='n' checked /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Description:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_description' value='y' /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_description' value='n' checked /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Coefficient:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_coef' value='y' checked /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_coef' value='n' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Date:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_date' value='y' checked /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_date' value='n' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			//echo "<td>Afficher le champ Emplacement du/de la ".ucfirst(strtolower(getSettingValue("gepi_denom_boite"))).":</td>\n";
			echo "<td>Afficher le champ Emplacement de l'�valuation :</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_boite' value='y' checked /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_dev_boite' value='n' /></td>\n";
			echo "</tr>\n";
			echo "</table>\n";

			echo "<p>Les champs suivants ne sont pas accessibles en interface simplifi�e:</p>\n";
			echo "<ul>\n";
			echo "<li>Faire appara�tre la note de l'�valuation sur le relev� de notes de l'�l�ve (<i>valeur par d�faut: 'oui'</i>)</li>\n";
			echo "<li>Statut de l'�valuation (<i>valeur par d�faut: 'La note de l'�valuation entre dans le calcul de la moyenne'</i>)</li>\n";
			echo "</ul>\n";
			echo "<p>Pour param�trer autrement ces champs, le professeur doit repasser en interface compl�te.</p>\n";

			echo "<p><i>NOTE:</i> L'acc�s � ces champs en interface simplifi�e pourra �tre ajout� dans le futur.</p>\n";


			echo "<input type='hidden' name='enregistrer' value='oui' />\n";

		}
		elseif($page=='add_modif_conteneur'){
			echo "<h2>Choix des items de la page: Cr�ation de ".strtolower(getSettingValue("gepi_denom_boite"))."</h2>\n";

			echo "<p>Vous allez param�trer des pr�f�rences pour $chaine_profs.</p>\n";

			echo "<p>Pour ce(s) professeur(s), utiliser l'interface simplifi�e par d�faut: Oui <input type='radio' name='add_modif_conteneur_simpl' value='y' checked /> / <input type='radio' name='add_modif_conteneur_simpl' value='n' /> Non</p>\n";

			echo "<p>Pour fixer les valeurs ci-dessous, validez le formulaire.</p>\n";
			echo "<table border='1'>\n";
			echo "<tr>\n";
			echo "<td style='font-weight: bold; text-align:left;'>Item</td>\n";
			echo "<td style='font-weight: bold; text-align:center;'>Afficher</td>\n";
			echo "<td style='font-weight: bold; text-align:center;'>Cacher</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Nom_court:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_nom_court' value='y' checked /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_nom_court' value='n' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Nom_complet:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_nom_complet' value='y' /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_nom_complet' value='n' checked /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Description:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_description' value='y' /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_description' value='n' checked /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Coefficient:</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_coef' value='y' checked /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_coef' value='n' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Emplacement du/de la ".ucfirst(strtolower(getSettingValue("gepi_denom_boite"))).":</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_boite' value='y' checked /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_boite' value='n' /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Afficher le(a) ".ucfirst(strtolower(getSettingValue("gepi_denom_boite")))." sur le relev� de notes:<br />La valeur par d�faut du champ (<i>affich� ou non</i>) est 'oui'.</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_aff_display_releve_notes' value='y' /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_aff_display_releve_notes' value='n' checked /></td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td>Afficher le champ Afficher le(a) ".ucfirst(strtolower(getSettingValue("gepi_denom_boite")))." sur le bulletin:<br />La valeur par d�faut du champ (<i>affich� ou non</i>) est 'non'.</td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_aff_display_bull' value='y' /></td>\n";
			echo "<td style='text-align:center;'><input type='radio' name='add_modif_conteneur_aff_display_bull' value='n' checked /></td>\n";
			echo "</tr>\n";
			echo "</table>\n";

			echo "<p>Les champs suivants ne sont pas accessibles en interface simplifi�e:</p>\n";
			echo "<ul>\n";
			echo "<li>Pr�cision du calcul de la moyenne du/de la ".strtolower(getSettingValue("gepi_denom_boite"))." (<i>valeur par d�faut: 'Arrondir au dixi�me de point sup�rieur'</i>)</li>\n";
			echo "<li>Pond�ration: Pour chaque �l�ve, augmente ou diminue de la valeur indiqu�e ci-contre, le coefficient de la meilleur note du/de la ".strtolower(getSettingValue("gepi_denom_boite"))." (<i>valeur par d�faut: '0'</i>)</li>\n";
			echo "<li>Notes prises en comptes dans le calcul de la moyenne du/de la ".strtolower(getSettingValue("gepi_denom_boite"))." (<i>valeur par d�faut: 'la moyenne s'effectue sur toutes les notes contenues � la racine du conteneur et sur les moyennes des sous-conteneurs, en tenant compte des options dans ces sous-conteneurs'</i>)</li>\n";
			/*
			Notes prises en comptes dans le calcul de la moyenne de la sous-mati�re TN
			la moyenne s'effectue sur toutes les notes contenues � la racine de TN et sur les moyennes de la sous-mati�re TN2, en tenant compte des options dans cette sous-mati�re
			la moyenne s'effectue sur toutes les notes contenues dans TN et dans la sous-mati�re TN2, sans tenir compte des options d�finies dans cette sous-mati�re
			*/
			echo "</ul>\n";
			echo "<p>Pour param�trer autrement ces champs, le professeur doit repasser en interface compl�te.</p>\n";

			echo "<p><i>NOTE:</i> L'acc�s � ces champs en interface simplifi�e pourra �tre ajout� dans le futur.</p>\n";
		}
		echo "<input type='hidden' name='enregistrer' value='oui' />\n";
		echo "<center><input type=\"submit\" name='ok' value=\"Valider\" style=\"font-variant: small-caps;\" /></center>\n";
	}
}

//echo "<center><input type=\"submit\" name='ok' value=\"Valider\" style=\"font-variant: small-caps;\" /></center>\n";

echo "</form>\n";
echo "<br />\n";
require("../lib/footer.inc.php");
?>