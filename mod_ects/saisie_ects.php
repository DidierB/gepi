<?php
/*
 * $Id: saisie_avis2.php 2167 2008-07-25 14:20:51Z crob $
 *
 * Copyright 2001, 2009 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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

// On indique qu'il faut creer des variables non prot�g�es (voir fonction cree_variables_non_protegees())
$variables_non_protegees = 'yes';

// Initialisations files
include("../lib/initialisationsPropel.inc.php");
require_once("../lib/initialisations.inc.php");

$gepiYear = $gepiSettings['gepiYear'];

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
};

include "../lib/bulletin_simple.inc.php";
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}

// On teste si un professeur peut saisir les ECTS
if (($_SESSION['statut'] == 'professeur') and $gepiSettings['GepiAccesSaisieEctsPP'] != 'yes') {
   die("Droits insuffisants pour effectuer cette op�ration");
}

// On teste si le service scolarit� peut saisir les avis
if (($_SESSION['statut'] == 'scolarite') and $gepiSettings['GepiRubConseilScol'] !='yes') {
   die("Droits insuffisants pour effectuer cette op�ration");
}

// initialisation
$id_classe = isset($_POST["id_classe"]) ? $_POST["id_classe"] :(isset($_GET["id_classe"]) ? $_GET["id_classe"] :NULL);
$periode_num = isset($_POST["periode_num"]) ? $_POST["periode_num"] :(isset($_GET["periode_num"]) ? $_GET["periode_num"] :NULL);
$fiche = isset($_POST["fiche"]) ? $_POST["fiche"] :(isset($_GET["fiche"]) ? $_GET["fiche"] :NULL);
$current_eleve_login = isset($_POST["current_eleve_login"]) ? $_POST["current_eleve_login"] :(isset($_GET["current_eleve_login"]) ? $_GET["current_eleve_login"] :NULL);
$ind_eleve_login_suiv = isset($_POST["ind_eleve_login_suiv"]) ? $_POST["ind_eleve_login_suiv"] :(isset($_GET["ind_eleve_login_suiv"]) ? $_GET["ind_eleve_login_suiv"] :NULL);
$current_eleve_login_ap = isset($NON_PROTECT["current_eleve_login_ap"]) ? traitement_magic_quotes(corriger_caracteres($NON_PROTECT["current_eleve_login_ap"])) :NULL;
$affiche_message = isset($_GET["affiche_message"]) ? $_GET["affiche_message"] :NULL;

include "../lib/periodes.inc.php";

//*******************************************************************************************************
$msg = '';
if (isset($_POST['is_posted'])) {
    if (($periode_num < $nb_periode) and ($periode_num > 0) and ($ver_periode[$periode_num] != "O"))  {
        $reg = 'yes';
        // si l'utilisateur n'a pas le statut scolarit�, on v�rifie qu'il est prof principal de l'�l�ve
        if (($_SESSION['statut'] != 'scolarite') and ($_SESSION['statut'] != 'secours')) {
             $test_prof_suivi = sql_query1("select professeur from j_eleves_professeurs
             where login = '$current_eleve_login' and
             professeur = '".$_SESSION['login']."' and
             id_classe = '".$id_classe."'
             ");
             if ($test_prof_suivi == '-1') {
                 $msg = "Vous n'�tes pas professeur de suivi de cet �l�ve.";
                 $reg = 'no';
             }
         }
         if ($reg == 'yes') {

             // C'est ici que l'enregistrement se passe r�ellement.

            $Eleve = ElevePeer::retrieveByLOGIN($current_eleve_login);
            $groupes = $Eleve->getEctsGroupes($periode_num);
            foreach($groupes as $groupe) {
                // On a l'�l�ve, le groupe, et la p�riode. On peut enregistrer.
                $valeur_ects = $_POST['valeur_ects_'.$groupe->getId()];
                $mention_ects = $_POST['mention_ects_'.$groupe->getId()];
                if (!empty($valeur_ects) && !is_numeric($valeur_ects)) $valeur_ects = "0";
                if (!in_array($mention_ects, array("A","B","C","D","E","F"))) $mention_ects = '';

                $Eleve->setEctsCredit($periode_num,$groupe->getId(),$valeur_ects,$mention_ects);
            }
        }

    } else {
        $msg = "La p�riode sur laquelle vous voulez enregistrer est verrouill�e";
    }
    if (isset($_POST['ok1']))  {
        if (($_SESSION['statut'] == 'scolarite') or ($_SESSION['statut'] == 'secours')) {
            $appel_donnees_eleves = mysql_query("SELECT DISTINCT e.* FROM eleves e, j_eleves_classes c
            WHERE (
            c.id_classe='$id_classe' AND
            c.login = e.login AND
            c.periode = '".$periode_num."'

            ) ORDER BY nom,prenom");
        } else {
            $appel_donnees_eleves = mysql_query("SELECT DISTINCT e.* FROM eleves e, j_eleves_classes c, j_eleves_professeurs p
            WHERE (c.id_classe='$id_classe' AND
            c.login = e.login AND
            p.login = c.login AND
            p.professeur = '".$_SESSION['login']."' AND
            c.periode = '".$periode_num."'
            ) ORDER BY nom,prenom");
        }
        $nb_eleve = mysql_num_rows($appel_donnees_eleves);
        $current_eleve_login = @mysql_result($appel_donnees_eleves, $ind_eleve_login_suiv, "login");
        $ind_eleve_login_suiv++;
        if ($ind_eleve_login_suiv >= $nb_eleve)  $ind_eleve_login_suiv = 0;

        header("Location: saisie_ects.php?periode_num=$periode_num&id_classe=$id_classe&current_eleve_login=$current_eleve_login&ind_eleve_login_suiv=$ind_eleve_login_suiv&fiche=y&msg=$msg&affiche_message=$affiche_message#app");
    }
}
//*******************************************************************************************************
$message_enregistrement = "Les modifications ont �t� enregistr�es !";
$themessage = 'Des valeurs ont �t� modifi�es. Voulez-vous vraiment quitter sans enregistrer ?';
//**************** EN-TETE *****************
$titre_page = "Saisie des ECTS";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<script type="text/javascript" language="javascript">
change = 'no';

</script>
<?php

// Premi�re �tape : la classe est d�finie, on definit la p�riode
if (isset($id_classe) and (!isset($periode_num))) {
    $classe_suivi = sql_query1("SELECT nom_complet FROM classes WHERE id = '".$id_classe."'");
    echo "<p class=bold><a href=\"saisie_ects.php\"><img src='../images/icons/back.png' alt='Retour' class='back_link' /> Mes classes</a></p>\n";
    echo "<p><b>".$classe_suivi.", choisissez la p�riode : </b></p>\n";
    include "../lib/periodes.inc.php";
    $i="1";
    echo "<ul>\n";
    while ($i < $nb_periode) {
        if ($ver_periode[$i] != "O") {
            echo "<li><a href='saisie_ects.php?id_classe=".$id_classe."&amp;periode_num=".$i."'>".ucfirst($nom_periode[$i])."</a></li>\n";
        } else {
            echo "<li>".ucfirst($nom_periode[$i])." (".$gepiClosedPeriodLabel.", �dition impossible).</li>\n";
        }
    $i++;
    }
    echo "</ul>\n";
}

// Deuxi�me �tape : la classe est d�finie, la p�riode est d�finie, on affiche la liste des �l�ves
if (isset($id_classe) and (isset($periode_num)) and (!isset($fiche))) {
    $classe_suivi = sql_query1("SELECT nom_complet FROM classes WHERE id = '".$id_classe."'");
    ?>

	<form enctype="multipart/form-data" action="saisie_ects.php" name="form1" method='post'>

    <p class=bold><a href="saisie_ects.php?id_classe=<?php echo $id_classe; ?>"><img src='../images/icons/back.png' alt='Retour' class='back_link' /> Choisir une autre p�riode</a>

	<?php

	echo "<input type='hidden' name='periode_num' value='$periode_num' />\n";

// Ajout lien classe pr�c�dente / classe suivante
if($_SESSION['statut']=='scolarite'){
	$sql = "SELECT DISTINCT c.id,c.classe FROM classes c, periodes p, j_scol_classes jsc, j_groupes_classes jgc WHERE p.id_classe = c.id  AND jsc.id_classe=c.id AND c.id = jgc.id_classe AND jgc.saisie_ects = TRUE AND jsc.login='".$_SESSION['login']."' ORDER BY classe";
}
elseif($_SESSION['statut']=='professeur'){

	// On a filtr� plus haut les profs qui n'ont pas getSettingValue("GepiRubConseilProf")=='yes'
	$sql="SELECT DISTINCT c.id,c.classe FROM classes c,
										j_eleves_classes jec,
										j_eleves_professeurs jep,
                                        j_groupes_classes
								WHERE jec.id_classe=c.id AND
                                        c.id = jgc.id_classe AND
                                        jgc.saisie_ects = TRUE AND
										jep.login=jec.login AND
										jep.professeur='".$_SESSION['login']."'
								ORDER BY c.classe;";
}
elseif($_SESSION['statut'] == 'autre'){
	// On recherche toutes les classes pour ce statut qui n'est accessible que si l'admin a donn� les bons droits
	$sql="SELECT DISTINCT c.* FROM classes c, periodes p, j_groupes_classes jgc WHERE p.id_classe = c.id AND c.id = jgc.id_classe AND jgc.saisie_ects = TRUE  ORDER BY classe";
}
elseif($_SESSION['statut'] == 'secours'){
	$sql="SELECT DISTINCT c.* FROM classes c, periodes p, j_groupes_classes jgc WHERE p.id_classe = c.id AND c.id = jgc.id_classe AND jgc.saisie_ects = TRUE  ORDER BY classe";
}

$chaine_options_classes="";

$cpt_classe=0;
$num_classe=-1;

$res_class_tmp=mysql_query($sql);
$nb_classes_suivies=mysql_num_rows($res_class_tmp);
if($nb_classes_suivies>0){
	$id_class_prec=0;
	$id_class_suiv=0;
	$temoin_tmp=0;
	while($lig_class_tmp=mysql_fetch_object($res_class_tmp)){
		if($lig_class_tmp->id==$id_classe){
			// Index de la classe dans les <option>
			$num_classe=$cpt_classe;

			$chaine_options_classes.="<option value='$lig_class_tmp->id' selected='true'>$lig_class_tmp->classe</option>\n";
			$temoin_tmp=1;
			if($lig_class_tmp=mysql_fetch_object($res_class_tmp)){
				$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
				$id_class_suiv=$lig_class_tmp->id;
			}
			else{
				$id_class_suiv=0;
			}
		}
		else {
			$chaine_options_classes.="<option value='$lig_class_tmp->id'>$lig_class_tmp->classe</option>\n";
		}
		if($temoin_tmp==0){
			$id_class_prec=$lig_class_tmp->id;
		}

		$cpt_classe++;

	}
}

// =================================
if (isset($id_class_prec) && $id_class_prec!=0) {
	echo " | <a href='".$_SERVER['PHP_SELF']."?id_classe=$id_class_prec&amp;periode_num=$periode_num' onclick=\"return confirm_abandon (this, change, '$themessage')\">Classe pr�c�dente</a>";
}

if(($chaine_options_classes!="")&&($nb_classes_suivies>1)) {

	echo "<script type='text/javascript'>
	// Initialisation
	change='no';

	function confirm_changement_classe(thechange, themessage)
	{
		if (!(thechange)) thechange='no';
		if (thechange != 'yes') {
			document.form1.submit();
		}
		else{
			var is_confirmed = confirm(themessage);
			if(is_confirmed){
				document.form1.submit();
			}
			else{
				document.getElementById('id_classe').selectedIndex=$num_classe;
			}
		}
	}
</script>\n";

	//echo " | <select name='id_classe' onchange=\"document.forms['form1'].submit();\">\n";
	echo " | <select name='id_classe' id='id_classe' onchange=\"confirm_changement_classe(change, '$themessage');\">\n";
	echo $chaine_options_classes;
	echo "</select>\n";
}

if(isset($id_class_suiv)){
	if($id_class_suiv!=0){echo " | <a href='".$_SERVER['PHP_SELF']."?id_classe=$id_class_suiv&amp;periode_num=$periode_num' onclick=\"return confirm_abandon (this, change, '$themessage')\">Classe suivante</a>";}
}
//fin ajout lien classe pr�c�dente / classe suivante
echo "</p>\n";

echo "</form>\n";

	?>

    <p class='grand'>Classe : <?php echo $classe_suivi; ?></p>

    <p>Cliquez sur le nom de l'�l�ve pour lequel vous voulez entrer ou modifier l'appr�ciation.</p>
    <?php
    if (($_SESSION['statut'] == 'scolarite') or ($_SESSION['statut'] == 'secours')) {
        $sql="SELECT DISTINCT e.* FROM eleves e, j_eleves_classes c
        WHERE (c.id_classe='$id_classe' AND
           c.login = e.login AND
           c.periode = '".$periode_num."'
           ) ORDER BY nom,prenom";
    } else {
        $sql="SELECT DISTINCT e.* FROM eleves e, j_eleves_classes c, j_eleves_professeurs p
        WHERE (c.id_classe='$id_classe' AND
           c.login = e.login AND
           p.login = c.login AND
           p.professeur = '".$_SESSION['login']."' AND
           c.periode = '".$periode_num."'
           ) ORDER BY nom,prenom";
    }

	$appel_donnees_eleves = mysql_query($sql);
    $nombre_lignes = mysql_num_rows($appel_donnees_eleves);
    $i = "0";
	$alt=1;
    while($i < $nombre_lignes) {
        $current_eleve_login = mysql_result($appel_donnees_eleves, $i, "login");
        $ind_eleve_login_suiv = 0;
        if ($i < $nombre_lignes-1) $ind_eleve_login_suiv = $i+1;
        $current_eleve_nom = mysql_result($appel_donnees_eleves, $i, "nom");
        $current_eleve_prenom = mysql_result($appel_donnees_eleves, $i, "prenom");
		$alt=$alt*(-1);
        echo "<a href = 'saisie_ects.php?periode_num=$periode_num&amp;id_classe=$id_classe&amp;fiche=y&amp;current_eleve_login=$current_eleve_login&amp;ind_eleve_login_suiv=$ind_eleve_login_suiv#app'>$current_eleve_nom $current_eleve_prenom</a><br/>\n";
        $i++;
    }
}


if (isset($fiche)) {

?>
<script type="text/javascript"><!--
function updatesum() {
 $('total_ects').value = 0;
 $$('input.valeur').each(function(a){
     $('total_ects').value = (($('total_ects').value-0) + (a.value-0));
 })
}
//--></script>


<?

    $Eleve = ElevePeer::retrieveByLOGIN($current_eleve_login);
    echo "<br/>";
	echo "<form enctype=\"multipart/form-data\" name='ects_form' id='ects_form' action=\"saisie_ects.php\" method=\"post\">\n";
	echo "<table class='boireaus' summary=\"El�ve ".$Eleve->getLogin()."\">\n";
	echo "<tr>\n";
	echo "<td colspan='3' class='bull_simple'>\n";
    echo "<span class='bull_simpl'><span class='bold'>".$Eleve->getNom()." ".$Eleve->getPrenom()."</span>";
	echo "</td>\n";
    echo "</tr>";
    echo "<tr><td>Enseignements</td><td>Cr�dits obtenus</td><td>Mention (de A � F)</td></tr>";


    $groupes = $Eleve->getEctsGroupes($periode_num);
   
    $total_valeur = 0;

    foreach($groupes as $group) {
        echo "<tr>";
        echo "<td class='bull_simple'>";
        // Information sur la mati�re
        echo "<p><b>".$group->getDescription()."</b><br/>";
        foreach($group->getProfesseurs() as $prof) {
        	echo "<i>".affiche_utilisateur($prof->getLogin(),$id_classe)."</i><br/>";
		}
        echo "</p></td>";
        $CreditEcts = $Eleve->getEctsCredit($periode_num,$group->getId());
        echo "<td class='bull_simple'>";
        $valeur_ects = $CreditEcts == null ? '' : $CreditEcts->getValeur();
        echo "<input type='text' class='valeur' style='width: 40px;' name='valeur_ects_".$group->getId()."' value='$valeur_ects' onblur='updatesum();'>";
        echo "</td>";
        echo "<td class='bull_simple'>";
        $mention_ects = $CreditEcts == null ? '' : $CreditEcts->getMention();
        echo "<input type='text' style='width: 40px;' name='mention_ects_".$group->getId()."' value='$mention_ects'>";
        echo "</td>";
        echo "</tr>";
        $total_valeur += $valeur_ects;
    }

    echo "<tr><td>Total :</td><td><input id='total_ects' name='total_ects' readonly style='width: 40px;' value='$total_valeur'/></td><td></td></tr>";
    ?>
    </table>
    <input type=hidden name=id_classe value=<?php echo "$id_classe";?> />
    <input type=hidden name=is_posted value="yes" />
    <input type=hidden name=periode_num value="<?php echo "$periode_num";?>" />
    <input type=hidden name=current_eleve_login value="<?php echo "$current_eleve_login";?>" />
    <input type=hidden name=ind_eleve_login_suiv value="<?php echo "$ind_eleve_login_suiv";?>" />
    <!--br /-->
    <br/>
	<input type="submit" NAME="ok1" value="Enregistrer et passer � l'�l�ve suivant" />
    <input type="submit" NAME="ok2" value="Enregistrer et revenir � la liste" /><br /><br />&nbsp;

    </form>
    <?php

}

//**********************************************************************************************************
require("../lib/footer.inc.php");
?>
