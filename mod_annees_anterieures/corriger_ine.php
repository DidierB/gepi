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

// INSERT INTO droits VALUES ('/mod_annees_anterieures/corriger_ine.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Correction d INE dans la table annees_anterieures', '');
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}


// Si le module n'est pas activ�...
if(getSettingValue('active_annees_anterieures')!="y"){
	// A DEGAGER
	// A VOIR: Comment enregistrer une tentative d'acc�s illicite?

	header("Location: ../logout.php?auto=1");
	die();
}




$confirmer=isset($_POST['confirmer']) ? $_POST['confirmer'] : NULL;

$mode=isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : NULL);

$annee=isset($_POST['annee']) ? $_POST['annee'] : (isset($_GET['annee']) ? $_GET['annee'] : NULL);
$ine=isset($_POST['ine']) ? $_POST['ine'] : (isset($_GET['ine']) ? $_GET['ine'] : NULL);
// Avec les apostrophes qui risquent d'�tre �chapp�es, on ne retient pas les nom et pr�nom pour l'identification
//$nom_eleve=isset($_POST['nom_eleve']) ? $_POST['nom_eleve'] : (isset($_GET['nom_eleve']) ? $_GET['nom_eleve'] : NULL);
//$prenom_eleve=isset($_POST['prenom_eleve']) ? $_POST['prenom_eleve'] : (isset($_GET['prenom_eleve']) ? $_GET['prenom_eleve'] : NULL);
$naissance=isset($_POST['naissance']) ? $_POST['naissance'] : (isset($_GET['naissance']) ? $_GET['naissance'] : NULL);
$ine_corrige=isset($_POST['ine_corrige']) ? $_POST['ine_corrige'] : (isset($_GET['ine_corrige']) ? $_GET['ine_corrige'] : NULL);

$recherche1=isset($_POST['recherche1']) ? $_POST['recherche1'] : NULL;
$recherche1_nom=isset($_POST['recherche1_nom']) ? $_POST['recherche1_nom'] : NULL;
$recherche1_prenom=isset($_POST['recherche1_prenom']) ? $_POST['recherche1_prenom'] : NULL;

$msg="";
if(isset($confirmer)){
	$cpt=0;
	if((isset($annee))&&(isset($ine))&&(isset($naissance))&&(isset($ine_corrige))){
		for($i=0;$i<count($ine);$i++){
			if($ine_corrige[$i]!=''){
				$sql="UPDATE annees_anterieures SET INE='$ine_corrige[$i]'
												WHERE INE='$ine[$i]' AND
													annee='$annee[$i]' AND
													naissance='$naissance[$i]'";
				$update=mysql_query($sql);
				if(!$update){
					$msg.="<b>Erreur</b> $ine[$i] -&gt; $ine_corrige[$i] ($annee[$i])<br />\n";
					//$msg.="$sql<br />\n";
				}
				else{
					$cpt++;
				}
			}
		}
	}
	else{
		// Ca ne devrait pas arriver: Soit tout est renseign�, soit rien n'est renseign� et on a pas valid� le formulaire.
		$msg="Des champs n'�taient pas correctement renseign�s.";
	}

	if(($msg=="")&&($cpt>0)){$msg="Enregistrement r�ussi.";}
}


$style_specifique="mod_annees_anterieures/annees_anterieures";

$themessage="Des modifications ont �t� effectu�es. Voulez-vous vraiment quitter sans enregistrer?";

//**************** EN-TETE *****************
$titre_page = "Correction d'INE pour les donn�es ant�rieures";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>

<script type="text/javascript" language="JavaScript">
	function get_eleves(f) {
		/*
		var l1    = f.elements["classe"];
		var l2    = f.elements["eleve"];
		var index = l1.selectedIndex;
		if(index < 1)
			l2.options.length = 0;
		else {
		*/
			var xhr_object = null;

			if(window.XMLHttpRequest) // Firefox
				xhr_object = new XMLHttpRequest();
			else if(window.ActiveXObject) // Internet Explorer
				xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
			else { // XMLHttpRequest non support� par le navigateur
				alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
				return;
			}

			xhr_object.open("POST", "liste_eleves_ajax.php", true);

			xhr_object.onreadystatechange = function() {
				if(xhr_object.readyState == 4)
					eval(xhr_object.responseText);
			}

			xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			//var data = "id_classe="+escape(l1.options[index].value)+"&form="+f.name+"&select=eleve";
			var data = "nom_ele="+escape(f.nom_ele.value)+"&prenom_ele="+escape(f.prenom_ele.value)+"&form="+f.name;
			xhr_object.send(data);
		//}
	}
</script>

<?php
echo "<div class='norme'><p class=bold><a href='";
echo "index.php";
echo "' onclick=\"return confirm_abandon (this, change, '$themessage')\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>\n";

if(!isset($mode)){
	echo "</p>\n";
	echo "</div>\n";

	echo "<p>Il arrive que lors de la conservation des donn�es d'une ann�e, le num�ro INE d'un �l�ve ne soit pas (<i>correctement</i>) rempli.<br />Ce num�ro est utilis� pour faire le lien entre un �l�ve de l'ann�e courante (<i>table 'eleves'</i>) et ses donn�es ant�rieures (<i>table 'annees_anterieures'</i>).<br />Si ce num�ro ne co�ncide pas entre les deux tables, la consultation est perturb�e.</p>\n";
	echo "<p>Cette page est destin�e � corriger des INE inscrits dans la table 'annees_anterieures'.</p>\n";

	echo "<p>Voulez-vous:</p>\n";
	echo "<ul>\n";
	echo "<li><a href='".$_SERVER['PHP_SELF']."?mode=ine_login'>afficher les �l�ves dont l'INE n'�tait pas rempli lors de la conservation des donn�es ant�rieures</a>.</li>\n";
	echo "<li><a href='".$_SERVER['PHP_SELF']."?mode=recherche'>rechercher un �l�ve</a></li>\n";
	echo "</ul>\n";
}
elseif($mode=="ine_login"){
	echo " | <a href='".$_SERVER['PHP_SELF']."' onclick=\"return confirm_abandon (this, change, '$themessage')\">Correction d'INE</a>\n";
	echo "</p>\n";
	echo "</div>\n";

	echo "<p>Affichage des �l�ves dont le num�ro INE n'�tait pas rempli lors d'une conservation des donn�es ant�rieures.</p>\n";

	$sql="SELECT DISTINCT annee,INE,nom,prenom,naissance FROM annees_anterieures WHERE INE LIKE 'LOGIN_%' ORDER BY annee,nom,prenom";
	$res1=mysql_query($sql);

	if(mysql_num_rows($res1)==0){
		echo "<p>Aucun �l�ve dans la table 'annees_anterieures' n'a d'INE au pr�fixe 'LOGIN_'<br />(<i>c'est-�-dire dont l'INE �tait non rempli lors d'une op�ration de conservation des donn�es ant�rieures</i>).</p>\n";
	}
	else{

		echo "<form name= \"formulaire\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
		echo "<input type='hidden' name='mode' value=\"ine_login\" />\n";

		echo "<table class='table_annee_anterieure'>\n";
		echo "<tr style='background-color: white;'>\n";
		echo "<th>Ann�e scolaire</th>\n";
		echo "<th>INE enregistr�</th>\n";
		echo "<th>Nom</th>\n";
		echo "<th>Pr�nom</th>\n";
		echo "<th>Date de naissance</th>\n";
		echo "<th>INE corrig�</th>\n";
		echo "<th>Chercher</th>\n";
		echo "</tr>\n";

		$cpt=0;
		$alt=-1;
		while($lig1=mysql_fetch_object($res1)){
			$alt=$alt*(-1);
			echo "<tr style='background-color:";
			if($alt==1){
				echo "silver";
			}
			else{
				echo "white";
			}
			echo "; text-align: center;'>\n";

			echo "<td>";
			echo $lig1->annee;
			echo "<input type='hidden' name='annee[$cpt]' value=\"$lig1->annee\" />\n";
			echo "</td>\n";

			echo "<td style='color: red;'>";
			echo $lig1->INE;
			echo "<input type='hidden' name='ine[$cpt]' value=\"$lig1->INE\" />\n";
			echo "</td>\n";

			echo "<td>";
			echo $lig1->nom;
			echo "<input type='hidden' name='nom_eleve[$cpt]' id='nom_eleve_$cpt' value=\"$lig1->nom\" />\n";
			echo "</td>\n";

			echo "<td>";
			echo $lig1->prenom;
			echo "<input type='hidden' name='prenom_eleve[$cpt]' id='prenom_eleve_$cpt' value=\"$lig1->prenom\" />\n";
			echo "</td>\n";

			echo "<td>";
			echo formate_date($lig1->naissance);
			echo "<input type='hidden' name='naissance[$cpt]' value=\"$lig1->naissance\" />\n";
			echo "</td>\n";

			echo "<td>";
			echo "<input type='text' name='ine_corrige[$cpt]' id='ine_corrige_$cpt' value='' onchange='changement();' />\n";
			echo "</td>\n";

			echo "<td>";
			echo " <a href='#' onClick=\"";
			// On renseigne le formulaire de recherche avec le nom et le pr�nom:
			echo "document.getElementById('nom_ele').value=document.getElementById('nom_eleve_$cpt').value;";
			echo "document.getElementById('prenom_ele').value=document.getElementById('prenom_eleve_$cpt').value;";
			// Pour le lien de renseignement de corrige_ine:
			echo "document.getElementById('ine_recherche').value='ine_corrige_$cpt';";
			// On fait le nettoyage pour ne pas laisser les traces d'une pr�c�dente requ�te:
			echo "document.getElementById('div_resultat').innerHTML='';";
			echo "afficher_div('div_search','y',-400,20);";
			echo "return false;";
			echo "\">";
			echo "<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' />";
			echo "</a>";
			echo "</td>\n";
			echo "</tr>\n";
			$cpt++;
		}
		echo "</table>\n";

		echo "<p align='center'><input type='submit' name='confirmer' value='Enregistrer' /></p>\n";
		echo "</form>\n";

		echo creer_div_infobulle("div_search","Formulaire de recherche dans la table 'eleves'","","<p>Saisir une portion du nom � rechercher...</p>
<form name='recherche' action='".$_SERVER['PHP_SELF']."' method='post'>
<input type='hidden' name='ine_recherche' id='ine_recherche' value='' />
<table border='0'>
	<tr>
		<th>Nom: </th>
		<td><input type='text' name='nom_ele' id='nom_ele' value='' onBlur='get_eleves(this.form)' /></td>
		<td rowspan='2'><input type='button' name='chercher' value='Chercher' onClick='get_eleves(this.form)' /></td>
	</tr>
	<tr>
		<th>Pr�nom: </th>
		<td><input type='text' name='prenom_ele' id='prenom_ele' value='' onBlur='get_eleves(this.form)' /></td>
	</tr>
</table>
</form>

<div id='div_resultat' style='margin: 1px;'></div>

","",27,0,"y","y","n","n");

		echo "<p><br /></p>\n";
		echo "<p><b>Attention:</b> Si vous modifiez un INE en attribuant l'INE d'un autre �l�ve que le bon, vous risquez de ne plus pouvoir trier ce qui correspond effectivement � un �l�ve.<br />Ne proc�dez � la correction qu'apr�s v�rification.</p>\n";
	}

	//echo "<div id='idretour' style='border: 1px solid black; background-color: white; width: 100px; height: 30px;'></div>\n";

}
elseif($mode=="recherche"){
	echo " | <a href='".$_SERVER['PHP_SELF']."' onclick=\"return confirm_abandon (this, change, '$themessage')\">Correction d'INE</a>\n";
	echo "</p>\n";
	echo "</div>\n";

	echo "<p>Recherche d'�l�ves pour corriger un num�ro INE erron� dans la table des donn�es ant�rieures.</p>\n";

	if(!isset($recherche1)){
		echo "<form name='recherche' action='".$_SERVER['PHP_SELF']."' method='post'>
<input type='hidden' name='mode' value='recherche' />
<input type='hidden' name='recherche1' value='y' />
<table border='0'>
	<tr>
		<!--td rowspan='2' valign='top'>El�ve dont le </td-->
		<td>El�ve dont </td>
		<td align='center'>le <b>nom</b></td>
		<td> contient :</td>
		<td><input type='text' name='recherche1_nom' value='' /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align='center'>et le <b>pr�nom</b></td>
		<td> contient: </td>
		<td><input type='text' name='recherche1_prenom' value='' /></td>
	</tr>
</table>
<input type='submit' name='chercher' value='Chercher' />
</form>\n";
	}
	else{
		$sql="SELECT DISTINCT annee,INE,nom,prenom,naissance FROM annees_anterieures
				WHERE nom LIKE '%$recherche1_nom%' AND
					prenom LIKE '%$recherche1_prenom%'
				ORDER BY annee,nom,prenom";
		//echo "$sql<br />";
		$res1=mysql_query($sql);

		if(mysql_num_rows($res1)==0){
			echo "<p>Aucun �l�ve dans la table 'annees_anterieures' ne remplit les crit�res demand�s.</p>\n";
		}
		else{

			echo "<form name= \"formulaire\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
			echo "<input type='hidden' name='mode' value=\"recherche\" />\n";
			echo "<input type='hidden' name='recherche1' value=\"y\" />\n";
			echo "<input type='hidden' name='recherche1_nom' value=\"$recherche1_nom\" />\n";
			echo "<input type='hidden' name='recherche1_prenom' value=\"$recherche1_prenom\" />\n";

			echo "<table class='table_annee_anterieure'>\n";
			echo "<tr style='background-color: white;'>\n";
			echo "<th>Ann�e scolaire</th>\n";
			echo "<th>INE enregistr�</th>\n";
			echo "<th>Nom</th>\n";
			echo "<th>Pr�nom</th>\n";
			echo "<th>Date de naissance</th>\n";
			echo "<th>INE corrig�</th>\n";
			echo "<th>Chercher</th>\n";
			echo "</tr>\n";

			$cpt=0;
			$alt=-1;
			while($lig1=mysql_fetch_object($res1)){
				$alt=$alt*(-1);
				echo "<tr style='background-color:";
				if($alt==1){
					echo "silver";
				}
				else{
					echo "white";
				}
				echo "; text-align: center;'>\n";

				echo "<td>";
				echo $lig1->annee;
				echo "<input type='hidden' name='annee[$cpt]' value=\"$lig1->annee\" />\n";
				echo "</td>\n";

				echo "<td style='color: red;'>";
				echo $lig1->INE;
				echo "<input type='hidden' name='ine[$cpt]' value=\"$lig1->INE\" />\n";
				echo "</td>\n";

				echo "<td>";
				echo $lig1->nom;
				echo "<input type='hidden' name='nom_eleve[$cpt]' id='nom_eleve_$cpt' value=\"$lig1->nom\" />\n";
				echo "</td>\n";

				echo "<td>";
				echo $lig1->prenom;
				echo "<input type='hidden' name='prenom_eleve[$cpt]' id='prenom_eleve_$cpt' value=\"$lig1->prenom\" />\n";
				echo "</td>\n";

				echo "<td>";
				echo formate_date($lig1->naissance);
				echo "<input type='hidden' name='naissance[$cpt]' value=\"$lig1->naissance\" />\n";
				echo "</td>\n";

				echo "<td>";
				echo "<input type='text' name='ine_corrige[$cpt]' id='ine_corrige_$cpt' value='' onchange='changement();' />\n";
				echo "</td>\n";

				echo "<td>";
				echo " <a href='#' onClick=\"";
				// On renseigne le formulaire de recherche avec le nom et le pr�nom:
				echo "document.getElementById('nom_ele').value=document.getElementById('nom_eleve_$cpt').value;";
				echo "document.getElementById('prenom_ele').value=document.getElementById('prenom_eleve_$cpt').value;";
				// Pour le lien de renseignement de corrige_ine:
				echo "document.getElementById('ine_recherche').value='ine_corrige_$cpt';";
				// On fait le nettoyage pour ne pas laisser les traces d'une pr�c�dente requ�te:
				echo "document.getElementById('div_resultat').innerHTML='';";
				echo "afficher_div('div_search','y',-400,20);";
				echo "return false;";
				echo "\">";
				echo "<img src='../images/icons/chercher.png' width='16' height='16' alt='Chercher' />";
				echo "</a>";
				echo "</td>\n";
				echo "</tr>\n";
				$cpt++;
			}
			echo "</table>\n";

			echo "<p align='center'><input type='submit' name='confirmer' value='Enregistrer' /></p>\n";
			echo "</form>\n";

			echo creer_div_infobulle("div_search","Formulaire de recherche dans la table 'eleves'","","<p>Saisir une portion du nom � rechercher...</p>
<form name='recherche' action='".$_SERVER['PHP_SELF']."' method='post'>
<input type='hidden' name='ine_recherche' id='ine_recherche' value='' />
<table border='0'>
	<tr>
		<th>Nom: </th>
		<td><input type='text' name='nom_ele' id='nom_ele' value='' onBlur='get_eleves(this.form)' /></td>
		<td rowspan='2'><input type='button' name='chercher' value='Chercher' onClick='get_eleves(this.form)' /></td>
	</tr>
	<tr>
		<th>Pr�nom: </th>
		<td><input type='text' name='prenom_ele' id='prenom_ele' value='' onBlur='get_eleves(this.form)' /></td>
	</tr>
</table>
</form>

<div id='div_resultat' style='margin: 1px;'></div>

","",27,0,"y","y","n","n");

			echo "<p><br /></p>\n";
			echo "<p><b>Attention:</b> Si vous modifiez un INE en attribuant l'INE d'un autre �l�ve que le bon, vous risquez de ne plus pouvoir trier ce qui correspond effectivement � un �l�ve.<br />Ne proc�dez � la correction qu'apr�s v�rification.</p>\n";
		}
	}
}


echo "<p><br /></p>\n";
require("../lib/footer.inc.php");
die();


















$sql="SELECT DISTINCT a.nom,a.prenom,a.INE,a.naissance
			FROM annees_anterieures a
			LEFT JOIN eleves e
			ON a.INE=e.no_gep
			WHERE e.no_gep IS NULL;";
$res1=mysql_query($sql);
$nb_ele=mysql_num_rows($res1);
if($nb_ele==0){
	echo "<p>Tous les �l�ves pr�sents dans la table 'annees_anterieures' sont dans la table 'eleves'.</p>\n";
}
else{
	echo "<p>Voici la liste des �l�ves pr�sents dans la table 'annees_anterieures', mais absents de la table 'eleves'.<br />
	Il s'agit normalement d'�l�ves ayant quitt� l'�tablissement.<br />
	Il peut cependant arriver que des �l�ves dont le num�ro INE n'�tait pas (<i>correctement</i>) rempli lors de la conservation de l'ann�e soit propos�s dans la liste ci-dessous.<br />
	Dans ce cas, le num�ro INE utilis� a un pr�fixe LOGIN_.<br />
	Ce n'est pas un identifiant correct parce que le login d'un �l�ve n'est pas n�cessairement fixe d'une ann�e sur l'autre (<i>dans le cas des doublons</i>).<br />
	<font color='red'>Une page doit �tre mise au point pour vous permettre de corriger ces INE</font>.</p>\n";

	echo "<form name= \"formulaire\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";

	echo "<table align='center' class='table_annee_anterieure'>\n";
	echo "<tr style='background-color:white;'>\n";
	echo "<th>Supprimer<br />";
	echo "<a href='javascript:modif_coche(true)'><img src='../images/enabled.png' width='15' height='15' alt='Tout cocher' /></a>/\n";
	echo "<a href='javascript:modif_coche(false)'><img src='../images/disabled.png' width='15' height='15' alt='Tout d�cocher' /></a>\n";
	echo "</th>\n";
	echo "<th>El�ve</th>\n";
	echo "<th>Date de naissance</th>\n";
	echo "<th>N�INE</th>\n";
	echo "</tr>\n";
	$cpt=0;
	while($lig_ele=mysql_fetch_object($res1)){
		echo "<tr style='text-align:center;' id='tr_$cpt'>\n";
		echo "<td><input type='checkbox' name='suppr[]' id='suppr_$cpt' value='$lig_ele->INE' onchange=\"modif_une_coche('$cpt');\" /></td>\n";
		echo "<td>".strtoupper($lig_ele->nom)." ".ucfirst(strtolower($lig_ele->prenom))."</td>\n";
		echo "<td>".formate_date($lig_ele->naissance)."</td>\n";
		echo "<td>";
		if(substr($lig_ele->INE,0,6)=="LOGIN_") {echo "<span style='color:red;'>";}
		echo $lig_ele->INE;
		if(substr($lig_ele->INE,0,6)=="LOGIN_"){echo "</span>";}
		echo "</td>\n";
		echo "</tr>\n";
		$cpt++;
	}
	echo "</table>\n";

	echo "<p align='center'><input type='submit' name='confirmer' value='Supprimer' /></p>\n";
	echo "</form>\n";

	echo "<script type='text/javascript' language='javascript'>
	function modif_coche(statut){
		// statut: true ou false
		for(k=0;k<$cpt;k++){
			if(document.getElementById('suppr_'+k)){
				document.getElementById('suppr_'+k).checked=statut;

				if(statut==true){
					document.getElementById('tr_'+k).style.backgroundColor='orange';
				}
				else{
					document.getElementById('tr_'+k).style.backgroundColor='';
				}
			}
		}
		changement();
	}

	function modif_une_coche(ligne){
		statut=document.getElementById('suppr_'+ligne).checked;

		if(statut==true){
			document.getElementById('tr_'+ligne).style.backgroundColor='orange';
		}
		else{
			document.getElementById('tr_'+ligne).style.backgroundColor='';
		}
		changement();
	}
</script>\n";

}

require("../lib/footer.inc.php");
?>
