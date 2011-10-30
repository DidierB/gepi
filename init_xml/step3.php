<?php
@set_time_limit(0);
/*
 * $Id$
 *
 * Copyright 2001, 2007 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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

check_token();

// MODIF A FAIRE:
// ALTER TABLE `eleves` ADD `ele_id` VARCHAR( 10 ) NOT NULL ;


//**************** EN-TETE *****************
$titre_page = "Outil d'initialisation de l'ann�e : Importation des �l�ves - Etape 3";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

//debug_var();
// Passer � 'y' pour afficher les requ�tes
$debug_ele="n";

//==================================
// RNE de l'�tablissement pour comparer avec le RNE de l'�tablissement de l'ann�e pr�c�dente
$gepiSchoolRne=getSettingValue("gepiSchoolRne") ? getSettingValue("gepiSchoolRne") : "";
//==================================

echo "<center><h3 class='gepi'>Premi�re phase d'initialisation<br />Importation des �l�ves,  constitution des classes et affectation des �l�ves dans les classes</h3></center>";
echo "<center><h3 class='gepi'>Troisi�me �tape : Enregistrement des �l�ves et affectation des �l�ves dans les classes</h3></center>";

if (isset($is_posted) and ($is_posted == "yes")) {
    $call_data = mysql_query("SELECT ID_TEMPO,ELENOM,ELEPRE,ELENOET,ELE_ID,ELESEXE,ELEDATNAIS,ELEDOUBL,ELENONAT,ELEREG,DIVCOD,ETOCOD_EP FROM temp_gep_import2 ORDER BY DIVCOD,ELENOM,ELEPRE");
    $nb = mysql_num_rows($call_data);
    $i = "0";
    while ($i < $nb) {
        //$req = mysql_query("select col2 from tempo2 where col1 = '$i'");
        //$reg_login = @mysql_result($req, 0, 'col2');

		if($debug_ele=='y') {echo "<br /><br />";}

        $id_tempo = @mysql_result($call_data, $i, "ID_TEMPO");

        $req = mysql_query("select col2 from tempo2 where col1 = '$id_tempo'");
        $reg_login = @mysql_result($req, 0, 'col2');

        $no_gep = @mysql_result($call_data, $i, "ELENONAT");
        $reg_nom = traitement_magic_quotes(corriger_caracteres(@mysql_result($call_data, $i, "ELENOM")));
        $reg_prenom = @mysql_result($call_data, $i, "ELEPRE");
        $reg_elenoet = @mysql_result($call_data, $i, "ELENOET");
        //$reg_ereno = @mysql_result($call_data, $i, "ERENO");
        $reg_ele_id = @mysql_result($call_data, $i, "ELE_ID");
        $reg_sexe = @mysql_result($call_data, $i, "ELESEXE");
        $reg_naissance = @mysql_result($call_data, $i, "ELEDATNAIS");
        $reg_doublant = @mysql_result($call_data, $i, "ELEDOUBL");
        $reg_classe = @mysql_result($call_data, $i, "DIVCOD");
        $reg_etab = @mysql_result($call_data, $i, "ETOCOD_EP");
        $tab_prenom = explode(" ",$reg_prenom);
        $reg_prenom = traitement_magic_quotes(corriger_caracteres($tab_prenom[0]));
        $reg_regime = mysql_result($call_data, $i, "ELEREG");
        if (($reg_sexe != "M") and ($reg_sexe != "F")) {$reg_sexe = "M";}
        if ($reg_naissance == '') {$reg_naissance = "19000101";}

        $maj_tempo = mysql_query("UPDATE temp_gep_import2 SET LOGIN='$reg_login' WHERE ID_TEMPO='$id_tempo'");

		$sql="INSERT INTO eleves SET no_gep='$no_gep',login='$reg_login',nom='$reg_nom',prenom='$reg_prenom',sexe='$reg_sexe',naissance='$reg_naissance',elenoet='$reg_elenoet',ele_id='$reg_ele_id'";
		if($debug_ele=='y') {echo "<span style='color:green;'>$sql</span><br />";}
        $reg_eleve = mysql_query($sql);
        if (!$reg_eleve) {
				echo "<p style='color:red'>Erreur lors de l'enregistrement de l'�l�ve $reg_nom $reg_prenom.</p>";
		}
		else {
			$sql="SELECT * FROM tempo_utilisateurs WHERE identifiant1='".$reg_ele_id."' AND statut='eleve';";
			if($debug_ele=='y') {echo "<span style='color:green;'>$sql</span><br />";}
			$res_tmp_u=mysql_query($sql);
			if(mysql_num_rows($res_tmp_u)>0) {
				$lig_tmp_u=mysql_fetch_object($res_tmp_u);

				$sql="INSERT INTO utilisateurs SET login='".$lig_tmp_u->login."', nom='".$reg_nom."', prenom='".$reg_prenom."', ";
				if($reg_sexe=='M') {
					$sql.="civilite='M', ";
				}
				else {
					$sql.="civilite='MLLE', ";
				}
				$sql.="password='".$lig_tmp_u->password."', salt='".$lig_tmp_u->salt."', email='".$lig_tmp_u->email."', statut='eleve', etat='inactif', change_mdp='n', auth_mode='".$lig_tmp_u->auth_mode."';";
				if($debug_ele=='y') {echo "<span style='color:blue;'>$sql</span><br />";}
				$insert_u=mysql_query($sql);
				if(!$insert_u) {
					echo "<span style='color:red'>Erreur lors de la re-cr�ation du compte utilisateur pour ".$reg_nom." ".$reg_prenom.".</span><br />\n";
				}
			}
		}

		$regime=traite_regime_sconet($reg_regime);
		if("$regime"=="ERR") {$regime="d/p";}
		//=========================

        if ($reg_doublant == "O") {$doublant = 'R';}
        if ($reg_doublant != "O") {$doublant = '-';}

        $register = mysql_query("INSERT INTO j_eleves_regime SET login='$reg_login',regime='$regime',doublant='$doublant'");
        if (!$register) {echo "<p style='color:red'>Erreur lors de l'enregistrement des infos de r�gime pour l'�l�ve $reg_nom $reg_prenom.";}

        $call_classes = mysql_query("SELECT * FROM classes");
        $nb_classes = mysql_num_rows($call_classes);
        $j = 0;
        while ($j < $nb_classes) {
            $classe = mysql_result($call_classes, $j, "classe");
            if ($reg_classe == $classe) {
                $id_classe = mysql_result($call_classes, $j, "id");
                $number_periodes = mysql_result(mysql_query("SELECT count(*) FROM periodes WHERE id_classe='$id_classe'"),0);
                $u = 1;
                while ($u <= $number_periodes) {
                    $reg = mysql_query("INSERT INTO j_eleves_classes SET login='$reg_login',id_classe='$id_classe',periode='$u', rang='0'");
                    if (!$reg) {echo "<p style='color:red'>Erreur lors de l'enregistrement de l'appartenance de l'�l�ve $reg_nom $reg_prenom � la classe $classe pour la p�riode $u";}
                    $u++;
                }
            }
            $j++;
        }

        if (($reg_etab != '')&&($reg_elenoet != '')) {

			if($gepiSchoolRne!="") {
				if($gepiSchoolRne!=$reg_etab) {
					$sql="SELECT 1=1 FROM j_eleves_etablissements WHERE id_eleve='$reg_elenoet';";
					$test_etab=mysql_query($sql);
					if(mysql_num_rows($test_etab)==0){
						$sql="INSERT INTO j_eleves_etablissements SET id_eleve='$reg_elenoet', id_etablissement='$reg_etab';";
						$insert_etab=mysql_query($sql);
						if (!$insert_etab) {
							echo "<p style='color:red'>Erreur lors de l'enregistrement de l'appartenance de l'�l�ve $reg_nom $reg_prenom � l'�tablissement $reg_etab.</p>\n";
						}
					}
					else {
						$sql="UPDATE j_eleves_etablissements SET id_etablissement='$reg_etab' WHERE id_eleve='$reg_elenoet';";
						$update_etab=mysql_query($sql);
						if (!$update_etab) {
							echo "<p style='color:red'>Erreur lors de l'enregistrement de l'appartenance de l'�l�ve $reg_nom $reg_prenom � l'�tablissement $reg_etab.</p>\n";
						}
					}
				}
			}
			else {
				// Si le RNE de l'�tablissement courant (celui du GEPI) n'est pas renseign�, on ins�re les nouveaux enregistrements, mais on ne met pas � jour au risque d'�craser un enregistrement correct avec l'info que l'�l�ve de 1�re �tait en 2nde dans le m�me �tablissement.
				// Il suffira de faire un
				//       DELETE FROM j_eleves_etablissements WHERE id_etablissement='$gepiSchoolRne';
				// une fois le RNE renseign�.
				$sql="SELECT 1=1 FROM j_eleves_etablissements WHERE id_eleve='$reg_elenoet';";
				$test_etab=mysql_query($sql);
				if(mysql_num_rows($test_etab)==0){
					$sql="INSERT INTO j_eleves_etablissements SET id_eleve='$reg_elenoet', id_etablissement='$reg_etab';";
					$insert_etab=mysql_query($sql);
					if (!$insert_etab) {
						echo "<p style='color:red'>Erreur lors de l'enregistrement de l'appartenance de l'�l�ve $reg_nom $reg_prenom � l'�tablissement $reg_etab.</p>\n";
					}
				}
			}

        }

        $i++;
    }
    // on vide la table tempo2 qui nous a servi � stocker les login temporaires des �l�ves
    $del = @mysql_query("DELETE FROM tempo2");

	// On renseigne le t�moin: La mise � jour � partir de sconet sera possible.
	saveSetting("import_maj_xml_sconet", 1);


    //echo "<p>L'importation des donn�es de <b>GEP</b> concernant la constitution des classes est termin�e.</p>";
    echo "<p>L'importation des donn�es concernant la constitution des classes est termin�e.</p>";
    echo "<p align='center'><a href='responsables.php'>Proc�der � la deuxi�me phase d'importation des responsables</a></p>";
	echo "<p><br /></p>\n";
    require("../lib/footer.inc.php");
	die();
}
else {
    // on vide la table tempo2 qui va nous servir � stocker les login temporaires des �l�ves
    $del = @mysql_query("DELETE FROM tempo2");

	//if(getSettingValue('use_sso')=="lcs") {
	if(getSettingValue('auth_sso')=="lcs") {
		// On va r�cup�rer les logins du LCS
		require_once("../lib/lcs.inc.php");
		$ds = connect_ldap($lcs_ldap_host,$lcs_ldap_port,"","");
	}

    //$call_data = mysql_query("SELECT ELENOM,ELEPRE,ELENOET,ELE_ID,ELESEXE,ELEDATNAIS,ELEDOUBL,ELENONAT,ELEREG,DIVCOD,ETOCOD_EP FROM temp_gep_import2 ORDER BY DIVCOD,ELENOM,ELEPRE");
    //$nb = mysql_num_rows($call_data);
    //$i = "0";

    $tab_sql=array();
	$tab_sql[]="SELECT ID_TEMPO,ELENOM,ELEPRE,ELENOET,ELE_ID,ELESEXE,ELEDATNAIS,ELEDOUBL,ELENONAT,ELEREG,DIVCOD,ETOCOD_EP FROM temp_gep_import2 t, tempo_utilisateurs tu WHERE t.ELE_ID=tu.identifiant1 ORDER BY DIVCOD,ELENOM,ELEPRE;";
	$tab_sql[]="SELECT ID_TEMPO,ELENOM,ELEPRE,ELENOET,ELE_ID,ELESEXE,ELEDATNAIS,ELEDOUBL,ELENONAT,ELEREG,DIVCOD,ETOCOD_EP FROM temp_gep_import2 WHERE ELE_ID NOT IN (SELECT identifiant1 FROM tempo_utilisateurs) ORDER BY DIVCOD,ELENOM,ELEPRE;";

    echo "<p>Le tableau suivant affiche les donn�es qui vont �tre enregistr�es dans la base de donn�e GEPI lorsque vous aurez confirm� ce choix tout en bas de la page.<br /><b>Tant que vous n'avez pas valid� en bas de la page, aucune donn�e n'est enregistr�e !</b></p>";

    echo "<p>Les valeurs en rouge signalent d'�ventuelles donn�es manquantes (ND pour \"non d�fini\") dans le fichier <b>eleves.csv</b> fourni ! Ceci n'est pas g�nant pour l'enregistrement dans la base <b>GEPI</b>. Vous aurez en effet la possibilit� de compl�ter les donn�es manquantes avec les outils fournis dans <b>GEPI</b></p>";
    echo "<p>Une fois cette page enti�rement charg�e, ce qui peut prendre un peu de temps, <b>veuillez lire attentivement les remarques en bas de la page </b>avant de proc�der � l'enregistrement d�finitif des donn�es</p>\n";
    echo "<table class='boireaus' border=1 cellpadding=2 cellspacing=2>";
    echo "<tr><th><p class=\"small\">N� INE</p></th><th><p class=\"small\">Identifiant</p></th><th><p class=\"small\">Nom</p></th><th><p class=\"small\">Pr�nom</p></th><th><p class=\"small\">Sexe</p></th><th><p class=\"small\">Date de naiss.</p></th><th><p class=\"small\">R�gime</p></th><th><p class=\"small\">Doublant</p></th><th><p class=\"small\">Classe</p></th><th><p class=\"small\">Etablissement d'origine ou pr�c�dent</p></th></tr>\n";

	$alt=1;
    $max_lignes_pb = 0;

    $ii = "0";

	for($loop=0;$loop<count($tab_sql);$loop++) {
		$call_data = mysql_query($tab_sql[$loop]);
		if($debug_ele=='y') {
			echo "<tr>\n";
			echo "<td colspan='10'>\n";
			echo $tab_sql[$loop];
			echo "</td>\n";
			echo "</tr>\n";
		}
		$nb = mysql_num_rows($call_data);
	    $i = "0";

		while ($i < $nb) {
			if($debug_ele=='y') {echo "<br /><br />";}

			$lcs_eleve_en_erreur="n";

			$alt=$alt*(-1);
			$ligne_pb = 'no';
			$id_tempo = mysql_result($call_data, $i, "ID_TEMPO");
			$no_gep = mysql_result($call_data, $i, "ELENONAT");
			$reg_nom = mysql_result($call_data, $i, "ELENOM");
			$reg_prenom = mysql_result($call_data, $i, "ELEPRE");
			$reg_elenoet = mysql_result($call_data, $i, "ELENOET");
			//$reg_ereno = mysql_result($call_data, $i, "ERENO");
			$reg_ele_id = mysql_result($call_data, $i, "ELE_ID");
			$reg_sexe = mysql_result($call_data, $i, "ELESEXE");
			$reg_naissance = mysql_result($call_data, $i, "ELEDATNAIS");
			$reg_doublant = mysql_result($call_data, $i, "ELEDOUBL");
			$reg_classe = mysql_result($call_data, $i, "DIVCOD");
			$reg_etab = mysql_result($call_data, $i, "ETOCOD_EP");
			$tab_prenom = explode(" ",$reg_prenom);
			$reg_prenom = $tab_prenom[0];
			$reg_regime = mysql_result($call_data, $i, "ELEREG");
			if ($no_gep != '') {
				$no_gep_aff = $no_gep;
			} else {
				$no_gep_aff = "<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
	
			// On teste pour savoir s'il faut cr�er un login
			$nouv_login='no';
			if ($no_gep != '') {
					$nouv_login = 'yes';
			}
			// S'il s'agit d'un �l�ve ne figurant pas d�j� dans une des bases �l�ve des ann�es pass�es,
			// on cr�e un login !
	
			if (($no_gep == '') or ($nouv_login=='yes')) {
				$login_eleve="";

				$reg_nom = strtr($reg_nom,"������������������������������","aaaeeeeiioouuucAAAEEEEIIOOUUUC");
				$reg_prenom = strtr($reg_prenom,"������������������������������","aaaeeeeiioouuucAAAEEEEIIOOUUUC");
				/*
				$temp1 = strtoupper($reg_nom);
				$temp1 = preg_replace('/[^0-9a-zA-Z_]/',"", $temp1);
				$temp1 = strtr($temp1, " '-", "___");
				$temp1 = substr($temp1,0,7);
				$temp2 = strtoupper($reg_prenom);
				$temp2 = preg_replace('/[^0-9a-zA-Z_]/',"", $temp2);
				$temp2 = strtr($temp2, " '-", "___");
				$temp2 = substr($temp2,0,1);
				$login_eleve = $temp1.'_'.$temp2;
				*/

				if($reg_ele_id!='') {
					$sql="SELECT * FROM tempo_utilisateurs WHERE identifiant1='".$reg_ele_id."' AND statut='eleve';";
					if($debug_ele=='y') {echo "<span style='color:green;'>$sql</span><br />";}
					$res_tmp_u=mysql_query($sql);
					if(mysql_num_rows($res_tmp_u)>0) {
						$lig_tmp_u=mysql_fetch_object($res_tmp_u);
						$login_eleve=$lig_tmp_u->login;
						if($debug_ele=='y') {echo "<span style='color:green;'>Login r�cup�r� de la table 'tempo_utilisateurs' : '$login_eleve'</span><br />";}
					}
				}
	
				if($login_eleve=="") {
					$login_ele_gen_type=getSettingValue('login_ele_gen_type');
					if($login_ele_gen_type=='') {$login_ele_gen_type='name9_p';}
					$login_eleve=generate_unique_login($reg_nom, $reg_prenom, 'name9_p', 'maj');
					if($debug_ele=='y') {echo "<span style='color:blue;'>Login nouvellement g�n�r� pour '$reg_nom $reg_prenom' : '$login_eleve'</span><br />";}
				}
	
				// Dans le cas o� Gepi est int�gr� � un ENT, il ne doit pas g�n�rer de login mais r�cup�rer celui qui existe d�j�
				if (getSettingValue("use_ent") == 'y') {
					// On a r�cup�r� les informations dans la table ldap_bx
					// voir aussi les explications de la ligne 710 du fichiers professeurs.php
					$sql_p = "SELECT login_u FROM ldap_bx
											WHERE identite_u = '".$no_gep."'";
					$query_p = mysql_query($sql_p);
					$nbre = mysql_num_rows($query_p);
					if ($nbre >= 1) {
						// On consid�re que l'information est bonne puisqu'elle a �t� construite avec la m�me source sconet
						$login_eleve = mysql_result($query_p, 0,"login_u");
					}else{
						// Il faudra trouver une solution dans ce cas l� (m�me s'il ne doit pas �tre tr�s fr�quent
						//$login_eleve = "erreur_".$i;
						$login_eleve = "erreur_".$id_tempo;
					}
					if($debug_ele=='y') {echo "<span style='color:lime;'>Login r�cup�r� de ldap_bx : $login_eleve</span><br />";}
				}
				//echo "Avant auth_sso<br />";
				//if(getSettingValue('use_sso')=="lcs") {
				if(getSettingValue('auth_sso')=="lcs") {
					$lcs_eleve_en_erreur="y";
					if($reg_elenoet!='') {
						$login_eleve=get_lcs_login($reg_elenoet, 'eleve');
						//echo "get_lcs_login($reg_elenoet, 'eleve')=".$login_eleve."<br />";
						if($login_eleve!='') {
							$test_tempo2 = mysql_num_rows(mysql_query("SELECT col2 FROM tempo2 WHERE (col2='$login_eleve' or col2='".strtoupper($login_eleve)."')"));
							if ($test_tempo2 != "0") {
								$ligne_pb = 'yes';

								if($debug_ele=='y') {echo "<span style='color:red;'>PROBLEME avec le login r�cup�r� du LDAP LCS ($login_eleve) : d�j� pr�sent dans la table 'tempo2'</span><br />";}
							} else {
								//$reg = mysql_query("INSERT INTO tempo2 VALUES ('$i', '$login_eleve')");
								$reg = mysql_query("INSERT INTO tempo2 VALUES ('$id_tempo', '$login_eleve')");
								//return 'yes';
								$lcs_eleve_en_erreur="n";

								if($debug_ele=='y') {echo "<span style='color:orange;'>Login r�cup�r� sans probl�me du LDAP LCS : $login_eleve</span><br />";}

							}
						}
						else {
							$ligne_pb = 'yes';
						}
					}
					else {
						$ligne_pb = 'yes';
					}
				}
				else {
					// On teste l'unicit� du login que l'on vient de cr�er
					$k = 2;
					$test_unicite = 'no';
					$temp = $login_eleve;
					while ($test_unicite != 'yes') {
						$test_unicite = test_unique_e_login($login_eleve,$id_tempo);
						if ($test_unicite != 'yes') {
							$login_eleve = $temp.$k;
							$k++;
						}
					}
					if($debug_ele=='y') {echo "<span style='color:coral;'>Login apr�s contr�le d'unicit� : $login_eleve</span><br />";}
				}
			}
	
			if ($reg_nom != '') {
				$reg_nom_aff = $reg_nom;
			} else {
				$reg_nom_aff = "<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
			if ($reg_prenom != '') {
				$reg_prenom_aff = $reg_prenom;
			} else {
				$reg_prenom_aff = "<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
			if (($reg_sexe == "M") or ($reg_sexe == "F")) {
				$reg_sexe_aff = $reg_sexe;
			} else {
				$reg_sexe_aff = "<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
			if ($reg_naissance != '') {
				$eleve_naissance_annee = substr($reg_naissance, 0, 4);
				$eleve_naissance_mois = substr($reg_naissance, 4, 2);
				$eleve_naissance_jour = substr($reg_naissance, 6, 2);
				$naissance = $eleve_naissance_jour."/".$eleve_naissance_mois."/".$eleve_naissance_annee;
			} else {
				$naissance = 'non d�finie';
			}

			$reg_regime_aff=traite_regime_sconet($reg_regime);
			if($reg_regime_aff=="ERR"){
				$reg_regime_aff="<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
			//=========================
	
			if ($reg_doublant == "N") {
				$reg_doublant_aff = "N";
			} else if ($reg_doublant == "O") {
				$reg_doublant_aff = "O";
			} else {
				$reg_doublant_aff = "<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
	
			$call_classes = mysql_query("SELECT * FROM classes");
			$nb_classes = mysql_num_rows($call_classes);
			$j = 0;
			$classe_error = 'yes';
			while ($j < $nb_classes) {
				$classe = mysql_result($call_classes, $j, "classe");
				if ($reg_classe == $classe) {
					$classe_aff = $classe;
					$classe_error = 'no';
				}
				$j++;
			}
			if ($classe_error == 'yes') {
				$classe_aff = "<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
			if ($reg_etab != '') {
				$calletab = mysql_query("SELECT * FROM etablissements WHERE (id = '$reg_etab')");
				$result_etab = mysql_num_rows($calletab);
				if ($result_etab != 0) {
					$etab_nom = @mysql_result($calletab, 0, "nom");
					$etab_cp = @mysql_result($calletab, 0, "cp");
					$etab_ville = @mysql_result($calletab, 0, "ville");
					$reg_etab_aff = "$etab_nom, $etab_cp $etab_ville";
				} else {
					$reg_etab_aff = "<font color = 'red'>RNE : $reg_etab, �tab. non r�pertori�</font>";
					$ligne_pb = 'yes';
				}
			} else {
				$reg_etab_aff = "<font color = 'red'>ND</font>";
				$ligne_pb = 'yes';
			}
			if (!isset($affiche)) $affiche = 'tout';
			// On affiche la ligne du tableau
			if (($affiche != 'partiel') or (($affiche == 'partiel') and ($ligne_pb == 'yes'))) {
				echo "<tr class='lig$alt white_hover'><td><p class=\"small\">$no_gep_aff</p></td>";
				echo "<td><p class=\"small\">$login_eleve</p></td>";
				echo "<td><p class=\"small\">$reg_nom_aff</p></td>";
				echo "<td><p class=\"small\">$reg_prenom_aff</p></td>";
				echo "<td><p class=\"small\">$reg_sexe_aff</p></td>";
				echo "<td><p class=\"small\">$naissance</p></td>";
				echo "<td><p class=\"small\">$reg_regime_aff</p></td>";
				echo "<td><p class=\"small\">$reg_doublant_aff</p></td>";
				echo "<td><p class=\"small\">$classe_aff</p></td>";
				echo "<td><p class=\"small\">$reg_etab_aff</p></td></tr>";
			}
	
			// Si la ligne comportait un probl�me, on incr�mente max_lignes_pb
			if ($ligne_pb == 'yes') {
				$max_lignes_pb++;
			}
			$i++;
			$ii++;	
		}
	}
    echo "</table>";
    //echo "<p><b>Nombre total de lignes : $nb</b><br />\n";
    echo "<p><b>Nombre total de lignes : $ii</b><br />\n";
    if ($max_lignes_pb == 0) {
        echo "Aucune erreur n'a �t� d�tect�e !</p>";
    } else {
        echo "Des donn�es manquantes ou incompl�tes ont �t� d�tect�es dans <b>$max_lignes_pb lignes</b> : Elles apparaissent dans le tableau ci-dessus en rouge !";
        if ($affiche != 'partiel') {
            echo "<p>--> Pour n'afficher que les lignes ou des probl�mes ont �t� d�tect�s, cliquez sur le bouton \"Affichage partiel\" :</p>";
            echo "<form enctype='multipart/form-data' action='step3.php' method=post>";
			echo add_token_field();
            echo "<input type=hidden name='is_posted' value='no' />";
            echo "<input type=hidden name='affiche' value='partiel' />";
            echo "<center><input type='submit' value='Affichage partiel' /></center>";
            echo "</form>";
        } else {
            echo "<p>--> Pour afficher toutes les lignes, cliquez sur le bouton \"Afficher tout\" :</p>";
            echo "<form enctype='multipart/form-data' action='step3.php' method=post>";
			echo add_token_field();
            echo "<input type=hidden name='is_posted' value='no' />";
            echo "<input type=hidden name='affiche' value='tout' />";
            echo "<center><input type='submit' value='Afficher tout' /></center>";
            echo "</form>";
        }
    }

    if (getSettingValue("use_ent") == 'y') {
    	// Dans le cas d'un ent on renvoie l'admin pour qu'il v�rifie tous les logins de la forme erreur_xx
    	echo '
			<p>--&gt; Avant d\'enregistrer, vous allez v�rifier tous les logins potentiellement erron�s.</p>
			<p><a href="../mod_ent/gestion_ent_eleves.php">V�rifier les logins</a></p>
		';
    } else {
	    echo "<p>--&gt; Pour Enregistrer toutes les donn�es dans la base <b>GEPI</b>, cliquez sur le bouton \"Enregistrer\" !</p>\n";
    	echo "<form enctype='multipart/form-data' action='step3.php' method='post'>\n";

	    //echo "<p>Si vous disposez d'un fichier ELEVE_ETABLISSEMENT.CSV, vous pouvez le fournir maintenant:<br />";
    	//echo "<input type=\"file\" size=\"80\" name=\"csv_file\" /></p>\n";

		echo add_token_field();
	    echo "<input type='hidden' name='is_posted' value='yes' />\n";
    	echo "<p style='text-align: center;'><input type='submit' value='Enregistrer' /></p>\n";
    	echo "</form>\n";
    }
    //echo "</div>";
	echo "<p><br /></p>\n";
    require("../lib/footer.inc.php");
}
?>