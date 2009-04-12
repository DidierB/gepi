<?php
/*
 * $Id$
 *
 * Copyright 2001, 2007 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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

// On indique qu'il faut cr�e des variables non prot�g�es (voir fonction cree_variables_non_protegees())
// cela ici concerne le mot de passe
$variables_non_protegees = 'yes';
$pb_maj = '';

// Initialisations files
require_once ("../lib/initialisations.inc.php");


// Resume session
$resultat_session = $session_gepi->security_check();

if (isset ($_POST['submit'])) {
	if (isset ($_POST['login']) && isset ($_POST['no_anti_inject_password'])) {
		$_POST['login'] = strtoupper($_POST['login']);
		$md5password = md5($NON_PROTECT['password']);
		$sql = "SELECT UPPER(login) login, password, prenom, nom, statut FROM utilisateurs WHERE (login = '" . $_POST['login'] . "' and password = '" . $md5password . "' and etat != 'inactif' and statut = 'administrateur')";

		$res_user = sql_query($sql);
		$num_row = sql_count($res_user);

		if ($num_row == 1) {
			$valid = 'yes';
			$resultat_session = "1";
			$_SESSION['login'] = $_POST['login'];
			$_SESSION['statut'] = 'administrateur';
			$_SESSION['etat'] = 'actif';
			$_SESSION['start'] = mysql_result(mysql_query("SELECT now();"),0);
			$sql = "INSERT INTO log (LOGIN, START, SESSION_ID, REMOTE_ADDR, USER_AGENT, REFERER, AUTOCLOSE, END) values (
					'" . $_SESSION['login'] . "',
					'".$_SESSION['start']."',
					'" . session_id() . "',
					'" . $_SERVER['REMOTE_ADDR'] . "',
					'" . $_SERVER['HTTP_USER_AGENT'] . "',
					'" . $_SERVER['HTTP_REFERER'] . "',
					'1',
					'".$_SESSION['start']."' + interval " . getSettingValue('sessionMaxLength') . " minute
				)
			;";
			$res = sql_query($sql);

		} else {
			$message = "Identifiant ou mot de passe incorrect, ou bien vous n'�tes pas administrateur.";
		}
	}
}

function traite_requete($requete = "") {
	global $pb_maj;
	$retour = "";
	$res = mysql_query($requete);
	$erreur_no = mysql_errno();
	if (!$erreur_no) {
		$retour = "";
	} else {
		switch ($erreur_no) {
			case "1060" :
				// le champ existe d�j� : pas de probl�me
				$retour = "";
				break;
			case "1061" :
				// La cl�f existe d�j� : pas de probl�me
				$retour = "";
				break;
			case "1062" :
				// Pr�sence d'un doublon : cr�ation de la cl�f impossible
				$retour = "<font color=\"#FF0000\">Erreur (<b>non critique</b>) sur la requ�te : <i>" . $requete . "</i> (" . mysql_errno() . " : " . mysql_error() . ")</font><br />\n";
				$pb_maj = 'yes';
				break;
			case "1068" :
				// Des cl�fs existent d�j� : pas de probl�me
				$retour = "";
				break;
			case "1091" :
				// D�j� supprim� : pas de probl�me
				$retour = "";
				break;
			default :
				$retour = "<font color=\"#FF0000\">Erreur sur la requ�te : <i>" . $requete . "</i> (" . mysql_errno() . " : " . mysql_error() . ")</font><br />\n";
				$pb_maj = 'yes';
				break;
		}
	}
	return $retour;
}

$valid = isset ($_POST["valid"]) ? $_POST["valid"] : 'no';
$force_maj = isset ($_POST["force_maj"]) ? $_POST["force_maj"] : '';

// Num�ro de version effective
$version_old = getSettingValue("version");
// Num�ro de version RC effective
$versionRc_old = getSettingValue("versionRc");
// Num�ro de version Beta effective
$versionBeta_old = getSettingValue("versionBeta");

$rc_old = '';
if ($versionRc_old != '') {
	$rc_old = "-RC" . $versionRc_old;
}
$rc = '';
if ($gepiRcVersion != '') {
	$rc = "-RC" . $gepiRcVersion;
}

$beta_old = '';
if ($versionBeta_old != '') {
	$beta_old = "-beta" . $versionBeta_old;
}
$beta = '';
if ($gepiBetaVersion != '') {
	$beta = "-beta" . $gepiBetaVersion;
}


echo ('
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<meta http-equiv="Pragma" content="no-cache" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		<meta http-equiv="Expires" content="0" />
		<link rel="stylesheet" href="../style.css" type="text/css" />
		<title>Mise � jour de la base de donn�e GEPI</title>
		<link rel="shortcut icon" type="image/x-icon" href="../favicon.ico" />
		<link rel="icon" type="image/ico" href="../favicon.ico" />
		');

if(isset($style_screen_ajout)){
	// Styles param�trables depuis l'interface:
	if($style_screen_ajout=='y'){
		// La variable $style_screen_ajout se param�tre dans le /lib/global.inc
		// C'est une s�curit�... il suffit de passer la variable � 'n' pour d�sactiver ce fichier CSS et �ventuellement r�tablir un acc�s apr�s avoir impos� une couleur noire sur noire
		echo "<link rel='stylesheet' type='text/css' href='$gepiPath/style_screen_ajout.css' />\n";
	}
}

echo ('
	</head>
	<body>
');


if (($resultat_session == '0') and ($valid != 'yes')) {
	echo('
		<form action="maj.php" method="POST" style="width: 100%; margin-top: 24px; margin-bottom: 48px;">
			<div class="center">
				<H2 align="center"><?php echo "Mise � jour de la base de donn�e GEPI<br />(Acc�s administrateur)"; ?></H2>
			');

	if (isset ($message)) {
		echo ("<p align=\"center\"><font color=red>" . $message . "</font></p>");
	}
	echo('
				<fieldset style="padding-top: 8px; padding-bottom: 8px; width: 40%; margin-left: auto; margin-right: auto;">
					<legend style="font-variant: small-caps;">Identifiez-vous</legend>
					<table style="width: 100%; border: 0;" cellpadding="5" cellspacing="0" summary="Tableau d\'identification">
						<tr>
							<td style="text-align: right; width: 40%; font-variant: small-caps;"><label for="login">Identifiant</label></td>
							<td style="text-align: center; width: 60%;"><input type="text" name="login" size="16" /></td>
						</tr>
						<tr>
							<td style="text-align: right; width: 40%; font-variant: small-caps;"><label for="no_anti_inject_password">Mot de passe</label></td>
							<td style="text-align: center; width: 60%;"><input type="password" name="no_anti_inject_password" size="16" /></td>
						</tr>
					</table>
					<input type="submit" name="submit" value="Envoyer" style="font-variant: small-caps;" />
				</fieldset>
			</div>
		</form>
	</body>
</html>
');

	die();
};

if ((isset ($_SESSION['statut'])) and ($_SESSION['statut'] != 'administrateur')) {
	echo "<center><p class=grand><font color=red>Mise � jour de la base MySql de GEPI.<br />Vous n'avez pas les droits suffisants pour acc�der � cette page.</font></p></center></body></html>";
	die();
}

if (isset ($_POST['maj'])) {
	$pb_maj = '';
	// On commence la mise � jour
	$mess = "Mise � jour effectu�e.<br />(lisez attentivement le r�sultat de la mise � jour, en bas de cette page)";
	$result = '';
	$result_inter = '';

	// statuts dynamiques
	$result .= "&nbsp;->Ajout d'un champ 'autre' � la table 'droits'<br />";
	$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM droits LIKE 'autre'"));
	if ($test1 == 0) {
		$query = mysql_query("ALTER TABLE `droits` ADD `autre` VARCHAR( 1 ) NOT NULL DEFAULT 'F' AFTER `secours` ;");
		if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}
	}


	// A effectuer quelquesoit la mise � jour
	//champs de la table droits :   `id`   `administrateur`   `professeur`   `cpe`  `scolarite`   `eleve`   `responsable`   `secours`  `autre`   `description`  `statut`
	$tab_req[] = "TRUNCATE droits;";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_ooo/rapport_incident.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Mod�le Ooo : Rapport Incident', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_ooo/gerer_modeles_ooo.php', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'F', 'Mod�le Ooo : G�rer et utiliser les mod�les', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_ooo/ooo_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Mod�le Ooo : Admin', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_ooo/retenue.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Mod�le Ooo : Retenue', '');;";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_ooo/formulaire_retenue.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Mod�le Ooo : formulaire retenue', '');;";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_ooo/index.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Mod�le Ooo: Index : Index', '');;";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/update_colonne_retenue.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Affichage d une imprimante pour le responsable d un incident', '');;";
	$tab_req[] = "INSERT INTO droits VALUES ('/absences/index.php', 'F', 'F', 'V', 'F', 'F', 'F', 'V', 'F', 'Saisie des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/absences/saisie_absences.php', 'F', 'F', 'V', 'F', 'F', 'F', 'V', 'F', 'Saisie des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/accueil_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', ' ', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/accueil_modules.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/accueil.php', 'V', 'V', 'V', 'V', 'V', 'V', 'V', 'F', ' ', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/accueil_professeur.php', 'V', 'V', 'F', 'F', 'F', 'F', 'V', 'F', ' ', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/add_aid.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des AID', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/config_aid.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des AID', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/export_csv_aid.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des AID', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/help.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des AID', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des AID', '');";
	if (getSettingValue("active_version152")=="y") { // lorsque le trunk sera officiellement en 1.5.2, on supprimera ce test
		$tab_req[] = "INSERT INTO droits VALUES ('/aid/index2.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Gestion des AID (profs, �l�ves)', '');";
		$tab_req[] = "INSERT INTO droits VALUES ('/aid/modify_aid.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Gestion des AID (profs, �l�ves)', '');";
		$tab_req[] = "INSERT INTO droits VALUES ('/aid/modify_aid_new.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Gestion des AID (profs, �l�ves)', '');";
		$tab_req[] = "INSERT INTO droits VALUES ('/lib/confirm_query.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', '', '');";
	} else {
		$tab_req[] = "INSERT INTO droits VALUES ('/aid/index2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des AID (profs, �l�ves)', '');";
		$tab_req[] = "INSERT INTO droits VALUES ('/aid/modify_aid.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des AID (profs, �l�ves)', '');";
		$tab_req[] = "INSERT INTO droits VALUES ('/aid/modify_aid_new.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des AID (profs, �l�ves)', '');";
		$tab_req[] = "INSERT INTO droits VALUES ('/lib/confirm_query.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', '', '');";
	}
	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/edit.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Edition des bulletins', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/index.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Edition des bulletins', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/param_bull.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Edition des bulletins', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/verif_bulletins.php', 'F', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'V�rification du remplissage des bulletins', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/verrouillage.php', 'F', 'F', 'F', 'V', 'F', 'F', 'F', 'F', '(de)Verrouillage des p�riodes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes_admin/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des carnets de notes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/add_modif_conteneur.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Carnet de notes', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/add_modif_dev.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Carnet de notes', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/index.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Carnet de notes', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/saisie_notes.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Carnet de notes', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/toutes_notes.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Carnet de notes', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/visu_releve_notes.php', 'F', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'Visualisation et impression des relev�s de notes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_admin/admin_ct.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des cahier de texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_admin/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des cahier de texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_admin/modify_limites.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des cahier de texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_admin/modify_type_doc.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des cahier de texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte/index.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte/traite_doc.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/index.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_edition_compte_rendu.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_edition_notice_privee.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_duplication_notice.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_affichage_duplication_notice.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_deplacement_notice.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_affichage_deplacement_notice.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_suppression_notice.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_enregistrement_compte_rendu.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_enregistrement_notice_privee.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_edition_devoir.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_enregistrement_devoir.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_affichages_liste_notices.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/ajax_affichage_dernieres_notices.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/traite_doc.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte_2/exportcsv.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Cahier de texte', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/classes_ajout.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/classes_const.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/cpe_resp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Affectation des CPE aux classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/duplicate_class.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/eleve_options.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/init_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/modify_class.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/modify_nom_class.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/modify_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/periodes.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/prof_suivi.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/eleves/help.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/eleves/import_eleves_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/eleves/index.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Gestion des �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/eleves/modify_eleve.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Gestion des �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/etablissements/help.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des �tablissements', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/etablissements/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des �tablissements', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/etablissements/modify_etab.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des �tablissements', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/accueil_sauve.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Restauration, suppression et sauvegarde de la base', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/savebackup.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'T�l�chargement de sauvegardes la base', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/efface_base.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Restauration, suppression et sauvegarde de la base', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/gestion_connect.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des connexions', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/help_import.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/help.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/import_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/modify_impression.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des param�tres de la feuille de bienvenue', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/param_gen.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration g�n�rale', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/traitement_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Edition des groupes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/add_group.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Ajout de groupes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/edit_group.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Edition de groupes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/edit_eleves.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Edition des �l�ves des groupes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/edit_class.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Edition des groupes de la classe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/edit_class_grp_lot.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Affectation des mati�res aux professeurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/eleves.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/responsables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/disciplines.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/professeurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/eleves_classes.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/prof_disc_classes.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_csv/eleves_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation CSV de l\'ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/clean_tables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/init_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/responsables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/step1.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/step2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/step3.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/disciplines_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/prof_disc_classe_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/prof_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/lecture_xml_sts_emp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/init_pp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/init_dbf_sts/save_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l''ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_scribe/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation scribe de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_scribe/professeurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation scribe de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_scribe/eleves.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation scribe de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_scribe/eleves_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation scribe de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_scribe/prof_disc_classes.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation scribe de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_scribe/disciplines.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation scribe de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_lcs/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation LCS de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_lcs/eleves.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation LCS de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_lcs/professeurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation LCS de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_lcs/disciplines.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation LCS de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_lcs/affectations.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation LCS de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/clean_tables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/disciplines.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/init_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/prof_disc_classe.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/professeurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/responsables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/step1.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/step2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/initialisation/step3.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation de l\'ann?e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/matieres/help.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des mati�res', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/matieres/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des mati�res', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/matieres/matieres_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Importation des mati�res en CSV', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/matieres/matieres_categories.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Edition des cat�gories de mati�re', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/matieres/modify_matiere.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des mati�res', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/matieres/matieres_param.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/prepa_conseil/edit_limite.php', 'V', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'Edition des bulletins simplifi�s (documents de travail)', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/prepa_conseil/help.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/prepa_conseil/index1.php', 'F', 'V', 'F', 'V', 'F', 'F', 'V', 'F', 'Visualisation des notes et appr�ciations', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/prepa_conseil/index2.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation des notes par classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/prepa_conseil/index3.php', 'F', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'Edition des bulletins simplifi�s (documents de travail)', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/prepa_conseil/visu_aid.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Visualisation des notes et appr�ciations AID', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/prepa_conseil/visu_toutes_notes.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation des notes par classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/responsables/index.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Configuration et gestion des responsables �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/responsables/modify_resp.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Configuration et gestion des responsables �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/help.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/import_class_csv.php', 'F', 'V', 'F', 'V', 'F', 'F', 'V', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/import_note_app.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/index.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/saisie_aid.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Saisie des notes et appr�ciations AID', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/saisie_appreciations.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Saisie des appr�ciations du bulletins', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/saisie_avis.php', 'F', 'V', 'F', 'V', 'F', 'F', 'V', 'F', 'Saisie des avis du conseil de classe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/saisie_avis1.php', 'F', 'V', 'F', 'V', 'F', 'F', 'V', 'F', 'Saisie des avis du conseil de classe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/saisie_avis2.php', 'F', 'V', 'F', 'V', 'F', 'F', 'V', 'F', 'Saisie des avis du conseil de classe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/saisie_notes.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Saisie des notes du bulletins', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/traitement_csv.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Saisie des notes du bulletins', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/change_pwd.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/help.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/import_prof_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/impression_bienvenue.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/reset_passwords.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'R�initialisation des mots de passe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/modify_user.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/mon_compte.php', 'V', 'V', 'V', 'V', 'V', 'V', 'V', 'F', 'Gestion du compte (informations personnelles, mot de passe, ...)', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/tab_profs_matieres.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Affectation des matieres aux professeurs', '')";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/classe_classe.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/eleve_classe.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/eleve_eleve.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/evol_eleve_classe.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/evol_eleve.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/index.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/stats_classe.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/classes_param.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/fpdf/imprime_pdf.php', 'V', 'V', 'V', 'V', 'F', 'F', 'V', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/etablissements/import_etab_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration et gestion des �tablissements', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/import_app_cons.php', 'F', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Importation csv des avis du conseil de classe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/messagerie/index.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Gestion de la messagerie', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/absences/import_absences_gep.php', 'F', 'F', 'V', 'F', 'F', 'F', 'V', 'F', 'Saisie des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/absences/seq_gep_absences.php', 'F', 'F', 'V', 'F', 'F', 'F', 'V', 'F', 'Saisie des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilitaires/clean_tables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Maintenance', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/contacter_admin.php', 'V', 'V', 'V', 'V', 'V', 'V', 'V', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/gestion_absences.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/gestion_absences_liste.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/impression_absences.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/select.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/ajout_ret.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/ajout_dip.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/ajout_inf.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/ajout_abs.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/bilan_absence.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/bilan.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/lettre_aux_parents.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Gestion des absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/lib/tableau.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/lib/tableau_pdf.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/admin/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Administration du module absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/admin/admin_motifs_absences.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Administration du module absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/admin/admin_periodes_absences.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Administration du module absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/lib/liste_absences.php', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/lib/graphiques.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', '', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/professeurs/prof_ajout_abs.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Ajout des absences en classe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_trombinoscopes/trombinoscopes.php', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'Visualiser le trombinoscope', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_trombinoscopes/trombi_impr.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualiser le trombinoscope', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_trombinoscopes/trombinoscopes_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', '(des)activation du module trombinoscope', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/visu_profs_class.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation des �quipes p�dagogiques', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/popup.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation des �quipes p�dagogiques', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/index2.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation des moyennes des carnets de notes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/visu_toutes_notes2.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation des moyennes des carnets de notes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilitaires/verif_groupes.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'V�rification des incoh�rences d appartenances � des groupes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/affiche_eleve.php', 'F', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/draw_graphe.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Visualisation graphique des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/mes_listes.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Acc�s aux CSV des listes d �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/get_csv.php', 'F', 'V', 'V', 'V', 'F', 'F', 'V', 'F', 'G�n�ration de CSV �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/choix_couleurs.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Choix des couleurs des graphiques des r�sultats scolaires', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/visualisation/couleur.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Choix d une couleur pour le graphique des r�sultats scolaires', '');";
	//$tab_req[] = "INSERT INTO droits VALUES ('/gestion/config_prefs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'D�finition des pr�f�rences d utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/config_prefs.php', 'V', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'D�finition des pr�f�rences d utilisateurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilitaires/recalcul_moy_conteneurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Recalcul des moyennes des conteneurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/scol_resp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Affectation des comptes scolarit� aux classes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/lib/fiche_eleve.php', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'F', 'Fiche du suivie de l''�l�ve', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_miseajour/utilisateur/fenetre.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des mises � jour', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_miseajour/admin/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Administration du module de mise � jour', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/referencement.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'R�f�rencement de Gepi sur la base centralis�e des utilisateurs de Gepi', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/admin/admin_actions_absences.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des actions absences', '');";
	// Pour un module non pr�sent ni actif par d�faut:
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/commentaires_types.php', 'V', 'V', 'V', 'V', 'F', 'F', 'V', 'F', 'Saisie de commentaires-types', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/releve_pdf.php', 'V', 'V', 'V', 'V', 'F', 'F', 'V', 'F', 'Relev� de note au format PDF', '');";


	$tab_req[] = "INSERT INTO droits VALUES ('/impression/parametres_impression_pdf.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Impression des listes PDF; r�glage des param�tres', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/impression/impression_serie.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Impression des listes (PDF) en s�rie', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/impression/impression.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Impression rapide d une listes (PDF) ', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/impression/liste_pdf.php', 'F', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Impression des listes (PDF)', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/lecture_xml_sconet.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/init_pp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/clean_tables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/step2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/step1.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/disciplines_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/prof_disc_classe_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/lecture_xml_sts_emp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/prof_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/init_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/save_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/responsables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml/step3.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/responsables/maj_import.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Mise � jour depuis Sconet', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/responsables/conversion.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Conversion des donn�es responsables', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/create_responsable.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Cr�ation des utilisateurs au statut responsable', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/create_eleve.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Cr�ation des utilisateurs au statut �l�ve', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/edit_responsable.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Edition des utilisateurs au statut responsable', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/edit_eleve.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Edition des utilisateurs au statut �l�ve', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte/consultation.php', 'F', 'F', 'F', 'F', 'V', 'V', 'F', 'F', 'Consultation des cahiers de texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte/see_all.php', 'F', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'Consultation des cahiers de texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_texte/visu_prof_jour.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Acces_a_son_cahier_de_textes_personnel', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/droits_acces.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Param�trage des droits d acc�s', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/visu_profs_eleve.php', 'F', 'F', 'F', 'F', 'V', 'V', 'F', 'F', 'Consultation �quipe p�dagogique', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/impression_avis.php', 'F', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Impression des avis trimestrielles des conseils de classe.', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/impression/avis_pdf.php', 'F', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Impression des avis trimestrielles des conseils de classe. Module PDF', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/impression/parametres_impression_pdf_avis.php', 'F', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Impression des avis conseil classe PDF; reglage des parametres', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/password_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Export des identifiants et mots de passe en csv', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/impression/password_pdf.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Impression des identifiants et des mots de passe en PDF', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/buletin_pdf.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Bulletin scolaire au format PDF', '');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/gestion/etiquette_pdf.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Etiquette au format PDF', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/lib/export_csv.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Fichier d''exportation en csv des absences', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/gestion/statistiques.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Statistique du module vie scolaire', '1');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/lib/graph_camembert.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'graphique camembert', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/lib/graph_ligne.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'graphique camembert', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/admin/admin_horaire_ouverture.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'D�finition des horaires d''ouverture de l''�tablissement', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/admin/admin_config_semaines.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des types de semaines', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/gestion/fiche_pdf.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Fiche r�capitulatif des absences', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_absences/lib/graph_double_ligne.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'graphique absence et retard sur le m�me graphique', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/bulletin/param_bull_pdf.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'page de gestion des parametres du bulletin pdf', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/bulletin/bulletin_pdf_avec_modele_classe.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'page generant le bulletin pdf en fonction du modele affecte a la classe ', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/gestion/security_panel.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'panneau de controle des atteintes a la securite', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/gestion/security_policy.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'definition des politiques de securite', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/options_connect.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Options de connexions', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES('/mod_absences/gestion/alert_suivi.php', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'syst�me d''alerte de suivi d''�l�ve', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/gestion/efface_photos.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Suppression des photos non associ�es � des �l�ves', '');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/responsables/gerer_adr.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des adresses de responsables', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/responsables/choix_adr_existante.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Choix adresse de responsable existante', '');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/cahier_notes/export_cahier_notes.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Export CSV/ODS du cahier de notes', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/cahier_notes/import_cahier_notes.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Import CSV du cahier de notes', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/eleves/add_eleve.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Gestion des �l�ves', '');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/saisie/export_class_ods.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Export ODS des notes/appr�ciations', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/gestion_temp_dir.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des dossiers temporaires d utilisateurs', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/param_couleurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'D�finition des couleurs pour Gepi', '');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/utilisateurs/creer_remplacant.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'script de cr�ation d un rempla�ant', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/lettre_pdf.php', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'Publipostage des lettres d absences PDF', '1');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/accueil_simpl_prof.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Page d accueil simplifi�e pour les profs', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/step1.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/step2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/step3.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/responsables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/matieres.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/professeurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/prof_disc_classe_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/init_options.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/init_pp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/clean_tables.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/init_xml2/clean_temp.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation ann�e scolaire', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/conservation_annee_anterieure.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Conservation des donn�es ant�rieures', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/consultation_annee_anterieure.php', 'V', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'Consultation des donn�es d ann�es ant�rieures', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/index.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Index donn�es ant�rieures', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/popup_annee_anterieure.php', 'V', 'V', 'V', 'V', 'V', 'V', 'F', 'F', 'Consultation des donn�es ant�rieures', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Activation/d�sactivation du module donn�es ant�rieures', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/nettoyer_annee_anterieure.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Suppression de donn�es ant�rieures', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/archivage_aid.php', 'V', 'F', 'F', 'F', 'F', 'F','F', 'F', 'Fiches projets', '1');";

	$tab_req[] = "INSERT INTO droits VALUES ('/responsables/maj_import1.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Mise � jour depuis Sconet', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/responsables/maj_import2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Mise � jour depuis Sconet', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/mod_annees_anterieures/corriger_ine.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Correction d INE dans la table annees_anterieures', '');";
	$tab_req[] = "INSERT INTO `droits` VALUES ('/mod_annees_anterieures/liste_eleves_ajax.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Recherche d �l�ves', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/lib/graph_double_ligne_fiche.php', 'V', 'V', 'V', 'F', 'F', 'F', 'V', 'F', 'Graphique de la fiche �l�ve', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/admin/admin_config_calendrier.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'D�finir les diff�rentes p�riodes', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/index_edt.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Gestion des emplois du temps', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_initialiser.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation des emplois du temps', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/effacer_cours.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Effacer un cours des emplois du temps', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_calendrier.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation du calendrier', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/ajouter_salle.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Gestion des salles', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_parametrer.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'G�rer les param�tres EdT', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/voir_groupe.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Voir les groupes de Gepi', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/modif_edt_tempo.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Modification temporaire des EdT', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_init_xml.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Initialisation EdT par xml', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_init_csv.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'initialisation EdT par csv', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_init_csv2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'initialisation EdT par un autre csv', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_init_texte.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'initialisation EdT par un fichier texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_init_concordance.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'initialisation EdT par un fichier texte', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_init_concordance2.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'initialisation EdT par un autre fichier csv', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/modifier_cours.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Modifier un cours', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/modifier_cours_popup.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'Modifier un cours', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'R�gler le module emploi du temps', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_eleve.php', 'F', 'F', 'F', 'F', 'V', 'V', 'F', 'F', 'R�gler le module emploi du temps', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/edt_param_couleurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'R�gler les couleurs des mati�res (EdT)', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_organisation/ajax_edtcouleurs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Modifier les couleurs des affichages des emplois du temps.', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/creer_statut.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Ajouter et g�rer des statuts personnalis�s', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/utilisateurs/creer_statut_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F','F', 'Autoriser la cr�ation des statuts personnalis�s', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_gestion_gr/edt_aff_gr.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F','F', 'G�rer les groupes du module EdT', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_gestion_gr/edt_ajax_win.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F','F', 'G�rer les groupes du module EdT', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_gestion_gr/edt_liste_eleves.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F','F', 'G�rer les groupes du module EdT', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_gestion_gr/edt_liste_profs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F','F', 'G�rer les groupes du module EdT', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/edt_gestion_gr/edt_win.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F','F', 'G�rer les groupes du module EdT', '');";


	$tab_req[] = "INSERT INTO droits VALUES ('/absences/import_absences_sconet.php', 'F', 'F', 'V', 'F', 'F', 'F', 'V', 'F', 'Saisie des absences', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/export_modele_pdf.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'exportation en csv des modeles de bulletin pdf', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/absences/consulter_absences.php', 'F', 'F', 'V', 'F', 'F', 'F', 'V', 'F', 'Consulter les absences', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/professeurs/bilan_absences_professeur.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Bilan des absences pour chaque professeur', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/professeurs/bilan_absences_classe.php', 'F', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'Bilan des absences pour chaque professeur', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/voir_absences_viescolaire.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Consulter les absences du jour', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/bilan_absences_quotidien.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Consulter les absences par cr�neau', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/bilan_absences_quotidien_pdf.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Consulter les absences par cr�neau en pdf', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/bilan_absences_classe.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Consulter les absences par classe', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/gestion/bilan_repas_quotidien.php', 'F', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Consulter l inscription aux repas', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/absences.php', 'F', 'F', 'F', 'F', 'F', 'V', 'F', 'F', 'Consulter les absences de son enfant', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_absences/admin/interface_abs.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Param�trer les interfaces des professeurs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/absences/import_absences_gepi.php', 'F', 'F', 'V', 'V', 'F', 'F', 'V', 'F', 'Page d''importation des absences de gepi mod_absences', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/ajax_appreciations.php', 'F', 'V', 'F', 'F', 'F', 'F', 'V', 'F', 'Sauvegarde des appr�ciations du bulletins', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/lib/change_mode_header.php', 'V', 'V', 'V', 'V', 'V', 'V', 'V', 'F', 'Page AJAX pour changer la variable cacher_header', '1');";

	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/recopie_moyennes.php', 'F', 'F', 'F', 'F', 'F', 'F', 'V', 'F', 'Recopie des moyennes', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/groupes/fusion_group.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Fusionner des groupes', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/gestion/security_panel_archives.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'page archive du panneau de s�curit�', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/responsables/corrige_ele_id.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Correction des ELE_ID d apres Sconet', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/mod_inscription/inscription_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', '(De)activation du module inscription', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_inscription/inscription_index.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'acc�s au module configuration', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_inscription/inscription_config.php', 'V', 'F', 'F', 'V', 'F', 'F','F',  'F', 'Configuration du module inscription', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/mod_inscription/help.php', 'V', 'F', 'F', 'V', 'F', 'F','F', 'F', 'Configuration du module inscription', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/aid/index_fiches.php', 'V', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'Outils compl�mentaires de gestion des AIDs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/visu_fiches.php', 'V', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'Outils compl�mentaires de gestion des AIDs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/modif_fiches.php', 'V', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'Outils compl�mentaires de gestion des AIDs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/config_aid_fiches_projet.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des outils compl�mentaires de gestion des AIDs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/config_aid_matieres.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des outils compl�mentaires de gestion des AIDs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/config_aid_productions.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Configuration des outils compl�mentaires de gestion des AIDs', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/acces_appreciations.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Configuration de la restriction d acc�s aux appr�ciations pour les �l�ves et responsables', '');";


	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/rouen/fiches_brevet.php','V','F','F','F','F','F','F','F', 'Acc�s aux fiches brevet','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/poitiers/fiches_brevet.php','V','F','F','F','F','F','F','F', 'Acc�s aux fiches brevet','');";


	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/notanet_admin.php','V','F','F','F','F','F','F','F', 'Gestion du module NOTANET','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/index.php','V','V','F','F','F','F','F','F', 'Notanet: Accueil','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/extract_moy.php','V','F','F','F','F','F','F','F', 'Notanet: Extraction des moyennes','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/corrige_extract_moy.php','V','F','F','F','F','F','F','F', 'Notanet: Extraction des moyennes','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/select_eleves.php','V','F','F','F','F','F','F','F', 'Notanet: Associations �l�ves/type de brevet','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/select_matieres.php','V','F','F','F','F','F','F','F', 'Notanet: Associations mati�res/type de brevet','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/saisie_app.php','F','V','F','F','F','F','F','F', 'Notanet: Saisie des appr�ciations','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/generer_csv.php','V','F','F','F','F','F','F','F', 'Notanet: G�n�ration de CSV','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/choix_generation_csv.php','V','F','F','F','F','F','F','F', 'Notanet: G�n�ration de CSV','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/verrouillage_saisie_app.php','V','F','F','F','F','F','F','F', 'Notanet: (D�)Verrouillage des saisies','');";

	$tab_req[] = "INSERT INTO droits VALUES ('/bulletin/bull_index.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Edition des bulletins', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/visu_releve_notes_bis.php', 'V', 'V', 'V', 'V', 'V', 'V', 'V','F', 'Relev� de notes', '1');";
	$tab_req[] = "INSERT INTO droits VALUES ('/cahier_notes/param_releve_html.php', 'V', 'V', 'F', 'V', 'F', 'F', 'F','F', 'Param�tres du relev� de notes', '1');";

	$tab_req[] = "INSERT INTO droits VALUES ('/classes/changement_eleve_classe.php', 'V', 'F', 'F', 'V', 'F', 'F', 'F','F', 'Changement de classe pour un �l�ve', '1');";

	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/saisie_avis.php','V','F','F','F','F','F','F','F','Notanet: Saisie avis chef etablissement','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/poitiers/param_fiche_brevet.php','V','F','F','F','F','F','F','F','Notanet: Param�tres d impression','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_notanet/saisie_b2i_a2.php','V','F','F','F','F','F','F','F','Notanet: Saisie socles B2i et A2','');";

	$tab_req[] = "INSERT INTO droits VALUES ( '/eleves/liste_eleves.php', 'V', 'V', 'V', 'V', 'F', 'F', 'V', 'F', 'Lister des �l�ves', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/eleves/visu_eleve.php', 'V', 'V', 'V', 'V', 'F', 'F', 'V', 'F', 'Consultation_d_un_eleve', '');";

	$tab_req[] = "INSERT INTO droits VALUES ( '/cahier_texte_admin/rss_cdt_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'G�rer les flux rss du cdt', '');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/matieres/suppr_matiere.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Suppression d une matiere', '');";

	$tab_req[] = "INSERT INTO droits VALUES ( '/eleves/import_bull_eleve.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Importation bulletin �l�ve', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/eleves/export_bull_eleve.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Exportation bulletin �l�ve', '');";

	$tab_req[] = "INSERT INTO `droits`  VALUES ('/cahier_texte_admin/visa_ct.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Page de signature des cahiers de texte', '');";

	$tab_req[] = "INSERT INTO droits VALUES('/saisie/saisie_cmnt_type_prof.php','F','V','F','F','F','F','F','F', 'Saisie appr�ciations-types pour les profs','');";

	$tab_req[] = "INSERT INTO droits VALUES('/mod_ent/index.php','V','F','F','F','F','F','F','F', 'Gestion de l int�gration de GEPI dans un ENT','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_ent/gestion_ent_eleves.php','V','F','F','F','F','F','F','F', 'Gestion de l int�gration de GEPI dans un ENT','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_ent/gestion_ent_profs.php','V','F','F','F','F','F','F','F', 'Gestion de l int�gration de GEPI dans un ENT','');";
	$tab_req[] = "INSERT INTO droits VALUES('/mod_ent/miseajour_ent_eleves.php','V','F','F','F','F','F','F','F', 'Gestion de l int�gration de GEPI dans un ENT','');";

	// Module discipline:
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/traiter_incident.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Traitement', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/saisie_incident.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Saisie incident', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/occupation_lieu_heure.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Occupation lieu', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/liste_sanctions_jour.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Liste', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/index.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Index', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/incidents_sans_protagonistes.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Incidents sans protagonistes', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/edt_eleve.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: EDT �l�ve', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/ajout_sanction.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Ajout sanction', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/saisie_sanction.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Saisie sanction', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/definir_roles.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Discipline: D�finition des r�les', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/definir_mesures.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Discipline: D�finition des mesures', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/sauve_role.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Svg r�le incident', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/definir_autres_sanctions.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Discipline: D�finir types sanctions', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/liste_retenues_jour.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Liste des retenues du jour', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/avertir_famille.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Avertir famille incident', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/avertir_famille_html.php', 'V', 'F', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Avertir famille incident', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/sauve_famille_avertie.php', 'V', 'V', 'V', 'V', 'F', 'F', 'F', 'F', 'Discipline: Svg famille avertie', '');";
	$tab_req[] = "INSERT INTO droits VALUES ( '/mod_discipline/discipline_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Discipline: Activation/desactivation du module', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/aid/annees_anterieures_accueil.php', 'V', 'V', 'V', 'F', 'V', 'F', 'F', 'F', 'Configuration des AID', '');";

	$tab_req[] = "INSERT INTO droits VALUES ('/saisie/saisie_secours_eleve.php', 'F', 'F', 'F', 'F', 'F', 'F', 'V', 'F', 'Saisie notes/appr�ciations pour un �l�ve en compte secours', '');";
	$tab_req[] = "INSERT INTO droits VALUES ('/classes/classes_ajax_lib.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Page appel�e via ajax.', '');";

	$tab_req[] = "INSERT INTO `droits` VALUES ('/responsables/dedoublonnage_adresses.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'D�doublonnage des adresses responsables', '');";



    $tab_req[] = "INSERT INTO droits VALUES ( '/mod_ects/ects_admin.php', 'V', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'Module ECTS : Admin', '');";
    $tab_req[] = "INSERT INTO droits VALUES ( '/mod_ects/index_saisie.php', 'F', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Module ECTS : Accueil saisie', '');";
    $tab_req[] = "INSERT INTO droits VALUES ( '/mod_ects/saisie_ects.php', 'F', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'Module ECTS : Saisie', '');";

	//$tab_req[] = "";


	$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM droits LIKE 'responsable'"));
	if ($test1 == 1) {
		foreach ($tab_req as $key => $value) {
			$result .= traite_requete($value);
		}
	} else {
		$droits_requests = $tab_req;
		$tab_req = array ();
	}

	if (($force_maj == 'yes') or (quelle_maj("1.3.1"))) {
		$result .= "<b>Mise � jour jusqu'� la version 1.3.0 :</b><br />";
		$tab_req = array ();
		$tab_req[] = "ALTER IGNORE TABLE utilisateurs ADD change_mdp CHAR( 1 ) DEFAULT 'n' NOT NULL ;";
		$tab_req[] = "ALTER TABLE temp_gep_import ADD ELENOET VARCHAR( 40 ) NOT NULL AFTER ELEDATNAIS ;";
		$tab_req[] = "ALTER TABLE temp_gep_import ADD ERENO VARCHAR( 40 ) NOT NULL AFTER ELENOET;";
		$tab_req[] = "ALTER TABLE eleves ADD elenoet VARCHAR( 10 ) NOT NULL ;";
		$tab_req[] = "ALTER TABLE eleves ADD ereno VARCHAR( 10 ) NOT NULL ;";
		$tab_req[] = "CREATE TABLE IF NOT EXISTS responsables (ereno VARCHAR( 10 ) NOT NULL , nom1 VARCHAR( 20 ) NOT NULL , prenom1 VARCHAR( 20 ) NOT NULL ,adr1 VARCHAR( 100 ) NOT NULL , adr1_comp VARCHAR( 100 ) NOT NULL , commune1 VARCHAR( 50 ) NOT NULL ,cp1 VARCHAR( 6 ) NOT NULL ,nom2 VARCHAR( 20 ) NOT NULL, prenom2 VARCHAR( 20 ) NOT NULL ,adr2 VARCHAR( 100 ) NOT NULL , adr2_comp VARCHAR( 100 ) NOT NULL, commune2 VARCHAR( 50 ) NOT NULL ,cp2 VARCHAR( 6 ) NOT NULL);";


		foreach ($tab_req as $key => $value) {
			$result_inter .= traite_requete($value);
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'duree_conservation_logs'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('duree_conservation_logs', '365');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'longmin_pwd'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('longmin_pwd', '5');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'gepi_prof_suivi'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('gepi_prof_suivi', 'professeur principal');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiRubConseilProf'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiRubConseilProf', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiRubConseilScol'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiRubConseilScol', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_ecart_entete'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_ecart_entete', '0');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'page_garde_imprime'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('page_garde_imprime', 'no'); ");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'page_garde_texte'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('page_garde_texte', 'Madame, Monsieur,\r\n\r\nVeuillez trouvez ci-joint le bulletin scolaire de votre enfant. Nous vous rappelons que la journ�e __Portes ouvertes__ du Lyc�e aura lieu samedi 20 mai entre 10 h et 17 h.\r\n\r\nVeuillez agr�er, Madame, Monsieur, l\'expression de mes meilleurs sentiments.\r\n\r\n\r\n|<p style=\"text-align: right;\">Le proviseur</style>');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'page_garde_padding_top'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('page_garde_padding_top', '4');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'page_garde_padding_left'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('page_garde_padding_left', '11');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'page_garde_padding_text'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('page_garde_padding_text', '6');");

		$req = sql_query1("SELECT VALUE FROM setting WHERE NAME='version'");
		if ($req == -1) {
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('version', '$gepiVersion');");
		} else {
			$result_inter .= traite_requete("UPDATE setting SET VALUE='$gepiVersion' WHERE NAME='version';");
		}
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';
	}

	// version Gepi_1.3.1
	if (($force_maj == 'yes') or (quelle_maj("1.3.1"))) {
		$result .= "<b><br />Mise � jour vers la version 1.3.1 : </b><br />";
		$tab_req = array ();
		$tab_req[] = "ALTER TABLE utilisateurs ADD civilite CHAR( 5 ) NOT NULL AFTER prenom;";
		$tab_req[] = "ALTER TABLE classes ADD format_nom CHAR( 5 ) NOT NULL ;";
		$tab_req[] = "ALTER TABLE matieres ADD priority SMALLINT NOT NULL ;";
		$tab_req[] = "ALTER TABLE classes ADD display_rang CHAR( 1 ) DEFAULT 'n' NOT NULL ;";

		foreach ($tab_req as $key => $value) {
			$result_inter .= traite_requete($value);
		}
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_espace_avis'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_espace_avis', '5');");
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';
	}
	// version Gepi_1.3.2
	if (($force_maj == 'yes') or (quelle_maj("1.3.2"))) {
		$result .= "<b><br />Mise � jour vers la version 1.3.2 :</b><br />";

		//Changement des priorit�s d'affichage
		$req = sql_query1("SELECT VALUE FROM setting WHERE NAME='change_ordre_aff_matieres'");
		if ($req == -1) {
			// On passe de l'affichage selon le "poids" � l'affichage selon la "priorit�"
			$req = mysql_query("ALTER TABLE j_classes_matieres_professeurs ADD temp CHAR( 1 ) DEFAULT 'n' NOT NULL ;");
			$req = mysql_query("ALTER TABLE matieres ADD temp CHAR( 1 ) DEFAULT 'n' NOT NULL ;");
			$l = 11;
			while ($l < 51) {
				$new = 61 - $l;
				$maj = mysql_query("UPDATE j_classes_matieres_professeurs set priorite='" . $new . "', temp='y'
				where (
				temp = 'n' and
				priorite='" . $l . "'
				)
				");

				$maj = mysql_query("UPDATE matieres set priority='" . $new . "', temp='y'
				where (
				temp = 'n' and
				priority='" . $l . "'
				)
				");

				$l++;
			}
			$result_inter .= traite_requete("UPDATE j_classes_matieres_professeurs set priorite='50' where priorite='0'");
			$result_inter .= traite_requete("UPDATE matieres set priority='50' where priority='0'");
			$result_inter .= traite_requete("ALTER TABLE j_classes_matieres_professeurs DROP temp;");
			$result_inter .= traite_requete("ALTER TABLE matieres DROP temp;");

			// On re-num�rote � partir de 1 j_classes_matieres_professeurs
			$l = 11;
			$new = 11;
			while ($l < 51) {
				$test_query = mysql_query("SELECT priorite from j_classes_matieres_professeurs where priorite = '" . $l . "'");
				$result_test = mysql_num_rows($test_query);
				if ($result_test != 0) {
					$maj = mysql_query("UPDATE j_classes_matieres_professeurs set priorite='" . $new . "'
						where (
						priorite='" . $l . "'
						)
					   ");
					$new++;
				}
				$l++;
			}
			// On re-num�rote � partir de 1 matieres
			$l = 11;
			$new = 11;
			while ($l < 51) {
				$test_query = mysql_query("SELECT priority from matieres where priority = '" . $l . "'");
				$result_test = mysql_num_rows($test_query);
				if ($result_test != 0) {
					$maj = mysql_query("UPDATE matieres set priority='" . $new . "'
						where (
						priority='" . $l . "'
						)
					   ");
					$new++;
				}
				$l++;
			}
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('change_ordre_aff_matieres', 'ok');");
			$mess .= "<br /><br />Pour tenir compte d'un changement sur l'ordre d'affichage des mati�res, les priorit�s d'affichage ont �t� modifi�es. Il est conseill� de v�rifier que l'ordre d'affichage des mati�res est toujours conforme � vos r�glages initiaux";
		}
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';
	}
	//
	// version Gepi_1.3.3
	//
	if (($force_maj == 'yes') or (quelle_maj("1.3.3"))) {
		$result .= "<b><br />Mise � jour vers la version 1.3.3 :</b><br />";
		$result_inter .= traite_requete("CREATE TABLE IF NOT EXISTS messages (id int(11) NOT NULL auto_increment, texte text NOT NULL, date_debut int(11) NOT NULL default '0', date_fin int(11) NOT NULL default '0', auteur varchar(20) NOT NULL default '', destinataires varchar(10) NOT NULL default '', PRIMARY KEY  (id) );");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'disable_login'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('disable_login', 'no');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';
	}
	//
	// version Gepi_1.3.4
	//
	if (($force_maj == 'yes') or (quelle_maj("1.3.4"))) {
		$result .= "<b><br />Mise � jour vers la version 1.3.4 :</b><br />";
		$result .= "&nbsp;->Premi�re �tape de mise � jour : <br />";

		$query = mysql_query("CREATE TABLE IF NOT EXISTS j_eleves_cpe (e_login varchar(50) NOT NULL default '', cpe_login varchar(50) NOT NULL default '', PRIMARY KEY  (e_login,cpe_login)) TYPE=MyISAM;");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_formule_bas'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_formule_bas', 'Bulletin � conserver pr�cieusement. Aucun duplicata ne sera d�livr�. - GEPI : solution libre de gestion et de suivi des r�sultats scolaires.');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'delai_devoirs'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('delai_devoirs', '7');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiProfImprBul'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiProfImprBul', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiProfImprBulSettings'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiProfImprBulSettings', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiScolImprBulSettings'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiScolImprBulSettings', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAdminImprBulSettings'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAdminImprBulSettings', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveScol'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveScol','yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveProfP'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveProfP', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveProf'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveProf', 'no');");

		$result_inter .= traite_requete("CREATE TABLE IF NOT EXISTS ct_devoirs_entry (id_ct int(11) NOT NULL auto_increment, id_matiere varchar(32) NOT NULL default '', id_classe int(11) NOT NULL default '0', date_ct int(11) NOT NULL default '0', id_login varchar(32) NOT NULL default '', contenu text NOT NULL, KEY id_ct (id_ct));");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Deuxi�me �tape : optimisation des tables : <br />";

		$tab_req = array ();
		$tab_req[] = "ALTER TABLE matieres_notes ADD rang SMALLINT NOT NULL ;";
		$tab_req[] = "ALTER TABLE j_eleves_classes ADD rang SMALLINT DEFAULT '0' NOT NULL;";
		$tab_req[] = "CREATE TABLE IF NOT EXISTS absences_gep (id_seq char(2) NOT NULL default '', type char(1) NOT NULL default '');";
		$tab_req[] = "ALTER TABLE absences CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE absences CHANGE nb_absences nb_absences CHAR(3) NOT NULL;";
		$tab_req[] = "ALTER TABLE absences CHANGE non_justifie non_justifie CHAR(3) NOT NULL;";
		$tab_req[] = "ALTER TABLE absences CHANGE nb_retards nb_retards CHAR(3) NOT NULL;";
		$tab_req[] = "ALTER TABLE aid CHANGE id id VARCHAR( 100 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE aid_appreciations CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE aid_appreciations CHANGE id_aid id_aid VARCHAR( 100 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE aid_appreciations DROP assiduite , DROP investissement , DROP participation_creatrice;";
		$tab_req[] = "ALTER TABLE aid_appreciations CHANGE periode periode INT NOT NULL;";
		$tab_req[] = "ALTER TABLE aid_appreciations CHANGE statut statut VARCHAR( 10 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE avis_conseil_classe CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE avis_conseil_classe CHANGE periode periode INT NOT NULL;";
		$tab_req[] = "ALTER TABLE avis_conseil_classe CHANGE avis avis TEXT NOT NULL;";
		$tab_req[] = "ALTER TABLE avis_conseil_classe CHANGE statut statut VARCHAR( 10 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE classes CHANGE classe classe VARCHAR( 100 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE cn_notes_conteneurs CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE cn_notes_devoirs CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE id id VARCHAR( 200 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE administrateur administrateur VARCHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE professeur professeur VARCHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE cpe cpe VARCHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE scolarite scolarite VARCHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE eleve eleve VARCHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE secours secours VARCHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE droits CHANGE description description VARCHAR( 255 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE eleves CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE `eleves` CHANGE `nom` `nom` VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE `eleves` CHANGE `prenom` `prenom` VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE `eleves` CHANGE `sexe` `sexe` VARCHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_aid_eleves CHANGE id_aid id_aid VARCHAR( 100 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_aid_utilisateurs CHANGE id_aid id_aid VARCHAR( 100 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_aid_utilisateurs CHANGE id_utilisateur id_utilisateur VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_eleves_classes CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_eleves_etablissements CHANGE id_eleve id_eleve VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_eleves_professeurs CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_eleves_professeurs CHANGE professeur professeur VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_eleves_regime CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_eleves_regime CHANGE doublant doublant CHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_eleves_regime CHANGE regime regime CHAR( 5 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE j_professeurs_matieres CHANGE id_professeur id_professeur VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE log CHANGE LOGIN LOGIN VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE matieres CHANGE matiere matiere VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE matieres CHANGE nom_complet nom_complet VARCHAR( 200 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE matieres_appreciations CHANGE login login VARCHAR( 50 ) NOT NULL;";
		#$tab_req[] = "ALTER TABLE matieres_appreciations CHANGE matiere matiere VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE matieres_appreciations CHANGE periode periode INT NOT NULL;";
		$tab_req[] = "ALTER TABLE matieres_notes CHANGE login login VARCHAR( 50 ) NOT NULL;";
		#$tab_req[] = "ALTER TABLE matieres_notes CHANGE matiere matiere VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE matieres_notes CHANGE periode periode INT NOT NULL;";
		$tab_req[] = "ALTER TABLE matieres_notes CHANGE statut statut VARCHAR( 10 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE messages CHANGE auteur auteur VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE periodes CHANGE nom_periode nom_periode VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE periodes CHANGE num_periode num_periode INT NOT NULL;";
		$tab_req[] = "ALTER TABLE periodes CHANGE verouiller verouiller CHAR( 1 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE periodes CHANGE id_classe id_classe INT DEFAULT '0' NOT NULL;";
		$tab_req[] = "ALTER TABLE utilisateurs CHANGE login login VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE utilisateurs CHANGE nom nom VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE utilisateurs CHANGE prenom prenom VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE utilisateurs CHANGE password password CHAR( 32 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE utilisateurs CHANGE email email VARCHAR( 50 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE utilisateurs CHANGE etat etat VARCHAR( 20 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE utilisateurs CHANGE statut statut VARCHAR( 20 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE `cn_conteneurs` CHANGE `description` `description` VARCHAR( 128 ) NOT NULL;";
		$tab_req[] = "ALTER TABLE `cn_devoirs` CHANGE `description` `description` VARCHAR( 128 ) NOT NULL;";

		foreach ($tab_req as $key => $value) {
			$result_inter .= traite_requete($value);
		}
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		// Ajout des clefs primaires
		$result .= "&nbsp;->Troisi�me �tape : cr�ation des clefs primaires : <br />";
		$tab_req = array ();
		$tab_req[] = "ALTER TABLE absences ADD PRIMARY KEY ( login , periode );";
		$tab_req[] = "ALTER TABLE absences_gep ADD PRIMARY KEY ( id_seq );";
		$tab_req[] = "ALTER TABLE aid ADD PRIMARY KEY ( id );";
		$tab_req[] = "ALTER TABLE aid_appreciations ADD PRIMARY KEY ( login , id_aid , periode );";
		$tab_req[] = "ALTER TABLE avis_conseil_classe ADD PRIMARY KEY ( login , periode );";
		$tab_req[] = "ALTER TABLE droits ADD PRIMARY KEY ( id );";
		$tab_req[] = "ALTER TABLE cn_notes_conteneurs ADD PRIMARY KEY ( login , id_conteneur );";
		$tab_req[] = "ALTER TABLE cn_notes_devoirs ADD PRIMARY KEY ( login , id_devoir );";
		$tab_req[] = "ALTER TABLE eleves ADD PRIMARY KEY ( login );";
		$tab_req[] = "ALTER TABLE aid_config ADD PRIMARY KEY ( indice_aid );";
		$tab_req[] = "ALTER TABLE j_aid_eleves ADD PRIMARY KEY ( id_aid , login );";
		$tab_req[] = "ALTER TABLE j_aid_utilisateurs ADD PRIMARY KEY ( id_aid , id_utilisateur );";
		$tab_req[] = "ALTER TABLE j_eleves_classes ADD PRIMARY KEY ( login , id_classe , periode );";
		$tab_req[] = "ALTER TABLE j_eleves_etablissements ADD PRIMARY KEY ( id_eleve , id_etablissement );";
		$tab_req[] = "ALTER TABLE j_eleves_professeurs ADD PRIMARY KEY ( login , professeur , id_classe );";
		$tab_req[] = "ALTER TABLE j_eleves_regime ADD PRIMARY KEY ( login );";
		$tab_req[] = "ALTER TABLE j_professeurs_matieres ADD PRIMARY KEY ( id_professeur , id_matiere );";
		$tab_req[] = "ALTER TABLE matieres ADD PRIMARY KEY ( matiere );";
		$tab_req[] = "ALTER TABLE matieres_appreciations ADD PRIMARY KEY ( login , matiere , periode );";
		$tab_req[] = "ALTER TABLE matieres_notes ADD PRIMARY KEY ( login , matiere , periode );";
		$tab_req[] = "ALTER TABLE periodes ADD PRIMARY KEY ( num_periode , id_classe );";
		$tab_req[] = "ALTER TABLE responsables ADD PRIMARY KEY ( ereno );";
		$tab_req[] = "ALTER TABLE utilisateurs ADD PRIMARY KEY ( login );";

		foreach ($tab_req as $key => $value) {
			$result_inter .= traite_requete($value);
		}
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
			$result .= "<br /><b>Remarque : </b> Afin de r�gler le probl�me ci-dessus de cr�ation des clefs primaires,
		vous pouvez lancer la <b><a href='./clean_tables.php'>proc�dure de nettoyage des tables de liaison</a></b> puis recommencer la mise � jour en <b>for�ant la proc�dure</b>.";

		}
		$result_inter = '';
		$result .= "<br /><b>Remarque :</b> la version 1.3.4 int�gre une fonctionalit� d'attribution de CPE responsable du suivi pour chaque �l�ve. Vous devez donc attribuer un CPE aux �l�ves. Tant que vous n'aurez pas effectu� cette op�ration, les CPE n'auront pas acc�s � leurs outils respectifs. Rendez-vous dans /Gestion des bases/Gestion des classes/ et utilisez le lien 'Param�trage rapide CPE responsable' pour attribuer automatiquement les CPE aux �l�ves des classes que vous s�lectionnerez.";
	}
	//
	// version Gepi_1.4.0
	//

	if (($force_maj == 'yes') or (quelle_maj("1.4.0"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.4.0 :</b><br />";

		$result .= "&nbsp;->Cr�ation de la table absences_eleves <br />";
		$query = mysql_query("CREATE TABLE IF NOT EXISTS `absences_eleves` (`id_absence_eleve` int(11) NOT NULL auto_increment, `type_absence_eleve` char(1) NOT NULL default '', `eleve_absence_eleve` varchar(25) NOT NULL default '0', `justify_absence_eleve` char(3) NOT NULL default '', `info_justify_absence_eleve` text NOT NULL, `motif_absence_eleve` varchar(4) NOT NULL default '', `info_absence_eleve` text NOT NULL, `d_date_absence_eleve` date NOT NULL default '0000-00-00', `a_date_absence_eleve` date default NULL, `d_heure_absence_eleve` time default NULL, `a_heure_absence_eleve` time default NULL, `saisie_absence_eleve` varchar(50) NOT NULL default '', PRIMARY KEY  (`id_absence_eleve`)) TYPE=MyISAM AUTO_INCREMENT=57;");
		if ($query)
		$result .= "<font color=\"green\">Ok !</font><br />";

		$result .= "&nbsp;->Tentative d'ajout d'un champ dans la table absences_eleves (si �a ne marche pas, c'est simplement que la table existe d�j�...) <br />";
		$queryb = mysql_query("ALTER TABLE `absences_eleves` ADD `saisie_absence_eleve` varchar(50) NOT NULL default ''");
		if ($queryb)
		$result .= "<font color=\"green\">Ok !</font><br />";

		$result .= "&nbsp;->Changement de la structure de la table 'classes'<br />";
		$querybis = mysql_query("ALTER TABLE `classes` ADD `display_address` CHAR( 1 ) DEFAULT 'n' NOT NULL , ADD `display_coef` CHAR( 1 ) DEFAULT 'y' NOT NULL");
		if ($querybis)
		$result .= "<font color=\"green\">Ok !</font><br />";

		$result .= "&nbsp;->Insertion de nouveaux param�tres<br />";

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'gepiSchoolTel'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO `setting` VALUES ('gepiSchoolTel', '00 00 00 00 00');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'gepiSchoolFax'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO `setting` VALUES ('gepiSchoolFax', '00 00 00 00 00');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'gepiSchoolEmail'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO `setting` VALUES ('gepiSchoolEmail', 'ce.XXXXXXXX@ac-xxxxx.fr');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_right'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('addressblock_padding_right', '2');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_top'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('addressblock_padding_top', '4');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_text'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('addressblock_padding_text', '1');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_length'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('addressblock_length', '6');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveCpe'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveCpe','no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'col_boite_largeur'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('col_boite_largeur','120');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_mention_doublant'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('bull_mention_doublant','no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_numero'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('bull_affiche_numero','no');");

		$result .= "&nbsp;->Cr�ation de la table suivi_eleve_cpe <br />";
		$query = mysql_query("CREATE TABLE IF NOT EXISTS `suivi_eleve_cpe` (`id_suivi_eleve_cpe` int(11) NOT NULL auto_increment, `eleve_suivi_eleve_cpe` varchar(30) NOT NULL default '', `date_suivi_eleve_cpe` date NOT NULL default '0000-00-00', `komenti_suivi_eleve_cpe` text NOT NULL, PRIMARY KEY  (`id_suivi_eleve_cpe`)) TYPE=MyISAM;");
		if ($query)
		$result .= "<font color=\"green\">Ok !</font><br />";
	}

	if (($force_maj == 'yes') or (quelle_maj("1.4.1"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.4.1 :</b><br />";

		$result .= "&nbsp;->Tentative de modification du champ AUTOCLOSE dans la table log.<br />";
		$result_inter = traite_requete("ALTER TABLE `log` CHANGE `AUTOCLOSE` `AUTOCLOSE` ENUM( '0', '1', '2' ) DEFAULT '0' NOT NULL");
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}

		$result .= "&nbsp;->Tentative d'ajout d'un champ dans la table utilisateurs.<br />";
		$result_inter = traite_requete("ALTER TABLE `utilisateurs` ADD `date_verrouillage` datetime NOT NULL default '2006-01-01 00:00:00'");
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = "";
		$result .= "&nbsp;->Insertion de nouveaux param�tres dans la table setting<br />";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'temps_compte_verrouille'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO `setting` VALUES ('temps_compte_verrouille', '30');");
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'nombre_tentatives_connexion'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO `setting` VALUES ('nombre_tentatives_connexion', '10');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}

		$result .= "&nbsp;->On force tous les utilisateurs � mettre � jour leur mot de passe (s�curit�)<br />";

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'version'");
		$res_test = mysql_result($req_test, "0");

		if ($res_test < "1.4.1")
		$result_inter .= traite_requete("UPDATE utilisateurs SET change_mdp='y';");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}

	}

	if (($force_maj == 'yes') or (quelle_maj("1.4.2"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.4.2 :</b><br />";
		$result .= "&nbsp;->Tentative de modification du champ AUTOCLOSE dans la table log.<br />";
		$result_inter = traite_requete("ALTER TABLE `log` CHANGE `AUTOCLOSE` `AUTOCLOSE` ENUM( '0', '1', '2', '3', '4' ) DEFAULT '0' NOT NULL");
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
	}

	if (($force_maj == 'yes') or (quelle_maj("1.4.2.1"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.4.2.1 :</b><br />";
		$result .= "&nbsp;->Cr�ation de la table absences_creneaux <br />";
		$query2 = mysql_query("CREATE TABLE IF NOT EXISTS `absences_creneaux` (`id_definie_periode` int(11) NOT NULL auto_increment, `nom_definie_periode` varchar(10) NOT NULL default '', `heuredebut_definie_periode` time NOT NULL default '00:00:00', `heurefin_definie_periode` time NOT NULL default '00:00:00', PRIMARY KEY  (`id_definie_periode`)) TYPE=MyISAM AUTO_INCREMENT=42;");
		if ($query2)
		$result .= "<font color=\"green\">Ok !</font><br />";

		$result .= "&nbsp;->Insertion de valeurs par d�faut :";
		$test = mysql_result(mysql_query("SELECT count(*) FROM absences_creneaux"), "0");
		if ($test == "0") {
			$result .= "&nbsp;Oui<br />";
			$tab_req = array ();
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (1, 'M1', '08:00:00', '08:55:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (2, 'M2', '08:55:00', '09:50:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (3, 'M3', '10:05:00', '11:00:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (4, 'M4', '11:00:00', '11:55:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (5, 'S1', '13:30:00', '14:25:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (6, 'S2', '14:25:00', '15:20:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (7, 'S3', '15:35:00', '16:30:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (8, 'S4', '16:30:00', '17:30:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (32, 'M5', '11:55:00', '12:30:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (31, 'P1', '09:50:00', '10:05:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (33, 'R', '12:00:00', '13:00:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (34, 'R1', '13:00:00', '13:30:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (35, 'P2', '15:20:00', '15:35:00');";
			$tab_req[] = "INSERT INTO `absences_creneaux` VALUES (36, 'S5', '17:30:00', '18:25:00');";

			foreach ($tab_req as $key => $value) {
				$result_inter .= traite_requete($value);
			}
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= $result_inter;
			}
			$result_inter = '';
		} else {
			$result .= "&nbsp;<font color=blue>Non (la table n'est pas vide)</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table absences_motifs <br />";
		$query = mysql_query("CREATE TABLE IF NOT EXISTS `absences_motifs` (`id_motif_absence` int(11) NOT NULL auto_increment, `init_motif_absence` char(2) NOT NULL default '', `def_motif_absence` varchar(255) NOT NULL default '', PRIMARY KEY  (`id_motif_absence`)) TYPE=MyISAM AUTO_INCREMENT=33 ;");
		if ($query)
		$result .= "<font color=\"green\">Ok !</font><br />";
		$result .= "&nbsp;Insertion de valeurs par d�faut :";

		$test = mysql_result(mysql_query("SELECT count(*) FROM absences_motifs"), "0");
		if ($test == "0") {
			$result .= "&nbsp;Oui<br />";
			$tab_req = array ();
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (1, 'A', 'aucun motif');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (2, 'AS', 'accident sport');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (3, 'AT', 'non pr�sent en retenue');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (4, 'C', 'sur la cour');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (5, 'CF', 'convenances familiales');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (6, 'CO', 'convocation bureau');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (7, 'CS', 'competition sportive');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (8, 'DI', 'dispense d''e.p.s.');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (9, 'ET', 'erreur d''emploie du temps');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (10, 'EX', 'examen');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (11, 'H', 'Hospitalis�(e)');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (12, 'JP', 'justifie par le principal');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (13, 'MA', 'Maladie');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (14, 'OR', 'conseiller');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (15, 'PR', 'reveil');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (16, 'RC', 'refus de venir en cours');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (17, 'RE', 'renvoye');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (18, 'RT', 'pr�sent en retenue');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (19, 'RV', 'renvoi du cours');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (20, 'SM', 'refus de justification');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (21, 'SP', 'sorite p�dagogique');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (22, 'ST', 'stage � l''ext�rieur');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (23, 'T', 't�l�phone');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (24, 'TR', 'transport');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (25, 'VM', 'visite m�dical');";
			$tab_req[] = "INSERT INTO `absences_motifs` VALUES (26, 'IN', 'infirmerie');";
			$tab_req[] = "INSERT INTO `ct_types_documents` ( `id_type` , `titre` , `extension` , `upload` ) VALUES ( '', 'Texte OpenDocument', 'odt', 'oui' );";
			$tab_req[] = "INSERT INTO `ct_types_documents` ( `id_type` , `titre` , `extension` , `upload` ) VALUES ( '', 'Classeur OpenDocument', 'ods', 'oui' );";
			$tab_req[] = "INSERT INTO `ct_types_documents` ( `id_type` , `titre` , `extension` , `upload` ) VALUES ( '', 'Pr�sentation OpenDocument', 'odp', 'oui' );";
			$tab_req[] = "INSERT INTO `ct_types_documents` ( `id_type` , `titre` , `extension` , `upload` ) VALUES ( '', 'Dessin OpenDocument', 'odg', 'oui' );";
			$tab_req[] = "INSERT INTO `ct_types_documents` ( `id_type` , `titre` , `extension` , `upload` ) VALUES ( '', 'Base de donn�es OpenDocument', 'odb', 'oui' );";
			foreach ($tab_req as $key => $value) {
				$result_inter .= traite_requete($value);
			}
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= $result_inter;
			}
			$result_inter = '';

		} else {
			$result .= "&nbsp;<font color=blue>Non (la table n'est pas vide)</font><br />";
		}
		$test = sql_query1("select count(id_definie_periode) from definie_periodes");
		if ($test != -1) {
			$result .= "&nbsp;->Tentative de suppression de la table definie_periodes (si erreur, c'est probablement que la table n'existe plus).<br />";
			$result_inter = traite_requete("DROP TABLE definie_periodes");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= $result_inter;
			}
		}

		$test = sql_query1("select count(id_motif_absence) from motif_absence");
		if ($test != -1) {
			$result .= "&nbsp;->Tentative de suppression de la table motif_absence (si erreur, c'est probablement que la table n'existe plus).<br />";
			$result_inter = traite_requete("DROP TABLE motif_absence");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= $result_inter;
			}
		}

		$result .= "&nbsp;->Tentative d'ajout du champ heure_retard_eleve � la table absences_eleves.<br />";
		$result_inter = traite_requete("ALTER TABLE absences_eleves ADD heure_retard_eleve TIME NOT NULL ;");
		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'active_module_absence'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO `setting` VALUES ('active_module_absence', 'y');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'active_module_absence_professeur'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result .= traite_requete("INSERT INTO setting VALUES ('active_module_absence_professeur', 'y');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_appreciations'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_affiche_appreciations', 'y');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_absences'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_affiche_absences', 'y');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_avis'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_affiche_avis', 'y');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_aid'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_affiche_aid', 'y');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_formule'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_affiche_formule', 'y');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_signature'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('bull_affiche_signature', 'y');");

	}

	if (($force_maj == 'yes') or (quelle_maj("1.4.3"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.4.3 :</b><br />";

/*
* ATTENTION ! ICI se trouve une update de la 1.4.4 sur les cat�gories de mati�re.
* La raison : pour ceux qui mettent � jour depuis la 1.4.2. En effet la mise � jour des groupes
* utilise la lib groupes.inc.php, qui, dans ce paquetage, a �t� modifi�e pour prendre en compte
* les cat�gories de mati�re. La proc�dure de mise � jour est donc probl�matique
* pour une mise � jour d'une version inf�rieure � la 1.4.3 vers la 1.4.4.
*/

		$result .= "&nbsp;->Cr�ation de la table matieres_categories<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'matieres_categories'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `matieres_categories` (`id` int(11) NOT NULL auto_increment, `nom_court` varchar(255) NOT NULL default '', `nom_complet` varchar(255) NOT NULL default '', `priority` smallint(6) NOT NULL default '0', PRIMARY KEY  (`id`))");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Insertion de la cat�gorie de mati�re par d�faut<br />";
		$test = mysql_result(mysql_query("SELECT count(id) FROM matieres_categories WHERE id='1'"),0);
		if ($test == 0) {
			$query1b = mysql_query("INSERT INTO `matieres_categories` SET id = '1', nom_court = 'Autres', nom_complet = 'Autres', priority = '7'");
			if ($query1b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La mati�re par d�faut existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table j_matieres_categories_classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_matieres_categories_classes'"));
		if ($test1 == 0) {
			$query2 = mysql_query("CREATE TABLE IF NOT EXISTS `j_matieres_categories_classes` (`categorie_id` int(11) NOT NULL default '0', `classe_id` int(11) NOT NULL default '0', `priority` smallint(6) NOT NULL default '0', `affiche_moyenne` tinyint(1) NOT NULL default '0', PRIMARY KEY  (`categorie_id`,`classe_id`))");
			if ($query2) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ categorie_id � la table matieres<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM matieres LIKE 'categorie_id'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `matieres` ADD `categorie_id` INT NOT NULL default '1' AFTER `priority`");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ display_mat_cat � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'display_mat_cat'"));
		if ($test1 == 0) {
			$query4 = mysql_query("ALTER TABLE `classes` ADD `display_mat_cat` CHAR(1) NOT NULL default 'n' AFTER `display_coef`");
			if ($query4) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}
/*
* FIN DE LA PARTIE COPIEE DE LA PROCEDURE DE LA 1.4.4
*
* ===================================================
*/


		$result .= "&nbsp;->Cr�ation de la table groupes <br />";
		$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `groupes` (`id` int(11) NOT NULL auto_increment, `name` varchar(60) NOT NULL default '', `description` text NOT NULL, PRIMARY KEY  (`id`))");
		if ($query1) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table j_groupes_classes<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_groupes_classes'"));
		if ($test == 0) {
			$query2 = mysql_query("CREATE TABLE IF NOT EXISTS `j_groupes_classes` (`id_groupe` int(11) NOT NULL default '0', `id_classe` int(11) NOT NULL default '0', `priorite` smallint(6) NOT NULL, `coef` decimal(3,1) NOT NULL, PRIMARY KEY (`id_groupe`, `id_classe`))");
			if ($query2) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
		}

/*
* AJOUT DEPUIS LA MISE A JOUR 1.4.4
*/
		$result .= "&nbsp;->Ajout du champ categorie_id � la table j_groupes_classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM j_groupes_classes LIKE 'categorie_id'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `j_groupes_classes` ADD `categorie_id` int(11) NOT NULL default '1' AFTER `coef`");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}
/*
* FIN AJOUT --
*/

		$result .= "&nbsp;->Cr�ation de la table j_groupes_matieres<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_groupes_matieres'"));
		if ($test == 0) {
			$query3 = mysql_query("CREATE TABLE IF NOT EXISTS `j_groupes_matieres` (`id_groupe` int(11) NOT NULL default '0',`id_matiere` varchar(50) NOT NULL default '', PRIMARY KEY (`id_groupe`, `id_matiere`))");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table j_groupes_professeurs<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_groupes_professeurs'"));
		if ($test == 0) {
			$query4 = mysql_query("CREATE TABLE IF NOT EXISTS `j_groupes_professeurs` (`id_groupe` int(11) NOT NULL default '0',`login` varchar(50) NOT NULL default '', `ordre_prof` smallint(6) NOT NULL default '0', PRIMARY KEY (`id_groupe`, `login`))");
			if ($query4) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table j_eleves_groupes<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_eleves_groupes'"));
		if ($test == 0) {
			$query4b = mysql_query("CREATE TABLE IF NOT EXISTS `j_eleves_groupes` (`login` varchar(50) NOT NULL default '', `id_groupe` int(11) NOT NULL default '0', `periode` int(11) NOT NULL default '0', PRIMARY KEY (`login`, `id_groupe`, `periode`))");
			if ($query4b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table eleves_groupes_settings<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'eleves_groupes_settings'"));
		if ($test == 0) {
			$query5 = mysql_query("CREATE TABLE IF NOT EXISTS eleves_groupes_settings (login varchar(50) NOT NULL, id_groupe int(11) NOT NULL, `name` varchar(50) NOT NULL, `value` varchar(50) NOT NULL, PRIMARY KEY (`login`, `id_groupe`, `name`))");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ id_groupe � la table ct_devoirs_entry<br />";
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `ct_devoirs_entry` LIKE 'id_groupe'"));
		if ($test == 0) {
			$query6 = mysql_query("ALTER TABLE `ct_devoirs_entry` ADD `id_groupe` INT NOT NULL AFTER `id_ct`");
			if ($query6) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ id_groupe � la table ct_entry<br />";
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `ct_entry` LIKE 'id_groupe'"));
		if ($test == 0) {
			$query7 = mysql_query("ALTER TABLE `ct_entry` ADD `id_groupe` INT NOT NULL AFTER `id_ct`");
			if ($query7) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ id_groupe � la table cn_cahier_notes<br />";
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `cn_cahier_notes` LIKE 'id_groupe'"));
		if ($test == 0) {
			$query7 = mysql_query("ALTER TABLE `cn_cahier_notes` ADD `id_groupe` INT NOT NULL AFTER `id_cahier_notes`");
			if ($query7) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ id_groupe � la table matieres_notes<br />";
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `matieres_notes` LIKE 'id_groupe'"));
		if ($test == 0) {
			$query8 = mysql_query("ALTER TABLE `matieres_notes` ADD `id_groupe` INT NOT NULL AFTER `matiere`");
			if ($query8) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ recalcul_rang � la table groupes<br />";
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `groupes` LIKE 'recalcul_rang'"));
		if ($test == 0) {
			$query9 = mysql_query("ALTER TABLE `groupes` ADD `recalcul_rang` VARCHAR(10) NOT NULL");
			if ($query9) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ id_groupe � la table matieres_appreciations<br />";
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `matieres_appreciations` LIKE 'id_groupe'"));
		if ($test == 0) {
			$query10 = mysql_query("ALTER TABLE `matieres_appreciations` ADD `id_groupe` INT NOT NULL AFTER `matiere`");
			if ($query10) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�</font><br />";
		}

		$result .= "&nbsp;->Conversion des donn�es vers le nouveau mod�les de groupes (cette op�ration peut prendre plusieurs minutes)<br />";

		$test_groupes = mysql_query("SELECT count(*) FROM groupes");
		$nb_groupes = mysql_result($test_groupes, 0);
		if ($nb_groupes != 0) {
			$result .= "<font color=\"blue\">Non (des groupes existent d�j�)</font><br />";
		} else {

			// On r�cup�re la liste des mati�res pour �viter les requ�tes multiples
			$call_matieres = mysql_query("SELECT * FROM matieres");
			$nb = mysql_num_rows($call_matieres);
			$matieres = array ();
			for ($i = 0; $i < $nb; $i++) {
				$id_matiere = mysql_result($call_matieres, $i, "matiere");
				$matiere = mysql_result($call_matieres, $i, "nom_complet");
				$priority = mysql_result($call_matieres, $i, "priority");
				$matieres[$id_matiere] = array (
	"matiere" => $id_matiere,
	"nom_complet" => $matiere,
	"priority" => $priority
				);
			}

			$former_scheme = mysql_query("SELECT * FROM j_classes_matieres_professeurs");
			if (!$former_scheme) {
				$nb = 0;
			} else {
				$nb = mysql_num_rows($former_scheme);
			}

			for ($i = 0; $i < $nb; $i++) {
				$id_classe = mysql_result($former_scheme, $i, "id_classe");
				$id_matiere = mysql_result($former_scheme, $i, "id_matiere");
				$id_professeur = mysql_result($former_scheme, $i, "id_professeur");
				$priorite = mysql_result($former_scheme, $i, "priorite");
				$ordre_prof = mysql_result($former_scheme, $i, "ordre_prof");
				$coef = mysql_result($former_scheme, $i, "coef");
				$recalcul_rang = mysql_result($former_scheme, $i, "recalcul_rang");

				// On regarde si cette association correspond d�j� � un groupe
				$test = mysql_query("SELECT g.id FROM groupes g, j_groupes_classes jgc, j_groupes_matieres jgm WHERE (" .
"g.id = jgc.id_groupe AND " .
"jgc.id_classe = '" . $id_classe . "' AND " .
"jgc.id_groupe = jgm.id_groupe AND " .
"jgm.id_matiere = '" . $id_matiere . "')");

				if (mysql_num_rows($test) != 0) {
					// Si un enregistrement existe d�j�, �a veut dire que le groupe a d�j� �t� trait�
					// il ne reste alors qu'� ajouter le professeur mentionn� dans cette association

					$group_id = mysql_result($test, 0, "id");
					$insert_prof = mysql_query("INSERT into j_groupes_professeurs SET id_groupe = '" . $group_id . "', login = '" . $id_professeur . "', ordre_prof = '" . $ordre_prof . "'");

				} else {
					// La premi�re �tape consiste � cr�er le nouveau groupe, pour obtenir son ID
					$new_group = create_group($matieres[$id_matiere]["nom_complet"], $matieres[$id_matiere]["nom_complet"], $id_matiere, array (
							$id_classe
						));
					if (!is_numeric($new_group))
					echo $new_group . "<br />";
					$update = mysql_query("UPDATE groupes SET recalcul_rang = '" . $recalcul_rang . "' WHERE id = '" . $new_group . "'");
					$update2 = update_group_class_options($new_group, $id_classe, array (
		"priorite" => $priorite,
		"coef" => $coef,
		"categorie_id" => 1
						));
					// On ajoute le professeur
					$insert_prof = mysql_query("INSERT into j_groupes_professeurs SET id_groupe = '" . $new_group . "', login = '" . $id_professeur . "', ordre_prof = '" . $ordre_prof . "'");

					// On s'occupe maintenant des �l�ves, p�riode par p�riode

					$call_periodes = mysql_query("select num_periode FROM periodes WHERE id_classe = '" . $id_classe . "'");
					$nb_per = mysql_num_rows($call_periodes);
					for ($j = 0; $j < $nb_per; $j++) {
						$num_periode = mysql_result($call_periodes, $j, "num_periode");
						$call_eleves = mysql_query("SELECT login FROM j_eleves_classes WHERE (periode = '" . $num_periode . "' AND id_classe = '" . $id_classe . "')");
						$eleves = array ();
						while ($row = mysql_fetch_row($call_eleves)) {
							$eleves[] = $row[0];
						}

						$call_options = mysql_query("SELECT login FROM j_eleves_matieres WHERE (periode = '" . $num_periode . "' AND matiere = '" . $id_matiere . "')");
						$options = array ();
						while ($row = mysql_fetch_row($call_options)) {
							$options[] = $row[0];
						}

						$list_eleves = array_diff($eleves, $options);

						foreach ($list_eleves as $_login) {
							if ($new_group == 0)
							echo "ERREUR! New_group ID = 0<br />";
							// Appartenance au groupe
							$insert = mysql_query("INSERT into j_eleves_groupes SET login = '" . $_login . "', id_groupe = '" . $new_group . "', periode = '" . $num_periode . "'");
							// Mise � jour de la r�f�rence � la note du bulletin
							$update = mysql_query("UPDATE matieres_notes SET id_groupe = '" . $new_group . "' WHERE (login = '" . $_login . "' AND periode = '" . $num_periode . "' AND matiere = '" . $id_matiere . "')");
							// Mise � jour de la r�f�rence � l'appr�ciation du bulletin
							$update = mysql_query("UPDATE matieres_appreciations SET id_groupe = '" . $new_group . "' WHERE (login = '" . $_login . "' AND periode = '" . $num_periode . "' AND matiere = '" . $id_matiere . "')");
						}
					}

					// Et on fait les mises � jours de r�f�rences pour les carnets de notes et cahiers de texte
					$update_cn = mysql_query("UPDATE cn_cahier_notes SET id_groupe = '" . $new_group . "' WHERE (matiere = '" . $id_matiere . "' AND id_classe = '" . $id_classe . "')");

					$update_ct1 = mysql_query("UPDATE ct_devoir_entry SET id_groupe = '" . $new_group . "' WHERE (id_matiere = '" . $id_matiere . "' AND id_classe = '" . $id_classe . "')");
					$update_ct2 = mysql_query("UPDATE ct_entry SET id_groupe = '" . $new_group . "' WHERE (id_matiere = '" . $id_matiere . "' AND id_classe = '" . $id_classe . "')");
				}
			}
		}

		// Maintenant la migration est faite. On met � jour les primary keys et on supprime les champs et les tables qui ne sont plus utilis�s.
		// Cette �tape risque de mettre en avant un certain nombre de bugs persitants.

		$result .= "&nbsp;->Mise � jour de l'index de la table ct_devoirs_entry<br />";
		$query6b = mysql_query("ALTER TABLE `ct_devoirs_entry` DROP INDEX id_ct , ADD INDEX id_ct ( `id_ct` , `id_groupe` )");
		if ($query6b) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Mise � jour de l'index de la table ct_entry<br />";
		$query7b = mysql_query("ALTER TABLE `ct_entry` DROP INDEX id_ct, ADD INDEX id_ct ( `id_ct` , `id_groupe` )");
		if ($query7b) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Mise � jour de la cl� primaire de la table cn_cahier_notes<br />";
		$query7b = mysql_query("ALTER TABLE `cn_cahier_notes` DROP PRIMARY KEY , ADD PRIMARY KEY ( `id_cahier_notes` , `id_groupe` , `periode` )");
		if ($query7b) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Mise � jour de la cl� primaire de la table matieres_notes<br />";
		$query7b = mysql_query("ALTER TABLE `matieres_notes` DROP PRIMARY KEY , ADD PRIMARY KEY ( `login` , `id_groupe` , `periode` )");
		if ($query7b) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Mise � jour de la cl� primaire de la table matieres_appreciations<br />";
		$query10b = mysql_query("ALTER TABLE `matieres_appreciations` DROP PRIMARY KEY , ADD PRIMARY KEY ( `login` , `periode` , `id_groupe` )");
		if ($query10b) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Suppression des champs id_classe et id_matiere dans la table ct_devoirs_entry<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `ct_devoirs_entry` LIKE 'id_classe'"));
		$test2 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `ct_devoirs_entry` LIKE 'id_matiere'"));
		if ($test1 == 1 AND $test2 == 1) {
			$query10b = mysql_query("ALTER TABLE `ct_devoirs_entry` DROP `id_matiere` , DROP `id_classe`");
			if ($query10b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Les champs ont d�j� �t� supprim�s.</font><br />";
		}

		$result .= "&nbsp;->Suppression des champs id_classe et id_matiere dans la table ct_entry<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `ct_entry` LIKE 'id_classe'"));
		$test2 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `ct_entry` LIKE 'id_matiere'"));
		if ($test1 == 1 AND $test2 == 1) {
			$query10b = mysql_query("ALTER TABLE `ct_entry` DROP `id_matiere` , DROP `id_classe`");
			if ($query10b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Les champs ont d�j� �t� supprim�s.</font><br />";
		}

		$result .= "&nbsp;->Suppression des champs id_classe et matiere dans la table cn_cahier_notes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `cn_cahier_notes` LIKE 'id_classe'"));
		$test2 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `cn_cahier_notes` LIKE 'matiere'"));
		if ($test1 == 1 AND $test2 == 1) {
			$query10b = mysql_query("ALTER TABLE `cn_cahier_notes` DROP `matiere` , DROP `id_classe`");
			if ($query10b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Les champs ont d�j� �t� supprim�s.</font><br />";
		}

		$result .= "&nbsp;->Suppression du champ 'matiere' dans la table matieres_notes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `matieres_notes` LIKE 'matiere'"));
		if ($test1 == 1) {
			$query10b = mysql_query("ALTER TABLE `matieres_notes` DROP `matiere`");
			if ($query10b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ a d�j� �t� supprim�.</font><br />";
		}

		$result .= "&nbsp;->Suppression du champ 'matiere' dans la table matieres_appreciations<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM `matieres_appreciations` LIKE 'matiere'"));
		if ($test1 == 1) {
			$query10b = mysql_query("ALTER TABLE `matieres_appreciations` DROP `matiere`");
			if ($query10b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (champ d�j� supprim� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ a d�j� �t� supprim�.</font><br />";
		}

		$result .= "&nbsp;->Suppression de la table j_classes_matieres_professeurs<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_classes_matieres_professeurs'"));
		if ($test1 == 1) {
			$query10b = mysql_query("DROP TABLE `j_classes_matieres_professeurs`");
			if ($query10b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table a d�j� �t� supprim�e.</font><br />";
		}

		$result .= "&nbsp;->Suppression de la table j_eleves_matieres<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_eleves_matieres'"));
		if ($test1 == 1) {
			$query10b = mysql_query("DROP TABLE `j_eleves_matieres`");
			if ($query10b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (table d�j� supprim�e ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table a d�j� �t� supprim�e.</font><br />";
		}
		//=======================================
		// AJOUT: boireaus
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'p_bulletin_margin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result .= "&nbsp;->Ajout du param�tre p_bulletin_margin<br />";
			$query11 = mysql_query("INSERT INTO setting VALUES('p_bulletin_margin','5')");
			if ($query11) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur lors de l'insertion de 'p_bulletin_margin'.</font><br />";
			}
		}
		//=======================================

		$result .= "&nbsp;->Extension de la taille des champs elenoet et ereno de la table 'eleves'<br />";
		$query28 = mysql_query("ALTER TABLE eleves CHANGE elenoet elenoet VARCHAR( 50 ) NOT NULL, CHANGE ereno ereno VARCHAR( 50 ) NOT NULL");
		if ($query28) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Extension de la taille des champs ereno, nom1, prenom1, nom2, prenom2 de la table 'responsables'<br />";
		$query29 = mysql_query("ALTER TABLE responsables CHANGE ereno ereno VARCHAR( 50 ) NOT NULL, " .
												"CHANGE nom1 nom1 VARCHAR( 50 ) NOT NULL, " .
												"CHANGE prenom1 prenom1 VARCHAR( 50 ) NOT NULL, " .
												"CHANGE nom2 nom2 VARCHAR( 50 ) NOT NULL, " .
												"CHANGE prenom2 prenom2 VARCHAR( 50 ) NOT NULL"
		);
		if ($query29) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		// On nettoie la base pour s'assurer qu'il ne reste pas d'incoh�rences concernant les groupes
		// pour les �tablissements qui ont initialis� leur base sur la 1.4.3-rc2

		$result .= "&nbsp;-> Suppression des incoh�rences de la base de donn�es en lien avec les groupes<br/>";

		$nb_del1 = 0;
		$res1 = true;
		$test_groupes = mysql_query("select distinct(g.id) FROM groupes g WHERE NOT EXISTS (SELECT distinct(id_groupe) FROM j_groupes_classes jgc WHERE jgc.id_groupe = g.id)");
		if($test_groupes){
			for ($g=0;$g<mysql_num_rows($test_groupes);$g++) {
				$del_groupe_id = mysql_result($test_groupes, $g, "id");
				$res1 = mysql_query("DELETE FROM g, jeg, jgm, jgp USING groupes g, j_eleves_groupes jeg, j_groupes_matieres jgm, j_groupes_professeurs jgp WHERE (" .
		"g.id = '" . $del_groupe_id . "' AND " .
		"jeg.id_groupe = '" . $del_groupe_id . "' AND " .
		"jgm.id_groupe = '" . $del_groupe_id . "' AND " .
		"jgp.id_groupe = '" . $del_groupe_id . "')");
				if ($res1) {
					$nb_del1++;
				} else {
					echo mysql_error();
				}
			}
		}

		$nb_del2 = 0;
		$res2 = true;
		$test_eleves = mysql_query("select jeg.login, jeg.periode, jeg.id_groupe FROM j_eleves_groupes jeg WHERE NOT EXISTS (SELECT jec.login FROM j_eleves_classes jec WHERE (jec.periode = jeg.periode AND jec.login = jeg.login))");
		if($test_eleves){
			for ($g=0;$g<mysql_num_rows($test_eleves);$g++) {
				$del_eleve = mysql_result($test_eleves, $g, "login");
				$del_periode = mysql_result($test_eleves, $g, "periode");
				$del_groupe = mysql_result($test_eleves, $g, "id_groupe");
				$res2 = mysql_query("DELETE FROM j_eleves_groupes WHERE (" .
		"login = '" . $del_eleve . "' AND " .
		"periode = '" . $del_periode . "' AND " .
		"id_groupe = '" . $del_groupe . "')");
				if ($res2) {
					$nb_del2++;
				}
			}
		}

		if ($res1 && $res2) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Des erreurs ont �t� rencontr�es.</font><br />";
		}
		$result .= "-- $nb_del1 groupes fant�mes ont �t� supprim�s.<br/>-- $nb_del2 associations �l�ve/groupe/p�riode ont �t� supprim�es (un �l�ve ne peut pas appartenir � un enseignement pour une p�riode s'il n'est pas associ� � une classe pour cette m�me p�riode)<br />";

	}

	if (($force_maj == 'yes') or (quelle_maj("1.4.4"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.4.4 :</b><br />";

		$result .= "&nbsp;->Cr�ation de la table matieres_categories<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'matieres_categories'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `matieres_categories` (`id` int(11) NOT NULL auto_increment, `nom_court` varchar(255) NOT NULL default '', `nom_complet` varchar(255) NOT NULL default '', `priority` smallint(6) NOT NULL default '0', PRIMARY KEY  (`id`))");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Insertion de la cat�gorie de mati�re par d�faut<br />";
		$test = mysql_result(mysql_query("SELECT count(id) FROM matieres_categories WHERE id='1'"),0);
		if ($test == 0) {
			$query1b = mysql_query("INSERT INTO `matieres_categories` SET id = '1', nom_court = 'Autres', nom_complet = 'Autres', priority = '7'");
			if ($query1b) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La mati�re par d�faut existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table j_matieres_categories_classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_matieres_categories_classes'"));
		if ($test1 == 0) {
			$query2 = mysql_query("CREATE TABLE IF NOT EXISTS `j_matieres_categories_classes` (`categorie_id` int(11) NOT NULL default '0', `classe_id` int(11) NOT NULL default '0', `priority` smallint(6) NOT NULL default '0', `affiche_moyenne` tinyint(1) NOT NULL default '0', PRIMARY KEY  (`categorie_id`,`classe_id`))");
			if ($query2) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ categorie_id � la table matieres<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM matieres LIKE 'categorie_id'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `matieres` ADD `categorie_id` INT NOT NULL default '1' AFTER `priority`");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ categorie_id � la table j_groupes_classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM j_groupes_classes LIKE 'categorie_id'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `j_groupes_classes` ADD `categorie_id` int(11) NOT NULL default '1' AFTER `coef`");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ display_mat_cat � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'display_mat_cat'"));
		if ($test1 == 0) {
			$query4 = mysql_query("ALTER TABLE `classes` ADD `display_mat_cat` CHAR(1) NOT NULL default 'n' AFTER `display_coef`");
			if ($query4) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ display_nbdev � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'display_nbdev'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `classes` ADD `display_nbdev` CHAR(1) NOT NULL default 'n' AFTER `display_mat_cat`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}
		$result .= "&nbsp;->Ajout du champ heure_entry � la table ct_entry<br />";

		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_entry LIKE 'heure_entry'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `ct_entry` ADD `heure_entry` TIME NOT NULL AFTER `id_ct`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}



		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'gepi_denom_boite'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$query_boite1=mysql_query("INSERT INTO setting VALUES ('gepi_denom_boite', 'bo�te');");
			if($query_boite1){
				$result.="D�finition du param�tre gepi_denom_boite � 'bo�te': <font color=\"green\">Ok !</font><br />";
			}
			else{
				$result.="D�finition du param�tre gepi_denom_boite � 'bo�te': <font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'gepi_denom_boite_genre'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$query_boite1=mysql_query("INSERT INTO setting VALUES ('gepi_denom_boite_genre', 'f');");
			if($query_boite1){
				$result.="D�finition du param�tre gepi_denom_boite_genre � 'f': <font color=\"green\">Ok !</font><br />";
			}
			else{
				$result.="D�finition du param�tre gepi_denom_boite_genre � 'f': <font color=\"red\">Erreur !</font><br />";
			}
		}


		// Conversion de cm en mm:
		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'cnv_addressblock_dim_144'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			// La mise � jour des dimensions de cm en mm n'a pas encore �t� effectu�e.

			$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_top'");
			$res_test=mysql_num_rows($req_test);
			if ($res_test>0){
				//$lig_addressblock_padding_top=mysql_fetch_object($req_test);
				$lig_addressblock_padding_top=mysql_fetch_array($req_test);
				// Conversion de cm en mm:
				//$addressblock_padding_top0="$lig_addressblock_padding_top->value";
				$addressblock_padding_top0=$lig_addressblock_padding_top[0];
				$addressblock_padding_top1=$addressblock_padding_top0*10;
				$update_addressblock_padding_top=mysql_query("UPDATE setting SET value='$addressblock_padding_top1' WHERE name='addressblock_padding_top';");
				if($update_addressblock_padding_top){
					$result.="-&gt; Mise � jour du param�tre addressblock_padding_top de ".$addressblock_padding_top0."cm � ".$addressblock_padding_top1."mm: <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; Mise � jour du param�tre addressblock_padding_top de ".$addressblock_padding_top0."cm � ".$addressblock_padding_top1."mm: <font color=\"red\">Erreur !</font><br />";
				}
			}
			else{
				$insert_addressblock_padding_top=mysql_query("INSERT INTO setting VALUES ('addressblock_padding_top', '40');");
				if($insert_addressblock_padding_top){
					$result.="-&gt; D�finition du param�tre addressblock_padding_top � '40': <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; D�finition du param�tre addressblock_padding_top � '40': <font color=\"red\">Erreur !</font><br />";
				}
			}




			$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_right'");
			$res_test=mysql_num_rows($req_test);
			if ($res_test>0){
				//$lig_addressblock_padding_right=mysql_fetch_object($req_test);
				$lig_addressblock_padding_right=mysql_fetch_array($req_test);
				// Conversion de cm en mm:
				//$addressblock_padding_right0=$lig_addressblock_padding_right->value;
				$addressblock_padding_right0=$lig_addressblock_padding_right[0];
				$addressblock_padding_right1=$addressblock_padding_right0*10;
				$update_addressblock_padding_right=mysql_query("UPDATE setting SET value='$addressblock_padding_right1' WHERE name='addressblock_padding_right';");
				if($update_addressblock_padding_right){
					$result.="-&gt; Mise � jour du param�tre addressblock_padding_right de ".$addressblock_padding_right0."cm � ".$addressblock_padding_right1."mm: <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; Mise � jour du param�tre addressblock_padding_right de ".$addressblock_padding_right0."cm � ".$addressblock_padding_right1."mm: <font color=\"red\">Erreur !</font><br />";
				}
			}
			else{
				$insert_addressblock_padding_right=mysql_query("INSERT INTO setting VALUES ('addressblock_padding_right', '20');");
				if($insert_addressblock_padding_right){
					$result.="-&gt; D�finition du param�tre addressblock_padding_right � '20': <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; D�finition du param�tre addressblock_padding_right � '20': <font color=\"red\">Erreur !</font><br />";
				}
			}



			$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_text'");
			$res_test=mysql_num_rows($req_test);
			if ($res_test>0){
				//$lig_addressblock_padding_text=mysql_fetch_object($req_test);
				$lig_addressblock_padding_text=mysql_fetch_array($req_test);
				// Conversion de cm en mm:
				//$addressblock_padding_text0=$lig_addressblock_padding_text->value;
				$addressblock_padding_text0=$lig_addressblock_padding_text[0];
				$addressblock_padding_text1=$addressblock_padding_text0*10;
				$update_addressblock_padding_text=mysql_query("UPDATE setting SET value='$addressblock_padding_text1' WHERE name='addressblock_padding_text';");
				if($update_addressblock_padding_text){
					$result.="-&gt; Mise � jour du param�tre addressblock_padding_text de ".$addressblock_padding_text0."cm � ".$addressblock_padding_text1."mm: <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; Mise � jour du param�tre addressblock_padding_text de ".$addressblock_padding_text0."cm � ".$addressblock_padding_text1."mm: <font color=\"red\">Erreur !</font><br />";
				}
			}
			else{
				$insert_addressblock_padding_text=mysql_query("INSERT INTO setting VALUES ('addressblock_padding_text', '20');");
				if($insert_addressblock_padding_text){
					$result.="-&gt; D�finition du param�tre addressblock_padding_text � '20': <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; D�finition du param�tre addressblock_padding_text � '20': <font color=\"red\">Erreur !</font><br />";
				}
			}



			$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_length'");
			$res_test=mysql_num_rows($req_test);
			if ($res_test>0){
				//$lig_addressblock_length=mysql_fetch_object($req_test);
				$lig_addressblock_length=mysql_fetch_array($req_test);
				// Conversion de cm en mm:
				//$addressblock_length0=$lig_addressblock_length->value;
				$addressblock_length0=$lig_addressblock_length[0];
				$addressblock_length1=$addressblock_length0*10;
				$update_addressblock_length=mysql_query("UPDATE setting SET value='$addressblock_length1' WHERE name='addressblock_length';");
				if($update_addressblock_length){
					$result.="-&gt; Mise � jour du param�tre addressblock_length de ".$addressblock_length0."cm � ".$addressblock_length1."mm: <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; Mise � jour du param�tre addressblock_length de ".$addressblock_length0."cm � ".$addressblock_length1."mm: <font color=\"red\">Erreur !</font><br />";
				}
			}
			else{
				$insert_addressblock_length=mysql_query("INSERT INTO setting VALUES ('addressblock_length', '60');");
				if($insert_addressblock_length){
					$result.="-&gt; D�finition du param�tre addressblock_length � '60': <font color=\"green\">Ok !</font><br />";
				}
				else{
					$result.="-&gt; D�finition du param�tre addressblock_length � '60': <font color=\"red\">Erreur !</font><br />";
				}
			}

			$sql="INSERT INTO setting SET name='cnv_addressblock_dim_144', value='y'";
			$res_cnv_addressblock_dim_144=mysql_query($sql);
		}
		else{
			$result.="La conversion cm/mm des dimensions du bloc adresse a �t� effectu�e lors d'une pr�c�dente mise � jour.<br />";
		}
/*
$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_top'");
$res_test=mysql_num_rows($req_test);
if ($res_test>0){
$lig_addressblock_padding_top=mysql_fetch_object($req_test);
// Conversion de cm en mm:
$addressblock_padding_top0=$lig_addressblock_padding_top->value;
$addressblock_padding_top1=$addressblock_padding_top0*10;
$update_addressblock_padding_top=mysql_query("UPDATE setting SET value='$addressblock_padding_top1' WHERE name='addressblock_padding_top';");
if($update_addressblock_padding_top){
$result.="Mise � jour du param�tre addressblock_padding_top de ".$addressblock_padding_top0."cm � ".$addressblock_padding_top1."mm: <font color=\"green\">Ok !</font><br />";
}
else{
$result.="Mise � jour du param�tre addressblock_padding_top de ".$addressblock_padding_top0."cm � ".$addressblock_padding_top1."mm: <font color=\"red\">Erreur !</font><br />";
}
}
else{
$insert_addressblock_padding_top=mysql_query("INSERT INTO setting VALUES ('addressblock_padding_top', '40');");
if($insert_addressblock_padding_top){
$result.="D�finition du param�tre addressblock_padding_top � '40': <font color=\"green\">Ok !</font><br />";
}
else{
$result.="D�finition du param�tre addressblock_padding_top � '40': <font color=\"red\">Erreur !</font><br />";
}
}

$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_right'");
$res_test=mysql_num_rows($req_test);
if ($res_test>0){
$lig_addressblock_padding_right=mysql_fetch_object($req_test);
// Conversion de cm en mm:
$addressblock_padding_right0=$lig_addressblock_padding_right->value;
$addressblock_padding_right1=$addressblock_padding_right0*10;
$update_addressblock_padding_right=mysql_query("UPDATE setting SET value='$addressblock_padding_right1' WHERE name='addressblock_padding_right';");
if($update_addressblock_padding_right){
$result.="Mise � jour du param�tre addressblock_padding_right de ".$addressblock_padding_right0."cm � ".$addressblock_padding_right1."mm: <font color=\"green\">Ok !</font><br />";
}
else{
$result.="Mise � jour du param�tre addressblock_padding_right de ".$addressblock_padding_right0."cm � ".$addressblock_padding_right1."mm: <font color=\"red\">Erreur !</font><br />";
}
}
else{
$insert_addressblock_padding_right=mysql_query("INSERT INTO setting VALUES ('addressblock_padding_right', '20');");
if($insert_addressblock_padding_right){
$result.="D�finition du param�tre addressblock_padding_right � '20': <font color=\"green\">Ok !</font><br />";
}
else{
$result.="D�finition du param�tre addressblock_padding_right � '20': <font color=\"red\">Erreur !</font><br />";
}
}

$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_padding_text'");
$res_test=mysql_num_rows($req_test);
if ($res_test>0){
$lig_addressblock_padding_text=mysql_fetch_object($req_test);
// Conversion de cm en mm:
$addressblock_padding_text0=$lig_addressblock_padding_text->value;
$addressblock_padding_text1=$addressblock_padding_text0*10;
$update_addressblock_padding_text=mysql_query("UPDATE setting SET value='$addressblock_padding_text1' WHERE name='addressblock_padding_text';");
if($update_addressblock_padding_text){
$result.="Mise � jour du param�tre addressblock_padding_text de ".$addressblock_padding_text0."cm � ".$addressblock_padding_text1."mm: <font color=\"green\">Ok !</font><br />";
}
else{
$result.="Mise � jour du param�tre addressblock_padding_text de ".$addressblock_padding_text0."cm � ".$addressblock_padding_text1."mm: <font color=\"red\">Erreur !</font><br />";
}
}
else{
$insert_addressblock_padding_text=mysql_query("INSERT INTO setting VALUES ('addressblock_padding_text', '20');");
if($insert_addressblock_padding_text){
$result.="D�finition du param�tre addressblock_padding_text � '20': <font color=\"green\">Ok !</font><br />";
}
else{
$result.="D�finition du param�tre addressblock_padding_text � '20': <font color=\"red\">Erreur !</font><br />";
}
}

$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_length'");
$res_test=mysql_num_rows($req_test);
if ($res_test>0){
$lig_addressblock_length=mysql_fetch_object($req_test);
// Conversion de cm en mm:
$addressblock_length0=$lig_addressblock_length->value;
$addressblock_length1=$addressblock_length0*10;
$update_addressblock_length=mysql_query("UPDATE setting SET value='$addressblock_length1' WHERE name='addressblock_length';");
if($update_addressblock_length){
$result.="Mise � jour du param�tre addressblock_length de ".$addressblock_length0."cm � ".$addressblock_length1."mm: <font color=\"green\">Ok !</font><br />";
}
else{
$result.="Mise � jour du param�tre addressblock_length de ".$addressblock_length0."cm � ".$addressblock_length1."mm: <font color=\"red\">Erreur !</font><br />";
}
}
else{
$insert_addressblock_length=mysql_query("INSERT INTO setting VALUES ('addressblock_length', '60');");
if($insert_addressblock_length){
$result.="D�finition du param�tre addressblock_length � '60': <font color=\"green\">Ok !</font><br />";
}
else{
$result.="D�finition du param�tre addressblock_length � '60': <font color=\"red\">Erreur !</font><br />";
}
}
*/


		// Ajout de nouveaux param�tres pour le bloc adresse des responsables sur le bulletin
		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_font_size'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$query_addressblock_font_size=mysql_query("INSERT INTO setting VALUES ('addressblock_font_size', '12');");
			if($query_addressblock_font_size){
				$result.="D�finition du param�tre addressblock_font_size � '12': <font color=\"green\">Ok !</font><br />";
			}
			else{
				$result.="D�finition du param�tre addressblock_font_size � '12': <font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_logo_etab_prop'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$query_addressblock_logo_etab_prop=mysql_query("INSERT INTO setting VALUES ('addressblock_logo_etab_prop', '50');");
			if($query_addressblock_logo_etab_prop){
				$result.="D�finition du param�tre addressblock_logo_etab_prop � '50': <font color=\"green\">Ok !</font><br />";
			}
			else{
				$result.="D�finition du param�tre addressblock_logo_etab_prop � '50': <font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_classe_annee'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$query_addressblock_classe_annee=mysql_query("INSERT INTO setting VALUES ('addressblock_classe_annee', '35');");
			if($query_addressblock_classe_annee){
				$result.="D�finition du param�tre addressblock_classe_annee � '35': <font color=\"green\">Ok !</font><br />";
			}
			else{
				$result.="D�finition du param�tre addressblock_classe_annee � '35': <font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_ecart_bloc_nom'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$query_bull_ecart_bloc_nom=mysql_query("INSERT INTO setting VALUES ('bull_ecart_bloc_nom', '1');");
			if($query_bull_ecart_bloc_nom){
				$result.="D�finition du param�tre bull_ecart_bloc_nom � '1': <font color=\"green\">Ok !</font><br />";
			}
			else{
				$result.="D�finition du param�tre bull_ecart_bloc_nom � '1': <font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'addressblock_debug'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$query_addressblock_debug=mysql_query("INSERT INTO setting VALUES ('addressblock_debug', 'n');");
			if($query_addressblock_debug){
				$result.="D�finition du param�tre addressblock_debug � 'n': <font color=\"green\">Ok !</font><br />";
			}
			else{
				$result.="D�finition du param�tre addressblock_debug � 'n': <font color=\"red\">Erreur !</font><br />";
			}
		}



		//==============================================

		$result .= "&nbsp;->Ajout du champ display_moy_gen � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'display_moy_gen'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `classes` ADD `display_moy_gen` CHAR(1) NOT NULL default 'y' AFTER `display_nbdev`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Cr�ation de la table preferences <br />";
		$query = mysql_query("CREATE TABLE IF NOT EXISTS `preferences` (`login` VARCHAR( 50 ) NOT NULL ,`name` VARCHAR( 32 ) NOT NULL ,`value` TEXT NOT NULL);");
		if($query){
			$result.="<font color=\"green\">Ok !</font><br />";
		}


		$result .= "&nbsp;->Cr�ation de la table j_scol_classes <br />";
		$query = mysql_query("CREATE TABLE IF NOT EXISTS `j_scol_classes` (`login` VARCHAR( 50 ) NOT NULL ,`id_classe` INT( 11 ) NOT NULL);");
		if($query){
			$result.="<font color=\"green\">Ok !</font><br />";
		}

		$sql="SELECT 1=1 FROM j_scol_classes;";
		$query=mysql_query($sql);
		if(mysql_num_rows($query)==0) {
			$test=mysql_query("SHOW TABLES;");
			//$temoin_j_scol_classes="";
			$notok_j_scol_classes=false;
			if($test){
				while($lig_test=mysql_fetch_array($test)){
					if($lig_test[0]=='j_scol_classes'){
						$call_classes_tmp=mysql_query("SELECT DISTINCT c.* FROM classes c, periodes p WHERE p.id_classe = c.id  ORDER BY classe");
						if(mysql_num_rows($call_classes_tmp)>0){
							while($lig_clas=mysql_fetch_object($call_classes_tmp)){
								$sql="SELECT login,nom,prenom FROM utilisateurs WHERE (statut='scolarite' AND etat='actif') ORDER BY nom,prenom";
								$res_scol=mysql_query($sql);
								if(mysql_num_rows($res_scol)>0){
									while($lig_scol=mysql_fetch_object($res_scol)){
										//$test=mysql_query("SELECT 1=1 FROM j_scol_classes WHERE id_classe='".$lig_clas->id."' AND login='".$scol_login[$i]."'");
										$test=mysql_query("SELECT 1=1 FROM j_scol_classes WHERE id_classe='".$lig_clas->id."' AND login='".$lig_scol->login."'");
										if(mysql_num_rows($test)==0){
											$sql="INSERT INTO j_scol_classes SET id_classe='".$lig_clas->id."', login='".$lig_scol->login."'";
											$reg_data=mysql_query($sql);
											if(!$reg_data){
												$notok_j_scol_classes=true;
											}
										}
									}
								}
							}
						}
						//$temoin_j_scol_classes="ok";
					}
				}
			}
			else{
				$result.="Erreur � la lecture des tables!<br />";
			}

			if($notok_j_scol_classes){
				$result.="Erreur � la l'affectation des classes aux comptes scolarit�!<br />";
			}
		}

		// Mise � jour des tables pour le module absences

		$result .= "&nbsp;->Ajout du champ parqui_suivi_eleve_cpe � la table suivi_eleve_cpe<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM suivi_eleve_cpe LIKE 'parqui_suivi_eleve_cpe'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `suivi_eleve_cpe` ADD `parqui_suivi_eleve_cpe` varchar(150) NOT NULL AFTER `eleve_suivi_eleve_cpe`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ heure_suivi_eleve_cpe � la table suivi_eleve_cpe<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM suivi_eleve_cpe LIKE 'heure_suivi_eleve_cpe'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `suivi_eleve_cpe` ADD `heure_suivi_eleve_cpe` time NOT NULL AFTER `date_suivi_eleve_cpe`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ niveau_message_suivi_eleve_cpe � la table suivi_eleve_cpe<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM suivi_eleve_cpe LIKE 'niveau_message_suivi_eleve_cpe'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `suivi_eleve_cpe` ADD `niveau_message_suivi_eleve_cpe` varchar(1) NOT NULL AFTER `komenti_suivi_eleve_cpe`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table edt_classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_classes'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `edt_classes` (`id_edt_classe` int(11) NOT NULL auto_increment, `groupe_edt_classe` int(11) NOT NULL, `prof_edt_classe` varchar(25) NOT NULL, `matiere_edt_classe` varchar(10) NOT NULL, `semaine_edt_classe` varchar(5) NOT NULL, `jour_edt_classe` tinyint(4) NOT NULL, `datedebut_edt_classe` date NOT NULL, `datefin_edt_classe` date NOT NULL, `heuredebut_edt_classe` time NOT NULL, `heurefin_edt_classe` time NOT NULL, `salle_edt_classe` varchar(50) NOT NULL, PRIMARY KEY (`id_edt_classe`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table miseajour<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'miseajour'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `miseajour` (`id_miseajour` int(11) NOT NULL auto_increment, `fichier_miseajour` varchar(250) NOT NULL, `emplacement_miseajour` varchar(250) NOT NULL, `date_miseajour` date NOT NULL, `heure_miseajour` time NOT NULL, PRIMARY KEY  (`id_miseajour`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de valeurs par d�faut dans la table setting, si n�cessaires.<br />";
		//--
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'active_module_msj'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$query = mysql_query("INSERT INTO setting VALUES ('active_module_msj', 'n');");
		//--
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'site_msj_gepi'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$query = mysql_query("INSERT INTO setting VALUES ('site_msj_gepi', 'http://gepi.sylogix.net/releases/');");
		//--
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'rc_module_msj'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$query = mysql_query("INSERT INTO setting VALUES ('rc_module_msj', 'n');");
		//--
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'beta_module_msj'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$query = mysql_query("INSERT INTO setting VALUES ('beta_module_msj', 'n');");
		//--
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'dossier_ftp_gepi'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$query = mysql_query("INSERT INTO setting VALUES ('dossier_ftp_gepi', 'gepi');");



		//==========================================
		// AJOUT� APRES LA RC2
		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_intitule_app'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_intitule_app', 'Appr�ciations/Conseils');");
			$result .= "Initialisation du param�tre bull_intitule_app � 'Appr�ciations/Conseils': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_tel'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_affiche_tel', 'n');");
			$result .= "Initialisation du param�tre bull_affiche_tel � 'n': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_fax'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_affiche_fax', 'n');");
			$result .= "Initialisation du param�tre bull_affiche_fax � 'n': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$result .= "&nbsp;->Cr�ation de la table absences_actions<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'absences_actions'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `absences_actions` (`id_absence_action` int(11) NOT NULL auto_increment, `init_absence_action` char(2) NOT NULL default '', `def_absence_action` varchar(255) NOT NULL default '', PRIMARY KEY  (`id_absence_action`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ action_suivi_eleve_cpe � la table suivi_eleve_cpe<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM suivi_eleve_cpe LIKE 'action_suivi_eleve_cpe'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `suivi_eleve_cpe` ADD `action_suivi_eleve_cpe` varchar(2) NOT NULL AFTER `niveau_message_suivi_eleve_cpe`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Changement du type du champ 'coef' de la table cn_conteneurs<br />";
		$test1 = mysql_fetch_object(mysql_query("SHOW COLUMNS FROM cn_conteneurs LIKE 'coef'"));
		if ($test1->Type == "decimal(2,1)") {
			$query = mysql_query("ALTER TABLE cn_conteneurs CHANGE coef coef DECIMAL(3,1) NOT NULL default '1.0'");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ a d�j� �t� modifi�.</font><br />";
		}

		$result .= "&nbsp;->Changement du type du champ 'ponderation' de la table cn_conteneurs<br />";
		$test1 = mysql_fetch_object(mysql_query("SHOW COLUMNS FROM cn_conteneurs LIKE 'ponderation'"));
		if ($test1->Type == "decimal(2,1)") {
			$query = mysql_query("ALTER TABLE cn_conteneurs CHANGE ponderation ponderation DECIMAL(3,1) NOT NULL default '0.0'");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ a d�j� �t� modifi�.</font><br />";
		}

		$result .= "&nbsp;->Changement du type du champ 'coef' de la table cn_devoirs<br />";
		$test1 = mysql_fetch_object(mysql_query("SHOW COLUMNS FROM cn_devoirs LIKE 'coef'"));
		if ($test1->Type == "decimal(2,1)") {
			$query = mysql_query("ALTER TABLE cn_devoirs CHANGE coef coef decimal(3,1) NOT NULL default '0.0'");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ a d�j� �t� modifi�.</font><br />";
		}

		//ERIC Bulletion PDF
		$result .= "&nbsp;->Cr�ation de la table model_bulletin<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'model_bulletin'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `model_bulletin` (
  `id_model_bulletin` int(11) NOT NULL auto_increment,
  `nom_model_bulletin` varchar(100) NOT NULL,
  `active_bloc_datation` decimal(4,0) NOT NULL,
  `active_bloc_eleve` tinyint(4) NOT NULL,
  `active_bloc_adresse_parent` tinyint(4) NOT NULL,
  `active_bloc_absence` tinyint(4) NOT NULL,
  `active_bloc_note_appreciation` tinyint(4) NOT NULL,
  `active_bloc_avis_conseil` tinyint(4) NOT NULL,
  `active_bloc_chef` tinyint(4) NOT NULL,
  `active_photo` tinyint(4) NOT NULL,
  `active_coef_moyenne` tinyint(4) NOT NULL,
  `active_nombre_note` tinyint(4) NOT NULL,
  `active_nombre_note_case` tinyint(4) NOT NULL,
  `active_moyenne` tinyint(4) NOT NULL,
  `active_moyenne_eleve` tinyint(4) NOT NULL,
  `active_moyenne_classe` tinyint(4) NOT NULL,
  `active_moyenne_min` tinyint(4) NOT NULL,
  `active_moyenne_max` tinyint(4) NOT NULL,
  `active_regroupement_cote` tinyint(4) NOT NULL,
  `active_entete_regroupement` tinyint(4) NOT NULL,
  `active_moyenne_regroupement` tinyint(4) NOT NULL,
  `active_rang` tinyint(4) NOT NULL,
  `active_graphique_niveau` tinyint(4) NOT NULL,
  `active_appreciation` tinyint(4) NOT NULL,
  `affiche_doublement` tinyint(4) NOT NULL,
  `affiche_date_naissance` tinyint(4) NOT NULL,
  `affiche_dp` tinyint(4) NOT NULL,
  `affiche_nom_court` tinyint(4) NOT NULL,
  `affiche_effectif_classe` tinyint(4) NOT NULL,
  `affiche_numero_impression` tinyint(4) NOT NULL,
  `caractere_utilse` varchar(20) NOT NULL,
  `X_parent` float NOT NULL,
  `Y_parent` float NOT NULL,
  `X_eleve` float NOT NULL,
  `Y_eleve` float NOT NULL,
  `cadre_eleve` tinyint(4) NOT NULL,
  `X_datation_bul` float NOT NULL,
  `Y_datation_bul` float NOT NULL,
  `cadre_datation_bul` tinyint(4) NOT NULL,
  `hauteur_info_categorie` float NOT NULL,
  `X_note_app` float NOT NULL,
  `Y_note_app` float NOT NULL,
  `longeur_note_app` float NOT NULL,
  `hauteur_note_app` float NOT NULL,
  `largeur_coef_moyenne` float NOT NULL,
  `largeur_nombre_note` float NOT NULL,
  `largeur_d_une_moyenne` float NOT NULL,
  `largeur_niveau` float NOT NULL,
  `largeur_rang` float NOT NULL,
  `X_absence` float NOT NULL,
  `Y_absence` float NOT NULL,
  `hauteur_entete_moyenne_general` float NOT NULL,
  `X_avis_cons` float NOT NULL,
  `Y_avis_cons` float NOT NULL,
  `longeur_avis_cons` float NOT NULL,
  `hauteur_avis_cons` float NOT NULL,
  `cadre_avis_cons` tinyint(4) NOT NULL,
  `X_sign_chef` float NOT NULL,
  `Y_sign_chef` float NOT NULL,
  `longeur_sign_chef` float NOT NULL,
  `hauteur_sign_chef` float NOT NULL,
  `cadre_sign_chef` tinyint(4) NOT NULL,
  `affiche_filigrame` tinyint(4) NOT NULL,
  `texte_filigrame` varchar(100) NOT NULL,
  `affiche_logo_etab` tinyint(4) NOT NULL,
  `entente_mel` tinyint(4) NOT NULL,
  `entente_tel` tinyint(4) NOT NULL,
  `entente_fax` tinyint(4) NOT NULL,
  `L_max_logo` tinyint(4) NOT NULL,
  `H_max_logo` tinyint(4) NOT NULL,
  `toute_moyenne_meme_col` tinyint(4) NOT NULL,
  `active_reperage_eleve` tinyint(4) NOT NULL,
  `couleur_reperage_eleve1` smallint(6) NOT NULL,
  `couleur_reperage_eleve2` smallint(6) NOT NULL,
  `couleur_reperage_eleve3` smallint(6) NOT NULL,
  `couleur_categorie_entete` tinyint(4) NOT NULL,
  `couleur_categorie_entete1` smallint(6) NOT NULL,
  `couleur_categorie_entete2` smallint(6) NOT NULL,
  `couleur_categorie_entete3` smallint(6) NOT NULL,
  `couleur_categorie_cote` tinyint(4) NOT NULL,
  `couleur_categorie_cote1` smallint(6) NOT NULL,
  `couleur_categorie_cote2` smallint(6) NOT NULL,
  `couleur_categorie_cote3` smallint(6) NOT NULL,
  `couleur_moy_general` tinyint(4) NOT NULL,
  `couleur_moy_general1` smallint(6) NOT NULL,
  `couleur_moy_general2` smallint(6) NOT NULL,
  `couleur_moy_general3` smallint(6) NOT NULL,
  `titre_entete_matiere` varchar(50) NOT NULL,
  `titre_entete_coef` varchar(20) NOT NULL,
  `titre_entete_nbnote` varchar(20) NOT NULL,
  `titre_entete_rang` varchar(20) NOT NULL,
  `titre_entete_appreciation` varchar(50) NOT NULL,
  `active_coef_sousmoyene` tinyint(4) NOT NULL,
  `arrondie_choix` float NOT NULL,
  `nb_chiffre_virgule` tinyint(4) NOT NULL,
  `chiffre_avec_zero` tinyint(4) NOT NULL,
  `autorise_sous_matiere` tinyint(4) NOT NULL,
  `affichage_haut_responsable` tinyint(4) NOT NULL,
  `entete_model_bulletin` tinyint(4) NOT NULL,
  `ordre_entete_model_bulletin` tinyint(4) NOT NULL,
  `affiche_etab_origine` tinyint(4) NOT NULL,
  `imprime_pour` tinyint(4) NOT NULL,
  `largeur_matiere` FLOAT NOT NULL,
  PRIMARY KEY  (`id_model_bulletin`)
);";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";


			//ERIC Mise � jour des champs s'ils n'existent pas (rajout par rapport � la version pr�c�dente.
			$result .= "&nbsp;->Ajout du champ entete_model_bulletin � la table model_bulletin <br />";
			$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'entete_model_bulletin'"));
			if ($test1 == 0) {
				$query1 = mysql_query("ALTER TABLE `model_bulletin` ADD `entete_model_bulletin` tinyint(4) NOT NULL AFTER `affichage_haut_responsable`");
				if ($query1) {
					$result .= "<font color=\"green\">Ok !</font><br />";

					//maintenant que le champs existe, mise � jour des donn�es pour les 3 types de  bulletin fourni
					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET entete_model_bulletin='1' WHERE nom_model_bulletin='Standard'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre entete_model_bulletin � 1 pour le modele standard<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre entete_model_bulletin � 1 pour le modele standard<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET entete_model_bulletin='1' WHERE nom_model_bulletin='Standard avec photo'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre entete_model_bulletin � 1 pour le modele Standard avec photo<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre entete_model_bulletin � 1 pour le modele Standard avec photo<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET entete_model_bulletin='2' WHERE nom_model_bulletin='Affiche tout'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre entete_model_bulletin � 2 pour le modele Affiche tout<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre entete_model_bulletin � 2 pour le modele Affiche tout<font color=\"red\">Erreur !</font><br />";
					}

				} else {
					$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
				}
			} else {
				$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
			}

			$result .= "&nbsp;->Ajout du champ ordre_entete_model_bulletin � la table model_bulletin<br />";
			$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'ordre_entete_model_bulletin'"));
			if ($test1 == 0) {
				$query2 = mysql_query("ALTER TABLE `model_bulletin` ADD `ordre_entete_model_bulletin` tinyint(4) NOT NULL AFTER `entete_model_bulletin`");
				if ($query2) {
					$result .= "<font color=\"green\">Ok !</font><br />";

					//maintenant que le champs existe, mise � jour des donn�es pour les 3 types de  bulletin fourni
					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET ordre_entete_model_bulletin='1' WHERE nom_model_bulletin='Standard'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre ordre_entete_model_bulletin � 1 pour le modele standard<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre ordre_entete_model_bulletin � 1 pour le modele standard<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET ordre_entete_model_bulletin='1' WHERE nom_model_bulletin='Standard avec photo'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre ordre_entete_model_bulletin � 1 pour le modele Standard avec photo<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre ordre_entete_model_bulletin � 1 pour le modele Standard avec photo<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET ordre_entete_model_bulletin='1' WHERE nom_model_bulletin='Affiche tout'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre ordre_entete_model_bulletin � 1 pour le modele Affiche tout<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre ordre_entete_model_bulletin � 1 pour le modele Affiche tout<font color=\"red\">Erreur !</font><br />";
					}

				} else {
					$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
				}
			} else {
				$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
			}

			$result .= "&nbsp;->Ajout du champ affiche_etab_origine � la table model_bulletin<br />";
			$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'affiche_etab_origine'"));
			if ($test1 == 0) {
				$query3 = mysql_query("ALTER TABLE `model_bulletin` ADD `affiche_etab_origine` tinyint(4) NOT NULL AFTER `ordre_entete_model_bulletin`");
				if ($query3) {
					$result .= "<font color=\"green\">Ok !</font><br />";

					//maintenant que le champs existe, mise � jour des donn�es pour les 3 types de  bulletin fourni
					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET affiche_etab_origine='0' WHERE nom_model_bulletin='Standard'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre affiche_etab_origine � 0 pour le modele standard<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre affiche_etab_origine � 0 pour le modele standard<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET affiche_etab_origine='0' WHERE nom_model_bulletin='Standard avec photo'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre affiche_etab_origine � 0 pour le modele Standard avec photo<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre affiche_etab_origine � 0 pour le modele Standard avec photo<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET affiche_etab_origine='1' WHERE nom_model_bulletin='Affiche tout'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre affiche_etab_origine � 1 pour le modele Affiche tout<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre affiche_etab_origine � 1 pour le modele Affiche tout<font color=\"red\">Erreur !</font><br />";
					}

				} else {
					$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
				}
			} else {
				$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
			}

			$result .= "&nbsp;->Ajout du champ imprime_pour � la table model_bulletin<br />";
			$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'imprime_pour'"));
			if ($test1 == 0) {
				$query4 = mysql_query("ALTER TABLE `model_bulletin` ADD `imprime_pour` tinyint(4) NOT NULL AFTER `affiche_etab_origine`");
				if ($query4) {
					$result .= "<font color=\"green\">Ok !</font><br />";

					//maintenant que le champs existe, mise � jour des donn�es pour les 3 types de  bulletin fourni
					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET imprime_pour='0' WHERE nom_model_bulletin='Standard'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre imprime_pour � 0 pour le modele standard<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre imprime_pour � 0 pour le modele standard<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET imprime_pour='0' WHERE nom_model_bulletin='Standard avec photo'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre imprime_pour � 0 pour le modele Standard avec photo<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre imprime_pour � 0 pour le modele Standard avec photo<font color=\"red\">Erreur !</font><br />";
					}

					$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET imprime_pour='1' WHERE nom_model_bulletin='Affiche tout'");
					if(!$update_entete_model_bulletin){
						$result.="-&gt; Mise � jour du param�tre imprime_pour � 1 pour le modele Affiche tout<font color=\"green\">Ok !</font><br />";
					} else{
						$result.="-&gt; Mise � jour du param�tre imprime_pour � 1 pour le modele Affiche tout<font color=\"red\">Erreur !</font><br />";
					}

				} else {
					$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
				}
			} else {
				$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
			}

		}


		$result .= "&nbsp;->Ajout du champ `largeur_matiere` � la table model_bulletin <br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'largeur_matiere'"));
		if ($test1 == 0) {
			$query1 = mysql_query("ALTER TABLE `model_bulletin` ADD `largeur_matiere` FLOAT NOT NULL AFTER `imprime_pour`");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";

				//maintenant que le champs existe, mise � jour des donn�es pour les 3 types de  bulletin fourni
				$update_entete_model_bulletin=mysql_query("UPDATE model_bulletin SET `largeur_matiere`='40' WHERE 1");
				if($update_entete_model_bulletin){
					$result.="-&gt; Mise � jour du param�tre `largeur_matiere` � 40 pour tous les mod�les<font color=\"green\">Ok !</font><br />";
				} else{
					$result.="-&gt; Mise � jour du param�tre `largeur_matiere` � 40 pour tous les mod�les<font color=\"red\">Erreur !</font><br />";
				}

			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Ajout du mod�le 'Standard' de bulletin PDF<br />";
		$sql="SELECT id_model_bulletin FROM model_bulletin WHERE (nom_model_bulletin='Standard')";
		$test1=mysql_query($sql);
		if(mysql_num_rows($test1)==0){
			$sql="INSERT INTO model_bulletin VALUES('', 'Standard', 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 'Arial', 110, 40, 5, 40, 1, 110, 5, 1, 5, 5, 72, 200, 175, 8, 8, 10, 18, 5, 5, 246.3, 5, 5, 250, 130, 37, 1, 138, 250, 67, 37, 0, 1, 'DUPLICATA INTERNET', 1, 1, 1, 1, 75, 75, 0, 1, 255, 255, 207, 1, 239, 239, 239, 1, 239, 239, 239, 1, 239, 239, 239, 'Mati�re', 'coef.', 'nb. n.', 'rang', 'Appr�ciation / Conseils', 0, 0.01, 2, 0, 1, 1, 1, 1, 0, 0, 40);";
			$insert=mysql_query($sql);
			if ($insert) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le mod�le existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du mod�le 'Standard avec photo' de bulletin PDF<br />";
		$sql="SELECT id_model_bulletin FROM model_bulletin WHERE (nom_model_bulletin='Standard avec photo')";
		$test1=mysql_query($sql);
		if(mysql_num_rows($test1)==0){
			$sql="INSERT INTO model_bulletin VALUES('', 'Standard avec photo', 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 'Arial', 110, 40, 5, 40, 1, 110, 5, 1, 5, 5, 72, 200, 175, 8, 8, 10, 18, 5, 5, 246.3, 5, 5, 250, 130, 37, 1, 138, 250, 67, 37, 0, 1, 'DUPLICATA INTERNET', 1, 1, 1, 1, 75, 75, 0, 1, 255, 255, 207, 1, 239, 239, 239, 1, 239, 239, 239, 1, 239, 239, 239, 'Mati�re', 'coef.', 'nb. n.', 'rang', 'Appr�ciation / Conseils', 0, 0, 2, 0, 1, 1, 1, 1, 0, 0, 40);";
			$insert=mysql_query($sql);
			if ($insert) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le mod�le existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du mod�le 'Affiche tout' de bulletin PDF<br />";
		$sql="SELECT id_model_bulletin FROM model_bulletin WHERE (nom_model_bulletin='Affiche tout')";
		$test1=mysql_query($sql);
		if(mysql_num_rows($test1)==0){
			$sql="INSERT INTO model_bulletin VALUES('', 'Affiche tout', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 'Arial', 110, 40, 5, 40, 1, 110, 5, 1, 5, 5, 72, 200, 175, 8, 8, 10, 16.5, 6.5, 5, 246.3, 5, 5, 250, 130, 37, 1, 138, 250, 67, 37, 1, 1, 'DUPLICATA INTERNET', 1, 1, 1, 1, 75, 75, 1, 1, 255, 255, 207, 1, 239, 239, 239, 1, 239, 239, 239, 1, 239, 239, 239, 'Mati�re', 'coef.', 'nb. n.', 'rang', 'Appr�ciation / Conseils', 1, 0.01, 2, 0, 1, 1, 2, 1, 1, 1, 40);";
			$insert=mysql_query($sql);
			if ($insert) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le mod�le existe d�j�.</font><br />";
		}




		// Fin modif ERIC Bulletin PDF


		$result .= "&nbsp;->Ajout (si besoin) du param�tre de m�morisation du mode de sauvegarde<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'mode_sauvegarde'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('mode_sauvegarde', 'gepi');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) du param�tre de m�morisation de droit d'acc�s des profs � tous les relev�s de notes de toutes les classes<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveProfToutesClasses'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveProfToutesClasses', 'no');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'choix_bulletin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('choix_bulletin', '2');");
			$result .= "Initialisation du param�tre choix_bulletin � '2': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'min_max_moyclas'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('min_max_moyclas', '0');");
			$result .= "Initialisation du param�tre min_max_moyclas � '0': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}


		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_mention_nom_court'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_mention_nom_court', 'yes');");
			$result .= "Initialisation du param�tre bull_mention_nom_court � yes: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_affiche_eleve_une_ligne'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_affiche_eleve_une_ligne', 'yes');");
			$result .= "Initialisation du param�tre bull_affiche_eleve_une_ligne � yes: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}




		$result .= "&nbsp;->Ajout du champ suivi_definie_periode � la table absences_creneaux<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM absences_creneaux LIKE 'suivi_definie_periode'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `absences_creneaux` ADD `suivi_definie_periode` tinyint(4) NOT NULL AFTER `heurefin_definie_periode`");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$test2=mysql_query("SELECT 1=1 FROM absences_creneaux");
			if(mysql_num_rows($test2)>0){
				$result .= "Initialisation � 1 de la valeur du champ pour les enregistrements existants: ";
				$sql="UPDATE absences_creneaux SET suivi_definie_periode='1'";
				$update_absences_creneaux=mysql_query($sql);
				if($update_absences_creneaux){
					$result .= "<font color=\"green\">Ok !</font><br />";
				}
				else{
					$result .= "<font color=\"red\">Erreur !</font><br />";
				}
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ type_creneaux � la table absences_creneaux<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM absences_creneaux LIKE 'type_creneaux'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `absences_creneaux` ADD `type_creneaux` VARCHAR( 15 ) NOT NULL DEFAULT 'cours' ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		// Ajout d'un champ num_semaines_etab pour la gestion des semaines propre � chaque �tablissement
		$result .= "&nbsp;->Ajout du champ num_semaines_etab � la table edt_semaines<br />";
		$test1 = mysql_query("SHOW COLUMNS FROM edt_semaines LIKE 'num_semaines_etab'");
		if (mysql_num_rows($test1) == 0) {
			$query = mysql_query("ALTER TABLE `edt_semaines` ADD `num_semaines_etab` INT( 11 ) NOT NULL DEFAULT '0';");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}



		$result .= "&nbsp;->Ajout du champ support_suivi_eleve_cpe � la table suivi_eleve_cpe<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM suivi_eleve_cpe LIKE 'support_suivi_eleve_cpe'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `suivi_eleve_cpe` ADD `support_suivi_eleve_cpe` tinyint(4) NOT NULL AFTER `action_suivi_eleve_cpe`");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ support_suivi_eleve_cpe � la table suivi_eleve_cpe<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM suivi_eleve_cpe LIKE 'courrier_suivi_eleve_cpe'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `suivi_eleve_cpe` ADD `courrier_suivi_eleve_cpe` int(11) NOT NULL AFTER `support_suivi_eleve_cpe`");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table edt_dates_special<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_dates_special'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `edt_dates_special` (
`id_edt_date_special` int(11) NOT NULL auto_increment,
`nom_edt_date_special` varchar(200) NOT NULL,
`debut_edt_date_special` date NOT NULL,
`fin_edt_date_special` date NOT NULL,
PRIMARY KEY  (`id_edt_date_special`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Cr�ation de la table edt_semaines<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_semaines'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `edt_semaines` (
`id_edt_semaine` int(11) NOT NULL auto_increment,
`num_edt_semaine` int(11) NOT NULL default '0',
`type_edt_semaine` varchar(10) NOT NULL default '',
PRIMARY KEY  (`id_edt_semaine`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Insertion des semaines edt_semaines ";
		$cpt=1;
		while($cpt<=52){
			$sql="SELECT * FROM edt_semaines WHERE num_edt_semaine='$cpt'";
			//echo "<p>$sql</p>";
			$test1 = mysql_num_rows(mysql_query($sql));
			if ($test1 == 0) {
				$sql="INSERT INTO `edt_semaines` VALUES ('', $cpt, 'A', '0');";
				$query1 = mysql_query($sql);
				if ($query1) {
					$result .= "<font color=\"green\"> $cpt </font>";
				} else {
					$result .= "<font color=\"red\"> $cpt </font>";
				}
			}
			$cpt++;
		}
		$result.="<br />\n";


		$result .= "&nbsp;->Cr�ation de la table etiquettes_formats<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'etiquettes_formats'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `etiquettes_formats` (
`id_etiquette_format` int(11) NOT NULL auto_increment,
`nom_etiquette_format` varchar(150) NOT NULL,
`xcote_etiquette_format` float NOT NULL,
`ycote_etiquette_format` float NOT NULL,
`espacementx_etiquette_format` float NOT NULL,
`espacementy_etiquette_format` float NOT NULL,
`largeur_etiquette_format` float NOT NULL,
`hauteur_etiquette_format` float NOT NULL,
`nbl_etiquette_format` tinyint(4) NOT NULL,
`nbh_etiquette_format` tinyint(4) NOT NULL,
PRIMARY KEY  (`id_etiquette_format`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$req_test= mysql_query("SELECT * FROM etiquettes_formats WHERE nom_etiquette_format = 'Avery - A4 - 63,5 x 33,9 mm'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO `etiquettes_formats` VALUES (1, 'Avery - A4 - 63,5 x 33,9 mm', 2, 2, 5, 5, 63.5, 33, 3, 8);");
			$result .= "Insertion du format 'Avery - A4 - 63,5 x 33,9 mm': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}


		$result .= "&nbsp;->Cr�ation de la table horaires_etablissement<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'horaires_etablissement'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `horaires_etablissement` (
`id_horaire_etablissement` int(11) NOT NULL auto_increment,
`date_horaire_etablissement` date NOT NULL,
`jour_horaire_etablissement` varchar(15) NOT NULL,
`ouverture_horaire_etablissement` time NOT NULL,
`fermeture_horaire_etablissement` time NOT NULL,
`pause_horaire_etablissement` time NOT NULL,
`ouvert_horaire_etablissement` tinyint(4) NOT NULL,
PRIMARY KEY  (`id_horaire_etablissement`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$req_test= mysql_query("SELECT * FROM horaires_etablissement");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$result .= "Insertion des horaires �tablissement<br />\n";
			$query = mysql_query("INSERT INTO `horaires_etablissement` VALUES ('', '0000-00-00', 'lundi', '08:00:00', '17:30:00', '00:45:00', 1);");
			$result .= "Lundi: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `horaires_etablissement` VALUES ('', '0000-00-00', 'mardi', '08:00:00', '17:30:00', '00:45:00', 1);");
			$result .= "Mardi: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `horaires_etablissement` VALUES ('', '0000-00-00', 'mercredi', '08:00:00', '12:00:00', '00:00:00', 1);");
			$result .= "Mercredi: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `horaires_etablissement` VALUES ('', '0000-00-00', 'jeudi', '08:00:00', '17:30:00', '00:45:00', 1);");
			$result .= "Jeudi: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `horaires_etablissement` VALUES ('', '0000-00-00', 'vendredi', '08:00:00', '17:30:00', '00:45:00', 1);");
			$result .= "Vendredi: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}


		$result .= "&nbsp;->Cr�ation de la table lettres_cadres<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'lettres_cadres'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `lettres_cadres` (
`id_lettre_cadre` int(11) NOT NULL auto_increment,
`nom_lettre_cadre` varchar(150) NOT NULL,
`x_lettre_cadre` float NOT NULL,
`y_lettre_cadre` float NOT NULL,
`l_lettre_cadre` float NOT NULL,
`h_lettre_cadre` float NOT NULL,
`texte_lettre_cadre` text NOT NULL,
`encadre_lettre_cadre` tinyint(4) NOT NULL,
`couleurdefond_lettre_cadre` varchar(11) NOT NULL,
PRIMARY KEY  (`id_lettre_cadre`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$req_test= mysql_query("SELECT * FROM lettres_cadres");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$result .= "Insertion des lettres_cadres<br />\n";
			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (1, 'adresse responsable', 100, 40, 100, 5, 'A l\'attention de\r\n<civilitee_court_responsable> <nom_responsable> <prenom_responsable>\r\n<adresse_responsable>\r\n<cp_responsable> <commune_responsable>\r\n', 0, '||');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (2, 'adresse etablissement', 0, 0, 0, 0, '', 0, '');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (3, 'datation', 0, 0, 0, 0, '', 0, '');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (4, 'corp avertissement', 10, 70, 0, 5, '<u>Objet: </u> <g>Avertissement</g>\r\n\r\n\r\n<nom_civilitee_long>,\r\n\r\nJe me vois dans l\'obligation de donner un <b>AVERTISSEMENT</b>\r\n\r\n� <g><nom_eleve> <prenom_eleve></g> �l�ve de la classe <g><classe_eleve></g>.\r\n\r\n\r\npour la raison suivante : <g><sujet_eleve></g>\r\n\r\n<remarque_eleve>\r\n\r\n\r\n\r\nComme le pr�voit le r�glement int�rieur de l\'�tablissement, il pourra �tre sanctionn� � partir de ce jour.\r\nSanction(s) possible(s) :\r\n\r\n\r\n\r\n\r\nJe vous remercie de me renvoyer cet exemplaire apr�s l\'avoir dat� et sign�.\r\nVeuillez agr�er <nom_civilitee_long> <nom_responsable> l\'assurance de ma consid�ration distingu�e.\r\n\r\n\r\n\r\nDate et signatures des parents :	', 0, '||');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (5, 'corp blame', 10, 70, 0, 5, '<u>Objet</u>: <g>Bl�me</g>\r\n\r\n\r\n<nom_civilitee_long>\r\n\r\nJe me vois dans l\'obligation de donner un BLAME \r\n\r\n� <g><nom_eleve> <prenom_eleve></g> �l�ve de la classe <g><classe_eleve></g>.\r\n\r\nDemand� par: <g><courrier_demande_par></g>\r\n\r\npour la raison suivante: <g><raison></g>\r\n\r\n<remarque>\r\n\r\nJe vous remercie de me renvoyer cet exemplaire apr�s l\'avoir dat� et sign�.\r\nVeuillez agr�er <g><nom_civilitee_long> <nom_responsable></g> l\'assurance de ma consid�ration distingu�e.\r\n\r\n<u>Date et signatures des parents:</u>\r\n\r\n\r\n\r\n\r\n\r\nNous demandons un entretien avec la personne ayant demand� la sanction OUI / NON.\r\n(La prise de rendez-vous est � votre initiative)\r\n', 0, '||');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (6, 'corp convocation parents', 10, 70, 0, 5, '<u>Objet</u>: <g>Convocation des parents</g>\r\n\r\n\r\n<nom_civilitee_long>,\r\n\r\nVous �tes pri� de prendre contact avec le Conseiller Principal d\'Education dans les plus brefs d�lais, au sujet de <g><nom_eleve> <prenom_eleve></g> inscrit en classe de <g><classe_eleve></g>.\r\n\r\npour le motif suivant:\r\n\r\n<remarque>\r\n\r\n\r\n\r\nSans nouvelle de votre part avant le ........................................., je serai dans l\'obligation de proc�der � la descolarisation de l\'�l�ve, avec les cons�quences qui en r�sulteront, jusqu\'� votre rencontre.\r\n\r\n\r\nVeuillez agr�er <g><nom_civilitee_long> <nom_responsable></g> l\'assurance de ma consid�ration distingu�e.', 0, '||');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (7, 'corp exclusion', 10, 70, 0, 5, '<u>Objet: </u> <g>Sanction - Exclusion de l\'�tablissement</g>\r\n\r\n\r\n<nom_civilitee_long>,\r\n\r\nPar la pr�sente, je tiens � vous signaler que <nom_eleve>\r\n\r\ninscrit en classe de  <classe_eleve>\r\n\r\n\r\ns\'�tant rendu coupable des faits suivants : \r\n\r\n<remarque>\r\n\r\n\r\n\r\nEst exclu de l\'�tablissement,\r\n� compter du: <b><date_debut></b> � <b><heure_debut></b>,\r\njusqu\'au: <b><date_fin></b> � <b><heure_fin></b>.\r\n\r\n\r\nIl devra se pr�senter, au bureau de la Vie Scolaire \r\n\r\nle ....................................... � ....................................... ACCOMPAGNE DE SES PARENTS.\r\n\r\n\r\n\r\n\r\nVeuillez agr�er &lt;TYPEPARENT&gt; &lt;NOMPARENT&gt; l\'assurance de ma consid�ration distingu�e.', 0, '||');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (8, 'corp demande justificatif absence', 10, 70, 0, 5, '<u>Objet: </u> <g>Demande de justificatif d\'absence</g>\r\n\r\n\r\n<civilitee_long_responsable>,\r\n\r\nJ\'ai le regret de vous informer que <b><nom_eleve> <prenom_eleve></b>, �l�ve en classe de <b><classe_eleve></b> n\'a pas assist� au(x) cours:\r\n\r\n<liste>\r\n\r\nJe vous prie de bien vouloir me faire conna�tre le motif de son absence.\r\n\r\nPour permettre un contr�le efficace des pr�sences, toute absence d\'un �l�ve doit �tre justifi�e par sa famille, le jour m�me soit par t�l�phone, soit par �crit, soit par fax.\r\n\r\nAvant de regagner les cours, l\'�l�ve absent devra se pr�senter au bureau du Conseiller Principal d\'Education muni de son carnet de correspondance avec un justificatif sign� des parents.\r\n\r\nVeuillez agr�er <civilitee_long_responsable> <nom_responsable>, l\'assurance de ma consid�ration distingu�e.\r\n                                               \r\nCPE\r\n<civilitee_long_cpe> <nom_cpe> <prenom_cpe>\r\n\r\nPri�re de renvoyer, par retour du courrier, le pr�sent avis sign� des parents :\r\n\r\nMotif de l\'absence : \r\n________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________________\r\n\r\n\r\n\r\nDate et signatures des parents :  \r\n', 0, '||');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}

			$query = mysql_query("INSERT INTO `lettres_cadres` VALUES (10, 'signature', 100, 180, 0, 5, '<b><courrier_signe_par_fonction></b>,\r\n<courrier_signe_par>\r\n', 0, '||');");
			$result .= "- ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}


		$result .= "&nbsp;->Cr�ation de la table lettres_suivis<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'lettres_suivis'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `lettres_suivis` (
`id_lettre_suivi` int(11) NOT NULL auto_increment,
`lettresuitealettren_lettre_suivi` int(11) NOT NULL,
`quirecois_lettre_suivi` varchar(50) NOT NULL,
`partde_lettre_suivi` varchar(200) NOT NULL,
`partdenum_lettre_suivi` text NOT NULL,
`quiemet_lettre_suivi` varchar(150) NOT NULL,
`emis_date_lettre_suivi` date NOT NULL,
`emis_heure_lettre_suivi` time NOT NULL,
`quienvoi_lettre_suivi` varchar(150) NOT NULL,
`envoye_date_lettre_suivi` date NOT NULL,
`envoye_heure_lettre_suivi` time NOT NULL,
`type_lettre_suivi` int(11) NOT NULL,
`quireception_lettre_suivi` varchar(150) NOT NULL,
`reponse_date_lettre_suivi` date NOT NULL,
`reponse_remarque_lettre_suivi` varchar(250) NOT NULL,
`statu_lettre_suivi` varchar(20) NOT NULL,
PRIMARY KEY  (`id_lettre_suivi`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Cr�ation de la table lettres_tcs<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'lettres_tcs'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `lettres_tcs` (
`id_lettre_tc` int(11) NOT NULL auto_increment,
`type_lettre_tc` int(11) NOT NULL,
`cadre_lettre_tc` int(11) NOT NULL,
`x_lettre_tc` float NOT NULL,
`y_lettre_tc` float NOT NULL,
`l_lettre_tc` float NOT NULL,
`h_lettre_tc` float NOT NULL,
`encadre_lettre_tc` int(1) NOT NULL,
PRIMARY KEY  (`id_lettre_tc`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}



		$req_test= mysql_query("SELECT * FROM lettres_tcs");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$result .= "Insertion dans lettres_tcs: \n";
			$sql="INSERT INTO `lettres_tcs` (`id_lettre_tc`, `type_lettre_tc`, `cadre_lettre_tc`, `x_lettre_tc`, `y_lettre_tc`, `l_lettre_tc`, `h_lettre_tc`, `encadre_lettre_tc`) VALUES (1, 3, 0, 0, 0, 0, 0, 0),
(2, 3, 0, 0, 0, 0, 0, 0),
(3, 3, 0, 0, 0, 0, 0, 0),
(4, 3, 0, 0, 0, 0, 0, 0),
(5, 3, 0, 0, 0, 0, 0, 0),
(6, 3, 0, 0, 0, 0, 0, 0),
(7, 3, 0, 0, 0, 0, 0, 0),
(8, 3, 0, 0, 0, 0, 0, 0),
(9, 3, 0, 0, 0, 0, 0, 0),
(10, 3, 0, 0, 0, 0, 0, 0),
(11, 3, 0, 0, 0, 0, 0, 0),
(12, 3, 0, 0, 0, 0, 0, 0),
(13, 3, 0, 0, 0, 0, 0, 0),
(14, 3, 0, 0, 0, 0, 0, 0),
(15, 3, 0, 0, 0, 0, 0, 0),
(16, 3, 0, 0, 0, 0, 0, 0),
(17, 3, 0, 0, 0, 0, 0, 0),
(18, 3, 0, 0, 0, 0, 0, 0),
(19, 3, 0, 0, 0, 0, 0, 0),
(20, 3, 0, 0, 0, 0, 0, 0),
(21, 3, 0, 0, 0, 0, 0, 0),
(22, 3, 0, 0, 0, 0, 0, 0),
(23, 3, 0, 0, 0, 0, 0, 0),
(24, 3, 0, 0, 0, 0, 0, 0),
(25, 3, 0, 0, 0, 0, 0, 0),
(26, 3, 0, 0, 0, 0, 0, 0),
(27, 3, 0, 0, 0, 0, 0, 0),
(28, 3, 0, 0, 0, 0, 0, 0),
(29, 3, 0, 0, 0, 0, 0, 0),
(30, 3, 0, 0, 0, 0, 0, 0),
(31, 3, 0, 0, 0, 0, 0, 0),
(32, 3, 0, 0, 0, 0, 0, 0),
(33, 3, 0, 0, 0, 0, 0, 0),
(34, 3, 0, 0, 0, 0, 0, 0),
(35, 3, 0, 0, 0, 0, 0, 0),
(36, 3, 0, 0, 0, 0, 0, 0),
(37, 3, 0, 0, 0, 0, 0, 0),
(38, 3, 0, 0, 0, 0, 0, 0),
(39, 3, 0, 0, 0, 0, 0, 0),
(40, 3, 0, 0, 0, 0, 0, 0),
(41, 3, 0, 0, 0, 0, 0, 0),
(42, 3, 0, 0, 0, 0, 0, 0),
(43, 3, 0, 0, 0, 0, 0, 0),
(44, 3, 0, 0, 0, 0, 0, 0),
(45, 3, 0, 0, 0, 0, 0, 0),
(46, 3, 0, 0, 0, 0, 0, 0),
(47, 3, 0, 0, 0, 0, 0, 0),
(48, 3, 0, 0, 0, 0, 0, 0),
(49, 3, 0, 0, 0, 0, 0, 0),
(50, 3, 0, 0, 0, 0, 0, 0),
(51, 3, 0, 0, 0, 0, 0, 0),
(52, 3, 0, 0, 0, 0, 0, 0),
(53, 3, 0, 0, 0, 0, 0, 0),
(56, 3, 1, 100, 40, 100, 5, 0),
(57, 3, 4, 10, 70, 0, 5, 0),
(58, 1, 0, 0, 0, 0, 0, 0),
(59, 1, 0, 0, 0, 0, 0, 0),
(60, 1, 0, 0, 0, 0, 0, 0),
(61, 1, 0, 0, 0, 0, 0, 0),
(62, 1, 0, 0, 0, 0, 0, 0),
(63, 1, 0, 0, 0, 0, 0, 0),
(64, 1, 0, 0, 0, 0, 0, 0),
(65, 1, 1, 100, 40, 100, 5, 0),
(66, 1, 5, 10, 70, 0, 5, 0),
(68, 2, 1, 100, 40, 100, 5, 0),
(69, 2, 6, 10, 70, 0, 5, 0),
(70, 4, 1, 100, 40, 100, 5, 0),
(71, 4, 7, 10, 70, 0, 5, 0),
(72, 6, 0, 0, 0, 0, 0, 0),
(73, 6, 0, 0, 0, 0, 0, 0),
(74, 6, 0, 0, 0, 0, 0, 0),
(75, 6, 0, 0, 0, 0, 0, 0),
(76, 6, 0, 0, 0, 0, 0, 0),
(77, 6, 0, 0, 0, 0, 0, 0),
(78, 6, 0, 0, 0, 0, 0, 0),
(79, 6, 0, 0, 0, 0, 0, 0),
(80, 6, 0, 0, 0, 0, 0, 0),
(81, 6, 0, 0, 0, 0, 0, 0),
(82, 6, 0, 0, 0, 0, 0, 0),
(83, 6, 0, 0, 0, 0, 0, 0),
(84, 6, 0, 0, 0, 0, 0, 0),
(85, 6, 0, 0, 0, 0, 0, 0),
(86, 6, 0, 0, 0, 0, 0, 0),
(87, 6, 0, 0, 0, 0, 0, 0),
(88, 6, 0, 0, 0, 0, 0, 0),
(89, 6, 1, 100, 40, 100, 5, 0),
(90, 6, 8, 10, 70, 0, 5, 0),
(91, 7, 0, 0, 0, 0, 0, 0),
(92, 7, 0, 0, 0, 0, 0, 0),
(93, 7, 0, 0, 0, 0, 0, 0),
(94, 7, 0, 0, 0, 0, 0, 0),
(95, 7, 0, 0, 0, 0, 0, 0),
(96, 7, 0, 0, 0, 0, 0, 0),
(97, 7, 0, 0, 0, 0, 0, 0),
(98, 7, 0, 0, 0, 0, 0, 0),
(99, 7, 0, 0, 0, 0, 0, 0),
(100, 7, 0, 0, 0, 0, 0, 0),
(101, 7, 0, 0, 0, 0, 0, 0),
(102, 7, 0, 0, 0, 0, 0, 0),
(103, 7, 0, 0, 0, 0, 0, 0),
(104, 7, 0, 0, 0, 0, 0, 0),
(105, 7, 0, 0, 0, 0, 0, 0),
(106, 7, 0, 0, 0, 0, 0, 0),
(107, 7, 0, 0, 0, 0, 0, 0),
(108, 7, 0, 0, 0, 0, 0, 0),
(109, 7, 0, 0, 0, 0, 0, 0),
(110, 7, 0, 0, 0, 0, 0, 0),
(111, 1, 0, 0, 0, 0, 0, 0),
(112, 1, 0, 0, 0, 0, 0, 0),
(113, 1, 0, 0, 0, 0, 0, 0),
(114, 1, 0, 0, 0, 0, 0, 0),
(115, 1, 0, 0, 0, 0, 0, 0),
(116, 1, 0, 0, 0, 0, 0, 0),
(117, 1, 0, 0, 0, 0, 0, 0),
(118, 1, 0, 0, 0, 0, 0, 0),
(119, 1, 0, 0, 0, 0, 0, 0),
(120, 1, 0, 0, 0, 0, 0, 0),
(121, 1, 0, 0, 0, 0, 0, 0),
(122, 1, 0, 0, 0, 0, 0, 0),
(123, 1, 0, 0, 0, 0, 0, 0),
(124, 1, 0, 0, 0, 0, 0, 0),
(125, 1, 0, 0, 0, 0, 0, 0),
(126, 1, 0, 0, 0, 0, 0, 0),
(127, 1, 0, 0, 0, 0, 0, 0),
(128, 1, 0, 0, 0, 0, 0, 0),
(129, 1, 0, 0, 0, 0, 0, 0),
(130, 1, 0, 0, 0, 0, 0, 0),
(131, 2, 10, 100, 180, 0, 5, 0),
(132, 6, 0, 0, 0, 0, 0, 0),
(133, 6, 0, 0, 0, 0, 0, 0),
(134, 6, 0, 0, 0, 0, 0, 0),
(135, 6, 0, 0, 0, 0, 0, 0),
(136, 6, 0, 0, 0, 0, 0, 0),
(137, 6, 0, 0, 0, 0, 0, 0),
(138, 6, 0, 0, 0, 0, 0, 0),
(139, 6, 0, 0, 0, 0, 0, 0),
(140, 6, 0, 0, 0, 0, 0, 0),
(141, 6, 0, 0, 0, 0, 0, 0),
(142, 6, 0, 0, 0, 0, 0, 0),
(143, 6, 0, 0, 0, 0, 0, 0),
(144, 6, 0, 0, 0, 0, 0, 0),
(145, 6, 0, 0, 0, 0, 0, 0),
(146, 6, 0, 0, 0, 0, 0, 0),
(147, 6, 0, 0, 0, 0, 0, 0),
(148, 6, 0, 0, 0, 0, 0, 0),
(149, 6, 0, 0, 0, 0, 0, 0),
(150, 6, 0, 0, 0, 0, 0, 0),
(151, 6, 0, 0, 0, 0, 0, 0),
(152, 6, 0, 0, 0, 0, 0, 0),
(153, 6, 0, 0, 0, 0, 0, 0),
(154, 6, 0, 0, 0, 0, 0, 0),
(155, 6, 0, 0, 0, 0, 0, 0),
(156, 6, 0, 0, 0, 0, 0, 0),
(157, 6, 0, 0, 0, 0, 0, 0),
(158, 6, 0, 0, 0, 0, 0, 0),
(159, 6, 0, 0, 0, 0, 0, 0),
(160, 6, 0, 0, 0, 0, 0, 0),
(161, 6, 0, 0, 0, 0, 0, 0),
(162, 6, 0, 0, 0, 0, 0, 0),
(163, 6, 0, 0, 0, 0, 0, 0),
(164, 6, 0, 0, 0, 0, 0, 0),
(165, 6, 0, 0, 0, 0, 0, 0),
(166, 6, 0, 0, 0, 0, 0, 0),
(167, 6, 0, 0, 0, 0, 0, 0),
(168, 6, 0, 0, 0, 0, 0, 0),
(169, 6, 0, 0, 0, 0, 0, 0),
(170, 6, 0, 0, 0, 0, 0, 0),
(171, 6, 0, 0, 0, 0, 0, 0),
(172, 6, 0, 0, 0, 0, 0, 0),
(173, 6, 0, 0, 0, 0, 0, 0),
(174, 6, 0, 0, 0, 0, 0, 0),
(175, 6, 0, 0, 0, 0, 0, 0),
(176, 6, 0, 0, 0, 0, 0, 0),
(177, 6, 0, 0, 0, 0, 0, 0),
(178, 6, 0, 0, 0, 0, 0, 0),
(179, 6, 0, 0, 0, 0, 0, 0),
(180, 6, 0, 0, 0, 0, 0, 0),
(181, 6, 0, 0, 0, 0, 0, 0),
(182, 6, 0, 0, 0, 0, 0, 0),
(183, 6, 0, 0, 0, 0, 0, 0),
(184, 6, 0, 0, 0, 0, 0, 0),
(185, 6, 0, 0, 0, 0, 0, 0),
(186, 6, 0, 0, 0, 0, 0, 0),
(187, 6, 0, 0, 0, 0, 0, 0),
(188, 6, 0, 0, 0, 0, 0, 0),
(189, 6, 0, 0, 0, 0, 0, 0),
(190, 6, 0, 0, 0, 0, 0, 0),
(191, 6, 0, 0, 0, 0, 0, 0),
(192, 6, 0, 0, 0, 0, 0, 0),
(193, 6, 0, 0, 0, 0, 0, 0),
(194, 6, 0, 0, 0, 0, 0, 0),
(195, 6, 0, 0, 0, 0, 0, 0),
(196, 6, 0, 0, 0, 0, 0, 0),
(197, 6, 0, 0, 0, 0, 0, 0),
(198, 6, 0, 0, 0, 0, 0, 0),
(199, 6, 0, 0, 0, 0, 0, 0),
(200, 6, 0, 0, 0, 0, 0, 0);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}


		$result .= "&nbsp;->Cr�ation de la table lettres_types<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'lettres_types'"));
		if ($test1 == 0) {
			$sql="CREATE TABLE `lettres_types` (
`id_lettre_type` int(11) NOT NULL auto_increment,
`titre_lettre_type` varchar(250) NOT NULL,
`categorie_lettre_type` varchar(250) NOT NULL,
`reponse_lettre_type` varchar(3) NOT NULL,
PRIMARY KEY  (`id_lettre_type`)
);";
			$query1 = mysql_query($sql);
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$req_test= mysql_query("SELECT * FROM lettres_types");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$sql="INSERT INTO `lettres_types` VALUES (1, 'blame', 'sanction', ''),
(2, 'convocation des parents', 'suivi', ''),
(3, 'avertissement', 'sanction', ''),
(4, 'exclusion', 'sanction', ''),
(5, 'certificat de scolarit�', 'suivi', ''),
(6, 'demande de justificatif d''absence', 'suivi', 'oui'),
(7, 'demande de justificatif de retard', 'suivi', ''),
(8, 'rapport d''incidence', 'sanction', ''),
(0, 'regime de sortie', 'suivi', ''),
(10, 'retenue', 'sanction', '');";
			$query = mysql_query($sql);
			$result .= "Insertion des lettres types: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}


		$req_test= mysql_query("SELECT * FROM absences_actions");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$sql="INSERT INTO `absences_actions` VALUES (1, 'RC', 'Renvoi du cours'),
(2, 'RD', 'Renvoi d&eacute;finitif'),
(3, 'LP', 'Lettre aux parents'),
(4, 'CE', 'Demande de convocation de l&#039;&eacute;l&egrave;ve en vie scolaire'),
(5, 'A', 'Aucune');";
			$query = mysql_query($sql);
			$result .= "Insertions initiales dans absences_actions: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}


/*
// Cette requ�te ne fonctionne pas en 4.0.24-10sarge
$sql="SHOW COLUMNS FROM eleves WHERE type='date' AND field='naissance';";
$test=mysql_query($sql);
if(mysql_num_rows($test)==0){
$result.="-> Correction du type du champ 'naissance' de la table 'eleves' en type 'date': ";
$sql="ALTER TABLE `eleves` CHANGE `naissance` `naissance` DATE NULL DEFAULT NULL;";
$res=mysql_query($sql);
if($res){
		$result .= "<font color=\"green\">Ok !</font><br />\n";
} else {
		$result .= "<font color=\"red\">Erreur</font><br />\n";
}
}
*/

		$sql="show columns from eleves like 'naissance';";
		$res=mysql_query($sql);
		if(mysql_num_rows($res)>0){
			//$lig=mysql_fetch_object($res);
			//echo $lig->type."<br />\n";
			$lig=mysql_fetch_array($res);
			//echo $lig[1]."<br />\n";
			if(strtolower($lig[1])!='date'){
				$result.="-> Correction du type du champ 'naissance' de la table 'eleves' en type 'date': ";
				$sql="ALTER TABLE `eleves` CHANGE `naissance` `naissance` DATE NULL DEFAULT NULL;";
				$res=mysql_query($sql);
				if($res){
					$result .= "<font color=\"green\">Ok !</font><br />\n";
				} else {
					$result .= "<font color=\"red\">Erreur</font><br />\n";
				}
			}
		}

	}

	if (($force_maj == 'yes') or (quelle_maj("1.5.0"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.5.0" . $rc . $beta . " :</b><br />";

		$result .= "&nbsp;->Extension de la taille du champ NAME de la table 'setting'<br />";
		$query28 = mysql_query("ALTER TABLE setting CHANGE NAME NAME VARCHAR( 255 ) NOT NULL");
		if ($query28) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ responsable � la table droits<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM droits LIKE 'responsable'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `droits` ADD `responsable` varchar(1) NOT NULL DEFAULT 'F' AFTER `eleve`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";

				foreach ($droits_requests as $key => $value) {
					$exec = traite_requete($value);
				}
			} else {
				$result .= "<font color=\"red\">Erreur (le champ existe d�j� ?)</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Ajout du champ 'email' � la table 'eleves'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM eleves LIKE 'email'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `eleves` ADD `email` varchar(255) NOT NULL");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour les acc�s �l�ves et parents<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveEleve', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesCahierTexteEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesCahierTexteEleve', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveParent', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesCahierTexteParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesCahierTexteParent', 'yes');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';


		$result .= "&nbsp;->Ajout (si besoin) du param�tre autorisant l'utilisation de l'outil de r�cup�ration de mot de passe<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'enable_password_recovery'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('enable_password_recovery', 'no');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';


		$result .= "&nbsp;->Ajout du champ password_ticket � la table utilisateurs<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'password_ticket'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `utilisateurs` ADD `password_ticket` varchar(255) NOT NULL AFTER `date_verrouillage`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ ticket_expiration � la table utilisateurs<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'ticket_expiration'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `utilisateurs` ADD `ticket_expiration` datetime NOT NULL AFTER `password_ticket`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour les droits d'acc�s � la fonction de r�initialisation du mot de passe perdu<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiPasswordReinitProf'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiPasswordReinitProf', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiPasswordReinitScolarite'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiPasswordReinitScolarite', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiPasswordReinitCpe'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiPasswordReinitCpe', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiPasswordReinitAdmin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiPasswordReinitAdmin', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiPasswordReinitEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiPasswordReinitEleve', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiPasswordReinitParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiPasswordReinitParent', 'yes');");


		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) du param�tre autorisant l'acc�s public aux cahiers de texte<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'cahier_texte_acces_public'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('cahier_texte_acces_public', 'no');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour les droits d'acc�s � l'�quipe p�dagogique d'un �l�ve<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesEquipePedaEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesEquipePedaEleve', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesEquipePedaEmailEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesEquipePedaEmailEleve', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesCpePPEmailEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesCpePPEmailEleve', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesEquipePedaParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesEquipePedaParent', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesEquipePedaEmailParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesEquipePedaEmailParent', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesCpePPEmailParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesCpePPEmailParent', 'no');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour les droits d'acc�s aux bulletins simplifi�s et relev�s de notes<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesBulletinSimpleEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesBulletinSimpleEleve', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesBulletinSimpleParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesBulletinSimpleParent', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesBulletinSimpleProf'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesBulletinSimpleProf', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesBulletinSimpleProfTousEleves'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesBulletinSimpleProfTousEleves', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesBulletinSimpleProfToutesClasses'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesBulletinSimpleProfToutesClasses', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesReleveProfTousEleves'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesReleveProfTousEleves', 'yes');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour les droits d'acc�s aux moyennes par les professeurs<br/>";

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesMoyennesProf'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesMoyennesProf', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesMoyennesProfTousEleves'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesMoyennesProfTousEleves', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesMoyennesProfToutesClasses'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesMoyennesProfToutesClasses', 'yes');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour les droits d'acc�s aux graphiques de visualisation (eleves et responsables)<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesGraphEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesGraphEleve', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesGraphParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesGraphParent', 'yes');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour les fiches d'information destin�e aux nouveaux utilisateurs<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'ImpressionParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('ImpressionParent', '');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'ImpressionEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('ImpressionEleve', '');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'ImpressionNombre'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('ImpressionNombre', '1');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'ImpressionNombreParent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('ImpressionNombreParent', '1');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'ImpressionNombreEleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('ImpressionNombreEleve', '1');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout du champ show_email � la table utilisateurs<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'show_email'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `utilisateurs` ADD `show_email` varchar(3) NOT NULL DEFAULT 'no' AFTER `email`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ ele_id � la table eleves<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM eleves LIKE 'ele_id'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `eleves` ADD `ele_id` varchar(10) NOT NULL DEFAULT '' AFTER `ereno`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_categ_font_size_avis'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_categ_font_size_avis', '10');");
			$result .= "Initialisation du param�tre bull_categ_font_size_avis � '10': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_police_avis'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_police_avis', 'Times New Roman');");
			$result .= "Initialisation du param�tre bull_police_avis � 'Times New Roman': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'bull_font_style_avis'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('bull_font_style_avis', 'Normal');");
			$result .= "Initialisation du param�tre bull_font_style_avis � Normal: ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}


		$result .= "&nbsp;->Cr�ation de la table responsables2<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'responsables2'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `responsables2` (
`ele_id` varchar(10) NOT NULL,
`pers_id` varchar(10) NOT NULL,
`resp_legal` varchar(1) NOT NULL,
`pers_contact` varchar(1) NOT NULL
);");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table resp_pers<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'resp_pers'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `resp_pers` (
`pers_id` varchar(10) NOT NULL,
`login` varchar(50) NOT NULL,
`nom` varchar(30) NOT NULL,
`prenom` varchar(30) NOT NULL,
`civilite` varchar(5) NOT NULL,
`tel_pers` varchar(255) NOT NULL,
`tel_port` varchar(255) NOT NULL,
`tel_prof` varchar(255) NOT NULL,
`mel` varchar(100) NOT NULL,
`adr_id` varchar(10) NOT NULL,
PRIMARY KEY  (`pers_id`)
);");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table resp_adr<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'resp_adr'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE IF NOT EXISTS `resp_adr` (
`adr_id` varchar(10) NOT NULL,
`adr1` varchar(100) NOT NULL,
`adr2` varchar(100) NOT NULL,
`adr3` varchar(100) NOT NULL,
`adr4` varchar(100) NOT NULL,
`cp` varchar(6) NOT NULL,
`pays` varchar(50) NOT NULL,
`commune` varchar(50) NOT NULL,
PRIMARY KEY  (`adr_id`)
);");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Passage de 10 caract�res � 255 caract�res des champs tel_pers, tel_port et tel_prof de la table resp_pers.<br />";
		$alter1 = mysql_query("ALTER TABLE `resp_pers` CHANGE `tel_pers` `tel_pers` VARCHAR( 255 )");
		$result .= "tel_pers: ";
		if ($alter1) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}
		$alter2 = mysql_query("ALTER TABLE `resp_pers` CHANGE `tel_port` `tel_port` VARCHAR( 255 )");
		$result .= "tel_port: ";
		if ($alter2) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}
		$alter3 = mysql_query("ALTER TABLE `resp_pers` CHANGE `tel_prof` `tel_prof` VARCHAR( 255 )");
		$result .= "tel_prof: ";
		if ($alter3) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}


		// affectation des mod�les de bulletin  PDF aux classes
		$result .= "&nbsp;->Ajout du champs `modele_bulletin_pdf` � la table `classes`.<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'modele_bulletin_pdf'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `classes` ADD `modele_bulletin_pdf` VARCHAR( 255 ) NULL AFTER `display_moy_gen`");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}


		$req_test= mysql_query("SELECT VALUE FROM setting WHERE NAME = 'option_modele_bulletin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO `setting` VALUES ('option_modele_bulletin', '2');;");
			$result .= "Initialisation du param�tre option_modele_bulletin � '2': ";
			if($query){
				$result .= "<font color=\"green\">Ok !</font><br />";
			}
			else{
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		}

		$result .= "&nbsp;->Cr�ation de la table tentatives_intrusion<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'tentatives_intrusion'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `tentatives_intrusion` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `login` VARCHAR( 255 ) NULL , `adresse_ip` VARCHAR( 255 ) NOT NULL , `date` DATETIME NOT NULL , `niveau` SMALLINT NOT NULL , `fichier` VARCHAR( 255 ) NOT NULL , `description` TEXT NOT NULL , `statut` VARCHAR( 255 ) NOT NULL , PRIMARY KEY ( `id`, `login` ))");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champs `niveau_alerte` � la table `utilisateurs`.<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'niveau_alerte'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `utilisateurs` ADD `niveau_alerte` SMALLINT NOT NULL DEFAULT '0'");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champs `observation_securite` � la table `utilisateurs`.<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'observation_securite'"));
		if ($test1 == 0) {
			$query5 = mysql_query("ALTER TABLE `utilisateurs` ADD `observation_securite` TINYINT NOT NULL DEFAULT '0'");
			if ($query5) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) de param�tres par d�faut pour la d�finition de la politique de s�curit�<br/>";

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert_email_admin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert_email_admin', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert_email_min_level'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert_email_min_level', '1');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert1_normal_cumulated_level'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert1_normal_cumulated_level', '3');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert1_normal_email_admin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert1_normal_email_admin', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert1_normal_block_user'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert1_normal_block_user', 'no');");


		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert1_probation_cumulated_level'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert1_probation_cumulated_level', '2');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert1_probation_email_admin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert1_probation_email_admin', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert1_probation_block_user'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert1_probation_block_user', 'no');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert2_normal_cumulated_level'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert2_normal_cumulated_level', '7');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert2_normal_email_admin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert2_normal_email_admin', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert2_normal_block_user'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert2_normal_block_user', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert2_probation_cumulated_level'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert2_probation_cumulated_level', '5');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert2_probation_email_admin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert2_probation_email_admin', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'security_alert2_probation_block_user'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('security_alert2_probation_block_user', 'yes');");

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'deverouillage_auto_periode_suivante'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('deverouillage_auto_periode_suivante', 'n');");

		// Ajout Mod_absences
		$result .= "&nbsp;->Cr�ation de la table vs_alerts_eleves<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'vs_alerts_eleves'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `vs_alerts_eleves` (
				  `id_alert_eleve` int(11) NOT NULL auto_increment,
				  `eleve_alert_eleve` varchar(100) NOT NULL,
				  `date_alert_eleve` date NOT NULL,
				  `groupe_alert_eleve` int(11) NOT NULL,
				  `type_alert_eleve` int(11) NOT NULL,
				  `nb_trouve` int(11) NOT NULL,
				  `temp_insert` varchar(100) NOT NULL,
				  `etat_alert_eleve` tinyint(4) NOT NULL,
				  `etatpar_alert_eleve` varchar(100) NOT NULL,
				  PRIMARY KEY  (`id_alert_eleve`)
				  );");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table vs_alerts_groupes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'vs_alerts_groupes'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `vs_alerts_groupes` (
				  `id_alert_groupe` int(11) NOT NULL auto_increment,
				  `nom_alert_groupe` varchar(150) NOT NULL,
				  `creerpar_alert_groupe` varchar(100) NOT NULL,
				  PRIMARY KEY  (`id_alert_groupe`)
				  );");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table vs_alerts_types<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'vs_alerts_types'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `vs_alerts_types` (
				  `id_alert_type` int(11) NOT NULL auto_increment,
				  `groupe_alert_type` int(11) NOT NULL,
				  `type_alert_type` varchar(10) NOT NULL,
				  `specifisite_alert_type` varchar(25) NOT NULL,
				  `eleve_concerne` text NOT NULL,
				  `date_debut_comptage` date NOT NULL,
				  `nb_comptage_limit` varchar(200) NOT NULL,
				  PRIMARY KEY  (`id_alert_type`)
				  );");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}
		// Fin Ajout Mod_absences


		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';

		$result .= "&nbsp;->Ajout (si besoin) du param�tre s�lectionnant la feuille de style � utiliser<br/>";
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'gepi_stylesheet'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('gepi_stylesheet', 'style');");

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter;
		}
		$result_inter = '';


		$result .= "&nbsp;->Ajout du champ temp_dir � la table utilisateurs<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'temp_dir'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `utilisateurs` ADD `temp_dir` VARCHAR( 255 ) NOT NULL AFTER `observation_securite`");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

	}



	#
	# MISE A JOUR GEPI 1.5.1
	#

	if (($force_maj == 'yes') or (quelle_maj("1.5.1"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.5.1" . $rc . $beta . " :</b><br />";

		$result .= "&nbsp;->Ajout du champ rn_nomdev � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_nomdev'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_nomdev` CHAR( 1 ) NOT NULL DEFAULT 'n';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_toutcoefdev � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_toutcoefdev'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_toutcoefdev` CHAR( 1 ) NOT NULL DEFAULT 'n';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_coefdev_si_diff � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_coefdev_si_diff'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_coefdev_si_diff` CHAR( 1 ) NOT NULL DEFAULT 'n';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_datedev � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_datedev'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_datedev` CHAR( 1 ) NOT NULL DEFAULT 'n';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_sign_chefetab � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_sign_chefetab'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_sign_chefetab` CHAR( 1 ) NOT NULL DEFAULT 'n';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_sign_pp � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_sign_pp'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_sign_pp` CHAR( 1 ) NOT NULL DEFAULT 'n';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_sign_resp � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_sign_resp'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_sign_resp` CHAR( 1 ) NOT NULL DEFAULT 'n';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_sign_nblig � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_sign_nblig'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_sign_nblig` INT( 11 ) NOT NULL DEFAULT '3';");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ rn_formule � la table classes<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM classes LIKE 'rn_formule'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE `classes` ADD `rn_formule` TEXT NOT NULL;");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		// D�but de la 1.5.1? 20070904

		// ====================================
		// Ajouts concernant le dispositif EDT
		$result .= "&nbsp;->Cr�ation de la table 'salle_cours'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'salle_cours'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE salle_cours (`id_salle` INT( 3 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , `numero_salle` VARCHAR( 10 ) NOT NULL , `nom_salle` VARCHAR( 50 ) NOT NULL);");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'edt_cours'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_cours'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `edt_cours` (`id_cours` int(3) NOT NULL auto_increment, `id_groupe` varchar(10) NOT NULL, `id_salle` varchar(3) NOT NULL, `jour_semaine` varchar(10) NOT NULL, `id_definie_periode` varchar(3) NOT NULL, `duree` varchar(10) NOT NULL default '2', `heuredeb_dec` varchar(3) NOT NULL default '0', `id_semaine` varchar(3) NOT NULL default '0', `id_calendrier` varchar(3) NOT NULL default '0', `modif_edt` varchar(3) NOT NULL default '0', PRIMARY KEY  (`id_cours`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		// Ajout d'un champ � cette table
		$result .= "&nbsp;->Ajout du champ 'login_prof' � la table 'edt_cours'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM edt_cours LIKE 'login_prof'"));
		if ($test1 == 0) {
			$sql="ALTER TABLE `edt_cours` ADD `login_prof` varchar(50) NOT NULL;";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'edt_setting'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_setting'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `edt_setting` (`id` INT( 3 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`reglage` VARCHAR( 30 ) NOT NULL ,`valeur` VARCHAR( 30 ) NOT NULL);");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'edt_calendrier'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_calendrier'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `edt_calendrier` (`id_calendrier` int(11) NOT NULL auto_increment,
`classe_concerne_calendrier` text NOT NULL,
`nom_calendrier` varchar(100) NOT NULL default '',
`debut_calendrier_ts` varchar(11) NOT NULL,
`fin_calendrier_ts` varchar(11) NOT NULL,
`jourdebut_calendrier` date NOT NULL default '0000-00-00',
`heuredebut_calendrier` time NOT NULL default '00:00:00',
`jourfin_calendrier` date NOT NULL default '0000-00-00',
`heurefin_calendrier` time NOT NULL default '00:00:00',
`numero_periode` tinyint(4) NOT NULL default '0',
`etabferme_calendrier` tinyint(4) NOT NULL,
`etabvacances_calendrier` tinyint(4) NOT NULL,
PRIMARY KEY (`id_calendrier`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout des champs 'debut_calendrier_ts' et 'fin_calendrier_ts' � la table 'edt_calendrier'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM edt_calendrier LIKE 'fin_calendrier_ts'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `edt_calendrier` ADD `debut_calendrier_ts` VARCHAR( 11 ) NOT NULL AFTER `nom_calendrier` ,ADD `fin_calendrier_ts` VARCHAR( 11 ) NOT NULL AFTER `debut_calendrier_ts` ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Les champs existent d�j�.</font><br />";
		}


		$result .= "&nbsp;->Cr�ation de la table 'edt_gr_nom'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_gr_nom'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `edt_gr_nom` (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
					`nom` VARCHAR( 50 ) NOT NULL ,
					`nom_long` VARCHAR( 200 ) NOT NULL ,
					`subdivision_type` VARCHAR( 20 ) NOT NULL DEFAULT 'autre',
					`subdivision` VARCHAR( 50 ) NOT NULL ,
					PRIMARY KEY ( `id` ));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'edt_gr_eleves'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_gr_eleves'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `edt_gr_eleves` (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
					`id_gr_nom` INT( 11 ) NOT NULL ,
					`id_eleve` INT( 11 ) NOT NULL ,
					PRIMARY KEY ( `id` ));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'edt_gr_profs'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_gr_profs'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `edt_gr_profs` (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
					`id_gr_nom` INT( 11 ) NOT NULL ,
					`id_utilisateurs` VARCHAR( 50 ) NOT NULL ,
					PRIMARY KEY ( `id` ));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'edt_gr_classes'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_gr_classes'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `edt_gr_classes` (
					`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
					`id_gr_nom` INT( 11 ) NOT NULL ,
					`id_classe` INT( 11 ) NOT NULL ,
					PRIMARY KEY ( `id` ));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'nom_creneaux_s' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'nom_creneaux_s'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'nom_creneaux_s', '1');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'edt_aff_salle' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_salle'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'edt_aff_salle', 'nom');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'edt_aff_matiere' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_matiere'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'edt_aff_matiere', 'long');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'edt_aff_creneaux' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_creneaux'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'edt_aff_creneaux', 'noms');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'edt_aff_init_infos' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_init_infos'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'edt_aff_init_infos', 'oui');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'edt_aff_couleur' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_couleur'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'edt_aff_couleur', 'nb');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'edt_aff_init_infos2' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_init_infos2'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'edt_aff_init_infos2', 'oui');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'aff_cherche_salle' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'aff_cherche_salle'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'aff_cherche_salle', 'tous');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'param_menu_edt' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'param_menu_edt'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('', 'param_menu_edt', 'mouseover');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'scolarite_modif_cours' � la table 'edt_setting'<br/>";
		$req_test = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'scolarite_modif_cours'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO edt_setting VALUES ('' , 'scolarite_modif_cours', 'y');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'edt_calendrier_ouvert' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='edt_calendrier_ouvert'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('edt_calendrier_ouvert', 'y');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'scolarite_modif_cours' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'scolarite_modif_cours'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('scolarite_modif_cours', 'y');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'autorise_edt_tous' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'autorise_edt_tous'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('autorise_edt_tous', 'y');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'autorise_edt_admin' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'autorise_edt_admin'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('autorise_edt_admin', 'y');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'autorise_edt_eleve' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'autorise_edt_eleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('autorise_edt_eleve', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'mod_edt_gr' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'mod_edt_gr'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('mod_edt_gr', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}


		// Fin des ajouts concernant le dispositif EDT
		// ====================================


		// Multisite
		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'multisite' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='multisite'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('multisite', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		//D�buts du dispositif sur les rss dans le cdt
		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'rss_cdt_eleve' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'rss_cdt_eleve'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('rss_cdt_eleve', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		// statuts dynamiques
		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'statuts_prives' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'statuts_prives'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('statuts_prives', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		// Cr�ation des tables sur les statuts priv�s
		$result .= "&nbsp;->Cr�ation de la table 'droits_statut'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'droits_statut'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `droits_statut` (`id` int(11) NOT NULL auto_increment, `nom_statut` varchar(30) NOT NULL, PRIMARY KEY  (`id`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'droits_utilisateurs'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'droits_utilisateurs'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `droits_utilisateurs` (`id` int(11) NOT NULL auto_increment, `id_statut` int(11) NOT NULL, `login_user` varchar(50) NOT NULL, PRIMARY KEY  (`id`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Cr�ation de la table 'droits_speciaux'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'droits_speciaux'"));
		if ($test1 == 0) {
			$query1 = mysql_query("CREATE TABLE `droits_speciaux` (`id` int(11) NOT NULL auto_increment, `id_statut` int(11) NOT NULL, `nom_fichier` varchar(200) NOT NULL, `autorisation` char(1) NOT NULL, PRIMARY KEY  (`id`));");
			if ($query1) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		// ========================================


		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'active_notanet' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='active_notanet'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('active_notanet', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}


		//+++++++Modif li� � longmax_login++++++++++++
		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'longmax_login' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='longmax_login'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('longmax_login', '10');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}



		//===================================================
		// AJOUT DU CHAMP id A LA TABLE eleves
		$result .= "&nbsp;->Ajout du champ 'id' � la table 'eleves'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM eleves LIKE 'id'"));
		if ($test1 == 0) {
			$test2 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM eleves LIKE 'id_eleve'"));
			if ($test2 == 0) {
				$query2 = mysql_query("ALTER TABLE `eleves` ADD UNIQUE (`login`);");
				if ($query2) {
					$query3 = mysql_query("ALTER TABLE `eleves` DROP PRIMARY KEY;");
					if ($query3) {
						$query4 = mysql_query("ALTER TABLE `eleves` ADD `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ;");
						if ($query4) {
							$result .= "<font color=\"green\">Ok !</font><br />";
						} else {
							$result .= "<font color=\"red\">Erreur</font><br />";
						}
					} else {
						$result .= "<font color=\"red\">Erreur</font><br />";
					}
				} else {
					$result .= "<font color=\"red\">Erreur</font><br />";
				}
			}
			else{
				$result .= "<font color=\"blue\">Le champ a d�j� �t� trait�.</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}
		//===================================================


		//===================================================
		$result .= "&nbsp;->Extension � 255 caract�res du champ 'name' de la table 'preferences'<br />";
		$query = mysql_query("ALTER TABLE `preferences` CHANGE `name` `name` VARCHAR( 255 ) NOT NULL;");
		if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
		}
		//===================================================

		//===================================================
		$result .= "&nbsp;->Modification du champ 'id' de la table 'eleves' en 'id_eleve'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM eleves LIKE 'id'"));
		$test2 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM eleves LIKE 'id_eleve'"));
		if ($test1 == 0) {
			if ($test2 == 0) {
				$result .= "<font color=\"red\">Le champ n'existe pas !!!</font><br />";
			}
			else{
				$result .= "<font color=\"blue\">Le champ a d�j� �t� trait� !</font><br />";
			}
		}
		else{
			if ($test2 == 0) {
				$query = mysql_query("ALTER TABLE `eleves` CHANGE `id` `id_eleve` INT( 11 ) NOT NULL AUTO_INCREMENT;");
				if ($query) {
					$result .= "<font color=\"green\">Ok !</font><br />";
				} else {
					$result .= "<font color=\"red\">Erreur</font><br />";
				}
			}
			else{
				$result .= "<font color=\"red\">Erreur: Vous avez � la fois le champ 'id' et le champ 'id_eleve' !</font><br />";
			}
		}
		//===================================================

		//===================================================
		$result .= "&nbsp;->Ajout d'un champ 'numind' � la table 'utilisateurs'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'numind'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `utilisateurs` ADD `numind` VARCHAR( 255 ) NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}
		//===================================================


		//===================================================
		$result .= "&nbsp;->Ajout d'un champ 'nom_etab_gras' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'nom_etab_gras'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `nom_etab_gras` TINYINT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'taille_texte_date_edition' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'taille_texte_date_edition'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `taille_texte_date_edition` FLOAT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'taille_texte_matiere' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'taille_texte_matiere'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `taille_texte_matiere` FLOAT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'active_moyenne_general' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'active_moyenne_general'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `active_moyenne_general` TINYINT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'titre_bloc_avis_conseil' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'titre_bloc_avis_conseil'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `titre_bloc_avis_conseil` VARCHAR( 50 ) NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'taille_titre_bloc_avis_conseil' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'taille_titre_bloc_avis_conseil'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `taille_titre_bloc_avis_conseil` FLOAT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'taille_profprincipal_bloc_avis_conseil' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'taille_profprincipal_bloc_avis_conseil'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `taille_profprincipal_bloc_avis_conseil` FLOAT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'affiche_fonction_chef' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'affiche_fonction_chef'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `affiche_fonction_chef` TINYINT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'taille_texte_fonction_chef' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'taille_texte_fonction_chef'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `taille_texte_fonction_chef` FLOAT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'taille_texte_identitee_chef' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'taille_texte_identitee_chef'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE `model_bulletin` ADD `taille_texte_identitee_chef` FLOAT NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Ajout des champs 'tel_texte', 'fax_image',... � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'tel_texte'"));
		if ($test1 == 0) {
			$sql="ALTER TABLE `model_bulletin` ADD `tel_image` VARCHAR( 20 ) NOT NULL ,
ADD `tel_texte` VARCHAR( 20 ) NOT NULL ,
ADD `fax_image` VARCHAR( 20 ) NOT NULL ,
ADD `fax_texte` VARCHAR( 20 ) NOT NULL ,
ADD `courrier_image` VARCHAR( 20 ) NOT NULL ,
ADD `courrier_texte` VARCHAR( 20 ) NOT NULL ,
ADD `largeur_bloc_eleve` FLOAT NOT NULL ,
ADD `hauteur_bloc_eleve` FLOAT NOT NULL ,
ADD `largeur_bloc_adresse` FLOAT NOT NULL ,
ADD `hauteur_bloc_adresse` FLOAT NOT NULL ,
ADD `largeur_bloc_datation` FLOAT NOT NULL ,
ADD `hauteur_bloc_datation` FLOAT NOT NULL ,
ADD `taille_texte_classe` FLOAT NOT NULL ,
ADD `type_texte_classe` VARCHAR( 1 ) NOT NULL ,
ADD `taille_texte_annee` FLOAT NOT NULL ,
ADD `type_texte_annee` VARCHAR( 1 ) NOT NULL ,
ADD `taille_texte_periode` FLOAT NOT NULL ,
ADD `type_texte_periode` VARCHAR( 1 ) NOT NULL ,
ADD `taille_texte_categorie_cote` FLOAT NOT NULL ,
ADD `taille_texte_categorie` FLOAT NOT NULL ,
ADD `type_texte_date_datation` VARCHAR( 1 ) NOT NULL ,
ADD `cadre_adresse` TINYINT NOT NULL ;";
			//echo "<br />$sql<br />";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout des champs 'centrage_logo', 'ajout_cadre_blanc_photo',... � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'centrage_logo'"));
		if ($test1 == 0) {
			$sql="ALTER TABLE `model_bulletin` ADD `centrage_logo` TINYINT NOT NULL DEFAULT '0',
ADD `Y_centre_logo` FLOAT NOT NULL DEFAULT '18',
ADD `ajout_cadre_blanc_photo` TINYINT NOT NULL DEFAULT '0';";
			//echo "<br />$sql<br />";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}


		// 20071130
		$result .= "&nbsp;->Ajout des champs 'affiche_moyenne_mini_general' et 'affiche_moyenne_maxi_general' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'affiche_moyenne_mini_general'"));
		if ($test1 == 0) {
			$sql="ALTER TABLE `model_bulletin` ADD `affiche_moyenne_mini_general` TINYINT NOT NULL DEFAULT '1',
ADD `affiche_moyenne_maxi_general` TINYINT NOT NULL DEFAULT '1';";
			//echo "<br />$sql<br />";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ 'affiche_date_edition' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'affiche_date_edition'"));
		if ($test1 == 0) {
			$sql="ALTER TABLE `model_bulletin` ADD `affiche_date_edition` TINYINT NOT NULL DEFAULT '1';";
			//echo "<br />$sql<br />";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout du champ 'active_moyenne_general' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'active_moyenne_general'"));
		if ($test1 == 0) {
			$sql="ALTER TABLE `model_bulletin` ADD `active_moyenne_general` TINYINT NOT NULL DEFAULT '1';";
			//echo "<br />$sql<br />";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}
		//===================================================
		// Ajout d'un champ pour les AID et le bulletin simplifi�
		$result .= "&nbsp;->Ajout du champ 'bull_simplifie' � la table 'aid_config'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM aid_config LIKE 'bull_simplifie'"));
		if ($test1 == 0) {
			$sql="ALTER TABLE `aid_config` ADD `bull_simplifie` CHAR(1) NOT NULL DEFAULT 'y';";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		// Cr�ation de la table absences_rb
		$result .= "&nbsp;->Ajout de la table absences_rb. <br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'absences_rb'"));
		if ($test1 == 0) {
			$sql = "CREATE TABLE `absences_rb` (`id` int(5) NOT NULL auto_increment,
`eleve_id` varchar(30) NOT NULL,
`retard_absence` varchar(1) NOT NULL default 'A',
`groupe_id` varchar(8) NOT NULL,
`edt_id` int(5) NOT NULL default '0',
`jour_semaine` varchar(10) NOT NULL,
`creneau_id` int(5) NOT NULL,
`debut_ts` int(11) NOT NULL,
`fin_ts` int(11) NOT NULL,
`date_saisie` int(20) NOT NULL,
`login_saisie` varchar(30) NOT NULL, PRIMARY KEY  (`id`));";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		// Cr�ation de la table matieres_appreciations_tempo
		$result .= "&nbsp;->Ajout de la table matieres_appreciations_tempo. <br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'matieres_appreciations_tempo'"));
		if ($test1 == 0) {
			$sql = "CREATE TABLE `matieres_appreciations_tempo` ( `login` varchar(50) NOT NULL default '',
`id_groupe` int(11) NOT NULL default '0',
`periode` int(11) NOT NULL default '0',
`appreciation` text NOT NULL, PRIMARY KEY  (`login`,`id_groupe`,`periode`));";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'utiliserMenuBarre' � la table 'setting'<br />";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='utiliserMenuBarre'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('utiliserMenuBarre', 'no');");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'active_absences_parents' � la table 'setting'<br />";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='active_absences_parents'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('active_absences_parents', 'no');");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout (si besoin) du param�tre 'creneau_different' � la table 'setting'<br />";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='creneau_different'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query = mysql_query("INSERT INTO setting VALUES ('creneau_different', 'n');");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		// Cr�ation de la table absences_creneaux_bis
		$result .= "&nbsp;->Ajout de la table absences_creneaux_bis. <br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'absences_creneaux_bis'"));
		if ($test1 == 0) {
			$sql = "CREATE TABLE `absences_creneaux_bis` (
`id_definie_periode` int(11) NOT NULL auto_increment,
`nom_definie_periode` varchar(10) NOT NULL default '',
`heuredebut_definie_periode` time NOT NULL default '00:00:00',
`heurefin_definie_periode` time NOT NULL default '00:00:00',
`suivi_definie_periode` tinyint(4) NOT NULL,
`type_creneaux` varchar(15) NOT NULL,
PRIMARY KEY  (`id_definie_periode`));";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de la table edt_init. <br />";
		$test1 = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'edt_init'"));
		if ($test1 == 0) {
			$sql = "CREATE TABLE `edt_init`
(`id_init` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`ident_export` VARCHAR( 100 ) NOT NULL ,
`nom_export` VARCHAR( 200 ) NOT NULL ,
`nom_gepi` VARCHAR( 200 ) NOT NULL);";
			$query = mysql_query($sql);
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else{
			$result .= "<font color=\"blue\">La table existe d�j�.</font><br />";
		}


		//Initialisation des param�tres li�s au module inscription
		$result_inter = "";
		$result .= "&nbsp;-> Initialisation des param�tres li�s au module inscription<br />";

		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'inscription_items'"));
		if ($test == 0) {
			$result_inter .= traite_requete("CREATE TABLE IF NOT EXISTS inscription_items (id int(11) NOT NULL auto_increment, date varchar(20) NOT NULL default '', heure varchar(10) NOT NULL default '', description varchar(200) NOT NULL default '', PRIMARY KEY  (id));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table inscription_items a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table inscription_items existe d�j�.</font><br />";
		}

		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'inscription_j_login_items'"));
		if ($test == 0) {
			$result_inter .= traite_requete("CREATE TABLE IF NOT EXISTS inscription_j_login_items (login varchar(20) NOT NULL default '', id int(11) NOT NULL default '0');");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table inscription_j_login_items a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table inscription_j_login_items existe d�j�.</font><br />";
		}

		$req = sql_query1("SELECT VALUE FROM setting WHERE NAME = 'active_inscription'");
		if ($req == -1)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('active_inscription', 'n');");
		else
		$result .= "<font color=\"blue\">Le param�tre active_inscription existe d�j�.</font><br />";
		$req = sql_query1("SELECT VALUE FROM setting WHERE NAME = 'active_inscription_utilisateurs'");
		if ($req == -1)
		$result_inter .= traite_requete("INSERT INTO setting VALUES ('active_inscription_utilisateurs', 'n');");
		else
		$result .= "<font color=\"blue\">Le param�tre active_inscription_utilisateurs existe d�j�.</font><br />";

		$req = sql_query1("SELECT VALUE FROM setting WHERE NAME = 'mod_inscription_explication'");
		if ($req == -1)
		$result_inter .= traite_requete("INSERT INTO setting (NAME, VALUE) VALUES('mod_inscription_explication', '<p> <strong>Pr&eacute;sentation des dispositifs du Lyc&eacute;e dans les coll&egrave;ges qui organisent des rencontres avec les parents.</strong> <br />\r\n<br />\r\nChacun d&rsquo;entre vous conna&icirc;t la situation dans laquelle sont plac&eacute;s les &eacute;tablissements : </p>\r\n<ul>\r\n    <li>baisse d&eacute;mographique</li>\r\n    <li>r&eacute;gulation des moyens</li>\r\n    <li>- ... </li>\r\n</ul>\r\nCette ann&eacute;e encore nous devons &ecirc;tre pr&eacute;sents dans les r&eacute;unions organis&eacute;es au sein des coll&egrave;ges afin de pr&eacute;senter nos sp&eacute;cificit&eacute;s, notre valeur ajout&eacute;e, les &eacute;volution du projet, le label international, ... <br />\r\nsur cette feuille, vous avez la possibilit&eacute; de vous inscrire afin d''intervenir dans un ou plusieurs coll&egrave;ges selon vos convenances.');");
		else
		$result .= "<font color=\"blue\">Le param�tre mod_inscription_explication existe d�j�.</font><br />";
		$req = sql_query1("SELECT VALUE FROM setting WHERE NAME = 'mod_inscription_titre'");
		if ($req == -1)
		$result_inter .= traite_requete("INSERT INTO setting (NAME, VALUE) VALUES('mod_inscription_titre', 'Intervention dans les coll�ges');");
		else
		$result .= "<font color=\"blue\">Le param�tre mod_inscription_titre existe d�j�.</font><br />";

		if ($result_inter == '') {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= $result_inter."<br />";
		}
		//
		// Outils compl�menraires de gestion des AID
		//
		$result_inter = "";
		$result .= "<br />&nbsp;->Ajout des param�tres li�s aux outils compl�mentaires de gestion des AIDs<br />";
		// Cr�ation de la table j_aidcateg_utilisateurs
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_aidcateg_utilisateurs'"));
		if ($test == 0) {
			$result_inter .= traite_requete("CREATE TABLE IF NOT EXISTS j_aidcateg_utilisateurs (indice_aid INT NOT NULL ,id_utilisateur VARCHAR( 50 ) NOT NULL);");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table j_aidcateg_utilisateurs a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table j_aidcateg_utilisateurs existe d�j�.</font><br />";
		}
		$result_inter = "";
		// Modification de la table aid_config
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM aid_config LIKE 'feuille_presence'"));
		if ($test == 0) {
			$result_inter .= traite_requete("ALTER TABLE aid_config ADD outils_complementaires ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';");
			$result_inter .= traite_requete("ALTER TABLE aid_config ADD feuille_presence ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n';");
			if ($result_inter == '')
			$result .= "<font color=\"green\">Les champs outils_complementaires et feuille_presence dans la table aid_config ont �t� cr��s !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">Les champs outils_complementaires et feuille_presence dans la table aid_config existent d�j�.</font><br />";
		}
		// Modification de la table aid
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM aid LIKE 'en_construction'"));
		if ($test == 0) {
			$result_inter = traite_requete("ALTER TABLE aid ADD perso1 VARCHAR( 255 ) NOT NULL ,
ADD perso2 VARCHAR( 255 ) NOT NULL ,
ADD perso3 VARCHAR( 255 ) NOT NULL ,
ADD productions VARCHAR( 100 ) NOT NULL ,
ADD resume TEXT NOT NULL ,
ADD famille SMALLINT( 6 ) NOT NULL ,
ADD mots_cles VARCHAR( 255 ) NOT NULL ,
ADD adresse1 VARCHAR( 255 ) NOT NULL ,
ADD adresse2 VARCHAR( 255 ) NOT NULL ,
ADD public_destinataire VARCHAR( 50 ) NOT NULL ,
ADD contacts TEXT NOT NULL ,
ADD divers TEXT NOT NULL ,
ADD matiere1 VARCHAR( 100 ) NOT NULL ,
ADD matiere2 VARCHAR( 100 ) NOT NULL ,
ADD eleve_peut_modifier ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n' ,
ADD prof_peut_modifier ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n' ,
ADD cpe_peut_modifier ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n' ,
ADD fiche_publique ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n' ,
ADD affiche_adresse1 ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n' ,
ADD en_construction ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n'
;");
			if ($result_inter == '')
			$result .= "<font color=\"green\">Les champ productions, resume, famille, mots_cles, adresse1, adress2, public_destinataire, contacts, divers, matiere1, matiere2, eleve_peut_modifier, prof_peut_modifier, cpe_peut_modifier, fiche_publique, affiche_adresse1, en_construction, perso1, perso2, perso2 dans la table aid ont �t� cr��s !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">Les champ productions, resume, famille, mots_cles, adresse1, adress2, public_destinataire, contacts, divers, matiere1, matiere2, eleve_peut_modifier, prof_peut_modifier, cpe_peut_modifier, fiche_publique, affiche_adresse1, en_construction, perso1, perso2, perso2  dans la table aid existent d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_aid_eleves_resp'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `j_aid_eleves_resp` (`id_aid` varchar(100) NOT NULL default '',`login` varchar(60) NOT NULL default '',`indice_aid` int(11) NOT NULL default '0',PRIMARY KEY  (`id_aid`,`login`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table j_aid_eleves_resp a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table j_aid_eleves_resp existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'aid_familles'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `aid_familles` (`ordre_affichage` smallint(6) NOT NULL default '0',`id` smallint(6) NOT NULL default '0',`type` varchar(250) NOT NULL default '');");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table aid_familles a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table aid_familles est d�j� cr��e.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("select ordre_affichage from aid_familles"));
		if ($test == 0) {
			$result_inter = traite_requete("INSERT INTO `aid_familles` (`ordre_affichage`, `id`, `type`) VALUES
(0, 10, 'Information, presse'),
(1, 11, 'Philosophie et psychologie, pens�e'),
(2, 12, 'Religions'),
(3, 13, 'Sciences sociales, soci�t�, humanitaire'),
(4, 14, 'Langues, langage'),
(5, 15, 'Sciences (sciences dures)'),
(6, 16, 'Techniques, sciences appliqu�es, m�decine, cuisine...'),
(7, 17, 'Arts, loisirs et sports'),
(8, 18, 'Litt�rature, th��tre, po�sie'),
(9, 19, 'G�ographie et Histoire, civilisations anciennes');");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table aid_familles a �t� mise � jour !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table aid_familles est d�j� remplie.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'aid_public'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `aid_public` (`ordre_affichage` smallint(6) NOT NULL default '0',`id` smallint(6) NOT NULL default '0',`public` varchar(100) NOT NULL default '');");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table aid_public a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table aid_public existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("select * from aid_public"));
		if ($test == 0) {
			$result_inter = traite_requete("INSERT INTO `aid_public` (`ordre_affichage`, `id`, `public`) VALUES
(3, 1, 'Lyc�ens'),
(2, 2, 'Coll�giens'),
(1, 3, 'Ecoliers'),
(6, 4, 'Grand public'),
(5, 5, 'Experts (ou sp�cialistes)'),
(4, 6, 'Etudiants');");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table aid_public a �t� mise � jour !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table aid_public est d�j� remplie.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'aid_productions'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `aid_productions` (`id` smallint(6) NOT NULL auto_increment, `nom` varchar(100) NOT NULL default '', PRIMARY KEY  (`id`) );");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table aid_productions a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table aid_productions existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("select * from aid_productions"));
		if ($test == 0) {
			$result_inter = traite_requete("INSERT INTO `aid_productions` (`id`, `nom`) VALUES
(1, 'Dossier papier'),
(2, 'Emission de radio'),
(3, 'Exposition'),
(4, 'Film'),
(5, 'Spectacle'),
(6, 'R�alisation plastique'),
(7, 'R�alisation technique ou scientifique'),
(8, 'Jeu vid�o'),
(9, 'Animation culturelle'),
(10, 'Maquette'),
(11, 'Site internet'),
(12, 'Diaporama'),
(13, 'Production musicale'),
(14, 'Production th��trale'),
(15, 'Animation en milieu scolaire'),
(16, 'Programmation logicielle'),
(17, 'Journal');");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table aid_productions a �t� mise � jour !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table aid_productions est d�j� remplie.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'droits_aid'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `droits_aid` (`id` varchar(200) NOT NULL default '',`public` char(1) NOT NULL default '',`professeur` char(1) NOT NULL default '',`cpe` char(1) NOT NULL default '',`scolarite` char(1) NOT NULL default '',`eleve` char(1) NOT NULL default '',`responsable` char(1) NOT NULL default 'F',`secours` char(1) NOT NULL default '',`description` varchar(255) NOT NULL default '',`statut` char(1) NOT NULL default '',PRIMARY KEY  (`id`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table des droits_aid a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table droits_aid existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("select * from droits_aid"));
		if ($test == 0) {
			$result_inter = traite_requete("INSERT INTO `droits_aid` VALUES('nom', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('numero', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('perso1', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('perso2', 'F', 'F', 'V', 'F', 'F', 'F', 'F', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('productions', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Production', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('resume', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'R�sum�', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('famille', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Famille', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('mots_cles', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Mots cl�s', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('adresse1', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Adresse publique', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('adresse2', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Adresse priv�e', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('public_destinataire', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Public destinataire', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('contacts', 'F', 'V', 'F', 'F', 'V', 'F', 'F', 'Contacts, ressources', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('divers', 'F', 'V', 'F', 'F', 'V', 'F', 'F', 'Divers', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('matiere1', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Discipline principale', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('matiere2', 'V', 'V', 'F', 'F', 'V', 'F', 'F', 'Discipline secondaire', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('eleve_peut_modifier', '-', '-', '-', '-', '-', '-', '-', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('cpe_peut_modifier', '-', '-', '-', '-', '-', '-', '-', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('prof_peut_modifier', '-', '-', '-', '-', '-', '-', '-', 'A pr�ciser', '0');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('fiche_publique', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('affiche_adresse1', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('en_construction', 'F', 'F', 'F', 'F', 'F', 'F', 'F', 'A pr�ciser', '1');");
			$result_inter .= traite_requete("INSERT INTO `droits_aid` VALUES('perso3', 'V', 'F', 'V', 'F', 'F', 'F', 'F', 'A pr�ciser', '0');");

			if ($result_inter == '')
			$result .= "<font color=\"green\">La table des droits_aid a �t� mise � jour !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table droits_aid est d�j� remplie.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM matieres LIKE 'matiere_aid'"));
		if ($test == 0) {
			$result_inter = traite_requete("ALTER TABLE `matieres` ADD `matiere_aid` CHAR( 1 ) DEFAULT 'n' NOT NULL , ADD `matiere_atelier` CHAR( 1 ) DEFAULT 'n' NOT NULL;");
			if ($result_inter == '')
			$result .= "<font color=\"green\">Les champs matiere_aid et matiere_atelier ont �t� ajout�s � la table matieres !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">Les champs matiere_aid et matiere_atelier existent d�j� dans la table matieres !</font><br />";
		}
		$result .= "<br />&nbsp;->Ajout de la table table matieres_appreciations_grp<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'matieres_appreciations_grp'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE `matieres_appreciations_grp` ( `id_groupe` int(11) NOT NULL default '0', `periode` int(11) NOT NULL default '0', `appreciation` text NOT NULL, PRIMARY KEY  (`id_groupe`,`periode`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table matieres_appreciations_grp a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table matieres_appreciations_grp existe d�j�.</font><br />";
		}

		$result .= "<br />&nbsp;->Tentative d'ajout du champ display_parents_app dans la table cn_devoirs.<br />";
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM cn_devoirs LIKE 'display_parents_app'"));
		if ($test == 0) {
			$result_inter = traite_requete("ALTER TABLE `cn_devoirs` ADD `display_parents_app` CHAR( 1 ) NOT NULL DEFAULT '0'");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= $result_inter;
			}
		} else {
			$result .= "<font color=\"blue\">Le champs existe d�j�.</font><br />";
		}
		//==========================================================
		// Module Ateliers
		$result .= "<br />&nbsp;->Mise en place du module Ateliers<br />";
		$test = sql_query1("SELECT VALUE FROM setting WHERE NAME = 'active_ateliers'");
		if ($test == -1) {
			$result_inter = traite_requete("INSERT INTO setting (NAME, VALUE) VALUES('active_ateliers', 'n');");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Le param�tre active_ateliers a �t� cr��.</font><br />";
			} else {
				$result .= $result_inter;
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre active_ateliers existe d�j�.</font><br />";
		}

		$test = sql_query1("SHOW TABLES LIKE 'ateliers_config'");
		if ($test == -1) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `ateliers_config` (`nom_champ` char(100) NOT NULL default '', `content` char(255) NOT NULL default '',`param` char(100) NOT NULL default '');");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">La table ateliers_config a �t� cr��e.</font><br />";
			} else {
				$result .= $result_inter;
			}
		} else {
			$result .= "<font color=\"blue\">La table ateliers_config existe d�j�.</font><br />";
		}

		//==========================================================
		// Trombinoscope
		$result .= "<br />&nbsp;->Trombinoscope<br />";

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'param_module_trombinoscopes'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result_inter = traite_requete("INSERT INTO setting VALUES ('param_module_trombinoscopes', 'no_gep');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre param_module_trombinoscopes � no_gep: Ok !</font><br />";
			}
			else{
				$result.="<font color=\"red\">D�finition du param�tre param_module_trombinoscopes � no_gep: Erreur !</font><br />";
			}
		}else {
			$result .= "<font color=\"blue\">Le param�tre param_module_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'active_module_trombinoscopes'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result .= traite_requete("INSERT INTO setting VALUES ('active_module_trombinoscopes', 'y');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre active_module_trombinoscopes � y: Ok !</font><br />";
			}
			else{
				$result.="<font color=\"red\">D�finition du param�tre active_module_trombinoscopes � y: Erreur !</font><br />";
			}
		}else {
			$result .= "<font color=\"blue\">Le param�tre active_module_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'l_max_aff_trombinoscopes'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('l_max_aff_trombinoscopes', '120');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre l_max_aff_trombinoscopes � 120: Ok !</font><br />";
			}
			else{
				$result.="<font color=\"red\">D�finition du param�tre l_max_aff_trombinoscopes � 120: Erreur !</font><br />";
			}
		}else {
			$result .= "<font color=\"blue\">Le param�tre l_max_aff_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}
		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'h_max_aff_trombinoscopes'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('h_max_aff_trombinoscopes', '160');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre h_max_aff_trombinoscopes � 160: Ok !</font><br />";
			}
			else{
				$result.="<font color=\"red\">D�finition du param�tre h_max_aff_trombinoscopes � 160: Erreur !</font><br />";
			}
		}else {
			$result .= "<font color=\"blue\">Le param�tre h_max_aff_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}
		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'l_max_imp_trombinoscopes'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('l_max_imp_trombinoscopes', '70');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre l_max_imp_trombinoscopes � 70: Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre l_max_imp_trombinoscopes � 70: Erreur !</font><br />";
			}
		}else {
			$result .= "<font color=\"blue\">Le param�tre l_max_imp_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}
		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'h_max_imp_trombinoscopes'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('h_max_imp_trombinoscopes', '100');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre h_max_imp_trombinoscopes � 100: Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre h_max_imp_trombinoscopes � 100: Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre h_max_imp_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'h_resize_trombinoscopes'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('h_resize_trombinoscopes', '160');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre h_resize_trombinoscopes � 160: Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre h_resize_trombinoscopes � 160: Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre h_resize_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT VALUE FROM setting WHERE NAME = 'l_resize_trombinoscopes'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('l_resize_trombinoscopes', '120');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre l_resize_trombinoscopes � 120: Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre l_resize_trombinoscopes � 120: Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre l_resize_trombinoscopes existe d�j� dans la table setting.</font><br />";
		}

		//==========================================================
		// AJOUT: boireaus 20080218
		//        Dispositif de restriction des acc�s aux appr�ciations pour les comptes responsables/eleves
		$result .= "<br />&nbsp;->Dispositif de restriction des acc�s aux appr�ciations pour les comptes responsables/eleves<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'matieres_appreciations_acces'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `matieres_appreciations_acces` (`id_classe` INT( 11 ) NOT NULL , `statut` VARCHAR( 255 ) NOT NULL , `periode` INT( 11 ) NOT NULL , `date` DATE NOT NULL , `acces` ENUM( 'y', 'n', 'date' ) NOT NULL );");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table matieres_appreciations_acces a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table matieres_appreciations_acces existe d�j�.</font><br />";
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesRestrAccesAppProfP'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result_inter = traite_requete("INSERT INTO setting VALUES ('GepiAccesRestrAccesAppProfP', 'no');");
			if ($result_inter == '')
			$result .= "<font color=\"green\">Le param�tre GepiAccesRestrAccesAppProfP a �t� ajout� � la table setting !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">Le param�tre GepiAccesRestrAccesAppProfP existe d�j� dans la table setting.</font><br />";
		}
		// Module archivage
		$result .= "<br />&nbsp;->Module archivage : ajout (si besoin) du param�tre 'active_annees_anterieures' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name='active_annees_anterieures'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('active_annees_anterieures', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}
		$result .= "<br />&nbsp;->Module archivage : Cr�ation des tables d'archivage<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_aids'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `archivage_aids` (`id` int(11) NOT NULL auto_increment,`annee` varchar(200) NOT NULL default '',`nom` varchar(100) NOT NULL default '',`id_type_aid` int(11) NOT NULL default '0',`productions` varchar(100) NOT NULL default '',`resume` text NOT NULL,`famille` smallint(6) NOT NULL default '0',`mots_cles` text NOT NULL,`adresse1` varchar(255) NOT NULL default '',`adresse2` varchar(255) NOT NULL default '',`public_destinataire` varchar(50) NOT NULL default '',`contacts` text NOT NULL,`divers` text NOT NULL,`matiere1` varchar(100) NOT NULL default '',`matiere2` varchar(100) NOT NULL default '',`fiche_publique` enum('y','n') NOT NULL default 'n',`affiche_adresse1` enum('y','n') NOT NULL default 'n',`en_construction` enum('y','n') NOT NULL default 'n',`notes_moyenne` varchar(255) NOT NULL,`notes_min` varchar(255) NOT NULL,`notes_max` varchar(255) NOT NULL,`responsables` text NOT NULL,`eleves` text NOT NULL,`eleves_resp` text NOT NULL, PRIMARY KEY  (`id`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table archivage_aids a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table archivage_aids existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_eleves2'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `archivage_eleves2` (`annee` varchar(50) NOT NULL default '',`ine` varchar(50) NOT NULL,`doublant` enum('-','R') NOT NULL default '-',`regime` varchar(255) NOT NULL, PRIMARY KEY  (`ine`,`annee`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table archivage_eleves2 a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table archivage_eleves2 existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_eleves'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `archivage_eleves` (`ine` varchar(255) NOT NULL,`nom` varchar(255) NOT NULL default '',`prenom` varchar(255) NOT NULL default '',`sexe` char(1) NOT NULL,`naissance` date NOT NULL default '0000-00-00', PRIMARY KEY  (`ine`),  KEY `nom` (`nom`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table archivage_eleves a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table archivage_eleves existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_disciplines'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `archivage_disciplines` (`id` int(11) NOT NULL auto_increment,`annee` varchar(200) NOT NULL,`INE` varchar(255) NOT NULL,`classe` varchar(255) NOT NULL,`num_periode` tinyint(4) NOT NULL,`nom_periode` varchar(255) NOT NULL,`special` varchar(255) NOT NULL,`matiere` varchar(255) NOT NULL,`prof` varchar(255) NOT NULL,`note` varchar(255) NOT NULL,`moymin` varchar(255) NOT NULL,`moymax` varchar(255) NOT NULL,`moyclasse` varchar(255) NOT NULL,`rang` tinyint(4) NOT NULL,`appreciation` text NOT NULL,`nb_absences` int(11) NOT NULL,`non_justifie` int(11) NOT NULL,`nb_retards` int(11) NOT NULL, PRIMARY KEY  (`id`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table archivage_disciplines a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table archivage_disciplines existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_appreciations_aid'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `archivage_appreciations_aid` (`id_eleve` varchar(255) NOT NULL,`annee` varchar(200) NOT NULL,`classe` varchar(255) NOT NULL,`id_aid` int(11) NOT NULL,`periode` int(11) NOT NULL default '0',`appreciation` text NOT NULL,`note_eleve` varchar(50) NOT NULL,`note_moyenne_classe` varchar(255) NOT NULL,`note_min_classe` varchar(255) NOT NULL,`note_max_classe` varchar(255) NOT NULL,PRIMARY KEY  (`id_eleve`,`id_aid`,`periode`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table archivage_appreciations_aid a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table archivage_appreciations_aid existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_aid_eleve'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `archivage_aid_eleve` (`id_aid` int(11) NOT NULL default '0',`id_eleve` varchar(255) NOT NULL,`eleve_resp` char(1) NOT NULL default 'n',PRIMARY KEY  (`id_aid`,`id_eleve`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table archivage_aid_eleve a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table archivage_aid_eleve existe d�j�.</font><br />";
		}
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_types_aid'"));
		if ($test == 0) {
			$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS `archivage_types_aid` (`id` int(11) NOT NULL auto_increment,`annee` varchar(200) NOT NULL default '',`nom` varchar(100) NOT NULL default '',`nom_complet` varchar(100) NOT NULL default '',`note_sur` int(11) NOT NULL default '0',`type_note` varchar(5) NOT NULL default '', `display_bulletin` char(1) NOT NULL default 'y', PRIMARY KEY  (`id`));");
			if ($result_inter == '')
			$result .= "<font color=\"green\">La table archivage_types_aid a �t� cr��e !</font><br />";
			else
			$result .= $result_inter."<br />";
		} else {
			$result .= "<font color=\"blue\">La table archivage_types_aid existe d�j�.</font><br />";
		}

		// ================ modif jjocal ===============
		$result .= "<br />&nbsp;->Ajout (si besoin) du param�tre 'use_ent' � la table 'setting'<br/>";
		$req_test = mysql_query("SELECT value FROM setting WHERE name = 'use_ent'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$query3 = mysql_query("INSERT INTO setting VALUES ('use_ent', 'n');");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre existe d�j�.</font><br />";
		}

		// Modification Delineau
		// lorsque le trunk sera officiellement en 1.5.1, on supprimera ces lignes
		$result .= "<br />&nbsp;->Mise � jour des tables d'archivage.<br />";
		$result_inter = traite_requete("ALTER TABLE `archivage_aids` CHANGE `annee` `annee` VARCHAR( 200 ) NOT NULL");
		if ($result_inter == '')
		$result .= "<font color=\"green\">Le champ annee de la table archivage_aids a �t� modifi� !</font><br />";
		else
		$result .= $result_inter."<br />";
		$result_inter = traite_requete("ALTER TABLE `archivage_appreciations_aid` CHANGE `annee` `annee` VARCHAR( 200 ) NOT NULL");
		if ($result_inter == '')
		$result .= "<font color=\"green\">Le champ annee de la table archivage_appreciations_aid a �t� modifi� !</font><br />";
		else
		$result .= $result_inter."<br />";
		$result_inter = traite_requete("ALTER TABLE `archivage_disciplines` CHANGE `annee` `annee` VARCHAR( 200 ) NOT NULL");
		if ($result_inter == '')
		$result .= "<font color=\"green\">Le champ annee de la table archivage_disciplines a �t� modifi� !</font><br />";
		else
		$result .= $result_inter."<br />";
		$result_inter = traite_requete("ALTER TABLE `archivage_eleves2` CHANGE `annee` `annee` VARCHAR( 50 ) NOT NULL");
		if ($result_inter == '')
		$result .= "<font color=\"green\">Le champ annee de la table archivage_eleves2 a �t� modifi� !</font><br />";
		else
		$result .= $result_inter."<br />";
		$result_inter = traite_requete("ALTER TABLE `archivage_types_aid` CHANGE `annee` `annee` VARCHAR( 200 ) NOT NULL");
		if ($result_inter == '')
		$result .= "<font color=\"green\">Le champ annee de la table archivage_types_aid a �t� modifi� !</font><br />";
		else
		$result .= $result_inter."<br />";
		$result_inter = traite_requete("ALTER TABLE `archivage_aid_eleve` CHANGE `id_aid` `id_aid` INT( 11 ) NOT NULL DEFAULT '0'");
		if ($result_inter == '')
		$result .= "<font color=\"green\">Le champ id_aid de la table archivage_aid_eleve a �t� modifi� !</font><br />";
		else
		$result .= $result_inter."<br />";
		// Fin des lignes � supprimer quand la version stable sera sortie


		//==========================================================
		// Modification Delineau
		if (getSettingValue("active_version152")=="y") { // lorsque le trunk sera officiellement en 1.5.2, on supprimera ce test
			$result .= "<br />&nbsp;->Tentative de cr�ation de la table j_aid_utilisateurs_gest.<br />";
			$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'j_aid_utilisateurs_gest'"));
			if ($test == 0) {
				$result_inter = traite_requete("CREATE TABLE j_aid_utilisateurs_gest (id_aid varchar(100) NOT NULL default '', id_utilisateur varchar(50) NOT NULL default '', indice_aid int(11) NOT NULL default '0', PRIMARY KEY  (id_aid,id_utilisateur))");
				if ($result_inter == '')
				$result .= "<font color=\"green\">La table j_aid_utilisateurs_gest a �t� cr��e !</font><br />";
				else
				$result .= $result_inter."<br />";
			} else {
				$result .= "<font color=\"blue\">La table j_aid_utilisateurs_gest existe d�j�.</font><br />";
			}
		}

		//==========================================================
		$result .= "<br />&nbsp;->Ajout du champ 'affiche_ine' � la table 'model_bulletin'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM model_bulletin LIKE 'affiche_ine'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE model_bulletin ADD affiche_ine TINYINT NOT NULL;");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}
		//==========================================================


		//==========================================================
		$result .= "<br />&nbsp;->Contr�le/Mise � jour du dispositif Notanet/Fiches Brevet<br />";
		$temoin_notanet_err=0;

		$sql="CREATE TABLE IF NOT EXISTS notanet (
login varchar(50) NOT NULL default '',
ine text NOT NULL,
id_mat tinyint(4) NOT NULL,
notanet_mat varchar(255) NOT NULL,
matiere varchar(50) NOT NULL,
note varchar(4) NOT NULL default '',
note_notanet varchar(4) NOT NULL,
id_classe smallint(6) NOT NULL default '0'
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}

		$sql="SHOW COLUMNS FROM notanet LIKE 'note_notanet';";
		$test1 = mysql_num_rows(mysql_query($sql));

		$sql="SHOW COLUMNS FROM notanet LIKE 'id_mat';";
		$test2 = mysql_num_rows(mysql_query($sql));

		$sql="SHOW COLUMNS FROM notanet LIKE 'notanet_mat';";
		$test3 = mysql_num_rows(mysql_query($sql));

		if(($test1 == 0)||($test2 == 0)||($test3 == 0)) {
			$result .= "Suppression de l'ancienne table 'notanet': ";
			$query3 = mysql_query("DROP TABLE notanet;");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
				$temoin_notanet_err++;
			}
		}

		$sql="CREATE TABLE IF NOT EXISTS notanet (
login varchar(50) NOT NULL default '',
ine text NOT NULL,
id_mat tinyint(4) NOT NULL,
notanet_mat varchar(255) NOT NULL,
matiere varchar(50) NOT NULL,
note varchar(4) NOT NULL default '',
note_notanet varchar(4) NOT NULL,
id_classe smallint(6) NOT NULL default '0'
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}

		$sql="CREATE TABLE IF NOT EXISTS notanet_app (
login varchar(50) NOT NULL,
id_mat tinyint(4) NOT NULL,
matiere varchar(50) NOT NULL,
appreciation text NOT NULL,
id int(11) NOT NULL auto_increment,
PRIMARY KEY  (id)
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet_app': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}

		$sql="CREATE TABLE IF NOT EXISTS notanet_corresp (
id int(11) NOT NULL auto_increment,
type_brevet tinyint(4) NOT NULL,
id_mat tinyint(4) NOT NULL,
notanet_mat varchar(255) NOT NULL default '',
matiere varchar(50) NOT NULL default '',
statut enum('imposee','optionnelle','non dispensee dans l etablissement') NOT NULL default 'imposee',
PRIMARY KEY  (id)
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet_corresp': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}

		$sql="SHOW COLUMNS FROM notanet_corresp LIKE 'type_brevet';";
		$test1 = mysql_num_rows(mysql_query($sql));
		if($test1 == 0) {
			$result .= "<br />Ajout du champ 'type_brevet' � la table 'notanet_corresp': ";
			$query3 = mysql_query("ALTER TABLE notanet_corresp ADD type_brevet TINYINT NOT NULL AFTER id;");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
				$temoin_notanet_err++;
			}
		}

		$sql="SHOW COLUMNS FROM notanet_corresp LIKE 'id_mat';";
		$test1 = mysql_num_rows(mysql_query($sql));
		if($test1 == 0) {
			$result .= "<br />Ajout du champ 'id_mat' � la table 'notanet_corresp': ";
			$query3 = mysql_query("ALTER TABLE `notanet_corresp` ADD `id_mat` TINYINT NOT NULL AFTER `type_brevet` ;");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
				$temoin_notanet_err++;
			}
		}

		$sql="CREATE TABLE IF NOT EXISTS notanet_ele_type (
login varchar(50) NOT NULL,
type_brevet tinyint(4) NOT NULL,
PRIMARY KEY  (login)
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet_ele_type': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}

		$sql="CREATE TABLE IF NOT EXISTS notanet_verrou (
id_classe TINYINT NOT NULL ,
type_brevet TINYINT NOT NULL ,
verrouillage CHAR( 1 ) NOT NULL
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet_verrou': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}

		$sql="CREATE TABLE IF NOT EXISTS notanet_avis (
login VARCHAR( 50 ) NOT NULL ,
favorable ENUM( 'O', 'N', '' ) NOT NULL ,
avis TEXT NOT NULL ,
PRIMARY KEY ( login )
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet_avis': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}

		$sql="CREATE TABLE IF NOT EXISTS notanet_socles (
login VARCHAR( 50 ) NOT NULL ,
b2i ENUM( 'MS', 'ME', 'MN', 'AB', '' ) NOT NULL ,
a2 ENUM( 'MS', 'ME', 'MN', 'AB', '' ) NOT NULL ,
lv VARCHAR( 50 ) NOT NULL ,
PRIMARY KEY ( login )
);";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "Erreur sur la cr�ation de la table 'notanet_socles': ".$result_inter."<br />";
			$temoin_notanet_err++;
		}


		$result .= "<br />Contr�le des valeurs du champ 'b2i' de 'notanet_socles': ";
		$query3 = mysql_query("ALTER TABLE notanet_socles CHANGE b2i b2i ENUM( 'MS', 'ME', 'MN', 'AB', '' ) NOT NULL;");
		if ($query3) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
			$temoin_notanet_err++;
		}

		$result .= "<br />Contr�le des valeurs du champ 'a2' de 'notanet_socles': ";
		$query3 = mysql_query("ALTER TABLE notanet_socles CHANGE a2 a2 ENUM( 'MS', 'ME', 'MN', 'AB', '' ) NOT NULL;");
		if ($query3) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
			$temoin_notanet_err++;
		}

		$result .= "<br />Contr�le des valeurs du champ 'favorable' de 'notanet_avis': ";
		$query3 = mysql_query("ALTER TABLE notanet_avis CHANGE favorable favorable ENUM( 'O', 'N', '' ) NOT NULL;");
		if ($query3) {
			$result .= "<font color=\"green\">Ok !</font><br />";
		} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
			$temoin_notanet_err++;
		}



		if($temoin_notanet_err==0) {$result .= "<font color=\"green\">Ok !</font><br />";}


		//==========================================================

		$result .= "<br />Contr�le de la conversion de la table 'j_eleves_etablissements': ";

		$sql="SELECT 1=1 FROM setting WHERE name='conversion_j_eleves_etablissements';";
		$test_conv=mysql_query($sql);
		if(mysql_num_rows($test_conv)>0) {
			$result .= "<font color=\"blue\">D�j� effectu�e !</font><br />";
			//echo "<p>La conversion a d�j� �t� effectu�e.</p>\n";
		}
		else {
			$cpt_correction_ok=0;
			$cpt_correction_err=0;
			$cpt_nettoyage_ok=0;
			$cpt_nettoyage_err=0;

			$result .= "<br />Remplacement du LOGIN par l'ELENOET dans la table 'j_eleves_etablissements': ";

			$sql="SELECT id_eleve FROM j_eleves_etablissements;";
			$res_ele_etab=mysql_query($sql);
			if(mysql_num_rows($res_ele_etab)>0) {
				while($lig_ee=mysql_fetch_object($res_ele_etab)) {
					$sql="SELECT elenoet FROM eleves WHERE login='$lig_ee->id_eleve';";
					$test_ele=mysql_query($sql);
					if(mysql_num_rows($test_ele)>0) {
						$lig_ele=mysql_fetch_object($test_ele);
						if($lig_ele->elenoet!="") {
							$sql="UPDATE j_eleves_etablissements SET id_eleve='$lig_ele->elenoet' WHERE id_eleve='$lig_ee->id_eleve';";
							$correction=mysql_query($sql);
							if($correction) {
								$cpt_correction_ok++;
							}
							else {
								$cpt_correction_err++;
							}
						}
					}
					else {
						// On a une scorie: �l�ve qui n'est plus dans la table 'eleves'
						$sql="DELETE FROM j_eleves_etablissements WHERE id_eleve='$lig_ee->id_eleve';";
						$nettoyage=mysql_query($sql);
						if($nettoyage) {
							$cpt_nettoyage_ok++;
						}
						else {
							$cpt_nettoyage_err++;
						}
					}
				}
			}

			$result .= "<p>R�sultat des conversions:</p>\n";
			$result .= "<table class='boireaus' border='1' summary='Compte-rendu'>\n";
			$result .= "<tr><th>&nbsp;</th><th>Succ�s</th><th>Echec</th></tr>\n";
			$result .= "<tr><th>Conversion</th><td>$cpt_correction_ok</td><td>$cpt_correction_err</td></tr>\n";
			$result .= "<tr><th>Suppression de scories</th><td>$cpt_nettoyage_ok</td><td>$cpt_nettoyage_err</td></tr>\n";
			$result .= "</table>\n";

			$sql="INSERT INTO setting SET name='conversion_j_eleves_etablissements', value='effectuee';";
			$res_temoin=mysql_query($sql);
			if($res_temoin) {
				$result .= "<p>Mise en place d'un t�moin indiquant que la conversion est effectu�e.</p>\n";
			}
			else {
				$result .= "<p>ECHEC de la mise en place d'un t�moin indiquant que la conversion est effectu�e.</p>\n";
			}


		}



		$result .= "<br />&nbsp;->Ajout du champ 'lieu_naissance' � la table 'eleves'<br />";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM eleves LIKE 'lieu_naissance'"));
		if ($test1 == 0) {
			$query3 = mysql_query("ALTER TABLE eleves ADD lieu_naissance VARCHAR( 50 ) NOT NULL AFTER naissance ;");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
		}

		$test = sql_query1("SHOW TABLES LIKE 'communes'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 'communes'. ";
			$sql="CREATE TABLE IF NOT EXISTS communes (
code_commune_insee VARCHAR( 50 ) NOT NULL ,
departement VARCHAR( 50 ) NOT NULL ,
commune VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( code_commune_insee )
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 'communes': ".$result_inter."<br />";
				$temoin_notanet_err++;
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 'commentaires_types'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 'commentaires_types'. ";
			$sql="CREATE TABLE IF NOT EXISTS commentaires_types (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
commentaire TEXT NOT NULL ,
num_periode INT NOT NULL ,
id_classe INT NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 'commentaires_types': ".$result_inter."<br />";
				$temoin_notanet_err++;
			}
		}


		$test = sql_query1("SHOW TABLES LIKE 'modele_bulletin'");
		if ($test == -1) {
			$sql="SELECT * FROM model_bulletin;";
			$res_model=mysql_query($sql);
			if(mysql_num_rows($res_model)>0) {
				$cpt=0;
				while($tab_model[$cpt]=mysql_fetch_assoc($res_model)) {
					$id_model[$cpt]=$tab_model[$cpt]['id_model_bulletin'];
					//echo "\$id_model[$cpt]=\$tab_model[$cpt]['id_model_bulletin']=".$tab_model[$cpt]['id_model_bulletin']."<br />";
					$cpt++;
				}
/*
for($i=0;$i<count($tab_model);$i++) {
if(!empty($tab_model[$i])) {
	//echo "<p>\$tab_model[$i]</p>";
	echo "<p>Enregistrement \$tab_model[$i] de l'ancienne table 'model_bulletin'.</p>\n";
	echo "<table border='1'>\n";
	foreach($tab_model[$i] as $key => $value) {
		echo "<tr>\n";
		echo "<th>$key</th>\n";
		echo "<td>$value</td>\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
}
}
*/
				//$sql="DROP TABLE modele_bulletin;";
				//$nettoyage=mysql_query($sql);

				$result .= "<br />Cr�ation de latable 'modele_bulletin'<br />";
				$sql="CREATE TABLE IF NOT EXISTS modele_bulletin (
id_model_bulletin INT( 11 ) NOT NULL ,
nom VARCHAR( 255 ) NOT NULL ,
valeur VARCHAR( 255 ) NOT NULL
);";
				$res_model=mysql_query($sql);
				if(!$res_model) {
					$result .= "<font color=\"red\">Erreur</font><br />";
					//echo "<p>ERREUR sur $sql</p>\n";
				}
				else {
					for($i=0;$i<count($tab_model);$i++) {
						$cpt=0;
						//if(isset($tab_model[$i])) {
						if(!empty($tab_model[$i])) {
							//echo "<p>\$tab_model[$i]: ";
							$result .= "Enregistrements d'apr�s \$tab_model[$i] dans la nouvelle table 'modele_bulletin': ";
							foreach($tab_model[$i] as $key => $value) {
								if($cpt>0) {$result .= ", ";}

								$sql="INSERT INTO modele_bulletin SET id_model_bulletin='".$id_model[$i]."', nom='".$key."', valeur='".$value."';";
								$insert=mysql_query($sql);
								if($insert) {
									$result .= "<span style='color:green;'>$key:$value</span> ";
								}
								else {
									$result .= "<span style='color:red;'>$key:$value</span> ";
								}
								$cpt++;
							}
							$result .= "<br />\n";
						}
					}
				}
			}
			else {
				$result .= "<br /><span style='color:red;'>Erreur:</span> L'ancienne table 'model_bulletin' semble absente???<br />";
			}
		}
		else {
			$result .= "<br />La table 'modele_bulletin' existe d�j�.<br />";
		}

		//==========================================================
		// ALTER TABLE `ct_devoirs_entry` ADD `vise` CHAR( 1 ) NOT NULL DEFAULT 'n' AFTER `contenu` ;
		// ALTER TABLE `ct_entry` ADD `vise` VARCHAR( 1 ) NOT NULL DEFAULT 'n' AFTER `contenu` ;
		// ALTER TABLE `ct_entry` ADD `visa` VARCHAR( 1 ) NOT NULL DEFAULT 'n' AFTER `vise` ;

		// Modification de la base suite au dispositif de visa des cahiers de textes
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_devoirs_entry LIKE 'vise'"));
		if ($test == 0) {
			$result_inter .= traite_requete("ALTER TABLE `ct_devoirs_entry` ADD `vise` CHAR( 1 ) NOT NULL DEFAULT 'n' AFTER `contenu` ;");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Le champ vise a bien �t� cr�� dans la table ct_devoirs_entry !</font><br />";
			} else {
				$result .= $result_inter."<br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ vise dans la table ct_devoirs_entry existe d�j�.</font><br />";
		}

		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_entry LIKE 'vise'"));
		if ($test == 0) {
			$result_inter .= traite_requete("ALTER TABLE `ct_entry` ADD `vise` VARCHAR( 1 ) NOT NULL DEFAULT 'n' AFTER `contenu` ;");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Le champ vise a bien �t� cr�� dans la table ct_entry !</font><br />";
			} else {
				$result .= $result_inter."<br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ vise dans la table ct_entry existe d�j�.</font><br />";
		}

		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_entry LIKE 'visa'"));
		if ($test == 0) {
			$result_inter .= traite_requete("ALTER TABLE `ct_entry` ADD `visa` VARCHAR( 1 ) NOT NULL DEFAULT 'n' AFTER `vise` ;");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Le champ visa a bien �t� cr�� dans la table ct_entry !</font><br />";
			} else {
				$result .= $result_inter."<br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ visa dans la table ct_entry existe d�j�.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'visa_cdt_inter_modif_notices_visees'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('visa_cdt_inter_modif_notices_visees', 'yes');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre visa_cdt_inter_modif_notices_visees � 'yes': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre visa_cdt_inter_modif_notices_visees � 'yes': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre visa_cdt_inter_modif_notices_visees existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'texte_visa_cdt'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('texte_visa_cdt', 'Cahier de textes vis� ce jour <br />Le Principal <br /> M. XXXXX<br />');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre texte_visa_cdt : Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre texte_visa_cdt : Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre texte_visa_cdt existe d�j� dans la table setting.</font><br />";
		}



		// Modification de la table utilisateurs (ajout de auth_mode)
		$test = mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'auth_mode'"));
		if ($test == 0) {
			$result_inter .= traite_requete("ALTER TABLE utilisateurs ADD auth_mode ENUM( 'gepi', 'ldap', 'sso' ) NOT NULL DEFAULT 'gepi';");
			if ($result_inter == '') {
				$result .= "<font color=\"green\">Le champ auth_mode a bien �t� cr�� dans la table utilisateurs !</font><br />";
				if (getSettingValue("use_sso") == 'yes') {
					$update = mysql_query("UPDATE utilisateurs SET auth_mode = 'sso'");
				}
			} else {
				$result .= $result_inter."<br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le champ auth_mode dans la table utilisateurs existe d�j�.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'auth_locale'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('auth_locale', 'yes');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre auth_locale � 'yes': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre auth_locale � 'yes': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre auth_locale existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'auth_ldap'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('auth_ldap', 'no');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre auth_ldap � 'no': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre auth_ldap � 'no': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre auth_ldap existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'auth_sso'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('auth_sso', 'no');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre auth_sso � 'no': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre auth_sso � 'no': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre auth_sso existe d�j� dans la table setting.</font><br />";
		}

		if (getSettingValue('use_sso') == 'yes') {
			saveSetting('auth_sso','yes');
			saveSetting('auth_locale','no');
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'use_sso'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==1){
			$result_inter = traite_requete("DELETE FROM setting WHERE (name = 'use_sso');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">Suppression du param�tre use_sso: Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">Suppression du param�tre use_sso: Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre use_sso est d�j� supprim� de la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'may_import_user_profile'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('may_import_user_profile', 'no');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre may_import_user_profile � 'no': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre may_import_user_profile � 'no': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre may_import_user_profile existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'ldap_write_access'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('ldap_write_access', 'no');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre ldap_write_access � 'no': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre ldap_write_access � 'no': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre ldap_write_access existe d�j� dans la table setting.</font><br />";
		}


		$test = sql_query1("SHOW TABLES LIKE 'commentaires_types_profs'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 'commentaires_types_profs'. ";
			$sql="CREATE TABLE IF NOT EXISTS commentaires_types_profs (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
login VARCHAR( 255 ) NOT NULL ,
app TEXT NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 'commentaires_types_profs': ".$result_inter."<br />";
			}
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'denomination_professeur'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('denomination_professeur', 'professeur');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre denomination_professeur � 'professeur': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre denomination_professeur � 'professeur': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre denomination_professeur existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'denomination_professeurs'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('denomination_professeurs', 'professeurs');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre denomination_professeurs � 'professeurs': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre denomination_professeurs � 'professeurs': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre denomination_professeurs existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'denomination_responsable'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('denomination_responsable', 'responsable l�gal');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre denomination_responsable � 'responsable l�gal': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre denomination_responsable � 'responsable l�gal': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre denomination_responsable existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'denomination_responsables'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('denomination_responsables', 'responsables l�gaux');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre denomination_responsables � 'responsables l�gaux': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre denomination_responsables � 'responsables l�gaux': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre denomination_responsables existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'denomination_eleve'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('denomination_eleve', '�l�ve');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre denomination_eleve � '�l�ve': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre denomination_eleve � '�l�ve': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre denomination_eleve existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'denomination_eleves'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('denomination_eleves', '�l�ves');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre denomination_eleves � '�l�ves': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre denomination_eleves � '�l�ves': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre denomination_eleves existe d�j� dans la table setting.</font><br />";
		}

		// Ajouts d'index
		$result .= "&nbsp;->Ajout de l'index 'statut' � la table utilisateurs<br />";
		$req_test = mysql_query("SHOW INDEX FROM utilisateurs WHERE Key_name = 'statut'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `utilisateurs` ADD INDEX statut ( `statut` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de l'index 'etat' � la table utilisateurs<br />";
		$req_test = mysql_query("SHOW INDEX FROM utilisateurs WHERE Key_name = 'etat'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `utilisateurs` ADD INDEX etat ( `etat` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Ajout de l'index 'login' � la table resp_pers<br />";
		$req_test = mysql_query("SHOW INDEX FROM resp_pers WHERE Key_name = 'login'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `resp_pers` ADD INDEX login ( `login` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de l'index 'adr_id' � la table resp_pers<br />";
		$req_test = mysql_query("SHOW INDEX FROM resp_pers WHERE Key_name = 'adr_id'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `resp_pers` ADD INDEX adr_id ( `adr_id` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}


		$result .= "&nbsp;->Ajout de l'index 'pers_id' � la table responsables2<br />";
		$req_test = mysql_query("SHOW INDEX FROM responsables2 WHERE Key_name = 'pers_id'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `responsables2` ADD INDEX pers_id ( `pers_id` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de l'index 'ele_id' � la table responsables2<br />";
		$req_test = mysql_query("SHOW INDEX FROM responsables2 WHERE Key_name = 'ele_id'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `responsables2` ADD INDEX ele_id ( `ele_id` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de l'index 'resp_legal' � la table responsables2<br />";
		$req_test = mysql_query("SHOW INDEX FROM responsables2 WHERE Key_name = 'resp_legal'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `responsables2` ADD INDEX resp_legal ( `resp_legal` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de l'index 'ele_id' � la table eleves<br />";
		$req_test = mysql_query("SHOW INDEX FROM eleves WHERE Key_name = 'ele_id'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `eleves` ADD INDEX ele_id ( `ele_id` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}

		$result .= "&nbsp;->Ajout de l'index 'id_classe' � la table j_eleves_classes<br />";
		$req_test = mysql_query("SHOW INDEX FROM j_eleves_classes WHERE Key_name = 'id_classe'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `j_eleves_classes` ADD INDEX id_classe ( `id_classe` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}


		//------------------------------------------------------------------------
		// Fin du bloc de mise � jour 1.5.1. Les mises � jour jusqu'� la diffusion
		// de la 1.5.1 stable doivent se situer au-dessus de cette ligne !
		//------------------------------------------------------------------------
	}


	#-----------------------------
	#	MISE A JOUR GEPI 1.5.2
	#-----------------------------
	if (($force_maj == 'yes') or (quelle_maj("1.5.2"))) {
		$result .= "<br /><br /><b>Mise � jour vers la version 1.5.2" . $rc . $beta . " :</b><br />";

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'sso_display_portail'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('sso_display_portail','no');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre sso_display_portail � 'no': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre sso_display_portail � 'no': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre sso_use_portail existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'sso_url_portail'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('sso_url_portail', 'https://www.example.com');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre sso_url_portail � 'https://www.example.com': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre sso_url_portail � 'https://www.example.com': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre denomination_eleves existe d�j� dans la table setting.</font><br />";
		}

		$req_test=mysql_query("SELECT value FROM setting WHERE name = 'sso_hide_logout'");
		$res_test=mysql_num_rows($req_test);
		if ($res_test==0){
			$result_inter = traite_requete("INSERT INTO setting VALUES ('sso_hide_logout', 'no');");
			if ($result_inter == '') {
				$result.="<font color=\"green\">D�finition du param�tre sso_hide_logout � 'no': Ok !</font><br />";
			} else {
				$result.="<font color=\"red\">D�finition du param�tre sso_hide_logout � 'no': Erreur !</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">Le param�tre sso_hide_logout existe d�j� dans la table setting.</font><br />";
		}

		// Module discipline
		$test = sql_query1("SHOW TABLES LIKE 's_incidents'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_incidents'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_incidents (
id_incident INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
declarant VARCHAR( 50 ) NOT NULL ,
date DATE NOT NULL ,
heure VARCHAR( 20 ) NOT NULL ,
id_lieu INT( 11 ) NOT NULL ,
nature VARCHAR( 255 ) NOT NULL ,
description TEXT NOT NULL,
etat VARCHAR( 20 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_incidents': ".$result_inter."<br />";
			}
		}
		// Avec cette table on ne g�re pas un historique des modifications de d�claration...


		$test = sql_query1("SHOW TABLES LIKE 's_qualites'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_qualites'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_qualites (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
qualite VARCHAR( 50 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_qualites': ".$result_inter."<br />";
			}
			else {
				$tab_qualite=array("Responsable","Victime","T�moin","Autre");
				for($loop=0;$loop<count($tab_qualite);$loop++) {
					$sql="SELECT 1=1 FROM s_qualites WHERE qualite='".$tab_qualite[$loop]."';";
					//echo "$sql<br />";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$sql="INSERT INTO s_qualites SET qualite='".$tab_qualite[$loop]."';";
						$insert=mysql_query($sql);
					}
				}
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_types_sanctions'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_types_sanctions'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_types_sanctions (
id_nature INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
nature VARCHAR( 255 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_types_sanctions': ".$result_inter."<br />";
			}
			else {
				$tab_type=array("Avertissement travail","Avertissement comportement");
				for($loop=0;$loop<count($tab_type);$loop++) {
					$sql="SELECT 1=1 FROM s_types_sanctions WHERE nature='".$tab_type[$loop]."';";
					//echo "$sql<br />";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$sql="INSERT INTO s_types_sanctions SET nature='".$tab_type[$loop]."';";
						$insert=mysql_query($sql);
					}
				}
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_autres_sanctions'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_autres_sanctions'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_autres_sanctions (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
id_sanction INT( 11 ) NOT NULL ,
id_nature INT( 11 ) NOT NULL ,
description TEXT NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_autres_sanctions': ".$result_inter."<br />";
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_mesures'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_mesures'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_mesures (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
type ENUM('prise','demandee') ,
mesure VARCHAR( 50 ) NOT NULL ,
commentaire TEXT NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_mesures': ".$result_inter."<br />";
			}
			else {
				// Mesures prises
				$tab_mesure=array("Travail suppl�mentaire","Mot dans le carnet de liaison");
				for($loop=0;$loop<count($tab_mesure);$loop++) {
					$sql="SELECT 1=1 FROM s_mesures WHERE mesure='".$tab_mesure[$loop]."';";
					//echo "$sql<br />";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$sql="INSERT INTO s_mesures SET mesure='".$tab_mesure[$loop]."', type='prise';";
						$insert=mysql_query($sql);
					}
				}

				// Mesures demand�es
				$tab_mesure=array("Retenue","Exclusion");
				for($loop=0;$loop<count($tab_mesure);$loop++) {
					$sql="SELECT 1=1 FROM s_mesures WHERE mesure='".$tab_mesure[$loop]."';";
					//echo "$sql<br />";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$sql="INSERT INTO s_mesures SET mesure='".$tab_mesure[$loop]."', type='demandee';";
						$insert=mysql_query($sql);
					}
				}
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_traitement_incident'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_traitement_incident'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_traitement_incident (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
id_incident INT( 11 ) NOT NULL ,
login_ele VARCHAR( 50 ) NOT NULL ,
login_u VARCHAR( 50 ) NOT NULL ,
id_mesure INT( 11 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_traitement_incident': ".$result_inter."<br />";
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_lieux_incidents'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_lieux_incidents'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_lieux_incidents (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
lieu VARCHAR( 255 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_lieux_incidents': ".$result_inter."<br />";
			}
			else {
				$tab_lieu=array("Classe","Couloir","Cour","R�fectoire","Autre");
				for($loop=0;$loop<count($tab_lieu);$loop++) {
					$sql="SELECT 1=1 FROM s_lieux_incidents WHERE lieu='".$tab_lieu[$loop]."';";
					//echo "$sql<br />";
					$test=mysql_query($sql);
					if(mysql_num_rows($test)==0) {
						$sql="INSERT INTO s_lieux_incidents SET lieu='".$tab_lieu[$loop]."';";
						$insert=mysql_query($sql);
					}
				}
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_protagonistes'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_incidents'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_protagonistes (
id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
id_incident INT NOT NULL ,
login VARCHAR( 50 ) NOT NULL ,
statut VARCHAR( 50 ) NOT NULL ,
qualite VARCHAR( 50 ) NOT NULL,
avertie ENUM('N','O') NOT NULL DEFAULT 'N'
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_protagonistes': ".$result_inter."<br />";
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_sanctions'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_sanctions'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_sanctions (
id_sanction INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
login VARCHAR( 50 ) NOT NULL ,
description TEXT NOT NULL ,
nature VARCHAR( 255 ) NOT NULL ,
effectuee ENUM( 'N', 'O' ) NOT NULL ,
id_incident INT( 11 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_sanctions': ".$result_inter."<br />";
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_communication'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_communication'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_communication (
id_communication INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
id_incident INT( 11 ) NOT NULL ,
login VARCHAR( 50 ) NOT NULL ,
nature VARCHAR( 255 ) NOT NULL ,
description TEXT NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_communication': ".$result_inter."<br />";
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_travail'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_travail'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_travail (
id_travail INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
id_sanction INT( 11 ) NOT NULL ,
date_retour DATE NOT NULL ,
heure_retour VARCHAR( 20 ) NOT NULL ,
travail TEXT NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_travail': ".$result_inter."<br />";
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_retenues'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_retenues'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_retenues (
id_retenue INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
id_sanction INT( 11 ) NOT NULL ,
date DATE NOT NULL ,
heure_debut VARCHAR( 20 ) NOT NULL ,
duree FLOAT NOT NULL ,
travail TEXT NOT NULL ,
lieu VARCHAR( 255 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_retenues': ".$result_inter."<br />";
			}
		}

		$test = sql_query1("SHOW TABLES LIKE 's_exclusions'");
		if ($test == -1) {
			$result .= "<br />Cr�ation de la table 's_exclusions'. ";
			$sql="CREATE TABLE IF NOT EXISTS s_exclusions (
id_exclusion INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
id_sanction INT( 11 ) NOT NULL ,
date_debut DATE NOT NULL ,
heure_debut VARCHAR( 20 ) NOT NULL ,
date_fin DATE NOT NULL ,
heure_fin VARCHAR( 20 ) NOT NULL,
travail TEXT NOT NULL ,
lieu VARCHAR( 255 ) NOT NULL
);";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result .= "<br />Erreur sur la cr�ation de la table 's_exclusions': ".$result_inter."<br />";
			}
		}

		// Fin du module discipline

		//module carnet de note
		$result .= "<br />Modification de la table 'cn_devoirs'. ";
		$testcn_devoirs_note_sur = mysql_num_rows(mysql_query("SHOW COLUMNS FROM cn_devoirs LIKE 'note_sur'"));
		if ($testcn_devoirs_note_sur == 0) {
			$query = mysql_query("ALTER TABLE `cn_devoirs` ADD `note_sur` INT(11) DEFAULT '20' AFTER `coef` ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font>";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<br /><font color=\"blue\">La colonne 'note_sur' existe deja.</font><br />";
		}

		$testcn_devoirs_ramener_sur_referentiel = mysql_num_rows(mysql_query("SHOW COLUMNS FROM cn_devoirs LIKE 'ramener_sur_referentiel'"));
		if ($testcn_devoirs_ramener_sur_referentiel == 0) {
			$result_inter = traite_requete("ALTER TABLE `cn_devoirs` ADD `ramener_sur_referentiel` CHAR(1) NOT NULL DEFAULT 'F' AFTER `note_sur` ;");
			if ($query) {
				$result .= "<font color=\"green\"> Ok !</font><br />";
			} else {
				$result .= "<br />Erreur sur la modification de la table 'cn_devoirs': ".$result_inter."<br />";
			}
		} else {
			$result .= "<font color=\"blue\">La colonne 'ramener_sur_referentiel' existe deja.</font><br />";
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'note_autre_que_sur_referentiel'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('note_autre_que_sur_referentiel', 'F');");
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'referentiel_note'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0){
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('referentiel_note', '20');");
		}


		//fin module carnet de note

		$sql="SELECT 1=1 FROM setting WHERE name='unzipped_max_filesize';";
		$query = mysql_query($sql);
		if (mysql_num_rows($query)==0) {
			$result .= "<br />Initialisation de la taille maximale d'un fichier extrait d'une archive ZIP&nbsp;: ";
			$sql="INSERT INTO setting SET name='unzipped_max_filesize',value='10';";
			$result_inter = traite_requete($sql);
			if ($result_inter != '') {
				$result.="<font color=\"red\">Erreur</font><br />";
			}
			else {
				$result.="<font color=\"green\">OK</font><br />";
			}
		}

		// Module ann�e ant�rieure
		$result .= "<br />Mise � jour de la table archivage_types_aid.<br />";
		$result_inter = traite_requete("ALTER TABLE archivage_types_aid ADD outils_complementaires ENUM( 'y', 'n' ) NOT NULL DEFAULT 'n' AFTER display_bulletin");
		if ($result_inter == '')
		$result .= "<font color=\"green\">Le champ outils_complementaires de la table archivage_types_aid a �t� ajout� !</font><br />";
		else
		$result .= $result_inter."<br />";


		$result .= "&nbsp;->Cr�ation de la absences_repas<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'absences_repas'"));
		if ($test == 0) {
			$query3 = mysql_query("CREATE TABLE `absences_repas` (`id` int(5) NOT NULL auto_increment, `date_repas` date NOT NULL default '0000-00-00', `id_groupe` varchar(8) NOT NULL, `eleve_id` varchar(30) NOT NULL, `pers_id` varchar(30) NOT NULL, PRIMARY KEY  (`id`));");
			if ($query3) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table absences_repas existe d�j�</font><br />";
		}

		//module cahier de texte 2
		$test = sql_query1("SHOW TABLES LIKE 'ct_devoirs_documents'");
		if ($test == -1) {
			$sql="CREATE TABLE ct_devoirs_documents
		(
		id INTEGER  NOT NULL AUTO_INCREMENT COMMENT 'Id document',
		id_ct_devoir INTEGER default 0 NOT NULL COMMENT 'Id devoir du cahier de texte',
		titre VARCHAR(255)  NOT NULL COMMENT 'titre du document',
		taille INTEGER default 0 NOT NULL COMMENT 'Taille du document',
		emplacement VARCHAR(255)  NOT NULL COMMENT 'chemin vers le document',
		PRIMARY KEY (id),
		INDEX ct_devoirs_documents_FI_1 (id_ct_devoir)
		);";
		}
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "<br />Erreur sur la cr�ation de la table 'ct_devoirs_documents': ".$result_inter."<br />";
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiCahierTexteVersion'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiCahierTexteVersion', '1');");
		}

		//ajout de la possibilit� null sur certaines colonnes
		$result_inter .= traite_requete("ALTER TABLE ct_entry MODIFY id_login varchar(32);");
		$result_inter .= traite_requete("ALTER TABLE ct_devoirs_entry MODIFY id_login varchar(32);");

		$test = sql_query1("SHOW TABLES LIKE 'ct_private_entry'");
		if ($test == -1) {
			$sql="CREATE TABLE ct_private_entry
		(
		id_ct INTEGER  NOT NULL AUTO_INCREMENT COMMENT 'Cle primaire de la cotice privee',
		heure_entry TIME default '00:00:00' NOT NULL COMMENT 'heure de l\'entree',
		date_ct INTEGER default 0 NOT NULL COMMENT 'date du compte rendu',
		contenu TEXT  NOT NULL COMMENT 'contenu redactionnel du compte rendu',
		id_groupe INTEGER  NOT NULL COMMENT 'Cle etrangere du groupe auquel appartient le compte rendu',
		id_login VARCHAR(32)  COMMENT 'Cle etrangere de l\'utilisateur auquel appartient le compte rendu',
		PRIMARY KEY (id_ct),
		INDEX ct_private_entry_FI_1 (id_groupe),
		CONSTRAINT ct_private_entry_FK_1
		FOREIGN KEY (id_groupe)
		REFERENCES groupes (id)
		ON DELETE CASCADE,
		INDEX ct_private_entry_FI_2 (id_login),
		CONSTRAINT ct_private_entry_FK_2
		FOREIGN KEY (id_login)
		REFERENCES utilisateurs (login)
		ON DELETE CASCADE
		)Type=MyISAM COMMENT='Notice privee du cahier de texte';";
		}
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "<br />Erreur sur la cr�ation de la table 'ct_private_entry': ".$result_inter."<br />";
		}

		//fin module cahier texte 2


		$result .= "&nbsp;->On autorise 40 caract�res pour le champ 'message' de la table 'aid_config'<br />";
		$sql = "ALTER TABLE aid_config CHANGE message message VARCHAR( 40 ) NOT NULL;";
		$result_inter = traite_requete($sql);
		if ($result_inter != '') {
			$result .= "<br />Erreur lors de l'augmentation � 40 caract�res du champ 'message' de la table 'aid_config': ".$result_inter."<br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'date_verrouillage' � la table 'periodes': ";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM periodes LIKE 'date_verrouillage'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE periodes ADD date_verrouillage TIMESTAMP NOT NULL ;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else {
			$result .= "<font color=\"blue\">Champ d�j� pr�sent</font><br />";
		}

		///Module OOO
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'active_mod_ooo'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('active_mod_ooo', 'n');");
		}

		// Module ECTS
		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'active_mod_ects'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('active_mod_ects', 'n');");
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesSaisieEctsPP'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesSaisieEctsPP', 'no');");
		}

		$req_test = mysql_query("SELECT VALUE FROM setting WHERE NAME = 'GepiAccesSaisieEctsScolarite'");
		$res_test = mysql_num_rows($req_test);
		if ($res_test == 0) {
			$result_inter .= traite_requete("INSERT INTO setting VALUES ('GepiAccesSaisieEctsScolarite', 'yes');");
		}

		$result .= "&nbsp;->Ajout d'un champ 'saisie_ects' � la table 'j_groupes_classes': ";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM j_groupes_classes LIKE 'saisie_ects'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE j_groupes_classes ADD saisie_ects BOOLEAN NOT NULL DEFAULT 0;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else {
			$result .= "<font color=\"blue\">Champ d�j� pr�sent</font><br />";
		}

		$result .= "&nbsp;->Ajout d'un champ 'valeur_ects' � la table 'j_groupes_classes': ";
		$test1 = mysql_num_rows(mysql_query("SHOW COLUMNS FROM j_groupes_classes LIKE 'valeur_ects'"));
		if ($test1 == 0) {
			$query = mysql_query("ALTER TABLE j_groupes_classes ADD valeur_ects DECIMAL(3,1) NOT NULL;");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		}
		else {
			$result .= "<font color=\"blue\">Champ d�j� pr�sent</font><br />";
		}


        $result .= "&nbsp;->Cr�ation de la table ects_credits<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'ects_credits'"));
		if ($test == 0) {
			$query2 = mysql_query("CREATE TABLE ects_credits
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT,
	id_eleve INTEGER(11)  NOT NULL COMMENT 'Identifiant de l\'eleve',
	num_periode INTEGER(11)  NOT NULL COMMENT 'Identifiant de la periode',
	id_groupe INTEGER(11)  NOT NULL COMMENT 'Identifiant du groupe',
	valeur DECIMAL(3,1) NOT NULL COMMENT 'Nombre de crédits obtenus par l\'eleve',
	mention VARCHAR(255)  NOT NULL COMMENT 'Mention obtenue',
	PRIMARY KEY (id,id_eleve,num_periode,id_groupe),
	INDEX ects_credits_FI_1 (id_eleve),
	CONSTRAINT ects_credits_FK_1
		FOREIGN KEY (id_eleve)
		REFERENCES eleves (id_eleve),
	INDEX ects_credits_FI_2 (id_groupe),
	CONSTRAINT ects_credits_FK_2
		FOREIGN KEY (id_groupe)
		REFERENCES groupes (id)
)");
			if ($query2) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
		}

        $result .= "&nbsp;->Cr�ation de la table archivage_ects<br />";
		$test = mysql_num_rows(mysql_query("SHOW TABLES LIKE 'archivage_ects'"));
		if ($test == 0) {
			$query2 = mysql_query("CREATE TABLE archivage_ects
                                    (
                                        id INTEGER(11)  NOT NULL AUTO_INCREMENT,
                                        annee VARCHAR(255)  NOT NULL COMMENT 'Annee scolaire',
                                        ine VARCHAR(255)  NOT NULL COMMENT 'Identifiant de l\'eleve',
                                        classe VARCHAR(255)  NOT NULL COMMENT 'Classe de l\'eleve',
                                        num_periode INTEGER(11)  NOT NULL COMMENT 'Identifiant de la periode',
                                        nom_periode VARCHAR(255)  NOT NULL COMMENT 'Nom complet de la periode',
                                        special VARCHAR(255)  NOT NULL COMMENT 'Cle utilisee pour isoler certaines lignes (par exemple un credit ECTS pour une periode et non une matiere)',
                                        matiere VARCHAR(255) COMMENT 'Nom de l\'enseignement',
                                        profs VARCHAR(255) COMMENT 'Liste des profs de l\'enseignement',
                                        valeur DECIMAL  NOT NULL COMMENT 'Nombre de crédits obtenus par l\'eleve',
                                        mention VARCHAR(255)  NOT NULL COMMENT 'Mention obtenue',
                                        PRIMARY KEY (id,ine,num_periode,special),
                                        INDEX archivage_ects_FI_1 (ine),
                                        CONSTRAINT archivage_ects_FK_1
                                            FOREIGN KEY (ine)
                                            REFERENCES eleves (no_gep)
                                    )");
			if ($query2) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
		}


		// Ajouts d'index
		$result .= "&nbsp;->Ajout de l'index 'annee' � la table archivage_disciplines<br />";
		$req_test = mysql_query("SHOW INDEX FROM archivage_disciplines WHERE Key_name = 'annee'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `archivage_disciplines` ADD INDEX annee ( `annee` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}

		// Ajouts d'index
		$result .= "&nbsp;->Ajout de l'index 'INE' � la table archivage_disciplines<br />";
		$req_test = mysql_query("SHOW INDEX FROM archivage_disciplines WHERE Key_name = 'INE'");
		$req_res = mysql_num_rows($req_test);
		if ($req_res == 0) {
			$query = mysql_query("ALTER TABLE `archivage_disciplines` ADD INDEX INE ( `INE` )");
			if ($query) {
				$result .= "<font color=\"green\">Ok !</font><br />";
			} else {
				$result .= "<font color=\"red\">Erreur</font><br />";
			}
		} else {
			$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
		}



		//------------------------------------------------------------------------
		// Fin du bloc de mise � jour 1.5.2. Les mises � jour jusqu'� la diffusion
		// de la 1.5.2 stable doivent se situer au-dessus de cette ligne !
		//------------------------------------------------------------------------
	}



	// Mise � jour du num�ro de version
	saveSetting("version", $gepiVersion);
	saveSetting("versionRc", $gepiRcVersion);
	saveSetting("versionBeta", $gepiBetaVersion);
	saveSetting("pb_maj", $pb_maj);
}


// Load settings
if (!loadSettings()) {
	die("Erreur chargement settings");
}

// Num�ro de version effective
$version_old = getSettingValue("version");
// Num�ro de version RC effective
$versionRc_old = getSettingValue("versionRc");
// Num�ro de version beta effective
$versionBeta_old = getSettingValue("versionBeta");

$rc_old = '';
if ($versionRc_old != '') {
	$rc_old = "-RC" . $versionRc_old;
}
$rc = '';
if ($gepiRcVersion != '') {
	$rc = "-RC" . $gepiRcVersion;
}

$beta_old = '';
if ($versionBeta_old != '') {
	$beta_old = "-beta" . $versionBeta_old;
}
$beta = '';
if ($gepiBetaVersion != '') {
	$beta = "-beta" . $gepiBetaVersion;
}

// Pb de mise � jour lors de la derni�re mise � jour
$pb_maj_bd = getSettingValue("pb_maj");

if (isset ($mess)) {
	echo "<center><p class=grand><font color=red>" . $mess . "</font></p></center>";
}
echo "<center><p class=grand>Mise � jour de la base de donn�es MySql de GEPI</p></center>";

echo "<hr /><h3>Num�ro de version actuel de la base MySql : GEPI " . $version_old . $rc_old . $beta_old . "</h3>";
echo "<hr />";
// Mise � jour de la base de donn�e

if ($pb_maj_bd != 'yes') {
	if (test_maj()) {
		echo "<h3>Mise � jour de la base de donn�es vers la version GEPI " . $gepiVersion . $rc . $beta . "</h3>";
		if (isset ($_SESSION['statut'])) {
			echo "<p>Il est vivement conseill� de faire une sauvegarde de la base MySql avant de proc�der � la mise � jour</p>";
			echo "<center><form enctype=\"multipart/form-data\" action=\"../gestion/accueil_sauve.php\" method=post name=formulaire>";
			if (getSettingValue("mode_sauvegarde") == "mysqldump") {
				echo "<input type='hidden' name='action' value='system_dump' />";
			} else {
				echo "<input type='hidden' name='action' value='dump' />";
			}
			echo "<input type=\"submit\" value=\"Lancer une sauvegarde de la base de donn�es\" /></form></center>";
		}
		echo "<p>Remarque : la proc�dure de mise � jour vers la version <b>GEPI " . $gepiVersion . $rc . $beta . "</b> est utilisable � partir d'une version GEPI 1.2 ou plus r�cente.</p>";
		echo "<form action=\"maj.php\" method=\"post\">";
		echo "<p><font color=red><b>ATTENTION : Votre base de donn�es ne semble pas �tre � jour.";
		if ($version_old != '')
		echo " Num�ro de version de la base de donn�es : GEPI " . $version_old . $rc_old . $beta_old;
		echo "</b></font><br />";
		echo "Cliquez sur le bouton suivant pour effectuer la mise � jour vers la version <b>GEPI " . $gepiVersion . $rc . $beta . "</b></p>";
		echo "<center><input type=submit value='Mettre � jour' /></center>";
		echo "<input type=hidden name='maj' value='yes' />";
		echo "<input type=hidden name='valid' value='$valid' />";
		echo "</form>";
	} else {
		echo "<h3>Mise � jour de la base de donn�es</h3>";
		echo "<p><b>Votre base de donn�es est � jour. Vous n'avez pas de mise � jour � effectuer.</b></p>";
		echo "<center><p class='grand'><b><a href='../gestion/index.php'>Retour</a></b></p></center>";
		echo "<form action=\"maj.php\" method=\"post\">";
		//echo "<p><b>N�anmoins, vous pouvez forcer la mise � jour. Cette proc�dure, bien que sans risque, n'est utile que dans certains cas pr�cis.</b></font><br />";
		echo "<p><b>N�anmoins, vous pouvez forcer la mise � jour. Cette proc�dure, bien que sans risque, n'est utile que dans certains cas pr�cis.</b><br />";
		echo "Cliquez sur le bouton suivant pour effectuer la mise � jour forc�e vers la version <b>GEPI " . $gepiVersion . $rc . $beta . "</b></p>";
		echo "<center><input type=submit value='Forcer la mise � jour' /></center>";
		echo "<input type=hidden name='maj' value='yes' />";
		echo "<input type=hidden name='force_maj' value='yes' />";
		echo "<input type=hidden name='valid' value='$valid' />";
		echo "</form>";
	}
} else {
	echo "<h3>Mise � jour de la base de donn�es</h3>";
	echo "<p><b><font color = 'red'>Une ou plusieurs erreurs ont �t� rencontr�es lors de la derni�re mise � jour de la base de donn�es
.</font></b></p>";
	echo "<form action=\"maj.php\" method=\"post\">";
	echo "<p><b>Si vous pensez avoir r�gl� les probl�mes entra�nant ces erreurs, vous pouvez tenter une nouvelle mise � jour</b>";
	echo " en cliquant sur le bouton suivant pour effectuer la mise � jour vers la version <b>GEPI " . $gepiVersion . $rc . $beta . "</b>.</p>";
	echo "<center><input type=submit value='Tenter une nouvelle mise � jour' /></center>";
	echo "<input type=hidden name='maj' value='yes' />";
	echo "<input type=hidden name='force_maj' value='yes' />";
	echo "<input type=hidden name='valid' value='$valid' />";
	echo "</form>";
}
echo "<hr />";
if (isset ($result)) {
	echo "<center><table width=\"80%\" border=\"1\" cellpadding=\"5\" cellspacing=\"1\" summary='R�sultat de mise � jour'><tr><td><h2 align=\"center\">R�sultat de la mise � jour</h2>";

	if(!getSettingValue('conv_new_resp_table')){
		$sql="SELECT 1=1 FROM responsables";
		$test=mysql_query($sql);
		if(mysql_num_rows($test)>0){
			echo "<p style='font-weight:bold; color:red;'>ATTENTION:</p>\n";
			echo "<blockquote>\n";
			echo "<p>Une conversion des donn�es responsables est requise.</p>\n";
			echo "<p>Suivez ce lien: <a href='../responsables/conversion.php'>CONVERTIR</a></p>\n";
			echo "<p>Vous pouvez quand m�me prendre le temps de lire attentivement les informations de mise � jour ci-dessous.</p>\n";
			echo "</blockquote>\n";
		}
	}

	echo $result;
	echo "</td></tr></table></center>";
}
?>
</body></html>
