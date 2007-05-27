<?php
/*
 * $Id$
 *
 * Copyright 2001-2004 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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

// On indique qu'il faut creer des variables non prot�g�es (voir fonction cree_variables_non_protegees())
$variables_non_protegees = 'yes';

// Begin standart header
$titre_page = "Param�tres de configuration des bulletins scolaires HTML";

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
include("../fckeditor/fckeditor.php") ;

// Check access
if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}
$reg_ok = 'yes';
$msg = '';
$bgcolor = "#DEDEDE";

if (isset($_POST['textsize'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['textsize'])) || $_POST['textsize'] < 1) {
        $_POST['textsize'] = 10;
    }
    if (!saveSetting("textsize", $_POST['textsize'])) {
        $msg .= "Erreur lors de l'enregistrement de textsize !";
        $reg_ok = 'no';
    }
}

//==================================
// AJOUT: boireaus
if (isset($_POST['p_bulletin_margin'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['p_bulletin_margin'])) || $_POST['p_bulletin_margin'] < 1) {
        $_POST['p_bulletin_margin'] = 5;
    }
    if (!saveSetting("p_bulletin_margin", $_POST['p_bulletin_margin'])) {
        $msg .= "Erreur lors de l'enregistrement de p_bulletin_margin !";
        $reg_ok = 'no';
    }
}


//==================================


if (isset($_POST['titlesize'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['titlesize'])) || $_POST['titlesize'] < 1) {
        $_POST['titlesize'] = 16;
    }
    if (!saveSetting("titlesize", $_POST['titlesize'])) {
        $msg .= "Erreur lors de l'enregistrement de titlesize !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['cellpadding'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['cellpadding'])) || $_POST['cellpadding'] < 0) {
        $_POST['cellpadding'] = 5;
    }
    if (!saveSetting("cellpadding", $_POST['cellpadding'])) {
        $msg .= "Erreur lors de l'enregistrement de cellpadding !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['cellspacing'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['cellspacing'])) || $_POST['cellspacing'] < 0) {
        $_POST['cellspacing'] = 2;
    }
    if (!saveSetting("cellspacing", $_POST['cellspacing'])) {
        $msg .= "Erreur lors de l'enregistrement de cellspacing !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['largeurtableau'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['largeurtableau'])) || $_POST['largeurtableau'] < 1) {
        $_POST['largeurtableau'] = 1440;
    }
    if (!saveSetting("largeurtableau", $_POST['largeurtableau'])) {
        $msg .= "Erreur lors de l'enregistrement de largeurtableau !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['col_matiere_largeur'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['col_matiere_largeur'])) || $_POST['col_matiere_largeur'] < 1) {
        $_POST['col_matiere_largeur'] = 300;
    }
    if (!saveSetting("col_matiere_largeur", $_POST['col_matiere_largeur'])) {
        $msg .= "Erreur lors de l'enregistrement de col_matiere_largeur !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['col_note_largeur'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['col_note_largeur'])) || $_POST['col_note_largeur'] < 1) {
        $_POST['col_note_largeur'] = 50;
    }
    if (!saveSetting("col_note_largeur", $_POST['col_note_largeur'])) {
        $msg .= "Erreur lors de l'enregistrement de col_note_largeur !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['col_boite_largeur'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['col_boite_largeur'])) || $_POST['col_boite_largeur'] < 1) {
        $_POST['col_boite_largeur'] = 120;
    }
    if (!saveSetting("col_boite_largeur", $_POST['col_boite_largeur'])) {
        $msg .= "Erreur lors de l'enregistrement de col_boite_largeur !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['col_hauteur'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['col_hauteur'])) || $_POST['col_hauteur'] < 1) {
        $_POST['col_hauteur'] = 0;
    }
    if (!saveSetting("col_hauteur", $_POST['col_hauteur'])) {
        $msg .= "Erreur lors de l'enregistrement de col_hauteur !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['bull_ecart_entete'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['bull_ecart_entete']))) {
        $_POST['bull_ecart_entete'] = 0;
    }
    if (!saveSetting("bull_ecart_entete", $_POST['bull_ecart_entete'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_ecart_entete !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_espace_avis'])) {

    if ((!(ereg ("^[0-9]{1,}$", $_POST['bull_espace_avis']))) or ($_POST['bull_espace_avis'] <= 0)) {
        $_POST['bull_espace_avis'] = 1;
    }
    if (!saveSetting("bull_espace_avis", $_POST['bull_espace_avis'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_espace_avis !";
        $reg_ok = 'no';
    }
}


if (isset($_POST['addressblock_padding_right'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['addressblock_padding_right']))) {
        $_POST['addressblock_padding_right'] = 0;
    }
    if (!saveSetting("addressblock_padding_right", $_POST['addressblock_padding_right'])) {
        $msg .= "Erreur lors de l'enregistrement de addressblock_padding_right !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['addressblock_padding_top'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['addressblock_padding_top']))) {
        $_POST['addressblock_padding_top'] = 0;
    }
    if (!saveSetting("addressblock_padding_top", $_POST['addressblock_padding_top'])) {
        $msg .= "Erreur lors de l'enregistrement de addressblock_padding_top !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['addressblock_padding_text'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['addressblock_padding_text']))) {
        $_POST['addressblock_padding_text'] = 0;
    }
    if (!saveSetting("addressblock_padding_text", $_POST['addressblock_padding_text'])) {
        $msg .= "Erreur lors de l'enregistrement de addressblock_padding_text !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['addressblock_length'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['addressblock_length']))) {
        $_POST['addressblock_padding_text'] = 0;
    }
    if (!saveSetting("addressblock_length", $_POST['addressblock_length'])) {
        $msg .= "Erreur lors de l'enregistrement de addressblock_length !";
        $reg_ok = 'no';
    }
}


//==================================
// Ajout: boireaus
if (isset($_POST['addressblock_font_size'])) {
    if (!(ereg ("^[0-9]{1,}$", $_POST['addressblock_font_size']))) {
        $_POST['addressblock_font_size'] = 12;
    }
    if (!saveSetting("addressblock_font_size", $_POST['addressblock_font_size'])) {
        $msg .= "Erreur lors de l'enregistrement de addressblock_font_size !";
        $reg_ok = 'no';
    }
}

if(isset($_POST['addressblock_logo_etab_prop'])) {
	if (!(ereg ("^[0-9]{1,}$", $_POST['addressblock_logo_etab_prop']))) {
			$addressblock_logo_etab_prop=50;
	}
	else{
			$addressblock_logo_etab_prop=$_POST['addressblock_logo_etab_prop'];
	}
}
else{
	if(getSettingValue("addressblock_logo_etab_prop")){
		$addressblock_logo_etab_prop=getSettingValue("addressblock_logo_etab_prop");
	}
	else{
		$addressblock_logo_etab_prop=50;
	}
}

if (isset($_POST['addressblock_classe_annee'])) {
	if (!(ereg ("^[0-9]{1,}$", $_POST['addressblock_classe_annee']))) {
			$addressblock_classe_annee=35;
	}
	else{
			$addressblock_classe_annee=$_POST['addressblock_classe_annee'];
	}
}
else{
	if(getSettingValue("addressblock_classe_annee")){
		$addressblock_classe_annee=getSettingValue("addressblock_classe_annee");
	}
	else{
		$addressblock_classe_annee=30;
	}
}

if((isset($_POST['addressblock_classe_annee']))&&(isset($_POST['addressblock_logo_etab_prop']))){
	$valtest=$addressblock_logo_etab_prop+$addressblock_classe_annee;
	if($valtest>100){
		$msg.="Erreur! La somme addressblock_logo_etab_prop+addressblock_classe_annee d�passe 100% de la largeur de la page !";
		$reg_ok = 'no';
	}
	else{
		if (!saveSetting("addressblock_logo_etab_prop", $addressblock_logo_etab_prop)) {
			$msg .= "Erreur lors de l'enregistrement de addressblock_logo_etab_prop !";
			$reg_ok = 'no';
		}

		if (!saveSetting("addressblock_classe_annee", $addressblock_classe_annee)) {
			$msg .= "Erreur lors de l'enregistrement de addressblock_classe_annee !";
			$reg_ok = 'no';
		}
	}
}


if (isset($_POST['bull_ecart_bloc_nom'])) {
    if (!(ereg ("^[0-9]{1,}$", $_POST['bull_ecart_bloc_nom']))) {
        $_POST['bull_ecart_bloc_nom'] = 0;
    }
    if (!saveSetting("bull_ecart_bloc_nom", $_POST['bull_ecart_bloc_nom'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_ecart_bloc_nom !";
        $reg_ok = 'no';
    }
}


if (isset($_POST['addressblock_debug'])) {
    if (($_POST['addressblock_debug']!="y")&&($_POST['addressblock_debug']!="n")) {
        $_POST['addressblock_debug'] = "n";
    }
    if (!saveSetting("addressblock_debug", $_POST['addressblock_debug'])) {
        $msg .= "Erreur lors de l'enregistrement de addressblock_debug !";
        $reg_ok = 'no';
    }
}
//==================================


if (isset($_POST['page_garde_padding_top'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['page_garde_padding_top']))) {
        $_POST['page_garde_padding_top'] = 0;
    }
    if (!saveSetting("page_garde_padding_top", $_POST['page_garde_padding_top'])) {
        $msg .= "Erreur lors de l'enregistrement de page_garde_padding_top !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['page_garde_padding_left'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['page_garde_padding_left']))) {
        $_POST['page_garde_padding_left'] = 0;
    }
    if (!saveSetting("page_garde_padding_left", $_POST['page_garde_padding_left'])) {
        $msg .= "Erreur lors de l'enregistrement de page_garde_padding_left !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['page_garde_padding_text'])) {

    if (!(ereg ("^[0-9]{1,}$", $_POST['page_garde_padding_text']))) {
        $_POST['page_garde_padding_text'] = 0;
    }
    if (!saveSetting("page_garde_padding_text", $_POST['page_garde_padding_text'])) {
        $msg .= "Erreur lors de l'enregistrement de page_garde_padding_text !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['ok'])) {

    if (isset($_POST['page_garde_imprime'])) {
        $temp = 'yes';
    } else {
        $temp = 'no';
    }
    if (!saveSetting("page_garde_imprime", $temp)) {
        $msg .= "Erreur lors de l'enregistrement de page_garde_imprime !";
        $reg_ok = 'no';
    }
}

if (isset($NON_PROTECT['page_garde_texte'])) {
    $imp = traitement_magic_quotes($NON_PROTECT['page_garde_texte']);
    if (!saveSetting("page_garde_texte", $imp)) {
        $msg .= "Erreur lors de l'enregistrement de page_garde_texte !";
        $reg_ok = 'no';
    }
}

if (isset($NON_PROTECT['bull_formule_bas'])) {
    $imp = traitement_magic_quotes($NON_PROTECT['bull_formule_bas']);
    if (!saveSetting("bull_formule_bas", $imp)) {
        $msg .= "Erreur lors de l'enregistrement de bull_formule_bas !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_mention_nom_court'])) {

    if (!saveSetting("bull_mention_nom_court", $_POST['bull_mention_nom_court'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_mention_nom_court !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_mention_doublant'])) {

    if (!saveSetting("bull_mention_doublant", $_POST['bull_mention_doublant'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_mention_doublant !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_affiche_eleve_une_ligne'])) {

    if (!saveSetting("bull_affiche_eleve_une_ligne", $_POST['bull_affiche_eleve_une_ligne'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_mention_nom_court !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_affiche_graphiques'])) {

    if (!saveSetting("bull_affiche_graphiques", $_POST['bull_affiche_graphiques'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_graphiques !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_affiche_appreciations'])) {

    if (!saveSetting("bull_affiche_appreciations", $_POST['bull_affiche_appreciations'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_appreciations !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_affiche_absences'])) {

    if (!saveSetting("bull_affiche_absences", $_POST['bull_affiche_absences'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_absences !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['bull_affiche_avis'])) {

    if (!saveSetting("bull_affiche_avis", $_POST['bull_affiche_avis'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_avis !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['bull_affiche_aid'])) {

    if (!saveSetting("bull_affiche_aid", $_POST['bull_affiche_aid'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_aid !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['bull_affiche_formule'])) {

    if (!saveSetting("bull_affiche_formule", $_POST['bull_affiche_formule'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_formule !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['bull_affiche_signature'])) {

    if (!saveSetting("bull_affiche_signature", $_POST['bull_affiche_signature'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_signature !";
        $reg_ok = 'no';
    }
}
if (isset($_POST['bull_affiche_numero'])) {

    if (!saveSetting("bull_affiche_numero", $_POST['bull_affiche_numero'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_numero !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['bull_affiche_etab'])) {
    if (!saveSetting("bull_affiche_etab", $_POST['bull_affiche_etab'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_etab !";
        $reg_ok = 'no';
    }
}


if(isset($_POST['bull_bordure_classique'])) {
    if (!saveSetting("bull_bordure_classique", $_POST['bull_bordure_classique'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_bordure_classique !";
        $reg_ok = 'no';
    }
}

if (isset($_POST['choix_bulletin'])) {

    if (!saveSetting("choix_bulletin", $_POST['choix_bulletin'])) {
        $msg .= "Erreur lors de l'enregistrement de choix_bulletin";
        $reg_ok = 'no';
    }
}

if (isset($_POST['min_max_moyclas'])) {

    if (!saveSetting("min_max_moyclas", $_POST['min_max_moyclas'])) {
        $msg .= "Erreur lors de l'enregistrement de min_max_moyclas !";
        $reg_ok = 'no';
    }
}

if(isset($_POST['activer_photo_bulletin'])) {
    if (!saveSetting("activer_photo_bulletin", $_POST['activer_photo_bulletin'])) {
        $msg .= "Erreur lors de l'enregistrement de activer_photo_bulletin !";
        $reg_ok = 'no';
    }
}

if(isset($_POST['bull_photo_hauteur_max'])) {
    if (!saveSetting("bull_photo_hauteur_max", $_POST['bull_photo_hauteur_max'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_photo_hauteur_max !";
        $reg_ok = 'no';
    }
}

if(isset($_POST['bull_photo_largeur_max'])) {
    if (!saveSetting("bull_photo_largeur_max", $_POST['bull_photo_largeur_max'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_photo_largeur_max !";
        $reg_ok = 'no';
    }
}

if(isset($_POST['bull_categ_font_size'])) {
	if (!(ereg ("^[0-9]{1,}$", $_POST['bull_categ_font_size']))) {
		$_POST['bull_categ_font_size'] = 10;
	}
	if (!saveSetting("bull_categ_font_size", $_POST['bull_categ_font_size'])) {
		$msg .= "Erreur lors de l'enregistrement de bull_categ_font_size !";
		$reg_ok = 'no';
	}
}


if(isset($_POST['bull_intitule_app'])) {
    if (!saveSetting("bull_intitule_app", $_POST['bull_intitule_app'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_intitule_app !";
        $reg_ok = 'no';
    }
}

if(isset($_POST['bull_affiche_tel'])) {
    if (!saveSetting("bull_affiche_tel", $_POST['bull_affiche_tel'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_tel !";
        $reg_ok = 'no';
    }
}

if(isset($_POST['bull_affiche_fax'])) {
    if (!saveSetting("bull_affiche_fax", $_POST['bull_affiche_fax'])) {
        $msg .= "Erreur lors de l'enregistrement de bull_affiche_fax !";
        $reg_ok = 'no';
    }
}


// Tableau des couleurs HTML:
$tabcouleur=Array("aliceblue","antiquewhite","aqua","aquamarine","azure","beige","bisque","black","blanchedalmond","blue","blueviolet","brown","burlywood","cadetblue","chartreuse","chocolate","coral","cornflowerblue","cornsilk","crimson","cyan","darkblue","darkcyan","darkgoldenrod","darkgray","darkgreen","darkkhaki","darkmagenta","darkolivegreen","darkorange","darkorchid","darkred","darksalmon","darkseagreen","darkslateblue","darkslategray","darkturquoise","darkviolet","deeppink","deepskyblue","dimgray","dodgerblue","firebrick","floralwhite","forestgreen","fuchsia","gainsboro","ghostwhite","gold","goldenrod","gray","green","greenyellow","honeydew","hotpink","indianred","indigo","ivory","khaki","lavender","lavenderblush","lawngreen","lemonchiffon","lightblue","lightcoral","lightcyan","lightgoldenrodyellow","lightgreen","lightgrey","lightpink","lightsalmon","lightseagreen","lightskyblue","lightslategray","lightsteelblue","lightyellow","lime","limegreen","linen","magenta","maroon","mediumaquamarine","mediumblue","mediumorchid","mediumpurple","mediumseagreen","mediumslateblue","mediumspringgreen","mediumturquoise","mediumvioletred","midnightblue","mintcream","mistyrose","moccasin","navajowhite","navy","oldlace","olive","olivedrab","orange","orangered","orchid","palegoldenrod","palegreen","paleturquoise","palevioletred","papayawhip","peachpuff","peru","pink","plum","powderblue","purple","red","rosybrown","royalblue","saddlebrown","salmon","sandybrown","seagreen","seashell","sienna","silver","skyblue","slateblue","slategray","snow","springgreen","steelblue","tan","teal","thistle","tomato","turquoise","violet","wheat","white","whitesmoke","yellow","yellowgreen");

if (isset($_POST['bull_categ_bgcolor'])) {
	if((!in_array($_POST['bull_categ_bgcolor'],$tabcouleur))&&($_POST['bull_categ_bgcolor']!='')){
		$msg .= "Erreur lors de l'enregistrement de bull_categ_bgcolor ! (couleur invalide)";
		$reg_ok = 'no';
	}
	else{
		if (!saveSetting("bull_categ_bgcolor", $_POST['bull_categ_bgcolor'])) {
			$msg .= "Erreur lors de l'enregistrement de bull_categ_bgcolor !";
			$reg_ok = 'no';
		}
	}
}

// tableau des polices pour avis du CC de classe
$tab_polices_avis=Array("Arial","Helvetica","Serif","Times","Times New Roman","Verdana",);
if (isset($_POST['bull_police_avis'])) {
	if((!in_array($_POST['bull_police_avis'],$tab_polices_avis))&&($_POST['bull_police_avis']!='')){
		$msg .= "Erreur lors de l'enregistrement de bull_police_avis ! (police invalide)";
		$reg_ok = 'no';
	}
	else{
		if (!saveSetting("bull_police_avis", $_POST['bull_police_avis'])) {
			$msg .= "Erreur lors de l'enregistrement de bull_police_avis !";
			$reg_ok = 'no';
		}
	}
}

//Style des caract�res avis
// tableau des styles de polices pour avis du CC de classe
$tab_styles_avis=Array("Normal","Gras","Italique","Gras et Italique");
if (isset($_POST['bull_font_style_avis'])) {
	if((!in_array($_POST['bull_font_style_avis'],$tab_styles_avis))&&($_POST['bull_font_style_avis']!='')){
		$msg .= "Erreur lors de l'enregistrement de bull_police_avis ! (police invalide)";
		$reg_ok = 'no';
	}
	else{
		if (!saveSetting("bull_font_style_avis", $_POST['bull_font_style_avis'])) {
			$msg .= "Erreur lors de l'enregistrement de bull_police_avis !";
			$reg_ok = 'no';
		}
	}
}

//taille de la police avis
if(isset($_POST['bull_categ_font_size_avis'])) {
	if (!(ereg ("^[0-9]{1,}$", $_POST['bull_categ_font_size_avis']))) {
		$_POST['bull_categ_font_size_avis'] = 10;
	}
	if (!saveSetting("bull_categ_font_size_avis", $_POST['bull_categ_font_size_avis'])) {
		$msg .= "Erreur lors de l'enregistrement de bull_categ_font_size_avis !";
		$reg_ok = 'no';
	}
}


if (($reg_ok == 'yes') and (isset($_POST['ok']))) {
   $msg = "Enregistrement r�ussi !";
}


// End standart header
require_once("../lib/header.inc");
if (!loadSettings()) {
    die("Erreur chargement settings");
}
?>

<script type="text/javascript">
<!-- Debut
var nb='';
function SetDefaultValues(nb){
	if (nb=='A4V') {
		window.document.formulaire.titlesize.value = '14';
		window.document.formulaire.textsize.value = '8';
		window.document.formulaire.largeurtableau.value = '800';
		window.document.formulaire.col_matiere_largeur.value = '150';
		window.document.formulaire.col_note_largeur.value = '30';
		window.document.formulaire.col_boite_largeur.value = '120';
		window.document.formulaire.cellpadding.value = '3';
		window.document.formulaire.cellspacing.value = '1';
	}
	if(nb=='A3H'){
		window.document.formulaire.titlesize.value = '16';
		window.document.formulaire.textsize.value = '10';
		window.document.formulaire.largeurtableau.value = '1440';
		window.document.formulaire.col_matiere_largeur.value = '300';
		window.document.formulaire.col_note_largeur.value = '50';
		window.document.formulaire.col_boite_largeur.value = '150';
		window.document.formulaire.cellpadding.value = '5';
		window.document.formulaire.cellspacing.value = '2';
	}
	if(nb=='Adresse'){
		window.document.formulaire.addressblock_padding_right.value = '20';
		window.document.formulaire.addressblock_padding_top.value = '40';
		window.document.formulaire.addressblock_padding_text.value = '20';
		window.document.formulaire.addressblock_length.value = '60';
		window.document.formulaire.addressblock_font_size.value = '12';
		window.document.formulaire.addressblock_logo_etab_prop.value = '50';
		window.document.formulaire.addressblock_classe_annee.value = '35';
		window.document.formulaire.bull_ecart_bloc_nom.value = '1';

		//window.document.formulaire.addressblock_debug.value = 'n';
		window.document.getElementById('addressblock_debugn').checked='true';
	}
}
// fin du script -->
</script>

<p class=bold><a href="../accueil.php"><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour </a>
| <a href="./index.php"> Imprimer les bulletins au format HTML</a>
| <a href="./param_bull_pdf.php"> Param�tres d'impression des bulletins PDF</a>
</p>

<?php
if ((($_SESSION['statut']=='professeur') AND ((getSettingValue("GepiProfImprBul")!='yes') OR ((getSettingValue("GepiProfImprBul")=='yes') AND (getSettingValue("GepiProfImprBulSettings")!='yes')))) OR (($_SESSION['statut']=='scolarite') AND (getSettingValue("GepiScolImprBulSettings")!='yes')) OR (($_SESSION['statut']=='administrateur') AND (getSettingValue("GepiAdminImprBulSettings")!='yes')))
{
    die("Droits insuffisants pour effectuer cette op�ration");
}
?>


<form name="formulaire" action="param_bull.php" method="post" style="width: 100%;">
<H3>Mise en page du bulletin scolaire</H3>
<table cellpadding="8" cellspacing="0" width="100%" border="0">

    <tr <?php $nb_ligne = 1; if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        R�tablir les param�tres par d�faut :<br />
        &nbsp;&nbsp;&nbsp;<A HREF="javascript:SetDefaultValues('A4V')">Impression sur A4 "portrait"</A><br />
        &nbsp;&nbsp;&nbsp;<A HREF="javascript:SetDefaultValues('A3H')">Impression sur A3 "paysage"</A>

        </td>
        <td>
        &nbsp;
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Taille en points des gros titres :
        </td>
        <td><input type="text" name="titlesize" size="20" value="<?php echo(getSettingValue("titlesize")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Taille en points du texte (hormis les titres) :
        </td>
        <td><input type="text" name="textsize" size="20" value="<?php echo(getSettingValue("textsize")); ?>" />
        </td>
    </tr>
    <!-- D�but AJOUT: boireaus -->
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Marges hautes et basses des paragraphes en points du texte (hormis les titres) :
        </td>
        <td><input type="text" name="p_bulletin_margin" size="20" value="<?php
		if(getSettingValue("p_bulletin_margin")!=""){
			echo(getSettingValue("p_bulletin_margin"));
		}
		else{
			echo "5";
		}?>" />
        </td>
    </tr>
    <!-- Fin AJOUT: boireaus -->
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Largeur du tableau en pixels :
        </td>
        <td><input type="text" name="largeurtableau" size="20" value="<?php echo(getSettingValue("largeurtableau")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Largeur de la premi�re colonne (mati�res) en pixels :<br />
        <span class="small">(Si le contenu d'une cellule de la colonne est plus grand que la taille pr�vue, la mention ci-dessus devient caduque. La colonne sera dans ce cas dimensionn�e par le navigateur lui-m�me.)</span>
        </td>
        <td><input type="text" name="col_matiere_largeur" size="20" value="<?php echo(getSettingValue("col_matiere_largeur")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Largeur des colonnes min, max, classe et �l�ve en pixels :<br />
        <span class="small">(M�me remarque que ci-dessus)</span>
        </td>
        <td><input type="text" name="col_note_largeur" size="20" value="<?php echo(getSettingValue("col_note_largeur")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Largeur des cellules contenant les notes des carnets de notes � afficher sur les bulletins :<br />
        <span class="small">(M�me remarque que ci-dessus)</span>
        </td>
        <td><input type="text" name="col_boite_largeur" size="20" value="<?php echo(getSettingValue("col_boite_largeur")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Hauteur minimale des lignes en pixels ("0" si automatique) :<br />
        <span class="small">(Si le contenu d'une cellule est telle que la hauteur fix�e ci-dessus est insuffisante, la hauteur de la ligne sera dimensionn�e par le navigateur lui-m�me.)</span>
        </td>
        <td><input type="text" name="col_hauteur" size="20" value="<?php echo(getSettingValue("col_hauteur")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Espace en pixels entre le bord d'une cellule du tableau et le contenu de la cellule :
        </td>
        <td><input type="text" name="cellpadding" size="20" value="<?php echo(getSettingValue("cellpadding")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Espace en pixels entre les cellules du tableau :
        </td>
        <td><input type="text" name="cellspacing" size="20" value="<?php echo(getSettingValue("cellspacing")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Espace (nombre de lignes vides) entre l'en-t�te du bulletin et le tableau des notes et appr�ciations :
        </td>
        <td><input type="text" name="bull_ecart_entete" size="20" value="<?php echo(getSettingValue("bull_ecart_entete")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Espace (nombre de lignes vides) pour une saisie � la main de l'avis du Conseil de classe, si celui-ci n'a pas �t� saisi dans GEPI :
        </td>
        <td><input type="text" name="bull_espace_avis" size="20" value="<?php echo(getSettingValue("bull_espace_avis")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Bordures des cellules du tableau des moyennes et appr�ciations :
        </td>
        <td>
		<?php
			if(getSettingValue("bull_bordure_classique")=='n'){
				$bull_bordure_classique="n";
			}
			else{
				$bull_bordure_classique="y";
			}

			echo "<input type=\"radio\" name=\"bull_bordure_classique\" value=\"y\" ";
			if ($bull_bordure_classique=='y') echo " checked";
			echo " />&nbsp;classique&nbsp;HTML<br />\n";
			echo "<input type=\"radio\" name=\"bull_bordure_classique\" value=\"n\" ";
			if ($bull_bordure_classique=='n') echo " checked";
			echo " />&nbsp;trait&nbsp;noir\n";
		?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Taille en points du texte des cat�gories de mati�res (<i>lorsqu'elles sont affich�es</i>) :
        </td>
	<?php
		if(getSettingValue("bull_categ_font_size")){
			$bull_categ_font_size=getSettingValue("bull_categ_font_size");
		}
		else{
			$bull_categ_font_size=10;
		}
	?>
        <td><input type="text" name="bull_categ_font_size" size="20" value="<?php echo $bull_categ_font_size; ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Couleur de fond des lignes de cat�gories de mati�res (<i>lorsqu'elles sont affich�es</i>) :
        </td>
	<?php
		if(getSettingValue("bull_categ_bgcolor")){
			$bull_categ_bgcolor=getSettingValue("bull_categ_bgcolor");
		}
		else{
			$bull_categ_bgcolor="";
		}
	?>
        <td>
	<?php
		//<input type="text" name="bull_categ_bgcolor" size="20" value="echo $bull_categ_bgcolor;" />
		echo "<select name='bull_categ_bgcolor'>\n";
		echo "<option value=''>Aucune</option>\n";
		for($i=0;$i<count($tabcouleur);$i++){
			if($tabcouleur[$i]=="$bull_categ_bgcolor"){
				$selected=" selected='true'";
			}
			else{
				$selected="";
			}
			echo "<option value='$tabcouleur[$i]'$selected>$tabcouleur[$i]</option>\n";
		}
		echo "</select>\n";
        ?>
	</td>
    </tr>

<tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Taille en points du texte de l'avis du conseil de classe :
        </td>
	<?php
		if(getSettingValue("bull_categ_font_size_avis")){
			$bull_categ_font_size_avis=getSettingValue("bull_categ_font_size_avis");
		}
		else{
			$bull_categ_font_size_avis=10;
		}
	?>
        <td><input type="text" name="bull_categ_font_size_avis" size="20" value="<?php echo $bull_categ_font_size_avis; ?>" />
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Police de caract�res pour l'avis du conseil de classe :
        </td>
	<?php
		if(getSettingValue("bull_police_avis")){
			$bull_police_avis=getSettingValue("bull_police_avis");
		}
		else{
			$bull_police_avis="";
		}
	?>
        <td>
	<?php
		echo "<select name='bull_police_avis'>\n";
		echo "<option value=''>Aucune</option>\n";
		for($i=0;$i<count($tab_polices_avis);$i++){
			if($tab_polices_avis[$i]=="$bull_police_avis"){
				$selected=" selected='true'";
			}
			else{
				$selected="";
			}
			echo "<option value=\"$tab_polices_avis[$i]\" $selected>$tab_polices_avis[$i]</option>\n";
		}
		echo "</select>\n";
        ?>
	</td>
    </tr>

	<tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Style de caract�res pour l'avis du conseil de classe :
        </td>
	<?php
		if(getSettingValue("bull_font_style_avis")){
			$bull_font_style_avis=getSettingValue("bull_font_style_avis");
		}
		else{
			$bull_font_style_avis="normal";
		}
	?>
        <td>
	<?php
		echo "<select name='bull_font_style_avis'>\n";
		for($i=0;$i<count($tab_styles_avis);$i++){
			if($tab_styles_avis[$i]=="$bull_font_style_avis"){
				$selected=" selected='true'";
			}
			else{
				$selected="";
			}
			echo "<option value=\"$tab_styles_avis[$i]\" $selected>$tab_styles_avis[$i]</option>\n";
		}
		echo "</select>\n";
        ?>
	</td>
    </tr>

</table>
<hr />


<center><input type="submit" name="ok" value="Enregistrer" style="font-variant: small-caps;"/></center>


<hr />
<?php
//Informations devant figurer sur le bulletin scolaire</H3>
?>
<H3>Informations devant figurer sur le bulletin scolaire</H3>
<table cellpadding="8" cellspacing="0" width="100%" border="0">
<tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher le nom court de la classe :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_mention_nom_court\" value=\"yes\" ";
        if (getSettingValue("bull_mention_nom_court") == 'yes') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_mention_nom_court\" value=\"no\" ";
        if (getSettingValue("bull_mention_nom_court") == 'no') echo " checked";
        echo " />&nbsp;Non";
        ?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher la mention "doublant" ou "doublante", le cas �ch�ant :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_mention_doublant\" value=\"yes\" ";
        if (getSettingValue("bull_mention_doublant") == 'yes') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_mention_doublant\" value=\"no\" ";
        if (getSettingValue("bull_mention_doublant") == 'no') echo " checked";
        echo " />&nbsp;Non";
        ?>
        </td>
    </tr>
	<tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher les informations sur l'�l�ve sur une seule ligne <i>(si non une information par ligne)</i> :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_eleve_une_ligne\" value=\"yes\" ";
        if (getSettingValue("bull_affiche_eleve_une_ligne") == 'yes') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_eleve_une_ligne\" value=\"no\" ";
        if (getSettingValue("bull_affiche_eleve_une_ligne") == 'no') echo " checked";
        echo " />&nbsp;Non";
        ?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher les appr�ciations des mati�res :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_appreciations\" value=\"y\" ";
        if (getSettingValue("bull_affiche_appreciations") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_appreciations\" value=\"n\" ";
        if (getSettingValue("bull_affiche_appreciations") != 'y') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher les donn�es sur les absences :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_absences\" value=\"y\" ";
        if (getSettingValue("bull_affiche_absences") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_absences\" value=\"n\" ";
        if (getSettingValue("bull_affiche_absences") != 'y') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher les avis du conseil de classe :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_avis\" value=\"y\" ";
        if (getSettingValue("bull_affiche_avis") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_avis\" value=\"n\" ";
        if (getSettingValue("bull_affiche_avis") != 'y') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher les donn�es sur les AID :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_aid\" value=\"y\" ";
        if (getSettingValue("bull_affiche_aid") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_aid\" value=\"n\" ";
        if (getSettingValue("bull_affiche_aid") != 'y') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher le num�ro du bulletin :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_numero\" value=\"yes\" ";
        if (getSettingValue("bull_affiche_numero") == 'yes') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_numero\" value=\"no\" ";
        if (getSettingValue("bull_affiche_numero") == 'no') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher les graphiques indiquant les niveaux (A, B, C+, C-, D ou E) :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_graphiques\" value=\"yes\" ";
        if (getSettingValue("bull_affiche_graphiques") == 'yes') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_graphiques\" value=\"no\" ";
        if (getSettingValue("bull_affiche_graphiques") != 'yes') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher le nom du professeur principal et du chef d'�tablissement :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_signature\" value=\"y\" ";
        if (getSettingValue("bull_affiche_signature") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_signature\" value=\"n\" ";
        if (getSettingValue("bull_affiche_signature") != 'y') echo " checked";
        echo " />&nbsp;Non";
        ?>
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher l'�tablissement d'origine sur le bulletin :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_etab\" value=\"y\" ";
        if (getSettingValue("bull_affiche_etab") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_etab\" value=\"n\" ";
        if (getSettingValue("bull_affiche_etab") != 'y') echo " checked";
        echo " />&nbsp;Non";
        ?>
        </td>
    </tr>


<?php
if (getSettingValue("active_module_trombinoscopes")=='y') {
	echo "<tr ";
	if($nb_ligne % 2){echo "bgcolor=".$bgcolor;}
	$nb_ligne++;
	echo ">\n";
?>
        <td style="font-variant: small-caps;">
        Afficher la photo de l'�l�ve sur le bulletin :
        </td>
        <td>
<?php
	echo "<input type='radio' name='activer_photo_bulletin' id='activer_photo_bulletiny' value='y'";
	if (getSettingValue("activer_photo_bulletin")=='y'){echo "checked";}
	echo " onchange=\"aff_lig_photo('afficher')\" />&nbsp;Oui\n";
	echo "<input type='radio' name='activer_photo_bulletin' value='n'";
	if (getSettingValue("activer_photo_bulletin")!='y'){echo "checked";}
	echo " onchange=\"aff_lig_photo('cacher')\" />&nbsp;Non\n";
?>
        </td>
    </tr>
<?php
	if(getSettingValue("bull_photo_hauteur_max")){
		$bull_photo_hauteur_max=getSettingValue("bull_photo_hauteur_max");
	}
	else{
		$bull_photo_hauteur_max=80;
	}

	if(getSettingValue("bull_photo_largeur_max")){
		$bull_photo_largeur_max=getSettingValue("bull_photo_largeur_max");
	}
	else{
		$bull_photo_largeur_max=80;
	}
?>
    <tr id='ligne_bull_photo_hauteur_max'>
	<td style="font-variant: small-caps;">Hauteur maximale de la photo en pixels :</td>
	<td><input type="text" name="bull_photo_hauteur_max" size='4' value="<?php echo $bull_photo_hauteur_max;?>" /></td>
    </tr>
    <tr id='ligne_bull_photo_largeur_max'>
	<td style="font-variant: small-caps;">Largeur maximale de la photo en pixels :</td>
	<td><input type="text" name="bull_photo_largeur_max" size='4' value="<?php echo $bull_photo_largeur_max;?>" />

	<script type='text/javascript'>
		function aff_lig_photo(mode){
			if(mode=='afficher'){
				document.getElementById('ligne_bull_photo_hauteur_max').style.display='';
				document.getElementById('ligne_bull_photo_largeur_max').style.display='';
			}
			else{
				document.getElementById('ligne_bull_photo_hauteur_max').style.display='none';
				document.getElementById('ligne_bull_photo_largeur_max').style.display='none';
			}
		}

		if(document.getElementById('activer_photo_bulletiny').checked==false){
			aff_lig_photo('cacher');
		}
	</script>
	</td>
    </tr>
<?php
}
?>




    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher le num�ro de t�l�phone de l'�tablissement :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_tel\" value=\"y\" ";
        if (getSettingValue("bull_affiche_tel") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_tel\" value=\"n\" ";
        if (getSettingValue("bull_affiche_tel") != 'y') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher le num�ro de fax de l'�tablissement :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_fax\" value=\"y\" ";
        if (getSettingValue("bull_affiche_fax") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_fax\" value=\"n\" ";
        if (getSettingValue("bull_affiche_fax") != 'y') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;" colspan='2'>
        Intitul� de la colonne Appr�ciations :
        <?php
		echo "<input type=\"text\" name=\"bull_intitule_app\" value=\"".getSettingValue('bull_intitule_app')."\" size='100' />";
        ?>
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Afficher la formule figurant en bas de chaque bulletin :
        </td>
        <td>
        <?php
        echo "<input type=\"radio\" name=\"bull_affiche_formule\" value=\"y\" ";
        if (getSettingValue("bull_affiche_formule") == 'y') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"bull_affiche_formule\" value=\"n\" ";
        if (getSettingValue("bull_affiche_formule") != 'y') echo " checked";
        echo " />&nbsp;Non";

        ?>
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;" colspan="2">
        Formule figurant en bas de chaque bulletin :
        <input type="text" name="no_anti_inject_bull_formule_bas" size="100" value="<?php echo(getSettingValue("bull_formule_bas")); ?>" />
        </td>
    </tr>

	<tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Choix de l'apparence du bulletin (emplacement et regroupement des moyennes de la classe)
		<ul>
		<li><i>Toutes les informations chiffr�es sur la classe et l'�l�ve sont avant la colonne <?php echo getSettingValue('bull_intitule_app')?>.</i><br />
		<li><i>Idem choix 1. Les informations sur la classe sont regroup�es en une cat�gorie "Pour la classe".</i><br />
		<li><i>Idem choix 2. Les informations pour la classe sont situ�es apr�s la colonne <?php echo getSettingValue('bull_intitule_app')?>.</i><br />
        </ul>
		</td>
        <td> <br />
        <?php
		echo "<input type='radio' name='choix_bulletin' value='1'";
		if (getSettingValue("choix_bulletin") == '1') echo " checked";
		echo " /> Choix 1<br />";
		echo "<input type='radio' name='choix_bulletin' value='2'";
		if (getSettingValue("choix_bulletin") == '2') echo " checked";
		echo " /> Choix 2<br />";
		echo "<input type='radio' name='choix_bulletin' value='3'";
		//echo "toto".getSettingValue("choix_bulletin");
		if (getSettingValue("choix_bulletin") == '3') echo " checked";
		echo " /> Choix 3<br />";
        ?>
        </td>
    </tr>

	<tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">Afficher les moyennes minimale, classe et maximale dans une seule colonne pour gagner de la place pour l'appr�ciation : </td>
        <td>
	    <?php
        echo "<input type=\"radio\" name=\"min_max_moyclas\" value='1' ";
        if (getSettingValue("min_max_moyclas") == '1') echo " checked";
        echo " />&nbsp;Oui";
        echo "<input type=\"radio\" name=\"min_max_moyclas\" value='0' ";
        if (getSettingValue("min_max_moyclas") != '1') echo " checked";
        echo " />&nbsp;Non";
        ?>
        </td>
    </tr>

</table>
<hr />




<center><input type="submit" name="ok" value="Enregistrer" style="font-variant: small-caps;"/></center>



<hr />
<H3>Bloc adresse</H3>
<center><table border="1" cellpadding="10" width="90%"><tr><td>
Ces options contr�lent le positionnement du bloc adresse du responsable de l'�l�ve directement sur le bulletin (et non sur la page de garde - voir ci-dessous). L'affichage de ce bloc est contr�l� classe par classe, au niveau du param�trage de la classe.
</td></tr></table></center>

<table cellpadding="8" cellspacing="0" width="100%" border="0">

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;;$nb_ligne++;?>>
        <td colspan='2' style="font-variant: small-caps;">
	<a href="javascript:SetDefaultValues('Adresse')">R�tablir les param�tres par d�faut</a>
        </td>
     </tr>


    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor; ?>>
        <td style="font-variant: small-caps;">
        Espace en mm entre la marge droite de la feuille et le bloc "adresse" :
        </td>
        <td><input type="text" name="addressblock_padding_right" size="20" value="<?php echo(getSettingValue("addressblock_padding_right")); ?>" />
        </td>
     </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td colspan="2"><i>Tenez compte de la marge droite d'impression pour calculer l'espace entre le bord droit de la feuille et le bloc adresse</i></td>
     </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;?>>
        <td style="font-variant: small-caps;">
        Espace en mm entre la marge haute de la feuille et le bloc "adresse" :
        </td>
        <td><input type="text" name="addressblock_padding_top" size="20" value="<?php echo(getSettingValue("addressblock_padding_top")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td colspan="2"><i>Tenez compte de la marge haute d'impression pour calculer l'espace entre le bord haut de la feuille et le bloc adresse</i></td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Espace en mm entre le bloc "adresse" et le bloc des r�sultats :
        </td>
        <td><input type="text" name="addressblock_padding_text" size="20" value="<?php echo(getSettingValue("addressblock_padding_text")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Longueur en mm du bloc "adresse" :
        </td>
        <td><input type="text" name="addressblock_length" size="20" value="<?php echo(getSettingValue("addressblock_length")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Taille en points des textes du bloc "adresse" :
        </td>
	<?php
		if(!getSettingValue("addressblock_font_size")){
			$addressblock_font_size=12;
		}
		else{
			$addressblock_font_size=getSettingValue("addressblock_font_size");
		}
	?>
        <td><input type="text" name="addressblock_font_size" size="20" value="<?php echo $addressblock_font_size; ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Proportion (en % de la largeur de page) allou�e au logo et � l'adresse de l'�tablissement :
        </td>
	<?php
		if(!getSettingValue("addressblock_logo_etab_prop")){
			$addressblock_logo_etab_prop=50;
		}
		else{
			$addressblock_logo_etab_prop=getSettingValue("addressblock_logo_etab_prop");
		}
	?>
        <td><input type="text" name="addressblock_logo_etab_prop" size="20" value="<?php echo $addressblock_logo_etab_prop; ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Proportion (en % de la largeur de page) allou�e au bloc "Classe, ann�e, p�riode" :
        </td>
	<?php
		if(!getSettingValue("addressblock_classe_annee")){
			$addressblock_classe_annee=35;
		}
		else{
			$addressblock_classe_annee=getSettingValue("addressblock_classe_annee");
		}
	?>
        <td><input type="text" name="addressblock_classe_annee" size="20" value="<?php echo $addressblock_classe_annee; ?>" />
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Nombre de sauts de ligne entre le bloc Logo+Etablissement et le bloc Nom, pr�nom,... de l'�l�ve :
        </td>
	<?php
		if(!getSettingValue("bull_ecart_bloc_nom")){
			$bull_ecart_bloc_nom=0;
		}
		else{
			$bull_ecart_bloc_nom=getSettingValue("bull_ecart_bloc_nom");
		}
	?>
        <td><input type="text" name="bull_ecart_bloc_nom" size="20" value="<?php echo $bull_ecart_bloc_nom; ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        <font color='red'>Activer l'affichage des bordures pour comprendre la pr�sentation avec bloc "adresse"</font> :
        </td>
	<?php
		if(!getSettingValue("addressblock_debug")){
			$addressblock_debug="n";
		}
		else{
			$addressblock_debug=getSettingValue("addressblock_debug");
		}
	?>
        <td><input type="radio" id="addressblock_debugy" name="addressblock_debug" value="y" <?php if($addressblock_debug=="y"){echo "checked";}?> /> Oui <input type="radio" id="addressblock_debugn" name="addressblock_debug" value="n" <?php if($addressblock_debug=="n"){echo "checked";}?> /> Non
        </td>
    </tr>
</table>
<hr />



<center><input type="submit" name="ok" value="Enregistrer" style="font-variant: small-caps;"/></center>



<hr />
<H3>Page de garde</H3>
<center><table border="1" cellpadding="10" width="90%"><tr><td>
La page de garde contient les informations suivantes :
<ul>
<li>l'adresse o� envoyer le bulletin. Si vous utilisez des enveloppes � fen�tre, vous pouvez r�gler les param�tres ci-dessous pour qu'elle apparaisse dans le cadre pr�vu � cet effet,</li>
<li>un texte que vous pouvez personnaliser (voir plus bas).</li>
</ul>
<b><a href='javascript:centrerpopup("./modele_page_garde.php",600,600,"scrollbars=yes,statusbar=yes,menubar=yes,resizable=yes")'>Aper�u de la page de garde</a></b>
(Attention : la mise en page des bulletins est tr�s diff�rente � l'�cran et � l'impression.
Veillez � utiliser la fonction "aper�u avant impression" afin de vous rendre compte du r�sultat.
</td></tr></table></center>
<table cellpadding="8" cellspacing="0" width="100%" border="0">
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">Imprimer les pages de garde : </td>
        <td><input type="checkbox" name="page_garde_imprime" value="yes" <?php if (getSettingValue("page_garde_imprime")=='yes') echo "checked"; ?>/>
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;?>>
        <td style="font-variant: small-caps;">
        Espace en cm entre la marge gauche de la feuille et le bloc "adresse" :
        </td>
        <td><input type="text" name="page_garde_padding_left" size="20" value="<?php echo(getSettingValue("page_garde_padding_left")); ?>" />
        </td>
     </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td colspan="2"><i>Tenez compte de la marge gauche d'impression pour calculer l'espace entre le bord droit de la feuille et le bloc adresse</i></td>
     </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;?>>
        <td style="font-variant: small-caps;">
        Espace en cm entre la marge haute de la feuille et le bloc "adresse" :
        </td>
        <td><input type="text" name="page_garde_padding_top" size="20" value="<?php echo(getSettingValue("page_garde_padding_top")); ?>" />
        </td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td colspan="2"><i>Tenez compte de la marge haute d'impression pour calculer l'espace entre le bord haut de la feuille et le bloc adresse</i></td>
    </tr>
    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
        <td style="font-variant: small-caps;">
        Espace en cm entre le bloc "adresse" et le bloc "texte" :
        </td>
        <td><input type="text" name="page_garde_padding_text" size="20" value="<?php echo(getSettingValue("page_garde_padding_text")); ?>" />
        </td>
    </tr>

    <tr <?php if ($nb_ligne % 2) echo "bgcolor=".$bgcolor;$nb_ligne++; ?>>
    <?php
    $impression = getSettingValue("page_garde_texte");
    echo "<td valign=\"top\"  style=\"font-variant: small-caps;\">Texte de la page de garde apparaissant � la suite de l'adresse : </td>";
    echo "<td><div class='small'>";
    echo "<i>Mise en forme du message :</i>";

    $oFCKeditor = new FCKeditor('no_anti_inject_page_garde_texte') ;
    $oFCKeditor->BasePath = '../fckeditor/' ;
    $oFCKeditor->Config['DefaultLanguage']  = 'fr' ;
    $oFCKeditor->ToolbarSet = 'Basic' ;
    $oFCKeditor->Value      = $impression ;
    $oFCKeditor->Create() ;

    echo "</div></td></tr>";
?>

</table>


<hr /><center><input type="submit" name="ok" value="Enregistrer" style="font-variant: small-caps;"/></center>
</form>
<?php require("../lib/footer.inc.php");