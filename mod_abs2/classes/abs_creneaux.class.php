<?php
/**
 *
 *
 * @version $Id: parametrage_ajax.php 2708 2008-11-28 21:37:48Z jjocal $
 *
 * Copyright 2001, 2007 Thomas Belliard, Laurent Delineau, Eric Lebrun, Stephane Boireau, Julien Jocal
 *
 * This file is part of GEPI.
 *
 * GEPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * Classe qui permet d'utiliser les cr�neaux de Gepi
 * id             int(11)
 * nom_creneau    varchar(50)
 * debut_creneau 	int(12)
 * fin_creneau    int(12)
 * jour_creneau 	int(2)
 * type_creneau   enum('pause', 'repas', 'cours')
 *
 * @author jjocal
 */
class Abs_creneau extends activeRecordGepi {

  /**
   * la cl� [nbre] donne le nombre de cr�neaux total
   *
   * @var array Tous les cr�neaux sans aucune distinction
   */
  private $all_the_creneaux = NULL;
  /**
   *
   * @var integer L'id du premier cr�neau dans l'ordre chronologique
   */
  private $first_creneau = NULL;
  /**
   *
   * @var integer L'id du dernier cr�neau dans l'ordre chronologique
   */
  private $last_creneau = NULL;


  public function  __construct() {

    parent::__construct(__CLASS__);

    $this->all_the_creneaux = $this->findAllCreneaux();

    $this->all_the_creneaux["nbre"] = count($this->all_the_creneaux);

    $this->first_creneau = $this->all_the_creneaux[0]->id;
    $last = $this->all_the_creneaux["nbre"] - 1;
    $this->last_creneau = $this->all_the_creneaux[$last]->id;

  }

  /**
   * M�thode publique qui permet de connaitre l'id du premier cr�neau
   *
   * @return integer un id
   */
  public function getFirstCreneau(){
    return $this->first_creneau;
  }

  /**
   * M�thode qui permet de connaitre l'id du dernier cr�neau
   *
   * @return integer id
   */
  public function getLastCreneau(){
    return $this->last_creneau;
  }

  /**
   * heure du d�but du cr�neau
   *
   * @param integer $_id
   * @return numeric en nombre de secondes
   */
  public function getDebut($_id){
    for($a = 0 ; $a < $this->all_the_creneaux["nbre"] ; $a++){
      if ($this->all_the_creneaux[$a]->id == $_id){
        return $this->all_the_creneaux[$a]->debut_creneau;
      }
    }
  }

  /**
   * heure de fin du cr�neau
   *
   * @param integer $_id
   * @return numeric en nombre de secondes
   */
  public function getFin($_id){
    for($a = 0 ; $a < $this->all_the_creneaux["nbre"] ; $a++){
      if ($this->all_the_creneaux[$a]->id == $_id){
        return $this->all_the_creneaux[$a]->fin_creneau;
      }
    }
  }

  /**
   * M�thode qui transforme les secondes de l'horaire en heure fan�aise hh:mm
   *
   * @access public
   * @param string $var Nombre de secondes
   * @return string hh:mm
   */
  public function heureFr($var){
    $heures = floor($var / 3600);
    $reste = $var % 3600;
    $minutes = floor($reste / 60);
    $minutes = $minutes < 10 ? '0' . $minutes : $minutes;

    return $heures . ':' . $minutes;
  }

  /**
   * M�thode qui renvoit un horaire de la forme 10:00 sous un nombre de seconde �coul�es depuis 00:00
   *
   * @param string $var hh:mm
   * @return numeric
   */
  public function heureBdd($var){
    if (self::isHoraire($var)){
      $test = explode(":", $var);
      return (($test[0] * 3600) + ($test[1]* 60));
    }else{
      return false;
    }
  }

  /**
   * M�thode qui permet de v�rifier si les horaires saisis par l'utilisateur sont corrects ou pas
   *
   * @param string $info
   * @return boolean false/true
   */
  protected function isHoraire($info){
    $test = explode(":", $info);
    if (count($test) == 2){
      // C'est bon, on continue les tests
      if (is_numeric($test[0]) AND is_numeric($test[1])){
        // C'est encore bon, on termine les tests
        if ($test[0] < 25 AND $test[1] < 61){
          return true;
        }else{
          return false;
        }
      }else{
        return false;
      }
    }else{
      return false;
    }
  }

  /**
   * M�thode qui renvoie tous les cr�neaux sans distinction de type en commen�ant par le plus ancien
   *
   * @access public
   * @return array Liste de tous les cr�neaux
   */
  private function findAllCreneaux(){
    return $this->findAll(array('order_by'=> 'debut_creneau'));
  }

}
?>
