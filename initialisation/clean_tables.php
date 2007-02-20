<?php

@set_time_limit(0);
/*
 * Last modification  : 25/08/2006
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

extract($_POST, EXTR_OVERWRITE);


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

$liste_tables_del = array(
"classes",
"eleves",
"groupes",
"responsables",
"j_eleves_groupes",
"j_groupes_classes",
"j_groupes_professeurs",
"j_groupes_matieres",
"j_eleves_classes",
"j_professeurs_matieres",
"matieres",
"periodes",
"utilisateurs"
);

//**************** EN-TETE *****************
$titre_page = "Outil d'initialisation de l'ann�e : Nettoyage des tables";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class=bold>|<a href="index.php">Retour accueil initialisation</a>|</p>
<?php
echo "<center><h3 class='gepi'>Sixi�me phase d'initialisation<br />Nettoyage des tables</h3></center>";
if (!isset($is_posted)) {
   echo "<p><b>ATTENTION ...</b> : vous ne devez proc�der � cette op�ration uniquement si toutes les donn�es (�l�ves, classes, professeurs, disciplines, options) ont �t� d�finies !</p>";
   echo "<p>Les donn�es inutiles import�es � partir des fichiers GEP lors des diff�rentes phases d'initialisation seront effac�es !</p>";
   echo "<form enctype='multipart/form-data' action='clean_tables.php' method='post'>";
   echo "<input type=hidden name='is_posted' value='yes' />";
   echo "<p><input type=submit value='Proc�der au nettoyage' />";
   echo "</form>";
} else {
   $j=0;
   $flag=0;
   while (($j < count($liste_tables_del)) and ($flag==0)) {
       if (mysql_result(mysql_query("SELECT count(*) FROM $liste_tables_del[$j]"),0)==0) {
           $flag=1;
       }
       $j++;
   }
   if ($flag != 0){
       echo "<p><b>ATTENTION ...</b><br />";
       echo "L'initialisation des donn�es de l'ann�e n'est pas termin�e, certaines donn�es concernant les �l�ves, les classes, les groupes, les professeurs ou les mati�res sont manquantes. La proc�dure ne peut continuer !</p></body></html>";
       die();
   }
   //Suppression des donn�es inutiles dans la tables utilisateurs
   echo "<h3 class='gepi'>V�rification des donn�es concernant les professeurs</h3>";
   $req = mysql_query("select login from utilisateurs where (statut = 'professeur' and etat='actif')");
   $sup = 'no';
   $nb_prof = mysql_num_rows($req);
   $i = 0;
   while ($i < $nb_prof) {
       $login_prof = mysql_result($req, $i, 'login');
       $test = mysql_query("select id_professeur from j_professeurs_matieres where id_professeur = '$login_prof'");
       if (mysql_num_rows($test)==0) {
           $del = @mysql_query("delete from utilisateurs where login = '$login_prof'");
           echo "Le professeur $login_prof a �t� supprim� de la base.<br />";
           $sup = 'yes';
       } else {
           $test = mysql_query("select login from j_groupes_professeurs where login = '$login_prof'");
           if (mysql_num_rows($test)==0) {
               $del = @mysql_query("delete from utilisateurs where login = '$login_prof'");
               echo "Le professeur $login_prof a �t� supprim� de la base.<br />";
               $sup = 'yes';
            }
       }
       $i++;
   }
   if ($sup == 'no') {
       echo "<p>Aucun professeur n'a �t� supprim� !</p>";
    }
    //Suppression des donn�es inutiles dans la tables des mati�res
   echo "<h3 class='gepi'>V�rification des donn�es concernant les mati�res</h3>";
   $req = mysql_query("select matiere from matieres");
    $sup = 'no';
   $nb_mat = mysql_num_rows($req);
   $i = 0;
   while ($i < $nb_mat) {
       $mat = mysql_result($req, $i, 'matiere');
        $test1 = mysql_query("select id_matiere from j_professeurs_matieres where id_matiere = '$mat'");
        if (mysql_num_rows($test1)==0) {
            $test2 = mysql_query("select id_matiere from j_groupes_matieres where id_matiere = '$mat'");
           if (mysql_num_rows($test2)==0) {
               $del = @mysql_query("delete from matieres where matiere = '$mat'");
               echo "La mati�re $mat a �t� supprim�e de la base.<br />";
               $sup = 'yes';
           }
       }
       $i++;
    }
   if ($sup == 'no') {
       echo "<p>Aucune mati�re n'a �t� supprim�e !</p>";
    }
    //Suppression des donn�es inutiles dans la tables des responsables
   echo "<h3 class='gepi'>V�rification des donn�es concernant les responsables des �l�ves</h3>";
   $req = mysql_query("select ereno, nom1, prenom1 from responsables");
   $sup = 'no';
   $nb_resp = mysql_num_rows($req);
   $i = 0;
    while ($i < $nb_resp) {
        $resp = mysql_result($req, $i, 'ereno');
       $test1 = mysql_query("select ereno from eleves where ereno = '$resp'");
       if (mysql_num_rows($test1)==0) {
           $nom_resp = mysql_result($req, $i, 'nom1');
           $prenom_resp = mysql_result($req, $i, 'prenom1');
           $del = @mysql_query("delete from responsables where ereno = '$resp'");
           echo "Le responsable ".$prenom_resp." ".$nom_resp." a �t� supprim� de la base.<br />";
          $sup = 'yes';
       }
       $i++;
   }
   if ($sup == 'no') {
       echo "<p>Aucun responsable n'a �t� supprim� !</p>";
    }
   echo "<p>Fin de la proc�dure !</p>";
}
?>
</body>
</html>