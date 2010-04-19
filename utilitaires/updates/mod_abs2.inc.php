<?php
/* 
 * $Id$
 *
 * Fichier de mise � jour de la version 1.5.2 � la version 1.5.3
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

$result .= "<br /><br /><b>Mise � jour vers la version mod_abs2 :</b><br />";

$result .= "&nbsp;->Ajout des tables absence 2<br />";
#-----------------------------------------------------------------------------
#-- a_actions
#-----------------------------------------------------------------------------

$query = mysql_query("
DROP TABLE IF EXISTS a_actions;
");


$test = sql_query1("SHOW TABLES LIKE 'a_actions'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_actions'. ";
	$sql="
CREATE TABLE a_actions
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT COMMENT 'cle primaire auto-incrementee',
	nom VARCHAR(250)  NOT NULL COMMENT 'Nom de l\'action',
	commentaire TEXT COMMENT 'commentaire saisi par l\'utilisateur',
	sortable_rank INTEGER,
	PRIMARY KEY (id)
)Type=MyISAM COMMENT='Liste des actions possibles sur une absence';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_actions': ".$result_inter."<br />";
	}
}

#-----------------------------------------------------------------------------
#-- a_motifs
#-----------------------------------------------------------------------------

$query = mysql_query("
DROP TABLE IF EXISTS a_motifs;
");


$test = sql_query1("SHOW TABLES LIKE 'a_motifs'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_motifs'. ";
	$sql="
CREATE TABLE a_motifs
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT COMMENT 'cle primaire auto-incrementee',
	nom VARCHAR(250)  NOT NULL COMMENT 'Nom du motif',
	commentaire TEXT COMMENT 'commentaire saisi par l\'utilisateur',
	sortable_rank INTEGER,
	PRIMARY KEY (id)
)Type=MyISAM COMMENT='Liste des motifs possibles pour une absence';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_motifs': ".$result_inter."<br />";
	}
}

#-----------------------------------------------------------------------------
#-- a_justifications
#-----------------------------------------------------------------------------

$query = mysql_query("
DROP TABLE IF EXISTS a_justifications;
");


$test = sql_query1("SHOW TABLES LIKE 'a_justifications'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_justifications'. ";
	$sql="
CREATE TABLE a_justifications
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT COMMENT 'cle primaire auto-incrementee',
	nom VARCHAR(250)  NOT NULL COMMENT 'Nom de la justification',
	commentaire TEXT COMMENT 'commentaire saisi par l\'utilisateur',
	sortable_rank INTEGER,
	PRIMARY KEY (id)
)Type=MyISAM COMMENT='Liste des justifications possibles pour une absence';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_justifications': ".$result_inter."<br />";
	}
}

#-----------------------------------------------------------------------------
#-- a_types
#-----------------------------------------------------------------------------


$query = mysql_query("
DROP TABLE IF EXISTS a_types;
");

#-----------------------------------------------------------------------------
#-- a_saisies
#-----------------------------------------------------------------------------


$test = sql_query1("SHOW TABLES LIKE 'a_types'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_types'. ";
	$sql="
CREATE TABLE a_types
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT COMMENT 'Cle primaire auto-incrementee',
	nom VARCHAR(250)  NOT NULL COMMENT 'Nom du type d\'absence',
	justification_exigible TINYINT COMMENT 'Ce type d\'absence doit entrainer une justification de la part de la famille',
	responsabilite_etablissement TINYINT COMMENT 'L\'eleve est encore sous la responsabilite de l\'etablissement. Typiquement : absence infirmerie, mettre la propri�t� � vrai car l\'eleve est encore sous la responsabilit� de l\'etablissement',
	type_saisie VARCHAR(50) COMMENT 'Enumeration des possibilit�s de l\'interface de saisie de l\'absence pour ce type : DEBUT_ABS, FIN_ABS, DEBUT_ET_FIN_ABS, NON_PRECISE, COMMENTAIRE_EXIGE, DISCIPLINE',
	commentaire TEXT COMMENT 'commentaire saisi par l\'utilisateur',
	sortable_rank INTEGER,
	PRIMARY KEY (id)
)Type=MyISAM COMMENT='Liste des types d\'absences possibles dans l\'etablissement';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_types': ".$result_inter."<br />";
	}
}

#-----------------------------------------------------------------------------
#-- a_types_statut
#-----------------------------------------------------------------------------

$query = mysql_query("
DROP TABLE IF EXISTS a_types_statut;
");


$test = sql_query1("SHOW TABLES LIKE 'a_types_statut'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'types_statut'. ";
	$sql="
CREATE TABLE a_types_statut
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT COMMENT 'Cle primaire auto-incrementee',
	id_a_type INTEGER(11)  NOT NULL COMMENT 'Cle etrangere de la table a_type',
	statut VARCHAR(20)  NOT NULL COMMENT 'Statut de l\'utilisateur',
	PRIMARY KEY (id),
	INDEX a_types_statut_FI_1 (id_a_type),
	CONSTRAINT a_types_statut_FK_1
		FOREIGN KEY (id_a_type)
		REFERENCES a_types (id)
		ON DELETE CASCADE
)Type=MyISAM COMMENT='Liste des statuts autorises � saisir des types d\'absences';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_types_statut': ".$result_inter."<br />";
	}
}

#-----------------------------------------------------------------------------
#-- a_saisies
#-----------------------------------------------------------------------------

$query = mysql_query("
DROP TABLE IF EXISTS a_saisies;
");


$test = sql_query1("SHOW TABLES LIKE 'a_saisies'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_saisies'. ";
	$sql="
CREATE TABLE a_saisies
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT,
	utilisateur_id VARCHAR(100) COMMENT 'Login de l\'utilisateur professionnel qui a saisi l\'absence',
	eleve_id INTEGER(11) default -1 COMMENT 'id_eleve de l\'eleve objet de la saisie, egal � -1 si aucun eleve n\'est saisi',
	commentaire TEXT COMMENT 'commentaire de l\'utilisateur',
	debut_abs DATETIME COMMENT 'Debut de l\'absence en timestamp UNIX',
	fin_abs DATETIME COMMENT 'Fin de l\'absence en timestamp UNIX',
	id_edt_creneau INTEGER(12) default -1 COMMENT 'identifiant du creneaux de l\'emploi du temps',
	id_edt_emplacement_cours INTEGER(12) default -1 COMMENT 'identifiant du cours de l\'emploi du temps',
	id_groupe INTEGER default -1 COMMENT 'identifiant du groupe pour lequel la saisie a ete effectuee',
	id_classe INTEGER default -1 COMMENT 'identifiant de la classe pour lequel la saisie a ete effectuee',
	id_aid INTEGER default -1 COMMENT 'identifiant de l\'aid pour lequel la saisie a ete effectuee',
	created_at DATETIME,
	updated_at DATETIME,
	PRIMARY KEY (id),
	INDEX a_saisies_FI_1 (utilisateur_id),
	CONSTRAINT a_saisies_FK_1
		FOREIGN KEY (utilisateur_id)
		REFERENCES utilisateurs (login)
		ON DELETE SET NULL,
	INDEX a_saisies_FI_2 (eleve_id),
	CONSTRAINT a_saisies_FK_2
		FOREIGN KEY (eleve_id)
		REFERENCES eleves (id_eleve)
		ON DELETE CASCADE,
	INDEX a_saisies_FI_3 (id_edt_creneau),
	CONSTRAINT a_saisies_FK_3
		FOREIGN KEY (id_edt_creneau)
		REFERENCES edt_creneaux (id_definie_periode)
		ON DELETE SET NULL,
	INDEX a_saisies_FI_4 (id_edt_emplacement_cours),
	CONSTRAINT a_saisies_FK_4
		FOREIGN KEY (id_edt_emplacement_cours)
		REFERENCES edt_cours (id_cours)
		ON DELETE SET NULL,
	INDEX a_saisies_FI_5 (id_groupe),
	CONSTRAINT a_saisies_FK_5
		FOREIGN KEY (id_groupe)
		REFERENCES groupes (id)
		ON DELETE SET NULL,
	INDEX a_saisies_FI_6 (id_classe),
	CONSTRAINT a_saisies_FK_6
		FOREIGN KEY (id_classe)
		REFERENCES classes (id)
		ON DELETE SET NULL,
	INDEX a_saisies_FI_7 (id_aid),
	CONSTRAINT a_saisies_FK_7
		FOREIGN KEY (id_aid)
		REFERENCES aid (id)
		ON DELETE SET NULL
)Type=MyISAM COMMENT='Chaque saisie d\'absence doit faire l\'objet d\'une ligne dans la table a_saisies. Une saisie peut etre : une plage horaire longue dur�e (plusieurs jours), d�fini avec les champs debut_abs et fin_abs. Un creneau horaire, le jour etant precis� dans debut_abs. Un cours de l\'emploi du temps, le jours du cours etant precis� dans debut_abs.';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_saisies': ".$result_inter."<br />";
	}
}

$query = mysql_query("DROP TABLE IF EXISTS a_traitements;");
$test = sql_query1("SHOW TABLES LIKE 'a_traitements'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_traitements'. ";
	$sql="
CREATE TABLE a_traitements
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT COMMENT 'cle primaire auto-incremente',
	utilisateur_id VARCHAR(100) default '-1' COMMENT 'Login de l\'utilisateur professionnel qui a fait le traitement',
	a_type_id INTEGER(4) default -1 COMMENT 'cle etrangere du type d\'absence',
	a_motif_id INTEGER(4) default -1 COMMENT 'cle etrangere du motif d\'absence',
	a_justification_id INTEGER(4) default -1 COMMENT 'cle etrangere de la justification de l\'absence',
	a_action_id INTEGER(4) default -1 COMMENT 'cle etrangere de l\'action sur ce traitement',
	commentaire TEXT COMMENT 'commentaire saisi par l\'utilisateur',
	created_at DATETIME,
	updated_at DATETIME,
	PRIMARY KEY (id),
	INDEX a_traitements_FI_1 (utilisateur_id),
	CONSTRAINT a_traitements_FK_1
		FOREIGN KEY (utilisateur_id)
		REFERENCES utilisateurs (login)
		ON DELETE SET NULL,
	INDEX a_traitements_FI_2 (a_type_id),
	CONSTRAINT a_traitements_FK_2
		FOREIGN KEY (a_type_id)
		REFERENCES a_types (id)
		ON DELETE SET NULL,
	INDEX a_traitements_FI_3 (a_motif_id),
	CONSTRAINT a_traitements_FK_3
		FOREIGN KEY (a_motif_id)
		REFERENCES a_motifs (id)
		ON DELETE SET NULL,
	INDEX a_traitements_FI_4 (a_justification_id),
	CONSTRAINT a_traitements_FK_4
		FOREIGN KEY (a_justification_id)
		REFERENCES a_justifications (id)
		ON DELETE SET NULL,
	INDEX a_traitements_FI_5 (a_action_id),
	CONSTRAINT a_traitements_FK_5
		FOREIGN KEY (a_action_id)
		REFERENCES a_actions (id)
		ON DELETE SET NULL
)Type=MyISAM COMMENT='Un traitement peut gerer plusieurs saisies et consiste � definir les motifs/justifications... de ces absences saisies';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_traitements': ".$result_inter."<br />";
	}
}

$query = mysql_query("DROP TABLE IF EXISTS j_traitements_saisies;");
$test = sql_query1("SHOW TABLES LIKE 'j_traitements_saisies'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'j_traitements_saisies'. ";
	$sql="
CREATE TABLE j_traitements_saisies
(
	a_saisie_id INTEGER(12)  NOT NULL COMMENT 'cle etrangere de l\'absence saisie',
	a_traitement_id INTEGER(12)  NOT NULL COMMENT 'cle etrangere du traitement de ces absences',
	PRIMARY KEY (a_saisie_id,a_traitement_id),
	CONSTRAINT j_traitements_saisies_FK_1
		FOREIGN KEY (a_saisie_id)
		REFERENCES a_saisies (id)
		ON DELETE CASCADE,
	INDEX j_traitements_saisies_FI_2 (a_traitement_id),
	CONSTRAINT j_traitements_saisies_FK_2
		FOREIGN KEY (a_traitement_id)
		REFERENCES a_traitements (id)
		ON DELETE CASCADE
)Type=MyISAM COMMENT='Table de jointure entre la saisie et le traitement des absences';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'j_traitements_saisies': ".$result_inter."<br />";
	}
}

$query = mysql_query("DROP TABLE IF EXISTS a_envois;");
$test = sql_query1("SHOW TABLES LIKE 'a_envois'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_envois'. ";
	$sql="
CREATE TABLE a_envois
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT,
	utilisateur_id VARCHAR(100) default '-1' COMMENT 'Login de l\'utilisateur professionnel qui a lance l\'envoi',
	id_type_envoi INTEGER(4) default -1 NOT NULL COMMENT 'id du type de l\'envoi',
	commentaire TEXT COMMENT 'commentaire saisi par l\'utilisateur',
	statut_envoi VARCHAR(20) default '0' COMMENT 'Statut de cet envoi (envoye, en cours,...)',
	date_envoi DATETIME COMMENT 'Date en timestamp UNIX de l\'envoi',
	created_at DATETIME,
	updated_at DATETIME,
	PRIMARY KEY (id),
	INDEX a_envois_FI_1 (utilisateur_id),
	CONSTRAINT a_envois_FK_1
		FOREIGN KEY (utilisateur_id)
		REFERENCES utilisateurs (login)
		ON DELETE SET NULL,
	INDEX a_envois_FI_2 (id_type_envoi),
	CONSTRAINT a_envois_FK_2
		FOREIGN KEY (id_type_envoi)
		REFERENCES a_type_envois (id)
		ON DELETE SET NULL
)Type=MyISAM COMMENT='Chaque envoi est repertorie ici';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_envois': ".$result_inter."<br />";
	}
}

$query = mysql_query("DROP TABLE IF EXISTS a_type_envois;");
$test = sql_query1("SHOW TABLES LIKE 'a_type_envois'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'a_type_envois'. ";
	$sql="
CREATE TABLE a_type_envois
(
	id INTEGER(11)  NOT NULL AUTO_INCREMENT,
	nom VARCHAR(100)  NOT NULL COMMENT 'nom du type de l\'envoi',
	contenu LONGTEXT  NOT NULL COMMENT 'Contenu modele de l\'envoi',
	sortable_rank INTEGER,
	PRIMARY KEY (id)
)Type=MyISAM COMMENT='Chaque envoi dispose d\'un type qui est stocke ici';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'a_type_envois': ".$result_inter."<br />";
	}
}

$query = mysql_query("DROP TABLE IF EXISTS j_traitements_envois;");
$test = sql_query1("SHOW TABLES LIKE 'j_traitements_envois'");
if ($test == -1) {
	$result .= "<br />Cr�ation de la table 'j_traitements_envois'. ";
	$sql="
CREATE TABLE j_traitements_envois
(
	a_envoi_id INTEGER(12)  NOT NULL COMMENT 'cle etrangere de l\'envoi',
	a_traitement_id INTEGER(12)  NOT NULL COMMENT 'cle etrangere du traitement de ces absences',
	PRIMARY KEY (a_envoi_id,a_traitement_id),
	CONSTRAINT j_traitements_envois_FK_1
		FOREIGN KEY (a_envoi_id)
		REFERENCES a_envois (id)
		ON DELETE CASCADE,
	INDEX j_traitements_envois_FI_2 (a_traitement_id),
	CONSTRAINT j_traitements_envois_FK_2
		FOREIGN KEY (a_traitement_id)
		REFERENCES a_traitements (id)
		ON DELETE CASCADE
)Type=MyISAM COMMENT='Table de jointure entre le traitement des absences et leur envoi';
";
	$result_inter = traite_requete($sql);
	if ($result_inter != '') {
		$result .= "<br />Erreur sur la cr�ation de la table 'j_traitements_envois': ".$result_inter."<br />";
	}
}

//===================================================
?>
