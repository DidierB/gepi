<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2008
 */




/**
 * Impl�mentation basique du motif ActiveRecord pour GEPI
 * Utilise les modules PDO et PDO_mysql
 * Ne pr�voit pas encore d'abtraction totale de la base de donn�es
 * Utilise obligatoirement MySql
 */
class ActiveRecordGepi{

  /**
  * $_table permet de savoir sur quelle table de la base on travaille
  * Toutes les classes qui �tendront ActiveRecordGepi devront initialiser cet attribut
  * __contruct() {parent::__construct('nom_de_la_table');}
  *
  * @acces private
  *
  */
  private $_table = NULL;

  /**
  * $typeChamps permet de conserver une trace du type de chaque champ
  *
  * @acces private
  *
  */
  private $typeChamps = array();

  /**
  * $typeKeys permet de conserver une trace des cl�s de la table
  * PRI / UNI / MUL
  *
  * @acces private
  *
  */
  private $typeKeys = array();

  /**
  * $_pk est le nom de la cl� primaire de la table
  * peut-�tre un array()
  *
  * @acces private
  *
  */
  private $_pk = array();

  /**
  * $conn est la ressource de connexion PDO � la base
  * Soit on la r�cup�re par $GLOBALS["cnx"], soit on cr�e une connexion
  * La m�thode self::pdo_connect() permet de v�rifier l'�tat de la connexion PDO � la base
  * @acces protected
  */
  protected static $conn = NULL;

  /**
   * Constructor
   * Permet d'initialiser la bonne table de la base de donn�es ainsi que tous les champs sous la forme d'attributs
   *
   * @access protected
   */
  protected function __construct($_classe){

    $this->_table = $_table;

    $this->returnChamps();

  }

  /**
   * returnChamps() renvoie la liste des champs avec leurs valeurs par d�faut
   * Chaque champ est alors consid�r� comme une propri�t� de l'objet
   *
   * @access protected
   */
  protected function returnChamps(){

    if (!isset($this->_table)) {
			return false;
		}else{

			// On r�cup�re la liste des champs de la table en question
			$sql = "SHOW COLUMNS FROM ".$this->_table;
			$query = $this->_requete($sql);

			$return = $query->fetchAll(PDO::FETCH_OBJ);
			$nbre_champs = count($return);

			for($a = 0 ; $a < $nbre_champs ; $a++){

				$reponse[$return[$a]->Field] = $return[$a]->Type; // Pour en conserver une trace dans l'objet
				$reponseKey[$return[$a]->Field] = $return[$a]->Key; // Pour en conserver une trace dans l'objet

				$this->$return[$a]->Field = $return[$a]->Default; // Pour les modifier � loisir
				return true;

			}
			$this->typeChamps  = $reponse; // On stocke le type de chaque champ
			$this->typeKeys    = $reponseKey;
			return true;
		}
	}

  protected function _requete($sql){

    // Il faut v�rifier de quel type est la requ�te query/exec
    $this->verif_requete($sql);

  }

  protected function verif_requete($sql){

    if (!is_array($sql)) {

      // On teste pour former la bonne requ�te
		  $test = substr(strtoupper($sql), 0, 4);

      if ($test == 'SELE' OR $test == 'SHOW' OR $test == 'CREA' OR $test == 'DROP') {

        return self::pdo_connect()->query($sql);

      }elseif($test == 'INSE' OR $test == 'UPDA' OR $test == 'DELE'){

        $reponse = self::pdo_connect()->exec($sql);

        if($test == 'INSE'){ // on retourne alors le dernier id enregistr�

          return self::pdo_connect()->lastInsertId();

        }else{
				  return $reponse;
        }

      }else{
			 return false;
		  }

    }else{
      // On pourra ici coder une logique de construction des requ�tes SQL par
      // une classe requete qui pourrait � terme permettre de s'affranchir de MySql
    }

  }

  /**
  * Save() permet de r�cup�rer tous les champs de la table (qui ont �t� intitialis�s par ailleurs)
  * pour cr�er une entr�e dans la table.
  *
  * @acces public
  */

  public function save(){

    $verif = 'no';

    if (!isset($this->_table)) {
			return false;
		}

		if (!isset($this->id) OR $this->id == '') {

			$sql = "INSERT INTO ".$this->_table." SET ";
			$verif = 'insert';

		}else{

			$sql = "UPDATE ".$this->_table." SET ";
			$verif = 'update';

		}

		foreach($this->typeChamps as $cle => $valeur){

			if (isset($this->$cle) AND $this->$cle != '') {
				$sql .= $cle . ' = ' . $this->echappe($this->$cle) . ', ';
			}

		}

		$sql = substr($sql, 0, -2); // On enl�ve la derni�re virgule et le dernier espace
		if ($verif == 'update') {
			$sql .= ' WHERE id = ' . $this->id;
		}

		if ($this->_requete($sql)) {

			if ($verif == 'insert') {

				return self::pdo_connect()->lastInsertId();

			}else{

				return true;

			}

		}

  }

  protected function echappe($string){

    if (!get_magic_quotes_gpc()) {
      return self::pdo_connect()->quote($string);
    } else {
      return $string;
    }
  }

  /**
  * Permet  de retrouver une ou plusieurs cl�s primaires du tuple
  * On s'appuie sur la propri�t� $typeKeys de l'objet
  *
  * @acces private
  */

  private function chercherClePrimaire(){

    if (!$this->typeKeys) {
      return false;
    }else{

      foreach($this->typeKeys as $cle => $valeur){

        if ($valeur == "PRI") {
          // $cle est donc une cl� primaire de la table
          $_keys[] = $cle;
        }

		  }
		  $test = count($_keys);

		  if ($test == 1) {
		    $this->_pk = $_keys[0];
		    return true;
		  }else{
        $this->_pk = $_keys;
        return false;
      }

    }

  }

  /**
  * Singleton : m�thode statique d'acc�s � la base de donn�es
  * Permet d'utiliser une seule connexion sur toute l'application
  * On teste la connexion du fichier /lib/mysql.inc.php
  */
  protected static function pdo_connect(){

    self::$conn = isset($GLOBALS["cnx"]) ? $GLOBALS["cnx"] : NULL;

    if (!self::$conn) {
      // Il faut donc ouvrir une connexion
      include("../secure/connect.inc.php"); // Penser � l'enlever car il est d�j� inclu en production
      self::$conn = new PDO('mysql:host='.$GLOBALS["dbHost"].';dbname='.$GLOBALS["dbDb"], $GLOBALS["dbUser"], $GLOBALS["dbPass"]);

    }

    return self::$conn;

  }


  /**
  * On recherche toutes les propri�t�s de l'objet qui correspondent aux champs
  *
  *
  */


  /**
  * Permet de r�cup�rer tous les enregistrements de la table $this->_table
  * $tab_request peut prendre 3 options
  *   'where' _champ = valeur
  *   'order_by' champ, champ2
  *   'limit' num�rique
  */

	public function findAll($tab_request = NULL){

		if (!$this->_table) {

			return false;

		}else{

			$sql = 'SELECT * FROM ' . $this->_table;

			if (is_array($tab_request)) {

				$sql .= isset($tab_request['where']) ? ' WHERE ' . $tab_request['where'] : NULL;
				$sql .= isset($tab_request['order_by']) ? ' ORDER BY ' . $tab_request['order_by'] : NULL;
				$sql .= isset($tab_request['limit']) ? ' LIMIT ' . $tab_request['limit'] : NULL;

			}

			$req = $this->_requete($sql);
			$rep = $req->fetchAll(PDO::FETCH_OBJ);

			return $rep;

		}

	}

  /**
  * M�thode magique : __call permet une construction dynamique des requ�tes
  * @acces public
  */
  public function __call($methode, $valeur){

  }
}

?>