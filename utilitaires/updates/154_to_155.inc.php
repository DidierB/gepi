<?php
/*
 * $Id$
 *
 * Fichier de mise � jour de la version 1.5.4 � la version 1.5.5
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

$result .= "<br /><br /><b>Mise � jour vers la version 1.5.5" . $rc . $beta . " :</b><br />";

//===================================================
/*

// Exemples de sections:

// Ajout d'un champ dans setting

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

//===================================================

// Ajout d'une table

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

//===================================================

// Ajout d'un champ � une table

$result .= "&nbsp;->Ajout d'un champ id_lieu � la table 'a_types'<br />";
$test_date_decompte=mysql_num_rows(mysql_query("SHOW COLUMNS FROM a_types LIKE 'id_lieu';"));
if ($test_date_decompte>0) {
	$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
}
else {
	$query = mysql_query("ALTER TABLE a_types ADD id_lieu INTEGER(11) COMMENT 'cle etrangere du lieu ou se trouve l\'eleve' AFTER commentaire,
       ADD INDEX a_types_FI_1 (id_lieu),
       ADD CONSTRAINT a_types_FK_1
		FOREIGN KEY (id_lieu)
		REFERENCES a_lieux (id)
		ON DELETE SET NULL ;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

*/
//===================================================

$result .= "<br /><br /><b>Ajout d'une table 'j_groupes_visibilite' :</b><br />";
$test = sql_query1("SHOW TABLES LIKE 'j_groupes_visibilite'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS j_groupes_visibilite (
			id INT(11) NOT NULL auto_increment,
			id_groupe INT(11) NOT NULL,
			domaine varchar(255) NOT NULL default '',
			visible varchar(255) NOT NULL default '',
			PRIMARY KEY (id),
			INDEX id_groupe_domaine (id_groupe, domaine)
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

//===================================================

$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_documents LIKE 'visible';"));
if ($test_champ>0) {
	$result .= "&nbsp;->Ajout d'un champ visible � la table 'ct_documents'<br />";
	$query = mysql_query("ALTER TABLE ct_documents DROP visible;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

$result .= "&nbsp;->Ajout d'un champ 'visible_eleve_parent' � la table 'ct_documents'<br />";
$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_documents LIKE 'visible_eleve_parent';"));
if ($test_champ>0) {
	$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
}
else {
	$query = mysql_query("ALTER TABLE ct_documents ADD visible_eleve_parent BOOLEAN DEFAULT true COMMENT 'Visibilit� �l�ve/parent du document joint' AFTER emplacement;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_devoirs_documents LIKE 'visible';"));
if ($test_champ>0) {
	$result .= "&nbsp;->Suppression du champ 'visible' de la table 'ct_devoirs_documents'<br />";
	$query = mysql_query("ALTER TABLE ct_devoirs_documents DROP visible;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

$result .= "&nbsp;->Ajout d'un champ 'visible_eleve_parent' � la table 'ct_devoirs_documents'<br />";
$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_devoirs_documents LIKE 'visible_eleve_parent';"));
if ($test_champ>0) {
	$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
}
else {
	$query = mysql_query("ALTER TABLE ct_devoirs_documents ADD visible_eleve_parent BOOLEAN DEFAULT true COMMENT 'Visibilit� �l�ve/parent du document joint' AFTER emplacement;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

//===================================================

$result .= "&nbsp;->Ajout d'un champ 'date_visibilite_eleve' � la table 'ct_devoirs_entry'<br />";
$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM ct_devoirs_entry LIKE 'date_visibilite_eleve';"));
if ($test_champ>0) {
	$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
}
else {
	$query = mysql_query("ALTER TABLE ct_devoirs_entry ADD date_visibilite_eleve TIMESTAMP NOT NULL default now() COMMENT 'Timestamp precisant quand les devoirs sont portes a la conaissance des eleves' AFTER id_sequence;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

//===================================================

$result .= "<br /><br /><b>Mots de passe :</b><br />";
$result .= "&nbsp;->Ajout d'un champ 'salt' � la table 'utilisateur' et allongement du champs password<br />";
$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM utilisateurs LIKE 'salt';"));
if ($test_champ>0) {
	$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
}
else {
	$query = mysql_query("ALTER TABLE utilisateurs ADD salt varchar(128) COMMENT 'sel pour le hmac du mot de passe' AFTER password;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
	$query = mysql_query("ALTER TABLE utilisateurs MODIFY password varchar(128);");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

//===================================================

$result .= "<br /><br /><b>Messagerie :</b><br />";
$result .= "&nbsp;->Modification du champ 'destinataires' de la table 'messages' en 'statuts_destinataires'<br />";
$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM messages LIKE 'statuts_destinataires';"));
if ($test_champ>0) {
	$result .= "<font color=\"blue\">Le champ est d�j� modifi�.</font><br />";
}
else {
	$query = mysql_query("ALTER TABLE messages CHANGE destinataires statuts_destinataires VARCHAR( 10 ) NOT NULL DEFAULT '';");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

$result .= "&nbsp;->Ajout d'un champ 'login_destinataire' � la table 'messages'<br />";
$test_champ=mysql_num_rows(mysql_query("SHOW COLUMNS FROM messages LIKE 'login_destinataire';"));
if ($test_champ>0) {
	$result .= "<font color=\"blue\">Le champ existe d�j�.</font><br />";
}
else {
	$query = mysql_query("ALTER TABLE messages ADD login_destinataire VARCHAR( 50 ) NOT NULL default '' AFTER statuts_destinataires, ADD INDEX ( login_destinataire ) ;");
	if ($query) {
			$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
			$result .= "<font color=\"red\">Erreur</font><br />";
	}
}

// Ajout d'index
$result .= "&nbsp;->Ajout de l'index 'login_destinataire' � la table 'messages'<br />";
$req_res=0;
$req_test = mysql_query("SHOW INDEX FROM messages ");
if (mysql_num_rows($req_test)!=0) {
	while ($enrg = mysql_fetch_object($req_test)) {
		if ($enrg-> Key_name == 'login_destinataire') {$req_res++;}
	}
}
if ($req_res == 0) {
	$query = mysql_query("ALTER TABLE messages ADD INDEX login_destinataire ( login_destinataire )");
	if ($query) {
		$result .= "<font color=\"green\">Ok !</font><br />";
	} else {
		$result .= "<font color=\"red\">Erreur</font><br />";
	}
} else {
	$result .= "<font color=\"blue\">L'index existe d�j�.</font><br />";
}

$result .= "<br /><br /><b>Ajout d'une table 's_travail_mesure' :</b><br />";
$test = sql_query1("SHOW TABLES LIKE 's_travail_mesure'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS s_travail_mesure (id INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,id_incident INT( 11 ) NOT NULL ,login_ele VARCHAR( 50 ) NOT NULL , travail TEXT NOT NULL);");
	if ($result_inter == '') {
		$result .= "<font color=\"green\">SUCCES !</font><br />";
	}
	else {
		$result .= "<font color=\"red\">ECHEC !</font><br />";
	}
} else {
		$result .= "<font color=\"blue\">La table existe d�j�</font><br />";
}

//$result .= "<br /><br /><b>XXX :</b><br />";

?>
