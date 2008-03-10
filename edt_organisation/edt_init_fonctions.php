<?php

/**
 *
 *
 * @version $Id$
 *
 * Ensemble des fonctions qui renvoient la concordance pour le fichier txt
 * de l'import des EdT.
 *
 * Copyright 2001, 2008 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, St�phane Boireau, Julien Jocal
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

// le login du prof
function renvoiLoginProf($numero){
	// on cherche dans la base
	$query = mysql_query("SELECT nom_gepi FROM edt_init WHERE nom_export = '".$numero."' AND ident_export = '1'");
	if ($query) {
		$retour = mysql_result($query, "nom_gepi");
	}else{
		$retour = 'erreur_prof';
	}

	return $retour;
}

// la salle
function renvoiIdSalle($chiffre){
	// On cherche l'Id de la salle
		$retour = 'erreur_salle';
	// On ne prend que les 10 premi�res lettres du num�ro ($chiffre)
	$cherche = substr($chiffre, 0, 10);
	$query = mysql_query("SELECT id_salle FROM salle_cours WHERE numero_salle = '".$cherche."'");
	if ($query) {
		$reponse = mysql_result($query, "id_salle");
		if ($reponse == '') {
			$retour = "inc";
		}else{
			$retour = $reponse;
		}
	}else{
		$retour = 'erreur_salle';
	}

	return $retour;
}

// le jour
function renvoiJour($diminutif){
	// Les jours sont de la forme lu, Ma, Je,...
	switch ($diminutif) {
	case 'Lu':
	    $retour = 'lundi';
	    break;
	case 'Ma':
	    $retour = 'mardi';
	    break;
	case 'Me':
	    $retour = 'mercredi';
	    break;
	case 'Je':
	    $retour = 'jeudi';
	    break;
	case 'Ve':
	    $retour = 'vendredi';
	    break;
	case 'Sa':
	    $retour = 'samedi';
	    break;
	case 'Di':
	    $retour = 'dimanche';
	    break;
	default :
		$retour = 'inc';
	}
	return $retour;
}

// renvoie le nom de la bonne table des cr�neaux
function nomTableCreneau($jour){
	$jour_semaine = array("dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi");
		$numero_jour = NULL;
	for($t = 0; $t < 7; $t++){
		// On cherche � faire correspondre le numero_jour avec ce que donne la fonction php date("w")
		if ($jour == $jour_semaine[$t]) {
			$numero_jour = $t;
		}else{
			// A priori il n'y a rien � faire
		}
	}
	// Ensuite, en fonction du r�sultat, on teste et on renvoie la bonne table des cr�neaux
	if ($numero_jour == getSettingValue("jour_different")) {
		$retour = 'absences_creneaux_bis';
	}else{
		$retour = 'absences_creneaux';
	}

	return $retour;
}
// Id du cr�neau de d�but
function renvoiIdCreneau($heure_brute, $jour){
	// On transforme $heure_brute en un horaire de la forme hh:mm:ss
	$minutes = substr($heure_brute, 2);
	$heures = substr($heure_brute, 0, -2);
	$heuredebut = $heures.':'.$minutes.':00';
	$table = nomTableCreneau($jour);
	$query = mysql_query("SELECT id_definie_periode FROM ".$table." WHERE
					heuredebut_definie_periode <= '".$heuredebut."' AND
					heurefin_definie_periode > '".$heuredebut."'")
						OR DIE('Erreur renvoiIdCreneau : '.mysql_error());
	if ($query) {
		$retour = mysql_result($query, "id_definie_periode");
	}else{
		$retour = 'erreur_creneau';
	}

	return $retour;
}

// dur�e d'un cr�neau dans Gepi
function dureeCreneau(){
	// On r�cup�re les infos sur un cr�neau
	$creneau = mysql_fetch_array(mysql_query("SELECT heuredebut_definie_periode, heurefin_definie_periode FROM absences_creneaux LIMIT 1"));
	$deb = $creneau["heuredebut_definie_periode"];
	$fin = $creneau["heurefin_definie_periode"];
	$nombre_mn_deb = (substr($deb, 0, -5) * 60) + (substr($deb, 3, -3));
	$nombre_mn_fin = (substr($fin, 0, -5) * 60) + (substr($fin, 3, -3));
	$retour = $nombre_mn_fin - $nombre_mn_deb;

	return $retour;
}

// La dur�e pour les imports texte
function renvoiDuree($deb, $fin){
	// On d�termine la dur�e d'un cours
	$duree_cours_base = dureeCreneau();
	$nombre_mn_deb = (substr($deb, 0, -2) * 60) + (substr($deb, 2));
	$nombre_mn_fin = (substr($fin, 0, -2) * 60) + (substr($fin, 2));
	$duree_mn = $nombre_mn_fin - $nombre_mn_deb;
	// le nombre d'heures enti�res
	$nbre = $duree_mn / $duree_cours_base;
	settype($nbre, 'integer');
	// le nombre de minutes qui restent
	$mod = $duree_mn % $duree_cours_base;
	// Et on analyse ce dernier (attention, la dur�e se compte en demi-cr�neaux)
	if ($mod >= (($duree_cours_base * 2) / 3)) {
		// Si c'est sup�rieur au 2/3 de la dur�e du cours, alors c'est une heure enti�re
		$retour = ($nbre * 2) + 2;
	}elseif($mod > (($duree_cours_base) / 3)) {
		// Si c'est sup�rieur au tiers de la dur�e d'un cours, alors c'est un demi-cr�neau de plus
		$retour = ($nbre * 2) + 1;
	}else{
		// sinon, c'est un souci de quelques minutes sans importance
		$retour = $nbre * 2;
	}

	return $retour;
}

// Heure debut decal�e ou pas
function renvoiDebut($id_creneau, $heure_deb, $jour){
	// On d�termine la dur�e d'un cours
	$duree_cours_base = dureeCreneau();
	// nbre de mn de l'heure de l'import
	$nombre_mn_deb = (substr($heure_deb, 0, -2) * 60) + (substr($heure_deb, 2));
	// Nombre de mn de l'horaire de Gepi
	$table = nomTableCreneau($jour);
	if ($id_creneau != '') {
			$heure = mysql_fetch_array(mysql_query("SELECT heuredebut_definie_periode FROM ".$table." WHERE id_definie_periode = '".$id_creneau."'"));
		$decompose = explode(":", $heure["heuredebut_definie_periode"]);
		$nbre_mn_gepi = ($decompose[0] * 60) + $decompose[1];
		// On fait la diff�rence entre les deux horaires qui ont �t� convertis en nombre de minutes
		$diff = $nombre_mn_deb - $nbre_mn_gepi;
		// et on analyse cette diff�rence
		if ($diff === 0 OR $diff < ($duree_cours_base / 4)) {
			$retour = '0';
		}elseif($diff > ($duree_cours_base / 3) AND $diff < (($duree_cours_base / 3) * 2)){
			$retour = '0.5';
		}else{
			$retour = '0';
		}
	}else{
		// par d�faut, on renvoie un d�but classique
		$retour = '0';
	}

	return $retour;
}

// Renvoi des concordances
function renvoiConcordances($chiffre, $etape){
	// On r�cup�re dans la table edt_init la bonne concordance
	// 2=Classe 3=GROUPE 4=PARTIE 5=Mati�res pour IndexEducation
	// 1=cr�neaux 2=classe 3=mati�re 4=professeurs 7=regroupements 10=fr�quence pour UDT de OMT
	$query = mysql_query("SELECT nom_gepi FROM edt_init WHERE nom_export = '".$chiffre."' AND ident_export = '".$etape."'");
	if ($query) {
		$reponse = mysql_result($query, "nom_gepi");
		if ($reponse == '') {
			$retour = "inc";
		}else{
			$retour = $reponse;
		}
	}else{
		$retour = "erreur_".$etape;
	}

	return $retour;
}

// L'id_groupe
function renvoiIdGroupe($prof, $classe_txt, $matiere_txt, $grp_txt, $partie_txt, $type_import){
	// $prof est le login du prof tel qu'il existe dans Gepi, alors que les autresinfos ne sont pas encore "concord�s"

	if ($type_import == 'texte') {
		// On se pr�occupe de la partie qui arrive de edt_init_texte.php et edt_init_concordance.php
		// Les autres variables sont explicites dans leur d�signation (c'est leur nom dans l'export texte)
		$classe = renvoiConcordances($classe_txt, 2);
		$matiere = renvoiConcordances($matiere_txt, 5);
		$partie = $partie_txt; //renvoiConcordances($partie_txt, 4);
		$grp = renvoiConcordances($grp_txt, 3);
	}elseif($type_import == 'csv2'){
		// On se pr�occupe de la partie csv2 venant de edt_init_csv2.php et edt_init_concordance2.php
		$classe = $classe_txt;
		$matiere = $matiere_txt;
		$partie = '';
		$grp = $grp_txt;
	}else{
		$classe = '';
		$matiere = '';
		$partie = '';
		$grp = '';
	}


	// On commence par le groupe. S'il existe, on le renvoie tout de suite
	if ($grp != "aucun" AND $grp != '' AND $grp != "inc") {
		return $grp;
	//}elseif($partie != "aucun" AND $partie != ''){
		// Pour le moment, on n'utilise pas �a
	}else{
		// On r�cup�re la classe, la mati�re et le professeur
		// et on cherche un enseignement qui pourrait correspondre avec
		$req_groupe = mysql_query("SELECT jgp.id_groupe FROM j_groupes_professeurs jgp, j_groupes_classes jgc, j_groupes_matieres jgm WHERE
						jgp.login = '".$prof."' AND
						jgc.id_classe = '".$classe."' AND
						jgm.id_matiere = '".$matiere."' AND
						jgp.id_groupe = jgc.id_groupe AND
						jgp.id_groupe = jgm.id_groupe");

    	$rep_groupe = mysql_fetch_array($req_groupe);
    	$nbre_rep = mysql_num_rows($req_groupe);
    	// On v�rifie ce qu'il y a dans la r�ponse
    	if ($nbre_rep == 0) {
			$retour = "aucun";
		} elseif ($nbre_rep > 1) {
    		$retour = "plusieurs";
    	}else{
			$retour = $rep_groupe["id_groupe"];
		}

	} // fin du else

	return $retour;
}

/*
 * Fonction qui teste si une salle existe dans Gepi et qui l'enregistre si elle n'existe pas
 * $numero est le num�ro de la salle
*/
function testerSalleCsv2($numero){
	// On teste la table
	$query = mysql_query("SELECT id_salle FROM salle_cours WHERE numero_salle = '".$numero."'")
				OR error_reporting('Erreur dans la requ�te '.$query.' : '.mysql_error());
	$rep = @mysql_result($query, "id_salle");
	if ($rep != '' AND $rep != NULL AND $rep != FALSE) {
		// On renvoie "ok"
		return "ok";
	}else{
		// On enregistre la nouvelle salle
		$query2 = mysql_query("INSERT INTO salle_cours SET numero_salle = '".$numero."', nom_salle = ''");
		if ($query2) {
			return "enregistree";
		}
	}
}

/*
 * Fonction qui fonction renvoie l'id du cr�neau de d�part, la dur�e et le moment du d�but du cours (CSV2)
 * sous la forme d'un tableau id_creneau, duree et debut
*/
function rechercheCreneauCsv2($creneau){
	$duree_base = dureeCreneau();

	// On fait attention � la construction de ce cr�neau
	$test1 = explode(" - ", $creneau);

	// Pour le id du creneau
	$id_creneau = renvoiConcordances($creneau, 1);
	if ($id_creneau != 'inc') {
		$retour["id_creneau"] = $id_creneau;
	}else{
		// Il faut chercher d'une autre fa�on le bon id de cours avec $test1[0]
		$test2 = explode("h", $test1[0]); // $test2[0] = 8 et $test2[1] = 00
		if (strlen($test2[0]) < 2) {
			// On ajoute un '0' devant l'heure
			$heure = '0'.$test2[0];
		}else{
			$heure = $test2[0];
		}
		$heure_reconstruite = $heure.':'.$test2[1].':'.'00';
		$query = mysql_query("SELECT DISTINCT id_definie_periode FROM absences_creneaux
						WHERE heuredebut_definie_periode <= '".$heure_reconstruite."'
						ORDER BY heuredebut_definie_periode ASC LIMIT 1");
		if ($query) {
			// On a trouv�
			$reponse_id = mysql_fetch_array($query);
			if ($reponse_id["id_definie_periode"] != '') {
				$retour["id_creneau"] = $id_creneau;
			}else{
				// Si on n'a pas de r�ponse valide, on ne peut pas d�finir le cours
				return 'erreur';
			}
		}
	}

	// la dur�e et le d�but
	if (isset($test1[1])) {
		// �a veut dire que le cr�neau �tudi� est de la forme 8h00 - 9h35 : $test1[0] = 8h00 et $test1(1] = 9h00
		// on recherche si le d�but est bon ou pas pour savoir si le cours commence au d�but du cr�neau ou pas
		$heure_debut = mysql_fetch_array(mysql_query("SELECT heuredebut_definie_periode FROM absences_creneaux WHERE id_definie_periode = '".$id_creneau."'"));
		$test3 = explode(":", $heure_debut);
		if (substr($test3[0], 0, -1) == "0") {
			$heu = substr($test3[0], -1);
		}else{
			$heu = $test3[0];
		}

		// On d�finit le moment de d�but du cours
		if (($heu.'h'.$test3[1]) == $test[0]) {
			// Le cours commence au d�but du cr�neau
			$retour["debut"] = '0';
		}else{
			// Le cours commence au milieu du cr�neau
			$retour["debut"] = 'O.5';
		}

		// On d�finit la dur�e
		$he0 = explode("h", $test1[0]); // l'heure de d�but de la demande
		$he1 = explode("h", $test1[1]); // l'heure de fin de la demande
		$duree_demandee = (60 * ($he1[0] - $he0[0])) + ($he1[1] - $he0[1]);
		if ($duree_demandee == $duree_base) {
			// ALors la dur�e est de 1 cr�neau donc 2 pour Gepi
			$retour["duree"] = 2;
		}elseif($duree_demandee < $duree_base){
			// Alors le cours la moiti� d'un cr�neau
			$retour["duree"] = 1;
		}else{
			// Le cours dure plus de 1 cr�neau
			// On d�termine la dur�e exacte
			$test_duree = $duree_demandee / $duree_base;
			// On r�cup�re le nombre de cr�neaux entiers
			$nbre_t = explode(".", $test_duree); // $nbre_t[0] est donc le nombre cr�neaux entiers
			if (isset($nbre_t[1])) {
				$test2 = substr($nbre_t[1], 0, 1); // on ne garde que le premier chiffre apr�s la virgule
			}else{
				$test2 = 0;
			}

			if ($test2 < 3) {
				// c'est fini
				$retour["duree"] = $nbre_t[0] * 2;
			}elseif($test2 > 7){
				// On ajoute 1 cr�neau entier en plus
				$retour["duree"] = ($nbre_t[0] * 2) + 2;
			}else{
				// On ajoute un demi cr�neau en plus
				$retour["duree"] = ($nbre_t[0] * 2) + 1;
			}

		}

	}else{
		// �a veut dire que le cours commence au d�but du cr�neau et dure 1 cr�neau (donc 2 pour Gepi)
		$retour["duree"] = '2';
		$retour["debut"] = '0';
	}
	return $retour;
}

/*
 * Fonction qui enregistre les cours des imports UDT de OMT
*/
function enregistreCoursCsv2($jour, $creneau, $classe, $matiere, $prof, $salle, $groupe, $regroupement, $effectif, $modalite, $frequence, $aire){
	// Les �tapes vont de 0 � 11 en suivant l'ordre des variables ci-dessus
	// Si un cours est enregistr�, on renvoie 'oui', sinon on renvoie 'non'

	// le jour => il est bon, il faut juste l'�crire en minuscule
	$jour_e = strtolower($jour);
	// Cette fonction renvoie l'id du cr�neau de d�part, la dur�e et le moment du d�but du cours
	$test_creneau = rechercheCreneauCsv2($creneau);
	$creneau_e = $test_creneau["id_creneau"];
	$duree_e = $test_creneau["duree"];
	$heuredeb_dec = $test_creneau["debut"];
	// On r�cup�re les concordances
	$classe_e = renvoiConcordances($classe, 2);
	$matiere_e = renvoiConcordances($matiere, 3);
	$prof_e = renvoiConcordances($prof, 4);
	$salle_e = $salle; // on peut se le permettre puisque le travail sur les salles a d�j� �t� effectu�
	$type_semaine = renvoiConcordances($frequences, 10);

	// Il reste � d�terminer le groupe
	if ($regroupement != '') {
		$groupe_e = renvoiConcordances($regroupement, 7);
	}else{
		// On recherche le groupe
		renvoiIdGroupe($prof_e, $classe_e, $matiere_e, $groupe_e, $groupe, 'csv2');
	}

	// On v�rifie si tous les champs importants sont pr�cis�s ou non
	if ($jour_e != '' OR $creneau_e != 'erreur') {
		return 'non';
	}else{
		// On enregistre la ligne
		$sql = "INSERT INTO `edt_cours` (`id_cours`,
										`id_groupe`,
										`id_salle`,
										`jour_semaine`,
										`id_definie_periode`,
										`duree`,
										`heuredeb_dec`,
										`id_semaine`,
										`id_calendrier`,
										`modif_edt`,
										`login_prof`)
								VALUES ('',
										'".$a."',
										'".$salle_e."',
										'".$jour_e."',
										'".$creneau_e."',
										'".$duree_e."',
										'".$heuredeb_dec."',
										'".$type_semaine."',
										'0',
										'0',
										'".$prof_e."')";
		// et on renvoie 'ok'
		return 'ok';
	}
}
?>