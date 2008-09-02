<?php

// Pour les scripts situ�s � la racine de GEPI
if (isset($niveau_arbo) and ($niveau_arbo == "0")) {
  if (file_exists("./secure/config_lcs.inc.php"))
    include "./secure/config_lcs.inc.php";
// Pour les scripts situ�s dans un sous-r�pertoire � l'int�rieur d'une sous-r�pertoire de GEPI
} else if (isset($niveau_arbo) and ($niveau_arbo == "2")) {
  if (file_exists("../../secure/config_lcs.inc.php"))
    include "../../secure/config_lcs.inc.php";
// Pour les scripts situ�s dans un sous-sous-r�pertoire � l'int�rieur d'une sous-r�pertoire de GEPI
} else if (isset($niveau_arbo) and ($niveau_arbo == "3")) {
  if (file_exists("../../../secure/config_lcs.inc.php"))
    include "../../../secure/config_lcs.inc.php";
} else {
  if (file_exists("../secure/config_lcs.inc.php"))
    include "../secure/config_lcs.inc.php";
}

$version = substr(phpversion(), 0, 1);
if ($version == 4) {
  $ldap_class = "lib/LDAPServer.php4.class.php";
  $session_class = "lib/Session.php4.class.php";
} else {
  $ldap_class = "lib/LDAPServer.class.php";
  $session_class = "lib/Session.class.php";
}

// Pour les scripts situ�s � la racine de GEPI
if (isset($niveau_arbo) and ($niveau_arbo == "0")) {
   // Database configuration file
   require_once("./secure/connect.inc.php");
   // Database connection
   require_once("./lib/mysql.inc");
   // Global configuration file
   require_once("./lib/global.inc");
   // Traitement des donn�e
   require_once("./lib/traitement_data.inc.php");
   // Libraries
   include "./lib/share.inc.php";
   // Fonctions relatives aux groupes
    include "./lib/groupes.inc.php";
   // Fonctions relatives aux groupes
    include "./lib/classes.inc.php";
   // Settings
   require_once("./lib/settings.inc");
   // Load settings
   if (!loadSettings()) {
     die("Erreur chargement settings");
   }
   // Session related functions
   require_once("./".$ldap_class);
   require_once("./".$session_class);

// Pour les scripts situ�s dans un sous-r�pertoire � l'int�rieur d'une sous-r�pertoire de GEPI
} else if (isset($niveau_arbo) and ($niveau_arbo == "2")) {
   // Database configuration file
   require_once("../../secure/connect.inc.php");
   // Database connection
   require_once("../../lib/mysql.inc");
   // Global configuration file
   require_once("../../lib/global.inc");
   // Traitement des donn�e
   require_once("../../lib/traitement_data.inc.php");
   // Libraries
   include "../../lib/share.inc.php";
   // Fonctions relatives aux groupes
   include "../../lib/groupes.inc.php";
   // Fonctions relatives aux groupes
   include "../../lib/classes.inc.php";
   // Settings
   require_once("../../lib/settings.inc");
   // Load settings
   if (!loadSettings()) {
       die("Erreur chargement settings");
   }
   // Session related functions
   require_once("../../".$ldap_class);
   require_once("../../".$session_class);

// Pour les scripts situ�s dans un sous-sous-r�pertoire � l'int�rieur d'une sous-r�pertoire de GEPI
} else if (isset($niveau_arbo) and ($niveau_arbo == "3")) {
   // Database configuration file
   require_once("../../../secure/connect.inc.php");
   // Database connection
   require_once("../../../lib/mysql.inc");
   // Global configuration file
   require_once("../../../lib/global.inc");
   // Traitement des donn�e
   require_once("../../../lib/traitement_data.inc.php");
   // Libraries
   include "../../../lib/share.inc.php";
   // Fonctions relatives aux groupes
   include "../../../lib/groupes.inc.php";
   // Fonctions relatives aux groupes
   include "../../../lib/classes.inc.php";
   // Settings
   require_once("../../../lib/settings.inc");
   // Load settings
   if (!loadSettings()) {
       die("Erreur chargement settings");
   }
   // Session related functions
   require_once("../../../".$ldap_class);
   require_once("../../../".$session_class);

// Pour les scripts situ�s dans le sous-r�pertoire "public"
// Ces scripts font appel au fichier /public/secure/connect.inc et non pas /secure/connect.inc
} else if (isset($niveau_arbo) and ($niveau_arbo == "public")) {
   // Database configuration file
    require_once("./secure/connect.inc.php");
    // Database
    require_once("../lib/mysql.inc");
    // Global configuration file
    require_once("../lib/global.inc");
    // Traitement des donn�es
    require_once("../lib/traitement_data.inc.php");
    // Libraries
    include "../lib/share.inc.php";
    // Fonctions relatives aux groupes
    include "../lib/groupes.inc.php";
    // Settings
    require_once("../lib/settings.inc");
    // Load settings
    if (!loadSettings()) {
        die("Erreur chargement settings");
    }
   // Session related functions
   require_once("../".$ldap_class);
   require_once("../".$session_class);

// Pour les scripts situ�s dans un sous-r�pertoire GEPI
} else {
   // Database configuration file
   require_once("../secure/connect.inc.php");
   // Database connection
   require_once("../lib/mysql.inc");
   // Global configuration file
   require_once("../lib/global.inc");
   // Traitement des donn�e
   require_once("../lib/traitement_data.inc.php");
   // Libraries
   include "../lib/share.inc.php";
    // Fonctions relatives aux groupes
    include "../lib/groupes.inc.php";
     // Fonctions relatives aux groupes
    include "../lib/classes.inc.php";
   // Settings
   require_once("../lib/settings.inc");
   // Load settings
   if (!loadSettings()) {
       die("Erreur chargement settings");
   }
   // Session related functions
   require_once("../".$ldap_class);
   require_once("../".$session_class);
}

	// Modif pour la longueur des logins par $longmax_login du global.inc
	// Si le champ de setting existe alors il faut l'utiliser car il est r�gl� par la page param_gen.php
	if(isset($gepiSettings['longmax_login'])){
		$longmax_login = $gepiSettings['longmax_login'];
	}

// Initialisaton de la session Gepi :
$session_gepi = new Session();

?>