<?php
/**
 *
 *
 * @version $Id$
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
 * Permet de construire dynamiquement des requ�tes SQL
 * Par des m�thodes statiques
 *
 * @author Julien Jocal
 */
class sqlclass {
  /**
   *  Le tableau des informations de construction des requ�tes
   * 
   * @property 
   */
  private $_options = NULL;

  /**
   *  Le tableau des commandes SQL autoris�es
   *
   * @property
   */
  private $cde_sql = array('insert', 'update', 'select', 'delete', 'show');

  /**
   * Par d�faut, on fixe la requ�te
   *
   * @property
   */
  protected $_sqlDefault = 'SELECT';

  /**
   * Le constructeur charge le tableau qui permet de construire une requ�te
   * @param array $_options
   *
   * @access public
   */
  public function  __construct($_options) {
    if (is_array($_options)){
      $this->_options = $_options;
      foreach ($this->_options as $cle => $valeur):
        $this->$cle = $valeur;
      endforeach;
    }else{
      throw new Exception('Impossible de construire la requ�te car il manque des informations.');
    }
    echo $this->commandeSQL();
    return $this->_sql();
  }

  /**
   * M�thode prot�g�e de construction des requ�tes SQL
   * Permet de lire le tableau envoy� pour en construire une requ�te
   *
   * @access proteted
   */
  protected function _sql(){


  }

  /**
   * M�thode qui renvoit la commande SQL
   *
   *
   */
  protected function commandeSQL(){
    foreach($this as $cle => $valeur){
      //echo '<br />' . $cle . '<br /> valeur = ' . $valeur;
      if (in_array($cle, $this->cde_sql)){

        $complement = $cle == 'insert' ? ' INTO ' : '';

        return strtoupper($cle) . $complement . $valeur . ' ';
      }
    }
  }
}

$array_sql = array('insert'=>'utilisateurs');
$testSQL = new sqlclass($array_sql);
    echo '<pre>';
    print_r($testSQL);
    echo '</pre>';
?>
