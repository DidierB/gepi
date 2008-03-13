<?php

/**
 *
 *
 * @version $Id$
 *
 * Classes php qui renvoie les informations sur le serveur
 *
 * @copyright 2008
 */
// S�curit� : �viter que quelqu'un appelle ce fichier seul
$serveur_script = $_SERVER["SCRIPT_NAME"];
$analyse = explode("/", $serveur_script);
$analyse[3] = isset($analyse[3]) ? $analyse[3] : NULL;
	if ($analyse[3] == "serveur_infos.class.php") {
		die();
	}
/**
 * Classe qui renvoie l'ensemble des infos utiles
 * sur les param�tres du serveur
 */
class infos{


	/**
	 * Constructor
	 * @access protected
	 */
	function __construct(){
		// inutile ici
	}

	function infos(){
		// utile pour le compatibilit� php4
	}

	function versionPhp(){
		$test = phpversion();
		// on teste le premier chiffre
		$version = substr($test, 0, 1);
		if ($version == 5) {
			$retour = '<span style="color: green;">'.phpversion().'</span>';
		}elseif($version == 4){
			$retour = '<span style="color: green;">'.phpversion().'(Attention, car php4 ne sera plus suivi pour la s�curit� � partir du 8 ao�t 2008</span>';
		}else{
			$retour = '<span style="color: red;">'.phpversion().'(version ancienne !)</span>';
		}
		return $retour;
	}
	function versionGd(){
		$gd = gd_info();
		return $gd["GD Version"];
	}
	function versionMysql(){
		$test = mysql_get_server_info();
		// On regarde si c'est une version 4 ou 5
		$version = substr($test, 0, 1);
		if ($version == 4 OR $version == 5) {
			$retour = '<span style="color: green;">'.mysql_get_server_info().'</span>';
		}else{
			$retour = '<span style="color: red;">'.mysql_get_server_info().'(version ancienne !)</span>';
		}
		return $retour;
	}
	function listeExtension(){
		$nbre = count(get_loaded_extensions());
		$retour = '<table style="border: 1px solid black;">';
		for($a = 0; $a < $nbre; $a++){
			$extensions = get_loaded_extensions();

			$b = $a + 1;
			$c = $a + 2;
			$d = $a + 3;
			$retour .= '<tr>
				<td style="border: 1px solid black;">'.$extensions[$a].'</td>';
			if (isset($extensions[$b])) {
			$retour .= '
				<td style="border: 1px solid black;">'.$extensions[$b].'</td>';
			}else{
				$retour .= '<td>-</td>';
			}
			if (isset($extensions[$c])) {
				$retour .= '
					<td style="border: 1px solid black;">'.$extensions[$c].'</td>';
			}else{
				$retour .= '<td>-</td>';
			}
			if (isset($extensions[$d])) {
				$retour .= '
					<td style="border: 1px solid black;">'.$extensions[$d].'</td>';
			}else{
				$retour .= '<td>-</td>';
			}
				$retour .= '
					</tr>';
			$a = $a + 3;
		}
		$retour .= '</table><br />';

		return $retour;
	}
	function memoryLimit(){
		return ini_get('memory_limit');
	}
	function maxSize(){
		return ini_get('post_max_size');
	}
} // fin class infos

?>