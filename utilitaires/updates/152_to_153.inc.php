<?php
/* 
 * Fichier de mise � jour de la version 1.5.2 � la version 1.5.3
 * Le code PHP pr�sent ici est ex�cut� tel quel.
 * Pensez � conserver le code parfaitement compatible pour une application
 * multiple des mises � jour. Toute modification ne doit �tre r�alis�e qu'apr�s
 * un test pour s'assurer qu'elle est n�cessaire.
 *
 * Le r�sultat de la mise � jour est du html pr�format�. Il doit �tre concat�n�
 * dans la variable $result, qui est d�j� initialis�.
 *
 * Exemple : $result .= "<font color='gree'>Champ XXX ajout� avec succ�s</font>";
 */

$result .= "&nbsp;->Extension � 255 caract�res du champ 'SESSION_ID' de la table 'log'<br />";
$query = mysql_query("ALTER TABLE `log` CHANGE `SESSION_ID` `SESSION_ID` VARCHAR( 255 ) NOT NULL;");
if ($query) {
        $result .= "<font color=\"green\">Ok !</font><br />";
} else {
        $result .= "<font color=\"red\">Erreur</font><br />";
}

//===================================================


?>
