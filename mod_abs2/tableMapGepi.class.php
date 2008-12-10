<?php
/**
 * @version $Id$
 *
 * @copyright 2008
 *
 */

/**
 * Classe qui permet de dire � Gepi les liens qui existent entre plusieurs tables
 * Il faut pr�ciser le champ de la table1 qui fait r�f�rence � la table2, puis table3,...
 *
 * @author Julien Jocal
 */
class tableMapGepi {
  /**
   * Propri�t� de l'objet qui stocke les diff�rentes tables sous la forme d'un tableau php
   * exemple : $_tables = array(table2', 'table3');
   * o� table1 est la table principale li�e aux deux autres par le champs de $_fk;
   *
   * @access protected
   * @property array $_tables
   */
  protected $_tables;

  /**
   * Propri�t� de l'objet qui stocke la cl� �trang�re (m�me si elle n'est pas d�finie comme telle dans la table)
   * exemple : $_fk[] = array('champ_table1_vers_table2', 'champ_table2');
   * $_fk = array('champ_table1_vers_table3', 'champ_table3');
   * Si la bas est construite sous la forme champ id_utilisateurs vers la table utilisateurs
   * Cette propri�t� est remplie automatiquement par la m�thode setFk.
   *
   * @access protected
   * @property array $_fk
   */
  protected $_fk;

  /**
   *�Propri�t� de l'objet qui d�finit la table principale qui est li�e avec $_tables et $_fk
   * Permet ensuite de construire dynamiquement les
   */

    public function __construct(){
        // Constructeur de la classe
    }

    /**
     * M�thode qui permet de 
     * 
     * 
     */
    protected function setFk(){

    }

}
?>
