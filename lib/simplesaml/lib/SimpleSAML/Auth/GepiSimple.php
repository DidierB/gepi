<?php

/**
 * Classe pour l'authentification dans gepi
 *
 * Provides the same interface as Auth_Simple.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SimpleSAML_Auth_GepiSimple extends SimpleSAML_Auth_Simple {


	/**
	 * Initialise une authentification en utilisant les param�tre renseign�s dans gepi
	 *
	 * @param string|NULL $auth  The authentication source. Si non pr�cis�, utilise la source configur�e dans gepi.
	 */
	public function __construct($auth = null) {
		if ($auth == null) {
		    //on va s�lectionner la source d'authentification gepi
		    $path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
		    include_once("$path/secure/connect.inc.php");
		    // Database connection
		    require_once("$path/lib/mysql.inc");
		    require_once("$path/lib/settings.inc");
		    // Load settings
		    if (!loadSettings()) {
				die("Erreur chargement settings");
		    }
		    $auth = getSettingValue('auth_simpleSAML_source');
		}
		
		$config = SimpleSAML_Configuration::getOptionalConfig('authsources.php');
		$sources = $config->getOptions();
		if (!count($sources)) {
			echo 'Erreur simplesaml : Aucune source configur�e';
			die;
		}
		if (!in_array($auth, $sources)) {
			echo 'Erreur simplesaml : source '.$auth.' non configur�e. Utilisation par d�faut de la source : �Authentification au choix entre toutes les sources configurees�.';
			$auth = 'Authentification au choix entre toutes les sources configurees';
		}
			
		parent::__construct($auth);
	}
	
}
