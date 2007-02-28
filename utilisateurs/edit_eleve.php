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
$mode = isset($_POST["mode"]) ? $_POST["mode"] : (isset($_GET["mode"]) ? $_GET["mode"] : false);
$action = isset($_POST["action"]) ? $_POST["action"] : (isset($_GET["action"]) ? $_GET["action"] : false);

$msg = '';

// Si on est en traitement par lot, on s�lectionne tout de suite la liste des utilisateurs impliqu�s
$error = false;
if ($mode == "classe") {
	$nb_comptes = 0;
	if ($_POST['classe'] == "all") {
		$quels_eleves = mysql_query("SELECT distinct(jec.login) " .
				"FROM classes c, j_eleves_classes jec WHERE (" .
				"jec.id_classe = c.id)");
		if (!$quels_eleves) $msg .= mysql_error();
	} elseif (is_numeric($_POST['classe'])) {
		$quels_eleves = mysql_query("SELECT distinct(jec.login) " .
				"FROM classes c, j_eleves_classes jec WHERE (" .
				"jec.id_classe = '" . $_POST['classe']."')");
		if (!$quels_eleves) $msg .= mysql_error();
	} else {
		$error = true;
		$msg .= "Vous devez s�lectionner au moins une classe !<br/>";
	}
}

// Trois actions sont possibles depuis cette page : activation, d�sactivation et suppression.
// L'�dition se fait directement sur la page de gestion des responsables

if ($action == "rendre_inactif") {
	// D�sactivation d'utilisateurs actifs
	if ($mode == "individual") {
		// D�sactivation pour un utilisateur unique
		$test = mysql_result(mysql_query("SELECT count(login) FROM utilisateurs WHERE (login = '" . $_GET['eleve_login']."' AND etat = 'actif')"), 0);
		if ($test == "0") {
			$msg .= "Erreur lors de la d�sactivation de l'utilisateur : celui-ci n'existe pas ou bien est d�j� inactif.";
		} else {
			$res = mysql_query("UPDATE utilisateurs SET etat='inactif' WHERE (login = '".$_GET['eleve_login']."')");
			if ($res) {
				$msg .= "L'utilisateur ".$_GET['eleve_login'] . " a �t� d�sactiv�.";
			} else {
				$msg .= "Erreur lors de la d�sactivation de l'utilisateur.";
			}
		}
	} elseif ($mode == "classe" and !$error) {
		// Pour tous les �l�ves qu'on a d�j� s�lectionn�s un peu plus haut, on d�sactive les comptes
		while ($current_eleve = mysql_fetch_object($quels_eleves)) {
			$test = mysql_result(mysql_query("SELECT count(login) FROM utilisateurs WHERE login = '" . $current_eleve->login ."'"), 0);
			if ($test > 0) {
				// L'utilisateur existe bien dans la tables utilisateurs, on d�sactive
				$res = mysql_query("UPDATE utilisateurs SET etat = 'inactif' WHERE login = '" . $current_eleve->login . "'");
				if (!$res) {
					$msg .= "Erreur lors de la d�sactivation du compte ".$current_eleve->login."<br/>";
				} else {
					$nb_comptes++;
				}
			}
		}
		$msg .= "$nb_comptes comptes ont �t� d�sactiv�s.";		
	}
} elseif ($action == "rendre_actif") {
	// Activation d'utilisateurs pr�alablement d�sactiv�s
	if ($mode == "individual") {
		// Activation pour un utilisateur unique
		$test = mysql_result(mysql_query("SELECT count(login) FROM utilisateurs WHERE (login = '" . $_GET['eleve_login']."' AND etat = 'inactif')"), 0);
		if ($test == "0") {
			$msg .= "Erreur lors de la d�sactivation de l'utilisateur : celui-ci n'existe pas ou bien est d�j� actif.";
		} else {
			$res = mysql_query("UPDATE utilisateurs SET etat='actif' WHERE (login = '".$_GET['eleve_login']."')");
			if ($res) {
				$msg .= "L'utilisateur ".$_GET['eleve_login'] . " a �t� activ�.";
			} else {
				$msg .= "Erreur lors de l'activation de l'utilisateur.";
			}
		}
	} elseif ($mode == "classe") {
		// Pour tous les �l�ves qu'on a d�j� s�lectionn�s un peu plus haut, on d�sactive les comptes
		while ($current_eleve = mysql_fetch_object($quels_eleves)) {
			$test = mysql_result(mysql_query("SELECT count(login) FROM utilisateurs WHERE login = '" . $current_eleve->login ."'"), 0);
			if ($test > 0) {
				// L'utilisateur existe bien dans la tables utilisateurs, on d�sactive
				$res = mysql_query("UPDATE utilisateurs SET etat = 'actif' WHERE login = '" . $current_eleve->login . "'");
				if (!$res) {
					$msg .= "Erreur lors de l'activation du compte ".$current_eleve->login."<br/>";
				} else {
					$nb_comptes++;
				}
			}
		}
		$msg .= "$nb_comptes comptes ont �t� activ�s.";		
	}
	
} elseif ($action == "supprimer") {
	// Suppression d'un ou plusieurs utilisateurs
	if ($mode == "individual") {
		// Suppression pour un utilisateur unique
		$test = mysql_result(mysql_query("SELECT count(login) FROM utilisateurs WHERE (login = '" . $_GET['eleve_login']."')"), 0);
		if ($test == "0") {
			$msg .= "Erreur lors de la suppression de l'utilisateur : celui-ci n'existe pas.";
		} else {
			$res = mysql_query("DELETE FROM utilisateurs WHERE (login = '".$_GET['eleve_login']."')");
			if ($res) {
				$msg .= "L'utilisateur ".$_GET['eleve_login'] . " a �t� supprim�.";
			} else {
				$msg .= "Erreur lors de la suppression de l'utilisateur.";
			}
		}
	} elseif ($mode == "classe") {
		// Pour tous les �l�ves qu'on a d�j� s�lectionn�s un peu plus haut, on d�sactive les comptes
		while ($current_eleve = mysql_fetch_object($quels_eleves)) {
			$test = mysql_result(mysql_query("SELECT count(login) FROM utilisateurs WHERE login = '" . $current_eleve->login ."'"), 0);
			if ($test > 0) {
				// L'utilisateur existe bien dans la tables utilisateurs, on d�sactive
				$res = mysql_query("DELETE FROM utilisateurs WHERE login = '" . $current_eleve->login . "'");
				if (!$res) {
					$msg .= "Erreur lors de l'activation du compte ".$current_eleve->login."<br/>";
				} else {
					$nb_comptes++;
				}
			}
		}
		$msg .= "$nb_comptes comptes ont �t� supprim�s.";		
	}
}

//**************** EN-TETE *****************
$titre_page = "Modifier un compte �l�ve";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class=bold><a href="index.php"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a>
<?php
if ((getSettingValue('use_sso') != "cas" and getSettingValue("use_sso") != "lemon" and getSettingValue('use_sso') != "lcs" and getSettingValue("use_sso") != "ldap_scribe") OR $block_sso) {
    echo " | <a href=\"reset_passwords.php?user_status=eleve\" onclick=\"javascript:return confirm('�tes-vous s�r de vouloir effectuer cette op�ration ?\\n Celle-ci est irr�versible, et r�initialisera les mots de passe de tous les utilisateurs ayant le statut \'eleve\' et marqu�s actifs, avec un mot de passe alpha-num�rique g�n�r� al�atoirement.\\n En cliquant sur OK, vous lancerez la proc�dure, qui g�n�rera une page contenant les fiches-bienvenue � imprimer imm�diatement pour distribution aux utilisateurs concern�s.')\">R�initialiser mots de passe</a>";
}
	echo "</p>";
	echo "<p><b>Actions par lot</b> :";
	echo "<form action='edit_eleve.php' method='post'>";
	echo "<input type='hidden' name='mode' value='classe' />";
	echo "<input type='radio' name='action' value='rendre_inactif' /> Rendre inactif";
	echo "<input type='radio' name='action' value='rendre_actif' style='margin-left: 20px;'/> Rendre actif ";
	echo "<input type='radio' name='action' value='supprimer' style='margin-left: 20px;' /> Supprimer<br/>";
	echo "<br/>";
	echo "<select name='classe' size='1'>";
	echo "<option value='none'>S�lectionnez une classe</option>";
	echo "<option value='all'>Toutes les classes</option>";
	$quelles_classes = mysql_query("SELECT id,classe FROM classes ORDER BY classe");
	while ($current_classe = mysql_fetch_object($quelles_classes)) {
		echo "<option value='".$current_classe->id."'>".$current_classe->classe."</option>";
	}
	echo "</select>";
	echo "&nbsp;<input type='submit' name='Valider' value='Valider' />";
	echo "</form>";
?>
<br/><br/>
<table border="1">
<tr>
	<th>Identifiant</th><th>Nom Pr�nom</th><th>Etat</th><th>Actions</th>
</tr>
<?php
$quels_eleves = mysql_query("SELECT * FROM utilisateurs WHERE statut = 'eleve' ORDER BY nom,prenom");

while ($current_eleve = mysql_fetch_object($quels_eleves)) {
	echo "<tr>";
		echo "<td>";
			echo "<a href='../eleves/modify_eleve.php?eleve_login=".$current_eleve->login."'>".$current_eleve->login."</a>";
		echo "</td>";
		echo "<td>";
			echo $current_eleve->nom . " " . $current_eleve->prenom;
		echo "</td>";
		echo "<td>";
			echo $current_eleve->etat;
			echo "<br/>";
			if ($current_eleve->etat == "actif") {
				echo "<a href='edit_eleve.php?action=rendre_inactif&amp;mode=individual&amp;eleve_login=".$current_eleve->login."'>D�sactiver";
			} else {
				echo "<a href='edit_eleve.php?action=rendre_actif&amp;mode=individual&amp;eleve_login=".$current_eleve->login."'>Activer";
			}
			echo "</a>";
		echo "</td>";
		echo "<td>";
		echo "<a href='edit_eleve.php?action=supprimer&amp;mode=individual&amp;eleve_login=".$current_eleve->login."' onclick=\"javascript:return confirm('�tes-vous s�r de vouloir supprimer l\'utilisateur ?')\">Supprimer</a><br/>";
		echo "<a href=\"reset_passwords.php?user_login=".$current_eleve->login."\" onclick=\"javascript:return confirm('�tes-vous s�r de vouloir effectuer cette op�ration ?\\n Celle-ci est irr�versible, et r�initialisera le mot de passe de l\'utilisateur avec un mot de passe alpha-num�rique g�n�r� al�atoirement.\\n En cliquant sur OK, vous lancerez la proc�dure, qui g�n�rera une page contenant la fiche-bienvenue � imprimer imm�diatement pour distribution � l\'utilisateur concern�.')\" target='change'>R�initialiser le mot de passe</a>";
		echo "</td>\n";
	echo "</tr>";
}
?>
</table>
<?php require("../lib/footer.inc.php");?>