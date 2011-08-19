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
class AbsenceAgregationDecompteQuery extends BaseAbsenceAgregationDecompteQuery {
    
    /**
     * Filtre la requete sur les dates de d�but et de fin. En cas de date nulle, 
     * le premier jour ou le dernier de l'ann�e scolaire est utilis�
     * 
     * @param  DateTime $date_debut, $date_fin Dates de d�but et de fin de l'extraction des demi journ�es
     * @return    AbsenceAgregationDecompteQuery The current query, for fluid interface
     */
    public function filterByDateIntervalle($date_debut=Null,  $date_fin=Null) {
        
        if (is_null($date_debut) || is_null($date_fin)) {
            require_once("helpers/EdtHelper.php");
            if (is_null($date_debut)) {
                $date_debut = EdtHelper::getPremierJourAnneeScolaire();
            }
            if (is_null($date_fin)) {
                $date_fin = EdtHelper::getDernierJourAnneeScolaire();
            }
        }
        $Heure_debut=$date_debut->format('H');
        if($Heure_debut<12){
            $date_debut->setTime(0,0,0);
        }else{
           $date_debut->setTime(12,0,0); 
        }        
        $this->filterByDateDemiJounee($date_debut, Criteria::GREATER_EQUAL)
             ->filterByDateDemiJounee($date_fin, Criteria::LESS_EQUAL);
        return $this;
    }
       /**
     * Compte le nombre de retard
     * Attention, la requete n'est pas r�utilisable
     *
     * @return    int
     */
    public function countRetards() {
        $this->withColumn('SUM(AbsenceAgregationDecompte.NbRetards)', 'NbRetards')
                ->withColumn('1', 'dummy')
                ->groupBy('dummy');
        $retard = $this->find();
        if ($retard->isEmpty()) {
            return 0;
        } else {
            return $retard->getFirst()->getVirtualColumn('NbRetards');
        }
    }
} // AbsenceAgregationDecompteQuery
