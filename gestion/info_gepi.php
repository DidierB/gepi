<?php

/*

 * Last modification  : 14/03/2005

 *

 * Copyright 2001-2004 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun

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

if ($resultat_session == '0') {

   header("Location: ../logout.php?auto=1");

   die();

};

//**************** EN-TETE *****************

require_once("../lib/header.inc");

//**************** FIN EN-TETE *************



?>

<H1 class='gepi'>GEPI - Informations g�n�rales</H1>

<?php

echo "Vous �tes actuellement connect� sur l'application <b>GEPI (".getSettingValue("gepiSchoolName").")</b>.

<br />Par s�curit�, si vous n'envoyez aucune information au serveur (activation d'un lien ou soumission d'un formulaire) pendant plus de <b>".getSettingValue("sessionMaxLength")." minutes</b>, vous serez automatiquement d�connect� de l'application.";

echo "<H2>Administration de l'application GEPI</H2>";

echo "<table cellpadding='5'>";

echo "<tr><td>Nom et pr�nom de l'administrateur : </td><td><b>".getSettingValue("gepiAdminNom")." ".getSettingValue("gepiAdminPrenom")."</b></td></tr>";

echo "<tr><td>Fonction de l'administrateur : </td><td><b>".getSettingValue("gepiAdminFonction")."</b></td></tr>";

echo "<tr><td>Email de l'administrateur : </td><td><b><a href=\"mailto:" . getSettingValue("gepiAdminAdress") . "\">".getSettingValue("gepiAdminAdress")."</a></b></td></tr>";

echo "<tr><td>Nom de l'�tablissement : </td><td><b>".getSettingValue("gepiSchoolName")."</b></td></tr>";

echo "<tr><td Valign='top'>Adresse : </td><td><b>".getSettingValue("gepiSchoolAdress1")."<br />".getSettingValue("gepiSchoolAdress2")."<br />".getSettingValue("gepiSchoolZipCode").", ".getSettingValue("gepiSchoolCity")."</b></td></tr>";

echo "</table>";



echo "<H2>Objectifs de l'application GEPI</H2>";

echo "L'objectif de GEPI est la <b>gestion p�dagogique des �l�ves et de leur scolarit�</b>.

Dans ce but, des donn�es sont collect�es et stock�es dans une base unique de type MySql.";



echo "<H2>Obligations de l'utilisateur</H2>";

echo "Les membres de l'�quipe p�dagogique sont tenus de remplir les rubriques qui leur ont �t� affect�es par l'administrateur

lors du param�trage de l'application.";

echo "<br />Il est possible de modifier le contenu d'une rubrique tant que la p�riode concern�e n'a pas �t� close par l'administrateur.";



echo "<H2>Destinataires des donn�es relatives au bulletin scolaire</H2>";

echo "Concernant le bulletin scolaire, les donn�es suivantes sont r�colt�es aupr�s des membres de l'�quipe p�dagogique :

<ul><li>absences (pour chaque p�riode : nombre de demi-journ�es d'absence, nombre d'absences non justifi�es, nombre de retards, observations)</li>

<li>moyennes et appr�ciations par mati�re,</li>

<li>moyennes et appr�ciations par projet inter-disciplinaire,</li>

<li>avis du conseil de classe.</li>

</ul>

Toutes ces informations sont int�gralement reproduites sur un bulletin � la fin de chaque p�riode (voir ci-dessous).

<br /><br />

Ces donn�es servent � :

<ul>

<li>l'�laboration d'un bulletin � la fin de chaque p�riode, �dit� par le service scolarit� et communiqu� � l��l�ve

et � ses responsables l�gaux : notes obtenues, absences, moyennes, appr�ciations des enseignants, avis du conseil de classe.</li>

<li>l'�laboration d'un document de travail reprenant les informations du bulletin officiel et disponible pour les membres de l'�quipe p�dagogique de la classe concern�e</li>

</ul>";





//On v�rifie si le module cahiers de texte est activ�

if (getSettingValue("active_cahiers_texte")=='y') {

    echo "<H2>Destinataires des donn�es relatives au cahier de texte</H2>";

    echo "Conform�ment aux directives de l'Education Nationale, chaque professeur dispose dans GEPI d'un cahier de texte pour chacune de ses classes qu'il peut tenir � jour

    en �tant connect�.

    <br />

    Le cahier de texte relate le travail r�alis� en classe :

    <ul>

    <li>projet de l'�quipe p�dagogique,</li>

    <li>contenu p�dagogique de chaque s�ance, chronologie, objectif vis�, travail � faire ...</li>

    <li>documents divers,</li>

    <li>�valuations, ...</li>

    </ul>

    Il constitue un outil de communication pour l'�l�ve, les �quipes disciplinaires

    et pluridisciplinaires, l'administration, le chef d'�tablissement, les corps d'inspection et les familles.

    <br /> Les cahiers de texte sont accessibles en ligne.";

    if ((getSettingValue("cahiers_texte_login_pub") != '') and (getSettingValue("cahiers_texte_passwd_pub") != '')) {

       echo " <b>En raison du caract�re personnel du contenu, l'acc�s � l'interface de consultation publique est restreint</b>. Pour acc�der aux cahiers de texte, il est n�cessaire de demander aupr�s de l'administrateur,

       le nom d'utilisateur et le mot de passe valides.";

    } else {

       echo " <b>L'acc�s � l'interface de consultation publique est enti�rement libre et n'est soumise � aucune restriction.</b>";

    }



}

//On v�rifie si le module carnet de notes est activ�

if (getSettingValue("active_carnets_notes")=='y') {

    echo "<H2>Destinataires des donn�es relatives au carnet de notes</H2>";

    echo "Chaque professeur dispose dans GEPI d'un carnet de notes pour chacune de ses classes, qu'il peut tenir � jour

    en �tant connect�.

    <br />

    Le carnet de note permet la saisie des notes et/ou des commentaires de tout type d'�valuation (formatives, sommatives, oral, TP, TD, ...).

    <br /><b>Le professeur s'engage � ne faire figurer dans le carnet de notes que des notes et commentaires port�s � la connaissance de l'�l�ve (note et commentaire port�s sur la copie, ...).</b>

    Ces donn�es stock�es dans GEPI n'ont pas d'autre destinataire que le professeur lui-m�me et le ou les professeurs principaux de la classe.

    <br />Les notes peuvent servir � l'�laboration d'une moyenne qui figurera dans le bulletin officiel � la fin de chaque p�riode.";

}

//On v�rifie si le module observatoire est activ�

if (getSettingValue("active_observatoire")=='y') {

    echo "<H2>Destinataires des donn�es relatives � l'observatoire</H2>";

    echo "Chaque professeur dispose dans GEPI d'un observatoire pour chacune de ses classes, qu'il peut tenir � jour

    en �tant connect�.

    <br />

    Dans l'observatoire, le professeur a la possibilit� d'attribuer � chacun de ses �l�ves un code pour chaque p�riode.

    Ces codes et leur signification sont param�trables par les administrateurs de l'observatoire d�sign�s par l'administrateur g�n�ral de GEPI.

    <br />.

    Le professeur dispose �galement de la possibilit� de saisir un commentaire pour chacun de ses �l�ves

    dans le respect de la loi et dans le cadre strict de l'Education Nationale.

    <br /><br />L'observatoire et les donn�es qui y figurent sont accessibles � l'ensemble de l'�quipe p�dagogique de l'�tablissement.

    <br /><br />Dans le respect de la loi informatique et libert� 78-17 du 6 janvier 1978, les �l�ves sont avertis de l'existence de ces donn�es les concernant et

    disposent d'une droit d'acc�s";

}



?>





</body>

</html>