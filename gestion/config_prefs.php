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

// INSERT INTO droits VALUES ('/gestion/consult_prefs.php', 'V', 'V', 'F', 'F', 'F', 'F', 'F', 'D�finition des pr�f�rences d utilisateurs', '');
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}


/*
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
*/

//$page=isset($_GET['page']) ? $_GET['page'] : NULL;
$page=isset($_GET['page']) ? $_GET['page'] : (isset($_POST['page']) ? $_POST['page'] : NULL);

$prof=isset($_POST['prof']) ? $_POST['prof'] : NULL;

if($_SESSION['statut']!="administrateur"){
	unset($prof);
	$prof=array($_SESSION['login']);
}

//$page=isset($_POST['page']) ? $_POST['page'] : NULL;
$enregistrer=isset($_POST['enregistrer']) ? $_POST['enregistrer'] : NULL;
$msg="";

// Tester les valeurs de $page
// Les valeurs autoris�es sont (actuellement): accueil, add_modif_dev, add_modif_conteneur
//if(isset($page)){
if((isset($page))&&($_SESSION['statut']=="administrateur")){
	if(($page!="accueil_simpl")&&($page!="add_modif_dev")&&($page!="add_modif_conteneur")){
		$page=NULL;
		$enregistrer=NULL;
		$msg="La page choisie ne convient pas.";
	}
}

if(isset($enregistrer)){
	for($i=0;$i<count($prof);$i++){
		//if($page=='accueil_simpl'){
		if(($page=='accueil_simpl')||($_SESSION['statut']=='professeur')){
			//$tab=array('accueil_simpl','accueil_ct','accueil_cn','accueil_bull','accueil_visu','accueil_trombino','accueil_liste_pdf','accueil_aff_txt_icon');
			$tab=array('accueil_simpl','accueil_infobulles','accueil_ct','accueil_cn','accueil_bull','accueil_visu','accueil_trombino','accueil_liste_pdf');

			for($j=0;$j<count($tab);$j++){
				unset($valeur);
				//$valeur=isset($_POST[$tab[$j]]) ? $_POST[$tab[$j]] : NULL;
				$tmp_champ=$tab[$j]."_".$i;
				$valeur=isset($_POST[$tmp_champ]) ? $_POST[$tmp_champ] : NULL;

				$sql="DELETE FROM preferences WHERE login='".$prof[$i]."' AND name='".$tab[$j]."'";
				//echo $sql."<br />\n";
				$res_suppr=mysql_query($sql);

				if(isset($valeur)){
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='$valeur'";
					//echo $sql."<br />\n";
					if($res_insert=mysql_query($sql)){
					}
					else{
						$msg.="Erreur lors de l'enregistrement de $tab[$j] pour $prof[$i]<br />\n";
					}
				}
				else{
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='n'";
					//echo $sql."<br />\n";
					if($res_insert=mysql_query($sql)){
					}
					else{
						$msg.="Erreur lors de l'enregistrement de $tab[$j] pour $prof[$i]<br />\n";
					}
				}
			}
		}

		if(($page=='add_modif_dev')||($_SESSION['statut']=='professeur')){
			$tab=array('add_modif_dev_simpl','add_modif_dev_nom_court','add_modif_dev_nom_complet','add_modif_dev_description','add_modif_dev_coef','add_modif_dev_date','add_modif_dev_boite');
			for($j=0;$j<count($tab);$j++){
				unset($valeur);
				//$valeur=isset($_POST[$tab[$j]]) ? $_POST[$tab[$j]] : NULL;
				$tmp_champ=$tab[$j]."_".$i;
				$valeur=isset($_POST[$tmp_champ]) ? $_POST[$tmp_champ] : NULL;

				$sql="DELETE FROM preferences WHERE login='".$prof[$i]."' AND name='".$tab[$j]."'";
				//echo $sql."<br />\n";
				$res_suppr=mysql_query($sql);

				if(isset($valeur)){
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='$valeur'";
					//echo $sql."<br />\n";
					if($res_insert=mysql_query($sql)){
					}
					else{
						$msg.="Erreur lors de l'enregistrement de $tab[$j] pour $prof[$i]<br />\n";
					}
				}
				else{
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='n'";
					//echo $sql."<br />\n";
					if($res_insert=mysql_query($sql)){
					}
					else{
						$msg.="Erreur lors de l'enregistrement de $tab[$j] pour $prof[$i]<br />\n";
					}
				}
			}
		}

		if(($page=='add_modif_conteneur')||($_SESSION['statut']=='professeur')){
			$tab=array('add_modif_conteneur_simpl','add_modif_conteneur_nom_court','add_modif_conteneur_nom_complet','add_modif_conteneur_description','add_modif_conteneur_coef','add_modif_conteneur_boite','add_modif_conteneur_aff_display_releve_notes','add_modif_conteneur_aff_display_bull');
			for($j=0;$j<count($tab);$j++){
				unset($valeur);
				//$valeur=isset($_POST[$tab[$j]]) ? $_POST[$tab[$j]] : NULL;
				$tmp_champ=$tab[$j]."_".$i;
				$valeur=isset($_POST[$tmp_champ]) ? $_POST[$tmp_champ] : NULL;

				$sql="DELETE FROM preferences WHERE login='".$prof[$i]."' AND name='".$tab[$j]."'";
				//echo $sql."<br />\n";
				$res_suppr=mysql_query($sql);

				if(isset($valeur)){
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='$valeur'";
					//echo $sql."<br />\n";
					if($res_insert=mysql_query($sql)){
					}
					else{
						$msg.="Erreur lors de l'enregistrement de $tab[$j] pour $prof[$i]<br />\n";
					}
				}
				else{
					$sql="INSERT INTO preferences SET login='".$prof[$i]."', name='".$tab[$j]."', value='n'";
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

	//unset($page);
}

// Style sp�cifique pour la page:
$style_specifique="gestion/config_prefs";

// Couleur pour les cases dans lesquelles une modif est faite:
$couleur_modif='orange';

// Message d'alerte pour ne pas quitter par erreur sans valider:
$themessage="Des modifications ont �t� effectu�es. Voulez-vous vraiment quitter sans enregistrer?";


//**************** EN-TETE *****************
$titre_page = "Configuration des interfaces simplifi�es";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

// Initialisation de la variable utilis�e pour noter si des modifications ont �t� effectu�es dans la page.
echo "<script type='text/javascript'>
	change='no';
</script>\n";

/*
- Choisir la page � afficher
- Choisir les profs? ou juste r�p�ter la ligne de titre?
*/

echo "<form enctype=\"multipart/form-data\" name= \"formulaire\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
echo "<div class='norme'><p class=bold>";
echo "<a href='";
if($_SESSION['statut']=='administrateur'){
	echo "index.php";
}
else{
	echo "../accueil.php";
}
echo "' onclick=\"return confirm_abandon (this, change, '$themessage')\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>\n";

//if(!isset($page)){
if((!isset($page))&&($_SESSION['statut']=="administrateur")){
	echo "</div>\n";

	echo "<p>Cette page permet de configurer l'interface simplifi�e pour:</p>\n";
	echo "<ul>\n";
	echo "<li><a href='".$_SERVER['PHP_SELF']."?page=accueil_simpl'>Page d'accueil simplifi�e pour les professeurs</a></li>\n";
	echo "<li><a href='".$_SERVER['PHP_SELF']."?page=add_modif_dev'>Page de cr�ation d'�valuation</a></li>\n";
	echo "<li><a href='".$_SERVER['PHP_SELF']."?page=add_modif_conteneur'>Page de cr�ation de ".strtolower(getSettingValue("gepi_denom_boite"))."</a></li>\n";
	echo "</ul>\n";

}
else{
	if($_SESSION['statut']=="administrateur"){
		echo " | <a href='".$_SERVER['PHP_SELF']."' onclick=\"return confirm_abandon (this, change, '$themessage')\">Choix de la page</a>";
	}
	echo "</div>\n";


	unset($prof);
	$prof=array();
	if($_SESSION['statut']=="administrateur"){

		$sql="SELECT DISTINCT nom,prenom,login FROM utilisateurs WHERE statut='professeur' ORDER BY nom, prenom";
		$res_prof=mysql_query($sql);
		if(mysql_num_rows($res_prof)==0){
			echo "<p>Aucun professeur n'est encore d�fini.<br />Commencez par cr�er les comptes professeurs.</p>\n";
			require("../lib/footer.inc.php");
			die();
		}

		$i=0;
		while($lig_prof=mysql_fetch_object($res_prof)){
			$prof[$i]=array();
			$prof[$i]['login']=$lig_prof->login;
			$prof[$i]['nom']=$lig_prof->nom;
			$prof[$i]['prenom']=$lig_prof->prenom;
			$i++;
		}
	}
	else{
		$i=0;
		$prof[$i]['login']=$_SESSION['login'];
		$prof[$i]['nom']=$_SESSION['nom'];
		$prof[$i]['prenom']=$_SESSION['prenom'];
	}

	$nb_profs=count($prof);


	function cellule_checkbox($prof_login,$item,$num,$special){
		echo "<td align='center'";
		echo " id='td_".$item."_".$num."' ";
		//echo " style='text-align:center; ";
		$checked="";
		$coche="";
		$sql="SELECT * FROM preferences WHERE login='$prof_login' AND name='$item'";
		$test=mysql_query($sql);
		if(mysql_num_rows($test)>0){
			$lig_test=mysql_fetch_object($test);
			if($lig_test->value=="y"){
				//echo " style='background-color: lightgreen;'";
				//echo "background-color: lightgreen;";
				echo " class='coche'";
				$checked=" checked";
				$coche="y";
			}
			else{
				//echo " style='background-color: lightgray;'";
				//echo "background-color: lightgray;";
				echo " class='decoche'";
				$coche="n";
			}
		}
		//echo "'";
		echo ">";
		echo "<input type='checkbox' name='$item"."_"."$num' id='$item"."_"."$num' value='y'";

		/*
		// Supprim� apr�s avoir permis l'affichage des tableaux sur une seule page pour l'acc�s prof � ses propres param�trages
		if($special=="y"){
			echo " onchange=\"modif_ligne($num)\"";
		}
		*/

		echo $checked;
		//echo " onchange='changement();'";
		echo " onchange=\"changement_et_couleur('$item"."_"."$num','";
		//if($special=="y"){
		if($special!=''){
			//echo "td_nomprenom_$num";
			//echo "td_nomprenom_".$num."_".$special;
			$chaine_td="td_nomprenom_".$num."_".$special;
			echo $chaine_td;
		}
		echo "');\"";
		echo " />";

		//if($special=="y"){
		if($special!=''){
			if($coche=="y"){
				echo "<script type='text/javascript'>
	//document.getElementById('td_nomprenom_'+$num).style.backgroundColor='lightgreen';
	document.getElementById('$chaine_td').style.backgroundColor='lightgreen';
</script>\n";
			}
			elseif($coche=="n"){
				echo "<script type='text/javascript'>
	//document.getElementById('td_nomprenom_'+$num).style.backgroundColor='lightgray';
	document.getElementById('$chaine_td').style.backgroundColor='lightgray';
</script>\n";
			}
		}

		echo "</td>\n";
	}


/*
	echo "<style type='text/css'>
	table.contenu {
		border: 1px solid black;
		border-collapse: collapse;
	}

	.contenu th {
		font-weight:bold;
		text-align: center;
		background-color: white;
		border: 1px solid black;
	}

	.contenu td {
		vertical-align: middle;
		text-align: center;
		border: 1px solid black;
	}

	.contenu tr.entete {
		background-color: white;
	}

	.contenu .coche {
		background-color: lightgreen;
	}

	.contenu .decoche {
		background-color: lightgray;
	}
</style>\n";
*/

	echo "<p align='center'><input type=\"submit\" name='enregistrer' value=\"Valider\" style=\"font-variant: small-caps;\" /></p>\n";

	//if($page=="accueil_simpl"){
	if(($page=="accueil_simpl")||($_SESSION['statut']=='professeur')){
		echo "<p>Param�trage de la page d'<b>accueil</b> simplifi�e pour les professeurs.</p>\n";

		//$tabchamps=array('accueil_simpl','accueil_ct','accueil_trombino','accueil_cn','accueil_bull','accueil_visu','accueil_liste_pdf');
		//accueil_aff_txt_icon
		$tabchamps=array('accueil_simpl','accueil_infobulles','accueil_ct','accueil_trombino','accueil_cn','accueil_bull','accueil_visu','accueil_liste_pdf');

		//echo "<table border='1'>\n";
		echo "<table class='contenu'>\n";

		// 1�re ligne
		//$lignes_entete="<tr style='background-color: white;'>\n";
		$lignes_entete="<tr class='entete'>\n";
		$lignes_entete.="<th rowspan='3'>Professeur</th>\n";
		$lignes_entete.="<th rowspan='2'>Utiliser l'interface simplifi�e</th>\n";
		$lignes_entete.="<th rowspan='2'>Afficher les infobulles</th>\n";
		$lignes_entete.="<th colspan='6'>Afficher les liens pour</th>\n";
		$lignes_entete.="</tr>\n";

		// 2�me ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete.="<tr class='entete'>\n";
		$lignes_entete.="<th>le Cahier de textes</th>\n";
		$lignes_entete.="<th>le Trombinoscope</th>\n";
		$lignes_entete.="<th>le Carnet de notes</th>\n";
		$lignes_entete.="<th>les notes et appr�ciations des Bulletins</th>\n";
		$lignes_entete.="<th>la Visualisation des graphes et bulletins simplifi�s</th>\n";
		$lignes_entete.="<th>les Listes PDF des �l�ves</th>\n";
		$lignes_entete.="</tr>\n";

		// 3�me ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete.="<tr class='entete'>\n";
		for($i=0;$i<count($tabchamps);$i++){
			$lignes_entete.="<th>";
			$lignes_entete.="<a href='javascript:modif_coche(\"$tabchamps[$i]\",true)'><img src='../images/enabled.png' width='15' height='15' alt='Tout cocher' /></a>/\n";
			$lignes_entete.="<a href='javascript:modif_coche(\"$tabchamps[$i]\",false)'><img src='../images/disabled.png' width='15' height='15' alt='Tout d�cocher' /></a>\n";
			$lignes_entete.="</th>\n";
		}
		$lignes_entete.="</tr>\n";


		//$i=0;
		//while($lig_prof=mysql_fetch_object($res_prof)){
		for($i=0;$i<count($prof);$i++){
			if($i-ceil($i/10)*10==0){
				echo $lignes_entete;
			}

			echo "<tr>\n";

			//echo "<td id='td_nomprenom_".$i."'>";
			echo "<td id='td_nomprenom_".$i."_accueil_simpl'>";
			//echo strtoupper($lig_prof->nom)." ".ucfirst(strtolower($lig_prof->prenom));
			echo strtoupper($prof[$i]['nom'])." ".ucfirst(strtolower($prof[$i]['prenom']));
			//echo "<input type='hidden' name='prof[$i]' value='$lig_prof->login' />";
			echo "<input type='hidden' name='prof[$i]' value='".$prof[$i]['login']."' />";
			echo "</td>\n";

			/*
			cellule_checkbox($prof[$i]['login'],'accueil_simpl',$i,'y');

			cellule_checkbox($prof[$i]['login'],'accueil_ct',$i,'');
			cellule_checkbox($prof[$i]['login'],'accueil_trombino',$i,'');
			cellule_checkbox($prof[$i]['login'],'accueil_cn',$i,'');
			cellule_checkbox($prof[$i]['login'],'accueil_bull',$i,'');
			cellule_checkbox($prof[$i]['login'],'accueil_visu',$i,'');
			cellule_checkbox($prof[$i]['login'],'accueil_liste_pdf',$i,'');
			*/

			$j=0;
			//cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'y');
			cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'accueil_simpl');
			//for($j=0;$j<count($tabchamps);$j++){
			for($j=1;$j<count($tabchamps);$j++){
				cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'');
			}

			echo "</tr>\n";
			//$i++;
		}

		echo "</table>\n";
	}








	if(($page=="add_modif_dev")||($_SESSION['statut']=='professeur')){
		echo "<p>Param�trage de la page de <b>cr�ation d'�valuation</b> pour les professeurs</p>\n";

		$tabchamps=array( 'add_modif_dev_simpl','add_modif_dev_nom_court','add_modif_dev_nom_complet','add_modif_dev_description','add_modif_dev_coef','add_modif_dev_date','add_modif_dev_boite');

		//echo "<table border='1'>\n";
		echo "<table class='contenu'>\n";

		// 1�re ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete="<tr class='entete'>\n";
		$lignes_entete.="<th rowspan='3'>Professeur</th>\n";
		$lignes_entete.="<th rowspan='2'>Utiliser l'interface simplifi�e</th>\n";
		$lignes_entete.="<th colspan='6'>Afficher les champs</th>\n";
		$lignes_entete.="</tr>\n";

		// 2�me ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete.="<tr class='entete'>\n";
		$lignes_entete.="<th>Nom court</th>\n";
		$lignes_entete.="<th>Nom complet</th>\n";
		$lignes_entete.="<th>Description</th>\n";
		$lignes_entete.="<th>Coefficient</th>\n";
		$lignes_entete.="<th>Date</th>\n";
		$lignes_entete.="<th>".ucfirst(strtolower(getSettingValue("gepi_denom_boite")))."</th>\n";
		$lignes_entete.="</tr>\n";

		// 3�me ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete.="<tr class='entete'>\n";
		for($i=0;$i<count($tabchamps);$i++){
			$lignes_entete.="<th>";
			$lignes_entete.="<a href='javascript:modif_coche(\"$tabchamps[$i]\",true)'><img src='../images/enabled.png' width='15' height='15' alt='Tout cocher' /></a>/\n";
			$lignes_entete.="<a href='javascript:modif_coche(\"$tabchamps[$i]\",false)'><img src='../images/disabled.png' width='15' height='15' alt='Tout d�cocher' /></a>\n";
			$lignes_entete.="</th>\n";
		}
		$lignes_entete.="</tr>\n";


		//$i=0;
		//while($lig_prof=mysql_fetch_object($res_prof)){
		for($i=0;$i<count($prof);$i++){
			if($i-ceil($i/10)*10==0){
				echo $lignes_entete;
			}

			echo "<tr>\n";

			//echo "<td>";
			//echo "<td id='td_nomprenom_".$i."'>";
			echo "<td id='td_nomprenom_".$i."_add_modif_dev'>";
			//echo strtoupper($lig_prof->nom)." ".ucfirst(strtolower($lig_prof->prenom));
			echo strtoupper($prof[$i]['nom'])." ".ucfirst(strtolower($prof[$i]['prenom']));
			//echo "<input type='hidden' name='prof[$i]' value='$lig_prof->login' />";
			echo "<input type='hidden' name='prof[$i]' value='".$prof[$i]['login']."' />";
			echo "</td>\n";

			$j=0;
			//cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'y');
			cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'add_modif_dev');
			//for($j=0;$j<count($tabchamps);$j++){
			for($j=1;$j<count($tabchamps);$j++){
				cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'');
			}

			echo "</tr>\n";
			//$i++;
		}

		echo "</table>\n";
	}






	if(($page=="add_modif_conteneur")||($_SESSION['statut']=='professeur')){
		echo "<p>Param�trage de la page de <b>cr�ation de ".ucfirst(strtolower(getSettingValue("gepi_denom_boite")))."</b> pour les professeurs</p>\n";

		$tabchamps=array('add_modif_conteneur_simpl','add_modif_conteneur_nom_court','add_modif_conteneur_nom_complet','add_modif_conteneur_description','add_modif_conteneur_coef','add_modif_conteneur_boite','add_modif_conteneur_aff_display_releve_notes','add_modif_conteneur_aff_display_bull');

		//echo "<table border='1'>\n";
		echo "<table class='contenu'>\n";

		// 1�re ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete="<tr class='entete'>\n";
		$lignes_entete.="<th rowspan='3'>Professeur</th>\n";
		$lignes_entete.="<th rowspan='2'>Utiliser l'interface simplifi�e</th>\n";
		$lignes_entete.="<th colspan='7'>Afficher les champs</th>\n";
		$lignes_entete.="</tr>\n";

		// 2�me ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete.="<tr class='entete'>\n";
		$lignes_entete.="<th>Nom court</th>\n";
		$lignes_entete.="<th>Nom complet</th>\n";
		$lignes_entete.="<th>Description</th>\n";
		$lignes_entete.="<th>Coefficient</th>\n";
		$lignes_entete.="<th>".ucfirst(strtolower(getSettingValue("gepi_denom_boite")))."</th>\n";
		$lignes_entete.="<th>Afficher sur le relev� de notes</th>\n";
		$lignes_entete.="<th>Afficher sur le bulletin</th>\n";
		$lignes_entete.="</tr>\n";

		// 3�me ligne
		//$lignes_entete.="<tr style='background-color: white;'>\n";
		$lignes_entete.="<tr class='entete'>\n";
		for($i=0;$i<count($tabchamps);$i++){
			$lignes_entete.="<th>";
			$lignes_entete.="<a href='javascript:modif_coche(\"$tabchamps[$i]\",true)'><img src='../images/enabled.png' width='15' height='15' alt='Tout cocher' /></a>/\n";
			$lignes_entete.="<a href='javascript:modif_coche(\"$tabchamps[$i]\",false)'><img src='../images/disabled.png' width='15' height='15' alt='Tout d�cocher' /></a>\n";
			$lignes_entete.="</th>\n";
		}
		$lignes_entete.="</tr>\n";


		//$i=0;
		//while($lig_prof=mysql_fetch_object($res_prof)){
		for($i=0;$i<count($prof);$i++){
			if($i-ceil($i/10)*10==0){
				echo $lignes_entete;
			}

			echo "<tr>\n";

			//echo "<td>";
			//echo "<td id='td_nomprenom_".$i."'>";
			echo "<td id='td_nomprenom_".$i."_add_modif_conteneur'>";
			//echo strtoupper($lig_prof->nom)." ".ucfirst(strtolower($lig_prof->prenom));
			echo strtoupper($prof[$i]['nom'])." ".ucfirst(strtolower($prof[$i]['prenom']));
			//echo "<input type='hidden' name='prof[$i]' value='$lig_prof->login' />";
			echo "<input type='hidden' name='prof[$i]' value='".$prof[$i]['login']."' />";
			echo "</td>\n";

			$j=0;
			//cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'y');
			cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'add_modif_conteneur');
			//for($j=0;$j<count($tabchamps);$j++){
			for($j=1;$j<count($tabchamps);$j++){
				cellule_checkbox($prof[$i]['login'],$tabchamps[$j],$i,'');
			}

			echo "</tr>\n";
			//$i++;
		}

		echo "</table>\n";
	}








	// La page n'est consid�r�e que pour l'admin pour r�duire la longueur de la liste
	if($_SESSION['statut']=='administrateur'){
		echo "<input type=\"hidden\" name='page' value=\"$page\" />\n";
	}

	echo "<p align='center'><input type=\"submit\" name='enregistrer' value=\"Valider\" style=\"font-variant: small-caps;\" /></p>\n";

	echo "<script type='text/javascript' language='javascript'>
	function modif_coche(item,statut){
		// statut: true ou false
		for(k=0;k<$nb_profs;k++){
			if(document.getElementById(item+'_'+k)){
				document.getElementById(item+'_'+k).checked=statut;

				document.getElementById('td_'+item+'_'+k).style.backgroundColor='$couleur_modif';
			}
		}
		changement();
	}

	function changement_et_couleur(id,special){
		if(document.getElementById(id)){
			document.getElementById('td_'+id).style.backgroundColor='$couleur_modif';
		}

		if(special!=''){
			document.getElementById(special).style.backgroundColor='$couleur_modif';
		}

		changement();
	}
";

	/*
	echo "
	function modif_ligne(num){";

	$liste_champs="";
	for($k=0;$k<count($tabchamps);$k++){
		if($k>0){$liste_champs.=", ";}
		$liste_champs.="'$tabchamps[$k]'";
	}

		echo "
		tabchamps=Array($liste_champs);
		for(k=0;k<tabchamps.length;k++){
			item=tabchamps[k];
			if(document.getElementById('td_'+item+'_'+num)){
				document.getElementById('td_'+item+'_'+num).style.backgroundColor='orange';
			}
		}
		changement();
	}
";
	*/
	echo "</script>\n";


	echo "<p><i>Remarques:</i></p>\n";
	echo "<ul>\n";
	echo "<li>La prise en compte des champs choisis est conditionn�e par le fait d'avoir coch� ou non la colonne 'Utiliser l'interface simplifi�e' pour l'utilisateur consid�r�.</li>\n";
	echo "<li>Les champs non propos�s dans les interfaces simplifi�es restent accessibles aux utilisateurs en cliquant sur les liens 'Interface compl�te' propos�s dans les pages d'interfaces simplifi�es .</li>\n";
	echo "</ul>\n";
	//}
}

echo "</form>\n";
echo "<br />\n";
require("../lib/footer.inc.php");
?>