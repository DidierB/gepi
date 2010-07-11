<?php
/*
 * $Id: $
*/

/**
 * Contenu � afficher dans /gestion/droit_acces.php
 *
 * @author regis
 */
class class_droit_acces_template {

  private $msg = "";
  private $statut="";
  private $valeur="";
  private $name="";
  private $texte="";
  private  $item="";
  private $enregistre="";


/**
 * Contenu � afficher dans /gestion/droit_acces.php
 *
 * @author regis
 */
  function  __construct($donneesPassee=NULL) {

	$this->enregistre=$donneesPassee;

  }

  private function enregistre($nom){
	if (isset($_POST[$nom])) {
		$temp = 'yes';
	} else {
		$temp = 'no';
	}
	if (!saveSetting($nom, $temp)) {
		$msg .= "Erreur lors de l'enregistrement de ".$nom." avec la valeur ".$temp." !<br />";
	}
  }
 
/**
 * R�cup�re les donn�es � afficher et enregistre au besoin les r�glages dans la table setting
 *
 * @var $statutPasse : Statut � r�gler
 * @var $namePasse : Nom de la variable � enregistrer dans la table setting
 * @var $valuePasse : Valeur de la variable � enregistrer dans la table setting
 * @var $textePasse : Texte � afficher dans la page
 */
  public function set_entree($statutPasse, $namePasse, $textePasse){

	if ($this->enregistre){
	  $this->enregistre($namePasse);
	}
	
	$this->item[]=array('statut' => $statutPasse, 'name' => $namePasse, 'texte' => $textePasse);

	return TRUE;

  }

/**
 * Renvoie les donn�es � afficher d'un item
 *
 */
  public function get_item(){

	return $this->item;
  }

}
?>
