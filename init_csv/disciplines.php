<?php
@set_time_limit(0);
/*
* Last modification  : 15/09/2006
*
* Copyright 2001, 2006 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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

//**************** EN-TETE *****************
$titre_page = "Outil d'initialisation de l'ann�e : Importation des mati�res";
require_once("../lib/header.inc");
//************** FIN EN-TETE ***************
?>
<p class=bold>|<a href="index.php">Retour accueil initialisation</a>|</p>
<?php

echo "<center><h3 class='gepi'>Troisi�me phase d'initialisation<br />Importation des mati�res</h3></center>";


if (!isset($_POST["action"])) {
	//
	// On s�lectionne le fichier � importer
	//	
	
	echo "<p>Vous allez effectuer la troisi�me �tape : elle consiste � importer le fichier <b>g_disciplines.csv</b> contenant les donn�es relatives aux disciplines.";
	echo "<p>Remarque : cette op�ration n'efface aucune donn�e dans la base. Elle ne fait qu'une mise � jour, le cas �ch�ant, de la liste des mati�res.";
	echo "<p>Les champs suivants doivent �tre pr�sents, dans l'ordre, et <b>s�par�s par un point-virgule</b> : ";
	echo "<ul><li>Nom court de la mati�re (il doit �tre unique)</li>" .
			"<li>Nom long de la mati�re</li>" .
			"</ul>";
	echo "<p>Veuillez pr�ciser le nom complet du fichier <b>g_disciplines.csv</b>.";
	echo "<form enctype='multipart/form-data' action='disciplines.php' method='post'>";
	echo "<input type='hidden' name='action' value='upload_file' />";
	echo "<p><input type=\"file\" size=\"80\" name=\"csv_file\" />";
	echo "<p><input type='submit' value='Valider' />";
	echo "</form>";

} else {
	//
	// Quelque chose a �t� post�
	//
	if ($_POST['action'] == "save_data") {
		//
		// On enregistre les donn�es dans la base.
		// Le fichier a d�j� �t� affich�, et l'utilisateur est s�r de vouloir enregistrer
		//
				
		$go = true;
		$i = 0;
		// Compteur d'erreurs
		$error = 0;
		// Compteur d'enregistrement
		$total = 0;
		while ($go) {
		
			$reg_nom_court = $_POST["ligne".$i."_nom_court"];
			$reg_nom_long = $_POST["ligne".$i."_nom_long"];
			
			// On nettoie et on v�rifie :
			$reg_nom_court = preg_replace("/[^A-Za-z0-9.\-]/","",trim(strtoupper($reg_nom_court)));
			if (strlen($reg_nom_court) > 50) $reg_nom_court = substr($reg_nom_court, 0, 50);
			$reg_nom_long = preg_replace("/[^A-Za-z0-9 .\-��������]/","",trim($reg_nom_long));
			if (strlen($reg_nom_long) > 200) $reg_nom_long = substr($reg_nom_long, 0, 200);

			// Maintenant que tout est propre, on fait un test sur la table pour voir si la mati�re existe d�j� ou pas
			
			$test = mysql_result(mysql_query("SELECT count(matiere) FROM matieres WHERE matiere = '" . $reg_nom_court . "'"), 0);
			
			if ($test == 0) {
				// Test n�gatif : aucune mati�re avec ce nom court... on enregistre !

				$insert = mysql_query("INSERT INTO matieres SET " .
						"matiere = '" . $reg_nom_court . "', " .
						"nom_complet = '" . htmlentities($reg_nom_long) . "'");
						
				if (!$insert) {
					$error++;
					echo mysql_error();
				} else {
					$total++;					
				}
				
			}


			$i++;
			if (!isset($_POST['ligne'.$i.'_nom_court'])) $go = false;	
		}
		
		if ($error > 0) echo "<p><font color=red>Il y a eu " . $error . " erreurs.</font></p>";
		if ($total > 0) echo "<p>" . $total . " mati�res ont �t� enregistr�s.</p>";
		
		echo "<p><a href='index.php'>Revenir � la page pr�c�dente</a></p>";		
		
	
	} else if ($_POST['action'] == "upload_file") {
		//
		// Le fichier vient d'�tre envoy� et doit �tre trait�
		// On va donc afficher le contenu du fichier tel qu'il va �tre enregistr� dans Gepi
		// en proposant des champs de saisie pour modifier les donn�es si on le souhaite
		//

		$csv_file = isset($_FILES["csv_file"]) ? $_FILES["csv_file"] : NULL;

		// On v�rifie le nom du fichier... Ce n'est pas fondamentalement indispensable, mais
		// autant forcer l'utilisateur � �tre rigoureux
		if(strtolower($csv_file['name']) == "g_disciplines.csv") {
			
			// Le nom est ok. On ouvre le fichier
			$fp=fopen($csv_file['tmp_name'],"r");
	
			if(!$fp) {
				// Aie : on n'arrive pas � ouvrir le fichier... Pas bon.
				echo "<p>Impossible d'ouvrir le fichier CSV !</p>";
				echo "<p><a href='disciplines.php'>Cliquer ici </a> pour recommencer !</center></p>";
			} else {
				
				// Fichier ouvert ! On attaque le traitement
				
				// On va stocker toutes les infos dans un tableau
				// Une ligne du CSV pour une entr�e du tableau
				$data_tab = array();
	
				//=========================
				// On lit une ligne pour passer la ligne d'ent�te:
				$ligne = fgets($fp, 4096);
				//=========================
				
					$k = 0;
					while (!feof($fp)) {
						$ligne = fgets($fp, 4096);
						if(trim($ligne)!="") {

							$tabligne=explode(";",$ligne);

							// 0 : Nom court de la mati�re
							// 1 : Nom long de la mati�re
							

							// On nettoie et on v�rifie :
							$tabligne[0] = preg_replace("/[^A-Za-z0-9.\-]/","",trim(strtoupper($tabligne[0])));
							if (strlen($tabligne[0]) > 50) $tabligne[0] = substr($tabligne[0], 0, 50);
							$tabligne[1] = preg_replace("/[^A-Za-z0-9 .\-��������]/","",trim($tabligne[1]));
							if (strlen($tabligne[1]) > 200) $tabligne[1] = substr($tabligne[1], 0, 200);
							
							$data_tab[$k] = array();
							
							

							$data_tab[$k]["nom_court"] = $tabligne[0];
							$data_tab[$k]["nom_long"] = $tabligne[1];

						}
					$k++;
					}

				fclose($fp);
				
				// Fin de l'analyse du fichier.
				// Maintenant on va afficher tout �a.
				
				echo "<form enctype='multipart/form-data' action='disciplines.php' method='post'>";
				echo "<input type='hidden' name='action' value='save_data' />";
				echo "<table>";
				echo "<tr><td>Nom court (unique)</td><td>Nom long</td></tr>";
				
				for ($i=0;$i<$k-1;$i++) {
					echo "<tr>";
					echo "<td>";
					echo $data_tab[$i]["nom_court"];
					echo "<input type='hidden' name='ligne".$i."_nom_court' value='" . $data_tab[$i]["nom_court"] . "'>";
					echo "</td>";
					echo "<td>";
					echo $data_tab[$i]["nom_long"];
					echo "<input type='hidden' name='ligne".$i."_nom_long' value='" . $data_tab[$i]["nom_long"] . "'>";
					echo "</td>";
					echo "</tr>";
				}
				
				echo "</table>";
				
				echo "<input type='submit' value='Enregistrer'>";

				echo "</form>";
			}

		} else if (trim($csv_file['name'])=='') {
	
			echo "<p>Aucun fichier n'a �t� s�lectionn� !<br />";
			echo "<a href='disciplines.php'>Cliquer ici </a> pour recommencer !</center></p>";
	
		} else {
			echo "<p>Le fichier s�lectionn� n'est pas valide !<br />";
			echo "<a href='disciplines.php'>Cliquer ici </a> pour recommencer !</center></p>";
		}
	}
}

?>

</body>
</html>