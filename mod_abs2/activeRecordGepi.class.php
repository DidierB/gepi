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
  * @access private
  *
  */
  private $_table = NULL;

  /**
   * On doit la passer � true dans le cas d'un nouvel enregistrement
   *
   * @var boul $newTuple = FALSE
   * @access private
   *
   */
  private $newTuple = FALSE;

  /**
  * $typeChamps permet de conserver une trace du type de chaque champ
  *
  * @access private
  *
  */
  private $typeChamps = array();

  /**
  * $typeKeys permet de conserver une trace des cl�s de la table
  * PRI / UNI / MUL
  *
  * @access private
  *
  */
  private $typeKeys = array();

  /**
  * $_pk est le nom de la cl� primaire de la table
  * peut-�tre un array()
  *
  * @access private
  *
  */
  private $_pk = NULL;

  /**
  * $conn est la ressource de connexion PDO � la base
  * Soit on la r�cup�re par $GLOBALS["cnx"], soit on cr�e une connexion
  * La m�thode self::pdo_connect() permet de v�rifier l'�tat de la connexion PDO � la base
  *
  * @access protected
  */
  protected static $conn = NULL;

  /**
   * Constructor
   * Permet d'initialiser la bonne table de la base de donn�es ainsi que tous les champs sous la forme d'attributs
   * Par d�faut, le nom de la table est le nom de la classe fille en minuscule et au pluriel.
   * Si la r�gle n'est pas respect�e, on peut imposer un nom de table avec $this->setTableName()
   *
   * @access protected
   */
  protected function __construct($_classe){

    $this->_table = strtolower(self::pluralize($_classe));

    $this->returnChamps();

  }

  /**
  * m�thode prot�g�e qui permet d'imposer le nom d'une table pr�cise dans le cas o� le pluriel
  * ne suffit pas.
  *
  * @access protected
  */
  protected function setTableName($_table){
    $this->_table = $_table;
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

        $champ = $return[$a]->Field;

        $this->$champ = $return[$a]->Default; // Pour les modifier � loisir

      }
      $this->typeChamps  = $reponse; // On stocke le type de chaque champ
      $this->typeKeys    = $reponseKey; // On stocke le type de cl� pour chaque champ
      return true;
    }
  }

  /**
   * M�thode qui permet de v�rifier si un champ $colonne existe bien dans la table appel�e
   *
   * @param string $colonne
   * @return boulean 
   */
  protected function colonneExiste($colonne){
      return array_key_exists($colonne, $this->typeChamps);
  }

  /**
   * M�thode "magique" qui permet de modifier la valeur d'une propri�t� en v�rifiant qu'elle existe bien.
   * Elle ne modifie que les propri�t�s qui correspondent aux champs de la table $this->_table
   *
   * @param string $name
   * @param sring $value
   * @return void
   *
   */
  public function  __set($name, $value) {
      if (!$this->colonneExiste($name)){
          return FALSE;
      }else{
          $this->$name = $value;
      }
  }

  /**
   * M�thode "magique" qui retourne la valeur d'une propri�t� de l'objet en v�rifiant qu'elle existe bien
   * Elle ne renvoie que les propri�t�s qui correspondent aux champs de la table $ths->_table
   *
   * @param string $name
   * @return string
   *
   */
  public function  __get($name) {
      if (!$this->colonneExiste($name)){
          return FALSE;
      }else{
          return $this->$name;
      }
  }

  /**
   * Cette m�thode permet de peupler le tuple avec un seul tableau php
   *
   * @param array $valeurs
   * @return void
   *
   */
  public function populate(array $valeurs){
      if (!is_array($valeurs)){
          throw new Exception("Un tableau de valeurs est attendu");
      }else{
          foreach ($valeurs AS $cle => $valeur){
               // On peuple toutes les propri�t�s dynamiques de l'objet qui ont d�j� �t� cr�es
              if (isset($this->$cle)){
                  $this->$cle = (!get_magic_quotes_gpc()) ? $this->echappe($valeur) : $valeur;
              }
          }
      }

  }

  protected function _requete($sql){

    // Il faut v�rifier de quel type est la requ�te query/exec
    return $this->verif_requete($sql);

  }

  protected function verif_requete($sql){

    if (!is_array($sql)) {

      // On teste pour former la bonne requ�te
      $test = substr(strtoupper($sql), 0, 4);

      if ($test == 'SELE' OR $test == 'SHOW' OR $test == 'CREA' OR $test == 'DROP') {

        return self::pdo_connect()->query($sql);

      }elseif($test == 'INSE' OR $test == 'UPDA' OR $test == 'DELE'){

        if ($reponse = self::pdo_connect()->exec($sql)) {

        }else{
          throw new Exception('Erreur dans la requ�te ' . $reponse);
        }

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
      $_sql = new sqlclass($sql);
      $cmdPDO = isset($_sql->commandePdo) ? $_sql->commandePdo : 'query';
      return self::pdo_connect()->$cmdPDO($_sql);

    }

  }

  /**
   * M�thode qui permet de peupler un champ du tuple avant sauvegarde
   * Dans le cas d'un objet d�j� peupl�, elle permet une maj des champs
   *
   * @access public
   * @var string $valeur
   * @var string $champ
   */
  public function getChamp($champ, $valeur){
      if (isset($this->$champ)){
          $this->$champ = $valeur;
          return true;
      }else{
          return false;
      }
  }
  public function setChamp($champ){
      if (isset($this->$champ)){
          return $this->$champ;
      }else{
          return false;
      }
  }

  /**
  * Save() permet de r�cup�rer tous les champs de la table (qui ont �t� intitialis�s par ailleurs)
  * pour cr�er une entr�e dans la table.
  *
  * @access public
  */

  public function save(){

    $verif = 'no';

    if (!isset($this->_table)) {
      return false;
    }

    if (!$this->chercherClePrimaire()) {
      // Alors id est la cl� primaire
      // Si le champ id n'existe pas alors qu'il y a plusieurs champs cl� primaire
      // C'est qu'il y a une erreur de conception dans la table
      $this->_pk = $this->id;

    }elseif($this->chercherClePrimaire() == 'no'){
      // Il n'y a pas de cl� primaire dans cette table
      // Difficile de savoir s'il s'agit d'un insert ou d'un update
      // Mais cette situation ne devrait pas arriver ou alors tellement rarement qu'il faudra songer � coder la requ�te en dur
    }else{
      // Il n'y a qu'une cl� primaire
      // et c'est $this->_pk
    }

    $clePrimaire = $this->_pk; // on d�signe ainsi le nom du champ qui fait office de cl� primaire

    if (!isset($this->$clePrimaire) OR $this->$clePrimaire == '' OR $this->newTuple === true) {

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
      $sql .= " WHERE " . $this->_pk . " = '" . $this->$clePrimaire . "'";
    }

    if ($query = $this->_requete($sql)) {

      return $query;

    }

  }

  public function isNew(){

    $this->newTuple = true;

  }

  public function isNotNew(){
    $this->newTuple = false;
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
  * @access private
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

      if ($test === 1) {
        $this->_pk = $_keys[0];
        return true;
      }elseif($test === 0){
        return 'no';
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
  *
  * @access protected
  */
  protected static function pdo_connect(){

    self::$conn = isset($GLOBALS["cnx"]) ? $GLOBALS["cnx"] : NULL;

    if (!self::$conn) {
      // Il faut donc ouvrir une connexion
      include("../secure/connect.inc.php"); // Penser � l'enlever car il est d�j� inclu en production
      //self::$conn = new PDO('mysql:host='.$GLOBALS["dbHost"].';dbname='.$GLOBALS["dbDb"], $GLOBALS["dbUser"], $GLOBALS["dbPass"]);
      self::$conn = new PDO('mysql:host='.$dbHost.';dbname='.$dbDb, $dbUser, $dbPass);

    }

    return self::$conn;

  }


  /**
  * Permet de rajouter un "s" � la fin de la classe pour trouver la bonne _table
  *
  * @access public
  */

  public static function pluralize($word) {
    return $word.'s';
  }

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
  * Tous les findBychamp() ou les findChampByAutrechamp()
  *
  * @access public
  */
  public function __call($methode, $valeur){

    $test = explode('By', $methode);

    if ($test[0] == 'find') {

      return $this->findBy(strtolower(substr($methode, 6)), $valeur);

    } elseif ( (substr($test[0], 0, 4) == 'find') AND $test[0] != 'find' ){

      $infos = array(0 => strtolower(substr($test[0], 4)), 1 => strtolower($test[1]));

      return $this->findByFk($infos, $valeur);

    } else {

      $this->gest_erreurs("Cette m�thodes n'est pas disponible dans cette classe");

    }

  }

  /**
  * Cette fonction peuple les propri�t�s de l'objet cr��
  * avec les valeurs des diff�rents champs de la table
  *
  * @access protected
  * @var array $valeur
  */

  protected function findBy($where, $valeur){

    $sql = "SELECT * FROM " . $this->_table . " WHERE " . $where . ' = ' . $this->echappe($valeur[0]) . 'LIMIT 1';

    if ($query_s = $this->_requete($sql)) {

      $rep = $query_s->fetch(PDO::FETCH_OBJ);

      foreach($rep as $cle => $valeur){

        $this->$cle = $valeur;

      }

    } else {

      return false;

    }

  }

  /**
   * findByFk() permet d'ajouter les infos avec les cl�s �trang�res
   * On va permettre de faire simplement des liens avec les cl�s �trang�res
   * exemple $utilisateurs->findj_groupes_professeursByLogin($valeur)
   * o� $valeur est le login de l'utilisateur (ou toute autre information en rapport avec $this->_table
   * Login est le champs qui sert de cl� �trang�re vers la table j_groupes_professeurs
   * ce qui veut donc dire que la table j_groupes_professeurs � un champ login qui va servir pour la jointure
   *
   * $other_key[0] est la table de recherche
   * $other_key[1] est le champ de jointure
   * $valeur est la valeur de ce champ de jointure
   *
   * @param array $other_key
   * @param arrayy $valeur
   * @return array
   */
  protected function findByFk($other_key, $valeur){

    $sql = NULL;

    $sql .= "SELECT * FROM " . $other_key[0]; // . ", " . $this->_table;
    $sql .= " WHERE " . $other_key[1] . " = " . $this->echappe($valeur[0]);

    if ($query_s = $this->_requete($sql)) {

      $return = $query_s->fetchAll(PDO::FETCH_OBJ);
      $return["nbre"] = count($return);
      return $return;

    }else{

      return false;

    }

  }


}

/*

class Utilisateur extends ActiveRecordGepi{

  public function __construct(){

    parent::__construct(__CLASSE__);

  }
}

try{
  $test = new Utilisateur();
  $test->findByLogin("SGARCIA"); // Il suffit de mettre un login de votre base pour tester


  if ($test2 = $test->findJ_groupes_professeursByLogin($test->login)) { // findJ_aid_utilisateursByid_utilisateur($this->login) marche tr�s bien aussi
    echo '<pre>';
    print_r($test2);
    echo '</pre>';
  }else{
    throw new Exception('Impossible de lister les groupes de ce professeur : '.$test->login);
  }
  $test->setChamp('nom', 'Captain'); // Permet de modifier le champ nom de l'objet $test
  $test->save(); // Et l'enregistrement est mis � jour dans la base.

}catch(Exception $e){

  //Les exceptions ne sont pas directement li�es � la classe ci dessus mais une bien belle fa�on de traiter les erreurs.

  echo '<pre>';
  print_r($e);
  echo '</pre>';

}
 */
?>