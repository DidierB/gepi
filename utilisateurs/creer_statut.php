<?php

/**
 *
 * Modif table `droits` : ALTER TABLE `droits` ADD `autre` VARCHAR( 1 ) NOT NULL DEFAULT 'F' AFTER `secours` ;
 * @version $Id$
 * @copyright 2008
 */
$affiche_connexion = 'yes';
$niveau_arbo = 1;
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

// S�curit�
if (!checkAccess()) {
    header("Location: ../logout.php?auto=2");
    die();
}
if (getSettingValue("statuts_prives") != "y") {
	trigger_error('Impossible d\'acc�der � ce module de Gepi.', E_USER_ERROR);
}

//	include("utilisateurs.class.php");
$titre_page = 'Gestion des nouveaux statuts';
include("../lib/header.inc");


// ========================================= Variables ============================
$action = isset($_POST["action"]) ? $_POST["action"] : NULL;
$nouveau_statut = isset($_POST["nouveau_statut"]) ? $_POST["nouveau_statut"] : NULL;
$login_user = isset($_POST["login_user"]) ? $_POST["login_user"] : NULL;
$statut_user = isset($_POST["statut_user"]) ? $_POST["statut_user"] : NULL;
$msg = $msg2 = NULL;

// Ces tableaux d�finissent les diff�rents fichiers � autoriser en fonction du statut
$values_b = '';
// droits g�n�raux et communs � tous les utilisateurs
$autorise = array('/accueil.php',
				'/utilisateurs/mon_compte.php',
				'/gestion/contacter_admin.php',
				'/gestion/info_gepi.php');
// droits sp�cifiques sur les pages relatives aux droits possibles
$notes = array('/cahier_notes/visu_releve_notes.php');
$bull_simp = array('/prepa_conseil/index3.php', '/prepa_conseil/edit_limite.php');
$voir_absences = array('/mod_absences/gestion/voir_absences_viescolaire.php',
						'/mod_absences/gestion/bilan_absences_quotidien.php',
						'/mod_absences/gestion/bilan_absences_classe.php',
						'/mod_absences/gestion/bilan_absences_quotidien_pdf.php',
						'/mod_absences/lib/tableau.php',
						'/mod_absences/lib/export_csv.php');
$saisir_absences = array('/mod_absences/gestion/select.php',
						'/mod_absences/gestion/ajout_abs.php',
						'/mod_absences/lib/liste_absences.php');
$cdt = array('cahier_texte/see_all.php');
$edt = array('/edt_organisation/index_edt.php');

if ($action == 'ajouter') {

	// on fait quelques v�rifications sur le nom du statut (si il existe d�j�, longueur du nom, enlever les ' et les ",...)
	// On ne garde que les 12 premi�res lettres
	$stat_1 = substr($nouveau_statut, 0, 12);
	// On enl�ve les accents
	$stat_2 = strtr($stat_1, "���������������", "eeeeiiooaaauuuc");
	// On enl�ve les apostrophes et les guillemets
	$insert_statut = htmlentities($stat_2, ENT_QUOTES);

	// On ajoute le statut priv� apr�s avoir v�rifi� qu'il n'existe pas d�j�
	$query_v = mysql_query("SELECT id FROM droits_statut WHERE nom_statut = '".$insert_statut."'");
	$verif = mysql_num_rows($query_v);

	if ($verif >= 1) {

		$msg .= "<h3 class='red'>Ce statut priv&eacute;, existe d&eacute;j&agrave; !</h3>";

	}else{

		$sql = "INSERT INTO droits_statut (id, nom_statut) VALUES ('', '".$insert_statut."')";
		$enregistre = mysql_query($sql) OR trigger_error('Impossible d\'enregistrer ce nouveau statut', E_USER_WARNING);
		$cherche_id = mysql_query("SELECT id FROM droits_statut WHERE nom_statut = '".$insert_statut."'");
		$last_id = mysql_result($cherche_id, "id");

		if ($enregistre) {

			// On enregistre les droits g�n�raux ad�quats
			for($a = 0 ; $a < 4 ; $a ++){
				$values_b .= '("", "'.$last_id.'", "'.$autorise[$a].'", "V")';
				if ($a <= 2) {
					$values_b .= ', ';
				}
			}

 			$autorise_b = mysql_query("INSERT INTO droits_speciaux (id, id_statut, nom_fichier, autorisation) VALUES ".$values_b."")
			 										OR trigger_error('Impossible d\'enregistrer : '.$values.' : '.mysql_error(), E_USER_WARNING);

			if ($autorise_b) {
				$msg .= "<h3 class='green'>Ce statut est enregistr&eacute !</h3>";
			}

		}

	}


} // if ($action == 'ajouter')

if ($action == 'modifier') {
	// On initialise toutes les variables envoy�es
	$sql = "SELECT id, nom_statut FROM droits_statut ORDER BY nom_statut";
	$query = mysql_query($sql);
	$nbre = mysql_num_rows($query);

	for($a = 0; $a < $nbre; $a++){

		$b = mysql_result($query, $a, "id");

		$suppr[$a] = isset($_POST["suppr|".$b]) ? $_POST["suppr|".$b] : NULL;
		$ne[$a] = isset($_POST["ne|".$b]) ? $_POST["ne|".$b] : NULL;
		$bs[$a] = isset($_POST["bs|".$b]) ? $_POST["bs|".$b] : NULL;
		$va[$a] = isset($_POST["va|".$b]) ? $_POST["va|".$b] : NULL;
		$sa[$a] = isset($_POST["sa|".$b]) ? $_POST["sa|".$b] : NULL;
		$cdt[$a] = isset($_POST["cdt|".$b]) ? $_POST["cdt|".$b] : NULL;
		$ee[$a] = isset($_POST["ee|".$b]) ? $_POST["ee|".$b] : NULL;
		$te[$a] = isset($_POST["te|".$b]) ? $_POST["te|".$b] : NULL;

		// On assure les diff�rents traitements traitements
		if ($suppr[$a] == 'on') {
			// On supprime le statut demand�
			$sql_d = "DELETE FROM droits_statut WHERE id = '".$b."'";
			$query_d = mysql_query($sql_d) OR trigger_error('Impossible de supprimer ce statut : '.mysql_error(), E_USER_NOTICE);
		}

	}
}


// On r�cup�re tous les statuts nouveaux qui existent
$aff_tableau = $aff_select = $aff_users = $selected = '';
$sql = "SELECT id, nom_statut FROM droits_statut ORDER BY nom_statut";
$query = mysql_query($sql);

if ($query) {
	while($rep = mysql_fetch_array($query)){

	$aff_tableau .= '
	<tr style="border: 1px solid lightblue; text-align: center;">
		<td style="font-weight: bold; color: red;">'.$rep["nom_statut"].'</td>
		<td><input type="checkbox" name="ne|'.$rep["id"].'" /></td>
		<td><input type="checkbox" name="bs|'.$rep["id"].'" /></td>
		<td><input type="checkbox" name="va|'.$rep["id"].'" /></td>
		<td><input type="checkbox" name="sa|'.$rep["id"].'" /></td>
		<td><input type="checkbox" name="cdt|'.$rep["id"].'" /></td>
		<td><input type="checkbox" name="ee|'.$rep["id"].'" /></td>
		<td><input type="checkbox" name="te|'.$rep["id"].'" /></td>
		<td><input type="checkbox" name="suppr|'.$rep["id"].'" /></td>
	</tr>
	<tr style="background-color: white;"><td colspan="9"></td></tr>';
	}
}

// On traite la partie sur les utilisateurs 'autre' pour leur d�finir le bon statut

	// On traite les demandes de l'admin sur la d�finition des statuts des utilisateurs 'autre'
	if ($action == "defStatut") {
		// On v�rifie si cet utilisateur existe d�j�
		$query_v2 = mysql_query("SELECT id_statut FROM droits_utilisateurs WHERE login_user = '".$login_user."'")
									OR trigger_error('Impossible de v�rifier le statut priv� de cet utilisateur.', E_USER_WARNING);
		$verif_v2 = mysql_num_rows($query_v2);
		if ($verif_v2 >= 1) {
			// alors le statut de cet utilisateur existe, on va donc le mettre � jour
			$sql_d = "UPDATE droits_utilisateurs SET id_statut = '".$statut_user."' WHERE login_user = '".$login_user."'";
		}else{
			$sql_d = "INSERT INTO droits_utilisateurs (id, id_statut, login_user) VALUES ('', '".$statut_user."', '".$login_user."')";
		}

		$query_statut = mysql_query($sql_d) OR trigger_error('Impossible d\'enregistrer dans la base.'.mysql_error(), E_USER_WARNING);

		if ($query_statut) {
			$msg2 .= '<h4 style="color: green;">Modification enregistr�e.</h4>';
		}

	}

	// On r�cup�re les utilisateurs qui ont un statut 'autre'
	$sql_u = "SELECT nom, prenom, login  FROM utilisateurs
											WHERE statut = 'autre'
											AND etat = 'actif'";
	$query_u = mysql_query($sql_u);

	// On affiche la liste des utilisateurs avec un select des statuts priv�s
	$i = 1;
	while($tab = mysql_fetch_array($query_u)){

		// On r�cup�re son statut s'il existe
		$query_s = mysql_query("SELECT id_statut FROM droits_utilisateurs WHERE login_user = '".$tab["login"]."'");
		$statut = mysql_result($query_s, "id_statut");

		$aff_users .= '
		<tr>
			<td>'.$tab["nom"].' '.$tab["prenom"].'</td>
			<td>
		<form name="form'.$i.'" action="creer_statut.php" method="post">
			<input type="hidden" name="action" value="defStatut" />
			<input type="hidden" name="login_user" value="'.$tab["login"].'" />

			<select name="statut_user" onchange=\'document.form'.$i.'.submit();\'>
				<option value="rien">Choix du statut</option>';

		$sql = "SELECT id, nom_statut FROM droits_statut ORDER BY nom_statut";
		$query = mysql_query($sql);
		while($rep = mysql_fetch_array($query)){
			if ($statut == $rep["id"]) {
				$selected = ' selected="selected"';
			}else{
				$selected = '';
			}
			$aff_users .= '
				<option value="'.$rep["id"].'"'.$selected.'>'.$rep["nom_statut"].'</option>';
		}

		$aff_users .= '
			</select>
		</form>
		</td></tr>';

		$i++;

	}



?>
<!-- D�but de la page sur les statut priv�s -->

<br />
<?php echo $essai; ?>
<?php echo $msg; ?>
<p>Pour pouvoir donner un statut priv&eacute; &agrave; un utilisateur, il faut qu'il soit enregistrer avec le statut Gepi 'autre' lors de sa cr&eacute;ation
(<a href="./modify_user.php">CREER UN UTILISATEUR</a>).
 Vous pourrez ensuite d&eacute;finir des statuts priv&eacute;s et leur donner des droits. Pour terminer, il suffira de faire le lien entre les stauts priv&eacute;s et les utilisateurs
 en bas de cette page.</p>

<div style="background-color: lightblue;">
<p style="color: grey; text-align: right; font-style: italic;">Gestion des droits des statuts priv&eacute;s&nbsp;&nbsp;</p>

<form action="creer_statut.php" method="post">
	<input type="hidden" name="action" value="modifier" />

<table style="border: 1px solid lightblue; background: #CCFFFF;">
	<thead>
	<tr>
		<th style="border: 1px solid lightblue;">Statut</th>
		<th style="border: 1px solid lightblue;">Voir les notes des �l�ves</th>
		<th style="border: 1px solid lightblue;">Voir les bulletins simplifi�s</th>
		<th style="border: 1px solid lightblue;">Voir les absences des �l�ves</th>
		<th style="border: 1px solid lightblue;">Saisir les absences des �l�ves</th>
		<th style="border: 1px solid lightblue;">Voir les cahiers de textes</th>
		<th style="border: 1px solid lightblue;">Voir les emplois du temps des �l�ves</th>
		<th style="border: 1px solid lightblue;">Voir tous les emplois du temps</th>
		<th style="border: 1px solid lightblue;">Supprimer le statut</th>
	</tr>
	<tr style="background-color: white;"><td colspan="9"></td></tr>
	</thead>
	<tbody>

		<?php echo $aff_tableau; ?>
		<tr style="background-color: white;"><td colspan="9"></td></tr>
	</tbody>
	<tfoot>
		<tr><td colspan="8"><input type="submit" name="modifier" value="Enregistrer et mettre &agrave; jour" /></td></tr>
	</tfoot>
</table>

</form>

</div>

<br />

<p style="cursor: pointer;" onClick="changementDisplay('ajoutStatut', '');">Ajouter un statut priv&eacute;</p>
<div id="ajoutStatut" style="display: none;">

	<form method="post" action="creer_statut.php">
		<p>
		<label for="new">Nom du nouveau statut</label>
		<input type="text" name="nouveau_statut" value="" />
		<input type="hidden" name="action" value="ajouter" />

		<input type="submit" name="Ajouter" value="Ajouter" />
		</p>

		<p style="color: grey; font-style: italic; margin-left: 10em;">Il vaut mieux ne mettre que des lettres. Longueur maximum : 12 caract&egrave;res.</p>

	</form>

</div>

<br />
<hr />
<br />
<!-- Quel statut pour quelle personne ? -->

<div id="userStatut" style="border: 5px solid silver; width: 20em;">

	<p style="text-align: right; font-style: italic; color: grey; background-color: lightblue;">Gestion des statuts priv&eacute;s</p>

	<table>

		<?php echo $aff_users; ?>
	</table>
		<?php echo $msg2; ?>
</div>

<?php
require("../lib/footer.inc.php");