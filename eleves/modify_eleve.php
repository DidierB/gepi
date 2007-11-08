<?php
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

unset($reg_login);
$reg_login = isset($_POST["reg_login"]) ? $_POST["reg_login"] : NULL;
unset($reg_nom);
$reg_nom = isset($_POST["reg_nom"]) ? $_POST["reg_nom"] : NULL;
unset($reg_prenom);
$reg_prenom = isset($_POST["reg_prenom"]) ? $_POST["reg_prenom"] : NULL;
unset($reg_email);
$reg_email = isset($_POST["reg_email"]) ? $_POST["reg_email"] : NULL;
unset($reg_sexe);
$reg_sexe = isset($_POST["reg_sexe"]) ? $_POST["reg_sexe"] : NULL;
unset($reg_no_nat);
$reg_no_nat = isset($_POST["reg_no_nat"]) ? $_POST["reg_no_nat"] : NULL;
unset($reg_no_gep);
$reg_no_gep = isset($_POST["reg_no_gep"]) ? $_POST["reg_no_gep"] : NULL;
unset($birth_year);
$birth_year = isset($_POST["birth_year"]) ? $_POST["birth_year"] : NULL;
unset($birth_month);
$birth_month = isset($_POST["birth_month"]) ? $_POST["birth_month"] : NULL;
unset($birth_day);
$birth_day = isset($_POST["birth_day"]) ? $_POST["birth_day"] : NULL;

//=========================
// AJOUT: boireaus 20071107
unset($reg_regime);
$reg_regime = isset($_POST["reg_regime"]) ? $_POST["reg_regime"] : NULL;
unset($reg_doublant);
$reg_doublant = isset($_POST["reg_doublant"]) ? $_POST["reg_doublant"] : NULL;

//echo "\$reg_regime=$reg_regime<br />";
//echo "\$reg_doublant=$reg_doublant<br />";

//=========================


unset($reg_resp1);
$reg_resp1 = isset($_POST["reg_resp1"]) ? $_POST["reg_resp1"] : NULL;
unset($reg_resp2);
$reg_resp2 = isset($_POST["reg_resp2"]) ? $_POST["reg_resp2"] : NULL;

unset($reg_etab);
$reg_etab = isset($_POST["reg_etab"]) ? $_POST["reg_etab"] : NULL;

unset($mode);
$mode = isset($_POST["mode"]) ? $_POST["mode"] : (isset($_GET["mode"]) ? $_GET["mode"] : NULL);
unset($order_type);
$order_type = isset($_POST["order_type"]) ? $_POST["order_type"] : (isset($_GET["order_type"]) ? $_GET["order_type"] : NULL);
unset($quelles_classes);
$quelles_classes = isset($_POST["quelles_classes"]) ? $_POST["quelles_classes"] : (isset($_GET["quelles_classes"]) ? $_GET["quelles_classes"] : NULL);
unset($eleve_login);
$eleve_login = isset($_POST["eleve_login"]) ? $_POST["eleve_login"] : (isset($_GET["eleve_login"]) ? $_GET["eleve_login"] : NULL);
//echo "\$eleve_login=$eleve_login<br />";

$definir_resp = isset($_POST["definir_resp"]) ? $_POST["definir_resp"] : (isset($_GET["definir_resp"]) ? $_GET["definir_resp"] : NULL);
if(($definir_resp!=1)&&($definir_resp!=2)){$definir_resp=NULL;}

$definir_etab = isset($_POST["definir_etab"]) ? $_POST["definir_etab"] : (isset($_GET["definir_etab"]) ? $_GET["definir_etab"] : NULL);

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



/*
foreach($_POST as $post => $val){
	echo $post.' : '.$val."<br />\n";
}

echo "\$eleve_login=$eleve_login<br />";
echo "\$valider_choix_resp=$valider_choix_resp<br />";
echo "\$definir_resp=$definir_resp<br />";
*/
// Validation d'un choix de responsable
if((isset($eleve_login))&&(isset($definir_resp))&&(isset($_POST['valider_choix_resp']))) {
	if($definir_resp==1){
		$pers_id=$reg_resp1;
	}
	else{
		$pers_id=$reg_resp2;
	}

	if($pers_id==""){
		// Recherche de l'ele_id
		$sql="SELECT ele_id FROM eleves WHERE login='$eleve_login'";
		$res_ele=mysql_query($sql);
		if(mysql_num_rows($res_ele)==0){
			$msg="Erreur: L'�l�ve $eleve_login n'a pas l'air pr�sent dans la table 'eleves'.";
		}
		else{
			$lig_ele=mysql_fetch_object($res_ele);

			$sql="DELETE FROM responsables2 WHERE ele_id='$lig_ele->ele_id' AND resp_legal='$definir_resp'";
			$suppr=mysql_query($sql);
			if($suppr){
				$msg="Suppression de l'association de l'�l�ve avec le responsable $definir_resp r�ussie.";
			}
			else{
				$msg="Echec de la suppression l'association de l'�l�ve avec le responsable $definir_resp.";
			}
		}
	}
	else{
		$sql="SELECT 1=1 FROM resp_pers WHERE pers_id='$pers_id'";
		$test=mysql_query($sql);

		if(mysql_num_rows($test)==0){
			$msg="Erreur: L'identifiant de responsable propos� n'existe pas.";
		}
		else{
			// Recherche de l'ele_id
			$sql="SELECT ele_id FROM eleves WHERE login='$eleve_login'";
			$res_ele=mysql_query($sql);
			if(mysql_num_rows($res_ele)==0){
				$msg="Erreur: L'�l�ve $eleve_login n'a pas l'air pr�sent dans la table 'eleves'.";
			}
			else{
				$lig_ele=mysql_fetch_object($res_ele);

				//$sql="SELECT 1=1 FROM responsables2 WHERE pers_id='$pers_id' AND ele_id='$lig_ele->ele_id' AND resp_legal='$definir_resp'";
				$sql="SELECT 1=1 FROM responsables2 WHERE ele_id='$lig_ele->ele_id' AND resp_legal='$definir_resp'";
				$test=mysql_query($sql);

				if(mysql_num_rows($test)==0){
					$sql="INSERT INTO responsables2 SET pers_id='$pers_id', ele_id='$lig_ele->ele_id', resp_legal='$definir_resp', pers_contact='1'";
					$insert=mysql_query($sql);
					if($insert){
						$msg="Association de l'�l�ve avec le responsable $definir_resp r�ussie.";
					}
					else{
						$msg="Echec de l'association de l'�l�ve avec le responsable $definir_resp.";
					}
				}
				else{
					$sql="UPDATE responsables2 SET pers_id='$pers_id' WHERE ele_id='$lig_ele->ele_id' AND resp_legal='$definir_resp'";
					$update=mysql_query($sql);
					if($update){
						$msg="Association de l'�l�ve avec le responsable $definir_resp r�ussie.";
					}
					else{
						$msg="Echec de l'association de l'�l�ve avec le responsable $definir_resp.";
					}
				}
			}
		}
	}
	unset($definir_resp);
}




// Validation d'un choix d'�tablissement d'origine
if((isset($eleve_login))&&(isset($definir_etab))&&(isset($_POST['valider_choix_etab']))) {

	if($reg_etab==""){
		$sql="DELETE FROM j_eleves_etablissements WHERE id_eleve='$eleve_login'";
		$suppr=mysql_query($sql);
		if($suppr){
			$msg="Suppression de l'association de l'�l�ve avec un �tablissement r�ussie.";
		}
		else{
			$msg="Echec de la suppression l'association de l'�l�ve avec un �tablissement.";
		}
	}
	else{
		$sql="SELECT 1=1 FROM etablissements WHERE id='$reg_etab'";
		//echo "$sql<br />";
		$test=mysql_query($sql);

		if(mysql_num_rows($test)==0){
			$msg="Erreur: L'�tablissement choisi (<i>$reg_etab</i>) n'existe pas dans la table 'etablissement'.";
		}
		else{
			$sql="SELECT 1=1 FROM j_eleves_etablissements WHERE id_eleve='$eleve_login'";
			$test=mysql_query($sql);

			if(mysql_num_rows($test)==0){
				$sql="INSERT INTO j_eleves_etablissements SET id_eleve='$eleve_login', id_etablissement='$reg_etab'";
				$insert=mysql_query($sql);
				if($insert){
					$msg="Association de l'�l�ve avec l'�tablissement $reg_etab r�ussie.";
				}
				else{
					$msg="Echec de l'association de l'�l�ve avec l'�tablissement $reg_etab.";
				}
			}
			else{
				$sql="UPDATE j_eleves_etablissements SET id_etablissement='$reg_etab' WHERE id_eleve='$eleve_login'";
				$update=mysql_query($sql);
				if($update){
					$msg="Association de l'�l�ve avec l'�tablissement $reg_etab r�ussie.";
				}
				else{
					$msg="Echec de l'association de l'�l�ve avec l'�tablissement $reg_etab.";
				}
			}
		}
	}
	unset($definir_etab);
}


//================================================
// Validation de modifications dans le formulaire de nom, pr�nom,...
if (isset($_POST['is_posted']) and ($_POST['is_posted'] == "1")) {
	// D�termination du format de la date de naissance
	$call_eleve_test = mysql_query("SELECT naissance FROM eleves WHERE");
	$test_eleve_naissance = @mysql_result($call_eleve_test, "0", "naissance");
	$format = strlen($test_eleve_naissance);


	// Cas de la cr�ation d'un �l�ve
	$reg_nom = trim($reg_nom);
	$reg_prenom = trim($reg_prenom);
	$reg_email = trim($reg_email);
	if ($reg_resp1 == '(vide)') $reg_resp1 = '';
	if (!ereg ("^[0-9]{4}$", $birth_year)) $birth_year = "1900";
	if (!ereg ("^[0-9]{2}$", $birth_month)) $birth_month = "01";
	if (!ereg ("^[0-9]{2}$", $birth_day)) $birth_day = "01";
	if ($format == '10'){
		// YYYY-MM-DD
		$reg_naissance = $birth_year."-".$birth_month."-".$birth_day." 00:00:00";
	}
	else{
		if ($format == '8') {
			// YYYYMMDD
			$reg_naissance = $birth_year.$birth_month.$birth_day;
			settype($reg_naissance,"integer");
		} else {
			// Format inconnu
			$reg_naissance = $birth_year.$birth_month.$birth_day;
		}
	}

	//===========================
	//AJOUT:
	if(!isset($msg)){$msg="";}
	//===========================

	$continue = 'yes';
	if (($reg_nom == '') or ($reg_prenom == '')) {
		$msg = "Les champs nom et pr�nom sont obligatoires.";
		$continue = 'no';
	}

	//$msg.="\$reg_login=$reg_login<br />";
	//if(isset($eleve_login)){$msg.="\$eleve_login=$eleve_login<br />";}

	// $reg_login non vide correspond � un nouvel �l�ve.
	// On a saisi un login avant de valider
	if (($continue == 'yes') and (isset($reg_login))) {
		// CE CAS NE DOIT PLUS SE PRODUIRE PUISQUE J'AI AJOUT� UNE PAGE add_eleve.php D'APRES L'ANCIENNE modify_eleve.php
		// On doit n�cessairement passer dans le else plus bas...

		//echo "\$reg_login=$reg_login<br/>";

		$msg = '';
		$ok = 'yes';
		if (ereg ("^[a-zA-Z_]{1}[a-zA-Z0-9_]{0,11}$", $reg_login)) {
			if ($reg_no_gep != '') {
				$test1 = mysql_query("SELECT login FROM eleves WHERE elenoet='$reg_no_gep'");
				$count1 = mysql_num_rows($test1);
				if ($count1 != "0") {
					//$msg .= "Erreur : un �l�ve ayant le m�me num�ro GEP existe d�j�.<br />";
					$msg .= "Erreur : un �l�ve ayant le m�me num�ro interne Sconet (elenoet) existe d�j�.<br />";
					$ok = 'no';
				}
			}

			if ($reg_no_nat != '') {
				$test2 = mysql_query("SELECT login FROM eleves WHERE no_gep='$reg_no_nat'");
				$count2 = mysql_num_rows($test2);
				if ($count2 != "0") {
					$msg .= "Erreur : un �l�ve ayant le m�me num�ro national existe d�j�.";
					$ok = 'no';
				}
			}

			if ($ok == 'yes') {
				$test = mysql_query("SELECT login FROM eleves WHERE login='$reg_login'");
				$count = mysql_num_rows($test);
				if ($count == "0") {

					if(!isset($ele_id)){
						// GENERER UN ele_id...
						/*
						$sql="SELECT MAX(ele_id) max_ele_id FROM eleves";
						$res_ele_id_eleve=mysql_query($sql);
						$max_ele_id = mysql_result($call_resp , 0, "max_ele_id");

						$sql="SELECT MAX(ele_id) max_ele_id FROM responsables2";
						$res_ele_id_responsables2=mysql_query($sql);
						$max_ele_id2 = mysql_result($call_resp , 0, "max_ele_id");

						if($max_ele_id2>$max_ele_id){$max_ele_id=$max_ele_id2;}
						$ele_id=$max_ele_id+1;
						*/
						// PB si on fait ensuite un import sconet le pers_id risque de ne pas correspondre... de provoquer des collisions.
						// QUAND ON LES METS A LA MAIN, METTRE UN ele_id, pers_id,... n�gatifs?

						// PREFIXER D'UN a...

						$sql="SELECT ele_id FROM eleves WHERE ele_id LIKE 'e%' ORDER BY ele_id DESC";
						$res_ele_id_eleve=mysql_query($sql);
						if(mysql_num_rows($res_ele_id_eleve)>0){
							$tmp=0;
							$lig_ele_id_eleve=mysql_fetch_object($res_ele_id_eleve);
							$tmp=substr($lig_ele_id_eleve->ele_id,1);
							$tmp++;
							$max_ele_id=$tmp;
						}
						else{
							$max_ele_id=1;
						}

						$sql="SELECT ele_id FROM responsables2 WHERE ele_id LIKE 'e%' ORDER BY ele_id DESC";
						$res_ele_id_responsables2=mysql_query($sql);
						if(mysql_num_rows($res_ele_id_responsables2)>0){
							$tmp=0;
							$lig_ele_id_responsables2=mysql_fetch_object($res_ele_id_responsables2);
							$tmp=substr($lig_ele_id_responsables2->ele_id,1);
							$tmp++;
							$max_ele_id2=$tmp;
						}
						else{
							$max_ele_id2=1;
						}

						$tmp=max($max_ele_id,$max_ele_id2);
						$ele_id="e".sprintf("%09d",max($max_ele_id,$max_ele_id2));
					}

					/*
					$reg_data1 = mysql_query("INSERT INTO eleves SET
						no_gep = '".$reg_no_nat."',
						nom='".$reg_nom."',
						prenom='".$reg_prenom."',
						login='".$reg_login."',
						sexe='".$reg_sexe."',
						naissance='".$reg_naissance."',
						elenoet = '".$reg_no_gep."',
						ereno = '".$reg_resp1."',
						ele_id = '".$ele_id."'
						");
					*/
					$reg_data1 = mysql_query("INSERT INTO eleves SET
						no_gep = '".$reg_no_nat."',
						nom='".$reg_nom."',
						prenom='".$reg_prenom."',
						email='".$reg_email ."',
						login='".$reg_login."',
						sexe='".$reg_sexe."',
						naissance='".$reg_naissance."',
						elenoet = '".$reg_no_gep."',
						ele_id = '".$ele_id."'
						");


					/*
					$sql="SELECT 1=1 FROM responsables2 WHERE ele_id='$ele_id' AND pers_id='$reg_resp1'";
					$test_resp1=mysql_query($sql);
					if(mysql_num_rows($test_resp1)>0){
						// Il y a d�j� une association �l�ve/responsable (c'est bizarre pour un �l�ve que l'on inscrit maintenant???)
						$sql="SELECT 1=1 FROM responsables2 WHERE ele_id='$ele_id' AND pers_id='$reg_resp1' AND resp_legal='2'";
						$test_resp1b=mysql_query($sql);
						if(mysql_num_rows($test_resp1b)==1){
							// Le responsable 2 devient responsable 1.
							$temoin_maj_resp="";
							$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND pers_id!='$reg_resp1' AND resp_legal='1'";
							$test_resp1c=mysql_query($sql);
							if(mysql_num_rows($test_resp1c)==1){
								$lig_autre_resp=mysql_fetch_object($test_resp1c);
								$sql="UPDATE responsables2 SET resp_legal='2' WHERE ele_id='$ele_id' AND pers_id='$lig_autre_resp->pers_id'";
								$res_update=mysql_query($sql);
								if(!$res_update){
									$msg.="Erreur lors de la mise � jour du responsable $lig_autre_resp->pers_id en responsable l�gal n�2.<br />\n";
									$temoin_maj_resp="PB";
								}
							}

							if($temoin_maj_resp==""){
								$sql="UPDATE responsables2 SET resp_legal='1' WHERE ele_id='$ele_id' AND pers_id='$reg_resp1'";
								$res_update=mysql_query($sql);
								if(!$res_update){
									$msg.="Erreur lors de la mise � jour du responsable $reg_resp1 en responsable l�gal n�1.<br />\n";
								}
							}
						}
						// Sinon, l'association est d�j� la bonne... pas de changement.
					}
					else{
						// Il n'y a pas encore d'association entre cet �l�ve et ce responsable
						$temoin_maj_resp="";
						$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND pers_id!='$reg_resp1' AND resp_legal='1'";
						$test_resp1c=mysql_query($sql);
						//if(mysql_num_rows($test_resp1c)==1){
						if(mysql_num_rows($test_resp1c)>0){
							$lig_autre_resp=mysql_fetch_object($test_resp1c);

							// Y avait-il un autre responsable l�gal n�2?
							$sql="DELETE FROM responsables2 WHERE ele_id='$ele_id' AND resp_legal='2'";
							$res_menage=mysql_query($sql);
							if(!$res_menage){
								$msg.="Erreur lors de la suppression de l'association avec le pr�c�dent responsable l�gal n�2.<br />";
								$temoin_maj_resp="PB";
							}
							else{
								// L'ancien resp_legal 1 devient resp_legal 2
								$sql="UPDATE responsables2 SET resp_legal='2' WHERE ele_id='$ele_id' AND pers_id='$lig_autre_resp->pers_id'";
								$res_update=mysql_query($sql);
								if(!$res_update){
									$msg.="Erreur lors de la mise � jour du responsable $lig_autre_resp->pers_id en responsable l�gal n�2.<br />\n";
									$temoin_maj_resp="PB";
								}
							}
						}

						if($temoin_maj_resp==""){
							$sql="INSERT INTO responsables2 SET ele_id='$ele_id', pers_id='$reg_resp1', resp_legal='1', pers_contact='1'";
							$reg_data2b=mysql_query($sql);
							if(!$reg_data2b){
								$msg.="Erreur lors de la mise � jour du responsable $reg_resp1 en responsable l�gal n�1.<br />\n";
							}
						}
					}
					*/

					// R�gime:
					$reg_data3 = mysql_query("INSERT INTO j_eleves_regime SET login='$reg_login', doublant='-', regime='d/p'");
					/*
					// R�gime et �tablissement d'origine:
					$call_test = mysql_query("SELECT * FROM j_eleves_etablissements WHERE id_eleve = '$reg_login'");
					$count2 = mysql_num_rows($call_test);
					if ($count2 == "0") {
						if ($reg_etab != "(vide)") {
							$reg_data2 = mysql_query("INSERT INTO j_eleves_etablissements VALUES ('$reg_login','$reg_etab')");
						}
					} else {
						if ($reg_etab != "(vide)") {
							$reg_data2 = mysql_query("UPDATE j_eleves_etablissements SET id_etablissement = '$reg_etab' WHERE id_eleve='$reg_login'");
						} else {
							$reg_data2 = mysql_query("DELETE FROM j_eleves_etablissements WHERE id_eleve='$reg_login'");
						}
					}
					*/
					if ((!$reg_data1) or (!$reg_data3)) {
						$msg = "Erreur lors de l'enregistrement des donn�es";
					} elseif ($mode == "unique") {
						$mess=rawurlencode("El�ve enregistr� !");
						header("Location: index.php?msg=$mess");
						die();
					} elseif ($mode == "multiple") {
						$mess=rawurlencode("El�ve enregistr�.Vous pouvez saisir l'�l�ve suivant.");
						header("Location: modify_eleve.php?mode=multiple&msg=$mess");
						die();
					}
				} else {
					$msg="Un �l�ve portant le m�me identifiant existe d�ja !";
				}
			}
		} else {
			$msg="L'identifiant choisi est constitu� au maximum de 12 caract�res : lettres, chiffres ou \"_\" et ne doit pas commencer par un chiffre !";
		}
	} else if ($continue == 'yes') {
		// C'est une mise � jour pour un �l�ve qui existait d�j� dans la table 'eleves'.

		// On nettoie les windozeries
		$reg_data = mysql_query("UPDATE eleves SET no_gep = '$reg_no_nat', nom='$reg_nom',prenom='$reg_prenom',email='$reg_email',sexe='$reg_sexe',naissance='".$reg_naissance."', ereno='".$reg_resp1."', elenoet = '".$reg_no_gep."' WHERE login='".$eleve_login."'");
		if (!$reg_data) {
			$msg = "Erreur lors de l'enregistrement des donn�es";
		} else {
			// On met � jour la table utilisateurs si un compte existe pour cet �l�ve
			$test_login = mysql_result(mysql_query("SELECT count(login) FROM utilisateurs WHERE login = '".$eleve_login ."'"), 0);
			if ($test_login > 0) {
				$res = mysql_query("UPDATE utilisateurs SET nom='".$reg_nom."', prenom='".$reg_prenom."', email='".$reg_email."' WHERE login = '".$eleve_login."'");
				//$msg.="TEMOIN test_login puis update<br />";
			}
		}




		if(isset($reg_doublant)){
			if ($reg_doublant!='R') {$reg_doublant = '-';}

			$call_regime = mysql_query("SELECT * FROM j_eleves_regime WHERE login='$eleve_login'");
			$nb_test_regime = mysql_num_rows($call_regime);
			if ($nb_test_regime == 0) {
				// On va se retrouver �ventuellement avec un r�gime vide... cela peut-il poser pb?
				$reg_data = mysql_query("INSERT INTO j_eleves_regime SET login='$eleve_login', doublant='$reg_doublant';");
				if (!($reg_data)) {$reg_ok = 'no';}
			} else {
				$reg_data = mysql_query("UPDATE j_eleves_regime SET doublant = '$reg_doublant' WHERE login='$eleve_login';");
				if (!($reg_data)) {$reg_ok = 'no';}
			}
		}

		if(isset($reg_regime)){
			if (($reg_regime!='i-e')&&($reg_regime!='int.')&&($reg_regime!='ext.')&&($reg_regime!='d/p')) {
				$reg_regime='d/p';
			}

			$call_regime = mysql_query("SELECT * FROM j_eleves_regime WHERE login='$eleve_login'");
			$nb_test_regime = mysql_num_rows($call_regime);
			if ($nb_test_regime == 0) {
				$reg_data = mysql_query("INSERT INTO j_eleves_regime SET login='$eleve_login', regime='$reg_regime'");
				if (!($reg_data)) {$reg_ok = 'no';}
			} else {
				$reg_data = mysql_query("UPDATE j_eleves_regime SET regime = '$reg_regime'  WHERE login='$eleve_login'");
				if (!($reg_data)) {$reg_ok = 'no';}
			}
		}



		/*
		$call_test = mysql_query("SELECT * FROM j_eleves_etablissements WHERE id_eleve = '$eleve_login'");
		$count = mysql_num_rows($call_test);
		if ($count == "0") {
			if ($reg_etab != "(vide)") {
				$reg_data = mysql_query("INSERT INTO j_eleves_etablissements VALUES ('$eleve_login','$reg_etab')");
			}
		} else {
			if ($reg_etab != "(vide)") {
				$reg_data = mysql_query("UPDATE j_eleves_etablissements SET id_etablissement = '$reg_etab' WHERE id_eleve='$eleve_login'");
			} else {
				$reg_data = mysql_query("DELETE FROM j_eleves_etablissements WHERE id_eleve='$eleve_login'");
			}
		}
		*/

		if (!$reg_data) {
			$msg = "Erreur lors de l'enregistrement des donn�es ! ";
		} else {
			//$msg = "Les modifications ont bien �t� enregistr�es !";
			// MODIF POUR AFFICHER MES TEMOINS...
			$msg .= "Les modifications ont bien �t� enregistr�es ! ";
		}


		// Envoi de la photo
		if(isset($reg_no_gep)){
			if($reg_no_gep!=""){
				if(strlen(ereg_replace("[0-9]","",$reg_no_gep))==0){
					if(isset($_POST['suppr_filephoto'])){
						if($_POST['suppr_filephoto']=='y'){

							// R�cup�ration du nom de la photo en tenant compte des histoires des z�ro 02345.jpg ou 2345.jpg
							$photo=nom_photo($reg_no_gep);

							if("$photo"!=""){
								if(unlink("../photos/eleves/$photo")){
									$msg.="La photo ../photos/eleves/$photo a �t� supprim�e. ";
								}
								else{
									$msg.="Echec de la suppression de la photo ../photos/eleves/$photo ";
								}
							}
							else{
								$msg.="Echec de la suppression de la photo correspondant � $reg_no_gep (<i>non trouv�e</i>) ";
							}
						}
					}

					// Contr�ler qu'un seul �l�ve a bien cet elenoet???
					$sql="SELECT 1=1 FROM eleves WHERE elenoet='$reg_no_gep'";
					$test=mysql_query($sql);
					$nb_elenoet=mysql_num_rows($test);
					if($nb_elenoet==1){
						// filephoto
						$filephoto_tmp=$HTTP_POST_FILES['filephoto']['tmp_name'];
						if($filephoto_tmp!=""){
							$filephoto_name=$HTTP_POST_FILES['filephoto']['name'];
							$filephoto_size=$HTTP_POST_FILES['filephoto']['size'];
							// Tester la taille max de la photo?

							if(is_uploaded_file($filephoto_tmp)){
								$dest_file="../photos/eleves/$reg_no_gep.jpg";
								$source_file=stripslashes("$filephoto_tmp");
								$res_copy=copy("$source_file" , "$dest_file");
								if($res_copy){
									$msg.="Mise en place de la photo effectu�e.";
								}
								else{
									$msg.="Erreur lors de la mise en place de la photo.";
								}
							}
							else{
								$msg.="Erreur lors de l'upload de la photo.";
							}
						}
					}
					elseif($nb_elenoet==0){
							//$msg.="Le num�ro GEP de l'�l�ve n'est pas enregistr� dans la table 'eleves'.";
							$msg.="Le num�ro interne Sconet (elenoet) de l'�l�ve n'est pas enregistr� dans la table 'eleves'.";
					}
					else{
						//$msg.="Le num�ro GEP est commun � plusieurs �l�ves. C'est une anomalie.";
						$msg.="Le num�ro interne Sconet (elenoet) est commun � plusieurs �l�ves. C'est une anomalie.";
					}
				}
				else{
					//$msg.="Le num�ro GEP propos� contient des caract�res non num�riques.";
					$msg.="Le num�ro interne Sconet (elenoet) propos� contient des caract�res non num�riques.";
				}
			}
		}


		$temoin_ele_id="";
		$sql="SELECT ele_id FROM eleves WHERE login='$eleve_login'";
		$res_ele_id_eleve=mysql_query($sql);
		if(mysql_num_rows($res_ele_id_eleve)==0){
			$msg.="Erreur: Le champ ele_id n'est pas pr�sent. Votre table 'eleves' n'a pas l'air � jour.<br />";
			$temoin_ele_id="PB";
		}
		else{
			$lig_tmp=mysql_fetch_object($res_ele_id_eleve);
			$ele_id=$lig_tmp->ele_id;
		}


		/*
		if($temoin_ele_id==""){
			$sql="SELECT 1=1 FROM responsables2 WHERE ele_id='$ele_id' AND pers_id='$reg_resp1'";
			$test_resp1=mysql_query($sql);
			if(mysql_num_rows($test_resp1)>0){
				// Il y a d�j� une association �l�ve/responsable (c'est bizarre pour un �l�ve que l'on inscrit maintenant???)
				$sql="SELECT 1=1 FROM responsables2 WHERE ele_id='$ele_id' AND pers_id='$reg_resp1' AND resp_legal='2'";
				$test_resp1b=mysql_query($sql);
				if(mysql_num_rows($test_resp1b)==1){
					// Le responsable 2 devient responsable 1.
					$temoin_maj_resp="";
					$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND pers_id!='$reg_resp1' AND resp_legal='1'";
					$test_resp1c=mysql_query($sql);
					if(mysql_num_rows($test_resp1c)==1){
						$lig_autre_resp=mysql_fetch_object($test_resp1c);
						$sql="UPDATE responsables2 SET resp_legal='2' WHERE ele_id='$ele_id' AND pers_id='$lig_autre_resp->pers_id'";
						$res_update=mysql_query($sql);
						if(!$res_update){
							$msg.="Erreur lors de la mise � jour du responsable $lig_autre_resp->pers_id en responsable l�gal n�2.<br />\n";
							$temoin_maj_resp="PB";
						}
					}

					if($temoin_maj_resp==""){
						$sql="UPDATE responsables2 SET resp_legal='1' WHERE ele_id='$ele_id' AND pers_id='$reg_resp1'";
						$res_update=mysql_query($sql);
						if(!$res_update){
							$msg.="Erreur lors de la mise � jour du responsable $reg_resp1 en responsable l�gal n�1.<br />\n";
						}
					}
				}
				// Sinon, l'association est d�j� la bonne... pas de changement.
			}
			else{
				// Il n'y a pas encore d'association entre cet �l�ve et ce responsable
				$temoin_maj_resp="";
				$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND pers_id!='$reg_resp1' AND resp_legal='1'";
				$test_resp1c=mysql_query($sql);
				//if(mysql_num_rows($test_resp1c)==1){
				if(mysql_num_rows($test_resp1c)>0){
					$lig_autre_resp=mysql_fetch_object($test_resp1c);

					// Y avait-il un autre responsable l�gal n�2?
					$sql="DELETE FROM responsables2 WHERE ele_id='$ele_id' AND resp_legal='2'";
					$res_menage=mysql_query($sql);
					if(!$res_menage){
						$msg.="Erreur lors de la suppression de l'association avec le pr�c�dent responsable l�gal n�2.<br />";
						$temoin_maj_resp="PB";
					}
					else{
						// L'ancien resp_legal 1 devient resp_legal 2
						$sql="UPDATE responsables2 SET resp_legal='2' WHERE ele_id='$ele_id' AND pers_id='$lig_autre_resp->pers_id'";
						$res_update=mysql_query($sql);
						if(!$res_update){
							$msg.="Erreur lors de la mise � jour du responsable $lig_autre_resp->pers_id en responsable l�gal n�2.<br />\n";
							$temoin_maj_resp="PB";
						}
					}
				}

				if($temoin_maj_resp==""){
					$sql="INSERT INTO responsables2 SET ele_id='$ele_id', pers_id='$reg_resp1', resp_legal='1', pers_contact='1'";
					$reg_data2b=mysql_query($sql);
					if(!$reg_data2b){
						$msg.="Erreur lors de la mise � jour du responsable $reg_resp1 en responsable l�gal n�1.<br />\n";
					}
				}
			}
		}
		*/




/*
		$sql="SELECT 1=1 FROM responsables2 WHERE ele_id='$ele_id' AND pers_id='$reg_resp1'";
		$test_resp1=mysql_query($sql);
		if(mysql_num_rows($test_resp1)){
			$sql="SELECT 1=1 FROM responsables2 WHERE ele_id='$ele_id' AND pers_id='$reg_resp1' AND resp_legal='2'";
			$test_resp1b=mysql_query($sql);
			if(mysql_num_rows($test_resp1b)==1){
				$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND pers_id!='$reg_resp1' AND resp_legal='1'";
				$test_resp1c=mysql_query($sql);
				if(mysql_num_rows($test_resp1c)==1){
					$lig_autre_resp=mysql_fetch_object($test_resp1c);
					$sql="UPDATE responsables2 SET resp_legal='2' WHERE ele_id='$ele_id' AND pers_id='$lig_autre_resp->pers_id'";
					$res_update=mysql_query($sql);
				}

				$sql="UPDATE responsables2 SET resp_legal='1' WHERE ele_id='$ele_id' AND pers_id='$reg_resp1'";
				$res_update=mysql_query($sql);
			}
		}
		else{
			$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND pers_id!='$reg_resp1' AND resp_legal='1'";
			$test_resp1c=mysql_query($sql);
			if(mysql_num_rows($test_resp1c)==1){
				$lig_autre_resp=mysql_fetch_object($test_resp1c);
				$sql="UPDATE responsables2 SET resp_legal='2' WHERE ele_id='$ele_id' AND pers_id='$lig_autre_resp->pers_id'";
				$res_update=mysql_query($sql);
			}

			$sql="INSERT INTO responsables2 SET ele_id='$ele_id', pers_id='$reg_resp1', resp_legal='1', pers_contact='1'";
			$reg_data2b=mysql_query($sql);
		}

		// AJOUTER DES TESTS DE SUCCES DE LA M�J.
*/

	}
}

//================================================

// On appelle les informations de l'utilisateur pour les afficher :
if (isset($eleve_login)) {
    $call_eleve_info = mysql_query("SELECT * FROM eleves WHERE login='$eleve_login'");
    $eleve_nom = mysql_result($call_eleve_info, "0", "nom");
    $eleve_prenom = mysql_result($call_eleve_info, "0", "prenom");
    $eleve_email = mysql_result($call_eleve_info, "0", "email");
    $eleve_sexe = mysql_result($call_eleve_info, "0", "sexe");
    $eleve_naissance = mysql_result($call_eleve_info, "0", "naissance");
    if (strlen($eleve_naissance) == 10) {
        // YYYY-MM-DD
        $eleve_naissance_annee = substr($eleve_naissance, 0, 4);
        $eleve_naissance_mois = substr($eleve_naissance, 5, 2);
        $eleve_naissance_jour = substr($eleve_naissance, 8, 2);
    } elseif (strlen($eleve_naissance) == 8 ) {
        // YYYYMMDD
        $eleve_naissance_annee = substr($eleve_naissance, 0, 4);
        $eleve_naissance_mois = substr($eleve_naissance, 4, 2);
        $eleve_naissance_jour = substr($eleve_naissance, 6, 2);
    } elseif (strlen($eleve_naissance) == 19 ) {
        // YYYY-MM-DD xx:xx:xx
        $eleve_naissance_annee = substr($eleve_naissance, 0, 4);
        $eleve_naissance_mois = substr($eleve_naissance, 5, 2);
        $eleve_naissance_jour = substr($eleve_naissance, 8, 2);
    } else {
        // Format inconnu
        $eleve_naissance_annee = "??";
        $eleve_naissance_mois = "??";
        $eleve_naissance_jour = "????";
    }
    //$eleve_no_resp = mysql_result($call_eleve_info, "0", "ereno");
    $reg_no_nat = mysql_result($call_eleve_info, "0", "no_gep");
    $reg_no_gep = mysql_result($call_eleve_info, "0", "elenoet");
    $call_etab = mysql_query("SELECT e.* FROM etablissements e, j_eleves_etablissements j WHERE (j.id_eleve='$eleve_login' and e.id = j.id_etablissement)");
    $id_etab = @mysql_result($call_etab, "0", "id");

	//echo "SELECT e.* FROM etablissements e, j_eleves_etablissements j WHERE (j.id_eleve='$eleve_login' and e.id = j.id_etablissement)<br />";

	//=========================
	// AJOUT: boireaus 20071107
	$sql="SELECT * FROM j_eleves_regime WHERE login='$eleve_login';";
	//echo "$sql<br />\n";
	$res_regime=mysql_query($sql);
	if(mysql_num_rows($res_regime)>0){
		$lig_tmp=mysql_fetch_object($res_regime);
		$reg_regime=$lig_tmp->regime;
		$reg_doublant=$lig_tmp->doublant;
	}
	else{
		$reg_regime="d/p";
		$reg_doublant="-";
	}
	//=========================


	if(!isset($ele_id)){
		$ele_id=mysql_result($call_eleve_info, "0", "ele_id");
	}

	$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND resp_legal='1'";
	//echo "$sql<br />\n";
	$res_resp1=mysql_query($sql);
	if(mysql_num_rows($res_resp1)>0){
		$lig_no_resp1=mysql_fetch_object($res_resp1);
		$eleve_no_resp1=$lig_no_resp1->pers_id;
	}
	else{
		$eleve_no_resp1=0;
	}
	//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";

	$sql="SELECT pers_id FROM responsables2 WHERE ele_id='$ele_id' AND resp_legal='2'";
	//echo "$sql<br />\n";
	$res_resp2=mysql_query($sql);
	if(mysql_num_rows($res_resp2)>0){
		$lig_no_resp2=mysql_fetch_object($res_resp2);
		$eleve_no_resp2=$lig_no_resp2->pers_id;
	}
	else{
		$eleve_no_resp2=0;
	}


} else {
    if (isset($reg_nom)) $eleve_nom = $reg_nom;
    if (isset($reg_prenom)) $eleve_prenom = $reg_prenom;
    if (isset($reg_email)) $eleve_email = $reg_email;
    if (isset($reg_sexe)) $eleve_sexe = $reg_sexe;
    if (isset($reg_no_nat)) $reg_no_nat = $reg_no_nat;
    if (isset($reg_no_gep)) $reg_no_gep = $reg_no_gep;
    if (isset($birth_year)) $eleve_naissance_annee = $birth_year;
    if (isset($birth_month)) $eleve_naissance_mois = $birth_month;
    if (isset($birth_day)) $eleve_naissance_jour = $birth_day;
    //$eleve_no_resp = 0;
    $eleve_no_resp1 = 0;
    $eleve_no_resp2 = 0;
    $id_etab = 0;

	//=========================
	// AJOUT: boireaus 20071107
	// On ne devrait pas passer par l�.
	// Quand on arrive sur modify_elve.php, le login de l'�l�ve doit exister.
	$reg_regime="d/p";
	$reg_doublant="-";
	//=========================
}


//**************** EN-TETE *****************
$titre_page = "Gestion des �l�ves | Ajouter/Modifier une fiche �l�ve";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

/*
if ((isset($order_type)) and (isset($quelles_classes))) {
    echo "<p class=bold><a href=\"index.php?quelles_classes=$quelles_classes&amp;order_type=$order_type\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>";
} else {
    echo "<p class=bold><a href=\"index.php\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>";
}
*/

/*
// D�sactiv� pour permettre de renseigner un ELENOET manquant pour une conversion avec sconet
// Cela a en revanche �t� conserv� sur la page index.php
// On ne devrait donc arriver ici lorsqu'une conversion est r�clam�e qu'en venant de conversion.php pour remplir un ELENOET
if(!getSettingValue('conv_new_resp_table')){
	$sql="SELECT 1=1 FROM responsables";
	$test=mysql_query($sql);
	if(mysql_num_rows($test)>0){
		echo "<p>Une conversion des donn�es �l�ves/responsables est requise.</p>\n";
		echo "<p>Suivez ce lien: <a href='../responsables/conversion.php'>CONVERTIR</a></p>\n";
		require("../lib/footer.inc.php");
		die();
	}

	$sql="SHOW COLUMNS FROM eleves LIKE 'ele_id'";
	$test=mysql_query($sql);
	if(mysql_num_rows($test)==0){
		echo "<p>Une conversion des donn�es �l�ves/responsables est requise.</p>\n";
		echo "<p>Suivez ce lien: <a href='../responsables/conversion.php'>CONVERTIR</a></p>\n";
		require("../lib/footer.inc.php");
		die();
	}
	else{
		$sql="SELECT 1=1 FROM eleves WHERE ele_id=''";
		$test=mysql_query($sql);
		if(mysql_num_rows($test)>0){
			echo "<p>Une conversion des donn�es �l�ves/responsables est requise.</p>\n";
			echo "<p>Suivez ce lien: <a href='../responsables/conversion.php'>CONVERTIR</a></p>\n";
			require("../lib/footer.inc.php");
			die();
		}
	}
}
*/


?>
<!--form enctype="multipart/form-data" action="modify_eleve.php" method=post-->
<?php

//eleve_login=$eleve_login&amp;definir_resp=1
if(isset($definir_resp)){
	if(!isset($valider_choix_resp)){

		echo "<p class=bold><a href=\"modify_eleve.php?eleve_login=$eleve_login\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>";

		echo "<p>Choix du responsable l�gal <b>$definir_resp</b> pour <b>".ucfirst(strtolower($eleve_prenom))." ".strtoupper($eleve_nom)."</b></p>\n";

		$critere_recherche=isset($_POST['critere_recherche']) ? $_POST['critere_recherche'] : "";
		$afficher_tous_les_resp=isset($_POST['afficher_tous_les_resp']) ? $_POST['afficher_tous_les_resp'] : "n";
		$critere_recherche=ereg_replace("[^a-zA-Z�������������ܽ�����������������_ -]", "", $critere_recherche);

		if($critere_recherche==""){
			$critere_recherche=substr($eleve_nom,0,3);
		}

		echo "<form enctype='multipart/form-data' name='form_rech' action='modify_eleve.php' method='post'>\n";

		echo "<input type='hidden' name='eleve_login' value='$eleve_login' />\n";
		echo "<input type='hidden' name='definir_resp' value='$definir_resp' />\n";
		echo "<p align='center'><input type='submit' name='filtrage' value='Afficher' /> les responsables dont le <b>nom</b> contient: ";
		echo "<input type='text' name='critere_recherche' value='$critere_recherche' />\n";
		echo "</p>\n";


		echo "<input type='hidden' name='afficher_tous_les_resp' id='afficher_tous_les_resp' value='n' />\n";
		echo "<p align='center'><input type='button' name='afficher_tous' value='Afficher tous les responsables' onClick=\"document.getElementById('afficher_tous_les_resp').value='y'; document.form_rech.submit();\" /></p>\n";
		echo "</form>\n";


		echo "<form enctype='multipart/form-data' action='modify_eleve.php' method='post'>\n";

		echo "<input type='hidden' name='eleve_login' value='$eleve_login' />\n";
		echo "<input type='hidden' name='definir_resp' value='$definir_resp' />\n";

		if($definir_resp==1){
			$pers_id=$eleve_no_resp1;
		}
		else{
			$pers_id=$eleve_no_resp2;
		}

		//$sql="SELECT DISTINCT rp.pers_id,rp.nom,rp.prenom,ra.* FROM responsables2 r, resp_adr ra, resp_pers rp WHERE r.pers_id=rp.pers_id AND rp.adr_id=ra.adr_id ORDER BY rp.nom, rp.prenom";
		//$sql="SELECT DISTINCT rp.pers_id,rp.nom,rp.prenom FROM resp_pers rp ORDER BY rp.nom, rp.prenom";
		$sql="SELECT DISTINCT rp.pers_id,rp.nom,rp.prenom FROM resp_pers rp";
		if($afficher_tous_les_resp!='y'){
			if($critere_recherche!=""){
				$sql.=" WHERE rp.nom like '%".$critere_recherche."%'";
			}
		}
		$sql.=" ORDER BY rp.nom, rp.prenom";
		if($afficher_tous_les_resp!='y'){
			$sql.=" LIMIT 20";
		}
		$call_resp=mysql_query($sql);
		$nombreligne = mysql_num_rows($call_resp);
		// si la table des responsables est non vide :
		if ($nombreligne != 0) {
			echo "<p align='center'><input type='submit' name='valider_choix_resp' value='Enregistrer' /></p>\n";
			echo "<table align='center' border='1'>\n";
			echo "<tr>\n";
			echo "<td><input type='radio' name='reg_resp".$definir_resp."' value='' /></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#96C8F0;'><b>Responsable l�gal $definir_resp</b></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#AAE6AA;'><b>Adresse</b></td>\n";
			echo "</tr>\n";

			$cpt=1;
			while($lig_resp=mysql_fetch_object($call_resp)){
				if($cpt%2==0){$couleur="silver";}else{$couleur="white";}
				echo "<tr>\n";
				echo "<td style='text-align:center; background-color:$couleur;'><input type='radio' name='reg_resp".$definir_resp."' value='$lig_resp->pers_id' ";
				if($lig_resp->pers_id==$pers_id){
					echo "checked ";
				}
				echo "/></td>\n";
				echo "<td style='text-align:center; background-color:$couleur;'><a href='../responsables/modify_resp.php?pers_id=$lig_resp->pers_id' target='_blank'>".strtoupper($lig_resp->nom)." ".ucfirst(strtolower($lig_resp->prenom))."</a></td>\n";
				echo "<td style='text-align:center; background-color:$couleur;'>";

				$sql="SELECT ra.* FROM resp_adr ra, resp_pers rp WHERE rp.pers_id='$lig_resp->pers_id' AND rp.adr_id=ra.adr_id";
				$res_adr=mysql_query($sql);
				if(mysql_num_rows($res_adr)==0){
					// L'adresse du responsable n'est pas d�finie:
					//echo "<font color='red'>L'adresse du responsable l�gal n'est pas d�finie</font>: <a href='../responsables/modify_resp.php?pers_id=$lig_resp->pers_id' target='_blank'>D�finir l'adresse du responsable l�gal</a>\n";
					echo "&nbsp;";
				}
				else{
					$chaine_adr1="";
					$lig_adr=mysql_fetch_object($res_adr);
					if("$lig_adr->adr1"!=""){$chaine_adr1.="$lig_adr->adr1, ";}
					if("$lig_adr->adr2"!=""){$chaine_adr1.="$lig_adr->adr2, ";}
					if("$lig_adr->adr3"!=""){$chaine_adr1.="$lig_adr->adr3, ";}
					if("$lig_adr->adr4"!=""){$chaine_adr1.="$lig_adr->adr4, ";}
					if("$lig_adr->cp"!=""){$chaine_adr1.="$lig_adr->cp, ";}
					if("$lig_adr->commune"!=""){$chaine_adr1.="$lig_adr->commune";}
					if("$lig_adr->pays"!=""){$chaine_adr1.=" (<i>$lig_adr->pays</i>)";}
					echo $chaine_adr1;
				}

				echo "</td>\n";
				echo "</tr>\n";
				$cpt++;
			}

			echo "</table>\n";
			echo "<p align='center'><input type='submit' name='valider_choix_resp' value='Enregistrer' /></p>\n";
		}
		else{
			echo "<p>Aucun responsable n'est d�fini.</p>\n";
		}

		echo "<p>Si le responsable l�gal ne figure pas dans la liste, vous pouvez l'ajouter � la base<br />\n";
		echo "(<i>apr�s avoir, le cas �ch�ant, sauvegard� cette fiche</i>)<br />\n";
		echo "en vous rendant dans [Gestion des bases-><a href='../responsables/index.php'>Gestion des responsables �l�ves</a>]</p>\n";

		echo "</form>\n";
	}
	else{
		// On valide l'enregistrement...
		// ... il faut le faire plus haut avant le header...
	}
	require("../lib/footer.inc.php");
	die();
}



//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";



if(isset($definir_etab)){
	if(!isset($valider_choix_etab)){
		echo "<p class=bold><a href=\"modify_eleve.php?eleve_login=$eleve_login\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>";

		echo "<form enctype='multipart/form-data' name='form_rech' action='modify_eleve.php' method='post'>\n";

		echo "<p>Choix de l'�tablissement d'origine pour <b>".ucfirst(strtolower($eleve_prenom))." ".strtoupper($eleve_nom)."</b></p>\n";

		echo "<input type='hidden' name='eleve_login' value='$eleve_login' />\n";
		echo "<input type='hidden' name='definir_etab' value='y' />\n";

		$sql="SELECT * FROM etablissements ORDER BY ville,nom";
		$call_etab=mysql_query($sql);
		$nombreligne = mysql_num_rows($call_etab);
		if ($nombreligne != 0) {
			echo "<p align='center'><input type='submit' name='valider_choix_etab' value='Valider' /></p>\n";
			echo "<table align='center' border='1'>\n";
			echo "<tr>\n";
			echo "<td><input type='radio' name='reg_etab' value='' /></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#96C8F0;'><b>RNE</b></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#96C8F0;'><b>Niveau</b></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#96C8F0;'><b>Type</b></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#AAE6AA;'><b>Nom</b></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#AAE6AA;'><b>Code postal</b></td>\n";
			echo "<td style='font-weight:bold; text-align:center; background-color:#AAE6AA;'><b>Ville</b></td>\n";
			echo "</tr>\n";

			$cpt=1;
			while($lig_etab=mysql_fetch_object($call_etab)){
				if($cpt%2==0){$couleur="silver";}else{$couleur="white";}
				echo "<tr>\n";
				echo "<td style='text-align:center; background-color:$couleur;'><input type='radio' name='reg_etab' value='$lig_etab->id' ";
				if($lig_etab->id==$id_etab){
					echo "checked ";
				}
				echo "/></td>";
				echo "<td style='text-align:center; background-color:$couleur;'><a href='../etablissements/modify_etab.php?id=$lig_etab->id' target='_blank'>$lig_etab->id</a></td>\n";
				echo "<td style='text-align:center; background-color:$couleur;'>$lig_etab->niveau</td>\n";
				echo "<td style='text-align:center; background-color:$couleur;'>$lig_etab->type</td>\n";
				echo "<td style='text-align:center; background-color:$couleur;'>$lig_etab->nom</td>\n";
				echo "<td style='text-align:center; background-color:$couleur;'>$lig_etab->cp</td>\n";
				echo "<td style='text-align:center; background-color:$couleur;'>$lig_etab->ville</td>\n";

				echo "</tr>\n";
				$cpt++;
			}

			echo "</table>\n";
			echo "<p align='center'><input type='submit' name='valider_choix_etab' value='Valider' /></p>\n";
		}
		else{
			echo "<p>Aucun �tablissement n'est d�fini</p>\n";
		}

		echo "<p>Si un �tablissement ne figure pas dans la liste, vous pouvez l'ajouter � la base<br />\n";
		echo "en vous rendant dans [Gestion des bases-><a href='../etablissements/index.php'>Gestion des �tablissements</a>]</p>\n";

		echo "</form>\n";
	}
	else{
		// On valide l'enregistrement...
		// ... il faut le faire plus haut avant le header...
	}
	require("../lib/footer.inc.php");
	die();
}



if ((isset($order_type)) and (isset($quelles_classes))) {
    echo "<p class=bold><a href=\"index.php?quelles_classes=$quelles_classes&amp;order_type=$order_type\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>";
} else {
    echo "<p class=bold><a href=\"index.php\"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>";
}


echo "<form enctype='multipart/form-data' name='form_rech' action='modify_eleve.php' method='post'>\n";

//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";

//echo "\$eleve_login=$eleve_login<br />";

//echo "<table border='1'>\n";
echo "<table>\n";
echo "<tr>\n";
echo "<td>\n";

echo "<table cellpadding='5'>\n";
echo "<tr>\n";

	$photo_largeur_max=150;
	$photo_hauteur_max=150;

	function redimensionne_image($photo){
		global $photo_largeur_max, $photo_hauteur_max;

		// prendre les informations sur l'image
		$info_image=getimagesize($photo);
		// largeur et hauteur de l'image d'origine
		$largeur=$info_image[0];
		$hauteur=$info_image[1];

		// calcule le ratio de redimensionnement
		$ratio_l=$largeur/$photo_largeur_max;
		$ratio_h=$hauteur/$photo_hauteur_max;
		$ratio=($ratio_l>$ratio_h)?$ratio_l:$ratio_h;

		// d�finit largeur et hauteur pour la nouvelle image
		$nouvelle_largeur=round($largeur/$ratio);
		$nouvelle_hauteur=round($hauteur/$ratio);

		return array($nouvelle_largeur, $nouvelle_hauteur);
	}


    if (isset($eleve_login)) {
        echo "<td>Identifiant GEPI * : </td>
        <td>".$eleve_login."<input type=hidden name='eleve_login' size=20 ";
        if ($eleve_login) echo "value='$eleve_login'";
        echo " /></td>\n";
    } else {
        echo "<td>Identifiant GEPI * : </td>
        <td><input type=text name=reg_login size=20 value=\"\" /></td>\n";
    }
    ?>
</tr>
<tr>
    <td>Nom * : </td>
    <td><input type=text name='reg_nom' size=20 <?php if (isset($eleve_nom)) { echo "value=\"".$eleve_nom."\"";}?> /></td>
</tr>
<tr>
    <td>Pr�nom * : </td>
    <td><input type=text name='reg_prenom' size=20 <?php if (isset($eleve_prenom)) { echo "value=\"".$eleve_prenom."\"";}?> /></td>
</tr>
<tr>
    <td>Email : </td>
    <td><input type=text name='reg_email' size=20 <?php if (isset($eleve_email)) { echo "value=\"".$eleve_email."\"";}?> /></td>
</tr>
<tr>
    <td>Identifiant National : </td>
    <?php
    echo "<td><input type='text' name='reg_no_nat' size='20' ";
    if (isset($reg_no_nat)) echo "value=\"".$reg_no_nat."\"";
    echo " /></td>\n";
    ?>
</tr>
<?php
    //echo "<tr><td>Num�ro GEP : </td><td><input type=text name='reg_no_gep' size=20 ";
    echo "<tr><td>Num�ro interne Sconet (<i>elenoet</i>) : </td><td><input type='text' name='reg_no_gep' size='20' ";
    if (isset($reg_no_gep)) echo "value=\"".$reg_no_gep."\"";
    echo " /></td>\n";


    ?>

</table>
<?php

//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";

if(isset($reg_no_gep)){
	/*
	$photo="../photos/eleves/".$reg_no_gep.".jpg";
	if(!file_exists($photo)){
		$photo="../photos/eleves/".sprintf("%05d",$reg_no_gep).".jpg";
	}
	*/
	// R�cup�ration du nom de la photo en tenant compte des histoires des z�ro 02345.jpg ou 2345.jpg
	$photo=nom_photo($reg_no_gep);

	echo "<td align='center'>\n";
	$temoin_photo="non";
	if("$photo"!=""){
		$photo="../photos/eleves/".$photo;
		if(file_exists($photo)){
			$temoin_photo="oui";
			//echo "<td>\n";
			echo "<div align='center'>\n";
			$dimphoto=redimensionne_image($photo);
			//echo '<img src="'.$photo.'" style="width: '.$dimphoto[0].'px; height: '.$dimphoto[1].'px; border: 0px; border-right: 3px solid #FFFFFF; float: left;" alt="" />';
			echo '<img src="'.$photo.'" style="width: '.$dimphoto[0].'px; height: '.$dimphoto[1].'px; border: 0px; border: 3px solid #FFFFFF;" alt="" />';
			//echo "</td>\n";
			//echo "<br />\n";
			echo "</div>\n";
			echo "<div style='clear:both;'></div>\n";
		}
	}
	echo "<div align='center'>\n";
	//echo "<span id='lien_photo' style='font-size:xx-small;'>";
	echo "<div id='lien_photo' style='border: 1px solid black; padding: 5px; margin: 5px;'>";
	echo "<a href='#' onClick=\"document.getElementById('div_upload_photo').style.display='';document.getElementById('lien_photo').style.display='none';return false;\">";
	if($temoin_photo=="oui"){
		//echo "Modifier le fichier photo</a>\n";
		echo "Modifier le fichier photo</a>\n";
	}
	else{
		//echo "Envoyer un fichier photo</a>\n";
		echo "Envoyer<br />un fichier<br />photo</a>\n";
	}
	//echo "</span>\n";
	echo "</div>\n";
	echo "<div id='div_upload_photo' style='display:none;'>";
	echo "<input type='file' name='filephoto' />\n";
	if("$photo"!=""){
		if(file_exists($photo)){
			echo "<br />\n";
			echo "<input type='checkbox' name='suppr_filephoto' value='y' /> Supprimer la photo existante\n";
		}
	}
	echo "</div>\n";
	echo "</div>\n";
	echo "</td>\n";
}


// Lien vers les inscriptions � des groupes:
if(isset($eleve_login)){
	echo "<td>\n";
	// style='border: 1px solid black; text-align:center;'

	//echo "\$reg_regime=$reg_regime<br />";
	//echo "\$reg_doublant=$reg_doublant<br />";

	//=========================
	// AJOUT: boireaus 20071107
	echo "<table style='border-collaspe: collapse; border: 1px solid black;' align='center'>\n";
	echo "<tr>\n";
	echo "<th>R�gime: </th>\n";
	echo "<td style='text-align: center; border: 0px;'>I-ext<br /><input type='radio' name='reg_regime' value='i-e' ";
	if ($reg_regime == 'i-e') {echo " checked";}
	echo " /></td>\n";
	echo "<td style='text-align: center; border: 0px; border-left: 1px solid #AAAAAA;'>Int<br/><input type='radio' name='reg_regime' value='int.' ";
	if ($reg_regime == 'int.') {echo " checked";}
	echo " /></td>\n";
	echo "<td style='text-align: center; border: 0px; border-left: 1px solid #AAAAAA;'>D/P<br/><input type='radio' name='reg_regime' value='d/p' ";
	if ($reg_regime == 'd/p') {echo " checked";}
	echo " /></td>\n";
	echo "<td style='text-align: center; border: 0px; border-left: 1px solid #AAAAAA;'>Ext<br/><input type='radio' name='reg_regime' value='ext.' ";
	if ($reg_regime == 'ext.') {echo " checked";}
	echo " /></td></tr>\n";
	echo "</table>\n";

	echo "<br />\n";
	//echo "<tr><td>&nbsp;</td></tr>\n";

	echo "<table style='border-collaspe: collapse; border: 1px solid black;' align='center'>\n";
	echo "<tr>\n";
	echo "<th>Redoublant: </th>\n";
	echo "<td style='text-align: center; border: 0px;'>O<br /><input type='radio' name='reg_doublant' value='R' ";
	if ($reg_doublant == 'R') {echo " checked";}
	echo " /></td>\n";
	echo "<td style='text-align: center; border: 0px; border-left: 1px solid #AAAAAA;'>N<br /><input type='radio' name='reg_doublant' value='-' ";
	if ($reg_doublant == '-') {echo " checked";}
	echo " /></td></tr>\n";
	echo "</table>\n";

	echo "<br />\n";
	//=========================

	echo "<div style='border: 1px solid black;'>\n";
	$sql="SELECT jec.id_classe,c.classe, jec.periode FROM j_eleves_classes jec, classes c WHERE jec.login='$eleve_login' AND jec.id_classe=c.id GROUP BY jec.id_classe ORDER BY jec.periode";
	$res_grp1=mysql_query($sql);
	if(mysql_num_rows($res_grp1)==0){
		echo "L'�l�ve n'est encore associ� � aucune classe.";
	}
	else{
		while($lig_classe=mysql_fetch_object($res_grp1)){
			//echo "Enseignements suivis en <a href='../classes/eleve_options.php?login_eleve=$eleve_login&amp;id_classe=$lig_classe->id_classe' target='_blank'>$lig_classe->classe</a><br />\n";
			echo "<a href='../classes/eleve_options.php?login_eleve=$eleve_login&amp;id_classe=$lig_classe->id_classe&amp;quitter_la_page=y' target='_blank'>Enseignements suivis</a> en $lig_classe->classe\n";
			echo "<br />\n";

			//echo "D�finir/consulter <a href='../classes/classes_const.php?id_classe=$lig_classe->id_classe&amp;quitter_la_page=y' target='_blank'>le r�gime, le professeur principal, le CPE responsable</a> de l'�l�ve.\n";
			//echo "<br />\n";
		}
	}
	echo "</div>\n";
	echo "</td>\n";
}

echo "</tr>\n";
echo "</table>\n";

//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";

if (($reg_no_gep == '') and (isset($eleve_login))) {
   //echo "<font color=red>ATTENTION : Cet �l�ve ne poss�de pas de num�ro GEP. Vous ne pourrez pas importer les absences � partir des fichiers GEP pour cet �l�ves.</font>\n";
   echo "<font color='red'>ATTENTION : Cet �l�ve ne poss�de pas de num�ro interne Sconet (<i>elenoet</i>). Vous ne pourrez pas importer les absences � partir des fichiers GEP/Sconet pour cet �l�ves.</font>\n";

	$sql="select value from setting where name='import_maj_xml_sconet'";
	$test_sconet=mysql_query($sql);
	if(mysql_num_rows($test_sconet)>0){
		$lig_tmp=mysql_fetch_object($test_sconet);
		if($lig_tmp->value=='1'){
			echo "<br />";
			echo "<font color='red'>Vous ne pourrez pas non plus effectuer les mises � jour de ses informations depuis Sconet<br />(<i>l'ELENOET et l'ELE_ID ne correspondront pas aux donn�es de Sconet</i>).</font>\n";
		}
	}
}
//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";

?>
<center>
<!--table border = '1' CELLPADDING = '5'-->
<table class='boireaus' cellpadding='5'>
<tr><td><div class='norme'>Sexe : <br />
<?php
if (!(isset($eleve_sexe))) $eleve_sexe="M";
?>
<input type=radio name=reg_sexe value=M <?php if ($eleve_sexe == "M") { echo "CHECKED" ;} ?> /> Masculin
<input type=radio name=reg_sexe value=F <?php if ($eleve_sexe == "F") { echo "CHECKED" ;} ?> /> F�minin
</div></td><td><div class='norme'>
Date de naissance (respecter format 00/00/0000) : <br />
Jour <input type=text name=birth_day size=2 value=<?php if (isset($eleve_naissance_jour)) echo $eleve_naissance_jour;?> />
Mois<input type=text name=birth_month size=2 value=<?php if (isset($eleve_naissance_mois)) echo $eleve_naissance_mois;?> />
Ann�e<input type=text name=birth_year size=4 value=<?php if (isset($eleve_naissance_annee)) echo $eleve_naissance_annee;?> />
</div></td></tr>
</table></center>

<p><b>Remarques</b> :
<br />- la modification du r�gime de l'�l�ve (demi-pensionnaire, interne, ...) s'effectue dans le module de gestion des classes !
<br />- Les champs * sont obligatoires.</p>
<?php

echo "<input type=hidden name=is_posted value=\"1\" />\n";
if (isset($order_type)) echo "<input type=hidden name=order_type value=\"$order_type\" />\n";
if (isset($quelles_classes)) echo "<input type=hidden name=quelles_classes value=\"$quelles_classes\" />\n";
if (isset($eleve_login)) echo "<input type=hidden name=eleve_login value=\"$eleve_login\" />\n";
if (isset($mode)) echo "<input type=hidden name=mode value=\"$mode\" />\n";
echo "<center><input type=submit value=Enregistrer /></center>\n";
echo "</form>\n";

//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";


if(isset($eleve_login)){
	//$sql="SELECT rp.nom,rp.prenom,rp.pers_id,ra.* FROM responsables2 r, resp_adr ra, resp_pers rp WHERE r.resp_legal='1' AND r.pers_id=rp.pers_id AND rp.adr_id=ra.adr_id ORDER BY rp.nom, rp.prenom";
	//$sql="SELECT DISTINCT rp.pers_id,rp.nom,rp.prenom,ra.* FROM responsables2 r, resp_adr ra, resp_pers rp WHERE r.pers_id=rp.pers_id AND rp.adr_id=ra.adr_id ORDER BY rp.nom, rp.prenom";
	$sql="SELECT DISTINCT rp.pers_id,rp.nom,rp.prenom FROM resp_pers rp ORDER BY rp.nom, rp.prenom";
	$call_resp=mysql_query($sql);
	$nombreligne = mysql_num_rows($call_resp);
	// si la table des responsables est non vide :
	if ($nombreligne != 0) {

		echo "<br />\n";
		echo "<hr />\n";
		echo "<h3>Envoi des bulletins par voie postale</h3>\n";

		//echo "\$eleve_no_resp1=$eleve_no_resp1<br />\n";

		echo "<i>Si vous n'envoyez pas les bulletins scolaires par voie postale, vous pouvez ignorer cette rubrique.</i>";
		echo "<br />\n<br />\n";

		$temoin_tableau="";
		$chaine_adr1='';
		// Lorsque le $eleve_no_resp1 est non num�rique (cas sans sconet), on a p000000012 et il consid�re que p000000012==0
		// Il faut comparer des chaines de caract�res.
		//if($eleve_no_resp1==0){
		if("$eleve_no_resp1"=="0"){
			// Le responsable 1 n'est pas d�fini:
			echo "<p>Le responsable l�gal 1 n'est pas d�fini: <a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=1'>D�finir le responsable l�gal 1</a></p>\n";
		}
		else{
			$sql="SELECT nom,prenom FROM resp_pers WHERE pers_id='$eleve_no_resp1'";
			$res_resp=mysql_query($sql);
			if(mysql_num_rows($res_resp)==0){
				// Bizarre: Le responsable 1 n'est pas d�fini:
				echo "<p>Le responsable l�gal 1 n'est pas d�fini: <a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=1'>D�finir le responsable l�gal 1</a></p>\n";
			}
			else{
				$temoin_tableau="oui";
				$lig_resp=mysql_fetch_object($res_resp);
				echo "<table border='0'>\n";
				echo "<tr valign='top'>\n";
				echo "<td rowspan='2'>Le responsable l�gal 1 est: </td>\n";
				echo "<td><a href='../responsables/modify_resp.php?pers_id=$eleve_no_resp1' target='_blank'>".ucfirst(strtolower($lig_resp->prenom))." ".strtoupper($lig_resp->nom)."</a></td>\n";
				//echo "<td><a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=1'>Modifier l'association</a></td>\n";
				echo "<td><a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=1'>Modifier le responsable</a></td>\n";
				echo "</tr>\n";

				echo "<tr valign='top'>\n";
				// La 1�re colonne est dans le rowspan

				$sql="SELECT ra.* FROM resp_adr ra, resp_pers rp WHERE rp.pers_id='$eleve_no_resp1' AND rp.adr_id=ra.adr_id";
				$res_adr=mysql_query($sql);
				if(mysql_num_rows($res_adr)==0){
					// L'adresse du responsable 1 n'est pas d�finie:
					echo "<td colspan='2'>\n";
					echo "L'adresse du responsable l�gal 1 n'est pas d�finie: <a href='../responsables/modify_resp.php?pers_id=$eleve_no_resp1' target='_blank'>D�finir l'adresse du responsable l�gal 1</a>\n";
					echo "</td>\n";
					$adr_id_1er_resp="";
				}
				else{
					echo "<td>\n";
					$lig_adr=mysql_fetch_object($res_adr);
					$adr_id_1er_resp=$lig_adr->adr_id;
					if("$lig_adr->adr1"!=""){$chaine_adr1.="$lig_adr->adr1, ";}
					if("$lig_adr->adr2"!=""){$chaine_adr1.="$lig_adr->adr2, ";}
					if("$lig_adr->adr3"!=""){$chaine_adr1.="$lig_adr->adr3, ";}
					if("$lig_adr->adr4"!=""){$chaine_adr1.="$lig_adr->adr4, ";}
					if("$lig_adr->cp"!=""){$chaine_adr1.="$lig_adr->cp, ";}
					if("$lig_adr->commune"!=""){$chaine_adr1.="$lig_adr->commune";}
					if("$lig_adr->pays"!=""){$chaine_adr1.=" (<i>$lig_adr->pays</i>)";}
					echo $chaine_adr1;
					echo "</td>\n";
					echo "<td>\n";
					echo "<a href='../responsables/modify_resp.php?pers_id=$eleve_no_resp1' target='_blank'>Modifier l'adresse du responsable</a>\n";
					echo "</td>\n";
				}
				echo "</tr>\n";
				//echo "</table>\n";
			}
		}





		$chaine_adr2='';
		//if($eleve_no_resp2==0){
		if("$eleve_no_resp2"=="0"){
			// Le responsable 2 n'est pas d�fini:
			if($temoin_tableau=="oui"){echo "</table>\n";$temoin_tableau="non";}

			echo "<p>Le responsable l�gal 2 n'est pas d�fini: <a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=2'>D�finir le responsable l�gal 2</a></p>\n";
		}
		else{
			$sql="SELECT nom,prenom FROM resp_pers WHERE pers_id='$eleve_no_resp2'";
			$res_resp=mysql_query($sql);
			if(mysql_num_rows($res_resp)==0){
				// Bizarre: Le responsable 2 n'est pas d�fini:
				if($temoin_tableau=="oui"){echo "</table>\n";$temoin_tableau="non";}

				echo "<p>Le responsable l�gal 2 n'est pas d�fini: <a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=2'>D�finir le responsable l�gal 2</a></p>\n";
			}
			else{
				$lig_resp=mysql_fetch_object($res_resp);

				if($temoin_tableau!="oui"){
					echo "<table border='0'>\n";
					$temoin_tableau="oui";
				}
				echo "<tr valign='top'>\n";
				echo "<td rowspan='2'>Le responsable l�gal 2 est: </td>\n";
				echo "<td><a href='../responsables/modify_resp.php?pers_id=$eleve_no_resp2' target='_blank'>".ucfirst(strtolower($lig_resp->prenom))." ".strtoupper($lig_resp->nom)."</a></td>\n";
				//echo "<td><a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=2'>Modifier l'association</a></td>\n";
				echo "<td><a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_resp=2'>Modifier le responsable</a></td>\n";
				echo "</tr>\n";

				echo "<tr valign='top'>\n";
				// La 1�re colonne est dans le rowspan

				$sql="SELECT ra.* FROM resp_adr ra, resp_pers rp WHERE rp.pers_id='$eleve_no_resp2' AND rp.adr_id=ra.adr_id";
				$res_adr=mysql_query($sql);
				if(mysql_num_rows($res_adr)==0){
					// L'adresse du responsable 2 n'est pas d�finie:
					echo "<td colspan='2'>\n";
					echo "L'adresse du responsable l�gal 2 n'est pas d�finie: <a href='../responsables/modify_resp.php?pers_id=$eleve_no_resp2' target='_blank'>D�finir l'adresse du responsable l�gal 2</a>\n";
					echo "</td>\n";
				}
				else{
					echo "<td>\n";
					$lig_adr=mysql_fetch_object($res_adr);

					if(($lig_adr->adr_id!="")&&($lig_adr->adr_id!=$adr_id_1er_resp)){
						if("$lig_adr->adr1"!=""){$chaine_adr2.="$lig_adr->adr1, ";}
						if("$lig_adr->adr2"!=""){$chaine_adr2.="$lig_adr->adr2, ";}
						if("$lig_adr->adr3"!=""){$chaine_adr2.="$lig_adr->adr3, ";}
						if("$lig_adr->adr4"!=""){$chaine_adr2.="$lig_adr->adr4, ";}
						if("$lig_adr->cp"!=""){$chaine_adr2.="$lig_adr->cp, ";}
						if("$lig_adr->commune"!=""){$chaine_adr2.="$lig_adr->commune";}
						if("$lig_adr->pays"!=""){$chaine_adr2.=" (<i>$lig_adr->pays</i>)";}

						if("$chaine_adr1"=="$chaine_adr2"){
							echo "$chaine_adr2<br />\n<span style='color: red;'>Les adresses sont identiques, mais sont enregistr�es sous deux identifiants diff�rents (<i>$adr_id_1er_resp et $lig_adr->adr_id</i>); vous devriez modifier l'adresse pour pointer vers le m�me identifiant d'adresse.</span>";
						}
						else{
							echo "$chaine_adr2";
						}
					}
					else{
						echo "M�me adresse.";
					}
					echo "</td>\n";
					echo "<td>\n";
					echo "<a href='../responsables/modify_resp.php?pers_id=$eleve_no_resp2' target='_blank'>Modifier l'adresse du responsable</a>\n";
					echo "</td>\n";
				}
				echo "</tr>\n";
				//echo "</table>\n";
			}
		}
		if($temoin_tableau=="oui"){echo "</table>\n";$temoin_tableau="non";}



		if("$chaine_adr2"!=""){
			if("$chaine_adr1"!=""){
				if("$chaine_adr1"!="$chaine_adr2"){
					echo "<p><b>Les adresses des deux responsables l�gaux ne sont pas identiques. Par cons�quent, le bulletin sera envoy� aux deux responsables l�gaux.</b></p>\n";
				}
				else{
					echo "<p><b>Les adresses des deux responsables l�gaux sont identiques. Par cons�quent, le bulletin ne sera envoy� qu'� la premi�re adresse.</b></p>\n";
				}
			}
			else{
				echo "<p><b>Le bulletin ne sera envoy� qu'au deuxi�me responsable.</b></p>\n";
			}
		}
		else{
			if("$chaine_adr1"!=""){
				echo "<p><b>Le bulletin ne sera envoy� qu'au premier responsable.</b></p>\n";
			}
			else{
				echo "<p><b>Aucune adresse n'est renseign�e. Le bulletin ne pourra pas �tre envoy�.</b></p>\n";
			}
		}


		//if(($eleve_no_resp1==0)||($eleve_no_resp2==0)){
		if(("$eleve_no_resp1"=="0")||("$eleve_no_resp2"=="0")){
			echo "<p>Si le responsable l�gal ne figure pas dans la liste, vous pouvez l'ajouter � la base<br />\n";
			echo "(<i>apr�s avoir, le cas �ch�ant, sauvegard� cette fiche</i>)<br />\n";
			echo "en vous rendant dans [Gestion des bases-><a href='../responsables/index.php'>Gestion des responsables �l�ves</a>]</p>\n";
		}
	}
}



if(isset($eleve_login)){

	echo "<br />\n";
	echo "<hr />\n";

	echo "<h3>Etablissement d'origine</h3>\n";

	$sql="SELECT * FROM j_eleves_etablissements WHERE id_eleve='$eleve_login'";
	$res_etab=mysql_query($sql);
	if(mysql_num_rows($res_etab)==0) {
		echo "<p>L'�tablissement d'origine de l'�l�ve n'est pas renseign�.<br />\n";
		echo "<a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_etab=y'>Renseigner l'�tablissement d'origine</a>";
		echo "</p>\n";
	}
	else{
		$lig_etab=mysql_fetch_object($res_etab);

		if("$lig_etab->id_etablissement"==""){
			echo "<p><a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_etab=y'>D�finir l'�tablissement d'origine</a>";
			echo "</p>\n";
		}
		else{
			$sql="SELECT * FROM etablissements WHERE id='$lig_etab->id_etablissement'";
			$res_etab2=mysql_query($sql);
			if(mysql_num_rows($res_etab2)==0) {
				echo "<p>L'association avec l'identifiant d'�tablissement existe (<i>$lig_etab->id_etablissement</i>), mais les informations correspondantes n'existent pas dans la table 'etablissement'.<br />\n";

				echo "<a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_etab=y'>Modifier l'�tablissement d'origine</a>";
				echo "</p>\n";
			}
			else{
				echo "<p>L'�tablissement d'origine de l'�l�ve est:<br />\n";
				$lig_etab2=mysql_fetch_object($res_etab2);
				echo "&nbsp;&nbsp;&nbsp;".ucfirst(strtolower($lig_etab2->niveau))." ".$lig_etab2->type." ".$lig_etab2->nom.", ".$lig_etab2->cp.", ".$lig_etab2->ville." (<i>$lig_etab->id_etablissement</i>)<br />\n";
				echo "<a href='".$_SERVER['PHP_SELF']."?eleve_login=$eleve_login&amp;definir_etab=y'>Modifier l'�tablissement d'origine</a>";
				echo "</p>\n";
			}
		}
	}
	echo "<p><br /></p>\n";
}

require("../lib/footer.inc.php");
?>