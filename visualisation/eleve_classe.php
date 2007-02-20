<?php
/*
 * Last modification  : 04/04/2005
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

$datay1 = array();
$datay2 = array();
$etiquette = array();
$graph_title = "";
$v_legend1 = "";
$v_legend2 = "";

//**************** EN-TETE *****************
$titre_page = "Outil de visualisation | El�ve vis � vis de la classe";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

$id_classe = isset($_POST['id_classe']) ? $_POST['id_classe'] : (isset($_GET['id_classe']) ? $_GET['id_classe'] : NULL);
$periode = isset($_POST['periode']) ? $_POST['periode'] : (isset($_GET['periode']) ? $_GET['periode'] : NULL);
$suiv = isset($_GET['suiv']) ? $_GET['suiv'] : 'no';
$prec = isset($_GET['prec']) ? $_GET['prec'] : 'no';
$v_eleve = isset($_POST['v_eleve']) ? $_POST['v_eleve'] : (isset($_GET['v_eleve']) ? $_GET['v_eleve'] : NULL);

include "../lib/periodes.inc.php";
?>
<p class='bold'>|<a href='../accueil.php'>Accueil</a>|<a href='index.php'>Autre outil de visualisation</a>|
<?php

if (!isset($id_classe)) {
    echo "</p><p>S�lectionnez la classe :<br />\n";
    //$call_data = mysql_query("SELECT DISTINCT c.* FROM classes c, periodes p WHERE p.id_classe = c.id  ORDER BY classe");
    //$call_data = mysql_query("SELECT DISTINCT c.* FROM classes c, periodes p, j_scol_classes jsc WHERE p.id_classe = c.id  AND jsc.id_classe=c.id AND jsc.login='".$_SESSION['login']."' ORDER BY classe");

	if($_SESSION['statut']=='scolarite'){
		$call_data = mysql_query("SELECT DISTINCT c.* FROM classes c, periodes p, j_scol_classes jsc WHERE p.id_classe = c.id  AND jsc.id_classe=c.id AND jsc.login='".$_SESSION['login']."' ORDER BY classe");
	}
	elseif($_SESSION['statut']=='professeur'){
		$call_data=mysql_query("SELECT DISTINCT c.* FROM classes c, periodes p, j_groupes_classes jgc, j_groupes_professeurs jgp WHERE p.id_classe = c.id AND jgc.id_classe=c.id AND jgp.id_groupe=jgc.id_groupe AND jgp.login='".$_SESSION['login']."' ORDER BY c.classe");
	}
	elseif($_SESSION['statut']=='cpe'){
		$call_data=mysql_query("SELECT DISTINCT c.* FROM classes c, periodes p, j_eleves_classes jec, j_eleves_cpe jecpe WHERE
			p.id_classe = c.id AND
			jec.id_classe=c.id AND
			jec.periode=p.num_periode AND
			jecpe.e_login=jec.login AND
			jecpe.cpe_login='".$_SESSION['login']."'
			ORDER BY classe");
	}

    $nombre_lignes = mysql_num_rows($call_data);
    $i = 0;
	$nb_class_par_colonne=round($nombre_lignes/3);
        //echo "<table width='100%' border='1'>\n";
        echo "<table width='100%'>\n";
        echo "<tr valign='top' align='center'>\n";
        echo "<td align='left'>\n";
    while ($i < $nombre_lignes){
		$classe = mysql_result($call_data, $i, "classe");
		$ide_classe = mysql_result($call_data, $i, "id");

		if(($i>0)&&(round($i/$nb_class_par_colonne)==$i/$nb_class_par_colonne)){
			echo "</td>\n";
			//echo "<td style='padding: 0 10px 0 10px'>\n";
			echo "<td align='left'>\n";
		}

		echo "<a href='eleve_classe.php?id_classe=$ide_classe'>$classe</a><br />\n";
		$i++;
    }
    //echo "</p>\n";
        echo "</table>\n";
} else {
    echo "<a href=\"eleve_classe.php\">Choisir une autre classe</a>|\n";

    if (!$periode) {
        $call_classe = mysql_query("SELECT classe FROM classes WHERE id = '$id_classe'");
        $classe = mysql_result($call_classe, "0", "classe");

        ?>
        </p><p><span class='grand'>Classe : <?php echo $classe; ?></span><br />
        <br />Choisissez quelle p�riode vous souhaitez visualiser :<br />
        <form enctype="multipart/form-data" action="eleve_classe.php?temp=0#graph" method=post>
        <?php
        $i="1";
        while ($i < $nb_periode) {
            echo "<input type='radio' name='periode' value='$i' "; if ($i == '1') { echo "CHECKED";} echo " />$nom_periode[$i]<br />\n";
        $i++;
        }
        ?>
        <input type='radio' name='periode' value='annee' />Ann�e compl�te<br />
        <input type='submit' value='Visualiser' />
        <input type='hidden' name='id_classe' value='<?php echo $id_classe; ?>' />
        </form>
        <!--/p-->
        <br />
        <?php
    } else {
        $call_classe = mysql_query("SELECT classe FROM classes WHERE id = '$id_classe'");
        $classe = mysql_result($call_classe, "0", "classe");
        $call_eleve = mysql_query("SELECT DISTINCT e.* FROM eleves e, j_eleves_classes c WHERE (c.id_classe = '$id_classe' and e.login = c.login) order by nom");
        $nombreligne = mysql_num_rows($call_eleve);

        if (!isset($v_eleve)) {$v_eleve = @mysql_result($call_eleve, 0, 'login');}

        if ($suiv == 'yes') {
            $i = "0" ;
            while ($i < $nombreligne) {
                if ($v_eleve == mysql_result($call_eleve, $i, 'login') and ($i < $nombreligne-1)) {$v_eleve = mysql_result($call_eleve, $i+1, 'login');$i = $nombreligne;}
            $i++;
            }
        }
        if ($prec == 'yes') {
            $i = "0" ;
            while ($i < $nombreligne) {
                if ($v_eleve == mysql_result($call_eleve, $i, 'login') and ($i > '0')) {$v_eleve = mysql_result($call_eleve, $i-1, 'login');$i = $nombreligne;}
            $i++;
            }
        }
        ?>
    <table border=0><tr><td><p class=bold>|<a href="eleve_classe.php?id_classe=<?php echo $id_classe; ?>">Choisir une autre p�riode</a>|
        <a href="eleve_classe.php?id_classe=<?php echo $id_classe; ?>&amp;v_eleve=<?php echo $v_eleve; ?>&amp;prec=yes&amp;periode=<?php echo $periode; ?>">El�ve pr�c�dent</a>|
        <a href="eleve_classe.php?id_classe=<?php echo $id_classe; ?>&amp;suiv=yes&amp;periode=<?php echo $periode; ?>&amp;v_eleve=<?php echo $v_eleve; ?>">El�ve suivant</a>|
        </p></td>

        <td><form enctype="multipart/form-data" action="eleve_classe.php" method=post>
        <select size=1 name=v_eleve onchange="this.form.submit()">
        <?php
        $i = "0" ;
        while ($i < $nombreligne) {
            $eleve = mysql_result($call_eleve, $i, 'login');
            $nom_el = mysql_result($call_eleve, $i, 'nom');
            $prenom_el = mysql_result($call_eleve, $i, 'prenom');
            echo "<option value=$eleve";
            if ($v_eleve == $eleve) {echo " SELECTED ";}
            echo ">$nom_el  $prenom_el </option>";
        $i++;
        }
        ?>
        </select>
        <input type='hidden' name='id_classe' value='<?php echo $id_classe; ?>' />
        <input type='hidden' name='periode' value='<?php echo $periode; ?>' />
        </form></td></tr></table>
        <?php
        // On appelle les informations de l'utilisateur pour les afficher :
        $call_eleve_info = mysql_query("SELECT login,nom,prenom FROM eleves WHERE login='$v_eleve'");
        $eleve_nom = mysql_result($call_eleve_info, "0", "nom");
        $eleve_prenom = mysql_result($call_eleve_info, "0", "prenom");

        if ($periode != 'annee') {
                $temp = strtolower($nom_periode[$periode]);
        } else {
                $temp = 'Ann�e compl�te';
        }
        $graph_title = $eleve_nom." ".$eleve_prenom.", ".$classe.", ".$temp;
        $v_legend1 = "";
        $v_legend2 = "";
        $v_legend1 = $eleve_nom." ".$eleve_prenom;
        $v_legend2 = "Moy. ".$classe ;
        echo "<p>$eleve_nom  $eleve_prenom, classe de $classe   |  $temp</p>";
        echo "<table  border=1 cellspacing=2 cellpadding=5>";
        echo "<tr><td width='100'><p>Mati�re</p></td><td width='100'><p>Note �l�ve</p></td><td width='100'><p>Moyenne classe</p></td><td width='100'><p>Diff�rence</p></td></tr>";

        $affiche_categories = sql_query1("SELECT display_mat_cat FROM classes WHERE id='".$id_classe."'");
        if ($affiche_categories == "y") {
            $affiche_categories = true;
        } else {
            $affiche_categories = false;
        }

        if ($affiche_categories) {
            // On utilise les valeurs sp�cifi�es pour la classe en question
            $call_groupes = mysql_query("SELECT DISTINCT jgc.id_groupe ".
            "FROM j_eleves_groupes jeg, j_groupes_classes jgc, j_groupes_matieres jgm, j_matieres_categories_classes jmcc, matieres m " .
            "WHERE ( " .
            "jeg.login = '" . $v_eleve ."' AND " .
            "jgc.id_groupe = jeg.id_groupe AND " .
            "jgc.categorie_id = jmcc.categorie_id AND " .
            "jgc.id_classe = '".$id_classe."' AND " .
            "jgm.id_groupe = jgc.id_groupe AND " .
            "m.matiere = jgm.id_matiere" .
            ") " .
            "ORDER BY jmcc.priority,jgc.priorite,m.nom_complet");
        } else {
            $call_groupes = mysql_query("SELECT DISTINCT jgc.id_groupe, jgc.coef " .
            "FROM j_groupes_classes jgc, j_groupes_matieres jgm, j_eleves_groupes jeg " .
            "WHERE ( " .
            "jeg.login = '" . $v_eleve . "' AND " .
            "jgc.id_groupe = jeg.id_groupe AND " .
            "jgc.id_classe = '".$id_classe."' AND " .
            "jgm.id_groupe = jgc.id_groupe" .
            ") " .
            "ORDER BY jgc.priorite,jgm.id_matiere");
        }


        $nombre_lignes = mysql_num_rows($call_groupes);
        $i = 0;
        $compteur = 0;
        $moyenne_classe = '';
        $prev_cat_id = null;
        while ($i < $nombre_lignes) {
            $inserligne="no";
            $group_id = mysql_result($call_groupes, $i, "id_groupe");
            $current_group = get_group($group_id);

            if ($periode != 'annee') {
                if (in_array($v_eleve, $current_group["eleves"][$periode]["list"])) {
                    $inserligne="yes";
                    $note_eleve_query=mysql_query("SELECT * FROM matieres_notes WHERE (login='$v_eleve' AND periode='$periode' AND id_groupe='" . $current_group["id"] . "')");
                    $eleve_matiere_statut = @mysql_result($note_eleve_query, 0, "statut");
                    $note_eleve = @mysql_result($note_eleve_query, 0, "note");
                    if ($eleve_matiere_statut != "") { $note_eleve = $eleve_matiere_statut;}
                    if ($note_eleve == '') {$note_eleve = '-';}
                    $moyenne_classe_query = mysql_query("SELECT round(avg(note),1) as moyenne FROM matieres_notes WHERE (periode='$periode' AND id_groupe='" . $current_group["id"] . "' AND statut ='')");
                    $moyenne_classe = mysql_result($moyenne_classe_query, 0, "moyenne");
                }
            } else {
                $z = 1;
                $response = "no";
                while ($z < $nb_periode) {
                    if (in_array($v_eleve, $current_group["eleves"][$z]["list"])) $reponse = "yes";
                    $z++;
                }
                if ($reponse == 'yes') {
                    // L'�l�ve suit la mati�re au moins sur une des p�riodes de l'ann�e, donc on affiche la mati�re dans le tableau.
                    $inserligne="yes";
                    $note_eleve_annee_query=mysql_query("SELECT round(avg(note),1) moyenne FROM matieres_notes WHERE (login='$v_eleve' AND id_groupe='" . $current_group["id"] ."' AND statut='')");
                    $note_eleve = @mysql_result($note_eleve_annee_query, 0, "moyenne");
                    if ($note_eleve == '') {$note_eleve = '-';}
                    $z = 1;
                    while ($z < $nb_periode) {
                        $moyenne_classe_query = mysql_query("SELECT round(avg(note),1) as moyenne FROM matieres_notes WHERE (periode='$z' AND id_groupe='" . $current_group["id"] . "' AND statut ='')");
                        $temp = @mysql_result($moyenne_classe_query, 0, "moyenne");
                        $moyenne_classe = $moyenne_classe + $temp;
                        $z++;
                    }
                    $moyenne_classe = round($moyenne_classe/($nb_periode-1),1);
                }
            }
            if ($inserligne == "yes") {

                if ($affiche_categories) {
                // On regarde si on change de cat�gorie de mati�re
                    if ($current_group["classes"]["classes"][$id_classe]["categorie_id"] != $prev_cat_id) {
                        $prev_cat_id = $current_group["classes"]["classes"][$id_classe]["categorie_id"];
                        // On est dans une nouvelle cat�gorie
                        // On r�cup�re les infos n�cessaires, et on affiche une ligne
                        $cat_name = html_entity_decode_all_version(mysql_result(mysql_query("SELECT nom_complet FROM matieres_categories WHERE id = '" . $current_group["classes"]["classes"][$id_classe]["categorie_id"] . "'"), 0));
                        // On d�termine le nombre de colonnes pour le colspan
                        $nb_total_cols = 4;

                        // On a toutes les infos. On affiche !
                        echo "<tr>";
                        echo "<td colspan='" . $nb_total_cols . "'>";
                        echo "<p style='padding: 5; margin:0; font-size: 15px;'>".$cat_name."</p></td>";
                        echo "</tr>";
                    }
                }

                $moyenne_classe = mysql_result($moyenne_classe_query, 0, "moyenne");
                if ($moyenne_classe == '') {$moyenne_classe = '-';}
                if (($note_eleve == "-") or ($moyenne_classe == "-")) {$difference = '-';} else {$difference = $note_eleve-$moyenne_classe;}
                //echo "<tr><td><p>" . $current_group["description"] . "</p></td><td><p>$note_eleve";
                echo "<tr><td><p>" . htmlentities($current_group["description"]) . "</p></td><td><p>$note_eleve";
                echo "</p></td><td><p>$moyenne_classe</p></td><td><p>$difference</p></td></tr>";
                (ereg ("^[0-9\.\,]{1,}$", $note_eleve)) ? array_push($datay1,"$note_eleve") : array_push($datay1,"0");
                (ereg ("^[0-9\.\,]{1,}$", $moyenne_classe)) ? array_push($datay2,"$moyenne_classe") : array_push($datay2,"0");
                //array_push($etiquette,$current_group["matiere"]["nom_complet"]);
                array_push($etiquette,rawurlencode($current_group["matiere"]["nom_complet"]));
                $compteur++;
            }
            $i++;
        }
        echo "</table>";
        ?>
    <br />
    <a name="graph"></a>
    <table border=0><tr><td><span class=bold>|<a href="eleve_classe.php?id_classe=<?php echo $id_classe; ?>">Choisir une autre p�riode</a>|
        <a href="eleve_classe.php?id_classe=<?php echo $id_classe; ?>&amp;v_eleve=<?php echo $v_eleve; ?>&amp;prec=yes&amp;periode=<?php echo $periode; ?>#graph">El�ve pr�c�dent</a>|
        <a href="eleve_classe.php?id_classe=<?php echo $id_classe; ?>&amp;suiv=yes&amp;periode=<?php echo $periode; ?>&amp;v_eleve=<?php echo $v_eleve; ?>#graph">El�ve suivant</a>|</span></td>

        <td><form enctype="multipart/form-data" action="eleve_classe.php?temp=0#graph" method=post>
        <select size=1 name=v_eleve onchange="this.form.submit()">
        <?php
        $i = "0" ;
        while ($i < $nombreligne) {
            $eleve = mysql_result($call_eleve, $i, 'login');
            $nom_el = mysql_result($call_eleve, $i, 'nom');
            $prenom_el = mysql_result($call_eleve, $i, 'prenom');
            echo "<option value=$eleve";
            if ($v_eleve == $eleve) {echo " SELECTED ";}
            echo ">$nom_el  $prenom_el </option>\n";
        $i++;
        }
        ?>
        </select>
        <input type='hidden' name='id_classe' value='<?php echo $id_classe; ?>' />
        <input type='hidden' name='periode' value='<?php echo $periode; ?>' />
        </form></td></tr></table>
        <?php
        $temp1=implode("|", $datay1);
        $temp2=implode("|", $datay2);
        $etiq = implode("|", $etiquette);
        $graph_title = urlencode($graph_title);
        $v_legend1 = urlencode($v_legend1);
        $v_legend2 = urlencode($v_legend2);

        //echo "<img src='draw_artichow1.php?temp1=$temp1&temp2=$temp2&etiquette=$etiq&titre=$graph_title&v_legend1=$v_legend1&v_legend2=$v_legend2&compteur=$compteur&nb_data=3'>";
        //echo "<img src='draw_artichow1.php?temp1=$temp1&amp;temp2=$temp2&amp;etiquette=".rawurlencode("$etiq")."&amp;titre=$graph_title&amp;v_legend1=$v_legend1&amp;v_legend2=$v_legend2&amp;compteur=$compteur&amp;nb_data=3'>\n";
        echo "<img src='draw_artichow1.php?temp1=$temp1&amp;temp2=$temp2&amp;etiquette=$etiq&amp;titre=$graph_title&amp;v_legend1=$v_legend1&amp;v_legend2=$v_legend2&amp;compteur=$compteur&amp;nb_data=3' alt='Graphe de ".urldecode($v_legend1)."' />\n";

        echo "<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />";

    }
}
?>
</body>
</html>