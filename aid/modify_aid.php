<?php
/*
 * $Id$
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
//extract($_GET, EXTR_OVERWRITE);
//extract($_POST, EXTR_OVERWRITE);

// Initialisation des variables
$flag = isset($_GET["flag"]) ? $_GET["flag"] : (isset($_POST["flag"]) ? $_POST["flag"] : NULL);
$aid_id = isset($_GET["aid_id"]) ? $_GET["aid_id"] : (isset($_POST["aid_id"]) ? $_POST["aid_id"] : NULL);
$indice_aid = isset($_GET["indice_aid"]) ? $_GET["indice_aid"] : (isset($_POST["indice_aid"]) ? $_POST["indice_aid"] : NULL);
$add_eleve = isset($_POST["add_eleve"]) ? $_POST["add_eleve"] : NULL;
$add_prof = isset($_POST["add_prof"]) ? $_POST["add_prof"] : NULL;
$reg_prof_login = isset($_POST["reg_prof_login"]) ? $_POST["reg_prof_login"] : NULL;
$reg_add_eleve_login = isset($_POST["reg_add_eleve_login"]) ? $_POST["reg_add_eleve_login"] : NULL;

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

if (isset($add_prof) and ($add_prof == "yes")) {
    // On commence par v�rifier que le professeur n'est pas d�j� pr�sent dans cette liste.
    $test = mysql_query("SELECT * FROM j_aid_utilisateurs WHERE (id_utilisateur = '$reg_prof_login' and id_aid = '$aid_id' and indice_aid='$indice_aid')");
    $test2 = mysql_num_rows($test);
    if ($test2 != "0") {
        $msg = "Le professeur que vous avez tent� d'ajouter appartient d�j� � cet AID";
    } else {
        if ($reg_prof_login != '') {
            $reg_data = mysql_query("INSERT INTO j_aid_utilisateurs SET id_utilisateur= '$reg_prof_login', id_aid = '$aid_id', indice_aid='$indice_aid'");
            if (!$reg_data) { $msg = "Erreur lors de l'ajout du professeur !"; } else { $msg = "Le professeur a bien �t� ajout� !"; }
        }
    }
    $flag = "prof";
}

if (isset($add_eleve) and ($add_eleve == "yes")) {
    // Les �l�ves responsable : � chercher parmi les �l�ves de l'AID
    // On commence par supprimer les �l�ves responsables
    sql_query("delete from j_aid_eleves_resp where id_aid='$aid_id' and indice_aid='$indice_aid'");
    // Les �l�ves responsable sont � s�lectionner parmi les �l�ves de l'AID
    $call_eleves = mysql_query("SELECT * FROM j_aid_eleves WHERE (indice_aid='$indice_aid' and id_aid='$aid_id')");
    $nombre = mysql_num_rows($call_eleves);
    $i = "0";
    while ($i < $nombre) {
        $login_eleve = mysql_result($call_eleves, $i, "login");
        if (isset($_POST[$login_eleve."_resp"])) {
            sql_query("insert into j_aid_eleves_resp set id_aid='$aid_id', login='$login_eleve', indice_aid='$indice_aid'");
        }
        $i++;
    }

    // On commence par v�rifier que l'�l�ve n'est pas d�j� pr�sent dans cette liste, ni dans aucune.
    $test = mysql_query("SELECT * FROM j_aid_eleves WHERE (login='$reg_add_eleve_login' and indice_aid='$indice_aid')");
    $test2 = mysql_num_rows($test);
    if ($test2 != "0") {
        $msg = "L'�l�ve que vous avez tent� d'ajouter appartient d�j� � une AID";
    } else {
        if ($reg_add_eleve_login != '') {
            $reg_data = mysql_query("INSERT INTO j_aid_eleves SET login='$reg_add_eleve_login', id_aid='$aid_id', indice_aid='$indice_aid'");
            if (!$reg_data) { $msg = "Erreur lors de l'ajout de l'�l�ve"; } else { $msg = "L'�l�ve a bien �t� ajout�."; }
        }
    }
    $msg .= "<br />Les modifications ont �t� enregistr�es.";
    $flag = "eleve";
}


// On appelle les informations de l'aid pour les afficher :
$call_data = mysql_query("SELECT * FROM aid_config WHERE indice_aid = '$indice_aid'");
$nom_aid = @mysql_result($call_data, 0, "nom");
$activer_outils_comp = @mysql_result($call_data, 0, "outils_complementaires");

$calldata = mysql_query("SELECT nom FROM aid where (id = '$aid_id' and indice_aid='$indice_aid')");
$aid_nom = mysql_result($calldata, 0, "nom");
$_SESSION['chemin_retour'] = $_SERVER['REQUEST_URI'];

if (!isset($_GET["aid_id"]) OR !isset($_GET["indice_aid"]) OR !isset($_GET["flag"])) {
	$_SESSION['chemin_retour'] = $_SERVER['REQUEST_URI'].'?flag='.$flag.'&aid_id='.$aid_id.'&indice_aid='.$indice_aid;
}
// Ajout d'un style sp�cifique pour l'AID
$style_specifique = "aid/style_aid";

//**************** EN-TETE *********************
$titre_page = "Gestion des $nom_aid | Modifier les $nom_aid";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************


// On affiche un select avec la liste des aid de cette cat�gorie
$sql = "SELECT id, nom FROM aid WHERE indice_aid = '".$indice_aid."' ORDER BY numero";
$query = mysql_query($sql) OR DIE('Erreur dans la requ�te select * from aid : '.mysql_error());
$nbre = mysql_num_rows($query);

$aff_precedent = '';
$aff_suivant = '';

// On recherche les AID pr�c�dente et suivante
for($a = 0; $a < $nbre; $a++){
	$aid_p[$a]["id"] = mysql_result($query, $a, "id");

	// On teste pour savoir quel est le aid_id actuellement affich�
	if ($a != 0) {
		// Alors on propose un lien vers l'AID pr�c�dente
		if ($aid_p[$a]["id"] == $aid_id) {
			$aid_precedent = $aid_p[$a-1]["id"];
			$aff_precedent = '
			<a href="modify_aid.php?flag='.$flag.'&amp;indice_aid='.$indice_aid.'&amp;aid_id='.$aid_precedent.'">Aid pr�c�dente&nbsp;</a>';
		}
	}

	if ($a < ($nbre - 1)) {
		// alors on propose un lien vers l'AID suivante
		if ($aid_p[$a]["id"] == $aid_id) {
			$aid_suivant = mysql_result($query, $a+1, "id");
			$aff_suivant = '
			<a href="modify_aid.php?flag='.$flag.'&amp;indice_aid='.$indice_aid.'&amp;aid_id='.$aid_suivant.'">&nbsp;Aid suivante</a>';
		}
	}
}

echo '<form action="modify_aid.php" method="post" name="autre_aid">
<p class="bold"><a href="index2.php?indice_aid='.$indice_aid.'">
	<img src="../images/icons/back.png" alt="Retour" class="back_link" /> Retour</a>&nbsp;|&nbsp;'.$aff_precedent.'
		<select name="aid_id" onchange="document.autre_aid.submit();">
	';

// On recommence le query
$query = mysql_query($sql) OR DIE('Erreur dans la requ�te select * from aid : '.mysql_error());
while($infos = mysql_fetch_array($query)){
	// On affiche la liste des "<option>"
	if ($aid_id == $infos["id"]) {
		$selected = ' selected="selected"';
	}else{
		$selected = '';
	}
	echo '<option value="'.$infos["id"].'"'.$selected.'>&nbsp;'.$infos["nom"].'&nbsp;</option>'."\n";
}
echo '
		</select>
		<input type="hidden" name="indice_aid" value="'.$indice_aid.'" />
		<input type="hidden" name="flag" value="'.$flag.'" />'.$aff_suivant.'
</p>
	</form>';


if ($flag == "prof") { ?>
   <p class='grand'><?php echo "$nom_aid  $aid_nom";?></p>
    <p class='bold'>Liste des professeurs responsables :</p>
    <br />Les noms des professeurs ci-dessous figurent (selon le param&eacute;trage) sur les bulletins officiels et/ou les bulletins simplifi&eacute;s.<br />
    <?php
    if ($activer_outils_comp == "y")
        echo "De plus ces professeurs peuvent modifier les fiches projet (si l'administrateur a activ� cette possibilit�).<br />";


    $vide = 1;
    $call_liste_data = mysql_query("SELECT u.login, u.prenom, u.nom FROM utilisateurs u, j_aid_utilisateurs j WHERE (j.id_aid='$aid_id' and u.login=j.id_utilisateur and j.indice_aid='$indice_aid')  order by u.nom, u.prenom");
    $nombre = mysql_num_rows($call_liste_data);
    $i = "0";
    while ($i < $nombre) {
        $vide = 0;
        $login_prof = mysql_result($call_liste_data, $i, "login");
        $nom_prof = mysql_result($call_liste_data, $i, "nom");
        $prenom_prof = @mysql_result($call_liste_data, $i, "prenom");

        echo "<br /><b>";
        echo "$nom_prof $prenom_prof</b> | <a href='../lib/confirm_query.php?liste_cible=$login_prof&amp;liste_cible2=$aid_id&amp;liste_cible3=$indice_aid&amp;action=del_prof_aid'>\n<font size=2>supprimer</font></a>\n";
    $i++;
    }
    if ($vide == 1) {
        echo "<h4 style=\"color: red;\">Il n'y a pas actuellement de professeur responsable !</h4>";
    }
    ?>
    <p class='bold'>Ajouter un professeur responsable � la liste de l'AID :</p>

    <form enctype="multipart/form-data" action="modify_aid.php" method="post">
    <select size=1 name="reg_prof_login">
    <!--option value=''><p>(aucun)</p></option-->
    <option value=''>(aucun)</option>
    <?php
    $call_prof = mysql_query("SELECT login, nom, prenom FROM utilisateurs WHERE  etat!='inactif' AND statut = 'professeur' order by nom");
    $nombreligne = mysql_num_rows($call_prof);
    $i = "0" ;
    while ($i < $nombreligne) {
        $login_prof = mysql_result($call_prof, $i, 'login');
        $nom_el = mysql_result($call_prof, $i, 'nom');
        $prenom_el = mysql_result($call_prof, $i, 'prenom');

        echo "<option value=\"".$login_prof."\">".$nom_el." ".$prenom_el."</option>\n";
    $i++;
    }
    ?>
    </select>
    <input type="hidden" name="add_prof" value="yes" />
    <input type="hidden" name="aid_id" value="<?php echo $aid_id;?>" />
    <input type="hidden" name="indice_aid" value="<?php echo $indice_aid;?>" />
    <input type="submit" value='Enregistrer' />
    </form>
<?php }

if ($flag == "eleve") {
	// On ajoute le nom des profs et le nombre d'�l�ves
	$aff_profs = "<font style=\"color: brown; font-size: 12px;\">(";
	$req_profs = mysql_query("SELECT id_utilisateur FROM j_aid_utilisateurs WHERE id_aid = '".$aid_id."'");
	$nbre_profs = mysql_num_rows($req_profs);
	for($a=0; $a<$nbre_profs; $a++) {
		$rep_profs[$a]["id_utilisateur"] = mysql_result($req_profs, $a, "id_utilisateur");
		$rep_profs_a = mysql_fetch_array(mysql_query("SELECT nom, civilite FROM utilisateurs WHERE login = '".$rep_profs[$a]["id_utilisateur"]."'"));
		$aff_profs .= "".$rep_profs_a["civilite"].$rep_profs_a["nom"]." ";
	}
		$aff_profs .= ")</font>";
?>
    <p class='grand'><?php echo "$nom_aid  $aid_nom. $aff_profs"; ?></p>

    <p class = 'bold'>Liste des �l�ves de l'AID <?php echo $aid_nom ?> :</p>
    <hr />
    <?php
    $vide = 1;
    // Ajout d'un tableau
echo "<form enctype=\"multipart/form-data\" action=\"modify_aid.php\" method=\"post\">\n";
	echo "<table class=\"aid_tableau\" border=\"0\">";
    // appel de la liste des �l�ves de l'AID :
    $call_liste_data = mysql_query("SELECT e.login, e.nom, e.prenom, e.elenoet FROM eleves e, j_aid_eleves j WHERE (j.id_aid='$aid_id' and e.login=j.login and j.indice_aid='$indice_aid') ORDER BY nom, prenom");
    $nombre = mysql_num_rows($call_liste_data);
    // On affiche d'abord le nombre d'�l�ves
    		$s = "";
		if ($nombre >= 2) {
			$s = "s";
		}
		else {
			$s = "";
		}
    echo "<tr><td>\n";
    echo $nombre." �l�ve".$s.".\n</td><td></td>";
    if ($activer_outils_comp == "y") {
      echo "<td>El�ve responsable (*)</td>";
    }
    echo "</tr>\n";
    $i = "0";
    while ($i < $nombre) {
        $vide = 0;
        $login_eleve = mysql_result($call_liste_data, $i, "login");
        $nom_eleve = mysql_result($call_liste_data, $i, "nom");
        $prenom_eleve = @mysql_result($call_liste_data, $i, "prenom");
        $eleve_resp = sql_query1("select login from j_aid_eleves_resp where id_aid='$aid_id' and login ='$login_eleve' and indice_aid='$indice_aid'");
        $call_classe = mysql_query("SELECT c.classe FROM classes c, j_eleves_classes j WHERE (j.login = '$login_eleve' and j.id_classe = c.id) order by j.periode DESC");
        $classe_eleve = @mysql_result($call_classe, '0', "classe");
        $v_elenoet=mysql_result($call_liste_data, $i, 'elenoet');
        echo "<tr><td>\n";
        echo "<b>$nom_eleve $prenom_eleve</b>, $classe_eleve </td>\n<td> <a href='../lib/confirm_query.php?liste_cible=$login_eleve&amp;liste_cible2=$aid_id&amp;liste_cible3=$indice_aid&amp;action=del_eleve_aid'><img src=\"../images/icons/delete.png\" title=\"Supprimer cet �l�ve\" alt=\"Supprimer\" /></a>\n";

        // Dans le cas o� la cat�gorie d'AID est utilis�e pour la gestion des acc�s au trombinoscope, on ajouter un lien sur la photo de l'�l�ve.
        if (getSettingValue("num_aid_trombinoscopes")==$indice_aid) {
          $info="<div align='center'>\n";
      	  if($v_elenoet!=""){
		        $photo=nom_photo($v_elenoet);
		        if($photo!=""){
			          $info.="<img src='../photos/eleves/".$photo."' width='150' alt=\"photo\" />";
		        }
	        }
      	  $info.="</div>\n";
      	  $tabdiv_infobulle[]=creer_div_infobulle('info_popup_eleve'.$v_elenoet,$titre,"",$info,"",14,0,'y','y','n','n');

		      if($photo!="") {
       	    echo "<a href='#' onmouseover=\"afficher_div('info_popup_eleve".$v_elenoet."','y',-100,20);\"";
	          echo " onmouseout=\"cacher_div('info_popup_eleve".$v_elenoet."');\">";
	          echo "<img src='../images/icons/buddy.png' alt='Photo �l�ve' />";
	          echo "</a>";
	        } else {
	          echo "<img src='../images/icons/buddy_no.png' alt='Pas de photo' />";
          }
        }

        echo "</td>";
        if ($activer_outils_comp == "y") {
            echo "<td><center><input type=\"checkbox\" name=\"".$login_eleve."_resp\" value=\"y\" ";
            if ($eleve_resp!=-1) echo " checked ";
        echo "/></center></td>";
        }
        echo "</tr>\n";
    $i++;
    }

    echo "</table>";

    if ($vide == 1) {
        echo "<br /><p style=\"color: red;\">Il n'y a pas actuellement d'�l�ves dans cette AID !</p>";
    }
    $call_eleve = mysql_query("SELECT e.login, e.nom, e.prenom FROM eleves e LEFT JOIN j_aid_eleves j ON (e.login = j.login  and j.indice_aid='$indice_aid') WHERE j.login is null order by e.nom, e.prenom");
    $nombreligne = mysql_num_rows($call_eleve);
    if ($nombreligne != 0) {
        echo "<br />\n<p><span class = 'bold'>Ajouter un �l�ve � la liste de l'AID :</span>\n";
        echo "<a href=\"modify_aid_new.php?id_aid=".$aid_id."&amp;indice_aid=".$indice_aid."\">Lister les �l�ves par classe</a>\n";
        echo "<br /><select size=\"1\" name=\"reg_add_eleve_login\">";
        //echo "<option value=''><p>(aucun)</p></option>";
        echo "<option value=''>(aucun)</option>\n";
        $i = "0" ;
        while ($i < $nombreligne) {
            $eleve = mysql_result($call_eleve, $i, 'login');
            $nom_el = mysql_result($call_eleve, $i, 'nom');
            $prenom_el = mysql_result($call_eleve, $i, 'prenom');

            $call_classe = mysql_query("SELECT c.classe FROM classes c, j_eleves_classes j WHERE (j.login = '$eleve' and j.id_classe = c.id) order by j.periode DESC");
            $classe_eleve = @mysql_result($call_classe, '0', "classe");
            echo "<option value=\"$eleve\">$nom_el  $prenom_el $classe_eleve</option>\n";
        $i++;
        }
        ?>
        </select>


<?php } else {
        echo "<p>Tous les �l�ves de la base ont une AID. Impossible d'ajouter un �l�ve � cette AID !</p>";
    }
    ?>
    <input type="hidden" name="add_eleve" value="yes" />
    <input type="hidden" name="indice_aid" value="<?php echo $indice_aid;?>" />
    <input type="hidden" name="aid_id" value="<?php echo $aid_id;?>" />
    <input type="submit" value='Enregistrer' />
    </form>
    <?php if ($activer_outils_comp == "y") {?>
    <p><br />(*) Les &eacute;l&egrave;ves responsables peuvent par exemple acc&eacute;der dans certaines conditions &agrave; l'&eacute;dition des fiches AID.
    <?php }
}
require ("../lib/footer.inc.php");
?>
