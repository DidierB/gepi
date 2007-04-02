<?php
/*
 * $Id$
 *
 * Copyright 2001-2007 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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

if (isset($_GET['action'])) {
	// Une action a �t� demand�e
	switch ($_GET['action']) {
		case "activer":
			$res = mysql_query("UPDATE utilisateurs SET etat = 'actif' WHERE (login = '".$_GET['user_login']."')");
			break;	
		case "desactiver":
			$res = mysql_query("UPDATE utilisateurs SET etat = 'inactif' WHERE (login = '".$_GET['user_login']."')");
			break;
		case "observer":
			$res = mysql_query("UPDATE utilisateurs SET observation_securite = '1' WHERE (login = '".$_GET['user_login']."')");
			break;
		case "stop_observation":
			$res = mysql_query("UPDATE utilisateurs SET observation_securite = '0' WHERE (login = '".$_GET['user_login']."')");
			break;
		case "reinit_cumul":
			$res = mysql_query("UPDATE utilisateurs SET niveau_alerte = '0' WHERE (login = '".$_GET['user_login']."')");
			break;
		case "archiver":
			$res = mysql_query("UPDATE tentatives_intrusion SET statut = '' WHERE (statut = 'new')");
			break;	
	}
	if (!$res) echo mysql_error();
}

//**************** EN-TETE *****************
$titre_page = "S�curit� Gepi";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
echo "<p class=bold><a href='../accueil.php'><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a> | <a href='security_policy.php'>D�finir la politique de s�curit�</a></p>";

echo "<table class='menu' style='width: 90%;'>\n";
echo "<tr>\n";
echo "<th colspan='5'>Alertes r�centes (<a href='security_panel.php?action=archiver'>archiver</a>)</th>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td style='width: 20%;'>Utilisateur</td>";
echo "<td>Date</td>";
echo "<td>Niv.</td>";
echo "<td>Description</td>";
echo "<td style='width: 20%;'>Actions</td>\n";
echo "</tr>";

$req = mysql_query("SELECT t.*, u.nom, u.prenom, u.statut, u.etat, u.niveau_alerte, u.observation_securite FROM tentatives_intrusion t, utilisateurs u WHERE (t.statut = 'new' AND u.login = t.login) ORDER BY t.date DESC");
if (!$req) echo mysql_error();
while ($row = mysql_fetch_object($req)) {
	echo "<tr>\n";
	echo "<td>";
	if ($row->login != "-") {
		echo $row->login ." - ".$row->adresse_ip."<br/>";
		echo "<b>".$row->prenom . " " . $row->nom."</b>";
		echo "<br/>".$row->statut;
		if ($row->etat == "actif") {
			echo " (compte actif)";
		} else {
			echo " (compte d�sactiv�)";
		}
		echo "<br/>Score cumul� : ".$row->niveau_alerte;
	} else {
		echo "<b>Tentative ext�rieure<br/>(utilisateur non identifi�)</b>";
	}
	echo "</td>";
	echo "<td>".$row->date."</td>";
	echo "<td>".$row->niveau."</td>";
	echo "<td><b>Page : ".$row->fichier."</b><br/>".stripslashes($row->description)."</td>";
	echo "<td>";
	if ($row->login != "-") {
		echo "<p>";
		if ($row->etat == "actif") {
			echo "<a style='padding: 2px;' href='security_panel.php?action=desactiver&amp;user_login=".$row->login."'>D�sactiver le compte</a>";
		} else {
			echo "<a style='padding: 2px;' href='security_panel.php?action=activer&amp;user_login=".$row->login."'>R�activer le compte</a>";
		}
		if ($row->observation_securite == 0) {
			echo "<a style='padding: 2px;' href='security_panel.php?action=observer&amp;user_login=".$row->login."'>Placer en observation</a>";
		} else {
			echo "<a style='padding: 2px;' href='security_panel.php?action=stop_observation&amp;user_login=".$row->login."'>Retirer l'observation</a>";
		}
		echo "<a style='padding: 2px;' href='security_panel.php?action=reinit_cumul&amp;user_login=".$row->login."'>R�initialiser cumul</a>";
		echo "</p>";
	}
	echo "</td>\n";
	echo "</tr>";
}
echo "</table>";


echo "<table class='menu'>\n";
echo "<tr>\n";
echo "<th colspan='3'>Utilisateurs en observation</th>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td style='width: 200px;'>Utilisateur</td>";
echo "<td style='width: 50px;'>Cumul actuel</td>";
echo "<td style='width: auto;'>Actions</td>\n";
echo "</tr>";

$req = mysql_query("SELECT u.login, u.nom, u.prenom, u.statut, u.etat, u.niveau_alerte FROM utilisateurs u WHERE (u.observation_securite = '1') ORDER BY u.niveau_alerte DESC");
if (!$req) echo mysql_error();
while ($row = mysql_fetch_object($req)) {
	echo "<tr>\n";
	echo "<td>";
	echo $row->login ." - ".$row->statut ."<br/>";
	echo "<b>".$row->prenom . " " . $row->nom."</b>";
	echo "<br/>";
	if ($row->etat == "actif") {
		echo "Compte actif";
	} else {
		echo "Compte d�sactiv�";
	}
	echo "</td>";
	echo "<td>".$row->niveau_alerte."</td>";
	echo "<td>";
		echo "<p>";
		if ($row->etat == "actif") {
			echo "<a style='padding: 2px;' href='security_panel.php?action=desactiver&amp;user_login=".$row->login."'>D�sactiver le compte</a>";
		} else {
			echo "<a style='padding: 2px;' href='security_panel.php?action=activer&amp;user_login=".$row->login."'>R�activer le compte</a>";
		}
		echo "<a style='padding: 2px;' href='security_panel.php?action=stop_observation&amp;user_login=".$row->login."'>Retirer l'observation</a>";
		echo "<a style='padding: 2px;' href='security_panel.php?action=reinit_cumul&amp;user_login=".$row->login."'>R�initialiser cumul</a>";
		echo "</p>";
	echo "</td>\n";
	echo "</tr>";
}
echo "</table>";




echo "<table class='menu' style='width: 90%;'>\n";
echo "<tr>\n";
echo "<th colspan='5'>Historique des alertes</th>\n";
echo "</tr>\n";

echo "<tr>\n";
echo "<td style='width: 20%;'>Utilisateur</td>";
echo "<td>Date</td>";
echo "<td>Niv.</td>";
echo "<td>Description</td>";
echo "<td style='width: 20%;'>Actions</td>\n";
echo "</tr>";

$req = mysql_query("SELECT t.*, u.nom, u.prenom, u.statut, u.etat, u.niveau_alerte, u.observation_securite FROM tentatives_intrusion t, utilisateurs u WHERE (t.statut != 'new' AND u.login = t.login) ORDER BY t.date DESC");
if (!$req) echo mysql_error();
while ($row = mysql_fetch_object($req)) {
	echo "<tr>\n";
	echo "<td>";
	if ($row->login != "-") {
		echo $row->login ." - ".$row->adresse_ip."<br/>";
		echo "<b>".$row->prenom . " " . $row->nom."</b>";
		echo "<br/>".$row->statut;
		if ($row->etat == "actif") {
			echo " (compte actif)";
		} else {
			echo " (compte d�sactiv�)";
		}
		echo "<br/>Score cumul� : ".$row->niveau_alerte;
	} else {
		echo "<b>Tentative ext�rieure<br/>(utilisateur non identifi�)</b>";
	}
	echo "</td>";
	echo "<td>".$row->date."</td>";
	echo "<td>".$row->niveau."</td>";
	echo "<td><b>Page : ".$row->fichier."</b><br/>".stripslashes($row->description)."</td>";
	echo "<td>";
	if ($row->login != "-") {
		echo "<p>";
		if ($row->etat == "actif") {
			echo "<a style='padding: 2px;' href='security_panel.php?action=desactiver&amp;user_login=".$row->login."'>D�sactiver le compte</a>";
		} else {
			echo "<a style='padding: 2px;' href='security_panel.php?action=activer&amp;user_login=".$row->login."'>R�activer le compte</a>";
		}
		if ($row->observation_securite == 0) {
			echo "<a style='padding: 2px;' href='security_panel.php?action=observer&amp;user_login=".$row->login."'>Placer en observation</a>";
		} else {
			echo "<a style='padding: 2px;' href='security_panel.php?action=stop_observation&amp;user_login=".$row->login."'>Retirer l'observation</a>";
		}
		echo "<a style='padding: 2px;' href='security_panel.php?action=reinit_cumul&amp;user_login=".$row->login."'>R�initialiser cumul</a>";
		echo "</p>";
	}
	echo "</td>\n";
	echo "</tr>";
}
echo "</table>";


require("../lib/footer.inc.php");
?>