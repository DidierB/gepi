<?php

/**
 * @version $Id$
 *
 * Copyright 2001, 2008 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Julien Jocal
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
   header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
   die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}

// On initialise les variables
$etape = isset($_POST["etape"]) ? $_POST["etape"] : NULL;
$nbre_ligne = isset($_POST["nbre_ligne"]) ? $_POST["nbre_ligne"] : NULL;
$values = '';
//$ = isset($_POST[""]) ? $_POST[""] : NULL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="fr">
<head>
	<title>Enregistrer les concordances pour l'import de l'EdT</title>
</head>
<body>
<?php
// On indique � quelle �tape on se situe
echo '<p>ETAPE n� '.$etape.'</p>';

// Si cette �tape n'est pas nulle, on fait le travail demand�
if ($etape != NULL) {
	// On prend d'abord le cas des semaines
	if ($etape == 7) {
		// On r�cup�re les donn�es pour les sauvegarder dans la table edt_semaines
		// Mais avant on v�rifie si cette table n'est pas d�j� remplie


	// Puis on prend le cas des cours
	}elseif($etape == 9){
		// Pour les cours, on fait le lien avec les infos d�j� rentr�es dans la table edt_init
		// On explose la valeur
		for($c = 1; $c < $nbre_ligne + 1; $c++){
			$cours[$c] = isset($_POST["cours_".$c]) ? $_POST["cours_".$c] : NULL;
			$elements_cours = explode("|", $cours[$c]);
			// Si l'enregistrement n'est pas bon (soit que Gepi ne retrouve pas l'enseignement / AID soit que
			// la base r�agit mal, on affiche toutes les infos sur la ligne qui n'est pas enregistr�e
			echo '<b>Ligne n� '.$c.'</b>
				  classe : '.$elements_cours[0].
				' type semaine : '.$elements_cours[1].
				' jour : '.$elements_cours[2].
				' heure deb : '.$elements_cours[3].
				' heure fin : '.$elements_cours[4].
				' prof : '.$elements_cours[5].
				' grpe : '.$elements_cours[6].
				' partie : '.$elements_cours[7].
				' mati�re : '.$elements_cours[8].
				' salle : '.$elements_cours[9].
				' Grpe/enti�re : '.$elements_cours[10].'<br />'."\n";

		} // for($c = 0; $c < $nbre_ligne; $c++)  (de l'�tape 9)

	// pour tout ce qui n'est ni les types de semaines, ni des cours, on voit la concordance
	}else{
		// C'est le cas g�n�ral pour enregistrer les concordances entre le fichier txt et Gepi
		// On r�ceptionne les donn�es et on les rentre dans la base
		for($a = 0; $a < $nbre_ligne; $a++){

			$nom_gepi[$a] = isset($_POST["nom_gepi_".$a]) ? $_POST["nom_gepi_".$a] : NULL;
			$numero_texte[$a] = isset($_POST["numero_texte_".$a]) ? $_POST["numero_texte_".$a] : NULL;
			// On pr�pare la requ�te
			if ($nom_gepi[$a] != '') {
				$values .= "('', '".$etape."', '".$numero_texte[$a]."', '".$nom_gepi[$a]."'), ";

			}
		}
		// On envoie toutes les requ�tes d'un coup
		echo $values;
		$envoie = mysql_query("INSERT INTO edt_init (id_init, ident_export, nom_export, nom_gepi)
					VALUE ".$values." ('', ".$etape.", 'fin', 'fin')") OR DIE ('Erreur dans la requ�te $envoie de l\'�tape '.$etape.' : '.mysql_error().'<br />'.$envoie);

		// si l'envoi est une r�ussite alors on pass � l'�tape 2
		if ($envoie) {
			$prochaine_etape = $etape + 1;
			$vers_etape2 = mysql_query("UPDATE edt_init SET nom_export = '".$prochaine_etape."' WHERE ident_export = 'fichierTexte'");
			echo '
			<h3>L\'op�ration a r�ussi</h3>';
			// Certaines �tapes ne donnent lieu � aucun enregistrement
			if ($etape != 4) {
				echo '
				<p>Il y a eu '.$nbre_ligne.' enregistrements dans la base</p>';
			}else{
				// C'est la cas des "PARTIES" qui sont des r�f�rences � des groupes d'�l�ves
				// �tape 4
				echo '
				<p>Il n\'y a eu aucun enregistrement dans la base</p>';
			}
			echo '
			<a href="./edt_init_texte.php">Revenez en arri�re et recommencer la m�me op�ration pour l\'�tape '.$prochaine_etape.'.</a>';
		}

	} // fin du else
} // fin du if ($etape != NULL)


?>
</body>
</html>