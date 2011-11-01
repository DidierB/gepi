<?php
/**
 * Fichier de mise � jour de la version 1.5.5 � la version 1.5.6
 * 
 * $Id$
 *
 * Le code PHP pr�sent ici est ex�cut� tel quel.
 * Pensez � conserver le code parfaitement compatible pour une application
 * multiple des mises � jour. Toute modification ne doit �tre r�alis�e qu'apr�s
 * un test pour s'assurer qu'elle est n�cessaire.
 *
 * Le r�sultat de la mise � jour est du html pr�format�. Il doit �tre concat�n�
 * dans la variable $result, qui est d�j� initialis�.
 *
 * Exemple : $result .= msj_ok("Champ XXX ajout� avec succ�s");
 *
 * @copyright Copyright 2001, 2011 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
 * @license GNU/GPL,
 * @package General
 * @subpackage mise_a jour
 * @see msj_ok()
 * @see msj_erreur()
 * @see msj_present()
 */

$result .= "<h3 class='titreMaJ'>Mise � jour vers la version 1.5.6" . $rc . $beta . " :</h3>";

//===================================================
/*

// Exemples de sections:

// Ajout d'un champ dans setting

$req_test=mysql_query("SELECT value FROM setting WHERE name = 'cas_attribut_prenom'");
$res_test=mysql_num_rows($req_test);
if ($res_test==0){
  $result_inter = traite_requete("INSERT INTO setting VALUES ('cas_attribut_prenom', '');");
  if ($result_inter == '') {
    $result.=msj_ok("D�finition du param�tre cas_attribut_prenom : Ok !");
  } else {
    $result.=msj_erreur("D�finition du param�tre cas_attribut_prenom : Erreur !");
  }
} else {
  $result .= msj_present("Le param�tre cas_attribut_prenom existe d�j� dans la table setting.");
}

//===================================================

// Ajout d'une table

$result .= "<br /><br /><strong>Ajout d'une table modeles_grilles_pdf :</strong><br />";
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
		$result .= msj_ok("SUCCES !");
	}
	else {
		$result .= msj_erreur("ECHEC !");
	}
} else {
		$result .= msj_present("La table existe d�j�");
}

//===================================================

// Ajout d'un champ � une table

$result .= "&nbsp;->Ajout d'un champ id_lieu � la table 'a_types'<br />";
$test_date_decompte=mysql_num_rows(mysql_query("SHOW COLUMNS FROM a_types LIKE 'id_lieu';"));
if ($test_date_decompte>0) {
	$result .= msj_present("Le champ existe d�j�.");
}
else {
	$query = mysql_query("ALTER TABLE a_types ADD id_lieu INTEGER(11) COMMENT 'cle etrangere du lieu ou se trouve l\'eleve' AFTER commentaire,
       ADD INDEX a_types_FI_1 (id_lieu),
       ADD CONSTRAINT a_types_FK_1
		FOREIGN KEY (id_lieu)
		REFERENCES a_lieux (id)
		ON DELETE SET NULL ;");
	if ($query) {
			$result .= msj_ok("Ok !");
	} else {
			$result .= msj_erreur("Erreur");
	}
}

*/
//===================================================

$result .= "<strong>Ajout d'une table 'temp_abs_import' :</strong><br />";
$test = sql_query1("SHOW TABLES LIKE 'temp_abs_import'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS temp_abs_import (
		id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		login varchar(50) NOT NULL default '',
		elenoet varchar(50) NOT NULL default '',
		libelle varchar(50) NOT NULL default '',
		nbAbs INT(11) NOT NULL default '0',
		nbNonJustif INT(11) NOT NULL default '0',
		nbRet INT(11) NOT NULL default '0',
		UNIQUE KEY elenoet (elenoet));");
	if ($result_inter == '') {
		$result .= msj_ok("SUCCES !");
	}
	else {
		$result .= msj_erreur("ECHEC !");
	}
} else {
		$result .= msj_present("La table existe d�j�");
}

$req_test=mysql_query("SELECT value FROM setting WHERE name = 'utiliserMenuBarre'");
$res_test=mysql_num_rows($req_test);
if ($res_test==0){
  $result_inter = traite_requete("INSERT INTO setting VALUES ('utiliserMenuBarre', 'yes');");
  if ($result_inter == '') {
    $result.=msj_ok("D�finition du param�tre utiliserMenuBarre : Ok !");
  } else {
    $result.=msj_erreur("D�finition du param�tre utiliserMenuBarre : Erreur !");
  }
} else {
  $result .= msj_present("Le param�tre utiliserMenuBarre existe d�j� dans la table setting.");
}


//===================================================
?>
