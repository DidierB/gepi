<?php
/*
 * $Id$
 *
 * Copyright 2001, 2010 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Julien Jocal
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
 * Classe qui impl�mente un serveur pour permettre � un ENT de se connecter � GEPI
 * Acc�s limit� � la lecture seule. Pour limiter les acc�s, on liste les m�thodes disponibles
 * Les logins des �l�ves existent sous la forme d'un tableau envoy� en POST par curl
 *
 * @method notesEleve(), cdtDevoirsEleve(), cdtCREleve(), professeursEleve(), edtEleve()
 *
 * @author Julien Jocal
 * @license GPL
 */
$traite_anti_inject = 'no'; // pour �viter les �chappements dans les tableaux s�rialis�s
require_once("../lib/initialisationsPropel.inc.php");
//require_once("../lib/initialisations.inc.php");


class serveur_ent {

  /**
   * D�finit le type de demande (utilise le nom des m�thodes autoris�es)
   * @var string m�thode �voqu�e par la demande
   */
  private $_demande      = NULL;

  /**
   * D�finit la p�riode demand�e
   * @var integer Num�ro de la p�riode, 0 par d�faut �quivaut � toutes les p�riodes.
   */
  private $_periode   = 0;
  /**
   * liste des logins des enfants du parent qui demande (envoy� par le client)
   * @var array _enfants
   */
  private $_enfants     = array();
  /**
   * Le login ENT du demandeur (envoy� par le client)
   * @var string _login
   */
  private $_login       = NULL;
  /**
   * Le RNE de l'�tablissement du demandeur (envoy� par le client)
   * @var string RNE
   */
  private $_etab        = NULL;
  /**
   * la cl� secr�te entre le client et le serveur
   * @var string cl�
   */
  private $_api_key     = NULL;
  /**
   * Ce hash est envoy� par le client, le serveur le renvoie avec la r�ponse pour permettre au client de v�rifier qu'il s'agit bien de sa demande
   * @var string hash
   */
  private $_hash        = NULL;

  /**
   * Constructeur de la classe
   *
   * @example Si on est en multisite, il faut un cookie["RNE"] qui donne le bon RNE pour que GEPI se connecte sur la bonne base
   */
  public function __construct(){
    // On initialise toutes nos propri�t�s
    $this->setData();
    // V�rification de la cl�
    $this->verifKey('4567123');
    // On int�gre les fichiers d'initialisation de GEPI
    //require_once("../lib/initialisationsPropel.inc.php");
    //require_once("../lib/initialisations.inc.php");

    // On v�rifie que la demande est disponible
    if (!in_array($this->_demande, $this->getMethodesAutorisees())){
      $this->writeLog(__METHOD__, 'M�thode inexistante:'.$this->_demande, ((array_key_exists('login', $_POST)) ? $_POST['login'] : 'inexistant'));
      Die('M�thode inexistante !');
    }
    // On v�rifie si les logins des enfants envoy�s existent bien dans GEPI
    $reponse = array(); // permet de stocker les informations sur les enfants (tableau d'objets propel)

    foreach (unserialize($this->_enfants) as $enfants){
      // On cherche si cet enfant existe
      $enf = EleveQuery::create()->filterByLogin($enfants)->find();

      if ($enf->isEmpty()){
        // Ce login n'existe pas dans cette base
        $this->writeLog(__METHOD__, 'login enfant inexistant : ' . $enfants, ((array_key_exists('login', $_POST)) ? $_POST['login'] : 'inexistant'));
        $reponse[] = 'inexistant';
      }else{
        // on recherche la r�ponse pour ce login
        $arenvoyer = $this->{$this->_demande}($enf[0]);
        $reponse[$enf[0]->getLogin()] = $arenvoyer;
      }

    } // foreach
    //$this->_enfants = $reponse; // D�sormais on a les objets propel de ces enfants, reste � les manipuler

    if (is_array($reponse)){
      echo serialize($reponse);
    }else{
      echo serialize(array('erreur'=>'service absent'));
    }

  }

  /**
   * renvoie le header du REQUEST_METHOD
   * @return string GET POST PUT ...
   */
  public function testRequestMethod(){
    return $_SERVER['REQUEST_METHOD'];
  }

  /**
   * @todo Mieux g�rer le cas o� la requ�te n'est pas en POST
   * @return void initialise les propri�t�s de l'objet
   */
  private function setData(){
    // On ne fonctionne qu'en POST
    if ($this->testRequestMethod() != 'POST'){
      $this->writeLog(__METHOD__, 'La demande n\'a pas �t� pass�e en POST', ((array_key_exists('login', $_POST)) ? $_POST['login'] : 'inexistant'));
      Die();
    }else{
      // On v�rifie que les donn�es demand�es existent
      $this->_etab      = (array_key_exists('etab', $_POST)) ? $_POST['etab'] : null;
      $this->_enfants   = (array_key_exists('enfants', $_POST)) ? $_POST['enfants'] : null;
      $this->_api_key   = (array_key_exists('api_key', $_POST)) ? $_POST['api_key'] : 'false';
      $this->_demande   = (array_key_exists('demande', $_POST)) ? $_POST['demande'] : null;
      $this->_hash      = (array_key_exists('hash', $_POST)) ? $_POST['hash'] : null;
      $this->_login     = (array_key_exists('login', $_POST)) ? $_POST['login'] : null;
      $this->_periode   = (array_key_exists('periode', $_POST)) ? $_POST['periode'] : $this->_periode;
    }
  }

  private function verifKey($key){
    if ($this->_api_key != $key){
      $this->writeLog(__METHOD__, 'La cl� n\'est pas bonne ('.$this->_api_key.'|'.$key.')', ((array_key_exists('login', $_POST)) ? $_POST['login'] : 'inexistant'));
      Die('la cl� est obsol�te : ' . $this->_api_key . '|+|' . $key);
    }
  }
  /**
   * Renvoie la liste des m�thodes autoris�es par le serveur
   * @todo Penser � mettre � jour cette liste au fur et � mesure de la d�finition des m�thodes
   * @return array liste des m�thodes autoris�es
   */
  public function getMethodesAutorisees(){
    return array('notesEleve', 'cdtDevoirsEleve', 'cdtCREleve', 'professeursEleve', 'edtEleve');
  }

  /**
   * Renvoie la liste des notes d'un �l�ve en fonction de son login pour les deux derniers mois
   *
   * @param string $_login login de l'�l�ve
   * @return array Liste des notes d'un �l�ve
   */
  public function notesEleve($_login){
    return array();
  }

  /**
   * Renvoie la liste des devoirs � faire pour un �l�ve (en fonction du login de l'�l�ve)
   *
   * @todo Pour le moment, on renvoie pour chaque mati�re le devoir le plus �loign� dans le temps, il faudrait renvoyer tous les devoirs dont la date est post�rieure
   * @param string $_login
   * @return array Liste des devoirs � faire du cdt de l'�l�ve
   */
  public function cdtDevoirsEleve(eleve $_eleve){
    $var = array();

    foreach ($_eleve->getGroupes() as $groupes) {
      $devoirs = $groupes->getCahierTexteTravailAFairesJoinUtilisateurProfessionnel();
      if (!$devoirs->isEmpty()){
        foreach ($devoirs as $devoir){
          $dev = array($devoir->getDateCt() => strip_tags($devoir->getContenu(), 'div'));
        }
        $var[$groupes->getDescription()] = $dev;
      }else{
        $var[$groupes->getDescription()] = array(''=>'Regardez le cahier de textes de l\'enfant.');
      }
    }
    return $var;
  }

  /**
   * Renvoie la liste des derniers compte-rendus pour chaque enseignement auxquels est inscrit un �l�ve
   *
   * @param string $_login login de l'�l�ve
   * @return array Liste des Compte-Rendus d'un �l�ve
   */
  public function cdtCREleve($_login){
    return array();
  }

  /**
   * Renvoie la liste des professeurs d'un �l�ve avec leur mati�re associ�e
   *
   * @param string $_login login de l'�l�ve
   * @return array Liste des professeurs de l'�l�ve
   */
  public function professeursEleve(eleve $eleve){
    $reponse = array();
    if (!is_object($eleve)){
      $this->writeLog(__METHOD__, 'objet inexistant', ((array_key_exists('login', $_POST)) ? $_POST['login'] : 'inexistant'));
      Die('Erreur prof-eleve');
    }else{
      foreach ($eleve->getGroupes() as $groupes) {
        $reponse[] = $groupes->getUtilisateurProfessionnels();
      }
    }
    return $reponse;
  }

  /**
   * Renvoie la liste des cours d'un �l�ve au cours de la semaine actuelle
   *
   * @param string $_login
   * @return array edt d'un �l�ve sous la forme d'un tableau php : array('lundi'=>array('M1'=>'Math�matiques',...), 'mardi'=>array(),...)
   */
  public function edtEleve($_login){
    return array();
  }

  /**
   * Loggue les erreurs du serveur dans un fichier
   *
   * @param string $methode m�thode demand�e
   * @param string $message message d'erreur
   * @param string $login_demandeur login du demandeur
   */
  private function writeLog($methode, $message, $login_demandeur){
    // Du code pour �crire dans un fichier de log
    $fichier = fopen('../temp/serveur_ent.log', 'a+');
    fputs($fichier, ($this->_etab !== NULL ? $this->_etab : 'ETAB') . ' :: ' . $login_demandeur . ' = ' . $message . ' -> ' . $methode . ' ' . $_SERVER['REMOTE_ADDR'] . ".\n");
    fclose($fichier);

  }
}
$test = new serveur_ent();
?>