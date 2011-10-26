<?php
/*
 *
 * @version $Id$
 *
 * Copyright 2001, 2011 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Julien Jocal
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

$niveau_arbo = 1;

// Initialisations files
require_once("../lib/initialisations.inc.php");

// fonctions compl�mentaires et/ou librairies utiles

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == "c") {
   header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
   die();
} else if ($resultat_session == "0") {
    header("Location: ../logout.php?auto=1");
    die();
}

$sql="SELECT 1=1 FROM droits WHERE id='/statistiques/stat_connexions.php';";
$test=mysql_query($sql);
if(mysql_num_rows($test)==0) {
$sql="INSERT INTO droits SET id='/statistiques/stat_connexions.php',
administrateur='V',
professeur='F',
cpe='F',
scolarite='F',
eleve='F',
responsable='F',
secours='F',
autre='F',
description='Statistiques de connexion',
statut='';";
$insert=mysql_query($sql);
}

if (!checkAccess()) {
    header("Location: ../logout.php?auto=2");
    die();
}

$mode=isset($_POST['mode']) ? $_POST['mode'] : (isset($_GET['mode']) ? $_GET['mode'] : NULL);
$id_classe=isset($_POST['id_classe']) ? $_POST['id_classe'] : NULL;

$display_date_debut=isset($_POST['display_date_debut']) ? $_POST['display_date_debut'] : NULL;
$display_date_fin=isset($_POST['display_date_fin']) ? $_POST['display_date_fin'] : NULL;

$sql="SELECT DISTINCT id, classe FROM classes ORDER BY classe;";
//echo "$sql<br />\n";
$res_classes=mysql_query($sql);
$nb_classes=mysql_num_rows($res);
if($nb_classes>0) {
	$tab_classe=array();
	$cpt=0;
	while($lig_classe=mysql_fetch_object($res_classes)) {
		$tab_classe[$cpt]=array();
		$tab_classe[$cpt]['id']=$lig_classe->id;
		$tab_classe[$cpt]['classe']=$lig_classe->classe;

		$sql="SELECT DISTINCT login FROM j_eleves_classes WHERE id_classe='$lig_classe->id';";
		$res_eff=mysql_query($sql);
		$tab_classe[$cpt]['effectif']=mysql_num_rows($res_eff);

		$cpt++;
	}
}

// ===================== entete Gepi ======================================//
$titre_page = "Statistiques de connexion";
require_once("../lib/header.inc");
// ===================== fin entete =======================================//

//debug_var();

function tableau_php_tableau_html($tab) {
	$retour="";

	$retour="<table class='boireaus'>\n";
	$alt=1;
	for($loop=0;$loop<count($tab);$loop++) {
		$alt=$alt*(-1);
		$retour.="<tr class='lig$alt white_hover'>\n";
		$retour.="<td>".civ_nom_prenom($tab[$loop])."</td>\n";
		$retour.="</tr>\n";
	}
	$retour.="</table>\n";

	return $retour;
}


/*
# 0 : logout normal
# 2 : logout renvoy� par la fonction checkAccess (probl�me gepiPath ou acc�s interdit)
# 3 : logout li� � un timeout
# 4 : logout li� � une nouvelle connexion sous un nouveau profil
*/

if(!isset($mode)) {
	echo "<p class='bold'><a href='index.php'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>";
	
	if($nb_classes==0) {
		echo "<p style='color:red'>Aucune classe n'existe encore.</p>\n";
	
		require_once("../lib/footer.inc.php");
		die();
	}

	echo "<p>Choisissez&nbsp;:</p>\n";
	echo "<ul>\n";
	echo "<li><a href='".$_SERVER['PHP_SELF']."?mode=1'>Statistiques globales de connexions �l�ves et responsables</a></li>\n";
	echo "<li><a href='".$_SERVER['PHP_SELF']."?mode=2'>Statistiques des connexions parents d'une classe</a></li>\n";
	echo "</ul>\n";

	echo "<p style='color:red'>Faire une autre graphique avec les connexions �l�ves.</p>";
	echo "<p style='color:red'>Faire une autre graphique avec le nombre de connexions par semaine.</p>";
}
elseif($mode==1) {
	echo "<p class='bold'><a href='".$_SERVER['PHP_SELF']."'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>";
	
	$sql="select START from log order by START ASC limit 1;";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)>0) {
		// Toujours vrai si on est connect� pour consulter cette page
		$date_premier_log=mysql_result($res, 0);
		echo "<p>Les journaux de connexion remontent au ".formate_date($date_premier_log)."</p>";
	}
	
	$begin_bookings=getSettingValue('begin_bookings');
	$mysql_begin_bookings=strftime("%Y-%m-%d 00:00:00", $begin_bookings);
	echo "<p>Les logs ant�rieurs � ".formate_date($mysql_begin_bookings)." ne seront pas pris en compte.</p>\n";

	$sql="SELECT DISTINCT l.login from log l, resp_pers rp where rp.login=l.login and autoclose>='0' AND autoclose<='3' AND START>='$mysql_begin_bookings';";
	$res=mysql_query($sql);
	if(mysql_num_rows($res)==0) {
		echo "<p>Aucun compte parent n'a encore essay� de (<em>ou r�ussi �</em>) se connecter.</p>\n";
	}
	else {
		echo "<p>".mysql_num_rows($res)." parent(s) a(ont) r�ussi � se connecter � ce jour.</p>\n";
	
		$tab_parents_connectes_avec_succes=array();
		while($lig=mysql_fetch_object($res)) {
			$tab_parents_connectes_avec_succes[]=$lig->login;
		}
	
		// Mettre les pourcentages aussi
		echo "<p>Nombre d'�l�ves et responsables connect�s au moins une fois&nbsp;:</p>\n";
		echo "<table class='boireaus'>\n";
		echo "<tr>\n";
		echo "<th rowspan='2'>Classe</th>\n";
		echo "<th rowspan='2'>Effectif</th>\n";
		echo "<th colspan='2'>El�ves</th>\n";
		echo "<th rowspan='2'>Parents</th>\n";
		echo "<th colspan='2'>Parents d'enfants<br />diff�rents</th>\n";
		echo "<th rowspan='2'>Parents toujours<br />en �chec de<br />mot de passe</th>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<th>Eff.</th>\n";
		echo "<th>%</th>\n";
		echo "<th>Eff.</th>\n";
		echo "<th>%</th>\n";
		echo "</tr>\n";
		$alt=1;
		for($i=0;$i<count($tab_classe);$i++) {
			$sql="SELECT DISTINCT l.login FROM log l, j_eleves_classes jec WHERE jec.login=l.login AND jec.id_classe='".$tab_classe[$i]['id']."' AND l.autoclose>='0' AND l.autoclose<='3' AND l.login!='' AND START>='$mysql_begin_bookings' ORDER BY l.login;";
			//echo "$sql<br />";
			$res=mysql_query($sql);
			$nb_ele=mysql_num_rows($res);
			$tab_ele=array();
			if($nb_ele>0) {
				while($lig=mysql_fetch_object($res)) {
					$tab_ele[]=$lig->login;
				}
			}
			$titre_infobulle="El�ves connect�s au moins une fois\n";
			$texte_infobulle=tableau_php_tableau_html($tab_ele);
			$tabdiv_infobulle[]=creer_div_infobulle('div_ele_'.$i,$titre_infobulle,"",$texte_infobulle,"",25,0,'y','y','n','n');
	
			$sql="SELECT DISTINCT l.login FROM log l, resp_pers rp, eleves e, j_eleves_classes jec, responsables2 r WHERE jec.id_classe='".$tab_classe[$i]['id']."' AND jec.login=e.login AND e.ele_id=r.ele_id AND rp.pers_id=r.pers_id AND rp.login=l.login AND l.autoclose>='0' AND l.autoclose<='3' AND l.login!='' AND START>='$mysql_begin_bookings' ORDER BY l.login;";
			//echo "$sql<br />";
			$res=mysql_query($sql);
			$nb_parents=mysql_num_rows($res);
			$tab_resp=array();
			if($nb_parents>0) {
				while($lig=mysql_fetch_object($res)) {
					$tab_resp[]=strtoupper($lig->login);
					
				}
			}
			/*
			if($tab_classe[$i]['classe']=='5 D') {
				print_r($tab_resp);
			}
			*/
			$titre_infobulle="Parents connect�s au moins une fois\n";
			$texte_infobulle="<div align='center'>".tableau_php_tableau_html($tab_resp)."</div>";
			$tabdiv_infobulle[]=creer_div_infobulle('div_resp_'.$i,$titre_infobulle,"",$texte_infobulle,"",25,0,'y','y','n','n');
	
			$sql="SELECT DISTINCT l.login FROM log l, resp_pers rp, eleves e, j_eleves_classes jec, responsables2 r WHERE jec.id_classe='".$tab_classe[$i]['id']."' AND jec.login=e.login AND e.ele_id=r.ele_id AND rp.pers_id=r.pers_id AND rp.login=l.login AND l.autoclose='4' AND l.login!='' AND START>='$mysql_begin_bookings' ORDER BY l.login;";
			//echo "$sql<br />";
			$res=mysql_query($sql);
			$nb_parents_erreur_mdp=mysql_num_rows($res);
			$nb_parents_erreur_mdp_et_jamais_connectes_avec_succes=0;
			$tab_liste_parents_erreur_mdp_et_jamais_connectes_avec_succes=array();
			if($nb_parents_erreur_mdp>0) {
				while($lig=mysql_fetch_object($res)) {
					if(!in_array(strtoupper($lig->login), $tab_resp)) {
						$nb_parents_erreur_mdp_et_jamais_connectes_avec_succes++;
						$tab_liste_parents_erreur_mdp_et_jamais_connectes_avec_succes[]=$lig->login;
					}
				}
			}
			/*
			if($tab_classe[$i]['classe']=='5 D') {
				print_r($tab_liste_parents_erreur_mdp_et_jamais_connectes_avec_succes);
			}
			*/
			$titre_infobulle="Parents en �chec de connexion\n";
			$texte_infobulle=tableau_php_tableau_html($tab_liste_parents_erreur_mdp_et_jamais_connectes_avec_succes);
			$tabdiv_infobulle[]=creer_div_infobulle('div_resp_echec_'.$i,$titre_infobulle,"",$texte_infobulle,"",25,0,'y','y','n','n');
	
			$sql="SELECT DISTINCT jec.login FROM log l, resp_pers rp, eleves e, j_eleves_classes jec, responsables2 r WHERE jec.id_classe='".$tab_classe[$i]['id']."' AND jec.login=e.login AND e.ele_id=r.ele_id AND rp.pers_id=r.pers_id AND rp.login=l.login AND l.autoclose>='0' AND l.autoclose<='3' AND START>='$mysql_begin_bookings' ORDER BY l.login;";
			//echo "$sql<br />";
			$res=mysql_query($sql);
			$nb_parents_differents=mysql_num_rows($res);
	
			$alt=$alt*(-1);
			echo "<tr class='lig$alt white_hover'>\n";
			echo "<td>";
			echo $tab_classe[$i]['classe'];
			echo "</td>\n";
	
			echo "<td>";
			echo $tab_classe[$i]['effectif'];
			echo "</td>\n";
	
			if($nb_ele>0) {
				echo "<td>";
				echo "<a href=\"#\" onclick=\"afficher_div('div_ele_$i','y',10,10);return false;\"  onmouseover=\"delais_afficher_div('div_ele_$i','y',10,10,20,20,1000);\"  onmouseout=\"cacher_div('div_ele_$i');\">";
				echo $nb_ele."/".$tab_classe[$i]['effectif'];
				echo "</a>";
				echo "</td>\n";
				echo "<td>\n";
				echo (round(100*10*$nb_ele/$tab_classe[$i]['effectif'])/10);
				//echo "%";
				echo "</td>\n";
			}
			else {
				echo "<td>\n";
				echo $nb_ele;
				echo "</td>\n";
				echo "<td>\n";
				echo $nb_ele;
				echo "</td>\n";
			}
	
			echo "<td>";
			if($nb_parents>0) {
				echo "<a href=\"#\" onclick=\"afficher_div('div_resp_$i','y',10,10);return false;\" onmouseover=\"delais_afficher_div('div_resp_$i','y',10,10,20,20,1000);\" onmouseout=\"cacher_div('div_resp_$i');\">";
				echo $nb_parents;
				//echo "<br />".(round(100*10*$nb_parents/$tab_classe[$i]['effectif'])/10)."%";
				echo "</a>";
			}
			else {
				echo $nb_parents;
			}
			echo "</a>";
			echo "</td>\n";
	
			if($nb_parents_differents>0) {
				echo "<td>";
				echo $nb_parents_differents;
				echo "</td>\n";
				echo "<td>\n";
				echo (round(100*10*$nb_parents_differents/$tab_classe[$i]['effectif'])/10);
				//echo "%";
				echo "</td>\n";
			}
			else {
				echo "<td>\n";
				echo $nb_parents_differents;
				echo "</td>\n";
				echo "<td>\n";
				echo $nb_parents_differents;
				echo "</td>\n";
			}
	
			echo "<td>";
			if($nb_parents_erreur_mdp_et_jamais_connectes_avec_succes>0) {
				echo "<a href=\"#\" onclick=\"afficher_div('div_resp_echec_$i','y',10,10);return false;\"  onmouseover=\"delais_afficher_div('div_resp_echec_$i','y',10,10,20,20,1000);\"  onmouseout=\"cacher_div('div_resp_echec_$i');\" style='color:red'>";
				echo $nb_parents_erreur_mdp_et_jamais_connectes_avec_succes;
				//echo "<br />".(round(100*10*$nb_parents_erreur_mdp_et_jamais_connectes_avec_succes/$tab_classe[$i]['effectif'])/10)."%";
				echo "</a>";
			}
			else {
				echo $nb_parents_erreur_mdp_et_jamais_connectes_avec_succes;
			}
			echo "</td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}

	echo "<p><br /></p>\n";

}
elseif($mode==2) {
	echo "<p class='bold'><a href='".$_SERVER['PHP_SELF']."'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>";

	echo "<p class='bold'>Connexions parents&nbsp;:</p>\n";

	//=======================
	//Configuration du calendrier
	include("../lib/calendrier/calendrier.class.php");
	//$cal1 = new Calendrier("form_choix_edit", "display_date_debut");
	//$cal2 = new Calendrier("form_choix_edit", "display_date_fin");
	$cal1 = new Calendrier("formulaire", "display_date_debut");
	$cal2 = new Calendrier("formulaire", "display_date_fin");
	
	$annee = strftime("%Y");
	$mois = strftime("%m");
	$jour = strftime("%d");
	
	if($mois>8) {$date_debut_tmp="01/09/$annee";} else {$date_debut_tmp="01/09/".($annee-1);}
	
	//$display_date_debut=isset($_POST['display_date_debut']) ? $_POST['display_date_debut'] : (isset($_SESSION['display_date_debut']) ? $_SESSION['display_date_debut'] : $jour."/".$mois."/".$annee);
	$display_date_debut=isset($_POST['display_date_debut']) ? $_POST['display_date_debut'] : (isset($_SESSION['display_date_debut']) ? $_SESSION['display_date_debut'] : $date_debut_tmp);
	
	$display_date_fin=isset($_POST['display_date_fin']) ? $_POST['display_date_fin'] : (isset($_SESSION['display_date_fin']) ? $_SESSION['display_date_fin'] : $jour."/".$mois."/".$annee);
	//=======================
	
	echo "<br />\n";
	echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>\n";
	echo "<fieldset>\n";
	echo add_token_field();
	
	// Choix de la classe
	
	// =================================
	// AJOUT: boireaus
	$chaine_options_classes="";
	$sql="SELECT id, classe FROM classes ORDER BY classe";
	$res_class_tmp=mysql_query($sql);
	if(mysql_num_rows($res_class_tmp)>0){
		$id_class_prec=0;
		$id_class_suiv=0;
		$temoin_tmp=0;
		$cpt_classe=0;
		$num_classe=-1;
		while($lig_class_tmp=mysql_fetch_object($res_class_tmp)){
			if((isset($id_classe))&&($lig_class_tmp->id==$id_classe)) {
				// Index de la classe dans les <option>
				$num_classe=$cpt_classe;
	
				$chaine_options_classes.="<option value='$lig_class_tmp->id' selected='true'>$lig_class_tmp->classe</option>\n";
				$temoin_tmp=1;
				if($lig_class_tmp=mysql_fetch_object($res_class_tmp)){
					$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
					$id_class_suiv=$lig_class_tmp->id;
				}
				else{
					$id_class_suiv=0;
				}
			}
			else {
				$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
			}
	
			if($temoin_tmp==0){
				$id_class_prec=$lig_class_tmp->id;
			}
			$cpt_classe++;
		}
	}// =================================
	
	echo "Classe&nbsp;: <select name='id_classe' id='id_classe'>\n";
	echo $chaine_options_classes;
	echo "</select>\n";
	
	echo "<br />\n";
	
	echo "De la date&nbsp;: ";
	
	echo "<input type='text' name = 'display_date_debut' id = 'display_date_debut' size='10' value = \"".$display_date_debut."\" onKeyDown=\"clavier_date(this.id,event);\" AutoComplete=\"off\" />";
	
	echo "<a href=\"#calend\" onClick=\"".$cal1->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\"><img src=\"../lib/calendrier/petit_calendrier.gif\" alt=\"Calendrier\" border=\"0\" /></a>\n";
	
	echo "&nbsp;� la date&nbsp;: ";
	echo "<input type='text' name = 'display_date_fin' id = 'display_date_fin' size='10' value = \"".$display_date_fin."\" onKeyDown=\"clavier_date(this.id,event);\" AutoComplete=\"off\" />";
	echo "<label for='choix_periode_dates' style='cursor: pointer;'><a href=\"#calend\" onClick=\"".$cal2->get_strPopup('../lib/calendrier/pop.calendrier.php', 350, 170)."\"><img src=\"../lib/calendrier/petit_calendrier.gif\" alt=\"Calendrier\" border=\"0\" /></a>\n";
	echo "<br />\n";
	echo " (<i>Veillez � respecter le format jj/mm/aaaa</i>)\n";
	echo "<input type='hidden' name='mode' value='2' />\n";
	echo "<input type='submit' value='Valider' />\n";
	echo "</fieldset>\n";
	echo "</form>\n";
	echo "<br />\n";

	$tab=explode("/", $display_date_debut);
	$jour_debut=$tab[0];
	$mois_debut=$tab[1];
	$annee_debut=$tab[2];

	$tab=explode("/", $display_date_fin);
	$jour_fin=$tab[0];
	$mois_fin=$tab[1];
	$annee_fin=$tab[2];

	//$timestamp_debut=gmmktime(0, 0, 0, $mois_debut, $jour_debut, $annee_debut);
	//$timestamp_fin=gmmktime(0, 0, 0, $mois_fin, $jour_fin, $annee_fin);

	$date_mysql_debut="$annee_debut-$mois_debut-$jour_debut 00:00:00";
	$date_mysql_fin="$annee_fin-$mois_fin-$jour_fin 00:00:00";

	if($id_classe!='') {
		$sql="SELECT DISTINCT l.login, l.START from log l, resp_pers rp, responsables2 r, eleves e, j_eleves_classes jec WHERE jec.id_classe='$id_classe' AND e.login=jec.login AND r.ele_id=e.ele_id AND r.pers_id=rp.pers_id AND rp.login=l.login AND l.login!='' AND autoclose>='0' AND autoclose<='3' AND START>='".$date_mysql_debut."' AND END<='".$date_mysql_fin."' ORDER BY l.START, l.login;";
		//echo "$sql<br />";
		$res=mysql_query($sql);
		if(mysql_num_rows($res)==0) {
			echo "<p>Aucun compte parent n'a encore essay� de (<em>ou r�ussi �</em>) se connecter.</p>\n";
		}
		else {
			$tab_connexions=array();
			while($lig=mysql_fetch_object($res)) {
				$tab=explode(" ", $lig->START);
				$date=$tab[0];
				/*
				$tmp_date=explode("-", $date);
				$jour=$tmp_date[2];
				$mois=$tmp_date[1];
				$annee=$tmp_date[0];
	
				$timestamp=gmmktime(0, 0, 0, $mois, $jour, $annee);
				//$date=$timestamp;
				*/
				if(!isset($tab_connexions[$date]['login'])) {
					$tab_connexions[$date]['login']=array();
				}
				if(!in_array(strtoupper($lig->login), $tab_connexions[$date]['login'])) {
					$tab_connexions[$date]['login'][]=strtoupper($lig->login);
				}
			}
	
			/*
			echo "<pre>";
			print_r($tab_connexions);
			echo "</pre>";
			*/
	
			$timestamp_debut=gmmktime(0, 0, 0, $mois_debut, $jour_debut, $annee_debut);
			$timestamp_fin=gmmktime(0, 0, 0, $mois_fin, $jour_fin, $annee_fin);
	
			$js_jour_sem="";
			$js_jour="";
			$js_mois="";
	
			$my_echo_debug=0;
			$mode_my_echo_debug="";
	
			my_echo_debug("<table class='boireaus'>\n");
			my_echo_debug("<tr>\n");
			$timestamp=$timestamp_debut;
			while($timestamp<=$timestamp_fin) {
				$date=strftime("%Y-%m-%d", $timestamp);
	
				my_echo_debug("<td style='font-size:x-small'>".$date."</td>\n");
		
				if($js_jour_sem!="") {$js_jour_sem.=",";}
				$js_jour_sem.="'".strftime("%a", $timestamp)."'";
				
				if($js_jour!="") {$js_jour.=",";}
				$js_jour.="'".strftime("%d", $timestamp)."'";
				
				if($js_mois!="") {$js_mois.=",";}
				$js_mois.="'".strftime("%m", $timestamp)."'";
	
				$timestamp+=3600*24;
			}
			my_echo_debug("</tr>\n");
	
			$js_eff="";
			my_echo_debug("<tr>\n");
			$timestamp=$timestamp_debut;
			$compteur_jour=0;
			$max_eff=0;
			while($timestamp<=$timestamp_fin) {
				$date=strftime("%Y-%m-%d", $timestamp);
	
				my_echo_debug("<td>");
				if(isset($tab_connexions["$date"])) {
					$eff=count($tab_connexions["$date"]['login']);
					if($eff>$max_eff) {$max_eff=$eff;}
				}
				else {
					$eff=0;
				}
				my_echo_debug($eff);
	
				if($js_eff!="") {$js_eff.=",";}
				$js_eff.="'".$eff."'";
	
				my_echo_debug("</td>\n");
	
				$timestamp+=3600*24;
				$compteur_jour++;
			}
			my_echo_debug("</tr>\n");
			my_echo_debug("</table>\n");
	
			
			$largeur_barre=10;
			$largeur_totale=ceil(1.5*$largeur_barre*$compteur_jour)+20*2;
			
			$unite_y=20;
			
			$hauteur_totale=max(100,$unite_y*$max_eff)+3*$unite_y;
			$hauteur_totale_min=$hauteur_totale-40;
			$nb_grad_y=floor($hauteur_totale_min/$unite_y);
		
			echo "<canvas id='diagramme' width='$largeur_totale' height='$hauteur_totale'></canvas>\n";
	
			echo "<script type='text/javascript'>
	var texte_mois=new Array();
	texte_mois[1]='Janvier';
	texte_mois[2]='F�vrier';
	texte_mois[3]='Mars';
	texte_mois[4]='Avril';
	texte_mois[5]='Mai';
	texte_mois[6]='Juin';
	texte_mois[7]='Juillet';
	texte_mois[8]='Aout';
	texte_mois[9]='Septembre';
	texte_mois[10]='Octobre';
	texte_mois[11]='Novembre';
	texte_mois[12]='D�cembre';
	
	// Fonction de tracer de trait
	function tracer_ligne(ctx,x1,y1,x2,y2)  {
		ctx.beginPath();
		ctx.moveTo(x1, y1);
		ctx.lineTo(x2, y2);
		ctx.closePath();
		ctx.stroke();
	}
	
	var canvas = document.getElementById('diagramme');
	var context = canvas.getContext('2d');
	
	// Fond
	context.beginPath();
	context.rect(0, 0, $largeur_totale, $hauteur_totale);
	context.closePath();
	context.stroke();
	context.fillStyle = 'white';
	context.fill();
	
	var jour = [$js_jour];
	var jour_sel = [$js_jour_sem];
	var mois = [$js_mois];
	var eff = [$js_eff];
	
	// Origine du repere
	context.translate(20,".$hauteur_totale_min.");
	var x0 = 0;
	var y0 = 0;
	
	var largeur_barre = $largeur_barre;
	context.lineWidth = '1.0';
	
	// Couleur et largeur du trait
	context.fillStyle = '#000';
	context.lineWidth = '1.0';
	
	tracer_ligne(context,x0,y0,x0,-$hauteur_totale_min);
	
	context.font = '7pt Calibri,Geneva,Arial';
	context.lineWidth = '0.6';
	
	context.fillStyle = 'black';
	//context.strokeStyle = 'green';
	for(i=0;i<$nb_grad_y;i++) {
		//context.lineWidth = '0.5';
		//context.strokeText(i, x0-10, y0-i*$unite_y);
		context.fillText(i, x0-10, y0-i*$unite_y);
		//context.lineWidth = '0.1';
		tracer_ligne(context,x0,y0-i*$unite_y,$largeur_totale-20,y0-i*$unite_y);
	}
	
	context.lineWidth = '1.0';
	
	/*
	context.fillStyle = 'red';
	context.beginPath();
	context.rect(x0, y0, 30, 10);
	context.closePath();
	context.stroke();
	context.fill();
	context.fillStyle = '#000';
	
	context.fillStyle = 'red';
	context.beginPath();
	context.rect(x0+40, y0, 30, -50);
	context.closePath();
	context.stroke();
	context.fill();
	context.fillStyle = '#000';
	*/
	
	mois_precedent='';
	for (i=0; i<jour.length; i++) {
		context.fillStyle = 'steelblue';
		context.beginPath();
		//context.rect(x0+10 + (i * largeur_barre) +5*i, y0 -1 - eff[i], largeur_barre, 10*eff[i]);
		context.rect(x0+10+Math.round(i*1.5*largeur_barre), y0, largeur_barre, -$unite_y*eff[i]);
		context.closePath();
		context.stroke();
		context.fill();
	
		//context.lineWidth = '0.5';
		//context.lineWidth = '0.6';
		context.fillStyle = 'black';
		context.lineWidth = '1.0';
		//context.strokeText(jour[i], x0+10+Math.round(i*1.5*largeur_barre), y0+10);
		context.fillText(jour[i], x0+10+Math.round(i*1.5*largeur_barre), y0+10);
		j=jour_sel[i];
		//context.strokeText(j.substr(0,1), x0+10+Math.round(i*1.5*largeur_barre), y0+20);
		context.fillText(j.substr(0,1).toUpperCase(), x0+10+Math.round(i*1.5*largeur_barre), y0+20);
	
		if(mois[i]!=mois_precedent) {
			context.fillText(texte_mois[eval(mois[i])], x0+10+Math.round(i*1.5*largeur_barre), y0+30);
			//context.fillText(mois[i], x0+10+Math.round(i*1.5*largeur_barre), y0+30);
			mois_precedent=mois[i];
		}
	
		//context.fillStyle = '#000';
	
		//var mesure_texte = context.measureText(ages [i]).width;
		//var centrer_texte = (largeur_barre - mesure_texte)/2;
		//context.fillText(ages[i], x0+10 +centrer_texte + (i * largeur_barre) + 5*i, y0 + 18);
	}
	
</script>\n";
	
		}
	}
}
/*
for($i=0;$i<30;$i++) {
	echo "<p><br /></p>\n";
}
*/
//echo "<p><em>NOTES&nbsp;:</em></p>\n";

require_once("../lib/footer.inc.php");
?>
