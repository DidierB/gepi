<?php

require 'gepi/om/BaseCreneauPeer.php';


/**
 * Skeleton subclass for performing query and update operations on the 'a_creneaux' table.
 *
 * Les creneaux sont la base du temps des eleves et des cours
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    gepi
 */
class CreneauPeer extends BaseCreneauPeer {

  /**
   * Mets en cache la liste des creneaux
   *
   * @var array creneaux
   */
	private static $_liste_creneaux = NULL;

	public static function getAllCreneauxOrderByTime(){
		if (self::$_liste_creneaux == null) {
			$criteria = new Criteria();
			$criteria->addAscendingOrderByColumn(CreneauPeer::DEBUT_CRENEAU);
			self::$_liste_creneaux = self::doSelect($criteria);
		}
		return self::$_liste_creneaux;
	}

  /**
   * Renvoie le premier creneau de la journee
   *
   * @return array first creneau
   */
	public static function getFirstCreneau(){
		$creneaux = self::getListeCreneaux();
		if ($creneaux != null) {
			return $creneaux[0];
		} else {
			return null;
		}
	}

  /**
   * Renvoie le dernier creneau de la journee
   *
   * @return array last creneau
   */
	public static function getLastCreneau(){
		$creneaux = self::getListeCreneaux();
		$nbre = count($creneaux);
		return $creneaux[$nbre - 1];
	}

  /**
   * Purge la liste des creneaux mis en cache
   *
   * @return array last creneau
   */
	public static function clearListeCreneaux(){
		self::$_liste_creneaux = null;
	}

  /**
   * Renvoie la liste des cr�neaux sous la forme d'un tableau php
   *
   * @return array liste d'objet creneau
   */
  public static function getListeCreneaux(){
    if (self::$_liste_creneaux === NULL){
      self::$_liste_creneaux = self::getAllCreneauxOrderByTime();
    }
    return self::$_liste_creneaux;
  }

  /**
   * Renvoie le creneau pr�c�dent de celui pass� en argument
   * Si l'id du creneau d�passe 3600 (ce qui parait peu probable tout de m�me), on teste sur l'heure de d�but
   *
   * @var $creneau id du creneau ou heure de d�but
   * @return object CreneauPeer precedent
   */
  public static function getCreneauPrecedent($creneau){
    $creneaux = self::getListeCreneaux();
    $nbre = count($creneaux);
    if (is_numeric($creneau) AND $creneau < 3600){
      // On peut rechercher par rapport � l'id
      for($a = 0 ; $a < $nbre ; $a++){
        if ($creneaux[$a]->getId() == $creneau){
          return($creneaux[$a - 1]);
        }
      }
    }elseif($creneau > 3600){
      // On peut rechercher par rapport � l'heure de debut
      for($a = 0 ; $a < $nbre ; $a++){
        if ($creneaux[$a]->getDebutCreneau() <= $creneau AND $creneaux[$a]->getFinCreneau() >= $creneau){
          return($creneaux[$a - 1]);
        }
      }
    }
  }

} // CreneauPeer