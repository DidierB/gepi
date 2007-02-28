<?php
$starttime = microtime();
/*
 * Last modification  : 29/09/2006
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
// Begin standart header
$titre_page = "Accueil GEPI";
$affiche_connexion = 'yes';
$niveau_arbo = 0;

// Initialisations files
require_once("./lib/initialisations.inc.php");

// On teste s'il y a une mise � jour de la base de donn�es � effectuer
if (test_maj()) {
    header("Location: ./utilitaires/maj.php");
}

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
   header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
   die();
} else if ($resultat_session == '0') {
    header("Location: ./logout.php?auto=1");
    die();
}

// S�curit�
if (!checkAccess()) {
    header("Location: ./logout.php?auto=2");
    die();
}

unset ($_SESSION['order_by']);

// End standart header
require_once("./lib/header.inc");

$tab[0] = "administrateur";
$tab[1] = "professeur";
$tab[2] = "cpe";
$tab[3] = "scolarite";
$tab[4] = "eleve";
$tab[5] = "secours";
$tab[6] = "responsable";

function acces($id,$statut) {
    $tab_id = explode("?",$id);
    $query_droits = @mysql_query("SELECT * FROM droits WHERE id='$tab_id[0]'");
    $droit = @mysql_result($query_droits, 0, $statut);
    if ($droit == "V") {
        return "1";
    } else {
        return "0";
    }
}


function affiche_ligne($chemin_,$titre_,$expli_,$tab,$statut_) {
    if (acces($chemin_,$statut_)==1)  {
        $temp = substr($chemin_,1);
        echo "<tr>\n";
        echo "<td width=\"30%\" align=\"left\"><a href=$temp>$titre_</a>";
        echo "</td>\n";
        echo "<td align=\"left\">$expli_</td>\n";
        echo "</tr>\n";
    }
}


if ($_SESSION['statut'] == "administrateur") {
    echo "<div>\n";

    // V�rification et/ou changement du r�pertoire de backup
    if (!check_backup_directory()) {
        echo "<font color='red'>Il y a eu un probl�me avec la mise � jour du r�pertoire de sauvegarde. \n";
        echo "Veuillez v�rifier que le r�pertoire /backup de Gepi est accessible en �criture par le serveur (le serveur *uniquement* !)<br/>\n";
    }

    // * affichage du nombre de connect� *
    // compte le nombre d'enregistrement dans la table
    $sql = "select LOGIN from log where END > now()";
    $res = sql_query($sql);
    $nb_connect = sql_count($res);
    echo "Nombre de personnes actuellement connect�es : $nb_connect ";
    echo "(<a href = 'gestion/gestion_connect.php?mode_navig=accueil'>Gestion des connexions</a>)\n";

// christian : demande d'enregistrement
if ($force_ref) {
    ?><div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: #CFD7FF;  padding: 2px; margin-left: 60px; margin-right: 60px; margin-top: 2px; margin-bottom: 2px;  text-align: center; color: #1C1A8F; font-weight: bold;">Votre �tablissement n'est pas r�f�renc� parmi les utilisateurs de Gepi.<br /><a href="javascript:ouvre_popup_reference('<?php echo($gepiPath); ?>/referencement.php?etape=explication')" title="Pourquoi est-ce utile ?">Pourquoi est-ce utile ?</a> / <a href="javascript:ouvre_popup_reference('<?php echo($gepiPath); ?>/referencement.php?etape=1')" title="R�f�rencer votre �tablissement">R�f�rencer votre �tablissement</a>.</div><?php
}
// fin christian demande d'enregistrement

    // Test du mode de connexion (http ou https) :
    // FIXME: Les deux lignes ci-dessous ne sont-elles pas inutiles ?
    $uri = $_SERVER['PHP_SELF'];
    $parsed_uri = parse_url($uri);

    if (!isset($_SERVER['HTTPS']) OR (isset($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) != "on")) {
            echo "<br/><font color='red'>Connexion non s�curis�e ! Vous *devez* acc�der � Gepi en HTTPS (v�rifiez la configuration de votre serveur web)</font>\n";
    }

    if (ini_get("register_globals") == "1") {
            echo "<br/><font color='red'>PHP potentiellement mal configur� (register_globals=on)! Pour pr�venir certaines failles de s�curit�, vous *devez* configurer PHP avec le param�tre register_globals � off.</font>\n";
    }

    echo "</div>\n";
}
echo "<center>\n";

//Affichage des messages
$today=mktime(0,0,0,date("m"),date("d"),date("Y"));
$appel_messages = mysql_query("SELECT id, texte, date_debut, date_fin, auteur, destinataires FROM messages
    WHERE (
    texte != '' and
    date_debut <= '".$today."' and
    date_fin >= '".$today."'
    )
    order by id DESC");
$nb_messages = mysql_num_rows($appel_messages);
$ind = 0;
$texte_messages = '';
$affiche_messages = 'no';
while ($ind < $nb_messages) {
    $destinataires1 = mysql_result($appel_messages, $ind, 'destinataires');
    if (strpos($destinataires1, substr($_SESSION['statut'], 0, 1))) {
        if ($affiche_messages == 'yes') $texte_messages .= "<hr />";
        $affiche_messages = 'yes';
        $content = mysql_result($appel_messages, $ind, 'texte');
        // Mise en forme du texte
//        $auteur1 = mysql_result($appel_messages, $ind, 'auteur');
//        $nom_auteur = sql_query1("SELECT nom from utilisateurs where login = '".$auteur1."'");
//        $prenom_auteur = sql_query1("SELECT prenom from utilisateurs where login = '".$auteur1."'");
//        $texte_messages .= "<span class='small'>Message de </span>: ".$prenom_auteur." ".$nom_auteur;
        $texte_messages .= $content;
    }
    $ind++;
}
if ($affiche_messages == 'yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table id='messagerie'>\n";
    echo "<tr><td>".$texte_messages;
    echo "</td></tr></table>\n";
}



$chemin = array(
"/gestion/index.php",
"/accueil_admin.php",
"/accueil_modules.php"
);

$titre = array(
"Gestion g�n�rale",
"Gestion des bases",
"Gestion des modules"
);

$expli = array(
"Pour d�finir, modifier, supprimer des param�tres g�n�raux.",
"Pour g�rer les bases (utilisateurs, mati�res, classes, �l�ves, AIDs).",
"Pour g�rer les modules (cahiers de texte, carnet de notes, ...)."
);

$nb_ligne = count($chemin);
//
// Outils d'administration
//
$affiche = 'no';
for ($i=0;$i<$nb_ligne;$i++) {
    if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
}
if ($affiche=='yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table class='menu'>\n";
    echo "<tr>\n";
    echo "<th colspan='2'>Administration</th>\n";
    echo "</tr>\n";
    for ($i=0;$i<$nb_ligne;$i++) {
        affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
    }
    echo "</table>\n";
}
//
// Outils de gestion
//

// On teste si on l'utilisateur est un prof avec des mati�res. Si oui, on affiche les lignes relatives au cahier de texte et au carnet de notes
$test_prof_matiere = sql_count(sql_query("SELECT login FROM j_groupes_professeurs WHERE login = '".$_SESSION['login']."'"));
// On teste si le l'utilisateur est prof de suivi. Si oui on affiche la ligne relative remplissage de l'avis du conseil de classe
$test_prof_suivi = sql_count(sql_query("SELECT professeur FROM j_eleves_professeurs  WHERE professeur = '".$_SESSION['login']."'"));


$chemin = array();
if ((($test_prof_suivi != "0") and (getSettingValue("GepiProfImprBul")=='yes')) or ($_SESSION['statut']!='professeur'))
{$chemin[] = "/bulletin/verif_bulletins.php"; }
if ($_SESSION['statut']!='professeur')
{$chemin[] = "/bulletin/verrouillage.php"; }
if ((($test_prof_suivi != "0") and ($_SESSION['statut']=='professeur') AND (getSettingValue("GepiProfImprBul")=='yes') AND (getSettingValue("GepiProfImprBulSettings")=='yes')) OR (($_SESSION['statut']=='scolarite') AND (getSettingValue("GepiScolImprBulSettings")=='yes')) OR (($_SESSION['statut']=='administrateur') AND (getSettingValue("GepiAdminImprBulSettings")=='yes')))
{ $chemin[] = "/bulletin/param_bull.php"; }
if ($_SESSION['statut']=='scolarite')
{ $chemin[] = "/responsables/index.php"; }
if ($_SESSION['statut']=='scolarite')
{ $chemin[] = "/eleves/index.php"; }
if ((($test_prof_suivi != "0") and (getSettingValue("GepiProfImprBul")=='yes')) or ($_SESSION['statut']!='professeur'))
{ $chemin[] = "/bulletin/index.php";}

$titre = array();
if ((($test_prof_suivi != "0") and (getSettingValue("GepiProfImprBul")=='yes')) or ($_SESSION['statut']!='professeur'))
{ $titre[] = "Outil de v�rification";}
if ($_SESSION['statut']!='professeur')
{ $titre[] = "Verrouillage/D�verrouillage des p�riodes";}
if ((($test_prof_suivi != "0") and ($_SESSION['statut']=='professeur') AND (getSettingValue("GepiProfImprBul")=='yes') AND (getSettingValue("GepiProfImprBulSettings")=='yes')) OR (($_SESSION['statut']=='scolarite') AND (getSettingValue("GepiScolImprBulSettings")=='yes')) OR (($_SESSION['statut']=='administrateur') AND (getSettingValue("GepiAdminImprBulSettings")=='yes')))
{ $titre[] = "Param�tres d'impression des bulletins";}
if ($_SESSION['statut']=='scolarite')
{ $titre[] = "Gestion des fiches responsables �l�ves";}
if ($_SESSION['statut']=='scolarite')
{ $titre[] = "Gestion des fiches �l�ves";}
if ((($test_prof_suivi != "0") and (getSettingValue("GepiProfImprBul")=='yes')) or ($_SESSION['statut']!='professeur'))
{ $titre[] = "Visualisation et impression des bulletins";}

$expli = array();
if ((($test_prof_suivi != "0") and (getSettingValue("GepiProfImprBul")=='yes')) or ($_SESSION['statut']!='professeur'))
{$expli[] = "Permet de v�rifier si toutes les rubriques des bulletins sont remplies.";}
if ($_SESSION['statut']!='professeur')
{ $expli[] = "Permet de verrouiller ou d�verrouiller une p�riode pour une ou plusieurs classes.";}
if ((($test_prof_suivi != "0") and ($_SESSION['statut']=='professeur') AND (getSettingValue("GepiProfImprBul")=='yes') AND (getSettingValue("GepiProfImprBulSettings")=='yes')) OR (($_SESSION['statut']=='scolarite') AND (getSettingValue("GepiScolImprBulSettings")=='yes')) OR (($_SESSION['statut']=='administrateur') AND (getSettingValue("GepiAdminImprBulSettings")=='yes')))
{ $expli[] = "Permet de modifier les param�tres de mise en page et d'impression des bulletins.";}
if ($_SESSION['statut']=='scolarite')
{ $expli[] = "Cet outil vous permet de modifier/supprimer/ajouter des fiches responsable �l�ves.";}
if ($_SESSION['statut']=='scolarite')
{ $expli[] = "Cet outil vous permet de modifier/supprimer/ajouter des fiches �l�ves.";}
if ((($test_prof_suivi != "0") and (getSettingValue("GepiProfImprBul")=='yes')) or ($_SESSION['statut']!='professeur'))
{ $expli[] = "Cet outil vous permet de visualiser � l'�cran et d'imprimer les bulletins, classe par classe.";}

$nb_ligne = count($chemin);
$affiche = 'no';
for ($i=0;$i<$nb_ligne;$i++) {
    if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
    //else{echo "$chemin[$i] refus�<br />";}
}
if ($affiche=='yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table class='menu'>\n";
    echo "<tr>\n";
    echo "<th colspan='2'>Bulletins scolaires</th>\n";
    echo "</tr>\n";
    for ($i=0;$i<$nb_ligne;$i++) {
        affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
    }
    echo "</table>\n";
}



//
// Saisie
//
$chemin = array();
$chemin[] = "/absences/index.php";
if ((($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) and (getSettingValue("active_cahiers_texte")=='y')) $chemin[] = "/cahier_texte/index.php";
if ((($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) and (getSettingValue("active_carnets_notes")=='y')) $chemin[] = "/cahier_notes/index.php";
if (($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) $chemin[] = "/saisie/index.php";
if ((($test_prof_suivi != "0") and (getSettingValue("GepiRubConseilProf")=='yes')) or (($_SESSION['statut']!='professeur') and (getSettingValue("GepiRubConseilScol")=='yes') ) or ($_SESSION['statut']=='secours')  ) $chemin[] = "/saisie/saisie_avis.php";


$titre = array();
$titre[] = "Gestion des absences";
if ((($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) and (getSettingValue("active_cahiers_texte")=='y')) $titre[] = "Cahier de texte";
if ((($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) and (getSettingValue("active_carnets_notes")=='y')) $titre[] = "Carnet de notes : saisie des notes";
if (($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) $titre[] = "Bulletin : saisie des moyennes et des appr�ciations par mati�re";
if ((($test_prof_suivi != "0") and (getSettingValue("GepiRubConseilProf")=='yes')) or (($_SESSION['statut']!='professeur') and (getSettingValue("GepiRubConseilScol")=='yes') ) or ($_SESSION['statut']=='secours')  ) $titre[] = "Bulletin : saisie des avis du conseil";

$expli = array();
$expli[] = "Cet outil vous permet d'enregistrer les absences des �l�ves. Elles figureront sur le bulletin scolaire.";
if ((($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) and (getSettingValue("active_cahiers_texte")=='y')) $expli[] = "Cet outil vous permet de constituer un cahier de texte pour chacune de vos classes.";
if ((($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) and (getSettingValue("active_carnets_notes")=='y')) $expli[] = "Cet outil vous permet de constituer un carnet de notes pour chaque p�riode et de saisir les notes de toutes vos �valuations.";
if (($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) $expli[] = "Cet outil permet de saisir directement, sans passer par le carnet de notes, les moyennes et les appr�ciations du bulletin";
if ((($test_prof_suivi != "0") and (getSettingValue("GepiRubConseilProf")=='yes')) or (($_SESSION['statut']!='professeur') and (getSettingValue("GepiRubConseilScol")=='yes') ) or ($_SESSION['statut']=='secours')  ) $expli[] = "Cet outil permet la saisie des avis du conseil de classe.";

$call_data = mysql_query("SELECT * FROM aid_config ORDER BY nom");
$nb_aid = mysql_num_rows($call_data);
$i=0;
while ($i < $nb_aid) {
    $indice_aid = @mysql_result($call_data, $i, "indice_aid");
    $call_prof = mysql_query("SELECT * FROM j_aid_utilisateurs WHERE (id_utilisateur = '" . $_SESSION['login'] . "' and indice_aid = '$indice_aid')");
    $nb_result = mysql_num_rows($call_prof);
    if (($nb_result != 0) or ($_SESSION['statut'] == 'secours')) {
        $nom_aid = @mysql_result($call_data, $i, "nom");
        $chemin[] = "/saisie/saisie_aid.php?indice_aid=".$indice_aid;
        $titre[] = "Bulletin : saisie des appr�ciations $nom_aid";
        $expli[] = "Cet outil permet la saisie des appr�ciations des �l�ves pour les $nom_aid.";
    }
    $i++;
}


//==============================
// Pour permettre la saisie de commentaires-type, renseigner la variable $commentaires_types dans /lib/global.inc
// Et r�cup�rer le paquet commentaires_types sur... ADRESSE A DEFINIR:
if((file_exists('saisie/commentaires_types.php'))&&($commentaires_types=='y')){
    //echo "AAAAAAAAAAAAAAA";
    if ((($_SESSION['statut']=='professeur') AND ((getSettingValue("GepiProfImprBul")!='yes') OR ((getSettingValue("GepiProfImprBul")=='yes') AND (getSettingValue("GepiProfImprBulSettings")!='yes')))) OR (($_SESSION['statut']=='scolarite') AND (getSettingValue("GepiScolImprBulSettings")!='yes')) OR (($_SESSION['statut']=='administrateur') AND (getSettingValue("GepiAdminImprBulSettings")!='yes')))
    {
        // Pas d'acc�s au module;
    }
    else{
        //echo "BBBBBBBBBBB";
        $chemin[] = "/saisie/commentaires_types.php";
        $titre[] = "Saisie de commentaires-types";
        $expli[] = "Permet de d�finir des commentaires-types pour l'avis du conseil de classe.";
    }
}

//==============================


$nb_ligne = count($chemin);
$affiche = 'no';
for ($i=0;$i<$nb_ligne;$i++) {
    if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
}
if ($affiche=='yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table class='menu'>\n";
    echo "<tr>\n";
    echo "<th colspan='2'>Saisie</th>\n";
    echo "</tr>\n";
    for ($i=0;$i<$nb_ligne;$i++) {
        affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
    }
    echo "</table>\n";
}

//
// Outils destin�s essentiellement aux parents
// et aux �l�ves
//

// D�finition des conditions
$condition = true;
if ($condition) {
    $chemin[] = "/cahier_texte/consultation.php";
    $titre[] = "Cahier de texte";
    if ($_SESSION['statut'] == "responsable") {
    	$expli[] = "Permet de consulter les compte-rendus de s�ance et les devoirs � faire pour le ou les �l�ve(s) dont vous �tes responsable l�gal.";
    } else {
    	$expli[] = "Permet de consulter les compte-rendus de s�ance et les devoirs � faire pour les enseignements que vous suivez.";
    }
}

$nb_ligne = count($chemin);
$affiche = 'no';
for ($i=0;$i<$nb_ligne;$i++) {
    if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
}
if ($affiche=='yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table class='menu'>\n";
    echo "<tr>\n";
    echo "<th colspan='2'>Consultation</th>\n";
    echo "</tr>\n";
    for ($i=0;$i<$nb_ligne;$i++) {
        affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
    }
    echo "</table>\n";
}


//
// Outils de relev� de note
//
$condition = (
    (getSettingValue("active_carnets_notes")=='y')
    AND
        ((($_SESSION['statut'] == "scolarite") AND (getSettingValue("GepiAccesReleveScol") == "yes"))
        OR
        (($_SESSION['statut'] == "professeur") AND
            ((getSettingValue("GepiAccesReleveProf") == "yes") OR
                ((getSettingValue("GepiAccesReleveProfP") == "yes") AND ($test_prof_suivi != "0"))))
        OR
        (($_SESSION['statut'] == "cpe") AND getSettingValue("GepiAccesReleveCpe") == "yes")));


$chemin = array();
if ($condition) $chemin[] = "/cahier_notes/visu_releve_notes.php";

$titre = array();
if ($condition) $titre[] = "Visualisation et impression des relev�s de notes";

$expli = array();
if ($condition) $expli[] = "Cet outil vous permet de visualiser � l'�cran et d'imprimer les relev�s de notes, �l�ve par �l�ve, classe par classe.";


if ($condition) $chemin[] = "/cahier_notes/index2.php";
if ($condition) $titre[] = "Visualisation des moyennes des carnets de notes";
if ($condition) $expli[] = "Cet outil vous permet de visualiser � l'�cran les moyennes calcul�es d'apr�s le contenu des carnets de notes, ind�pendamment de la saisie des moyennes sur les bulletins.";


$nb_ligne = count($chemin);
$affiche = 'no';
for ($i=0;$i<$nb_ligne;$i++) {
    if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
}
if ($affiche=='yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table class='menu'>\n";
    echo "<tr>\n";
    echo "<th colspan='2'>Relev�s de notes</th>\n";
    echo "</tr>\n";
    for ($i=0;$i<$nb_ligne;$i++) {
        affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
    }
    echo "</table>\n";
}

//
// Outils de gestion des absences : module de Christian Chapel
//

// NOTE : CE MODULE N'EST PAS CONSIDERE COMME STABLE POUR LE MOMENT
// Il est donc d�sactiv� par une variable dans le fichier global.inc

if ($force_abs) {
//On v�rifie si le module est activ�
    if (getSettingValue("active_module_absence")=='y') {
    //
    // Gestion Absences, dispenses, retards
    //
        $chemin = array();
        $chemin[] = "/mod_absences/gestion/gestion_absences.php";

        $titre = array();
        $titre[] = "Gestion Absences, dispenses, retards et infirmeries";

        $expli = array();
        $expli[] = "Cet outil vous permet de g�rer les absences, dispenses, retards et autres  bobos � l'infirmerie des �l�ves.";

        $nb_ligne = count($chemin);
        $affiche = 'no';
        for ($i=0;$i<$nb_ligne;$i++) {
            if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
        }
        if ($affiche=='yes') {
              //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
   			  echo "<table class='menu'>\n";
              echo "<tr>\n";
              echo "<th colspan='2'>Gestion des retards et absences</th>\n";
              echo "</tr>\n";
              for ($i=0;$i<$nb_ligne;$i++) {
                affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
            }
            echo "</table>\n";
        }
    }

    //
    // Outils de gestion des absences par les professeurs : module de Christian Chapel
    //

    //On v�rifie si le module est activ�
    if (getSettingValue("active_module_absence_professeur")=='y') {
    //
    // Gestion des ajout d'Absences par les professeurs
    //
        $chemin = array();
        $chemin[] = "/mod_absences/professeurs/prof_ajout_abs.php";

        $titre = array();
        $titre[] = "Gestion des Absences par le professeur";

        $expli = array();
        $expli[] = "Cet outil vous permet de g�rer les absences durant vos cours.";

        $nb_ligne = count($chemin);
        $affiche = 'no';
        for ($i=0;$i<$nb_ligne;$i++) {
            if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
        }
        if ($affiche=='yes') {
              //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    		  echo "<table class='menu'>\n";
              echo "<tr>\n";
              echo "<th colspan='2'>Gestion des retards et absences</th>\n";
              echo "</tr>\n";
              for ($i=0;$i<$nb_ligne;$i++) {
                affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
            }
            echo "</table>\n";
        }
    }

    //
    // Outils de gestion des trombinoscopes : module de Christian Chapel
    //
}

//On v�rifie si le module est activ�
if (getSettingValue("active_module_trombinoscopes")=='y') {
//
// Visualisation des trombinoscopes
//
    $chemin = array();
    $chemin[] = "/mod_trombinoscopes/trombinoscopes.php";

    $titre = array();
    $titre[] = "Trombinoscopes";

    $expli = array();
    $expli[] = "Cet outil vous permet de visualiser les trombinoscopes des classes.";

    $nb_ligne = count($chemin);
    $affiche = 'no';
    for ($i=0;$i<$nb_ligne;$i++) {
        if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
    }
    if ($affiche=='yes') {
          //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    	  echo "<table class='menu'>\n";
          echo "<tr>\n";
          echo "<th colspan='2'>Trombinoscope</th>\n";
          echo "</tr>\n";
          for ($i=0;$i<$nb_ligne;$i++) {
            affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
        }
        echo "</table>\n";
    }
}

// Acc�s aux modules propres au LPI
if (file_exists("./lpi/accueil.php")) require("./lpi/accueil.php");

//
// Visualisation / Impression

$chemin = array();
//===========================
// AJOUT:boireaus
$chemin[] = "/groupes/visu_profs_class.php";
$chemin[] = "/impression/impression_serie.php";
if(($_SESSION['statut']=='scolarite')||($_SESSION['statut']=='professeur')||($_SESSION['statut']=='cpe')){
	$chemin[] = "/groupes/mes_listes.php";
}
//===========================
$chemin[] = "/visualisation/index.php";
if (($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur')) $chemin[] = "/prepa_conseil/index1.php";
$chemin[] = "/prepa_conseil/index2.php";
$chemin[] = "/prepa_conseil/index3.php";

$titre = array();
//===========================
// AJOUT:boireaus
$titre[] = "Visualisation des �quipes p�dagogiques";
$titre[] = "Impression PDF de listes";

if(($_SESSION['statut']=='scolarite')||($_SESSION['statut']=='professeur')||($_SESSION['statut']=='cpe')){
	$titre[] = "Exporter mes listes d'�l�ves";
}
//===========================
$titre[] = "Outils graphiques de visualisation";
if (($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur'))
    if ($_SESSION['statut']!='scolarite')
        $titre[] =  "Visualiser mes moyennes et appr�ciations des bulletins ";
    else
        $titre[] =  "Visualiser les moyennes et appr�ciations des bulletins ";
$titre[] = "Visualiser toutes les moyennes d'une classe";
$titre[] = "Visualiser les bulletins simplifi�s";

$expli = array();
//===========================
// AJOUT:boireaus
$expli[] = "Ceci vous permet de conna�tre tous les enseignants des classes dans lesquelles vous intervenez, ainsi que les compositions des groupes concern�s.";
$expli[] = "Ceci vous permet d'imprimer en PDF des listes d'�l�ves � l'unit� ou en s�rie. L'apparence des listes est param�trable.";
if(($_SESSION['statut']=='scolarite')||($_SESSION['statut']=='professeur')||($_SESSION['statut']=='cpe')){
	$expli[] = "Ce menu permet de t�l�charger ses listes d'�l�ves au format CSV avec les champs CLASSE;LOGIN;NOM;PRENOM;SEXE;DATE_NAISS.";
}

//===========================
$expli[] = "Visualisation graphique des r�sultats des �l�ves ou des classes, en croisant les donn�es de multiples mani�res.";
if (($test_prof_matiere != "0") or ($_SESSION['statut']!='professeur'))
    if ($_SESSION['statut']!='scolarite')
        $expli[] = "Tableau r�capitulatif de vos moyennes et/ou appr�ciations figurant dans les bulletins avec affichage de statistiques utiles pour le remplissage des livrets scolaires.";
    else
        $expli[] = "Tableau r�capitulatif des moyennes et/ou appr�ciations figurant dans les bulletins avec affichage de statistiques utiles pour le remplissage des livrets scolaires.";
$expli[] = "Tableau r�capitulatif des moyennes d'une classe.";
$expli[] = "Bulletins simplifi�s d'une classe.";


$call_data = mysql_query("SELECT * FROM aid_config ORDER BY nom");
$nb_aid = mysql_num_rows($call_data);
$i=0;
while ($i < $nb_aid) {
    $indice_aid = @mysql_result($call_data, $i, "indice_aid");
    $call_prof = mysql_query("SELECT * FROM j_aid_utilisateurs WHERE (id_utilisateur = '" . $_SESSION['login'] . "' and indice_aid = '$indice_aid')");
    $nb_result = mysql_num_rows($call_prof);
    if ($nb_result != 0) {
        $nom_aid = @mysql_result($call_data, $i, "nom");
        $chemin[] = "/prepa_conseil/visu_aid.php?indice_aid=".$indice_aid;
        $titre[] = "Visualiser des appr�ciations $nom_aid";
        $expli[] = "Cet outil permet la visualisation et l'impression des appr�ciations des �l�ves pour les $nom_aid.";
    }
    $i++;
}



$nb_ligne = count($chemin);
$affiche = 'no';
for ($i=0;$i<$nb_ligne;$i++) {
    if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
}
if ($affiche=='yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table class='menu'>\n";
    echo "<tr>\n";
    echo "<th colspan='2'>Visualisation - Impression</th>\n";
    echo "</tr>\n";
    for ($i=0;$i<$nb_ligne;$i++) {
        affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
    }
    echo "</table>\n";
}

// Gestion des messages

$chemin = array();
$chemin[] = "/messagerie/index.php";

$titre = array();
$titre[] = "Messagerie interne";

$expli = array();
$expli[] = "Cet outil permet la gestion des messages � afficher sur la page d'accueil des utilisateurs.";

$nb_ligne = count($chemin);
$affiche = 'no';
for ($i=0;$i<$nb_ligne;$i++) {
    if (acces($chemin[$i],$_SESSION['statut'])==1)  {$affiche = 'yes';}
}
if ($affiche=='yes') {
    //echo "<table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<table class='menu'>\n";
    echo "<tr>\n";
    echo "<th colspan='2'>Messagerie</th>\n";
    echo "</tr>\n";
    for ($i=0;$i<$nb_ligne;$i++) {
        affiche_ligne($chemin[$i],$titre[$i],$expli[$i],$tab,$_SESSION['statut']);
    }
    echo "</table>\n";
}


if ($_SESSION['statut'] == 'administrateur') {
    //echo "<br /><br /><table width=700 border=2 cellspacing=1 bordercolor=#330033 cellpadding=5>";
    echo "<br /><br /><table class='menu'>\n";
    echo "<tr><td align='left'><center>\n";
    echo "<div><b>Cr�er un fichier de sauvegarde/restauration de la base de donn�es ".$dbDb."</b></div>\n";
    echo "<form enctype=\"multipart/form-data\" action=\"gestion/accueil_sauve.php?action=dump\" method=\"post\" name=\"formulaire\">\n";
    echo "<input type=\"submit\" value=\"Lancer une sauvegarde de la base de donn�es\" /></form></center>\n";
    echo "<span class='small'><b>Remarque</b> :</span>
    <ul>
    <li><span class='small'>le r�pertoire \"documents\" contenant les documents joints aux cahiers de texte ne sera pas sauvegard�.</span></li>
    </ul>\n";
    echo "</td></tr></table>\n";
}

require_once("./lib/microtime.php");
?>
</center>
</div>
</body>
</html>