<?php



/**
 * Skeleton subclass for performing query and update operations on the 'a_saisies' table.
 *
 * Chaque saisie d'absence doit faire l'objet d'une ligne dans la table a_saisies. Une saisie peut etre : une plage horaire longue durée (plusieurs jours), défini avec les champs debut_abs et fin_abs. Un creneau horaire, le jour etant precisé dans debut_abs. Un cours de l'emploi du temps, le jours du cours etant precisé dans debut_abs.
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.gepi
 */
class AbsenceEleveSaisiePeer extends BaseAbsenceEleveSaisiePeer {

	/**
	 * Validates all modified columns of given AbsenceEleveTraitement object.
	 * If parameter $columns is either a single column name or an array of column names
	 * than only those columns are validated.
	 *
	 * NOTICE: This does not apply to primary or foreign keys for now.
	 *
	 * @param      AbsenceEleveTraitement $obj The object to validate.
	 * @param      mixed $cols Column name or array of column names.
	 *
	 * @return     mixed TRUE if all columns are valid or the error message of the first invalid column.
	 */
	public static function doValidate($obj, $cols = null)
	{
	    $failureMap = array();
	    if (($retval = parent::doValidate($obj, $cols)) !== true) {
		    $failureMap = array_merge($failureMap, $retval);
	    }


	    //validation maison
	    //on exclus mutuellement un id_classe, et id_groupe et un id_aid
	    $id_relation = 0;
	    if ($obj->getAidDetails() !== null) {
		$id_relation = $id_relation + 1;
	    }
	    if ($obj->getClasse() !== null) {
		$id_relation = $id_relation + 1;
	    }
	    if ($obj->getGroupe() !== null) {
		$id_relation = $id_relation + 1;
	    }
	    if ($id_relation > 1) {
		$failureMap[AbsenceEleveSaisiePeer::ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ID,"Il ne peut y avoir un groupe, une classe et une aid simultan�ments p�cis�.<br/>");
	    }

	    if ($obj->getEleveId() !== null) {
		if ($obj->getEleve() == null) {
		    $failureMap[AbsenceEleveSaisiePeer::ELEVE_ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ELEVE_ID,"L'id de l'eleve est incorrect.");
		}
	    }

	    if ($obj->getEdtEmplacementCours() !== null) {
		//si on saisie un cours, alors le creneau et la classe doive etre
		if ($obj->getIdClasse() !== null) {
		    $failureMap[AbsenceEleveSaisiePeer::ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ID,"Si un cours est renseigne la classe doit etre nul.");
		}
		if ($obj->getIdEdtCreneau() !== null && $obj->getEdtEmplacementCours()->getIdDefiniePeriode() != $obj->getIdEdtCreneau()) {
		    $failureMap[AbsenceEleveSaisiePeer::ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ID,"Si un cours est renseigne le creneau doit lui correspondre.");
		}
		if ($obj->getIdGroupe() === null && $obj->getIdAid() === null) {
		    $failureMap[AbsenceEleveSaisiePeer::ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ID,"Si un cours est renseigne alors le groupe ou l'aid doivent etre saisies.");
		}
		if ($obj->getIdGroupe() != null && $obj->getEdtEmplacementCours()->getIdGroupe() != $obj->getIdGroupe()) {
		    $failureMap[AbsenceEleveSaisiePeer::ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ID,"Si un cours est renseigne alors le groupe doit etre celui du cours.");
		}
		if ($obj->getIdAid() != null && $obj->getEdtEmplacementCours()->getIdAid() != $obj->getIdAid()) {
		    $failureMap[AbsenceEleveSaisiePeer::ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ID,"Si un cours est renseigne alors l'aid doit etre celle du cours.");
		}
	    }

	    //si il y a un eleve, on verifie qu'il appartient bien au groupe, � la classe ou � l'aid pr�cis�
	    if ($obj->getAidDetails() != null && $obj->getEleve() != null) {
		$criteria = new Criteria();
		$criteria->add(JAidElevesPeer::LOGIN, $obj->getEleve()->getLogin());
		if ($obj->getAidDetails()->countJAidElevess($criteria) == 0) {
		    $failureMap[AbsenceEleveSaisiePeer::ELEVE_ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ELEVE_ID,"L'eleve n'appartient pas � l'aid selectionn� : ".$obj->getAidDetails()->getNom());
		}
	    }

	    //si il y a un eleve, on verifie qu'il appartient bien au groupe, � la classe ou � l'aid pr�cis�
	    if ($obj->getGroupe() != null && $obj->getEleve() != null) {
		$criteria = new Criteria();
		$criteria->add(JEleveGroupePeer::LOGIN, $obj->getEleve()->getLogin());
		if ($obj->getGroupe()->countJEleveGroupes($criteria) == 0) {
		    $failureMap[AbsenceEleveSaisiePeer::ELEVE_ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ELEVE_ID,"L'eleve n'appartient pas au groupe selectionn�.");
		}
	    }

	    //si il y a un eleve, on verifie qu'il appartient bien au groupe, � la classe ou � l'aid pr�cis�
	    if ($obj->getClasse() != null && $obj->getEleve() != null) {
		$criteria = new Criteria();
		$criteria->add(JEleveClassePeer::LOGIN, $obj->getEleve()->getLogin());
		if ($obj->getClasse()->countJEleveClasses($criteria) == 0) {
		    $failureMap[AbsenceEleveSaisiePeer::ELEVE_ID] = new ValidationFailed(AbsenceEleveSaisiePeer::ELEVE_ID,"L'eleve n'appartient pas � la classe selectionn�e.");
		}
	    }

	    if ($obj->getUtilisateurId() === null) {
		$failureMap[AbsenceEleveSaisiePeer::UTILISATEUR_ID] = new ValidationFailed(AbsenceEleveSaisiePeer::UTILISATEUR_ID,"Il faut preciser l'utilisateur qui rentre la saisie.");
	    }

	    if ($obj->getDebutAbs() == null) {
		$failureMap[AbsenceEleveSaisiePeer::DEBUT_ABS] = new ValidationFailed(AbsenceEleveSaisiePeer::DEBUT_ABS,"La date de debut d'absence ne doit pas etre nulle.");
	    }

	    if ($obj->getFinAbs() == null) {
		 $failureMap[AbsenceEleveSaisiePeer::FIN_ABS] = new ValidationFailed(AbsenceEleveSaisiePeer::FIN_ABS,"La date de fin d'absence ne doit pas etre nulle.");
	    }

	    if ($obj->getDebutAbs('U') >= $obj->getFinAbs('U')) {
		$failureMap[AbsenceEleveSaisiePeer::FIN_ABS] = new ValidationFailed(AbsenceEleveSaisiePeer::FIN_ABS,"La date de debut d'absence doit etre strictement anterieure � la date de fin.");
	    }

	    return (!empty($failureMap) ? $failureMap : true);

	}


} // AbsenceEleveSaisiePeer
