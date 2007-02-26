<?php
/*
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
    die();
};


if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}

// Initialisation des variables
$create_mode = isset($_POST["mode"]) ? $_POST["mode"] : NULL;

if ($create_mode == "classe" OR $create_mode == "individual") {
	// On a une demande de cr�ation, on continue

	// On veut alimenter la variable $quels_eleves avec un r�sultat mysql qui contient
	// la liste des �l�ves pour lesquels on veut cr�er un compte
	$error = false;
	$msg = "";
	if ($create_mode == "individual") {
		// $_POST['pers_id'] est filtr� automatiquement contre les injections SQL, on l'utilise directement
		$test = mysql_query("SELECT count(e.login) FROM eleves e WHERE (e.login = '" . $_POST['eleve_login'] ."')");
		if (mysql_result($test, 0) == "0") {
			$error = true;
			$msg .= "Erreur lors de la cr�ation de l'utilisateur : aucun �l�ve avec ce login n'a �t� trouv� !<br/>";
		} else {
			$quels_eleves = mysql_query("SELECT e.* FROM eleves e WHERE (" .
				"e.login = '" . $_POST['eleve_login'] ."')");
		}
	} else {
		// On est en mode 'classe'
		if ($_POST['classe'] == "all") {
			$quels_eleves = mysql_query("SELECT distinct(e.login), e.nom, e.prenom, e.sexe, e.email " .
					"FROM classes c, j_eleves_classes jec, eleves e WHERE (" .
					"e.login = jec.login AND " .
					"jec.id_classe = c.id)");
			if (!$quels_eleves) $msg .= mysql_error();
		} elseif (is_numeric($_POST['classe'])) {
			$quels_eleves = mysql_query("SELECT distinct(e.login), e.nom, e.prenom, e.sexe, e.email " .
					"FROM classes c, j_eleves_classes jec, eleves e WHERE (" .
					"e.login = jec.login AND " .
					"jec.id_classe = '" . $_POST['classe']."')");
			if (!$quels_eleves) $msg .= mysql_error();
		} else {
			$error = true;
			$msg .= "Vous devez s�lectionner au moins une classe !<br/>";
		}
	}

	if (!$error) {
		$nb_comptes = 0;
		while ($current_eleve = mysql_fetch_object($quels_eleves)) {
			// Cr�ation du compte utilisateur pour l'�l�ve consid�r�
			$reg = true;
			$civilite = '';
			if ($current_eleve->sexe == "M") {
				$civilite = "M.";
			} elseif ($current_eleve->sexe == "F") {
				$civilite = "Mlle";
			}
			$reg = mysql_query("INSERT INTO utilisateurs SET " .
					"login = '" . $current_eleve->login . "', " .
					"nom = '" . addslashes($current_eleve->nom) . "', " .
					"prenom = '". addslashes($current_eleve->prenom) ."', " .
					"password = '', " .
					"civilite = '" . $civilite."', " .
					"email = '" . $current_eleve->email . "', " .
					"statut = 'eleve', " .
					"etat = 'actif', " .
					"change_mdp = 'n'");

			if (!$reg) {
				$msg .= "Erreur lors de la cr�ation du compte ".$current_eleve->login."<br/>";
			} else {			
				$nb_comptes++;
			}
		}
		if ($nb_comptes == 1) {
			$msg .= "Un compte a �t� cr�� avec succ�s.<br/>";
		} elseif ($nb_comptes > 1) {
			$msg .= $nb_comptes." comptes ont �t� cr��s avec succ�s.<br/>";
		}
		if ($nb_comptes > 0) {
			if ($create_mode == "individual") {
				// Mode de cr�ation de compte individuel. On fait un lien sp�cifique pour la fiche de bienvenue
				$msg .= "<br/><a target='change' href='reset_passwords.php?user_login=".$_POST['eleve_login']."'>";
			} else {
				// On est ici en mode de cr�ation par classe
				// Si on op�re sur toutes les classes, on ne sp�cifie aucune classe
				if ($_POST['classe'] == "all") {
					$msg .= "<br/><a target='change' href='reset_passwords.php?user_status=eleve'>";
				} elseif (is_numeric($_POST['classe'])) {
					$msg .= "<br/><a target='change' href='reset_passwords.php?user_status=eleve&amp;user_classe=".$_POST['classe']."'>";
				}
			}
			$msg .= "Imprimer la ou les fiche(s) de bienvenue</a><br/>Vous devez effectuer cette op�ration maintenant !";
		}
	}
}

//**************** EN-TETE *****************
$titre_page = "Gestion des utilisateurs | Modifier un utilisateur";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class=bold>
|<a href="index.php">Retour</a>|

<?php
$quels_eleves = mysql_query("SELECT e.* FROM eleves e LEFT JOIN utilisateurs u ON e.login=u.login WHERE (" .
		"u.login IS NULL) " .
		"ORDER BY e.nom,e.prenom");
$nb = mysql_num_rows($quels_eleves);
if($nb==0){
	echo "<p>Tous les �l�ves ont un compte utilisateur.</p>\n";
}
else{
	echo "<p>Les $nb �l�ves ci-dessous n'ont pas encore de compte utilisateur.</p>";

	if ((getSettingValue('use_sso') == "cas" OR getSettingValue("use_sso") == "lemon"  OR getSettingValue("use_sso") == "lcs" OR getSettingValue("use_sso") == "ldap_scribe")) {
		echo "<p><b>Note :</b> Vous utilisez une authentification externe � Gepi (SSO). Pour le moment, les logins �l�ves de Gepi sont g�n�r�s selon une m�thode interne � Gepi. Il est donc peu probable que le SSO fonctionne pour les comptes �l�ves.</p>";
	}

	echo "<p><b>Cr�er des comptes par lot</b> : s�lectionnez une classe ou bien l'ensemble des classes puis cliquez sur 'valider'.";
	echo "<form action='create_eleve.php' method='post'>";
	echo "<input type='hidden' name='mode' value='classe' />";
	echo "<select name='classe' size='1'>";
	echo "<option value='none'>S�lectionnez une classe</option>";
	echo "<option value='all'>Toutes les classes</option>";

	$quelles_classes = mysql_query("SELECT id,classe FROM classes ORDER BY classe");
	while ($current_classe = mysql_fetch_object($quelles_classes)) {
		echo "<option value='".$current_classe->id."'>".$current_classe->classe."</option>";
	}
	echo "</select>";
	echo "<input type='submit' name='Valider' value='Valider' />";
	echo "</form>";
	echo "<br/>";
	echo "<p><b>Cr�er des comptes individuellement</b> : cliquez sur le bouton 'Cr�er' d'un �l�ve pour cr�er un compte associ�.</p>";
	echo "<table>";
	while ($current_eleve = mysql_fetch_object($quels_eleves)) {
		echo "<tr>";
			echo "<td>";
			echo "<form action='create_eleve.php' method='post'>";
			echo "<input type='hidden' name='mode' value='individual'/>";
			echo "<input type='hidden' name='eleve_login' value='".$current_eleve->login."'/>";
			echo "<input type='submit' value='Cr�er'/>";
			echo "</form>";
			echo "<td>".$current_eleve->nom." ".$current_eleve->prenom."</td>";
		echo "</tr>";
	}
	echo "</table>";
}
?>
</body>
</html>