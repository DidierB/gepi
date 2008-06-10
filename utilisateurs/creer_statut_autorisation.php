<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2008
 * Fichier d'inclusion � la gestion des statuts 'autre'
 *
 */
//$niveau_arbo = 1;
	// Initialisations files
	//require_once("../lib/initialisations.inc.php");

if (!$_SESSION["statut"] OR ($_SESSION["statut"] != 'autre' AND $_SESSION["statut"] != 'administrateur')) {

		//tentative_intrusion(1, "Tentative d'acc�s � un fichier sans avoir les droits n�cessaires");
		trigger_error('Vous avez proc�d� � une lecture de fichier interdit', E_USER_ERROR);

}

// droits g�n�raux et communs � tous les utilisateurs
$autorise[0] = array('/accueil.php',
				'/utilisateurs/mon_compte.php',
				'/gestion/contacter_admin.php',
				'/gestion/info_gepi.php');
// droits sp�cifiques sur les pages relatives aux droits possibles
$autorise[1] = array('/cahier_notes/visu_releve_notes.php');
$autorise[2] = array('/prepa_conseil/index3.php', '/prepa_conseil/edit_limite.php');
$autorise[3] = array('/mod_absences/gestion/voir_absences_viescolaire.php',
						'/mod_absences/gestion/bilan_absences_quotidien.php',
						'/mod_absences/gestion/bilan_absences_classe.php',
						'/mod_absences/gestion/bilan_absences_quotidien_pdf.php',
						'/mod_absences/lib/tableau.php',
						'/mod_absences/lib/export_csv.php');
$autorise[4] = array('/mod_absences/gestion/select.php',
						'/mod_absences/gestion/ajout_abs.php',
						'/mod_absences/lib/liste_absences.php');
$autorise[5] = array('/cahier_texte/see_all.php');
$autorise[6] = array('/edt_organisation/index_edt.php');
$autorise[7] = array('/tous_les_edt');

// Intitul�s pour le menu
$menu_accueil[0] = array('7');
$menu_accueil[1] = array('relev�s de notes', 'Visionner les relev�s de notes de tous les �l�ves');
$menu_accueil[2] = array('Bulletins simplifi�s', 'Visionner tous les bulletins simplifi�s de tous les �l�ves');
$menu_accueil[3] = array('Voir les absences', 'Visionner toutes les absences de l\'�tablissement');
$menu_accueil[4] = array('Saisir les absences', 'Saisir les absences de tous les �l�ves de l\'�tablissement');
$menu_accueil[5] = array('Cahiers de textes', 'Visionner tous les cahiers de textes de l\'�tablissement');
$menu_accueil[6] = array('Emploi du temps', 'Visionner les emplois du temps de tous les �l�ves');
$menu_accueil[7] = array('Emploi du temps', 'Visionner tous les emplois du temps de l\'�tablissement');

?>