<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2007
 */
// S�curit� suppl�mentaire par rapport aux param�tres du module EdT / Calendrier
if (param_edt($_SESSION["statut"]) != "yes") {
	Die('Vous devez demander � votre administrateur l\'autorisation de voir cette page.');
}

// CSS et js particulier � l'EdT
$javascript_specifique = "edt_organisation/script/fonctions_edt";
$style_specifique = "edt_organisation/style_edt";

?>

VOIR Sandrine pour l'initialisation &agrave; partir d'un xml d'export vers STSWeb