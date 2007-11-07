<?php
// Fichier utilis� par l'administrateur pour param�trer l'EdT de Gepi

$titre_page = "Emploi du temps - Param�tres";
$affiche_connexion = 'yes';
$niveau_arbo = 1;

// Initialisations files
require_once("../lib/initialisations.inc.php");

// fonctions edt
require_once("./fonctions_edt.php");

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}

// S�curit�
if (!checkAccess()) {
    header("Location: ../logout.php?auto=2");
    die();
}
// S�curit� suppl�mentaire par rapport aux param�tres du module EdT / Calendrier
if (param_edt($_SESSION["statut"]) != "yes") {
	Die('Vous devez demander � votre administrateur l\'autorisation de voir cette page.');
}
// CSS et js particulier � l'EdT
$javascript_specifique = "edt_organisation/script/fonctions_edt";
$style_specifique = "edt_organisation/style_edt";
//=========Utilisation de prototype et des js de base ===========
$utilisation_prototype = "";
$utilisation_jsbase = "";
//=========Fin des Prototype et autres js =======================
// On ins�re l'ent�te de Gepi
require_once("../lib/header.inc");

// On ajoute le menu EdT
require_once("./menu.inc.php"); ?>


<br />
<!-- la page du corps de l'EdT -->

	<div id="lecorps">
<center>
<?php

// Initialiser les variables
$edt_aff_matiere=isset($_POST['edt_aff_matiere']) ? $_POST['edt_aff_matiere'] : NULL;
$edt_aff_creneaux=isset($_POST['edt_aff_creneaux']) ? $_POST['edt_aff_creneaux'] : NULL;
$edt_aff_couleur=isset($_POST['edt_aff_couleur']) ? $_POST['edt_aff_couleur'] : NULL;
$edt_aff_salle=isset($_POST['edt_aff_salle']) ? $_POST['edt_aff_salle'] : NULL;
$aff_cherche_salle = isset($_POST["aff_cherche_salle"]) ? $_POST["aff_cherche_salle"] : NULL;
$parametrer=isset($_POST['parametrer']) ? $_POST['parametrer'] : NULL;
$parametrer_ok=isset($_POST['parametrer1']) ? $_POST['parametrer1'] : NULL;
$param_menu_edt = isset($_POST["param_menu_edt"]) ? $_POST["param_menu_edt"] : NULL;


// R�cup�rer les param�tres tels qu'ils sont d�j� d�finis
if (isset($parametrer_ok)) {

	// Le r�glage de l'affichage des mati�res
	$req_reg_mat = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_matiere'");
	$tab_reg_mat = mysql_fetch_array($req_reg_mat);

	if ($edt_aff_matiere === $tab_reg_mat['valeur']) {
		echo "<p class=\"accept\">Aucune modification de l'affichage des mati�res</p>\n";
	}
	else {
		$modif_aff_mat = mysql_query("UPDATE edt_setting SET valeur = '$edt_aff_matiere' WHERE reglage = 'edt_aff_matiere'");
		echo "<p class=\"refus\"> Modification de l'affichage des mati�res enregistr�e</p>\n";
	}

	// Le r�glage de l'affichage du type d'heure
	$req_reg_cre = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_creneaux'");
	$tab_reg_cre = mysql_fetch_array($req_reg_cre);

	if ($edt_aff_creneaux === $tab_reg_cre['valeur']) {
		echo "<p class=\"accept\">Aucune modification de l'affichage des cr�neaux</p>\n";
	}
	else {
		$modif_aff_cre = mysql_query("UPDATE edt_setting SET valeur = '$edt_aff_creneaux' WHERE reglage = 'edt_aff_creneaux'");
		echo "<p class=\"refus\"> Modification de l'affichage des cr�neaux enregistr�e</p>\n";
	}

	// Le r�glage de l'affichage des couleurs
	$req_reg_coul = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_couleur'");
	$tab_reg_coul = mysql_fetch_array($req_reg_coul);

	if ($edt_aff_couleur === $tab_reg_coul['valeur']) {
		echo "<p class=\"accept\">Aucune modification des couleurs</p>\n";
	}
	else {
		$modif_aff_coul = mysql_query("UPDATE edt_setting SET valeur = '$edt_aff_couleur' WHERE reglage = 'edt_aff_couleur'");
		echo "<p class=\"refus\"> Modification de l'affichage des couleurs enregistr�e</p>\n";
	}

	//Le r�glage de l'affichage des salles
	$req_reg_salle = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'edt_aff_salle'");
	$tab_reg_salle = mysql_fetch_array($req_reg_salle);

	if ($edt_aff_salle === $tab_reg_salle['valeur']) {
		echo "<p class=\"accept\">Aucune modification de l'affichage des salles</p>\n";
	}
	else {
		$modif_aff_salle = mysql_query("UPDATE edt_setting SET valeur = '$edt_aff_salle' WHERE reglage = 'edt_aff_salle'");
		echo "<p class=\"refus\"> Modification de l'affichage des salle enregistr�e</p>\n";

	}

	// le r�glage de l'affichage du menu CHERCHER
	$req_cherche_salle = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'aff_cherche_salle'");
	$rep_cherche_salle = mysql_fetch_array($req_cherche_salle);

	if ($aff_cherche_salle === $rep_cherche_salle["valeur"]) {
		echo "<p class=\"accept\">Aucune modification de l'affichage du menu CHERCHER</p>\n";
	}
	else {
		$modif_cherch_salle = mysql_query("UPDATE edt_setting SET valeur = '$aff_cherche_salle' WHERE reglage = 'aff_cherche_salle'");
		echo "<p class=\"refus\">Modification de l'affichage du menu CHERCHER enregistr�e</p>\n";
	}

	// Le r�glage du fonctionnement du menu (param_menu_edt)
	$req_param_menu = mysql_query("SELECT valeur FROM edt_setting WHERE reglage = 'param_menu_edt'");
	$rep_param_menu = mysql_fetch_array($req_param_menu);

	if ($param_menu_edt === $rep_param_menu["valeur"]) {
		echo "<p class=\"accept\">Aucune modification du fonctionnement du menu.</p>\n";
	} else {
		$modif_param_menu = mysql_query("UPDATE edt_setting SET valeur = '$param_menu_edt' WHERE reglage = 'param_menu_edt'");
		echo "<p class=\"refus\">Modification du fonctionnement du menu enregistr�e.</p>\n";
	}

} //if (isset($parametrer_ok))
else {
	echo "Dans cette page, vous pouvez param�trer l'affichage des emplois du temps pour tous les utilisateurs de Gepi.";
}
?>
</center>
<form name="parametrer" method="post" action="edt_parametrer.php">
<table cellpadding="5" cellspacing="0" border="0" style="height: 150px; width: 100%;">
<tr><td>

<fieldset id="matiere">
	<legend>Les mati�res</legend>
		<span class="parametres">
			<input type="radio" name="edt_aff_matiere" value="court" <?php echo (aff_checked("edt_aff_matiere", "court")); ?>/>
			Noms courts (du type HG,...)
<br />
			<input type="radio" name="edt_aff_matiere" value="long" <?php echo (aff_checked("edt_aff_matiere", "long")); ?>/>
			Noms longs (Histoire G�ographie,...)

		</span>
</fieldset>

</td><td>
<fieldset id="horaires">
	<legend>Affichage des horaires</legend>
		<span class="parametres">
			<input type="radio" name="edt_aff_creneaux" value="noms" <?php echo (aff_checked("edt_aff_creneaux", "noms")); ?>/>
			Afficher le nom des cr&eacute;neaux (M1, M2,...)
<br />
			<input type="radio" name="edt_aff_creneaux" value="heures" <?php echo (aff_checked("edt_aff_creneaux", "heures")); ?>/>
			Afficher les heures de d&eacute;but et de fin du cr&eacute;neau
		</span>
</fieldset>

</td></tr>
</table>

<table cellpadding="5" cellspacing="0" border="0" style="height: 150px; width: 100%;">
<tr><td>
<fieldset id="couleurs">
	<legend>Affichage g�n�ral en couleur</legend>
		<span class="parametres">
			<input type="radio" name="edt_aff_couleur" value="coul" <?php echo (aff_checked("edt_aff_couleur", "coul")); ?>/>
			Couleurs
<br />
			<input type="radio" name="edt_aff_couleur" value="nb" <?php echo (aff_checked("edt_aff_couleur", "nb")); ?>/>
			Sans couleurs
		</span>
</fieldset>

</td><td>
<fieldset id="salles">
	<legend>Affichage des salles</legend>
		<span class="parametres">
			<input type="radio" name="edt_aff_salle" value="nom" <?php echo (aff_checked("edt_aff_salle", "nom")); ?>/>
			Par le nom de la salle (salle 2, salle de r&eacute;union,...)
<br />
			<input type="radio" name="edt_aff_salle" value="numero" <?php echo (aff_checked("edt_aff_salle", "numero")); ?>/>
			Par le num&eacute;ro de la salle uniquement
		</span>
</fieldset>
</td></tr>
</table>

<table cellpadding="5" cellspacing="0" border="0" style="height: 150px; width: 100%;">
	<tr>
		<td>
<fieldset id="aff_cherche_salle">
	<legend>Fonction chercher les salles vides</legend>
		<span class="parametres">
			<input type="radio" name="aff_cherche_salle" value="admin" <?php echo (aff_checked("aff_cherche_salle", "admin")); ?>/>
			Seul l'administrateur a acc&egrave;s &agrave; cette fonctionnalit&eacute;.
<br />
			<input type="radio" name="aff_cherche_salle" value="tous" <?php echo (aff_checked("aff_cherche_salle", "tous")); ?>/>
			Tous les utilisateurs ont acc&egrave;s &agrave; cette fonctionnalit&eacute; sauf les &eacute;l&egrave;ves et les responsables d'&eacute;l&egrave;ves.
		</span>
</fieldset>
		</td>
		<td></td>
	</tr>
</table>

<fieldset id="param_edtmenu">
	<legend>Le fonctionnement du menu</legend>
	<p>
		<input type="radio" id="edtMenuOver" name="param_menu_edt" value="mouseover" <?php echo (aff_checked("param_menu_edt", "mouseover")); ?>/>
		<label for="edtMenuOver">Les liens s'affichent quand la souris passe sur le titre.</label>
	</p>

	<p>
		<input type="radio" id="edtMenuClick" name="param_menu_edt" value="click" <?php echo (aff_checked("param_menu_edt", "click")); ?>/>
		<label for="EdTMenuClick">Les liens s'affichent quand l'utilisateur clique sur le titre.</label>
	</p>

	<p>
		<input type="radio" id="edtMenuRien" name="param_menu_edt" value="rien" <?php echo (aff_checked("param_menu_edt", "rien")); ?>/>
		<label for="EdtMenuRien">Tous les liens sont visibles tout le temps.</label>
	</p>
</fieldset>
	<input type="hidden" name="parametrer" value="ok" />
	<input type="hidden" name="parametrer1" value="ok" />
	<input type="submit" name="Valider" value="Valider" />

</form>
	</div>
<!--Fin du corps de la page-->
<br />
<br />
<?php
// inclusion du footer
require("../lib/footer.inc.php");
?>
