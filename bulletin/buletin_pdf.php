<?php
/*
 *
 * $Id$
 * 
 * Last modification  : 17/03/2007
 *
 * Copyright 2001, 2007 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, St�phane Boireau, Christian Chapel
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

// Global configuration file
// Quand on est en SSL, IE n'arrive pas � ouvrir le PDF.
//Le probl�me peut �tre r�solu en ajoutant la ligne suivante :
Header('Pragma: public');

require('../fpdf/fpdf.php');
require('../fpdf/ex_fpdf.php');
require_once("../fpdf/class.multicelltag.php");

define('FPDF_FONTPATH','../fpdf/font/');
define('TopMargin','5');
define('RightMargin','2');
define('LeftMargin','2');
define('BottomMargin','5');
define('LargeurPage','210');
define('HauteurPage','297');
session_cache_limiter('private');

$X1 = 0; $Y1 = 0; $X2 = 0; $Y2 = 0;
$X3 = 0; $Y3 = 0; $X4 = 0; $Y4 = 0;
$X5 = 0; $Y5 = 0; $X6 = 0; $Y6 = 0;

// Initialisations files
require_once("../lib/initialisations.inc.php");

// Lorsque qu'on utilise une session PHP, parfois, IE n'affiche pas le PDF
// C'est un probl�me qui affecte certaines versions d'IE.
// Pour le contourner, on ajoutez la ligne suivante avant session_start() :
session_cache_limiter('private');

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
    header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
};


if (!checkAccess()) {
    header("Location: ../logout.php?auto=1");
    die();
}
Header('Pragma: public');


//variable de session
  if(!empty($_SESSION['eleve'][0]) and $_SESSION['eleve'] != '')
   { $id_eleve = $_SESSION['eleve']; unset($_SESSION['classe']); } else { unset($_SESSION["eleve"]); }
  if(!empty($_SESSION['classe'][0]) and $_SESSION['classe'][0] != '')
   { $id_classe = $_SESSION['classe']; } else { unset($_SESSION['classe']); }
   $periode = $_SESSION['periode'];
   $periode_ferme = $_SESSION['periode_ferme'];
   $model_bulletin = $_SESSION['type_bulletin'];

function redimensionne_image($photo, $L_max, $H_max)
 {
	// prendre les informations sur l'image
	$info_image = getimagesize($photo);
	// largeur et hauteur de l'image d'origine
	$largeur = $info_image[0];
	$hauteur = $info_image[1];
	// largeur et/ou hauteur maximum � afficher en pixel
	 $taille_max_largeur = $L_max;
	 $taille_max_hauteur = $H_max;

	// calcule le ratio de redimensionnement
	 $ratio_l = $largeur / $taille_max_largeur;
	 $ratio_h = $hauteur / $taille_max_hauteur;
	 $ratio = ($ratio_l > $ratio_h)?$ratio_l:$ratio_h;

	// d�finit largeur et hauteur pour la nouvelle image
	 $nouvelle_largeur = $largeur / $ratio;
	 $nouvelle_hauteur = $hauteur / $ratio;

	// des Pixels vers Millimetres
	 $nouvelle_largeur = $nouvelle_largeur / 2.8346;
	 $nouvelle_hauteur = $nouvelle_hauteur / 2.8346;

	return array($nouvelle_largeur, $nouvelle_hauteur);
 }

//fonction calcul des moyennes de groupe [moyenne | moyenne mini | moyenne maxi]
function calcul_toute_moyenne_classe ($groupe_select, $periode_select)
 {
    global $prefix_base;

	$addition_des_notes=0; $note=0; $cpt_notes=0; $moyenne_mini=20; $moyenne_maxi=0;
	$requete_note = mysql_query('SELECT * FROM '.$prefix_base.'matieres_notes WHERE '.$prefix_base.'matieres_notes.id_groupe = "'.$groupe_select.'" AND '.$prefix_base.'matieres_notes.periode = "'.$periode_select.'"');
	while ($donner_note = mysql_fetch_array($requete_note))
	 {
		$note = $donner_note['note'];
		if($moyenne_mini>$note) { $moyenne_mini = $note; }
		if($moyenne_maxi<$note) { $moyenne_maxi = $note; }
		$addition_des_notes = $addition_des_notes+$note;
		$cpt_notes=$cpt_notes+1;
	 }
	$moyenne_groupe = $addition_des_notes / $cpt_notes;

	// renvoie un tableau avec [moyenne dugroupe | moyenne mini du groupe | moyenne maxi du groupe]
	return array($moyenne_groupe, $moyenne_mini, $moyenne_maxi);
 }

//permet de transformer les caract�re html
 function unhtmlentities($chaineHtml) {
         $tmp = get_html_translation_table(HTML_ENTITIES);
         $tmp = array_flip ($tmp);
         $chaineTmp = strtr ($chaineHtml, $tmp);

         return $chaineTmp;
 }


// format de date en fran�ais
function date_fr($var)
 {
        $var = explode("-",$var);
        $var = $var[2]."/".$var[1]."/".$var[0];
        return($var);
 }

// fonction affiche les moyennes avec les arrondies et le nombre de chiffre apr�s la virgule
// precision '0.01' '0.1' '0.25' '0.5' '1'
function present_nombre($nombre, $precision, $nb_chiffre_virgule, $chiffre_avec_zero)
 {
	if ( $precision === '' or $precision === '0.0' or $precision === '0' ) { $precision = '0.01'; }
	$nombre=number_format(round($nombre/$precision)*$precision, $nb_chiffre_virgule, ',', '');
        $nombre_explose = explode(",",$nombre);
	if($nombre_explose[1]==='0' and $chiffre_avec_zero==='1') { $nombre=$nombre_explose[0]; }
        return($nombre);
 }

class bul_PDF extends FPDF_MULTICELLTAG
{

/**
* Draws text within a box defined by width = w, height = h, and aligns
* the text vertically within the box ($valign = M/B/T for middle, bottom, or top)
* Also, aligns the text horizontally ($align = L/C/R/J for left, centered, right or justified)
* drawTextBox uses drawRows
*
* This function is provided by TUFaT.com
*/
function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=1)
{
    $xi=$this->GetX();
    $yi=$this->GetY();

    $hrow=$this->FontSize;
    $textrows=$this->drawRows($w,$hrow,$strText,0,$align,0,0,0);
    $maxrows=floor($h/$this->FontSize);
    $rows=min($textrows,$maxrows);

    if ($border==1)
        $this->Rect($xi,$yi,$w,$h,'D');
    if ($border==2)
        $this->Rect($xi,$yi,$w,$h,'DF');

    $dy=0;
    if (strtoupper($valign)=='M')
        $dy=($h-$rows*$this->FontSize)/2;
    if (strtoupper($valign)=='B')
        $dy=$h-$rows*$this->FontSize;

    $this->SetY($yi+$dy);
    $this->SetX($xi);

    $this->drawRows($w,$hrow,$strText,0,$align,0,$rows,1);

}

function drawRows($w,$h,$txt,$border=0,$align='J',$fill=0,$maxline=0,$prn=0)
{
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $b=0;
    if($border)
    {
        if($border==1)
        {
            $border='LTRB';
            $b='LRT';
            $b2='LR';
        }
        else
        {
            $b2='';
            if(is_int(strpos($border,'L')))
                $b2.='L';
            if(is_int(strpos($border,'R')))
                $b2.='R';
            $b=is_int(strpos($border,'T')) ? $b2.'T' : $b2;
        }
    }
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    while($i<$nb)
    {
        //Get next character
        $c=$s[$i];
        if($c=="\n")
        {
            //Explicit line break
            if($this->ws>0)
            {
                $this->ws=0;
                if ($prn==1) $this->_out('0 Tw');
            }
            if ($prn==1) {
                $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
            }
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border and $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
            continue;
        }
        if($c==' ')
        {
            $sep=$i;
            $ls=$l;
            $ns++;
        }
        $l+=$cw[$c];
        if($l>$wmax)
        {
            //Automatic line break
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
                if($this->ws>0)
                {
                    $this->ws=0;
                    if ($prn==1) $this->_out('0 Tw');
                }
                if ($prn==1) {
                    $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
            }
            else
            {
                if($align=='J')
                {
                    $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                    if ($prn==1) $this->_out(sprintf('%.3f Tw',$this->ws*$this->k));
                }
                if ($prn==1){
                    $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                }
                $i=$sep+1;
            }
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border and $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s,$i);
        }
        else
            $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
        $this->ws=0;
        if ($prn==1) $this->_out('0 Tw');
    }
    if($border and is_int(strpos($border,'B')))
        $b.='B';
    if ($prn==1) {
        $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
    }
    $this->x=$this->lMargin;
    return $nl;
}

function TextWithDirection($x,$y,$txt,$direction='R')
{
    $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
    if ($direction=='R')
        $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',1,0,0,1,$x*$this->k,($this->h-$y)*$this->k,$txt);
    elseif ($direction=='L')
        $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',-1,0,0,-1,$x*$this->k,($this->h-$y)*$this->k,$txt);
    elseif ($direction=='U')
        $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',0,1,-1,0,$x*$this->k,($this->h-$y)*$this->k,$txt);
    elseif ($direction=='D')
        $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',0,-1,1,0,$x*$this->k,($this->h-$y)*$this->k,$txt);
    else
        $s=sprintf('BT %.2f %.2f Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$txt);
    if ($this->ColorFlag)
        $s='q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
}

function TextWithRotation($x,$y,$txt,$txt_angle,$font_angle=0)
{
    $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));

    $font_angle+=90+$txt_angle;
    $txt_angle*=M_PI/180;
    $font_angle*=M_PI/180;

    $txt_dx=cos($txt_angle);
    $txt_dy=sin($txt_angle);
    $font_dx=cos($font_angle);
    $font_dy=sin($font_angle);

    $s=sprintf('BT %.2f %.2f %.2f %.2f %.2f %.2f Tm (%s) Tj ET',
             $txt_dx,$txt_dy,$font_dx,$font_dy,
             $x*$this->k,($this->h-$y)*$this->k,$txt);
    if ($this->ColorFlag)
        $s='q '.$this->TextColor.' '.$s.' Q';
    $this->_out($s);
}

// fonction graphique de niveau
function DiagBarre($X_placement, $Y_placement, $L_diagramme, $H_diagramme, $data, $place)
	{
		$this->SetFont('Courier', '', 10);
		//encadrement g�n�ral
		$this->Rect($X_placement, $Y_placement, $L_diagramme, $H_diagramme, 'D');
		//encadrement du diagramme
		$this->SetDrawColor(180);
		$X_placement_diagramme = $X_placement+0.5;
		$Y_placement_diagramme = $Y_placement+0.5;
		$L_diagramme_affiche = $L_diagramme-1;
		$H_diagramme_affiche = $H_diagramme-1;
		$this->Rect($X_placement_diagramme, $Y_placement_diagramme, $L_diagramme_affiche, $H_diagramme_affiche, 'D');


		//calcul de la longeur de chaque barre
		$nb_valeur=count($data);
		$L_barre = $L_diagramme_affiche/$nb_valeur;
		// calcul de la somme total des informations
		$total_des_valeur = array_sum($data);

		$espace_entre=$H_diagramme_affiche/$total_des_valeur;
		for($o=0;$o<$total_des_valeur;$o++)
		{
		$Y_echelle=$Y_placement_diagramme+($espace_entre*$o);
		//echelle
		$this->SetDrawColor(180);
		$this->Line($X_placement_diagramme, $Y_echelle, $X_placement_diagramme+$L_diagramme_affiche, $Y_echelle);
		}

		$i=0;
		foreach($data as $val) {
			//Barre
			if($place===$i) { $this->SetFillColor(5); } else { $this->SetFillColor(240); }
			$this->SetDrawColor(0, 0, 0);
			$H_barre = ($H_diagramme_affiche*$val)/$total_des_valeur;
			$Y_barre = ($Y_placement_diagramme+$H_diagramme_affiche) - $H_barre;
			$X_barre = $X_placement_diagramme+($L_barre*$i);
			$this->Rect($X_barre, $Y_barre, $L_barre, $H_barre, 'DF');
			$i++;
		}
	}

    //En-t�te du document
    function Header()
    {
    global $prefix_base;
	$model_bulletin=$_SESSION['type_bulletin'];
	if(!empty($model_bulletin))
	 {
	   $requete_model = mysql_query('SELECT id_model_bulletin, caractere_utilse, affiche_filigrame, texte_filigrame, affiche_logo_etab, entente_mel, entente_tel, entente_fax, L_max_logo, H_max_logo FROM '.$prefix_base.'model_bulletin WHERE id_model_bulletin="'.$model_bulletin.'"');
	   while($donner_model = mysql_fetch_array($requete_model))
	     {
		$caractere_utilse=$donner_model['caractere_utilse'];
		$affiche_filigrame=$donner_model['affiche_filigrame'];
		$texte_filigrame=$donner_model['texte_filigrame'];
		$affiche_logo_etab=$donner_model['affiche_logo_etab'];
		$entente_mel=$donner_model['entente_mel'];
		$entente_tel=$donner_model['entente_tel'];
		$entente_fax=$donner_model['entente_fax'];
		$L_max_logo=$donner_model['L_max_logo'];
		$H_max_logo=$donner_model['H_max_logo'];
	     }
	 } else {
		        $caractere_utilse = 'Arial';
			$affiche_filigrame='1'; // affiche un filigramme
			$texte_filigrame='DUPLICATA INTERNET'; // texte du filigrame
			$affiche_logo_etab='1';
			$entente_mel='1'; // afficher l'adresse mel dans l'ent�te
			$entente_tel='1'; // afficher le num�ro de t�l�phone dans l'ent�te
			$entente_fax='1'; // afficher le num�ro de fax dans l'ent�te
			$L_max_logo=75; $H_max_logo=75; //dimension du logo
		}

    //Affiche le filigrame
    if($affiche_filigrame==='1')
     {
      $this->SetFont('Arial','B',50);
      $this->SetTextColor(255,192,203);
      $this->TextWithRotation(40,190,$texte_filigrame,45);
      $this->SetTextColor(0,0,0);
     }

	//bloc identification etablissement
	$logo = '../images/'.getSettingValue('logo_etab');
	$format_du_logo = str_replace('.','',strstr(getSettingValue('logo_etab'), '.'));
	if($affiche_logo_etab==='1' and file_exists($logo) and getSettingValue('logo_etab') != '' and ($format_du_logo==='jpg' or $format_du_logo==='png'))
	{
	 $valeur=redimensionne_image($logo, $L_max_logo, $H_max_logo);
	 //$X_logo et $Y_logo; placement du bloc identite de l'�tablissement
	 $X_logo=5; $Y_logo=5; $L_logo=$valeur[0]; $H_logo=$valeur[1];
	 $X_etab=$X_logo+$L_logo; $Y_etab=$Y_logo;
	 //logo
         $this->Image($logo, $X_logo, $Y_logo, $L_logo, $H_logo);
	}

	//adresse
	 if ( !isset($X_etab) or empty($X_etab) ) { $X_etab = '5'; $Y_etab = '5'; }
 	 $this->SetXY($X_etab,$Y_etab);
 	 $this->SetFont($caractere_utilse,'',14);
//	 $this->SetFont($caractere_utilse,'',$taille);
	  $gepiSchoolName = getSettingValue('gepiSchoolName');
	 $this->Cell(90,7, $gepiSchoolName,0,2,'');
	 $this->SetFont($caractere_utilse,'',10);
	  $gepiSchoolAdress1 = getSettingValue('gepiSchoolAdress1');
	 $this->Cell(90,5, $gepiSchoolAdress1,0,2,'');
	  $gepiSchoolAdress2 = getSettingValue('gepiSchoolAdress2');
	 $this->Cell(90,5, $gepiSchoolAdress2,0,2,'');
	  $gepiSchoolZipCode = getSettingValue('gepiSchoolZipCode');
	  $gepiSchoolCity = getSettingValue('gepiSchoolCity');
	 $this->Cell(90,5, $gepiSchoolZipCode." ".$gepiSchoolCity,0,2,'');
	  $gepiSchoolTel = getSettingValue('gepiSchoolTel');
	  $gepiSchoolFax = getSettingValue('gepiSchoolFax');
	if($entente_tel==='1' and $entente_fax==='1') { $entete_communic = 'T�l: '.$gepiSchoolTel.' / Fax: '.$gepiSchoolFax; }
	if($entente_tel==='1' and empty($entete_communic)) { $entete_communic = 'T�l: '.$gepiSchoolTel; }
	if($entente_fax==='1' and empty($entete_communic)) { $entete_communic = 'Fax: '.$gepiSchoolFax; }
	if( isset($entete_communic) and $entete_communic != '' ) {
	 $this->Cell(90,5, $entete_communic,0,2,'');
	}
	if($entente_mel==='1') {
	  $gepiSchoolEmail = getSettingValue('gepiSchoolEmail');
	 $this->Cell(90,5, $gepiSchoolEmail,0,2,'');
	}
    }

    //Pied de page du document
    function Footer()
    {
        //Positionnement � 1 cm du bas et 0,5cm + 0,5cm du cot� gauche
   	$this->SetXY(5,-10);
        //Police Arial Gras 6
        $this->SetFont('Arial','B',8);
        $this->Cell(0,4.5, "Bulletin � conserver pr�cieusement. Aucun duplicata ne sera d�livr�. - GEPI : solution libre de gestion et de suivi des r�sultats scolaires.",0,0,'C');
    }
}

// chargement des information de la base de donn�es
	// connaitre la selection
		//si se sont des classes ou une classe qui � �t� s�lectionner on vas prendre l'id de tout leurs �l�ves
		//d�s que nous avons notre liste d'�l�ves � imprimer on vas prendre les informations

	//variable invaribale
		$gepiYear = getSettingValue('gepiYear');
		$annee_scolaire = $gepiYear;
		$date_bulletin=date("d/m/Y H:i");
		$nom_bulletin=date("Ymd_Hi");

// s�lection des information sur le mod�le des bulletins choisi.
if(!empty($model_bulletin))
  {
   $requete_model = mysql_query('SELECT * FROM '.$prefix_base.'model_bulletin WHERE id_model_bulletin="'.$model_bulletin.'"');
   while($donner_model = mysql_fetch_array($requete_model))
     {
	$active_bloc_datation = $donner_model['active_bloc_datation']; // afficher le cadre les informations datation du bulletin
	$active_bloc_eleve = $donner_model['active_bloc_eleve']; // afficher le cadre sur les informations �l�ve
	$active_bloc_adresse_parent = $donner_model['active_bloc_adresse_parent']; // afficher le cadre adresse des parents
	$active_bloc_absence = $donner_model['active_bloc_absence']; // afficher le cadre absences de l'�l�ve
	$active_bloc_note_appreciation = $donner_model['active_bloc_note_appreciation']; // afficher les notes et appr�ciations
	$active_bloc_avis_conseil = $donner_model['active_bloc_avis_conseil']; // afficher les avis du conseil de classe
	$active_bloc_chef = $donner_model['active_bloc_chef']; // fait - afficher la signature du chef
	$active_photo = $donner_model['active_photo']; // fait - afficher la photo de l'�l�ve
	$active_coef_moyenne = $donner_model['active_coef_moyenne']; // fait - afficher le co�ficient des moyenne par mati�re
	$active_nombre_note = $donner_model['active_nombre_note']; // fait - afficher le nombre de note par mati�re sous la moyenne de l'�l�ve
	$active_nombre_note_case = $donner_model['active_nombre_note_case']; // fait - afficher le nombre de note par mati�re
	$active_moyenne = $donner_model['active_moyenne']; // fait - afficher les moyennes
	$active_moyenne_eleve = $donner_model['active_moyenne_eleve']; // fait - afficher la moyenne de l'�l�ve
	$active_moyenne_classe = $donner_model['active_moyenne_classe']; // fait - afficher les moyennes de la classe
	$active_moyenne_min = $donner_model['active_moyenne_min']; // fait - afficher les moyennes minimum
	$active_moyenne_max = $donner_model['active_moyenne_max']; // fait - afficher les moyennes maximum
	$active_regroupement_cote = $donner_model['active_regroupement_cote']; // fait - afficher le nom des regroupement sur le cot�
	$active_entete_regroupement = $donner_model['active_entete_regroupement']; // fait - afficher les ent�te des regroupement
	$active_moyenne_regroupement = $donner_model['active_moyenne_regroupement']; // fait - afficher les moyennes des regroupement
	$active_rang = $donner_model['active_rang']; // fait - afficher le rang de l'�l�ve
	$active_graphique_niveau = $donner_model['active_graphique_niveau']; // fait - afficher le graphique des niveaux
	$active_appreciation = $donner_model['active_appreciation']; // fait - afficher les appr�ciations des professeurs
	$affiche_doublement = $donner_model['affiche_doublement']; // affiche si l'�l�ve � doubler
	$affiche_date_naissance = $donner_model['affiche_date_naissance']; // affiche la date de naissance de l'�l�ve
	$affiche_dp = $donner_model['affiche_dp']; // affiche l'�tat de demi pension ou extern
	$affiche_nom_court = $donner_model['affiche_nom_court']; // affiche le nom court de la classe
	$affiche_effectif_classe = $donner_model['affiche_effectif_classe']; // affiche l'effectif de la classe
	$affiche_numero_impression = $donner_model['affiche_numero_impression']; // affiche le num�ro d'impression des bulletins
	$affiche_etab_origine = $donner_model['affiche_etab_origine']; // affiche l'�tablissement d'orignine
 	$active_reperage_eleve = $donner_model['active_reperage_eleve']; // activ� la couleur de r�parage des moyenne de l'�l�ve
	$couleur_reperage_eleve1 = $donner_model['couleur_reperage_eleve1']; // couleur 1 du rep�rage ci-dessus
	$couleur_reperage_eleve2 = $donner_model['couleur_reperage_eleve2']; // couleur 2 du rep�rage ci-dessus
	$couleur_reperage_eleve3 = $donner_model['couleur_reperage_eleve3']; // couleur 3 du rep�rage ci-dessus
	$couleur_categorie_entete = $donner_model['couleur_categorie_entete']; // activ� la couleur de fond des cat�gorie ent�te
	$couleur_categorie_entete1 = $donner_model['couleur_categorie_entete1']; // couleur 1 du rep�rage ci-dessus
	$couleur_categorie_entete2 = $donner_model['couleur_categorie_entete2']; // couleur 2 du rep�rage ci-dessus
	$couleur_categorie_entete3 = $donner_model['couleur_categorie_entete3']; // couleur 3 du rep�rage ci-dessus
	$couleur_categorie_cote = $donner_model['couleur_categorie_cote']; // activ� la couleur de fond des cat�gorie sur le cot�
	$couleur_categorie_cote1 = $donner_model['couleur_categorie_cote1']; // couleur 1 du rep�rage ci-dessus
	$couleur_categorie_cote2 = $donner_model['couleur_categorie_cote2']; // couleur 2 du rep�rage ci-dessus
	$couleur_categorie_cote3 = $donner_model['couleur_categorie_cote3']; // couleur 3 du rep�rage ci-dessus
	$couleur_moy_general = $donner_model['couleur_moy_general']; // activer la couleur moyenne g�n�ral
	$couleur_moy_general1 = $donner_model['couleur_moy_general1']; // couleur 1 de la moyenne g�n�ral
	$couleur_moy_general2 = $donner_model['couleur_moy_general2']; // couleur 2 de la moyenne g�n�ral
	$couleur_moy_general3 = $donner_model['couleur_moy_general3']; // couleur 3 de la moyenne g�n�ral
	$titre_entete_matiere = $donner_model['titre_entete_matiere']; // texte de la colone mati�re
	$titre_entete_coef = $donner_model['titre_entete_coef']; // texte de la colone co�fficiant
	$titre_entete_nbnote = $donner_model['titre_entete_nbnote']; // texte de la colone nombre de note
	$titre_entete_rang = $donner_model['titre_entete_rang']; // texte de la colone rang
	$titre_entete_appreciation = unhtmlentities($donner_model['titre_entete_appreciation']); //texte de la colone appr�ciation
	$toute_moyenne_meme_col = $donner_model['toute_moyenne_meme_col']; //texte de la colone appr�ciation
	$entete_model_bulletin = $donner_model['entete_model_bulletin']; //choix du type d'entete des moyennes
	$ordre_entete_model_bulletin = $donner_model['ordre_entete_model_bulletin']; // ordre des ent�tes tableau du bulletin
	// information param�trage
	$caractere_utilse = $donner_model['caractere_utilse'];
	// cadre identit�e parents
	$X_parent=$donner_model['X_parent']; $Y_parent=$donner_model['Y_parent'];
	$imprime_pour=$donner_model['imprime_pour'];
	// cadre identit�e eleve
	$X_eleve=$donner_model['X_eleve']; $Y_eleve=$donner_model['Y_eleve'];
	$cadre_eleve=$donner_model['cadre_eleve'];
	// cadre de datation du bulletin
	$X_datation_bul=$donner_model['X_datation_bul']; $Y_datation_bul=$donner_model['Y_datation_bul'];
	$cadre_datation_bul=$donner_model['cadre_datation_bul'];
	// si les cat�gorie son affich� avec moyenne
	$hauteur_info_categorie=$donner_model['hauteur_info_categorie'];
	// cadre des notes et app
	$X_note_app=$donner_model['X_note_app']; $Y_note_app=$donner_model['Y_note_app']; $longeur_note_app=$donner_model['longeur_note_app']; $hauteur_note_app=$donner_model['hauteur_note_app'];
	if($active_regroupement_cote==='1') { $X_note_app=$X_note_app+5; $Y_note_app=$Y_note_app; $longeur_note_app=$longeur_note_app-5; $hauteur_note_app=$hauteur_note_app; }
	//coef des matiere
	$largeur_coef_moyenne = $donner_model['largeur_coef_moyenne'];
	//nombre de note par mati�re
	$largeur_nombre_note = $donner_model['largeur_nombre_note'];
	//champ des moyennes
	$largeur_d_une_moyenne = $donner_model['largeur_d_une_moyenne'];
	//graphique de niveau
	$largeur_niveau = $donner_model['largeur_niveau'];
	//rang de l'�l�ve
	$largeur_rang = $donner_model['largeur_rang'];
	// cadre absence
	$X_absence=$donner_model['X_absence']; $Y_absence=$donner_model['Y_absence'];
	// entete du bas contient les moyennes g�rn�ral
	$hauteur_entete_moyenne_general = $donner_model['hauteur_entete_moyenne_general'];
	// cadre des Avis du conseil de classe
	$X_avis_cons=$donner_model['X_avis_cons']; $Y_avis_cons=$donner_model['Y_avis_cons']; $longeur_avis_cons=$donner_model['longeur_avis_cons']; $hauteur_avis_cons=$donner_model['hauteur_avis_cons'];
	$cadre_avis_cons=$donner_model['cadre_avis_cons'];
	// cadre signature du chef
	$X_sign_chef=$donner_model['X_sign_chef']; $Y_sign_chef=$donner_model['Y_sign_chef']; $longeur_sign_chef=$donner_model['longeur_sign_chef']; $hauteur_sign_chef=$donner_model['hauteur_sign_chef'];
	$cadre_sign_chef=$donner_model['cadre_sign_chef'];

	//gestion des moyennes
	$arrondie_choix=$donner_model['arrondie_choix'];
	$nb_chiffre_virgule=$donner_model['nb_chiffre_virgule'];
	$chiffre_avec_zero=$donner_model['chiffre_avec_zero'];

	$autorise_sous_matiere=$donner_model['autorise_sous_matiere'];
	$affichage_haut_responsable=$donner_model['affichage_haut_responsable'];
	}
} else {
	// information d'activation des diff�rents partie du bulletin
	$active_bloc_datation = '1'; // fait - afficher les informations de datation du bulletin
	$active_bloc_eleve = '1'; // fait - afficher les informations sur l'�l�ve
	$active_bloc_adresse_parent = '1'; // fait - afficher l'adresse des parents
	$active_bloc_absence = '1'; // fait - afficher les absences de l'�l�ve
	$active_bloc_note_appreciation = '1'; // fait - afficher les notes et appr�ciations
	$active_bloc_avis_conseil = '1'; // fait - afficher les avis du conseil de classe
	$active_bloc_chef = '1'; // fait - afficher la signature du chef
	$active_photo = '0'; // fait - afficher la photo de l'�l�ve
	$active_coef_moyenne = '1'; // fait - afficher le co�ficient des moyenne par mati�re
	$active_coef_sousmoyene = '1'; // fait - afficher le co�ficient des moyenne par mati�re
	$active_nombre_note = '1'; // fait - afficher le nombre de note par mati�re sous la moyenne de l'�l�ve
	$active_nombre_note_case = '1'; // fait - afficher le nombre de note par mati�re
	$active_moyenne = '1'; // fait - afficher les moyennes
	$active_moyenne_eleve = '1'; // fait - afficher la moyenne de l'�l�ve
	$active_moyenne_classe = '1'; // fait - afficher les moyennes de la classe
	$active_moyenne_min = '1'; // fait - afficher les moyennes minimum
	$active_moyenne_max = '1'; // fait - afficher les moyennes maximum
	$active_regroupement_cote = '1'; // fait - afficher le nom des regroupement sur le cot�
	$active_entete_regroupement = '1'; // fait - afficher les ent�te des regroupement
	$active_moyenne_regroupement = '1'; // fait - afficher les moyennes des regroupement
	$active_rang = '1'; // fait - afficher le rang de l'�l�ve
	$active_graphique_niveau = '1'; // fait - afficher le graphique des niveaux
	$active_appreciation = '1'; // fait - afficher les appr�ciations des professeurs

	$affiche_doublement = '1'; // affiche si l'�l�ve � doubler
	$affiche_date_naissance = '1'; // affiche la date de naissance de l'�l�ve
	$affiche_dp = '1'; // affiche l'�tat de demi pension ou extern
	$affiche_nom_court = '1'; // affiche le nom court de la classe
	$affiche_effectif_classe = '1'; // affiche l'effectif de la classe
	$affiche_numero_impression = '1'; // affiche le num�ro d'impression des bulletins
	$affiche_etab_origine = '0'; // affiche l'�tablissement d'origine

	$toute_moyenne_meme_col='0'; // afficher les information moyenne classe/min/max sous la moyenne g�n�ral de l'�l�ve
	$active_coef_sousmoyene = '1'; //afficher le coeficent en dessous de la moyenne de l'�l�ve

	$entete_model_bulletin = '1'; //choix du type d'entete des moyennes
	$ordre_entete_model_bulletin = '1'; // ordre des ent�tes tableau du bulletin

	// information param�trage
	$caractere_utilse = 'Arial';
	// cadre identit�e parents
	$X_parent=110; $Y_parent=40;
	$imprime_pour = 1;
	// cadre identit�e eleve
	$X_eleve=5; $Y_eleve=40;
	$cadre_eleve=1;
	// cadre de datation du bulletin
	$X_datation_bul=110; $Y_datation_bul=5;
	$cadre_datation_bul=1;
	// si les cat�gorie son affich� avec moyenne
	$hauteur_info_categorie=5;
	// cadre des notes et app
	$X_note_app=5; $Y_note_app=72; $longeur_note_app=200; $hauteur_note_app=175;
	if($active_regroupement_cote==='1') { $X_note_app=$X_note_app+5; $Y_note_app=$Y_note_app; $longeur_note_app=$longeur_note_app-5; $hauteur_note_app=$hauteur_note_app; }
	 //coef des matiere
	  $largeur_coef_moyenne = 8;
	 //nombre de note par mati�re
	  $largeur_nombre_note = 8;
	 //champ des moyennes
	  $largeur_d_une_moyenne = 10;
	 //graphique de niveau
          $largeur_niveau = 18;
	 //rang de l'�l�ve
          $largeur_rang = 8;
	//autres infos
		  $active_reperage_eleve = '1';
		  $couleur_reperage_eleve1 = '255';
		  $couleur_reperage_eleve2 = '255';
		  $couleur_reperage_eleve3 = '207';
		  $couleur_categorie_cote = '1';
	          $couleur_categorie_cote1='239';
		  $couleur_categorie_cote2='239';
		  $couleur_categorie_cote3='239';
		  $couleur_categorie_entete = '1';
	          $couleur_categorie_entete1='239';
		  $couleur_categorie_entete2='239';
		  $couleur_categorie_entete3='239';
		  $couleur_moy_general = '1';
	          $couleur_moy_general1='239';
		  $couleur_moy_general2='239';
		  $couleur_moy_general3='239';
		  $titre_entete_matiere='Mati�re';
		 $active_coef_sousmoyene = '1'; $titre_entete_coef='coef.';
		  $titre_entete_nbnote='nb. n.';
		  $titre_entete_rang='rang';
		  $titre_entete_appreciation='Appr�ciation/Conseils';
	// cadre absence
	$X_absence=5; $Y_absence=246.3;
	// entete du bas contient les moyennes g�rn�ral
	$hauteur_entete_moyenne_general = 5;
	// cadre des Avis du conseil de classe
	$X_avis_cons=5; $Y_avis_cons=250; $longeur_avis_cons=130; $hauteur_avis_cons=37;
	$cadre_avis_cons=1;
	// cadre signature du chef
	$X_sign_chef=138; $Y_sign_chef=250; $longeur_sign_chef=67; $hauteur_sign_chef=37;
	$cadre_sign_chef=0;
	//les moyennes
	$arrondie_choix='0.01'; //arrondie de la moyenne
	$nb_chiffre_virgule='1'; //nombre de chiffre apr�s la virgule
	$chiffre_avec_zero='1'; // si une moyenne se termine par ,00 alors on supprimer les zero

	$autorise_sous_matiere = '1'; //autorise l'affichage des sous mati�re
	$affichage_haut_responsable = '1'; //affiche le nom du haut responsable de la classe
	}


// information � retenir pour la construction des bulletins

	//r�cup�ration des p�diode periode_classe[num�ro de la classe][compteur]

	//attribuer une selection de p�riode � une classe
	$cpt_p=0; $ancienne_classe='';
	$cpt_p_interne=0;
	while(!empty($periode[$cpt_p]))
	{
		// $periode[$cpt_p] d�tien le nom de la p�riode par exemple 1er trimestre

		// nous allons rechercher toute les classes qui ont le m�me nom de p�riode
		if ( isset($id_classe[0]) and !empty($id_classe[0]) )
		{
		 	$o=0; $prepa_requete = "";
		        while(!empty($id_classe[$o]))
			{
				if($o == "0") { $prepa_requete = 'id_classe = "'.$id_classe[$o].'"'; }
				if($o != "0") { $prepa_requete = $prepa_requete.' OR id_classe = "'.$id_classe[$o].'" '; }
				$o = $o + 1;
       	     		}
		}

		// nous allons rechercher toute les classes qui ont le m�me nom de p�riode par les �l�ves
		if ( isset($id_eleve[0]) and !empty($id_eleve[0]) )
		{
		 	$o=0; $prepa_requete = "";
		        while(!empty($id_eleve[$o]))
			{
				if($o == "0") { $prepa_requete = 'jec.login = "'.$id_eleve[$o].'"'; }
				if($o != "0") { $prepa_requete = $prepa_requete.' OR jec.login = "'.$id_eleve[$o].'" '; }
				$o = $o + 1;
       	     		}
		}
	$cpt_p = $cpt_p + 1;
	}

	// on enl�ve tout le supperflux du nom de la p�riode
	if (isset($periode[0]))
	{
 		$o=0;
	        while(!empty($periode[$o]))
		{
			$periode[$o] = eregi_replace("[ .'_-]{1}",'',$periode[$o]); //supprime les espace les . les ' les _ et -
			$periode[$o] = strtolower($periode[$o]); // mais en minuscule
			$periode[$o] = html_entity_decode($periode[$o]);
			$periode[$o] = eregi_replace("[����]{1}","e",$periode[$o]); // supprime les accents
			$o = $o + 1;
	        }
	}

	$cpt_p_interne = 0;
		if ( isset($id_classe[0]) and !empty($id_classe[0]) )
		{
			$requete_periode_select ="SELECT * FROM ".$prefix_base."periodes WHERE (".$prepa_requete.") ORDER BY nom_periode";
		}
		if ( isset($id_eleve[0]) and !empty($id_eleve[0]) )
		{
			$requete_periode_select ="SELECT * FROM ".$prefix_base."periodes p, ".$prefix_base."j_eleves_classes jec WHERE ( (".$prepa_requete.") AND jec.id_classe = p.id_classe ) GROUP BY p.num_periode, p.id_classe ORDER BY p.nom_periode";
		}

      	$execution_periode_select = mysql_query($requete_periode_select) or die('Erreur SQL !'.$requete_periode_select.'<br />'.mysql_error());
      	while ( $donnee_periode_select = mysql_fetch_array($execution_periode_select) )
	{
		// nom de la p�riode ex: 1er trimestre
		$nom_periode_select = $donnee_periode_select['nom_periode'];
		// id de la classe
		$id_classe_periode = $donnee_periode_select['id_classe'];
		// savoir si elles est v�rouillez
		$periode_verouillez_classe = $donnee_periode_select['verouiller'];
			// on transforme le nom de la p�riode sans accens sans espace...
			$nom_periode_select = eregi_replace("[ .'_-]{1}",'',$nom_periode_select); //supprime les espace les . les ' les _ et -
			$nom_periode_select = strtolower($nom_periode_select); // mais en minuscule
			$nom_periode_select = html_entity_decode($nom_periode_select);
			$nom_periode_select = eregi_replace("[����]{1}",'e',$nom_periode_select); // supprime les accents

		// si la classe et la p�riode corresponde alors on initialise
		if ( ( $periode_verouillez_classe === 'O' or $periode_verouillez_classe === 'P' ) or $periode_ferme != '1' )
		{
			if ( in_array($nom_periode_select, $periode) ) {
				if ( !isset($periode_classe[$id_classe_periode]) )
				{
					$cpt_p_interne = 0;
					$periode_classe[$id_classe_periode][$cpt_p_interne] = $donnee_periode_select['num_periode'];
				}
				elseif ( isset($periode_classe[$id_classe_periode]) )
				{
					$compte_entrer = count($periode_classe[$id_classe_periode]);
					$cpt_p_interne = $compte_entrer;
					$periode_classe[$id_classe_periode][$cpt_p_interne] = $donnee_periode_select['num_periode'];
				}
			}
		}
	}



	// sql s�lection des eleves et de leurs informations
	//requ�te des classes s�lectionn�
	if (isset($id_classe[0])) {
 	$o=0; $prepa_requete = "";
        while(!empty($id_classe[$o]))
	     {
		if($o == "0") { $prepa_requete = $prefix_base.'j_eleves_classes.id_classe = "'.$id_classe[$o].'"'; }
		if($o != "0") { $prepa_requete = $prepa_requete.' OR '.$prefix_base.'j_eleves_classes.id_classe = "'.$id_classe[$o].'" '; }
		$o = $o + 1;
             }
	}
	//requ�te des �l�ves s�lectionn�
	if (!empty($id_eleve[0])) {
 	$o=0; $prepa_requete = "";
        while(!empty($id_eleve[$o]))
	     {
		if($o == "0") { $prepa_requete = $prefix_base.'eleves.login = "'.$id_eleve[$o].'"'; }
		if($o != "0") { $prepa_requete = $prepa_requete.' OR '.$prefix_base.'eleves.login = "'.$id_eleve[$o].'" '; }
		$o = $o + 1;
             }
	}

	//tableau des donn�es �l�ve
		if (isset($id_classe[0])) { $call_eleve = mysql_query('SELECT * FROM '.$prefix_base.'eleves, '.$prefix_base.'j_eleves_classes, '.$prefix_base.'classes, '.$prefix_base.'j_eleves_regime WHERE '.$prefix_base.'j_eleves_classes.id_classe = '.$prefix_base.'classes.id AND '.$prefix_base.'eleves.login = '.$prefix_base.'j_eleves_classes.login AND '.$prefix_base.'j_eleves_regime.login='.$prefix_base.'eleves.login AND ('.$prepa_requete.') GROUP BY '.$prefix_base.'eleves.login ORDER BY '.$prefix_base.'j_eleves_classes.id_classe ASC, '.$prefix_base.'eleves.nom ASC, '.$prefix_base.'eleves.prenom ASC'); }
		if (isset($id_eleve[0])) { $call_eleve = mysql_query('SELECT * FROM '.$prefix_base.'eleves, '.$prefix_base.'j_eleves_classes, '.$prefix_base.'classes, '.$prefix_base.'j_eleves_regime WHERE '.$prefix_base.'j_eleves_classes.id_classe = '.$prefix_base.'classes.id AND ('.$prepa_requete.') AND '.$prefix_base.'eleves.login = '.$prefix_base.'j_eleves_classes.login AND '.$prefix_base.'j_eleves_regime.login='.$prefix_base.'eleves.login GROUP BY '.$prefix_base.'eleves.login ORDER BY '.$prefix_base.'j_eleves_classes.id_classe ASC, '.$prefix_base.'eleves.nom ASC, '.$prefix_base.'eleves.prenom ASC'); }
		//on compte les �l�ves s�lectionn�
		    $nb_eleves = mysql_num_rows($call_eleve);
		    $cpt_i = 1;
		    while ( $donner = mysql_fetch_array( $call_eleve ))
			{
				$ident_eleve[$cpt_i] = $donner['login'];
				$ident_eleve_sel1 = $ident_eleve[$cpt_i];
				$ele_id_eleve[$cpt_i] = $donner['ele_id'];
				$nom_eleve[$cpt_i] = $donner['nom'];
				$prenom_eleve[$cpt_i] = $donner['prenom'];
				$sexe[$cpt_i] = $donner['sexe'];
				if ($sexe[$cpt_i] == "M") {
			            $date_naissance[$cpt_i] = 'N� le '.date_fr($donner['naissance']);
			        } else {
				            $date_naissance[$cpt_i] = 'N�e le '.date_fr($donner['naissance']);
				       }
				$classe_id[$cpt_i] = $donner['id'];
				$classe_nomlong[$cpt_i] = $donner['nom_complet'];
				$classe_nomcour[$cpt_i] = $donner['classe'];
				$photo[$cpt_i] = "../photos/eleves/".strtolower($donner['elenoet']).".jpg";
				$doublement[$cpt_i]='';
				if($donner['doublant']==='R') {  if($sexe[$cpt_i]==='M') { $doublement[$cpt_i]='doublant'; } else { $doublement[$cpt_i]='doublante'; } }
        			if($donner['regime']==='d/p') { $dp[$cpt_i]='demi-pensionnaire'; }
 				if($donner['regime']==='ext.') { $dp[$cpt_i]='externe'; }
			        if($donner['regime']==='int.') { $dp[$cpt_i]='interne'; }
			        if($donner['regime']==='i-e') { if($sexe[$cpt_i]==='M') { $dp[$cpt_i]='interne extern�'; } else { $dp[$cpt_i]='interne extern�e'; } }
				if($donner['regime']!='ext.' and $donner['regime']!='d/p' and $donner['regime']==='int.' and $donner['regime']==='i-e') { $dp[$cpt_i]='inconnu'; }

				// etablissement d'origine
					// on v�rifi si l'�l�ve � un �tablissement d'origine
				        $cpt_etab_origine = mysql_result(mysql_query("SELECT count(*) FROM ".$prefix_base."j_eleves_etablissements jee, ".$prefix_base."etablissements etab WHERE jee.id_eleve = '".$ident_eleve_sel1."' AND jee.id_etablissement = etab.id"),0);
				        if($cpt_etab_origine != 0) {
           					$requete_etablissement_origine = "SELECT * FROM ".$prefix_base."j_eleves_etablissements jee, ".$prefix_base."etablissements etab WHERE jee.id_eleve = '".$ident_eleve_sel1."' AND jee.id_etablissement = etab.id";
				                $execution_etablissement_origine = mysql_query($requete_etablissement_origine) or die('Erreur SQL !'.$requete_etablissement_origine.'<br />'.mysql_error());
				                while ($donnee_etablissement_origine = mysql_fetch_array($execution_etablissement_origine))
				                {
							$etablissement_origine[$cpt_i] = $donnee_etablissement_origine['nom'].' ('.$donnee_etablissement_origine['id'].')';
						}
					}

				//connaitre le professeur responsable de l'�l�ve
				$requete_pp = mysql_query('SELECT professeur FROM '.$prefix_base.'j_eleves_professeurs WHERE (login="'.$ident_eleve[$cpt_i].'" AND id_classe="'.$classe_id[$cpt_i].'")');
        			$prof_suivi_login = @mysql_result($requete_pp, '0', 'professeur');
				$pp_classe[$cpt_i] = '<b>'.ucfirst(getSettingValue("gepi_prof_suivi")).' : </b><i>'.affiche_utilisateur($prof_suivi_login,$classe_id[$cpt_i]).'</i>';

				//les responsables
				$nombre_de_responsable = 0;
				$nombre_de_responsable =  mysql_result(mysql_query("SELECT count(*) FROM ".$prefix_base."resp_pers rp, ".$prefix_base."resp_adr ra, ".$prefix_base."responsables2 r WHERE ( r.ele_id = '".$ele_id_eleve[$cpt_i]."' AND r.pers_id = rp.pers_id AND rp.adr_id = ra.adr_id )"),0);
				if($nombre_de_responsable != 0)
				{
					$cpt_parents = 0;
					$requete_parents = mysql_query("SELECT * FROM ".$prefix_base."resp_pers rp, ".$prefix_base."resp_adr ra, ".$prefix_base."responsables2 r WHERE ( r.ele_id = '".$ele_id_eleve[$cpt_i]."' AND r.pers_id = rp.pers_id AND rp.adr_id = ra.adr_id ) ORDER BY resp_legal ASC");
					while ($donner_parents = mysql_fetch_array($requete_parents))
				  	 {
						$civilite_parents[$ident_eleve_sel1][$cpt_parents] = $donner_parents['civilite'];
					        $nom_parents[$ident_eleve_sel1][$cpt_parents] = $donner_parents['nom'];
			        		$prenom_parents[$ident_eleve_sel1][$cpt_parents] = $donner_parents['prenom'];
					        $adresse1_parents[$ident_eleve_sel1][$cpt_parents] = $donner_parents['adr1'];
					        $adresse2_parents[$ident_eleve_sel1][$cpt_parents] = $donner_parents['adr2'];
					        $ville_parents[$ident_eleve_sel1][$cpt_parents] = $donner_parents['commune'];
					        $cp_parents[$ident_eleve_sel1][$cpt_parents] = $donner_parents['cp'];
						$cpt_parents = $cpt_parents + 1;
					 }

				} else {
					 $civilite_parents[$ident_eleve_sel1][0] = '';
				         $nom_parents[$ident_eleve_sel1][0] = '';
			        	 $prenom_parents[$ident_eleve_sel1][0] = '';
				         $adresse1_parents[$ident_eleve_sel1][0] = '';
				         $adresse2_parents[$ident_eleve_sel1][0] = '';
				         $ville_parents[$ident_eleve_sel1][0] = '';
				         $cp_parents[$ident_eleve_sel1][0] = '';
					 $civilite_parents[$ident_eleve_sel1][1] = '';
			        	 $nom_parents[$ident_eleve_sel1][1] = '';
				         $prenom_parents[$ident_eleve_sel1][1] = '';
				         $adresse1_parents[$ident_eleve_sel1][1] = '';
				         $adresse2_parents[$ident_eleve_sel1][1] = '';
				         $ville_parents[$ident_eleve_sel1][1] = '';
				         $cp_parents[$ident_eleve_sel1][1] = '';
					}

				// si deux envoi car adresse diff�rent des responsables, par d�faut = 1
				$nb_bulletin_parent[$ident_eleve_sel1] = 1;
				if ( isset($adresse1_parents[$ident_eleve_sel1][1]) )
				{
					if ( $imprime_pour === '1' ) { $nb_bulletin_parent[$ident_eleve_sel1] = 1; }
					if ( $imprime_pour === '2' ) {
						if ( $adresse1_parents[$ident_eleve_sel1][0] != $adresse1_parents[$ident_eleve_sel1][1] and $adresse1_parents[$ident_eleve_sel1][1] != '' )
						{
							$nb_bulletin_parent[$ident_eleve_sel1] = 2;
						} else { $nb_bulletin_parent[$ident_eleve_sel1] = 1; }
						if ( $nom_parents[$ident_eleve_sel1][0] === '' ) { $nb_bulletin_parent[$ident_eleve_sel1] = 1; }
					}
					if ( $imprime_pour === '3' and $nom_parents[$ident_eleve_sel1][1] != '' ) { $nb_bulletin_parent[$ident_eleve_sel1] = 2; }
					if ( $imprime_pour === '3' and $nom_parents[$ident_eleve_sel1][1] === '' ) { $nb_bulletin_parent[$ident_eleve_sel1] = 1; }
				} else { $nb_bulletin_parent[$ident_eleve_sel1] = 1; }


				//conna�tre le cpe de l'�l�ve
				$query = mysql_query("SELECT u.login login FROM utilisateurs u, j_eleves_cpe j WHERE (u.login = j.cpe_login AND j.e_login = '".$ident_eleve[$cpt_i]."')");
			        $current_eleve_cperesp_login = @mysql_result($query, "0", "login");
				$cpe_eleve[$cpt_i] = '<i>'.affiche_utilisateur($current_eleve_cperesp_login,$classe_id[$cpt_i]).'</i>';

				//=================================
				// AJOUT: boireaus
				$cperesp_login[$cpt_i] = $current_eleve_cperesp_login;
				//=================================

			$cpt_i = $cpt_i + 1;
			}
	$nb_eleve_total = $cpt_i-1; //nombre total d'�l�ve s�lectionn�
	// fin de la s�lection des informations sur les �l�ves selectionn�

	//recherche des donn�es de notation et d'appreciation
		//on recherche les donne �l�ve par �l�ve

$passage_deux = 'non';
$cpt_info_eleve=1;
while($cpt_info_eleve<=$nb_eleve_total)
 {
    $cpt_info_periode=0;
	$id_classe = $classe_id[$cpt_info_eleve]; // classe de l'�l�ve

	while(!empty($periode_classe[$id_classe][$cpt_info_periode]))
	 {
		$id_periode=$periode_classe[$id_classe][$cpt_info_periode];
		$nombre_de_matiere = 0;
		$moy_general_eleve = 0;
		$cpt_info_eleve_matiere=0;
		//prendre toutes les mati�res dont fait partie l'�l�ve dans une p�riode donn�
			// syst�me de classement par ordre
			$systeme_de_classement=''.$prefix_base.'matieres.nom_complet ASC';
			if($active_regroupement_cote==='1' or $active_entete_regroupement==='1') { $systeme_de_classement = ' '.$prefix_base.'j_matieres_categories_classes.priority ASC, '.$prefix_base.'j_groupes_classes.priorite ASC, '.$prefix_base.'matieres_categories.id ASC,'.$systeme_de_classement; }
			if($active_regroupement_cote!='1' and $active_entete_regroupement!='1') { $systeme_de_classement = ' '.$prefix_base.'j_groupes_classes.priorite ASC, '.$systeme_de_classement; }
	//	$requete_toute_matier = mysql_query('SELECT * FROM '.$prefix_base.'matieres_notes, '.$prefix_base.'j_groupes_matieres, '.$prefix_base.'matieres, '.$prefix_base.'groupes, '.$prefix_base.'matieres_categories WHERE '.$prefix_base.'matieres_notes.login = "'.$ident_eleve[$cpt_info_eleve].'" AND '.$prefix_base.'matieres_notes.periode = "'.$id_periode.'" AND '.$prefix_base.'j_groupes_matieres.id_groupe='.$prefix_base.'groupes.id AND '.$prefix_base.'j_groupes_matieres.id_matiere='.$prefix_base.'matieres.matiere AND '.$prefix_base.'matieres_notes.id_groupe = '.$prefix_base.'groupes.id AND '.$prefix_base.'matieres.categorie_id='.$prefix_base.'matieres_categories.id ORDER BY '.$systeme_de_classement.'');
	$requete_toute_matier = mysql_query('SELECT * FROM '.$prefix_base.'matieres_notes, '.$prefix_base.'j_groupes_matieres, '.$prefix_base.'matieres, '.$prefix_base.'groupes, '.$prefix_base.'matieres_categories, '.$prefix_base.'j_groupes_classes, '.$prefix_base.'j_matieres_categories_classes WHERE '.$prefix_base.'matieres_notes.login = "'.$ident_eleve[$cpt_info_eleve].'" AND '.$prefix_base.'matieres_notes.periode = "'.$id_periode.'" AND '.$prefix_base.'j_groupes_matieres.id_groupe='.$prefix_base.'groupes.id AND '.$prefix_base.'j_groupes_matieres.id_matiere='.$prefix_base.'matieres.matiere AND '.$prefix_base.'matieres_notes.id_groupe = '.$prefix_base.'groupes.id AND '.$prefix_base.'matieres.categorie_id='.$prefix_base.'matieres_categories.id AND '.$prefix_base.'j_groupes_classes.id_groupe='.$prefix_base.'groupes.id AND '.$prefix_base.'j_matieres_categories_classes.classe_id='.$prefix_base.'j_groupes_classes.id_classe AND '.$prefix_base.'j_matieres_categories_classes.categorie_id='.$prefix_base.'matieres_categories.id ORDER BY '.$systeme_de_classement.'');
		// compteur du nombre de mati�re
		$nombre_de_matiere =  mysql_result(mysql_query('SELECT count(*) FROM '.$prefix_base.'matieres_notes, '.$prefix_base.'j_groupes_matieres, '.$prefix_base.'matieres, '.$prefix_base.'groupes, '.$prefix_base.'matieres_categories WHERE '.$prefix_base.'matieres_notes.login = "'.$ident_eleve[$cpt_info_eleve].'" AND '.$prefix_base.'matieres_notes.periode = "'.$id_periode.'" AND '.$prefix_base.'j_groupes_matieres.id_groupe='.$prefix_base.'groupes.id AND '.$prefix_base.'j_groupes_matieres.id_matiere='.$prefix_base.'matieres.matiere AND '.$prefix_base.'matieres_notes.id_groupe = '.$prefix_base.'groupes.id AND '.$prefix_base.'matieres.categorie_id='.$prefix_base.'matieres_categories.id ORDER BY '.$prefix_base.'matieres_categories.id ASC'),0);
		//login de l'�l�ve
		$login_eleve_select = $ident_eleve[$cpt_info_eleve]; // login de l'�l�ve
		// mise � 0 des totals coef
		$total_coef='0';
		while ($donner_toute_matier = mysql_fetch_array($requete_toute_matier))
		 {
			$id_groupe_aff=$donner_toute_matier['id_groupe'];

			// ses mati�res
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['matiere'] = $donner_toute_matier[9]; // nom long de la mati�re je ne peut utilise le nom_complet car il est d�jas utiliser avec les cat�gorie
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['id_groupe'] = $donner_toute_matier['id_groupe']; // id du groupe
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['categorie'] = $donner_toute_matier['nom_complet']; // nom de la cat�gorie de la mati�re
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['moy_eleve'] = $donner_toute_matier['note']; // moyenne de l'�l�ve pour une mati�re donn�e dans une p�riodes donn�es
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['rang'] = $donner_toute_matier['rang']; // rang de l'�l�ve pour une mati�re donn�e dans une p�riodes donn�es
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['affiche_moyenne'] = $donner_toute_matier['affiche_moyenne']; // afficher ou ne pas afficher la moyenne de la cat�gorie

			// calcule du nombre d'�l�ve fesant partie de ce groupe
			if(empty($nb_eleve_groupe[$id_groupe_aff])) { $nb_eleve_groupe[$id_groupe_aff]= mysql_result(mysql_query('SELECT count(*) FROM '.$prefix_base.'j_eleves_groupes WHERE periode="'.$id_periode.'" AND id_groupe="'.$id_groupe_aff.'"'),0); }
			$matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['nb_eleve_rang'] = $nb_eleve_groupe[$id_groupe_aff];

			//calcule des moyennes du groupe
			 $groupe_matiere = $donner_toute_matier['id_groupe']; // id du groupe de la mati�re s�lectionn�
			 $moyenne_general_groupe[$groupe_matiere] = calcul_toute_moyenne_classe ($groupe_matiere, $id_periode); // on r�cup�re les donnes le tableau des moyennes moyenne_classe/min/max d'un groupe/nombre de note
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['moy_classe'] = $moyenne_general_groupe[$groupe_matiere][0]; // moyenne du groupe
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['moy_min'] = $moyenne_general_groupe[$groupe_matiere][1]; // moyenne minimal du groupe
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['moy_max'] = $moyenne_general_groupe[$groupe_matiere][2]; //moyenne maximal du groupe

			//calcule du nombre de note dans une p�riode donner pour un groupe
			if($active_nombre_note==='1') {
			  $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['nb_notes_matiere']=mysql_result(mysql_query('SELECT count(*) FROM cn_notes_devoirs nd, cn_devoirs d, cn_cahier_notes cn WHERE (nd.login = "'.$login_eleve_select.'" and nd.id_devoir = d.id and d.display_parents="1" and cn.id_groupe = "'.$id_groupe_aff.'" and d.id_racine = cn.id_cahier_notes AND cn.periode="1")'),0);
			 }

			// autre requete pour rechercher les professeur responsable de la mati�re s�lectionn�
			$call_profs = mysql_query('SELECT u.login FROM '.$prefix_base.'utilisateurs u, '.$prefix_base.'j_groupes_professeurs j WHERE ( u.login = j.login and j.id_groupe="'.$id_groupe_aff.'") ORDER BY j.ordre_prof');
			$nombre_profs = mysql_num_rows($call_profs);
			$k = 0;
			while ($k < $nombre_profs) {
			        $current_matiere_professeur_login[$k] = mysql_result($call_profs, $k, "login");
			        $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['prof'][$k]=affiche_utilisateur($current_matiere_professeur_login[$k],$id_classe);
			        $k++;
			}

			// autre requete pour rechercher les appr�ciation d'une mati�re pour une p�riode donn�
			 $appreciation = mysql_fetch_array(mysql_query('SELECT * FROM '.$prefix_base.'matieres_appreciations WHERE login="'.$login_eleve_select.'" AND id_groupe="'.$groupe_matiere.'" AND periode="'.$id_periode.'"'));
	                 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['appreciation'] = $appreciation['appreciation'];
			 $appreciation=''; //remise � vide de la variable

			// connaitre le coefficient de la mati�re
			 $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['coef']='1';
			 if(empty($coef_matiere[$id_classe][$groupe_matiere]['coef'])) // si on le connait on ne retourne pas le chercher
			 {
			 	$coef_matiere[$id_classe][$groupe_matiere] = mysql_fetch_array(mysql_query('SELECT * FROM '.$prefix_base.'j_groupes_classes WHERE id_classe="'.$id_classe.'" AND id_groupe="'.$groupe_matiere.'"'));
				if($coef_matiere[$id_classe][$groupe_matiere]['coef']!=0.0)
				{
	                 	     $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['coef'] = $coef_matiere[$id_classe][$groupe_matiere]['coef'];
				} else { $coef_matiere[$id_classe][$groupe_matiere]['coef'] = '1'; $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['coef'] = '1'; }
			 } else { $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['coef'] = $coef_matiere[$id_classe][$groupe_matiere]['coef']; }
			 $total_coef = $total_coef+$matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['coef'];

			//calcule des moyennes par cat�gorie
			if($active_entete_regroupement==='1') {
			 $categorie_passage = $matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['categorie'];
			  if ( !isset($matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_eleve']) ) { $matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_eleve'] = '0'; }
			 $matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_eleve']=$matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_eleve']+$matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['moy_eleve']*$coef_matiere[$id_classe][$groupe_matiere]['coef'];
//			 $matiere[$login_eleve_select][$id_periode][$categorie_passage][nb_moy_eleve]=$matiere[$login_eleve_select][$id_periode][$categorie_passage][nb_moy_eleve]+1;
			 if(empty($matiere[$login_eleve_select][$id_periode][$categorie_passage]['coef_tt_catego'])) { $matiere[$login_eleve_select][$id_periode][$categorie_passage]['coef_tt_catego'] = 0; }
			 $matiere[$login_eleve_select][$id_periode][$categorie_passage]['coef_tt_catego']=$matiere[$login_eleve_select][$id_periode][$categorie_passage]['coef_tt_catego']+$coef_matiere[$id_classe][$groupe_matiere]['coef'];
			  if ( !isset($matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_classe']) ) { $matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_classe'] = '0'; }
			 $matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_classe']=$matiere[$login_eleve_select][$id_periode][$categorie_passage]['moy_classe']+$matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['moy_classe']*$coef_matiere[$id_classe][$groupe_matiere]['coef'];
//			 $matiere[$login_eleve_select][$id_periode][$categorie_passage][nb_moy_classe]=$matiere[$login_eleve_select][$id_periode][$categorie_passage][nb_moy_classe]+1;
			}

			//total pour la moyenne g�n�ral
			$moy_general_eleve = $moy_general_eleve+$matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['moy_eleve']*$matiere[$login_eleve_select][$id_periode][$cpt_info_eleve_matiere]['coef'];

			// gestion des graphique de niveau par mati�re
			if ($active_graphique_niveau==='1' and empty($data_grap[$id_periode][$id_groupe_aff][0])) {
                                $data_grap[$id_periode][$id_groupe_aff][0] = sql_query1("SELECT COUNT( note ) as quartile1 FROM matieres_notes WHERE (periode='".$id_periode."' AND id_groupe='".$id_groupe_aff."' AND statut ='' AND note>=15)");
                                $data_grap[$id_periode][$id_groupe_aff][1] = sql_query1("SELECT COUNT( note ) as quartile2 FROM matieres_notes WHERE (periode='".$id_periode."' AND id_groupe='".$id_groupe_aff."' AND statut ='' AND note>=12 AND note<15)");
                                $data_grap[$id_periode][$id_groupe_aff][2] = sql_query1("SELECT COUNT( note ) as quartile3 FROM matieres_notes WHERE (periode='".$id_periode."' AND id_groupe='".$id_groupe_aff."' AND statut ='' AND note>=10 AND note<12)");
                                $data_grap[$id_periode][$id_groupe_aff][3] = sql_query1("SELECT COUNT( note ) as quartile4 FROM matieres_notes WHERE (periode='".$id_periode."' AND id_groupe='".$id_groupe_aff."' AND statut ='' AND note>=8 AND note<10)");
                                $data_grap[$id_periode][$id_groupe_aff][4] = sql_query1("SELECT COUNT( note ) as quartile5 FROM matieres_notes WHERE (periode='".$id_periode."' AND id_groupe='".$id_groupe_aff."' AND statut ='' AND note>=5 AND note<8)");
                                $data_grap[$id_periode][$id_groupe_aff][5] = sql_query1("SELECT COUNT( note ) as quartile6 FROM matieres_notes WHERE (periode='".$id_periode."' AND id_groupe='".$id_groupe_aff."' AND statut ='' AND note<5)");
                            }

			//on cherche s'il faut affiche des sous mati�re pour cette mati�re
			$test_cn = mysql_query('SELECT cnc.note, cnc.statut, c.nom_court, c.id from '.$prefix_base.'cn_cahier_notes cn, '.$prefix_base.'cn_conteneurs c, '.$prefix_base.'cn_notes_conteneurs cnc WHERE cnc.login="'.$login_eleve_select.'" AND cn.periode="'.$id_periode.'" AND cn.id_groupe="'.$id_groupe_aff.'" AND cn.id_cahier_notes = c.id_racine AND c.id_racine!=c.id AND c.display_bulletin="1" AND cnc.id_conteneur=c.id');
		        $nb_ligne_cn = mysql_num_rows($test_cn);
		        $n = 0;
			 $sous_matiere[$login_eleve_select][$id_periode][$id_groupe_aff]['nb']=$nb_ligne_cn;
		        while ($n < $nb_ligne_cn) {
			 $sous_matiere[$login_eleve_select][$id_periode][$id_groupe_aff][$n]['titre']=mysql_result($test_cn, $n, 'c.nom_court');
			 $sous_matiere[$login_eleve_select][$id_periode][$id_groupe_aff][$n]['moyenne']=mysql_result($test_cn, $n, 'cnc.note');
	                 $n++;
        	        }

		 $cpt_info_eleve_matiere=$cpt_info_eleve_matiere+1;
		 }
		 // attribue le nombre de mati�re pour un �l�ve donner et une p�riode
		 $info_bulletin[$login_eleve_select][$id_periode]['nb_matiere']=$nombre_de_matiere;
		 //calcule de la moyenne g�n�ral de l'�l�ve pour un p�riode donn�es
	 	 // if($nombre_de_matiere!=0) { $info_bulletin[$login_eleve_select][$id_periode][moy_general_eleve] = $moy_general_eleve/$info_bulletin[$login_eleve_select][$id_periode][nb_matiere]; } else { $info_bulletin[$login_eleve_select][$id_periode][moy_general_eleve]=0; }
	 	 if($nombre_de_matiere!=0) { $info_bulletin[$login_eleve_select][$id_periode]['moy_general_eleve'] = $moy_general_eleve/$total_coef; } else { $info_bulletin[$login_eleve_select][$id_periode]['moy_general_eleve']='0'; }
		 $moy_gene_eleve = $info_bulletin[$login_eleve_select][$id_periode]['moy_general_eleve'];

		// gestion des graphique de niveau pour la classe
//		if ($active_graphique_niveau==='1') {
			// initialisation � 0 si vide
			if(empty($data_grap_classe[$id_periode][$id_classe][0])) { $data_grap_classe[$id_periode][$id_classe][0]='0'; }
			if(empty($data_grap_classe[$id_periode][$id_classe][1])) { $data_grap_classe[$id_periode][$id_classe][1]='0'; }
			if(empty($data_grap_classe[$id_periode][$id_classe][2])) { $data_grap_classe[$id_periode][$id_classe][2]='0'; }
			if(empty($data_grap_classe[$id_periode][$id_classe][3])) { $data_grap_classe[$id_periode][$id_classe][3]='0'; }
			if(empty($data_grap_classe[$id_periode][$id_classe][4])) { $data_grap_classe[$id_periode][$id_classe][4]='0'; }
			if(empty($data_grap_classe[$id_periode][$id_classe][5])) { $data_grap_classe[$id_periode][$id_classe][5]='0'; }

			// mini et maxi de la classe
			if(empty($moyenne_classe_minmax[$id_periode][$id_classe]['min'])) { $moyenne_classe_minmax[$id_periode][$id_classe]['min']='20'; }
			if(empty($moyenne_classe_minmax[$id_periode][$id_classe]['max'])) { $moyenne_classe_minmax[$id_periode][$id_classe]['max']='0'; }
			if($moyenne_classe_minmax[$id_periode][$id_classe]['min']>$moy_gene_eleve) { $moyenne_classe_minmax[$id_periode][$id_classe]['min'] = $moy_gene_eleve; }
			if($moyenne_classe_minmax[$id_periode][$id_classe]['max']<$moy_gene_eleve) { $moyenne_classe_minmax[$id_periode][$id_classe]['max'] = $moy_gene_eleve; }

		     if ($moy_gene_eleve >= 15) { $data_grap_classe[$id_periode][$id_classe][0]=$data_grap_classe[$id_periode][$id_classe][0]+1; }
		     else if (($moy_gene_eleve >= 12) and ($moy_gene_eleve < 15)) { $data_grap_classe[$id_periode][$id_classe][1]=$data_grap_classe[$id_periode][$id_classe][1]+1; }
		     else if (($moy_gene_eleve >= 10) and ($moy_gene_eleve < 12)) { $data_grap_classe[$id_periode][$id_classe][2]=$data_grap_classe[$id_periode][$id_classe][2]+1; }
	             else if (($moy_gene_eleve >= 8) and ($moy_gene_eleve < 10)) { $data_grap_classe[$id_periode][$id_classe][3]=$data_grap_classe[$id_periode][$id_classe][3]+1; }
          	     else if (($moy_gene_eleve >= 5) and ($moy_gene_eleve < 8)) { $data_grap_classe[$id_periode][$id_classe][4]=$data_grap_classe[$id_periode][$id_classe][4]+1; }
 	             else { $data_grap_classe[$id_periode][$id_classe][5]=$data_grap_classe[$id_periode][$id_classe][5]+1; }
//                }

		 //avis du conseil de classe pour un �l�ve et une p�riode donn�e
		 $avis_conseil_de_classe = mysql_fetch_array(mysql_query('SELECT * FROM avis_conseil_classe WHERE login="'.$login_eleve_select.'" AND periode="'.$id_periode.'"'));
	         $info_bulletin[$login_eleve_select][$id_periode]['avis_conseil_classe'] = $avis_conseil_de_classe['avis'];
		 $avis_conseil_de_classe=''; //remise � vide de la variable

		//connaitre l'effectif de la classe
		 if(empty($classe_effectif_tab[$id_classe][$id_periode]['effectif'])) // si on le connait on ne retourne pas le chercher
		 {
			$info_bulletin[$login_eleve_select][$id_periode]['effectif'] = mysql_result(mysql_query('SELECT count(*) FROM '.$prefix_base.'j_eleves_classes WHERE id_classe="'.$id_classe.'" AND periode="'.$id_periode.'"'),0);
			$classe_effectif_tab[$id_classe][$id_periode]['effectif'] = $info_bulletin[$login_eleve_select][$id_periode]['effectif'];
		 } else { $info_bulletin[$login_eleve_select][$id_periode]['effectif'] = $classe_effectif_tab[$id_classe][$id_periode]['effectif']; }

		// rang de l'�l�ve dans la classe
		 $rang_eleve_classe_requete = mysql_query('SELECT rang FROM '.$prefix_base.'j_eleves_classes WHERE periode="'.$id_periode.'" AND id_classe="'.$id_classe.'" AND login="'.$login_eleve_select.'"');
		 $rang_eleve_classe[$login_eleve_select][$id_periode]=@mysql_result($rang_eleve_classe_requete, "0", "rang");
                 if ((isset($rang_eleve_classe[$cpt_i]) and $rang_eleve_classe[$cpt_i] === 0) or (isset($rang_eleve_classe[$cpt_i]) and $rang_eleve_classe[$cpt_i] == -1)) { $rang_eleve_classe[$cpt_i] = "-"; } else { $rang_eleve_classe[$cpt_i] = ''; }

		//absences de l'�l�ve
		 $current_eleve_absences_query = mysql_query('SELECT * FROM absences WHERE (login="'.$login_eleve_select.'" AND periode="'.$id_periode.'")');
	         $info_bulletin[$login_eleve_select][$id_periode]['absences'] = @mysql_result($current_eleve_absences_query, 0, "nb_absences");
	         $info_bulletin[$login_eleve_select][$id_periode]['absences_nj'] = @mysql_result($current_eleve_absences_query, 0, "non_justifie");
	         $info_bulletin[$login_eleve_select][$id_periode]['absences_retards'] = @mysql_result($current_eleve_absences_query, 0, "nb_retards");
	         $info_bulletin[$login_eleve_select][$id_periode]['absences_appreciation'] = @mysql_result($current_eleve_absences_query, 0, "appreciation");
	         if($info_bulletin[$login_eleve_select][$id_periode]['absences'] == '') { $info_bulletin[$login_eleve_select][$id_periode]['absences'] = "?"; }
	         if($info_bulletin[$login_eleve_select][$id_periode]['absences_nj'] == '') { $info_bulletin[$login_eleve_select][$id_periode]['absences_nj'] = "?"; }
	         if($info_bulletin[$login_eleve_select][$id_periode]['absences_retards']=='') { $info_bulletin[$login_eleve_select][$id_periode]['absences_retards'] = "?"; }

		//haut responsable de la classe
		if(empty($info_classe[$id_classe]['nom_hautresponsable']))
		{
		 $calldata = mysql_query('SELECT * FROM '.$prefix_base.'classes WHERE id="'.$id_classe.'"');
		 $info_classe[$id_classe]['fonction_hautresponsable']=mysql_result($calldata, 0, "formule");
		 $info_classe[$id_classe]['nom_hautresponsable']= @mysql_result($calldata, 0, "suivi_par");
		}

	$cpt_info_periode=$cpt_info_periode+1;
	}
$cpt_info_eleve=$cpt_info_eleve+1;
}
	// d�finition d'une variable
	$hauteur_pris = 0;

// d�but de la g�n�ration du fichier PDF
$pdf=new bul_PDF('p', 'mm', 'A4'); //cr�ation du PDF en mode Portrait, unit�e de mesure en mm, de taille A4
$nb_eleve_aff = 1;
$categorie_passe = '';
$categorie_passe_count = 0;
if(empty($gepiSchoolName)) { $gepiSchoolName=getSettingValue('gepiSchoolName'); }
$pdf->SetCreator($gepiSchoolName);
$pdf->SetAuthor($gepiSchoolName);
$pdf->SetKeywords('');
$pdf->SetSubject('Bulletin');
$pdf->SetTitle('Bulletin');
$pdf->SetDisplayMode('fullwidth', 'single');
$pdf->SetCompression(TRUE);
$pdf->SetAutoPageBreak(TRUE, 5);

$responsable_place = 0;

while(!empty($nom_eleve[$nb_eleve_aff])) {
  $ident_eleve_aff = $ident_eleve[$nb_eleve_aff];
    $cpt_info_periode=0;
	$id_classe_selection = $classe_id[$nb_eleve_aff]; // classe de l'�l�ve

	$total_moyenne_classe_en_calcul=0; $total_moyenne_min_en_calcul=0; $total_moyenne_max_en_calcul=0; $total_coef_en_calcul=0;

	//boucle pour chaque p�riode d'un �l�ve
	while(!empty($periode_classe[$id_classe_selection][$cpt_info_periode]))
	 {

	$pdf->AddPage(); //ajout d'une page au document
	$i = $nb_eleve_aff;
	$id_periode = $periode_classe[$id_classe_selection][$cpt_info_periode];
	$pdf->SetFont('Arial','B',12);

	// gestion des styles
	$pdf->SetStyle("b","arial","B",8,"0,0,0");
	$pdf->SetStyle("i","arial","I",8,"0,0,0");
	$pdf->SetStyle("u","arial","U",8,"0,0,0");

	// bloc affichage de l'adresse des parents
	if($active_bloc_adresse_parent==='1') {
 	 $pdf->SetXY($X_parent,$Y_parent);
	 $texte_1_responsable = $civilite_parents[$ident_eleve_aff][$responsable_place]." ".$nom_parents[$ident_eleve_aff][$responsable_place]." ".$prenom_parents[$ident_eleve_aff][$responsable_place];
	 $texte_1_responsable = trim($texte_1_responsable);
		$hauteur_caractere=12;
		$pdf->SetFont($caractere_utilse,'B',$hauteur_caractere);		
		$val = $pdf->GetStringWidth($texte_1_responsable);
		$taille_texte = 90;
		$grandeur_texte='test';
		while($grandeur_texte!='ok') {
		 if($taille_texte<$val) 
		  {
		     $hauteur_caractere = $hauteur_caractere-0.3;
		     $pdf->SetFont($caractere_utilse,'B',$hauteur_caractere);
		     $val = $pdf->GetStringWidth($texte_1_responsable);
		  } else { $grandeur_texte='ok'; }
        	}
	 $pdf->Cell(90,7, $texte_1_responsable,0,2,'');
	 $texte_1_responsable = $adresse1_parents[$ident_eleve_aff][$responsable_place];
		$hauteur_caractere=10;
		$pdf->SetFont($caractere_utilse,'',$hauteur_caractere);		
		$val = $pdf->GetStringWidth($texte_1_responsable);
		$taille_texte = 90;
		$grandeur_texte='test';
		while($grandeur_texte!='ok') {
		 if($taille_texte<$val) 
		  {
		     $hauteur_caractere = $hauteur_caractere-0.3;
		     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere);
		     $val = $pdf->GetStringWidth($texte_1_responsable);
		  } else { $grandeur_texte='ok'; }
        	}
	 $pdf->Cell(90,5, $texte_1_responsable,0,2,'');
	 $texte_1_responsable = $adresse2_parents[$ident_eleve_aff][$responsable_place];
		$hauteur_caractere=10;
		$pdf->SetFont($caractere_utilse,'',$hauteur_caractere);		
		$val = $pdf->GetStringWidth($texte_1_responsable);
		$taille_texte = 90;
		$grandeur_texte='test';
		while($grandeur_texte!='ok') {
		 if($taille_texte<$val) 
		  {
		     $hauteur_caractere = $hauteur_caractere-0.3;
		     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere);
		     $val = $pdf->GetStringWidth($texte_1_responsable);
		  } else { $grandeur_texte='ok'; }
        	}
	 $pdf->Cell(90,5, $texte_1_responsable,0,2,'');
	 $pdf->Cell(90,5, '',0,2,'');
	 $texte_1_responsable = $cp_parents[$ident_eleve_aff][$responsable_place]." ".$ville_parents[$ident_eleve_aff][$responsable_place];
		$hauteur_caractere=10;
		$pdf->SetFont($caractere_utilse,'',$hauteur_caractere);		
		$val = $pdf->GetStringWidth($texte_1_responsable);
		$taille_texte = 90;
		$grandeur_texte='test';
		while($grandeur_texte!='ok') {
		 if($taille_texte<$val) 
		  {
		     $hauteur_caractere = $hauteur_caractere-0.3;
		     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere);
		     $val = $pdf->GetStringWidth($texte_1_responsable);
		  } else { $grandeur_texte='ok'; }
        	}
	 $pdf->Cell(90,5, $texte_1_responsable,0,2,'');
	 $texte_1_responsable = '';
	}


	// bloc affichage information sur l'�l�ves
	if($active_bloc_eleve==='1') {
 	 $pdf->SetXY($X_eleve,$Y_eleve);
 	 $pdf->SetFont($caractere_utilse,'B',14);
	 $longeur_cadre_eleve = $pdf->GetStringWidth($nom_eleve[$i]." ".$prenom_eleve[$i]);
	 $rajout_cadre_eleve = 100-$longeur_cadre_eleve;
	 $longeur_cadre_eleve = $longeur_cadre_eleve + $rajout_cadre_eleve;
	 $nb_ligne = 5; $hauteur_ligne = 6;
	 $hauteur_cadre_eleve = $nb_ligne*$hauteur_ligne;
	 if($cadre_eleve!=0) { $pdf->Rect($X_eleve, $Y_eleve, $longeur_cadre_eleve, $hauteur_cadre_eleve, 'D'); }
	 $X_eleve_2=$X_eleve; $Y_eleve_2=$Y_eleve;

		//photo de l'�l�ve
	 	if($active_photo==='1' and $photo[$i]!='' and file_exists($photo[$i])) {
		 $L_photo_max=$hauteur_cadre_eleve*2.8; $H_photo_max=$hauteur_cadre_eleve*2.8;
		 $valeur=redimensionne_image($photo[$i], $L_photo_max, $H_photo_max);
		 $X_photo=$X_eleve+0.20; $Y_photo=$Y_eleve+0.25; $L_photo=$valeur[0]; $H_photo=$valeur[1];
		 $X_eleve_2=$X_eleve+$L_photo; $Y_eleve_2=$Y_photo;
	         $pdf->Image($photo[$i], $X_photo, $Y_photo, $L_photo, $H_photo);
		}

 	 $pdf->SetXY($X_eleve_2,$Y_eleve_2);
	 $pdf->Cell(90,7, $nom_eleve[$i]." ".$prenom_eleve[$i],0,2,'');
	 $pdf->SetFont($caractere_utilse,'',10);
	 if($affiche_date_naissance==='1') {
	  if($date_naissance[$i]!="") { $pdf->Cell(90,5, $date_naissance[$i],0,2,''); }
	 }
	 if($affiche_dp==='1') {
	  if($dp[$i]!="") { $pdf->Cell(90,4, $dp[$i],0,2,''); }
	 }
	 if($affiche_doublement==='1') {
	  if($doublement[$i]!="") { $pdf->Cell(90,4.5, $doublement[$i],0,2,''); }
	 }
	 if($affiche_nom_court==='1') {
	  if($classe_nomcour[$i]!="") { $pdf->Cell(90,4.5, unhtmlentities($classe_nomcour[$i]),0,2,''); }
	 }
	 if($affiche_effectif_classe==='1') {
	  if($info_bulletin[$ident_eleve_aff][$id_periode]['effectif']!="") { $pdf->Cell(45,4.5, 'Effectif : '.$info_bulletin[$ident_eleve_aff][$id_periode]['effectif'].' �l�ves',0,0,''); }
	 }
	 if($affiche_numero_impression==='1') {
	  $num_ordre = $i;
	  $pdf->Cell(45,4, 'Bulletin N� '.$num_ordre,0,2,'');
	 }
	 if($affiche_etab_origine==='1' and !empty($etablissement_origine[$i]) ) {
 	 $pdf->SetX($X_eleve_2);
	  $pdf->Cell(90,4, 'Etab. origine : '.$etablissement_origine[$i],0,2,'');
	 }
	}

	// bloc affichage datation du bulletin
	if($active_bloc_datation==='1') {
 	 $pdf->SetXY($X_datation_bul, $Y_datation_bul);
 	 $pdf->SetFont($caractere_utilse,'B',14);
	 $longeur_cadre_datation_bul = 95;
	 $nb_ligne_datation_bul = 3; $hauteur_ligne_datation_bul = 6;
	 $hauteur_cadre_datation_bul = $nb_ligne_datation_bul*$hauteur_ligne_datation_bul;
	 if($cadre_datation_bul!=0) { $pdf->Rect($X_datation_bul, $Y_datation_bul, $longeur_cadre_datation_bul, $hauteur_cadre_datation_bul, 'D'); }
	 $pdf->Cell(90,7, "Classe de ".unhtmlentities($classe_nomlong[$i]),0,2,'C');
	 $pdf->SetFont($caractere_utilse,'',12);
	 $pdf->Cell(90,5, "Ann�e scolaire ".$annee_scolaire,0,2,'C');
	 $pdf->SetFont($caractere_utilse,'',10);
		//conna�tre le nom de la p�riode
		if(empty($nom_periode[$id_classe_selection][$id_periode])) {
		 $requete_periode = mysql_query('SELECT * FROM '.$prefix_base.'periodes WHERE id_classe="'.$id_classe_selection.'" AND num_periode="'.$id_periode.'"');
 		 $nom_periode[$id_classe_selection][$id_periode] = @mysql_result($requete_periode, '0', 'nom_periode');
		}
	 $pdf->Cell(90,5, "Bulletin du ".unhtmlentities($nom_periode[$id_classe_selection][$id_periode]),0,2,'C');
	 $pdf->SetFont($caractere_utilse,'',8);
	 $pdf->Cell(95,7, $date_bulletin,0,2,'R');
	 $pdf->SetFont($caractere_utilse,'',10);
	}

	// bloc note et appr�ciation
	 //nombre de matiere � afficher
	  $nb_matiere = $info_bulletin[$ident_eleve_aff][$id_periode]['nb_matiere'];
	if($active_bloc_note_appreciation==='1' and $nb_matiere!='0') {
	$pdf->Rect($X_note_app, $Y_note_app, $longeur_note_app, $hauteur_note_app, 'D');
		//ent�te du tableau des note et app
		$nb_entete_moyenne = $active_moyenne_eleve+$active_moyenne_classe+$active_moyenne_min+$active_moyenne_max; //min max classe eleve
		$hauteur_entete = 8;
		$hauteur_entete_pardeux = $hauteur_entete/2;
	 	 $pdf->SetXY($X_note_app, $Y_note_app);
	 	 $pdf->SetFont($caractere_utilse,'',10);
		 $largeur_matiere = 40;
		 $pdf->Cell($largeur_matiere, $hauteur_entete, $titre_entete_matiere,1,0,'C');
		 $largeur_utilise = $largeur_matiere;

		// co�fficient mati�re
		if($active_coef_moyenne==='1') {
	 	  $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
	 	  $pdf->SetFont($caractere_utilse,'',8);
		  $pdf->Cell($largeur_coef_moyenne, $hauteur_entete, $titre_entete_coef,'LRB',0,'C');
		  $largeur_utilise = $largeur_utilise + $largeur_coef_moyenne;
		}

		// nombre de note
		if($active_nombre_note_case==='1') {
	 	  $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
	 	  $pdf->SetFont($caractere_utilse,'',8);
		  $pdf->Cell($largeur_nombre_note, $hauteur_entete, $titre_entete_nbnote,'LRB',0,'C');
		  $largeur_utilise = $largeur_utilise + $largeur_nombre_note;
		}

// eleve | min | classe | max | rang | niveau | appreciation | 
if ( $ordre_entete_model_bulletin === '1' ) {
	$ordre_moyenne[0] = 'eleve';
	$ordre_moyenne[1] = 'min';
	$ordre_moyenne[2] = 'classe';
	$ordre_moyenne[3] = 'max';
	$ordre_moyenne[4] = 'rang';
	$ordre_moyenne[5] = 'niveau';
	$ordre_moyenne[6] = 'appreciation';
}

// min | classe | max | eleve | niveau | rang | appreciation | 
if ( $ordre_entete_model_bulletin === '2' ) {
	$ordre_moyenne[0] = 'min';
	$ordre_moyenne[1] = 'classe';
	$ordre_moyenne[2] = 'max';
	$ordre_moyenne[3] = 'eleve';
	$ordre_moyenne[4] = 'niveau';
	$ordre_moyenne[5] = 'rang';
	$ordre_moyenne[6] = 'appreciation';
}


// eleve | niveau | rang | appreciation | min | classe | max
if ( $ordre_entete_model_bulletin === '3' ) {
	$ordre_moyenne[0] = 'eleve';
	$ordre_moyenne[1] = 'niveau';
	$ordre_moyenne[2] = 'rang';
	$ordre_moyenne[3] = 'appreciation';
	$ordre_moyenne[4] = 'min';
	$ordre_moyenne[5] = 'classe';
	$ordre_moyenne[6] = 'max';
}

// eleve | classe | min | max | rang | niveau | appreciation | 
if ( $ordre_entete_model_bulletin === '4' ) {
	$ordre_moyenne[0] = 'eleve';
	$ordre_moyenne[1] = 'classe';
	$ordre_moyenne[2] = 'min';
	$ordre_moyenne[3] = 'max';
	$ordre_moyenne[4] = 'rang';
	$ordre_moyenne[5] = 'niveau';
	$ordre_moyenne[6] = 'appreciation';
}

// eleve | min | classe | max | niveau | rang | appreciation | 
if ( $ordre_entete_model_bulletin === '5' ) {
	$ordre_moyenne[0] = 'eleve';
	$ordre_moyenne[1] = 'min';
	$ordre_moyenne[2] = 'classe';
	$ordre_moyenne[3] = 'max';
	$ordre_moyenne[4] = 'niveau';
	$ordre_moyenne[5] = 'rang';
	$ordre_moyenne[6] = 'appreciation';
}

// min | classe | max | eleve | rang | niveau | appreciation | 
if ( $ordre_entete_model_bulletin === '6' ) {
	$ordre_moyenne[0] = 'min';
	$ordre_moyenne[1] = 'classe';
	$ordre_moyenne[2] = 'max';
	$ordre_moyenne[3] = 'eleve';
	$ordre_moyenne[4] = 'rang';
	$ordre_moyenne[5] = 'niveau';
	$ordre_moyenne[6] = 'appreciation';
}


// les moyennes eleve, classe, min, max
		if( $active_moyenne==='1') {
/*
	 	 $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
		 $largeur_moyenne = $largeur_d_une_moyenne * $nb_entete_moyenne;
 		 $text_entete_moyenne = 'Moyenne';
			if ( $type_bulletin === '2' and $active_moyenne_eleve === '1' and $nb_entete_moyenne > 1 )
			{ 
				$largeur_moyenne = $largeur_d_une_moyenne * ( $nb_entete_moyenne - 1 );
				$text_entete_moyenne = 'Pour la classe';
				if ( $ordre_moyenne[0] === 'eleve' ) {
				 	$pdf->SetXY($X_note_app+$largeur_utilise+$largeur_d_une_moyenne, $Y_note_app);
				}
				if ( $ordre_moyenne[0] != 'eleve' ) {
				 	$pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
				}
			}
		 $pdf->Cell($largeur_moyenne, $hauteur_entete_pardeux, $text_entete_moyenne,1,0,'C');
	 	 $pdf->SetFont($caractere_utilse,'',9);
		    $largeur_d_une_moyenne = $largeur_moyenne / $nb_entete_moyenne;
			    if ( $type_bulletin === '2' and $active_moyenne_eleve === '1' and $nb_entete_moyenne > 1 )
			    {
				    $largeur_d_une_moyenne = $largeur_moyenne / ( $nb_entete_moyenne - 1 );
			    }*/
		}

$cpt_ordre = 0;
$chapeau_moyenne = 'non';
while ( !empty($ordre_moyenne[$cpt_ordre]) ) {
		
		// le chapeau des moyennes
			$ajout_espace_au_dessus = 4;
			if ( $entete_model_bulletin === '1' and $nb_entete_moyenne > 1 and ( $ordre_moyenne[$cpt_ordre] === 'classe' or $ordre_moyenne[$cpt_ordre] === 'min' or $ordre_moyenne[$cpt_ordre] === 'max' or $ordre_moyenne[$cpt_ordre] === 'eleve' ) and $chapeau_moyenne === 'non' and $ordre_entete_model_bulletin != '3' )
			{
				$largeur_moyenne = $largeur_d_une_moyenne * $nb_entete_moyenne;
				$text_entete_moyenne = 'Moyenne';
			 	$pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
				 $pdf->Cell($largeur_moyenne, $hauteur_entete_pardeux, $text_entete_moyenne,1,0,'C');
				$chapeau_moyenne = 'oui';
			}   

			if ( ($entete_model_bulletin === '2' and $nb_entete_moyenne > 1 and ( $ordre_moyenne[$cpt_ordre] === 'classe' or $ordre_moyenne[$cpt_ordre] === 'min' or $ordre_moyenne[$cpt_ordre] === 'max' ) and $chapeau_moyenne === 'non' ) or ( $entete_model_bulletin === '1' and $ordre_entete_model_bulletin === '3' and $chapeau_moyenne === 'non' and ( $ordre_moyenne[$cpt_ordre] === 'classe' or $ordre_moyenne[$cpt_ordre] === 'min' or $ordre_moyenne[$cpt_ordre] === 'max' )  ) )
			{
				$largeur_moyenne = $largeur_d_une_moyenne * ( $nb_entete_moyenne - 1 );
				$text_entete_moyenne = 'Pour la classe';
			 	$pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
				 $pdf->Cell($largeur_moyenne, $hauteur_entete_pardeux, $text_entete_moyenne,1,0,'C');
				$chapeau_moyenne = 'oui';
			}

		    //eleve
		    if($active_moyenne_eleve==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'eleve' ) {
			$ajout_espace_au_dessus = 4;
			$hauteur_de_la_cellule = $hauteur_entete_pardeux;
			    if ( $entete_model_bulletin === '2' and $active_moyenne_eleve === '1' and $nb_entete_moyenne > 1 )
			    {
				$hauteur_de_la_cellule = $hauteur_entete;
				$ajout_espace_au_dessus = 0;
			    }
		     $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app+$ajout_espace_au_dessus);
		     $pdf->SetFillColor($couleur_reperage_eleve1, $couleur_reperage_eleve2, $couleur_reperage_eleve3);
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_de_la_cellule, "El�ve",1,0,'C',$active_reperage_eleve);
		     $pdf->SetFillColor(0, 0, 0);
		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
		    }

		    //classe
		    if($active_moyenne_classe==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'classe' ) {
		     $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app+4);
			     $hauteur_caractere = '8.5';
			     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere);
			$text_moy_classe = 'Classe';
			if ( $entete_model_bulletin === '2' ) { $text_moy_classe = 'Moy.'; }
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_pardeux, $text_moy_classe,1,0,'C');
		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
	  	    }
		    //min
		    if($active_moyenne_min==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'min' ) {
		     $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app+4);
			     $hauteur_caractere = '8.5';
			     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere);
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_pardeux, "Min.",1,0,'C');
		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
		    }
		    //max
		    if($active_moyenne_max==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'max' ) {
		     $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app+4);
			     $hauteur_caractere = '8.5';
			     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere);
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_pardeux, "Max.",1,0,'C');
		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
		    }
		

	 	 $pdf->SetFont($caractere_utilse,'',10);

		// rang de l'�l�ve
		if( $active_rang === '1' and $ordre_moyenne[$cpt_ordre] === 'rang' ) {
	 	  $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
	 	  $pdf->SetFont($caractere_utilse,'',8);
		  $pdf->Cell($largeur_rang, $hauteur_entete, $titre_entete_rang,'LRB',0,'C');
		  $largeur_utilise = $largeur_utilise + $largeur_rang;
		}

		// graphique de nvieau
		 if( $active_graphique_niveau === '1' and $ordre_moyenne[$cpt_ordre] === 'niveau' ) {
	 	  $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
		  $pdf->Cell($largeur_niveau, $hauteur_entete_pardeux, "Niveau",'LR',0,'C');
	 	  $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app+4);
	 	  $pdf->SetFont($caractere_utilse,'',8);
		  $pdf->Cell($largeur_niveau, $hauteur_entete_pardeux, "ABC+C-DE",'LRB',0,'C');
		  $largeur_utilise = $largeur_utilise+$largeur_niveau;
		 }

		//appreciation
		 if($active_appreciation==='1' and $ordre_moyenne[$cpt_ordre] === 'appreciation' ) {
	 	  $pdf->SetXY($X_note_app+$largeur_utilise, $Y_note_app);
			if ( !empty($ordre_moyenne[$cpt_ordre+1]) ) { 
				$cpt_ordre_sous = $cpt_ordre + 1;
				$largeur_appret = 0;
				while ( !empty($ordre_moyenne[$cpt_ordre_sous]) ) {
					if ( $ordre_moyenne[$cpt_ordre_sous] === 'eleve' ) { $largeur_appret = $largeur_appret + $largeur_d_une_moyenne; }
					if ( $ordre_moyenne[$cpt_ordre_sous] === 'rang' ) { $largeur_appret = $largeur_appret + $largeur_rang; }
					if ( $ordre_moyenne[$cpt_ordre_sous] === 'niveau' ) { $largeur_appret = $largeur_appret + $largeur_niveau; }
					if ( $ordre_moyenne[$cpt_ordre_sous] === 'min' ) { $largeur_appret = $largeur_appret + $largeur_d_une_moyenne; }
					if ( $ordre_moyenne[$cpt_ordre_sous] === 'classe' ) { $largeur_appret = $largeur_appret + $largeur_d_une_moyenne; }
					if ( $ordre_moyenne[$cpt_ordre_sous] === 'max' ) { $largeur_appret = $largeur_appret + $largeur_d_une_moyenne; }
				$cpt_ordre_sous = $cpt_ordre_sous + 1;
				}
				$largeur_appreciation = $longeur_note_app - $largeur_utilise - $largeur_appret;
		} else { $largeur_appreciation = $longeur_note_app-$largeur_utilise; }
	 	  $pdf->SetFont($caractere_utilse,'',10);
		  $pdf->Cell($largeur_appreciation, $hauteur_entete, $titre_entete_appreciation,'LRB',0,'C');
		     $largeur_utilise = $largeur_utilise + $largeur_appreciation;
		 }
$cpt_ordre = $cpt_ordre + 1;
}
		  $largeur_utilise = 0;
// fin de boucle d'ordre

		//emplacement des blocs mati�re et note et appr�ciation

			//si cat�gorie activ� il faut conmpte le nombre de cat�gorie
			$nb_categories_select=0; $categorie_passe_for='';

			for($x=0;$x<$nb_matiere;$x++)
			  {
 		             if($matiere[$ident_eleve_aff][$id_periode][$x]['categorie']!=$categorie_passe_for) { $nb_categories_select=$nb_categories_select+1; }
			     $categorie_passe_for=$matiere[$ident_eleve_aff][$id_periode][$x]['categorie'];
			  }

 		 $X_bloc_matiere=$X_note_app; $Y_bloc_matiere=$Y_note_app+$hauteur_entete; $longeur_bloc_matiere=$longeur_note_app;
		 if($active_moyenne==='1') { $hauteur_toute_entete=$hauteur_entete+$hauteur_entete_moyenne_general; } else { $hauteur_toute_entete=$hauteur_entete; }
		 $hauteur_bloc_matiere=$hauteur_note_app-$hauteur_toute_entete;
		 $X_note_moy_app = $X_note_app; $Y_note_moy_app = $Y_note_app+$hauteur_note_app-$hauteur_entete;
	         if($active_entete_regroupement==='1') {
		  $espace_entre_matier = ($hauteur_bloc_matiere-($nb_categories_select*5))/$nb_matiere;
		 } else { $espace_entre_matier = $hauteur_bloc_matiere/$nb_matiere; }
	 	 $pdf->SetXY($X_bloc_matiere, $Y_bloc_matiere);
		 $Y_decal = $Y_bloc_matiere;
		 for($m=0; $m<$nb_matiere; $m++)
		  {
		 	$pdf->SetXY($X_bloc_matiere, $Y_decal);

			// si on affiche les cat�gorie
			if($active_entete_regroupement==='1') {
				//si on affiche les moyenne des cat�gorie
				if($matiere[$ident_eleve_aff][$id_periode][$m]['categorie']!=$categorie_passe)
 				{
					$pdf->SetFont($caractere_utilse,'',10);
					$pdf->SetFillColor($couleur_categorie_entete1, $couleur_categorie_entete2, $couleur_categorie_entete3);
					$pdf->Cell($largeur_matiere, $hauteur_info_categorie, unhtmlentities($matiere[$ident_eleve_aff][$id_periode][$m]['categorie']),'TLB',0,'L',$couleur_categorie_entete);
					$largeur_utilise = $largeur_matiere;

					// co�fficient mati�re
					if($active_coef_moyenne==='1') {
			 	 	  $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
				 	  $pdf->SetFont($caractere_utilse,'',10);
					  $pdf->Cell($largeur_coef_moyenne, $hauteur_info_categorie, '','T',0,'C',$couleur_categorie_entete);
					  $largeur_utilise = $largeur_utilise+$largeur_coef_moyenne;
					}

					// nombre de note
					if($active_nombre_note_case==='1') {
			 	 	  $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
				 	  $pdf->SetFont($caractere_utilse,'',10);
					  $pdf->Cell($largeur_nombre_note, $hauteur_info_categorie, '','T',0,'C',$couleur_categorie_entete);
					  $largeur_utilise = $largeur_utilise+$largeur_nombre_note;
					}
					$pdf->SetFillColor(0, 0, 0);

					// les moyennes eleve, classe, min, max par cat�gorie
				 	   $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
$cpt_ordre = 0;
$chapeau_moyenne = 'non';
while ( !empty($ordre_moyenne[$cpt_ordre]) ) {
				            //eleve
		 			    if($active_moyenne_eleve==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'eleve' ) {
					     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
					     if($active_moyenne_regroupement==='1') {
						$categorie_passage=$matiere[$ident_eleve_aff][$id_periode][$m]['categorie'];
						if($matiere[$ident_eleve_aff][$id_periode][$m]['affiche_moyenne']==='1')
						 {
							$calcule_moyenne_eleve_categorie[$categorie_passage]=$matiere[$ident_eleve_aff][$id_periode][$categorie_passage]['moy_eleve']/$matiere[$ident_eleve_aff][$id_periode][$categorie_passage]['coef_tt_catego'];
					 	        $pdf->SetFont($caractere_utilse,'B',8);
			 			        $pdf->SetFillColor($couleur_reperage_eleve1, $couleur_reperage_eleve2, $couleur_reperage_eleve3);
						        $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, present_nombre($calcule_moyenne_eleve_categorie[$categorie_passage], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),1,0,'C',$active_reperage_eleve);
							$pdf->SetFillColor(0, 0, 0);
						 } else {
				 			        $pdf->SetFillColor(255, 255, 255);
							        $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, '','TL',0,'C',$active_reperage_eleve);
							}
					     } else {
					     	      $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, '','T',0,'C');
					            }
		 			     $largeur_utilise = $largeur_utilise+$largeur_d_une_moyenne;
					    }
				            //classe
		 			    if($active_moyenne_classe==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'classe' ) {
					     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
					     if($active_moyenne_regroupement==='1') {
						$categorie_passage=$matiere[$ident_eleve_aff][$id_periode][$m]['categorie'];
						if($matiere[$ident_eleve_aff][$id_periode][$m]['affiche_moyenne']==='1')
						{
						 $calcule_moyenne_classe_categorie[$categorie_passage]=$matiere[$ident_eleve_aff][$id_periode][$categorie_passage]['moy_classe']/$matiere[$ident_eleve_aff][$id_periode][$categorie_passage]['coef_tt_catego'];
						 $calcule_moyenne_classe_categorie[$categorie_passage]=$calcule_moyenne_classe_categorie[$categorie_passage];
				 	         $pdf->SetFont($caractere_utilse,'',8);
					         $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, present_nombre($calcule_moyenne_classe_categorie[$categorie_passage], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'TLR',0,'C');
						} else {
						         $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, '','T',0,'C');
						       }
					     } else {
					 	      $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, '','T',0,'C');
					            }
		 			     $largeur_utilise = $largeur_utilise+$largeur_d_une_moyenne;
					    }
				 	    $pdf->SetFont($caractere_utilse,'',10);
				            //min
		 			    if($active_moyenne_min==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'min' ) {
					     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
					     $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, '','T',0,'C');
		 			     $largeur_utilise = $largeur_utilise+$largeur_d_une_moyenne;
					    }
				            //max
		 			    if($active_moyenne_max==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'max' ) {
					     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
					     $pdf->Cell($largeur_d_une_moyenne, $hauteur_info_categorie, '','T',0,'C');
		 			     $largeur_utilise = $largeur_utilise+$largeur_d_une_moyenne;
					    }
$cpt_ordre = $cpt_ordre + 1;
}
		  $largeur_utilise = 0;
// fin de boucle d'ordre
					 
					// rang de l'�l�ve
					if($active_rang==='1') {
			 	 	  $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
				 	  $pdf->SetFont($caractere_utilse,'',10);
					  $pdf->Cell($largeur_rang, $hauteur_info_categorie, '','T',0,'C');
					  $largeur_utilise = $largeur_utilise+$largeur_rang;
					}
					// graphique de niveau
					 if($active_graphique_niveau==='1') {
			 	 	  $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
					  $pdf->Cell($largeur_niveau, $hauteur_info_categorie, '','T',0,'C');
					  $largeur_utilise = $largeur_utilise+$largeur_niveau;
					 }
					//appreciation
					 if($active_appreciation==='1') {
				 	  $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal);
					  $pdf->Cell($largeur_appreciation, $hauteur_info_categorie, '','TB',0,'C');
					  $largeur_utilise=0;
					 }
					$Y_decal = $Y_decal + 5;

				}
			}
				if($matiere[$ident_eleve_aff][$id_periode][$m]['categorie']===$categorie_passe) { $categorie_passe_count=$categorie_passe_count+1; } else { $categorie_passe_count=0; }
				if($matiere[$ident_eleve_aff][$id_periode][$m]['categorie']!=$categorie_passe) { $categorie_passe_count=$categorie_passe_count+1; }
				// fin des moyen par cat�gorie

				// si on affiche les cat�gorie sur le cot�

			if(!isset($matiere[$ident_eleve_aff][$id_periode][$m+1]['categorie'])) { $matiere[$ident_eleve_aff][$id_periode][$m+1]['categorie']=''; }

			if($active_regroupement_cote==='1') {
				if($matiere[$ident_eleve_aff][$id_periode][$m]['categorie']!=$matiere[$ident_eleve_aff][$id_periode][$m+1]['categorie'] and $categorie_passe!='')
 				{
					//hauteur du regroupement hauteur des matier * nombre de matier de la cat�gorie
					$hauteur_regroupement=$espace_entre_matier*$categorie_passe_count;

					//placement du cadre
						if($nb_eleve_aff===0) { $enplus = 5; }
						if($nb_eleve_aff!=0) { $enplus = 0; }
					if($active_entete_regroupement==='1') {
				 	   $pdf->SetXY($X_bloc_matiere-5, $Y_decal-$hauteur_regroupement+$espace_entre_matier);
					} else {
						   if($nb_eleve_aff===0) { $enplus_2 = 1.8; }
						   if($nb_eleve_aff!=0) { $enplus_2 = 1.5; }
						   $pdf->SetXY($X_bloc_matiere-5, $Y_decal-$hauteur_regroupement+$hauteur_entete+5+$enplus+$enplus_2);
						}
		 		        $pdf->SetFillColor($couleur_categorie_cote1, $couleur_categorie_cote2, $couleur_categorie_cote3);
					if($couleur_categorie_cote === '1') { $mode_choix_c = '2'; } else { $mode_choix_c = '1'; }
					$pdf->drawTextBox("", 5, $hauteur_regroupement, 'C', 'T', $mode_choix_c);
					//texte � afficher
					$hauteur_caractere_vertical = '8';
				 	$pdf->SetFont($caractere_utilse,'',$hauteur_caractere_vertical);
					$text_s = unhtmlentities($matiere[$ident_eleve_aff][$id_periode][$m]['categorie']);
					$longeur_test_s = $pdf->GetStringWidth($text_s);

					// gestion de la taille du texte vertical
					$taille_texte = $hauteur_regroupement;
					$grandeur_texte = 'test';
					while($grandeur_texte != 'ok') {
					 if($taille_texte < $longeur_test_s)
					  {
					     $hauteur_caractere_vertical = $hauteur_caractere_vertical-0.3;
					     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere_vertical);
					     $longeur_test_s = $pdf->GetStringWidth($text_s);
					  } else { $grandeur_texte = 'ok'; }
	                		}


					//d�calage pour centre le texte
					$deca = ($hauteur_regroupement-$longeur_test_s)/2;
						$deca = 0;
					$deca = ($hauteur_regroupement-$longeur_test_s)/2;

					//place le texte dans le cadre
					$placement = $Y_decal+$espace_entre_matier-$deca;
				 	$pdf->SetFont($caractere_utilse,'',$hauteur_caractere_vertical);
					$pdf->TextWithDirection($X_bloc_matiere-1,$placement,unhtmlentities($text_s),'U');
				 	$pdf->SetFont($caractere_utilse,'',10);
		 		        $pdf->SetFillColor(0, 0, 0);
				}
			}
				// fin d'affichage cat�gorie sur le cot�
				$categorie_passe=$matiere[$ident_eleve_aff][$id_periode][$m]['categorie'];
			// fin de gestion de cat�gorie

		 	$pdf->SetXY($X_bloc_matiere, $Y_decal);

			// calcule la taille du titre de la mati�re
				$hauteur_caractere_matiere=10;
				$pdf->SetFont($caractere_utilse,'B',$hauteur_caractere_matiere);
				$val = $pdf->GetStringWidth($matiere[$ident_eleve_aff][$id_periode][$m]['matiere']);
				$taille_texte = ($largeur_matiere);
				$grandeur_texte='test';
				while($grandeur_texte!='ok') {
				 if($taille_texte<$val)
				  {
				     $hauteur_caractere_matiere = $hauteur_caractere_matiere-0.3;
				     $pdf->SetFont($caractere_utilse,'B',$hauteur_caractere_matiere);
				     $val = $pdf->GetStringWidth($matiere[$ident_eleve_aff][$id_periode][$m]['matiere']);
				  } else { $grandeur_texte='ok'; }
                		}
				$grandeur_texte='test';
		 	$pdf->Cell($largeur_matiere, $espace_entre_matier/2, $matiere[$ident_eleve_aff][$id_periode][$m]['matiere'],'LR',1,'L');
			$Y_decal = $Y_decal+($espace_entre_matier/2);
		 	$pdf->SetXY($X_bloc_matiere, $Y_decal);
		 	$pdf->SetFont($caractere_utilse,'',8);
			// nom des professeurs
			$nb_prof_matiere = count($matiere[$ident_eleve_aff][$id_periode][$m]['prof']);
			$espace_matiere_prof = $espace_entre_matier/2;
			$espace_matiere_prof = $espace_matiere_prof/$nb_prof_matiere;
			$nb_pass_count = '0';
			$text_prof = '';
			while ($nb_prof_matiere > $nb_pass_count)
			{
				// calcule de la hauteur du caract�re du prof
				$text_prof = $matiere[$ident_eleve_aff][$id_periode][$m]['prof'][$nb_pass_count];
				if ( $nb_prof_matiere <= 2 ) { $hauteur_caractere_prof = 8; }
				elseif ( $nb_prof_matiere == 3) { $hauteur_caractere_prof = 5; }
				elseif ( $nb_prof_matiere > 3) { $hauteur_caractere_prof = 2; }
				$pdf->SetFont($caractere_utilse,'',$hauteur_caractere_prof);
				$val = $pdf->GetStringWidth($text_prof);
				$taille_texte = ($largeur_matiere);
				$grandeur_texte='test';
				while($grandeur_texte!='ok') {
				 if($taille_texte<$val)
				  {
				     $hauteur_caractere_prof = $hauteur_caractere_prof-0.3;
				     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere_prof);
				     $val = $pdf->GetStringWidth($text_prof);
				  } else { $grandeur_texte='ok'; }
                		}
				$grandeur_texte='test';
		 	        $pdf->SetX($X_bloc_matiere);
				if( empty($matiere[$ident_eleve_aff][$id_periode][$m]['prof'][$nb_pass_count+1]) ) {
				 	$pdf->Cell($largeur_matiere, $espace_matiere_prof, $text_prof,'LRB',1,'L');
				}
				if( !empty($matiere[$ident_eleve_aff][$id_periode][$m]['prof'][$nb_pass_count+1]) ) {
				 	$pdf->Cell($largeur_matiere, $espace_matiere_prof, $text_prof,'LR',1,'L');
				}
			$nb_pass_count = $nb_pass_count + 1;
			}
		 	//$pdf->Cell($largeur_matiere, $espace_entre_matier/3, $matiere[$ident_eleve_aff][$id_periode][$m]['prof'],'LRB',0,'L');
			$largeur_utilise = $largeur_matiere;

			// co�fficient mati�re
			if($active_coef_moyenne==='1') {
		 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
	 	 	 $pdf->SetFont($caractere_utilse,'',10);
			 $pdf->Cell($largeur_coef_moyenne, $espace_entre_matier, $matiere[$ident_eleve_aff][$id_periode][$m]['coef'],1,0,'C');
			 $largeur_utilise = $largeur_utilise+$largeur_coef_moyenne;
			}
				//permet le calcule total des coefficients
				// if(empty($moyenne_min[$id_classe][$id_periode])) { 
				$total_coef_en_calcul=$total_coef_en_calcul+$matiere[$ident_eleve_aff][$id_periode][$m]['coef'];
				//}

			// nombre de note
			if($active_nombre_note_case==='1') {
		 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
	 	 	 $pdf->SetFont($caractere_utilse,'',10);
			 $pdf->Cell($largeur_nombre_note, $espace_entre_matier, $matiere[$ident_eleve_aff][$id_periode][$m]['nb_notes_matiere'],1,0,'C');
			 $largeur_utilise = $largeur_utilise+$largeur_nombre_note;
			}

			// les moyennes eleve, classe, min, max
$cpt_ordre = 0;
while ( !empty($ordre_moyenne[$cpt_ordre]) ) {
			    //eleve
		 	    if( $active_moyenne_eleve === '1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'eleve' ) {
			     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
	 		     $pdf->SetFont($caractere_utilse,'B',10);
		             $pdf->SetFillColor($couleur_reperage_eleve1, $couleur_reperage_eleve2, $couleur_reperage_eleve3);
				//calcul nombre de sous affichage
				$nb_sousaffichage='1';
				if(empty($active_coef_sousmoyene)) { $active_coef_sousmoyene = ''; }

			     if($active_coef_sousmoyene==='1') { $nb_sousaffichage = $nb_sousaffichage + 1; }
			     if($active_nombre_note==='1') { $nb_sousaffichage = $nb_sousaffichage + 1; }
			     if($toute_moyenne_meme_col==='1') { if($active_moyenne_classe==='1') { $nb_sousaffichage = $nb_sousaffichage + 1; } }
			     if($toute_moyenne_meme_col==='1') { if($active_moyenne_min==='1') { $nb_sousaffichage = $nb_sousaffichage + 1; } }
			     if($toute_moyenne_meme_col==='1') { if($active_moyenne_max==='1') { $nb_sousaffichage = $nb_sousaffichage + 1; } }
			     $pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier/$nb_sousaffichage, present_nombre($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'TLR',2,'C',$active_reperage_eleve);
			     if($active_coef_sousmoyene==='1') {
				$pdf->SetFont($caractere_utilse,'I',7);
				$pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier/$nb_sousaffichage, 'coef. 2.0','LR',2,'C',$active_reperage_eleve);
			     }
			     if($toute_moyenne_meme_col==='1') {
				$pdf->SetFont($caractere_utilse,'I',7);
				if($active_moyenne_classe==='1') { $pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier/$nb_sousaffichage, 'cla.'.present_nombre($matiere[$ident_eleve_aff][$id_periode][$m]['moy_classe'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'LR',2,'C',$active_reperage_eleve); }
				if($active_moyenne_min==='1') { $pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier/$nb_sousaffichage, 'min.'.present_nombre($matiere[$ident_eleve_aff][$id_periode][$m]['moy_min'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'LR',2,'C',$active_reperage_eleve); }
				if($active_moyenne_max==='1') { $pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier/$nb_sousaffichage, 'max.'.present_nombre($matiere[$ident_eleve_aff][$id_periode][$m]['moy_max'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'LR',2,'C',$active_reperage_eleve); }
			     }
			     if($active_nombre_note==='1') {
				$pdf->SetFont($caractere_utilse,'I',7);
				$pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier/$nb_sousaffichage, '12 not.','LR',2,'C',$active_reperage_eleve);
			     }
		 	     $pdf->SetFont($caractere_utilse,'',10);
			     $pdf->SetFillColor(0, 0, 0);
			     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
			    }
			    //classe
 			    if( $active_moyenne_classe === '1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'classe' ) {
			     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
			     $pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier, present_nombre($matiere[$ident_eleve_aff][$id_periode][$m]['moy_classe'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'TLR',0,'C');
				//permet le calcule de la moyenne g�n�ral de la classe
				if(empty($moyenne_classe[$id_classe][$id_periode])) { $total_moyenne_classe_en_calcul=$total_moyenne_classe_en_calcul+($matiere[$ident_eleve_aff][$id_periode][$m]['moy_classe']*$matiere[$ident_eleve_aff][$id_periode][$m]['coef']); }
	 		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
			    }
			    //min
 			    if( $active_moyenne_min==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'min' ) {
			     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
			     $pdf->SetFont($caractere_utilse,'',8);
			     $pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier, present_nombre($matiere[$ident_eleve_aff][$id_periode][$m]['moy_min'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'TLR',0,'C');
				//permet le calcule de la moyenne mini
				if(empty($moyenne_min[$id_classe][$id_periode])) { $total_moyenne_min_en_calcul=$total_moyenne_min_en_calcul+($matiere[$ident_eleve_aff][$id_periode][$m]['moy_min']*$matiere[$ident_eleve_aff][$id_periode][$m]['coef']); }
			     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
			    }
			    //max
 			    if( $active_moyenne_max === '1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'max' ) {
			     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
			     $pdf->Cell($largeur_d_une_moyenne, $espace_entre_matier, present_nombre($matiere[$ident_eleve_aff][$id_periode][$m]['moy_max'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),'TLRB',0,'C');
				//permet le calcule de la moyenne maxi
				if(empty($moyenne_max[$id_classe][$id_periode])) { $total_moyenne_max_en_calcul=$total_moyenne_max_en_calcul+($matiere[$ident_eleve_aff][$id_periode][$m]['moy_max']*$matiere[$ident_eleve_aff][$id_periode][$m]['coef']); }
			     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
			    }
//			    $largeur_utilise = $largeur_utilise+$largeur_moyenne;


			// rang de l'�l�ve
			if($active_rang==='1' and $ordre_moyenne[$cpt_ordre] === 'rang' ) {
		 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
	 	 	 $pdf->SetFont($caractere_utilse,'',10);
			 $pdf->Cell($largeur_rang, $espace_entre_matier, $matiere[$ident_eleve_aff][$id_periode][$m]['rang'].'/'.$matiere[$ident_eleve_aff][$id_periode][$m]['nb_eleve_rang'],1,0,'C');
			 $largeur_utilise = $largeur_utilise+$largeur_rang;
			}

			// graphique de niveau
		 	if($active_graphique_niveau==='1' and $ordre_moyenne[$cpt_ordre] === 'niveau' ) {
		 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
	 	 	 $pdf->SetFont($caractere_utilse,'',10);
			 $id_groupe_graph = $matiere[$ident_eleve_aff][$id_periode][$m]['id_groupe'];
			  // placement de l'�l�ve dans le graphique de niveau
			    if ($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']!="") {
                                if ($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']<5) { $place_eleve=5;}
                                if (($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']>=5) and ($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']<8))  { $place_eleve=4;}
                                if (($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']>=8) and ($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']<10)) { $place_eleve=3;}
                                if (($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']>=10) and ($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']<12)) {$place_eleve=2;}
                                if (($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']>=12) and ($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']<15)) { $place_eleve=1;}
                                if ($matiere[$ident_eleve_aff][$id_periode][$m]['moy_eleve']>=15) { $place_eleve=0;}
                            }
			 $pdf->DiagBarre($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2), $largeur_niveau, $espace_entre_matier, $data_grap[$id_periode][$id_groupe_graph], $place_eleve);
			 $place_eleve=''; // on vide la variable
			 $largeur_utilise = $largeur_utilise+$largeur_niveau;
			}

			//appr�ciation
			if($active_appreciation==='1' and $ordre_moyenne[$cpt_ordre] === 'appreciation' ) {
				// si on autorise l'affichage des sous mati�re et s'il y en a alors on les affiche
				 $id_groupe_select = $matiere[$ident_eleve_aff][$id_periode][$m]['id_groupe'];
	 	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
			 $X_sous_matiere = 0; $largeur_sous_matiere=0;

				if($autorise_sous_matiere==='1' and !empty($sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select][0]['titre'])) {
					$X_sous_matiere = $X_note_moy_app+$largeur_utilise;
					$Y_sous_matiere = $Y_decal-($espace_entre_matier/2);
					$n=0;
					$largeur_texte_sousmatiere=0; $largeur_sous_matiere=0;
					while( !empty($sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select][$n]['titre']) )
					{
					 $pdf->SetFont($caractere_utilse,'',8);
					 $largeur_texte_sousmatiere = $pdf->GetStringWidth($sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select][$n]['titre'].': '.$sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select][$n]['moyenne']);
					 if($largeur_sous_matiere<$largeur_texte_sousmatiere) { $largeur_sous_matiere=$largeur_texte_sousmatiere; }
					$n = $n + 1;
					}
					if($largeur_sous_matiere!='0') { $largeur_sous_matiere = $largeur_sous_matiere + 2; }
					$n=0;
					while( !empty($sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select][$n]['titre']) )
					{
	 	 	 		 $pdf->SetXY($X_sous_matiere, $Y_sous_matiere);
			 	 	 $pdf->SetFont($caractere_utilse,'',8);
					 $pdf->Cell($largeur_sous_matiere, $espace_entre_matier/$sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select]['nb'], $sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select][$n]['titre'].': '.$sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select][$n]['moyenne'],1,0,'L');
					 $Y_sous_matiere = $Y_sous_matiere+$espace_entre_matier/$sous_matiere[$ident_eleve_aff][$id_periode][$id_groupe_select]['nb'];
					$n = $n + 1;
					}
					$largeur_utilise = $largeur_utilise+$largeur_sous_matiere;
				}
	 	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_decal-($espace_entre_matier/2));
			 // calcule de la taille du texte des appr�ciation
 			 $hauteur_caractere_appreciation=9;
		 	 $pdf->SetFont($caractere_utilse,'',$hauteur_caractere_appreciation);
			 $val = $pdf->GetStringWidth($matiere[$ident_eleve_aff][$id_periode][$m]['appreciation']);
				$largeur_appreciation2 = $largeur_appreciation - $largeur_sous_matiere;

			 $taille_texte = (($espace_entre_matier/3)*$largeur_appreciation2);
			 $grandeur_texte='test';
			 while($grandeur_texte!='ok') {
				 if($taille_texte<$val)
				  {
				     $hauteur_caractere_appreciation = $hauteur_caractere_appreciation-0.3;
				     $pdf->SetFont($caractere_utilse,'',$hauteur_caractere_appreciation);
				     $val = $pdf->GetStringWidth($matiere[$ident_eleve_aff][$id_periode][$m]['appreciation']);
				  } else { $grandeur_texte='ok'; }
                		}
				$grandeur_texte='test';
			 $pdf->drawTextBox($matiere[$ident_eleve_aff][$id_periode][$m]['appreciation'], $largeur_appreciation2, $espace_entre_matier, 'J', 'M', 1);
		 	 $pdf->SetFont($caractere_utilse,'',10);
			 $largeur_utilise = $largeur_utilise + $largeur_appreciation2;
//			$largeur_utilise = 0;
			}

$cpt_ordre = $cpt_ordre + 1;
}
		  $largeur_utilise = 0;
// fin de boucle d'ordre
			$Y_decal = $Y_decal+($espace_entre_matier/2);
		  }



	     //bas du tableau des note et app si les affichage des moyennes ne sont pas affich� le bas du tableau ne seras pas affich�
	     if($active_moyenne==='1') {
		 $X_note_moy_app = $X_note_app; $Y_note_moy_app = $Y_note_app+$hauteur_note_app-$hauteur_entete_moyenne_general;
	 	 $pdf->SetXY($X_note_moy_app, $Y_note_moy_app);
	 	 $pdf->SetFont($caractere_utilse,'',10);
		 $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
		 $pdf->Cell($largeur_matiere, $hauteur_entete_moyenne_general, "Moyenne g�n�rale",1,0,'C', $couleur_moy_general);
		 $largeur_utilise = $largeur_matiere;

		// co�fficient mati�re
		if($active_coef_moyenne==='1') {
	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
		 $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
		 $pdf->Cell($largeur_coef_moyenne, $hauteur_entete_moyenne_general, "",1,0,'C', $couleur_moy_general);
		 $largeur_utilise = $largeur_utilise + $largeur_coef_moyenne;
		}

		// nombre de note
		if($active_nombre_note_case==='1') {
	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
		 $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
		 $pdf->Cell($largeur_nombre_note, $hauteur_entete_moyenne_general, "",1,0,'C', $couleur_moy_general);
		 $largeur_utilise = $largeur_utilise + $largeur_nombre_note;
		}

	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);

$cpt_ordre = 0;
while ( !empty($ordre_moyenne[$cpt_ordre]) ) {
		    //eleve
	 	    if($active_moyenne_eleve==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'eleve' ) {
		     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
	 	     $pdf->SetFont($caractere_utilse,'B',10);
		     $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
		      	if($active_reperage_eleve==='1') { $pdf->SetFillColor($couleur_reperage_eleve1, $couleur_reperage_eleve2, $couleur_reperage_eleve3); $couleur_moy_general = 1; }
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_moyenne_general, present_nombre($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve'], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),1,0,'C',$couleur_moy_general);
		 	if($active_reperage_eleve==='1' and $couleur_moy_general==='1') { $couleur_moy_general = 0; }
	 	     $pdf->SetFont($caractere_utilse,'',10);
	 	     $pdf->SetFillColor(0, 0, 0);
 		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
		    }
		    //classe
	 	    if($active_moyenne_classe==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'classe' ) {
		     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
	 	     $pdf->SetFont($caractere_utilse,'',8);
		     $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
//			if(empty($moyenne_classe[$id_classe_selection][$id_periode])) { $moyenne_classe[$id_classe_selection][$id_periode]=$total_moyenne_classe_en_calcul/$total_coef_en_calcul; }
			$moyenne_classe = $total_moyenne_classe_en_calcul / $total_coef_en_calcul;
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_moyenne_general, present_nombre($moyenne_classe, $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),1,0,'C', $couleur_moy_general);
 		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
		    }
		    //min
//$moyenne_classe_minmax[$id_periode][$id_classe_selection]['min']='20';
//$moyenne_classe_minmax[$id_periode][$id_classe_selection]['max']='0';
	 	    if($active_moyenne_min==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'min' ) {
		     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
	 	     $pdf->SetFont($caractere_utilse,'',8);
		     $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
		$moyenne_min = $total_moyenne_min_en_calcul / $total_coef_en_calcul;
//		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_moyenne_general, present_nombre($moyenne_min[$id_classe_selection][$id_periode], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),1,0,'C', $couleur_moy_general);
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_moyenne_general, present_nombre($moyenne_min, $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),1,0,'C', $couleur_moy_general);
 		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
		    }
		    //max
	 	    if($active_moyenne_max==='1' and $active_moyenne === '1' and $ordre_moyenne[$cpt_ordre] === 'max' ) {
		     $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
	 	     $pdf->SetFont($caractere_utilse,'',8);
		     $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
//			if(empty($moyenne_min[$id_classe_selection][$id_periode])) { $moyenne_max[$id_classe_selection][$id_periode]=$total_moyenne_max_en_calcul/$total_coef_en_calcul; }
	 		$moyenne_max = $total_moyenne_max_en_calcul / $total_coef_en_calcul;
//		    $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_moyenne_general, present_nombre($moyenne_max[$id_classe_selection][$id_periode], $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),1,0,'C', $couleur_moy_general);
		     $pdf->Cell($largeur_d_une_moyenne, $hauteur_entete_moyenne_general, present_nombre($moyenne_max, $arrondie_choix, $nb_chiffre_virgule, $chiffre_avec_zero),1,0,'C', $couleur_moy_general);
 		     $largeur_utilise = $largeur_utilise + $largeur_d_une_moyenne;
		    }

		// rang de l'�l�ve
		if($active_rang==='1' and $ordre_moyenne[$cpt_ordre] === 'rang' ) {
	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
 	         $pdf->SetFont($caractere_utilse,'',8);
		 $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
		 $pdf->Cell($largeur_rang, $hauteur_entete_moyenne_general, $rang_eleve_classe[$ident_eleve_aff][$id_periode].'/'.$classe_effectif_tab[$id_classe_selection][$id_periode]['effectif'],'TLRB',0,'C', $couleur_moy_general);
		 $largeur_utilise = $largeur_utilise + $largeur_rang;
		}

		// graphique de niveau
	 	if($active_graphique_niveau==='1' and $ordre_moyenne[$cpt_ordre] === 'niveau' ) {
	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
		 $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
			  // placement de l'�l�ve dans le graphique de niveau
			    if ($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']!="") {
                                if ($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']<5) { $place_eleve=5;}
                                if (($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']>=5) and ($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']<8))  { $place_eleve=4;}
                                if (($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']>=8) and ($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']<10)) { $place_eleve=3;}
                                if (($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']>=10) and ($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']<12)) {$place_eleve=2;}
                                if (($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']>=12) and ($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']<15)) { $place_eleve=1;}
                                if ($info_bulletin[$ident_eleve_aff][$id_periode]['moy_general_eleve']>=15) { $place_eleve=0;}
                            }
			 $pdf->DiagBarre($X_note_moy_app+$largeur_utilise, $Y_note_moy_app, $largeur_niveau, $hauteur_entete_moyenne_general, $data_grap_classe[$id_periode][$id_classe_selection], $place_eleve);
			 $place_eleve=''; // on vide la variable
		 $largeur_utilise = $largeur_utilise+$largeur_niveau;
		}
		//appr�ciation
		if($active_appreciation==='1' and $ordre_moyenne[$cpt_ordre] === 'appreciation' ) {
	 	 $pdf->SetXY($X_note_moy_app+$largeur_utilise, $Y_note_moy_app);
		 $pdf->SetFillColor($couleur_moy_general1, $couleur_moy_general2, $couleur_moy_general3);
		 $pdf->Cell($largeur_appreciation, $hauteur_entete_moyenne_general, '','TLRB',0,'C', $couleur_moy_general);
		 $largeur_utilise = $largeur_utilise + $largeur_appreciation;
		}
$cpt_ordre = $cpt_ordre + 1;
}
		  $largeur_utilise = 0;
// fin de boucle d'ordre
		 $pdf->SetFillColor(0, 0, 0);
	      }
	}

	// bloc absence
	if($active_bloc_absence==='1') {
	 $pdf->SetXY($X_absence, $Y_absence);
	 $origine_Y_absence = $Y_absence;
	 $pdf->SetFont($caractere_utilse,'I',8);
	 $info_absence='';
	 if($info_bulletin[$ident_eleve_aff][$id_periode]['absences'] != '?' and $info_bulletin[$ident_eleve_aff][$id_periode]['absences_retards'] != '?') {
		 if($info_bulletin[$ident_eleve_aff][$id_periode]['absences'] == '0')
		  {
	                $info_absence="<i>Aucune demi-journ�e d'absence</i>.";
	          } else {
	                	$info_absence="<i>Nombre de demi-journ�es d'absence ";
	                	if ($info_bulletin[$ident_eleve_aff][$id_periode]['absences_nj'] == '0') { $info_absence = $info_absence."justifi�es "; }
	                	$info_absence = $info_absence.": </i><b>".$info_bulletin[$ident_eleve_aff][$id_periode]['absences']."</b>";
	                	if ($info_bulletin[$ident_eleve_aff][$id_periode]['absences_nj'] != '0')
				 {
	                    		$info_absence = $info_absence." (dont <b>".$info_bulletin[$ident_eleve_aff][$id_periode]['absences_nj']."</b> non justifi�e";
					if ($info_bulletin[$ident_eleve_aff][$id_periode]['absences_nj'] != '1') { $info_absence = $info_absence."s"; }
	              		      	$info_absence = $info_absence.")";
	                 	 }
	        	  $info_absence = $info_absence.".";
			 }
	          if($info_bulletin[$ident_eleve_aff][$id_periode]['absences_retards'] != '0')
		  {
        	        $info_absence = $info_absence."<i> Nombre de retards : </i><b>".$info_bulletin[$ident_eleve_aff][$id_periode]['absences_retards']."</b>";
	          }
	  }
	  $pdf->SetFont($caractere_utilse,'',8);
/*
        echo "  (C.P.E. charg�";
        $sql="SELECT civilite FROM utilisateurs WHERE login='$current_eleve_cperesp_login'";
        $res_civi=mysql_query($sql);
        if(mysql_num_rows($res_civi)>0){
            $lig_civi=mysql_fetch_object($res_civi);
            if($lig_civi->civilite!="M."){
                echo "e";
            }
        }
        echo " du suivi : ". affiche_utilisateur($current_eleve_cperesp_login,$id_classe) . ")";
            if ($current_eleve_appreciation_absences != ""){echo "<br />$current_eleve_appreciation_absences";}
            echo "</p></td>\n</tr>\n</table>\n";
        }
*/
		// MODIF: boireaus
		//$info_absence = $info_absence." (C.P.E. charg� du suivi : ".$cpe_eleve[$i].")";
		$info_absence = $info_absence." (C.P.E. charg�";
        $sql="SELECT civilite FROM utilisateurs WHERE login='".$cperesp_login[$i]."'";
        $res_civi=mysql_query($sql);
        if(mysql_num_rows($res_civi)>0){
            $lig_civi=mysql_fetch_object($res_civi);
            if($lig_civi->civilite!="M."){
                $info_absence = $info_absence."e";
            }
        }
		$info_absence = $info_absence." du suivi : ".$cpe_eleve[$i].")";
		$pdf->MultiCellTag(200, 5, $info_absence, '', 'J', '');


	  if ( isset($Y_avis_cons_init) ) { $Y_avis_cons = $Y_avis_cons_init; }
	  if ( isset($Y_sign_chef_init) ) { $Y_sign_chef = $Y_sign_chef_init; }
	  if ( !isset($Y_avis_cons_init) ) { $Y_avis_cons_init = $Y_avis_cons + 0.5; }
	  if ( !isset($Y_sign_chef_init) ) { $Y_sign_chef_init = $Y_sign_chef + 0.5; }

	  if ( isset($hauteur_avis_cons_init) ) { $hauteur_avis_cons = $hauteur_avis_cons_init; }
	  if ( isset($hauteur_sign_chef_init) ) { $hauteur_sign_chef = $hauteur_sign_chef_init; }
	  if ( !isset($hauteur_avis_cons_init) ) { $hauteur_avis_cons_init = $hauteur_avis_cons - 0.5; }
	  if ( !isset($hauteur_sign_chef_init) ) { $hauteur_sign_chef_init = $hauteur_sign_chef - 0.5; }

          if($info_bulletin[$ident_eleve_aff][$id_periode]['absences_appreciation'] != "")
	   {

		// supprimer les espaces
		$text_absences_appreciation = trim(str_replace(array("\r\n","\r","\n"), ' ', $info_bulletin[$ident_eleve_aff][$id_periode]['absences_appreciation']));
		$info_absence_appreciation = "<i>Avis CPE:</i> <b>".$text_absences_appreciation."</b>";
		$text_absences_appreciation = '';
	 	$pdf->SetXY($X_absence, $Y_absence+4);
	 	$pdf->SetFont($caractere_utilse,'',8);
		$pdf->MultiCellTag(200, 3, $info_absence_appreciation, '', 'J', '');
		//$hauteur_avis_cons_init = $hauteur_avis_cons;
		$val = $pdf->GetStringWidth($info_absence_appreciation);
		// nombre de ligne que prend la remarque cpe
		 	//Arrondi � l'entier sup�rieur : ceil()
		$nb_ligne = 1;
		$nb_ligne = ceil($val / 200);
		$hauteur_pris = $nb_ligne * 3;

		$Y_avis_cons = $Y_avis_cons + $hauteur_pris; $hauteur_avis_cons = $hauteur_avis_cons - ( $hauteur_pris + 0.5 );
		$Y_sign_chef = $Y_sign_chef + $hauteur_pris; $hauteur_sign_chef = $hauteur_sign_chef - ( $hauteur_pris + 0.5 );
		$hauteur_pris = 0;
	   } else {
		       if($Y_avis_cons_init!=$Y_avis_cons)
			{
			  $Y_avis_cons = $Y_avis_cons - $hauteur_pris;
			  $hauteur_avis_cons = $hauteur_avis_cons + $hauteur_pris;
			  $Y_sign_chef = $Y_sign_chef - $hauteur_pris;
			  $hauteur_sign_chef = $hauteur_sign_chef + $hauteur_pris;
			  $hauteur_pris = 0;
			}
		  }
	 $info_absence = '';
	 $info_absence_appreciation = '';
 	 $pdf->SetFont($caractere_utilse,'',10);
	}

       if($Y_avis_cons_init!=$Y_avis_cons) {
		$Y_avis_cons = $Y_avis_cons + 0.5;
		$Y_sign_chef = $Y_sign_chef + 0.5;
	}

	// bloc avis du conseil de classe
	if($active_bloc_avis_conseil==='1') {
	 if($cadre_avis_cons!=0) { $pdf->Rect($X_avis_cons, $Y_avis_cons, $longeur_avis_cons, $hauteur_avis_cons, 'D'); }
	 $pdf->SetXY($X_avis_cons,$Y_avis_cons);
 	 $pdf->SetFont($caractere_utilse,'I',10);
	 $pdf->Cell($longeur_avis_cons,5, "Avis du Conseil de classe: ",0,2,'');
	 $pdf->SetXY($X_avis_cons+2.5,$Y_avis_cons+5);
 	 $pdf->SetFont($caractere_utilse,'',10);
	 $texteavis = $info_bulletin[$ident_eleve_aff][$id_periode]['avis_conseil_classe'];
	 $pdf->drawTextBox($texteavis, $longeur_avis_cons-5, $hauteur_avis_cons-10, 'J', 'M', 0);
	 $X_pp_aff=$X_avis_cons; $Y_pp_aff=$Y_avis_cons+$hauteur_avis_cons-5;
	 $pdf->SetXY($X_pp_aff,$Y_pp_aff);
	 $pdf->MultiCellTag(200, 5, $pp_classe[$i], '', 'J', '');
	}

	// bloc du chef
	if($active_bloc_chef==='1') {
	 if($cadre_sign_chef!=0) { $pdf->Rect($X_sign_chef, $Y_sign_chef, $longeur_sign_chef, $hauteur_sign_chef, 'D'); }
	 $pdf->SetXY($X_sign_chef,$Y_sign_chef);
 	 $pdf->SetFont($caractere_utilse,'',10);
	 if($affichage_haut_responsable==='1') {
	 	 $pdf->SetFont($caractere_utilse,'B',10);
		 $pdf->Cell($longeur_sign_chef,5, $info_classe[$id_classe_selection]['fonction_hautresponsable'],0,2,'');
	 	 $pdf->SetFont($caractere_utilse,'I',8);
		 $pdf->Cell($longeur_sign_chef,5, $info_classe[$id_classe_selection]['nom_hautresponsable'],0,2,'');
	 } else {
			$pdf->MultiCell($longeur_sign_chef,5, "Visa du Chef d'�tablissement\nou de son d�l�gu�",0,2,'');
		}
	}
  $cpt_info_periode = $cpt_info_periode+1;
  }

	//$nb_eleve_aff = $nb_eleve_aff + 1;
	if ( $nb_bulletin_parent[$ident_eleve_aff] === 1 or $passage_deux === 'oui' )
	{
		//compte le nombre d'�l�ment affich�
		$nb_eleve_aff = $nb_eleve_aff + 1;
		$passage_deux = 'non';
		$responsable_place = 0;
	}
	elseif ( $nb_bulletin_parent[$ident_eleve_aff] === 2 and $passage_deux != 'oui' )
	{
		//compte le nombre d'�l�ment affich�
		$nb_eleve_aff = $nb_eleve_aff ;
		$passage_deux = 'oui';
		$responsable_place = 1;
	}
	elseif ( $nb_bulletin_parent[$ident_eleve_aff] === 2 and $passage_deux === 'oui' )
	{
		//compte le nombre d'�l�ment affich�
		$nb_eleve_aff = $nb_eleve_aff + 1;
		$passage_deux = 'non';
		$responsable_place = 0;
	}


}

//vid� les variable de session utilis�
//unset($_SESSION["periode"]);
unset($_SESSION["classe"]);
unset($_SESSION["eleve"]);

//fermeture du fichier pdf et lecture dans le navigateur 'nom', 'I/D'
$nom_bulletin = 'bulletin_'.$nom_bulletin.'.pdf';
$pdf->Output($nom_bulletin,'I');
//$pdf->closeParsers();
?>
