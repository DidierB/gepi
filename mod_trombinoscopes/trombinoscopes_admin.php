<?php
/*
 * Copyright 2001, 2002 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Christian Chapel
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

// mise � jour : 05/09/2006 16:19

// Initialisations files
require_once("../lib/initialisations.inc.php");

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
die();
};
// Check access
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
die();
}
$msg = '';
if (isset($_POST['activer'])) {
    if (!saveSetting("active_module_trombinoscopes", $_POST['activer'])) $msg = "Erreur lors de l'enregistrement du param�tre activation/d�sactivation !";
}
if (isset($_POST['activer_redimensionne'])) {
    if (!saveSetting("active_module_trombinoscopes_rd", $_POST['activer_redimensionne'])) $msg = "Erreur lors de l'enregistrement du param�tre de redimenssionement des photos !";
}
if (isset($_POST['activer_rotation'])) {
    if (!saveSetting("active_module_trombinoscopes_rt", $_POST['activer_rotation'])) $msg = "Erreur lors de l'enregistrement du param�tre rotation des photos !";
}
if (isset($_POST['l_max_aff_trombinoscopes'])) {
    if (!saveSetting("l_max_aff_trombinoscopes", $_POST['l_max_aff_trombinoscopes'])) $msg = "Erreur lors de l'enregistrement du param�tre largeur maximum !";
}
if (isset($_POST['h_max_aff_trombinoscopes'])) {
    if (!saveSetting("h_max_aff_trombinoscopes", $_POST['h_max_aff_trombinoscopes'])) $msg = "Erreur lors de l'enregistrement du param�tre hauteur maximum !";
}
if (isset($_POST['l_max_imp_trombinoscopes'])) {
    if (!saveSetting("l_max_imp_trombinoscopes", $_POST['l_max_imp_trombinoscopes'])) $msg = "Erreur lors de l'enregistrement du param�tre largeur maximum !";
}
if (isset($_POST['h_max_imp_trombinoscopes'])) {
    if (!saveSetting("h_max_imp_trombinoscopes", $_POST['h_max_imp_trombinoscopes'])) $msg = "Erreur lors de l'enregistrement du param�tre hauteur maximum !";
}


if (isset($_POST['is_posted']) and ($msg=='')) $msg = "Les modifications ont �t� enregistr�es !";
// header
$titre_page = "Gestion du module trombinoscope";
require_once("../lib/header.inc");
?>
<!--link rel="stylesheet" href="../mod_absences/styles/mod_absences.css"-->
<p class=bold>
|<a href="../accueil.php">Accueil</a>|
<a href="../accueil_modules.php">Retour administration des modules</a>|
</p>
<H2>Configuration g�n�rale</H2>
<i>La d�sactivation du module trombinoscope n'entra�ne aucune suppression des donn�es. Lorsque le module est d�sactiv�, il n'y a pas d'acc�s au module.</i>
<br />
<form action="trombinoscopes_admin.php" name="form1" method="post">
<input type="radio" name="activer" value="y" <?php if (getSettingValue("active_module_trombinoscopes")=='y') echo " checked"; ?>  />&nbsp;Activer le module trombinoscope<br />
<input type="radio" name="activer" value="n" <?php
	//if (getSettingValue("active_module_trombinoscopes")=='n'){echo " checked";}
	if (getSettingValue("active_module_trombinoscopes")!='y'){echo " checked";}
?>  />&nbsp;D�sactiver le module trombinoscope
<input type="hidden" name="is_posted" value="1" />
<br />
<H2>Configuration d'affichage</H2>
&nbsp;&nbsp;&nbsp;&nbsp;<i>Les valeurs ci-dessous vous servent au param�trage des valeur maxi des largeur et des hauteur.</i><br />
<span style="font-weight: bold;">Pour l'�cran</span><br />
&nbsp;&nbsp;&nbsp;&nbsp;largeur maxi <input name="l_max_aff_trombinoscopes" size="3" maxlength="3" value="<?php echo getSettingValue("l_max_aff_trombinoscopes"); ?>" />&nbsp;
hauteur maxi&nbsp;<input name="h_max_aff_trombinoscopes" size="3" maxlength="3" value="<?php echo getSettingValue("h_max_aff_trombinoscopes"); ?>" />
<br /><span style="font-weight: bold;">Pour l'impression</span><br />
&nbsp;&nbsp;&nbsp;&nbsp;largeur maxi <input name="l_max_imp_trombinoscopes" size="3" maxlength="3" value="<?php echo getSettingValue("l_max_imp_trombinoscopes"); ?>" />&nbsp;
hauteur maxi&nbsp;<input name="h_max_imp_trombinoscopes" size="3" maxlength="3" value="<?php echo getSettingValue("h_max_imp_trombinoscopes"); ?>" />
<br />
<H2>Configuration du redimessionnement des photos</H2>
<i>La d�sactivation du redimessionnement des photos n'entra�ne aucune suppression des donn�es. Lorsque le syst�me de redimessionnement est d�sactiv�, les photos transfer� sur le site ne serons pas r�duite en 340x240.</i>
<br />
<input type="radio" name="activer_redimensionne" value="y" <?php if (getSettingValue("active_module_trombinoscopes_rd")=='y') echo " checked"; ?> />&nbsp;Activer le redimensionnement des photos en 120x160<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Remarque</b> attention GD doit �tre actif sur le serveur de GEPI pour utiliser le redimensionnement.<br />
<input type="radio" name="activer_redimensionne" value="n" <?php if (getSettingValue("active_module_trombinoscopes_rd")=='n') echo " checked"; ?> />&nbsp;D�sactiver le redimensionnement des photos
<ul><li>Rotation de l'image : <input name="activer_rotation" value="" type="radio" <?php if (getSettingValue("active_module_trombinoscopes_rt")=='') { ?>checked="checked"<?php } ?> /> 0�
<input name="activer_rotation" value="90" type="radio" <?php if (getSettingValue("active_module_trombinoscopes_rt")=='90') { ?>checked="checked"<?php } ?> /> 90�
<input name="activer_rotation" value="180" type="radio" <?php if (getSettingValue("active_module_trombinoscopes_rt")=='180') { ?>checked="checked"<?php } ?> /> 180�
<input name="activer_rotation" value="270" type="radio" <?php if (getSettingValue("active_module_trombinoscopes_rt")=='270') { ?>checked="checked"<?php } ?> /> 270� &nbsp;Selectionner une valeur si vous d�sirer une rotation de la photo original</li>
</ul>
<input type="hidden" name="is_posted" value="1" />
<div class="center"><input type="submit" value="Enregistrer" style="font-variant: small-caps;" /></div>
</form>
</body>
</html>
