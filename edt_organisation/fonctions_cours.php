<?php

/**
 * Ensemble des fonctions qui permettent de cr�er un nouveau cours en v�rifiant les pr�c�dents
 *
 * @version $Id$
 * @copyright 2007
 */

// Fonction qui renvoie l'id du cr�neau suivant de celui qui est appel�
function creneauSuivant($creneau){
	$cherche_creneaux = array();
	$cherche_creneaux = retourne_id_creneaux();
	$ch_index = array_search($creneau, $cherche_creneaux);
	if (isset($cherche_creneaux[$ch_index+1])) {
		$reponse = $cherche_creneaux[$ch_index+1];
	}else{
		$reponse = "aucun";
	}
	return $reponse;
}

// Fonction qui renvoie l'id du cr�neau pr�c�dent de celui qui est appel�
function creneauPrecedent($creneau){
	$cherche_creneaux = array();
	$cherche_creneaux = retourne_id_creneaux();
	$ch_index = array_search($creneau, $cherche_creneaux);
	if (isset($cherche_creneaux[$ch_index-1])) {
		$reponse = $cherche_creneaux[$ch_index-1];
	}else{
		$reponse = "aucun";
	}
	return $reponse;
}

// Fonction qui renvoie le nombre de cr�neaux pr�c�dents celui qui est appel�
function nombreCreneauxPrecedent($creneau){
	// On r�cup�re l'heure du creneau appel�
	$heure_creneau_appele = mysql_fetch_array(mysql_query("SELECT heuredebut_definie_periode FROM absences_creneaux WHERE id_definie_periode = '".$creneau."'"));
	$requete = mysql_query("SELECT id_definie_periode FROM absences_creneaux WHERE heuredebut_definie_periode < '".$heure_creneau_appele["heuredebut_definie_periode"]."' AND type_creneaux != 'pause' ORDER BY heuredebut_definie_periode");
	$nbre = mysql_num_rows($requete);

	return $nbre;
}

// Fonction qui renvoie le nombre de cr�neaux qui suivent celui qui est appel�
function nombreCreneauxApres($creneau){
	// On r�cup�re l'heure du creneau appel�
	$heure_creneau_appele = mysql_fetch_array(mysql_query("SELECT heuredebut_definie_periode FROM absences_creneaux WHERE id_definie_periode = '".$creneau."'"));
	$requete = mysql_query("SELECT id_definie_periode FROM absences_creneaux WHERE heuredebut_definie_periode > '".$heure_creneau_appele["heuredebut_definie_periode"]."' AND type_creneaux != 'pause' ORDER BY heuredebut_definie_periode");
	$nbre = mysql_num_rows($requete);

	return $nbre;
}

// Fonction qui renvoie l'inverse de heuredeb_dec
function inverseHeuredeb_dec($heuredeb_dec){
	if ($heuredeb_dec == "0.5") {
		$retour = "0";
	}elseif($heuredeb_dec == "0"){
		$retour = "0.5";
	}else{
		$retour = NULL;
	}

	return $retour;
}

// Fonction qui v�rifie que le professeur n'a pas d�j� cours � ce moment l� et sur la dur�e
function verifProf($nom, $jour, $creneau, $duree, $heuredeb_dec){
	$coursoupas = "non";
	// Le nouveau cours doit �tre cr�er ce $jour, sur ce $creneau sur une $duree donn�e.
	$sql = "SELECT * FROM edt_cours, j_groupes_professeurs WHERE edt_cours.jour_semaine='".$jour."' AND edt_cours.id_definie_periode='".$creneau."' AND edt_cours.id_groupe=j_groupes_professeurs.id_groupe AND login='".$nom."' AND edt_cours.heuredeb_dec = '".$heuredeb_dec."'";
	$requete = mysql_query($sql);
	$reponse = mysql_num_rows($requete); //OR DIE('Erreur dans la reponse : '.mysql_error());
		if ($reponse >= 1) {
			$coursoupas = "oui";
		}
	// On v�rifie alors pour tous les cr�neaux pr�c�dents sans oublier les 1/2 cr�neaux
		$creneau_t = $creneau;
				$nbre_test = (nombreCreneauxPrecedent($creneau)) + 1;
		for($a = 1; $a < $nbre_test; $a++){
			$creneau = creneauPrecedent($creneau);
			$requete = mysql_query("SELECT duree FROM edt_cours, j_groupes_professeurs WHERE
				edt_cours.jour_semaine = '".$jour."' AND
				edt_cours.id_definie_periode = '".$creneau."' AND
				edt_cours.id_groupe = j_groupes_professeurs.id_groupe AND
				login = '".$nom."' AND
				edt_cours.heuredeb_dec = '".$heuredeb_dec."'") OR DIE('Erreur dans la requete n� '.$a.' du type : '.mysql_error());
			$verif = mysql_fetch_array($requete);
			// On v�rifie que la dur�e n'exc�de pas le cours appel�
				if ($verif["duree"] > (2 * $a)) {
					$coursoupas = "oui";
				}
			//echo "FOR($a) : |".$verif["duree"]."|".$heuredeb_dec."|".$creneau."<br />";
		}
	// On d�cale l'heure de d�but et on refait la m�me op�ration
	$inv_heuredeb_dec = inverseHeuredeb_dec($heuredeb_dec);
	$creneau = $creneau_t;
		for($a = 1; $a < $nbre_test; $a++){
			$creneau = creneauPrecedent($creneau);
			$requete = mysql_query("SELECT duree FROM edt_cours, j_groupes_professeurs WHERE
				edt_cours.jour_semaine = '".$jour."' AND
				edt_cours.id_definie_periode = '".$creneau."' AND
				edt_cours.id_groupe = j_groupes_professeurs.id_groupe AND
				login = '".$nom."' AND
				edt_cours.heuredeb_dec = '".$inv_heuredeb_dec."'") OR DIE('Erreur dans la requete n� '.$a.' du type : '.mysql_error());
			$verif = mysql_fetch_array($requete);
			// On v�rifie que la dur�e n'exc�de pas le cours appel�
				if ($verif["duree"] > ((2 * $a) +1)) {
					$coursoupas = "oui";
				}
			//echo "FOR2($a) : ".$verif["duree"]."|".$inv_heuredeb_dec."|".$creneau."<br />";
		}
	$creneau = $creneau_t;
// On v�rifie aussi si ce nouveau cours n'empi�te pas sur un cours suivant pour le m�me professeur
	if ($duree >= 2 AND $heuredeb_dec == "0") {
		// On v�rifie s'il n'y a pas d�j� un cours qui commence en 0.5
	$requete = mysql_query("SELECT duree FROM edt_cours, j_groupes_professeurs WHERE
				edt_cours.jour_semaine = '".$jour."' AND
				edt_cours.id_definie_periode = '".$creneau."' AND
				edt_cours.id_groupe = j_groupes_professeurs.id_groupe AND
				login = '".$nom."' AND
				edt_cours.heuredeb_dec = '0.5'")
				OR DIE('Erreur dans la requete : '.mysql_error());
	$verif1 = mysql_num_rows($requete);
	}
	// Si la dur�e est sup�rieure � 1 cr�neau (donc sup�rieure � 2)
	if ($duree >= 2){
		// Il convient alors de v�rifier le tout en fonction de cette dur�e
			$nbre_test = nombreCreneauxApres($creneau) + 1;
		for($c = 1; $c < $nbre_test; $c++){
			if ($duree >= ($c * 2)) {
				$creneau = creneauSuivant($creneau);
				$requete = mysql_query("SELECT duree FROM edt_cours, j_groupes_professeurs WHERE
					edt_cours.jour_semaine = '".$jour."' AND
					edt_cours.id_definie_periode = '".$creneau."' AND
					edt_cours.id_groupe = j_groupes_professeurs.id_groupe AND
					login = '".$nom."' AND
					edt_cours.heuredeb_dec = '".$heuredeb_dec."'")
					OR DIE('Erreur dans la requete : '.mysql_error());
				$verif = mysql_num_rows($requete);

				if ($verif == "1") {
					$coursoupas = "oui";
				}
			}
			//echo "FOR3a($c) : ".$verif."|".$heuredeb_dec."|".$creneau."<br />";
			if ($duree >= ($c * 2 + 1)) {
				$heuredeb_dec = inverseHeuredeb_dec($heuredeb_dec);
				$requete = mysql_query("SELECT duree FROM edt_cours, j_groupes_professeurs WHERE
					edt_cours.jour_semaine = '".$jour."' AND
					edt_cours.id_definie_periode = '".$creneau."' AND
					edt_cours.id_groupe = j_groupes_professeurs.id_groupe AND
					login = '".$nom."' AND
					edt_cours.heuredeb_dec = '".$heuredeb_dec."'")
					OR DIE('Erreur dans la requete : '.mysql_error());
				$verif = mysql_num_rows($requete);

				if ($verif == "1") {
					$coursoupas = "oui";
				}
			}
			//echo "FOR3b($c) : ".$verif."|".$heuredeb_dec."|".$creneau."<br />";
		}
	}


	return $coursoupas;
}

?>