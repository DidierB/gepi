<?php
/*
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

// On indique qu'il faut cr�e des variables non prot�g�es (voir fonction cree_variables_non_protegees())
$variables_non_protegees = 'yes';

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

// Cr�ation du ou des nouveaux utilisateurs

// Cr�ation d'une fonction pour g�n�rer le login � partir du nom et du pr�nom
function generate_unique_login($_nom, $_prenom, $_mode) {
	
	if ($_mode == null) {
		$_mode = "fname8";
	}
    // On g�n�re le login
    $_prenom = strtr($_prenom, "������������������", "eeeeEEEEuuUUiIaaAA");
    $_prenom = preg_replace("/[^a-zA-Z.\-]/", "", $_prenom);
    $_nom = strtr($_nom, "������������������", "eeeeEEEEuuUUiIaaAA");
    $_nom = preg_replace("/[^a-zA-Z.\-]/", "", $_nom);
    if ($_mode == "name") {
            $temp1 = $_nom;
            $temp1 = strtoupper($temp1);
            $temp1 = ereg_replace(" ","", $temp1);
            $temp1 = ereg_replace("-","_", $temp1);
            $temp1 = ereg_replace("'","", $temp1);
            //$temp1 = substr($temp1,0,8);
        } elseif ($_mode == "name8") {
            $temp1 = $_nom;
            $temp1 = strtoupper($temp1);
            $temp1 = ereg_replace(" ","", $temp1);
            $temp1 = ereg_replace("-","_", $temp1);
            $temp1 = ereg_replace("'","", $temp1);
            $temp1 = substr($temp1,0,8);
        } elseif ($_mode == "fname8") {
            $temp1 = $_prenom{0} . $_nom;
            $temp1 = strtoupper($temp1);
            $temp1 = ereg_replace(" ","", $temp1);
            $temp1 = ereg_replace("-","_", $temp1);
            $temp1 = ereg_replace("'","", $temp1);
            $temp1 = substr($temp1,0,8);
        } elseif ($_mode == "fname19") {
            $temp1 = $_prenom{0} . $_nom;
            $temp1 = strtoupper($temp1);
            $temp1 = ereg_replace(" ","", $temp1);
            $temp1 = ereg_replace("-","_", $temp1);
            $temp1 = ereg_replace("'","", $temp1);
            $temp1 = substr($temp1,0,19);
        } elseif ($_mode == "firstdotname") {

            $temp1 = $_prenom . "." . $_nom;
            $temp1 = strtoupper($temp1);

            $temp1 = ereg_replace(" ","", $temp1);
            $temp1 = ereg_replace("-","_", $temp1);
            $temp1 = ereg_replace("'","", $temp1);
            //$temp1 = substr($temp1,0,19);
        } elseif ($_mode == "firstdotname19") {
            $temp1 = $_prenom . "." . $_nom;
            $temp1 = strtoupper($temp1);
            $temp1 = ereg_replace(" ","", $temp1);
            //$temp1 = ereg_replace("-","_", $temp1);
            $temp1 = ereg_replace("'","", $temp1);
            $temp1 = substr($temp1,0,19);
        } elseif ($_mode == "namef8") {
            $temp1 =  substr($_nom,0,7) . $_prenom{0};
            $temp1 = strtoupper($temp1);
            $temp1 = ereg_replace(" ","", $temp1);
            $temp1 = ereg_replace("-","_", $temp1);
            $temp1 = ereg_replace("'","", $temp1);
            //$temp1 = substr($temp1,0,8);
        } else {
        	return false;
        }

        $login_user = $temp1;
        // On teste l'unicit� du login que l'on vient de cr�er
        $m = '';
        $test_unicite = 'no';
        while ($test_unicite != 'yes') {
            $test_unicite = test_unique_login($login_user.$m);
            if ($test_unicite != 'yes') {
            	if ($m == '') {
            		$m = 2;
            	} else {
                	$m++;
            	}
            } else {
            	$login_user = $login_user.$m;
            }
        }
        
        // Nettoyage final
        $login_user = substr($login_user, 0, 50);
        $login_user = preg_replace("/[^A-Za-z0-9._\-]/","",trim(strtoupper($login_user)));
        
        $test1 = $login_user{0};
		while ($test1 == "_" OR $test1 == "-" OR $test1 == ".") {
			$login_user = substr($login_user, 1);
			$test1 = $login_user{0};
		}        
        
		$test1 = $login_user{strlen($login_user)-1};
		while ($test1 == "_" OR $test1 == "-" OR $test1 == ".") {
			$login_user = substr($login_user, 0, strlen($login_user)-1);				
			$test1 = $login_user{strlen($login_user)-1};
		}
		
		return $login_user;
}

// Initialisation des variables
$create_mode = isset($_POST["mode"]) ? $_POST["mode"] : NULL;

if ($create_mode == "classe" OR $create_mode == "individual") {
	// On a une demeande de cr�ation, on continue
	
	// On veut alimenter la variable $quels_parents avec un r�sultat mysql qui contient
	// la liste des parents pour lesquels on veut cr�er un compte
	$error = false;
	$msg = "";
	if ($create_mode == "individual") {
		// $_POST['pers_id'] est filtr� automatiquement contre les injections SQL, on l'utilise directement
		$test = mysql_query("SELECT count(e.login) FROM eleves e, responsables2 re WHERE (e.ele_id = re.ele_id AND re.pers_id = '" . $_POST['pers_id'] ."')");
		if (mysql_result($test, 0) == "0") {
			$error = true;
			$msg .= "Erreur lors de la cr�ation de l'utilisateur : aucune association avec un �l�ve n'a �t� trouv�e !<br/>";
		} else {
			$quels_parents = mysql_query("SELECT r.* FROM resp_pers r, responsables2 re WHERE (" .
				"r.login = '' AND " .
				"r.pers_id = re.pers_id AND " .
				"re.pers_id = '" . $_POST['pers_id'] ."')");
		}
	} else {
		// On est en mode 'classe'
		if ($_POST['classe'] == "all") {
			$msg .= "Toutes les classes<br/>";
			$quels_parents = mysql_query("SELECT distinct(r.pers_id), r.nom, r.prenom, r.civilite, r.mel " .
					"FROM resp_pers r, responsables2 re, classes c, j_eleves_classes jec, eleves e WHERE (" .
					"r.login = '' AND " .
					"r.pers_id = re.pers_id AND " .
					"re.ele_id = e.ele_id AND " .
					"e.login = jec.login AND " .
					"jec.id_classe = c.id)");
			if (!$quels_parents) $msg .= mysql_error();
		} elseif (is_numeric($_POST['classe'])) {
			$quels_parents = mysql_query("SELECT distinct(r.pers_id), r.nom, r.prenom, r.civilite, r.mel " .
					"FROM resp_pers r, responsables2 re, classes c, j_eleves_classes jec, eleves e WHERE (" .
					"r.login = '' AND " .
					"r.pers_id = re.pers_id AND " .
					"re.ele_id = e.ele_id AND " .
					"e.login = jec.login AND " .
					"jec.id_classe = '" . $_POST['classe']."')");
			if (!$quels_parents) $msg .= mysql_error();
		} else {
			$error = true;
			$msg .= "Vous devez s�lectionner au moins une classe !<br/>";
		}
	}
	
	if (!$error) {
		$nb_comptes = 0;
		while ($current_parent = mysql_fetch_object($quels_parents)) {
			// Cr�ation du compte utilisateur pour le responsable consid�r�
			$reg_login = generate_unique_login($current_parent->nom, $current_parent->prenom, getSettingValue("mode_generation_login"));
			$reg = true;
			$reg = mysql_query("INSERT INTO utilisateurs SET " .
					"login = '" . $reg_login . "', " .
					"nom = '" . $current_parent->nom . "', " .
					"prenom = '". $current_parent->prenom ."', " .
					"password = '', " .
					"civilite = '" . $current_parent->civilite."', " .
					"email = '" . $current_parent->mel . "', " .
					"statut = 'responsable', " .
					"etat = 'actif', " .
					"change_mdp = 'n'");
			$reg2 = mysql_query("UPDATE resp_pers SET login = '" . $reg_login . "' WHERE (pers_id = '" . $current_parent->pers_id . "')");

			if (!$reg) {
				$msg .= "Erreur lors de la cr�ation du compte ".$reg_login."<br/>";
			} else {
				$nb_comptes++;
			}
		}
		if ($nb_comptes == 1) {
			$msg .= "Un compte a �t� cr�� avec succ�s.<br/>";
		} elseif ($nb_comptes > 1) {
			$msg .= $nb_comptes." comptes ont �t� cr��s avec succ�s.<br/>";
		}
		if ($nb_comptes > 0) {
			if ($create_mode == "individual") {
				// Mode de cr�ation de compte individuel. On fait un lien sp�cifique pour la fiche de bienvenue
				$msg .= "<br/><a target='change' href='reset_passwords.php?user_login=".$reg_login."'>";
			} else {
				// On est ici en mode de cr�ation par classe
				// Si on op�re sur toutes les classes, on ne sp�cifie aucune classe
				if ($_POST['classe'] == "all") {
					$msg .= "<br/><a target='change' href='reset_passwords.php?user_status=responsable'>";
				} elseif (is_numeric($_POST['classe'])) {
					$msg .= "<br/><a target='change' href='reset_passwords.php?user_status=responsable&user_classe=".$_POST['classe']."'>";	
				}				
			}
			$msg .= "Imprimer la ou les fiche(s) de bienvenue</a><br/>Vous devez effectuer cette op�ration maintenant !";
		}
	}
}

//**************** EN-TETE *****************
$titre_page = "Gestion des utilisateurs | Modifier un utilisateur";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************
?>
<p class=bold>
|<a href="index.php">Retour</a>|

<?php
$quels_parents = mysql_query("SELECT * FROM resp_pers WHERE login = '' ORDER BY nom,prenom");

$nb = mysql_num_rows($quels_parents);

echo "<p>Les $nb responsables ci-dessous n'ont pas encore de compte utilisateur.</p>";
if (getSettingValue("mode_generation_login") == null) {
	echo "<p><b>ATTENTION !</b> Vous n'avez pas d�fini le mode de g�n�ration des logins. Allez sur la page de <a href='../gestion/param_gen.php'>gestion g�n�rale</a> pour d�finir le mode que vous souhaitez utiliser. Par d�faut, les logins seront g�n�r�s au format pnom tronqu� � 8 caract�res (ex: ADURANT).</p>";
}
if ((getSettingValue('use_sso') == "cas" OR getSettingValue("use_sso") == "lemon"  OR getSettingValue("use_sso") == "lcs" OR getSettingValue("use_sso") == "ldap_scribe")) {
	echo "<p><b>Note :</b> Vous utilisez une authentification externe � Gepi (SSO). Aucun mot de passe ne sera donc assign� aux utilisateurs que vous vous appr�t� � cr�er. Soyez certain de g�n�rer les login selon le m�me format que pour votre source d'authentification SSO.</p>";    
}

echo "<p><b>Cr�er des comptes par lot</b> : s�lectionnez une classe ou bien l'ensemble des classes puis cliquez sur 'valider'.";
echo "<form action='create_responsable.php' method='post'>";
echo "<input type='hidden' name='mode' value='classe' />";
echo "<select name='classe' size='1'>";
echo "<option value='none'>S�lectionnez une classe</option>";
echo "<option value='all'>Toutes les classes</option>";

$quelles_classes = mysql_query("SELECT id,classe FROM classes ORDER BY classe");
while ($current_classe = mysql_fetch_object($quelles_classes)) {
	echo "<option value='".$current_classe->id."'>".$current_classe->classe."</option>"; 
}
echo "</select>";
echo "<input type='submit' name='Valider' value='Valider' />";
echo "</form>";
echo "<br/>";
echo "<p><b>Cr�er des comptes individuellement</b> : cliquez sur le bouton 'Cr�er' d'un responsable pour cr�er un compte associ�.</p>";
echo "<table>";
while ($current_parent = mysql_fetch_object($quels_parents)) {
	echo "<tr>";
		echo "<td>";
		echo "<form action='create_responsable.php' method='post'>";
		echo "<input type='hidden' name='mode' value='individual'/>";
		echo "<input type='hidden' name='pers_id' value='".$current_parent->pers_id."'/>";
		echo "<input type='submit' value='Cr�er'/>";
		echo "</form>";
		echo "<td>".$current_parent->nom." ".$current_parent->prenom."</a></td>";	
	echo "</tr>";
}
echo "</table>";

?>
</body>
</html>