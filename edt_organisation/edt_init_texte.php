<?php

/**
 * edt_init_textes.php est un fichier qui permet d'initialiser l'EdT par les exports de type "Charlemagne".
 * On passe par une table edt_init qui a 4 champs : id_init (auto incr�ment�), identifiant, nom_gepi, nom_export
 *
 * CREATE TABLE `edt_init` (
 * `id_init` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
 * `ident_export` VARCHAR( 100 ) NOT NULL ,
 * `nom_export` VARCHAR( 200 ) NOT NULL ,
 * `nom_gepi` VARCHAR( 200 ) NOT NULL
 * );
 *
 * @version $Id$
 * @copyright 2007
 */

$titre_page = "Emploi du temps - Initialisation EDT";
$affiche_connexion = 'yes';
$niveau_arbo = 1;

// Initialisations files
require_once("../lib/initialisations.inc.php");

// fonctions edt
require_once("./fonctions_edt.php");

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
   header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
   die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}

/*/ S�curit�
if (!checkAccess()) {
    header("Location: ../logout.php?auto=2");
    die();
}
// S�curit� suppl�mentaire par rapport aux param�tres du module EdT / Calendrier
if (param_edt($_SESSION["statut"]) != "yes") {
	Die('Vous devez demander � votre administrateur l\'autorisation de voir cette page.');
}*/
// CSS et js particulier � l'EdT
$javascript_specifique = "edt_organisation/script/fonctions_edt";
$style_specifique = "edt_organisation/style_edt";
//==============PROTOTYPE===============
$utilisation_prototype = "ok";
//============fin PROTOTYPE=============
// On ins�re l'ent�te de Gepi
require_once("../lib/header.inc");

// On ajoute le menu EdT
require_once("./menu.inc.php");

// ======================= traitement du fichier =====================

$action = isset($_POST["action"]) ? $_POST["action"] : NULL;
$txt_file = isset($_FILES["txt_file"]) ? $_FILES["txt_file"] : NULL;
$truncate_cours = isset($_POST["truncate_edt"]) ? $_POST["truncate_edt"] : NULL;
$etape = NULL;

// On va donc afficher le contenu du fichier tel qu'il va �tre enregistr� dans Gepi
// en proposant des champs de saisie pour modifier les donn�es si on le souhaite
if ($action == "upload_file") {


	// On v�rifie le nom du fichier...
	if(strtolower($txt_file['name']) == "emploidutemps.txt") {
		// Le nom est ok. On ouvre le fichier
        $fp = fopen($txt_file['tmp_name'],"r");

		if(!$fp) {
			// Prob sur l'ouverture du fichier
			echo "<p>Impossible d'ouvrir le fichier texte !</p>\n";
			echo "<p style=\"text-align: center;\"><a href=\"./edt_init_texte.php\">Veuillez recommencer</a></p>\n";
        }else {
			// On v�rifie si on demande d'effacer la table en question
			if ($truncate_cours == "oui") {
			$vider_table = mysql_query("TRUNCATE TABLE edt_init");
			} // fin du !fp

			// On peut enfin s'attaquer au travail sur le fichier
			// On teste d'abord pour savoir � quelle �tape on est
			$query = mysql_query("SELECT nom_export FROM edt_init WHERE ident_export = 'fichierTexte'");
			$nbre_rep = mysql_num_rows($query);
			if ($nbre_rep === 0) {
				// C'est qu'on est au tout d�but, au premier passage et donc
				// on cr�e le champ fichierTexte
				$insert = mysql_query("INSERT INTO edt_init SET ident_export = 'fichierTexte', nom_export = '1', nom_gepi = '".date("d-m-Y h:i")."'");
				$etape = 1;
			}else{
				// On r�cup�re d'abord le num�ro de l'�tape actuel
				$etape = mysql_result($query, "nom_export");
				// On incr�mentera de 1 si cette nouvelle �tape est valid�e
			}

			$neuf_etapes = array("PROFESSEUR", "CLASSE", "GROUPE", "PARTIE", "MATIERE", "ETABLISSEMENT", "SEMAINE", "CONGES", "COURS");
			$autorise = "stop";
			// On ouvre alors le fichier ligne par ligne
			while($tab = fgetcsv($fp, 1024, "	")) {
				if ($tab[0] == $neuf_etapes[$etape - 1]) {
					// On commence l'�tape demand�e et on autorise donc � r�cup�rer les donn�es utiles
					$autorise = "continue";
					echo '<p>Vous �tes dans l\'�tape '.$etape.'</p>';
					echo '<p>Gestion des '.$neuf_etapes[$etape - 1].'.</p>';

				}elseif($tab[0] == $neuf_etapes[$etape]){
					// On arrive � l'�tape suivante et donc on arr�te de r�cup�rer lesdonn�es du fichier
					$autorise = "stop";
					echo '<p>La lecture du fichier pour cette �tape est termin�e, vous devez maintenant faire les concordances.</p>';
					echo "\n<hr /><br />\n";
				}
				// Si $autorise = "continue"; alors on peut utiliser les infos

				if ($autorise == "continue") {
					if ($etape == 1) {
						// On traite les professeurs
						if ($tab[0] == "PROFESSEUR") {
							echo 'Il y a '.$tab[1].' professeurs.<br />'."\n";
						}else{
							// on permet la concordance
							echo 'Mati�re : '.$tab[0].' civilit� :'.$tab[1].' nom : <b>'.$tab[2].'</b>';


							echo '<br />'."\n";
						}
					}
				}

			}

		}
	}else{
		// Si on est l� c'est que le nom du fichier n'est pas bon.
		echo '<p>Ce n\'est pas le bon nom de fichier, vous devriez regarder et modifier le cas �ch�ant.</p>';
		echo "<p style=\"text-align: center;\"><a href=\"./edt_init_texte.php\">Veuillez recommencer</a></p>\n";
	}
} // fin du if ($action == "upload_file")...

// ======================= fin du traitement du fichier ==============
?>

<h4 class="gepi">Initialisation des l'emploi du temps de Gepi en utilisant les exports textes du type "Charlemagne".</h4>

<p>Certains logiciels propri&eacute;taires de traitement des emplois du temps proposent des exportations en format texte.
Celles-ci doivent avoir 9 parties pour pouvoir les utiliser ici :</p>
<ul>
	<li>PROFESSEUR</li>
	<li>CLASSE</li>
	<li>GROUPE</li>
	<li>PARTIE</li>
	<li>MATIERE</li>
	<li>ETABLISSEMENT</li>
	<li>SEMAINE</li>
	<li>CONGES</li>
	<li>COURS</li>
</ul>

<p>Pour chaque partie, vous allez devoir faire le lien avec les informations de Gepi. Vous devrez donc faire passer le fichier texte 9 fois et
la derni&egrave;re sera la plus longue.</p>

	<p>Veuillez pr�ciser le nom complet du fichier <b>emploidutemps.txt</b>.</p>
		<form enctype="multipart/form-data" action="edt_init_texte.php" method="post">
			<input type="hidden" name="action" value="upload_file" />
			<p>
				<label for="truncateEdt">Recommencer en effa&ccedil;ant tous les param&egrave;tres d&eacute;j&agrave; cr&eacute;&eacute;s.</label>
				<input type="checkbox" id="truncateEdt" name="truncate_edt" value="oui" />
			</p>
			<p><input type="file" size="80" name="txt_file" /></p>
			<p><input type="submit" value="Valider" /></p>
		</form>

<?php
require_once("..\lib\footer.inc.php");
?>