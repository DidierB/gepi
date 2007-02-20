<?php

/*
 * Last modification  : 14/03/2005
 *
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

if (!function_exists("dbase_open"))  {
    $msg = "ATTENTION : PHP n'est pas configur� pour g�rer les fichiers GEP (dbf). L'extention  d_base n'est pas active. Adressez-vous � l'administrateur du serveur pour corriger le probl�me.";
}

//**************** EN-TETE *****************
$titre_page = "Outil d'initialisation de l'ann�e";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class=bold>|<a href="../gestion/index.php">Retour</a>|</p>

<p>Vous allez effectuer l'initialisation de l'ann�e scolaire qui vient de d�buter.</p>
<ul>
<li>Au cours de la proc�dure, le cas �ch�ant, certaines donn�es de l'ann�e pass�e seront d�finitivement effac�es de la base GEPI (�l�ves, notes, appr�ciations, ...) . Seules seront conserv�es les donn�es suivantes :<br /><br />
- les donn�es relatives aux �tablissements,<br />
- les donn�es relatives aux classes : intitul�s courts, intitul�s longs, nombre de p�riodes et noms des p�riodes,<br />
- les donn�es relatives aux mati�res : identifiants et intitul�s complets,<br />
- les donn�es relatives aux utilisateurs (professeurs, administrateurs, ...). Concernant les professeurs, les mati�res enseign�es par les professeurs sont conserv�es,<br />
- Les donn�es relatives aux diff�rents types d'AID.</li><br />

<li>L'initialisation s'effectue en quatre phases, chacune n�cessitant un fichier GEP particulier :<br />
    <ul>
    <br />
    <li><a href='step1.php'>Proc�der � la premi�re phase</a> d'importation des �l�ves,  de constitution des classes et d'affectation des �l�ves dans les classes : le fichier <b>F_ELE.DBF</b> est requis.</li>
    <br />
    <li><a href='responsables.php'>Proc�der � la deuxi�me phase</a> d'importation des responsables des �l�ves : le fichier <b>F_ERE.DBF</b> est requis.</li>
    <br />
    <li><a href='disciplines.php'>Proc�der � la troisi�me phase</a> d'importation des mati�res : le fichier <b>F_tmt.dbf</b> est requis.</li>
    <br />
    <li><a href='professeurs.php'>Proc�der � la quatri�me phase</a> d'importation des professeurs : le fichier <b>F_wind.dbf</b> est requis.</li>
    <br />
    <li><a href='prof_disc_classe.php'>Proc�der � la cinqui�me phase</a> d'affectation des mati�res � chaque professeur, d'affectation des professeurs dans chaque classe  et de d�finition des options suivies par les �l�ves : les fichiers <b>F_men.dbf</b> et <b>F_gpd.dbf</b> sont requis.</li>
    <br />
    <li><a href='clean_tables.php'>Proc�der � la sixi�me phase</a> de nettoyage des donn�es : les donn�es inutiles import�es � partir des fichiers GEP lors des diff�rentes phases d'initialisation seront effac�es !</li>
    </ul>
    <br />
</li>
<li>Une fois toute la proc�dure d'initialisation des donn�es termin�e, il vous sera possible d'effectuer toutes les modifications n�cessaires au cas par cas par le biais des outils de gestion inclus dans <b>GEPI</b>.</li>
</ul>

</body>
</html>