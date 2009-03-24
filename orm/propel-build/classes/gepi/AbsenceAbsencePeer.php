<?php

require 'gepi/om/BaseAbsenceAbsencePeer.php';


/**
 * Skeleton subclass for performing query and update operations on the 'a_absences' table.
 *
 * Une absence est la compilation des saisies pour un meme eleve, cette compilation est faite automatiquement par Gepi
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    gepi
 */
class AbsenceAbsencePeer extends BaseAbsenceAbsencePeer {

  /**
   * Methode statique qui recherche si la saisie continue une absence ou pas
   *
   * @param array $new_abs Tableau des informations sur la nouvelle absence
   * @return object Niveau de r�ponse (array("rien"=>"rien") s'il n'y a pas d�j� une absence sinon ce sera un objet de l'absence � mettre � jour).
   */
  public static function verifAvantEnregistrement($new_abs){
    if (is_array($new_abs)){
      // On recherche toutes les absences de cet �l�ve sur la semaine pass�e

      $test_deb = $new_abs["debut_abs"] - (7*24*3600);
      $criteria = new Criteria();
      $criteria->add(AbsenceAbsencePeer::ELEVE_ID, $new_abs["eleve_id"], Criteria::EQUAL);
      $criteria->add(AbsenceAbsencePeer::DEBUT_ABS, $test_deb, Criteria::GREATER_EQUAL);
      $test = self::doSelect($criteria);

      // Pour chacune de ces absences, on teste si il y a continuit� avec l'absences saisie $new_abs
      if (empty ($test)){
        // Il n'y a aucune r�ponse, donc la saisie est une nouvelle absence
        $retour = array('rien'=>'rien');
      }else{
        $retour = array();
        $test_du_creneau = CreneauPeer::getCreneauPrecedentCours($new_abs["debut_abs"]); // $test_du_creneau est un objet

        if ($test_du_creneau){
          // On recherche s'il existe une absence pour cet �l�ve sur ce cr�neau
          foreach ($test as $absent){
            if ($absent->getFinAbs() >= $test_du_creneau->getFinCreneau()){
              $retour = $absent; // On retourne l'objet absence � mettre � jour
              break;
            }
          }
          if (empty ($retour)){
            $retour["rien"] = "rien2";
          }

        }else{
          // Il faut donc rechercher s'il existe une absence pour le jour precedent
          // Quel est le dernier cr�neau du jour pr�c�dent
          $aujourdhui = date("N");
          $retour["rien"] = "rien3";

        }
      }
    }else{
      $retour = false;
    }
    return $retour;
  }

} // AbsenceAbsencePeer
