<?php

/*
 * $Id$
 *
 * Copyright 2001, 2011 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
	header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
	die();

} else if ($resultat_session == '0') {
	header("Location: ../logout.php?auto=1");
	die();
}

if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

//**************** EN-TETE *****************
$titre_page = "Outil d'initialisation de l'ann�e";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class="bold"><a href="../gestion/index.php#init_xml"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>

<p>Vous allez effectuer l'initialisation de l'ann�e scolaire qui vient de d�buter.<br />
<?php

	if((getSettingValue('use_sso')=="lcs")||(getSettingValue('use_sso')=="ldap_scribe")) {
		echo "<p style='color:red;'><b>ATTENTION&nbsp;:</b> Vous utilisez un serveur LCS ou SCRIBE.<br />
		Il existe un mode d'initialisation de l'ann�e propre � <a href='../init_lcs/index.php'>LCS</a> d'une part et � SCRIBE d'autre part (<i><a href='../init_scribe/index.php'>Scribe</a> et <a href='../init_scribe_ng/index.php'>Scribe_ng</a></i>).<br />
		Si vous initialisez l'ann�e avec le mode XML, vous ne pourrez pas utiliser les comptes de votre serveur LCS/SCRIBE par la suite pour acc�der � GEPI.<br />R�fl�chissez-y � deux fois avant de poursuivre.</p>\n";
		echo "<br />\n";
	}

	echo "<p>Avez-vous pens� � effectuer les diff�rentes op�rations de fin d'ann�e et pr�paration de nouvelle ann�e � la page <a href='../gestion/changement_d_annee.php' style='font-weight:bold;'>Changement d'ann�e</a>&nbsp?</p>\n";

?>
</p>
<ul>
<li>
	<p>Au cours de la proc�dure, le cas �ch�ant, certaines donn�es de l'ann�e pass�e seront d�finitivement effac�es de la base GEPI (<em>�l�ves, notes, appr�ciations,...</em>).<br />
	Seules seront conserv�es les donn�es suivantes&nbsp;:<br /></p>
	<ul>
		<li><p>les donn�es relatives aux �tablissements,</p></li>
		<li><p>les donn�es relatives aux classes : intitul�s courts, intitul�s longs, nombre de p�riodes et noms des p�riodes,</p></li>
		<li><p>les donn�es relatives aux mati�res : identifiants et intitul�s complets,</p></li>
		<li><p>les donn�es relatives aux utilisateurs (professeurs, administrateurs, ...). Concernant les professeurs, les mati�res enseign�es par les professeurs sont conserv�es,</p></li>
		<li><p>Les donn�es relatives aux diff�rents types d'AID.</p></li>
	</ul>
</li>
<li>
	<p>Professeurs, mati�res,...&nbsp;: <a href='lecture_xml_sts_emp.php'>G�n�rer les fichiers CSV � partir de l'export XML de STS</a>.</p>
	<p>El�ves&nbsp;: <a href='lecture_xml_sconet.php'>G�n�rer les fichiers CSV � partir des exports XML de Sconet</a>.</p>
</li>
<li>

	<?php
	//==================================
	// RNE de l'�tablissement pour comparer avec le RNE de l'�tablissement de l'ann�e pr�c�dente
	$gepiSchoolRne=getSettingValue("gepiSchoolRne") ? getSettingValue("gepiSchoolRne") : "";
	//==================================
	if($gepiSchoolRne=="") {
		echo "<p><b style='color:red;'>Attention</b>: Le RNE de l'�tablissement n'est pas renseign� dans 'Gestion g�n�rale/<a href='../gestion/param_gen.php' target='_blank'>Configuration g�n�rale</a>'<br />Cela peut perturber l'import de l'�tablissement d'origine des �l�ves.<br />Vous devriez corriger avant de poursuivre.</p>\n";
	}
	?>

	<p>Pour proc�der aux importations:</p>
	<ul>
		<li><p><a href='step1.php'>Proc�der � la premi�re phase</a> d'importation des �l�ves,  de constitution des classes et d'affectation des �l�ves dans les classes : le fichier <b>ELEVES.CSV</b> est requis.</p></li>
		<li><p><a href='responsables.php'>Proc�der � la deuxi�me phase</a> d'importation des responsables des �l�ves : les fichiers <b>PERSONNES.CSV</b>, <b>RESPONSABLES.CSV</b> et <b>ADRESSES.CSV</b> sont requis.</p></li>
		<li><p><a href='disciplines_csv.php'>Proc�der � la troisi�me phase</a> d'importation des mati�res : le fichier <b>F_tmt.csv</b> est requis.</p></li>
		<li><p><a href='prof_csv.php?a=a<?php echo add_token_in_url();?>'>Proc�der � la quatri�me phase</a> d'importation des professeurs : le fichier <b>F_wind.csv</b> est requis.</p></li>
		<li><p><a href='prof_disc_classe_csv.php?a=a<?php echo add_token_in_url();?>'>Proc�der � la cinqui�me phase</a> d'affectation des mati�res � chaque professeur, d'affectation des professeurs dans chaque classe  et de d�finition des options suivies par les �l�ves : les fichiers <b>F_men.csv</b> et <b>F_gpd.csv</b> sont requis.</p></li>

		<li><p><a href='init_pp.php'>Proc�der � la sixi�me phase</a>: Initialisation des professeurs principaux.</p></li>

		<li><p><a href='clean_tables.php?a=a<?php echo add_token_in_url();?>'>Proc�der � la septi�me phase</a> de nettoyage des donn�es : les donn�es inutiles import�es � partir des fichiers GEP lors des diff�rentes phases d'initialisation seront effac�es&nbsp;!</p></li>

	</ul>
</li>
<li>
	<p>Une fois toute la proc�dure d'initialisation des donn�es termin�e, il vous sera possible d'effectuer toutes les modifications n�cessaires au cas par cas par le biais des outils de gestion inclus dans <b>GEPI</b>.</p>
</li>
</ul>
<?php require("../lib/footer.inc.php");?>