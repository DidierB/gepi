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

?>

VOIR Sandrine pour l'initialisation &agrave; partir d'un xml d'export vers STSWeb