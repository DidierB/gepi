<?php
/*
 * $Id$
 *
 * Fichier de mise � jour de la version 1.5.3 � la version 1.5.4
 * Le code PHP pr�sent ici est ex�cut� tel quel.
 * Pensez � conserver le code parfaitement compatible pour une application
 * multiple des mises � jour. Toute modification ne doit �tre r�alis�e qu'apr�s
 * un test pour s'assurer qu'elle est n�cessaire.
 *
 * Le r�sultat de la mise � jour est du html pr�format�. Il doit �tre concat�n�
 * dans la variable $result, qui est d�j� initialis�.
 *
 * Exemple : $result .= "<font color='gree'>Champ XXX ajout� avec succ�s</font>";
 */

$result .= "<br /><br /><b>Mise � jour vers la version 1.5.4" . $rc . $beta . " :</b><br />";

//===================================================
//
//deja mis dans 153_to_1531
//
//$champ_courant=array('nom1', 'prenom1', 'nom2', 'prenom2');
//for($loop=0;$loop<count($champ_courant);$loop++) {
//	$result .= "&nbsp;->Extension � 50 caract�res du champ '$champ_courant[$loop]' de la table 'responsables'<br />";
//	$query = mysql_query("ALTER TABLE responsables CHANGE $champ_courant[$loop] $champ_courant[$loop] VARCHAR( 50 ) NOT NULL;");
//	if ($query) {
//			$result .= "<font color=\"green\">Ok !</font><br />";
//	} else {
//			$result .= "<font color=\"red\">Erreur</font><br />";
//	}
//}
//
//$champ_courant=array('nom', 'prenom');
//for($loop=0;$loop<count($champ_courant);$loop++) {
//	$result .= "&nbsp;->Extension � 50 caract�res du champ '$champ_courant[$loop]' de la table 'resp_pers'<br />";
//	$query = mysql_query("ALTER TABLE resp_pers CHANGE $champ_courant[$loop] $champ_courant[$loop] VARCHAR( 50 ) NOT NULL;");
//	if ($query) {
//			$result .= "<font color=\"green\">Ok !</font><br />";
//	} else {
//			$result .= "<font color=\"red\">Erreur</font><br />";
//	}
//}
//===================================================


// Ajout de param�tres pour l'import d'attributs depuis CAS
// Param�tre d'activation de la synchro � la vol�e Scribe NG

$req_test=mysql_query("SELECT value FROM setting WHERE name = 'cas_attribut_prenom'");
$res_test=mysql_num_rows($req_test);
if ($res_test==0){
  $result_inter = traite_requete("INSERT INTO setting VALUES ('cas_attribut_prenom', '');");
  if ($result_inter == '') {
    $result.="<font color=\"green\">D�finition du param�tre cas_attribut_prenom : Ok !</font><br />";
  } else {
    $result.="<font color=\"red\">D�finition du param�tre cas_attribut_prenom : Erreur !</font><br />";
  }
} else {
  $result .= "<font color=\"blue\">Le param�tre cas_attribut_prenom existe d�j� dans la table setting.</font><br />";
}

$req_test=mysql_query("SELECT value FROM setting WHERE name = 'cas_attribut_nom'");
$res_test=mysql_num_rows($req_test);
if ($res_test==0){
  $result_inter = traite_requete("INSERT INTO setting VALUES ('cas_attribut_nom', '');");
  if ($result_inter == '') {
    $result.="<font color=\"green\">D�finition du param�tre cas_attribut_nom : Ok !</font><br />";
  } else {
    $result.="<font color=\"red\">D�finition du param�tre cas_attribut_nom : Erreur !</font><br />";
  }
} else {
  $result .= "<font color=\"blue\">Le param�tre cas_attribut_nom existe d�j� dans la table setting.</font><br />";
}

$req_test=mysql_query("SELECT value FROM setting WHERE name = 'cas_attribut_email'");
$res_test=mysql_num_rows($req_test);
if ($res_test==0){
  $result_inter = traite_requete("INSERT INTO setting VALUES ('cas_attribut_email', '');");
  if ($result_inter == '') {
    $result.="<font color=\"green\">D�finition du param�tre cas_attribut_email : Ok !</font><br />";
  } else {
    $result.="<font color=\"red\">D�finition du param�tre cas_attribut_email : Erreur !</font><br />";
  }
} else {
  $result .= "<font color=\"blue\">Le param�tre cas_attribut_email existe d�j� dans la table setting.</font><br />";
}


//===================================================
$result .= "<br /><br /><b>Ajout d'une table modeles_grilles_pdf :</b><br />";
$test = sql_query1("SHOW TABLES LIKE 'modeles_grilles_pdf'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS modeles_grilles_pdf (
		id_modele INT(11) NOT NULL auto_increment,
		login varchar(50) NOT NULL default '',
		nom_modele varchar(255) NOT NULL,
		par_defaut ENUM('y','n') DEFAULT 'n',
		PRIMARY KEY (id_modele)
		);");
	if ($result_inter == '') {
		$result .= "<font color=\"green\">SUCCES !</font><br />";
	}
	else {
		$result .= "<font color=\"red\">ECHEC !</font><br />";
	}
} else {
		$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
}

$result .= "<br /><br /><b>Ajout d'une table modeles_grilles_pdf_valeurs :</b><br />";
$test = sql_query1("SHOW TABLES LIKE 'modeles_grilles_pdf_valeurs'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS modeles_grilles_pdf_valeurs (
		id_modele INT(11) NOT NULL,
		nom varchar(255) NOT NULL default '',
		valeur varchar(255) NOT NULL,
		INDEX id_modele_champ (id_modele, nom)
		);");
	if ($result_inter == '') {
		$result .= "<font color=\"green\">SUCCES !</font><br />";
	}
	else {
		$result .= "<font color=\"red\">ECHEC !</font><br />";
	}
} else {
		$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
}

?>
