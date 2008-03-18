<?php

/**
 * Fichier temporaire uniquement pr�sent dans les versions RC pour teter les configurations serveur
 * et d'autres param�tres pour comprendre certaines erreurs.
 *
 * @version $Id$ 1.5.1RC1
 *
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

// On initialise
$titre_page = "Administration - Param�tres du serveur";
$affiche_connexion = 'yes';
$niveau_arbo = 1;

// Initialisations files
require_once("../lib/initialisations.inc.php");

// D�finition de la classe php
require_once("serveur_infos.class.php");

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
   header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
   die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}

// Instance de la classe infos (voir serveur_infos.class.php)
$test = new infos;

// Analyse des param�tres
if ($test->secureServeur() == 'on') {
	$style_register = ' style="color: red; font-weight: bold;"';
}elseif($test->secureServeur() == 'off'){
	$style_register = '';
}else{
	$style_register = ' style="color: red; font-style: italic;"';
}
if ($test->maxExecution() <= '30') {
	$warning_maxExec = '&nbsp;(Cette valeur peut �tre un peu courte si votre �tablissement est important)';
}else{
	$warning_maxExec = '&nbsp;(Cette valeur devrait suffire dans la grande majorit� des cas)';
}
/*+++++++++++++++++++++ On ins�re l'ent�te de Gepi ++++++++++++++++++++*/
$javascript_specifique = "edt_organisation/script/fonctions_edt";
$style_specifique = "edt_organisation/style_edt";

require_once("../lib/header.inc");
/*++++++++++++++++++++++ fin ent�te ++++++++++++++++++++++++++++++++++++*/
echo '
<p class=bold><a href="../gestion/index.php">
	<img src="../images/icons/back.png" alt="Retour" class="back_link" /> Retour</a>
</p>
';


/* ======= Affichage des param�tres ============= */



echo '
	<h4>Les donn�es de base de votre serveur web</h4>
	<p'.$style_register.'>Le register_globals est � '.$test->secureServeur().'.</p>';

echo '<p>Votre version de php est la '.$test->versionPhp().'.</p>
	<p>Votre version de serveur de base de donn�es MySql est la '.$test->versionMysql().'.</p>
	<p>Votre version du module GD est la '.$test->versionGd().'&nbsp;(indispensable pour toutes les images).</p>
	<br />
	<hr />
	<h4>&nbsp;&nbsp;Liste des modules impl�ment�s avec votre php : </h4>'.$test->listeExtension().'
	<hr />
	<h4>Les r�glages php : </h4>
	- La m�moire maximale allou�e � php est de '.$test->memoryLimit().'.
	<br />
	- La taille maximum d\'une variable envoy�e � Gepi ne doit pas d�passer '.$test->maxSize().'.
	<br />
	- Le temps maximum allou� � php pour traiter une requ�te est de : '.$test->maxExecution().' secondes'.$warning_maxExec.'.
	<br />
	- La taille maximum d\'un fichier envoy� � Gepi est de '.$test->tailleMaxFichier().'.';

echo '<br />';

// inclusion du footer
require_once("../lib/footer.inc.php");
?>