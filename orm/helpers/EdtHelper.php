<?php
/**
 *
 * @version $Id$
 *
 * Copyright 2010 Josselin Jacquard
 *
 * This file and the mod_abs2 module is distributed under GPL version 3, or
 * (at your option) any later version.
 *
 * This file is part of GEPI.
 *
 * GEPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

/**
 * Classe de helpers sur les edt
 */
class EdtHelper {

   public static $semaine_declaration = array("dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi");

 /**
   * Renvoi vrai ou faux selon que l'�tablissement est ouvert � date et l'heure indiqu�e
   *
   * @param      mixed $dt
   * @return boolean
   */
    public static function isEtablissementOuvert($dt){
            if (!EdtHelper::isHoraireOuvert($dt) || !EdtHelper::isJourneeOuverte($dt)) {
                return false;
            } else {
                return true;
            }
    }


  /**
   * Renvoi vrai ou faux selon que l'�tablissement est ouvert � date (jour) indiqu�e
   *
   * @param      dateTime $dt
   * @return boolean false/true
   */
    public static function isJourneeOuverte($dt){
            $jour_semaine = EdtHelper::$semaine_declaration[$dt->format("w")];
	    $horaire_tab = EdtHorairesEtablissementPeer::retrieveAllEdtHorairesEtablissementArrayCopy();
            if (!isset($horaire_tab[$jour_semaine])) {
                //etab ferm�
                return false;
            }

            //est-ce une p�riode ouverte
            $edt_periode_courante = EdtCalendrierPeriodePeer::retrieveEdtCalendrierPeriodeActuelle($dt);
            if ($edt_periode_courante != null
                    && ($edt_periode_courante->getEtabfermeCalendrier() == 0 || $edt_periode_courante->getEtabvacancesCalendrier() == 1)) {
                //etab ferm�
                return false;
            }

            return true;
    }

 /**
   * Renvoi vrai ou faux selon que l'�tablissement est ouvert � cette horaire (sans se pr�ocupper des vacances)
   *
   * @param      mixed $dt
   * @return boolean
   */
    public static function isHoraireOuvert($dt){
            $jour_semaine = EdtHelper::$semaine_declaration[$dt->format("w")];
	    $horaire_tab = EdtHorairesEtablissementPeer::retrieveAllEdtHorairesEtablissementArrayCopy();
            if (isset($horaire_tab[$jour_semaine])) {
                $horaire = $horaire_tab[$jour_semaine];
            } else {
                return false;
            }
            if ($dt->format('Hi') >= $horaire->getFermetureHoraireEtablissement('Hi')
                    ||	$dt->format('Hi') < $horaire->getOuvertureHoraireEtablissement('Hi')) {
                //etab ferm�
                return false;
            }

            return true;
    }
    
   /**
   * Renvoi le premier jour de l'ann�e scolaire sous forme d'objet DateTime
   *   * 
   * @return     DateTime      $DateDebutAnneeScolaire premier septembre de l'ann�e scolaire en cours � 00:00:00 (bascule d'annee semaine 33)
   *
   */
    public static function getPremierJourAnneeScolaire(){
        
        $date_now = new DateTime('now');
        $DateDebutAnneeScolaire=clone ($date_now);
        $semaine_en_cours = $date_now->format('W');
        $annee_en_cours = $date_now->format('Y');
        if ($semaine_en_cours < 33) {
            $annee_en_cours=$annee_en_cours-1;
        } 
        $DateDebutAnneeScolaire->setDate($annee_en_cours,9,1);
        $DateDebutAnneeScolaire->setTime(0,0,0);
        return($DateDebutAnneeScolaire);
    } 
    
  /**
   * Renvoi le dernier jour de l'ann�e scolaire sous forme d'objet DateTime
   *    
   * @return     DateTime      $DateDebutAnneeScolaire 31 aout de l'ann�e scolaire en cours � 23:59:59 (bascule d'annee semaine 33)
   */
    public static function getDernierJourAnneeScolaire(){
        $date_now = new DateTime('now');
        $DateFinAnneeScolaire=clone ($date_now);
        $semaine_en_cours = $date_now->format('W');
        $annee_en_cours = $date_now->format('Y');
        if ($semaine_en_cours >= 33) {
            $annee_en_cours=$annee_en_cours+1;
        } 
        $DateFinAnneeScolaire->setDate($annee_en_cours,8,31);
        $DateFinAnneeScolaire->setTime(23,59,59);
        return($DateFinAnneeScolaire);           
    } 
    
   /**
   * Renvoi le nombre de demi-journ�es ouvertes entre deux dates de debut ou de fin (ou premier et dernier jour de l'ann�e scolaire si les dates ne sont pas sp�cifi�es
   *
   * @param      DateTime $date_debut 
   * @param      DateTime $date_fin
   * @return     Int      $nbre_demi_journees_etab_ouvert
   */
    public static function getNbreDemiJourneesEtabOuvert($date_debut=Null,$date_fin=Null){
        
        //clonage des da&tes de debut et de fin pour ne pas modifier les objets date directement 
        if($date_debut==Null){
            $date_debut_clone=EdtHelper::getPremierJourAnneeScolaire();
        }else{
            $date_debut_clone=clone($date_debut);
        }
        $date_debut_clone->setTime(00, 00, 00);
        if($date_fin==Null){
            $date_fin_clone=EdtHelper::getDernierJourAnneeScolaire();
        }else{
            $date_fin_clone=clone $date_fin;
        }
        $date_fin_clone->setTime(23, 59, 59);
        // on va tester demi journ�e par demi journ�e si l'�tab est ouvert
        
        $nbre_demi_journees_etab_ouvert=0;
        while ($date_debut_clone->format('U') < $date_fin_clone->format('U')){
            $date_clone= clone $date_debut_clone;            
            if($date_debut_clone->format('h:i')=="00:00"){                
                $date_clone->setTime(09,00,00); //on met 9 heures au cas ou un �tab commence � 8h30 par exemple                
            }elseif($date_debut_clone->format('h:i')=="12:00"){
                $date_clone->setTime(15,00,00);//on met 15 heures pour �tre dans la demi journ�e de l'apr�s-midi
            }else {
                echo'Il y a un probl�me sur les heures';
                die();
            }
            if(EdtHelper::isEtablissementOuvert($date_clone)){
                $nbre_demi_journees_etab_ouvert++;                
            }
            $date_debut_clone->modify("+12 hours");
        }
        return($nbre_demi_journees_etab_ouvert);           
    }
}
?>