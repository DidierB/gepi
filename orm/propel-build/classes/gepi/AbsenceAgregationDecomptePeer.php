<?php



/**
 * Skeleton subclass for performing query and update operations on the 'a_agregation_decompte' table.
 *
 * Table d'agregation des decomptes de demi journees d'absence et de retard
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    propel.generator.gepi
 */
class AbsenceAgregationDecomptePeer extends BaseAbsenceAgregationDecomptePeer {
	/**
	 *
	 * V�rifie que l'ensemble de la table d'agr�gation est � jours, pour tous les �l�ves. Corrige la table dans certain cas non couteux, sinon renvoi faux
	 *
	 * @param      DateTime $dateDebut date de d�but pour la prise en compte du test
	 * @param      DateTime $dateFin date de fin pour la prise en compte du test
	 * @return		Boolean
	 *
	 */
	public static function checkSynchroAbsenceAgregationTable(DateTime $dateDebut = null, DateTime $dateFin = null) {

		//on initialise les date clone qui seront manipul�s dans l'algoritme, c'est n�cessaire pour ne pas modifier les dates pass�es en param�tre.
		$dateDebutClone = null;
		$dateFinClone = null;
		
		if ($dateDebut != null) {
			$dateDebutClone = clone $dateDebut;
			$dateDebutClone->setTime(0,0);
		}
		if ($dateFin != null) {
			$dateFinClone = clone $dateFin;
			$dateFinClone->setTime(23,59);
		}
		
		//on va v�rifier que tout les marqueurs de fin des calculs de mise � jour sont bien pr�sents pour tout les �l�ves
		$query = '
			SELECT distinct eleves.ID_ELEVE
			FROM `eleves` 
			LEFT JOIN (
				SELECT distinct ELEVE_ID
				FROM `a_agregation_decompte`
				WHERE date_demi_jounee IS NULL) as a_agregation_decompte_selection
			ON (eleves.ID_ELEVE=a_agregation_decompte_selection.ELEVE_ID)
			WHERE a_agregation_decompte_selection.ELEVE_ID IS NULL';
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		if ($num_rows>0 && $num_rows < 50) {
			//on va corriger la table pour ces �l�ves l�
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		    	$eleve = EleveQuery::create()->findOneByIdEleve($row[0]);
		    	echo'on v�rifie suite a une absence de marqueur '.$row[0].'<br/>';
		    	$eleve->checkAndUpdateSynchroAbsenceAgregationTable($dateDebutClone,$dateFinClone);
			}
			//apr�s avoir corrig� on relance le test
			return(AbsenceAgregationDecomptePeer::checkSynchroAbsenceAgregationTable($dateDebutClone, $dateFinClone));
		} elseif ($num_rows>0) {
			return false;
		}
		
		//conditions sql sur les dates
		$date_saisies_selection = ' 1=1 ';
		$date_agregation_selection = ' 1=1 ';
		if ($dateDebutClone != null) {
			$date_saisies_selection .= ' and a_saisies.fin_abs >= "'.$dateDebutClone->format('Y-m-d H:i:s').'" ';
			$date_agregation_selection .= ' and a_agregation_decompte.DATE_DEMI_JOUNEE >= "'.$dateDebutClone->format('Y-m-d H:i:s').'" ';
		}
		if ($dateFinClone != null) {
			$date_saisies_selection .= ' and a_saisies.debut_abs <= "'.$dateFinClone->format('Y-m-d H:i:s').'" ';
			$date_agregation_selection .= ' and a_agregation_decompte.DATE_DEMI_JOUNEE <= "'.$dateFinClone->format('Y-m-d H:i:s').'" ';
		}
				
		//on va v�rifier que tout les �l�ves ont bien des entr�es dans la table d'agr�gation pour cette p�riode
		$query = '
			SELECT distinct eleves.ID_ELEVE
			FROM `eleves` 
			LEFT JOIN (
				SELECT distinct ELEVE_ID
				FROM `a_agregation_decompte`
				WHERE '.$date_agregation_selection.') as a_agregation_decompte_selection
			ON (eleves.ID_ELEVE=a_agregation_decompte_selection.ELEVE_ID)
			WHERE a_agregation_decompte_selection.ELEVE_ID is null';
		$result = mysql_query($query);
		$num_rows = mysql_num_rows($result);
		if ($num_rows>0 && $num_rows < 50) {
			//on va corriger la table pour ces �l�ves l�
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		    	$eleve = EleveQuery::create()->findOneByIdEleve($row[0]);
		    	echo'on v�rifie suite a une absence d entr�e '.$row[0].'<br/>';
		    	$eleve->checkAndUpdateSynchroAbsenceAgregationTable($dateDebutClone,$dateFinClone);
			}
			//apr�s avoir corrig� on relance le test
			return(AbsenceAgregationDecomptePeer::checkSynchroAbsenceAgregationTable($dateDebutClone, $dateFinClone));
		} elseif ($num_rows>0) {
			return false;
		}
		
		
		/* on va r�cup�r� trois informations en base de donn�e :
		 * - est-ce qu'il y a bien le marqueur de fin de calcul (entr�e avec a_agregation_decompte.DATE_DEMI_JOUNEE IS NULL)
		 * - est-ce que la date updated_at de mise � jour de la table est bien post�rieure aux date de modification des saisies et autres entr�es
		 * - on va compter le nombre de demi journ�e, elle doivent �tre toutes remplies
		 */
		//$query = 'select ELEVE_ID is not null, union_date <= as updated_at, count_demi_jounee
		$query = 'select union_date, updated_at, count_demi_jounee, count_eleve
		
		FROM
			(SELECT updated_at 
			FROM a_agregation_decompte WHERE '.$date_agregation_selection.'	
			ORDER BY updated_at DESC LIMIT 1) as updated_at_select

		LEFT JOIN (';
		if ($dateDebutClone != null && $dateFinClone != null) {
			$query .= '
			(SELECT count(*) as count_demi_jounee from  a_agregation_decompte WHERE '.$date_agregation_selection;
		} else {
			$query .= '
			(SELECT -1 as count_demi_jounee from  a_agregation_decompte limit 1';
		}
			$query .= '
			) as count_demi_journee_select
		) ON 1=1
		
		LEFT JOIN (';
		if ($dateDebutClone != null && $dateFinClone != null) {
			$query .= '
			(SELECT count(DISTINCT eleve_id) as count_eleve from  a_agregation_decompte WHERE '.$date_agregation_selection;
		} else {
			$query .= '
			(SELECT -1 as count_demi_jounee from  a_agregation_decompte limit 1';
		}
			$query .= '
			) as count_eleve_id_select
		) ON 1=1
		
		LEFT JOIN (
			(SELECT count(*) as count_manquement from  a_agregation_decompte 
			WHERE '.$date_agregation_selection.'
			AND manquement_obligation_presence=1
			) as count_select_manquement
		) ON 1=1
		
		LEFT JOIN (
			(SELECT union_date from 
				(SELECT updated_at as union_date FROM a_saisies WHERE a_saisies.deleted_at is null and '.$date_saisies_selection.'
				UNION ALL
					SELECT deleted_at as union_date  FROM a_saisies WHERE a_saisies.deleted_at is not null and '.$date_saisies_selection.'
				UNION ALL
					SELECT a_traitements.updated_at as union_date  FROM a_traitements join j_traitements_saisies on a_traitements.id = j_traitements_saisies.a_traitement_id join a_saisies on a_saisies.id = j_traitements_saisies.a_saisie_id WHERE  a_traitements.deleted_at is null and a_saisies.deleted_at is null and '.$date_saisies_selection.'
				UNION ALL
					SELECT a_traitements.deleted_at as union_date  FROM a_traitements join j_traitements_saisies on a_traitements.id = j_traitements_saisies.a_traitement_id join a_saisies on a_saisies.id = j_traitements_saisies.a_saisie_id WHERE a_traitements.deleted_at is not null and a_saisies.deleted_at is null and '.$date_saisies_selection.'
				
				ORDER BY union_date DESC LIMIT 1
				) AS union_date_union_all_select
			) AS union_date_select
		) ON 1=1;';
			
		$result_query = mysql_query($query);
		if ($result_query === false) {
			echo 'Erreur sur la requete : '.$query.'<br/>'.mysql_error().'<br/>';
			return false;
		}
		$row = mysql_fetch_array($result_query);
		mysql_free_result($result_query);
		if (!$row['union_date']) {//il n'y a pas de saisie sur cette p�riode, donc �a doit �tre � jour.
			//TODO on va v�rifier que toutes les entr�es de la table d'agr�gation dont bien nulle
			if ($row['count_manquement']) {
			echo 2;
				return false;
			} else {
				return true;
			}
		} else if (!$row['updated_at'] || $row['union_date'] > $row['updated_at']){//si on a pas de updated_at dans la table d'agr�gation, ou si la date de mise � jour des saisies est post�rieure � updated_at ou 
			echo 3;
			return false;
		} else if ($row['count_demi_jounee']==-1){
			return true;//on ne v�rifie pas le nombre d'entr�e car les dates ne sont pas pr�cis�e
		} else {
			$nbre_demi_journees=(int)(($dateFinClone->format('U')+3600-$dateDebutClone->format('U'))/(3600*12)); // on compte les tranches de 12h
            //on ajoute une heure � la date de fin pour d�passer 23:59:59 et bien d�passer la tranche de 00:00
            //si on a un debut � 00:00 et une fin la m�me journ�e � 23:59, en ajoutant une heure � la fin on a largement deux tranches de 12h completes
            //donc bien deux demi journ�es de d�compt�es
            if ($row['count_demi_jounee'] == $nbre_demi_journees*EleveQuery::create()->count()) {
				return true;
            } else {
            	return false;
            }
		}
	}
} // AbsenceAgregationDecomptePeer
