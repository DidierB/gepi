<?php
/*
 * Last modification  : 15/06/2006
 *
 * Copyright 2001, 2006 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Christian Chapel
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

$niveau_arbo = 2;
// Initialisations files
require_once("../../lib/initialisations.inc.php");

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
    header("Location: ../../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../../logout.php?auto=1");
    die();
};

if (!checkAccess()) {
    header("Location: ../../logout.php?auto=1");
    die();
}


//On v�rifie si le module est activ�
if (getSettingValue("active_module_msj")!='y') {
    die("Le module n'est pas activ�.");
}

  include('../lib/fonction_dossier.php');
  include('../lib/pclzip.lib.php');
  include('../lib/pcltar.lib.php');
  include('../lib/pcltrace.lib.php');
  include('../lib/pclerror.lib.php');

// uid de pour ne pas refaire renvoyer plusieurs fois le m�me formulaire
// autoriser la validation de formulaire $uid_post===$_SESSION['uid_prime']
 if(empty($_SESSION['uid_prime'])) { $_SESSION['uid_prime']=''; }
 if (empty($_GET['uid_post']) and empty($_POST['uid_post'])) {$uid_post='';}
    else { if (isset($_GET['uid_post'])) {$uid_post=$_GET['uid_post'];} if (isset($_POST['uid_post'])) {$uid_post=$_POST['uid_post'];} }
	$uid = md5(uniqid(microtime(), 1));
	   // on remplace les %20 par des espaces
	    $uid_post = eregi_replace('%20',' ',$uid_post);
	if($uid_post===$_SESSION['uid_prime']) { $valide_form = 'yes'; } else { $valide_form = 'no'; }
	$_SESSION['uid_prime'] = $uid;
	// variable � conna�tre
	$site_de_miseajour = getSettingValue('site_msj_gepi');

    if (empty($_GET['maj_logiciel']) AND empty($_POST['maj_logiciel'])) {$maj_logiciel='';}
     else { if (isset($_GET['maj_logiciel'])) {$maj_logiciel=$_GET['maj_logiciel'];} if (isset($_POST['maj_logiciel'])) {$maj_logiciel=$_POST['maj_logiciel'];} }
    if (empty($_GET['maj_fichier']) AND empty($_POST['maj_fichier'])) {$maj_fichier='';}
     else { if (isset($_GET['maj_fichier'])) {$maj_fichier=$_GET['maj_fichier'];} if (isset($_POST['maj_fichier'])) {$maj_fichier=$_POST['maj_fichier'];} }
    if (empty($_GET['maj_type']) AND empty($_POST['maj_type'])) {$maj_type='';}
     else { if (isset($_GET['maj_type'])) {$maj_type=$_GET['maj_type'];} if (isset($_POST['maj_type'])) {$maj_type=$_POST['maj_type'];} }
    if (empty($_GET['tableau_select']) AND empty($_POST['tableau_select'])) {$tableau_select='';}
     else { if (isset($_GET['tableau_select'])) {$tableau_select=$_GET['tableau_select'];} if (isset($_POST['tableau_select'])) {$tableau_select=$_POST['tableau_select'];} }
    if(!isset($_SESSION['message_traitement'])) { $message_traitement=''; } else { $message_traitement=$_SESSION['message_traitement']; }
    if(!isset($_SESSION['message_erreur'])) { $message_erreur=''; } else { $message_erreur=$_SESSION['message_erreur']; }

		if(empty($_POST['source_fichier_select'])) { $source_fichier_select=''; } else {$source_fichier_select=$_POST['source_fichier_select']; }
		if(empty($_POST['nom_fichier_select'])) { $nom_fichier_select=''; } else {$nom_fichier_select=$_POST['nom_fichier_select']; }
		if(empty($_POST['emplacement_fichier_select'])) { $emplacement_fichier_select=''; } else {$emplacement_fichier_select=$_POST['emplacement_fichier_select']; }
		if(empty($_POST['date_fichier_select'])) { $date_fichier_select=''; } else {$date_fichier_select=$_POST['date_fichier_select']; }
		if(empty($_POST['heure_fichier_select'])) { $heure_fichier_select=''; } else {$heure_fichier_select=$_POST['heure_fichier_select']; }
		if(empty($_POST['md5_fichier_select'])) { $md5_fichier_select=''; } else {$md5_fichier_select=$_POST['md5_fichier_select']; }

	$affiche_info_rc=''; $affiche_info_beta='';

$ligne=''; $ligne2='';
$version_stable=''; $version_rc=''; $version_beta='';

	//information FTP
	$dossier_ftp_gepi = getSettingValue("dossier_ftp_gepi");
	
//on recherche le fichier de mise � jour sur le site du principal
if(url_exists($site_de_miseajour."version.msj"))
{
	    if (!$fp = fopen($site_de_miseajour."version.msj","r"))
 	     {
		   // impossible d'ouvrire le fichier de mise � jour
		   $_SESSION['message_erreur'] = "Impossible d'ouvrir le fichier d'information de mise � jour";
		   header("Location: fenetre.php?maj_type=".$maj_type);
	      } else {
			  while (!feof($fp)) { //on parcourt toutes les lignes
			        $ligne .= fgets($fp, 4096); // lecture du contenu de la ligne
			  }
	   	          fclose($fp);

			$ereg = eregi("<info>(.*)</info>",$ligne,$stable_serveur);
				$erega = eregi("<changelog>(.*)</changelog>",$stable_serveur[1],$changelog);

			$ereg1 = eregi("<stable>(.*)</stable>",$ligne,$stable_serveur);
				$ereg1a = eregi("<version_stable>(.*)</version_stable>",$stable_serveur[1],$version_stable_serveur);
				$ereg2a = eregi("<site_stable>(.*)</site_stable>",$stable_serveur[1],$version_stable_site);
				$ereg3a= eregi("<fichier_stable>(.*)</fichier_stable>",$stable_serveur[1],$version_stable_fichier);
				$ereg4a= eregi("<md5_stable>(.*)</md5_stable>",$stable_serveur[1],$version_stable_md5);

			if (getSettingValue("rc_module_msj")==='y') {
			$ereg2 = eregi("<rc>(.*)</rc>",$ligne,$rc_serveur);
				$ereg1b = eregi("<version_rc>(.*)</version_rc>",$rc_serveur[1],$version_rc_serveur);
				$ereg2b = eregi("<site_rc>(.*)</site_rc>",$rc_serveur[1],$version_rc_site);
				$ereg3b = eregi("<fichier_rc>(.*)</fichier_rc>",$rc_serveur[1],$version_rc_fichier);
				$ereg4b = eregi("<md5_rc>(.*)</md5_rc>",$rc_serveur[1],$version_rc_md5);
				$affiche_info_rc="oui";
			}

			if (getSettingValue("beta_module_msj")==='y') {
			$ereg3 = eregi("<beta>(.*)</beta>",$ligne,$beta_serveur);
				$ereg1c = eregi("<version_beta>(.*)</version_beta>",$beta_serveur[1],$version_beta_serveur);
				$ereg2c = eregi("<site_beta>(.*)</site_beta>",$beta_serveur[1],$version_beta_site);
				$ereg3c = eregi("<fichier_beta>(.*)</fichier_beta>",$beta_serveur[1],$version_beta_fichier);
				$ereg4c = eregi("<md5_beta>(.*)</md5_beta",$beta_serveur[1],$version_beta_md5);
				$affiche_info_beta="oui";
			}
		      }

//on recherche la version du client
    $version_stable_client[1]=getSettingValue('version');
    if($affiche_info_rc==='oui') { $version_rc_client[1]=getSettingValue('versionRc'); }
    if($affiche_info_beta==='oui') { $version_beta_client[1]=getSettingValue('versionBeta'); }

// version stable
$nouvelle_stable = 'non';
$texte_stable='pas de version stable disponible actuellement';
if($version_stable_serveur[1]>$version_stable_client[1]) {
		$texte_stable = 'une nouvelle version stable est disponible';		
		$nouvelle_stable = 'oui';
} elseif($version_stable_serveur[1]===$version_stable_client[1]) {
		$texte_stable = 'votre version est � jour';	
} elseif($version_stable_serveur[1]<$version_stable_client[1]) {
		$texte_stable = 'vous avez une version sup�rieur � celle disponible';
}

// version rc
if($affiche_info_rc==='oui')
{ 
$nouvelle_rc = 'non';
$texte_rc='pas de version RC disponible actuellement';
$rc_version = explode('/', $version_rc_serveur[1]);
if($rc_version[0]>$version_stable_client[1] or $rc_version[0]===$version_stable_client[1]) 
 {
	if($rc_version[1]>$version_rc_client[1] and $rc_version[1]!='0') {
		$texte_rc = 'une nouvelle version RC est disponible';
		$nouvelle_rc = 'oui';
	} elseif($rc_version[1]===$version_rc_client[1]) {
		$texte_rc = 'votre version RC est � jour';	
	} elseif($rc_version[1]<$version_rc_client[1]) {
		$texte_rc = 'vous avez une version sup�rieur � celle disponible';	
	}
 } else { $texte_rc = 'aucune version RC disponible actuellement'; }
}
	
// version beta
if($affiche_info_beta==='oui')
{ 
$nouvelle_beta = 'non';
$texte_beta='pas de version BETA disponible actuellement';
$beta_version = explode('/', $version_beta_serveur[1]);
if($beta_version[0]>$version_stable_client[1] or $beta_version[0]===$version_stable_client[1]) 
 {
	if($beta_version[1]>$version_beta_client[1] and $beta_version[1]!='0') {
		$texte_beta = 'une nouvelle version BETA est disponible';
		$nouvelle_beta = 'oui';
	} elseif($beta_version[1]===$version_beta_client[1]) {
		$texte_beta = 'votre version BETA � jour';
	} elseif($beta_version[1]<$version_beta_client[1]) {
		$texte_beta = 'vous avez une version sup�rieur � celle disponible';	
	}
 } else { $texte_beta = 'aucune version BETA disponible actuellement'; }
 if( $rc_version[0]!='' ) { $nouvelle_beta='non'; }
}

// mettre � jour un fichier du logiciel
if($maj_fichier==='oui' and $valide_form==='yes' and $maj_type==='fichier')
 {

	// r�pertoire temporaire
	$rep_temp     = '../../documents/msj_temp/';

	$tableau_select['source_fichier']['1'] = $source_fichier_select;
	$tableau_select['nom_fichier']['1'] = $nom_fichier_select;
	$tableau_select['emplacement_fichier']['1'] = $emplacement_fichier_select;
	$tableau_select['date_fichier']['1'] = $date_fichier_select;
	$tableau_select['heure_fichier']['1'] = $heure_fichier_select;
	$tableau_select['md5_fichier']['1'] = $md5_fichier_select;

	// on copie le fichier dans le dossier temporaire
	$copie_fichier=copie_fichier_temp($tableau_select, $rep_temp);
	// on transfert le fichier via FTP
	$transfert_fichier=envoi_ftp($copie_fichier, $dossier_ftp_gepi);

	     //mise � jour ok on l'ins�re dans la base ou on le met � jour
	     // on regarde s'il existe d�jas un enregistrement identitique
             $compte_msj = mysql_result(mysql_query('SELECT count(*) FROM '.$prefix_base.'miseajour WHERE fichier_miseajour="'.$tableau_select['nom_fichier']['1'].'" AND emplacement_miseajour="'.$tableau_select['emplacement_fichier']['1'].'"'),0);
	     // si oui
	     if( $compte_msj === "0" ) { $requete='INSERT INTO '.$prefix_base.'miseajour (fichier_miseajour, emplacement_miseajour, date_miseajour, heure_miseajour) values ("'.$tableau_select['nom_fichier']['1'].'","'.$tableau_select['emplacement_fichier']['1'].'","'.date_sql($tableau_select['date_fichier']['1']).'","'.$tableau_select['heure_fichier']['1'].'")'; }
	     // si non
             if( $compte_msj != "0" ) { $requete='UPDATE '.$prefix_base.'miseajour SET date_miseajour = "'.date_sql($tableau_select['date_fichier']['1']).'", heure_miseajour  = "'.$tableau_select['heure_fichier']['1'].'" WHERE fichier_miseajour="'.$tableau_select['nom_fichier']['1'].'" AND emplacement_miseajour="'.$tableau_select['emplacement_fichier']['1'].'"'; }
             $resultat = mysql_query($requete) or die('Erreur SQL !'.$requete.'<br />'.mysql_error());

	// on supprime le fichier du dossier temporaire
        unlink($rep_temp.$tableau_select['nom_fichier']['1']);
 }

if($maj_logiciel==='oui' and $valide_form==='yes')
{

	if($maj_type==='stable') { $site = $version_stable_site[1]; $fichier=$version_stable_fichier[1]; $md5_de_verif = $version_stable_md5[1]; }
	if($maj_type==='rc') { $site = $version_rc_site[1]; $fichier=$version_rc_fichier[1]; $md5_de_verif = $version_rc_md5[1]; }
	if($maj_type==='beta') { $site = $version_beta_site[1]; $fichier=$version_beta_fichier[1]; $md5_de_verif = $version_beta_md5[1]; }

 	//connaitre l'extension et le nom du fichier complet sans l'extension
	$nom_fichier_sans_ext=eregi_replace("\.zip",'',$fichier);
	  if($nom_fichier_sans_ext!=$fichier) { $ext='.zip'; }
	  else { $nom_fichier_sans_ext=eregi_replace("\.tar.gz",'',$fichier);
		  if($nom_fichier_sans_ext!=$fichier) { $ext='.tar.gz'; }
	       }

	// emplacement du fichier � jour
	$file = $site.$fichier;
	// emplacement de la copie sur le serveur � mettre � jour
	$newfile = '../../documents/msj_temp/'.$fichier;

	// si le dossier de t�l�chargement de mise � jour n'existe pas on le cr�er
	$rep_de_miseajour='../../documents/msj_temp/';
	if (!is_dir($rep_de_miseajour))
	 {
		$old = umask(0000); 
		mkdir($rep_de_miseajour, 0777);
		chmod($rep_de_miseajour, 0777);
		umask($old);
	 }


	// on copie l'archive du logiciel � jour dans le dossier des mise � jour
	$old = umask(0000); 
	if(!url_exists($file))
         {
	   // le t�l�chargement n'a pas r�ussi car le fichier de mise � jour n'est pas pr�sent
	   $_SESSION['message_erreur'] = "Le fichier $file est inexistant";
           header("Location: fenetre.php?maj_type=".$maj_type);
	 } else {
		  // le fichier existe on le copie
		  if (!copy($file, $newfile)) 
                  {
		    // le t�l�chargement n'a pas r�ussi echec de connection au serveur de mise � jour
		    $_SESSION['message_erreur'] = "La copie du fichier $file n'a pas r�ussi...";
	            header("Location: fenetre.php?maj_type=".$maj_type);
		  } else {
			    umask($old);
			    // le fichier � �t� copier
			    // on v�rifie le md5
			    $md5_du_fichier_telecharge = md5_file($rep_de_miseajour.$fichier);
			    if($md5_de_verif!=$md5_du_fichier_telecharge)
			    {
			      // si le md5 n'est pas bon message d'erreur
			      $_SESSION['message_erreur'] = "le fichier t�l�charg� est corrompu, mise � jour impossible";
			      header("Location: fenetre.php?maj_type=".$maj_type);
			     } else {
				      // si le md5 est bon on continue la mise � jour
				      // on d�compresse le fichier
				      if(!class_exists("PclZip")) 
   				      {
					// si il manque la bilblioth�que on donne un message d'erreur
					$_SESSION['message_erreur'] = "il manque la biblioth�que de d�compression des fichiers";
			      	        header("Location: fenetre.php?maj_type=".$maj_type);
				      } else {
						   // source du fichier compress�
					  	   $source = $rep_de_miseajour.$fichier;
						   // destinations de la d�compression du fichier
						   $destination = '../../documents/msj_temp';
					           if ($ext === ".zip") {
						      $old = umask(0000);
						      $archive = new PclZip($source);
						      if (@$archive -> extract(PCLZIP_OPT_PATH, $destination, PCLZIP_OPT_SET_CHMOD, 0777, PCLZIP_OPT_REMOVE_PATH, $nom_fichier_sans_ext) == TRUE) { unlink($source); } else { die("Error : ".$archive->errorInfo(true)); }
					              umask($old);
						      // on liste le dossier
						      $copie_fichier = listage_dossier($destination, $destination);
						      // on transfert via FTP
						      $transfert_fichier=envoi_ftp($copie_fichier, $dossier_ftp_gepi);
						      umask($old);
						      // on supprime le dossier msj_temp
						      $old = umask(0000);
						      $dossier_destination[0]=$destination;
						      supprimer_rep($dossier_destination);
						      // puis on le recret
						      mkdir($destination, 0777);
					              umask($old);

							//mise � jour ok on l'ins�re dans la base
						     // puisque que c'est une nouvelle version on efface les donn�es de la base mise � jour
						     $requete='TRUNCATE TABLE '.$prefix_base.'miseajour';
						     $resultat = mysql_query($requete) or die('Erreur SQL !'.$requete.'<br />'.mysql_error());
						     // puis on informe la base de la version actuelle de la mise � jour
						     $requete='INSERT INTO '.$prefix_base.'miseajour (fichier_miseajour, emplacement_miseajour, date_miseajour, heure_miseajour) values ("'.$beta_version[0].'","","'.date('Y-m-d').'","'.date('H:i:s').'")';
					             $resultat = mysql_query($requete) or die('Erreur SQL !'.$requete.'<br />'.mysql_error());
						   }
				           	   if ($ext === ".tar.gz") {
						      $old = umask(0000);
						      @$archive = PclTarExtract($source, $destination, 'gepi');
						      unlink($source);
//debug
// echo $archive[5][status];
						      // on liste le dossier
						      $copie_fichier = listage_dossier($destination, $destination);
						      // on transfert via FTP
						      $transfert_fichier=envoi_ftp($copie_fichier, $dossier_ftp_gepi);
						      umask($old);
						      // on supprime le dossier msj_temp
						      $old = umask(0000);
						      $dossier_destination[0]=$destination;
						      supprimer_rep($dossier_destination);
						      // puis on le recret
						      mkdir($destination, 0777);
					              umask($old);

							//mise � jour ok on l'ins�re dans la base
						     // puisque que c'est une nouvelle version on efface les donn�es de la base mise � jour
						     $requete='TRUNCATE TABLE '.$prefix_base.'miseajour';
						     $resultat = mysql_query($requete) or die('Erreur SQL !'.$requete.'<br />'.mysql_error());
						     // puis on informe la base de la version actuelle de la mise � jour
						     $requete='INSERT INTO '.$prefix_base.'miseajour (fichier_miseajour, emplacement_miseajour, date_miseajour, heure_miseajour) values ("'.$beta_version[0].'","","'.date('Y-m-d').'","'.date('H:i:s').'")';
					             $resultat = mysql_query($requete) or die('Erreur SQL !'.$requete.'<br />'.mysql_error());
//debug
//echo '<pre>';
//print_r($copie_fichier);
//echo '<pre>';
						   }
						   $_SESSION['message_traitement'] = 'mise � jour termin�e ! Pensez � vous d�connecter et vous reconnecter en administrateur apr�s cette mise � jour';
						   $_SESSION['message_erreur'] = '';
				 		   header("Location: fenetre.php?maj_type=".$maj_type);
					       }	   
		                     }
			  }
	         }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="fr">
<head>
<title>Mise � jour de GEPI</title>
<meta HTTP-EQUIV="Content-Type" content="text/html; charset=iso-8859-1" />
<META HTTP-EQUIV="Pragma" CONTENT="no-cache" />
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache" />
<META HTTP-EQUIV="Expires" CONTENT="0" />
<link rel="stylesheet" type="text/css" href="../../style.css" />

<script type="text/javascript">
//******************************************************************************
//   Composant de barre d'attente - jsWait
//   Vincent Fiack - 18/03/2003
//*******************************************************************************/
    
document.writeln('<div id="jsWaitMessage" style="font-family: Verdana; font-size: 10px; text-align: center; padding: 3px; position: absolute; left: 30%; top: 40%; height: 20px; width: 300px; z-index:3"><\/div>');
document.writeln('<div id="jsWaitArea" style="display: none; position: absolute; left: 30%; top: 40%; height: 20px; width: 300px; border: 1px black solid; background: #DFF1FF;z-index:2">');
document.writeln('<div id="jsWaitBlock" style="position: relative; left: 0px; height: 20px; width: 50px; background: #4A57EF;z-index:2"><\/div>' );
document.writeln('<\/div>');
 
jsWait_defaultInstance = null;
 
function showWait(message)
{
 jsWait_defaultInstance = new jsWait('jsWait_defaultInstance', message);
 jsWait_defaultInstance.show();
}
 
// -------------------------------------------
//        D�finition du type jsWait
// -------------------------------------------
 
/**
* Constructeur
* @param name le nom du composant
* @param message le message a afficher
*/
function jsWait(name, message)
{
 this.name = name;
 this.message = message;
 this.speed = 10;
 this.direction = 2;
 
 this.waiting = false;
 
 this.divMessage = document.getElementById("jsWaitMessage" );
 this.divArea = document.getElementById("jsWaitArea" );
 this.divBlock = document.getElementById("jsWaitBlock" );
}
 
 
// -------------------------------------------
//        M�thodes publiques
// -------------------------------------------
 
jsWait.prototype.show = function()
{
 this.divMessage.innerHTML = this.message;
 this.divMessage.style.display = "block";
 this.divArea.style.display = "block";
 this.divBlock.style.display = "block";
 this.divBlock.style.left = "0px";
 this.waiting = true;
 this.loop();
}
 
jsWait.prototype.setMessage = function(message)
{
 this.message = message;
 this.divMessage.innerHTML = this.message;
}
 
jsWait.prototype.stop = function()
{
 this.waiting = false;
 this.divMessage.style.display = "none";
 this.divArea.style.display = "none";
 this.divBlock.style.display = "none";
}
 
 
// -------------------------------------------
//        M�thodes priv�es
// -------------------------------------------
 
jsWait.prototype.loop = function()
{
 myLeft = this.divBlock.style.left;
 myLeft = myLeft.substring(0, myLeft.length-2);
 intLeft = parseInt(myLeft);
 
 if(intLeft >= 250)
   this.direction = -2;
 if(intLeft <= 0)
   this.direction = 2;
 
 myLeft = "" + (intLeft+this.direction) + "px";
 this.divBlock.style.left = myLeft;
 
 if(this.waiting)
   setTimeout(this.name + ".loop()", this.speed);
} 

function fermeFenetre() {
  window.open('','_parent','');
  window.close();
}

</script>
<style type="text/css">
/* affichage si erreur */
.erreur {
    color: #FFFFFF;
    font: normal 10pt Arial;
    text-align: center;
}

.info {
    color: #FFFFFF;
    font: normal 10pt Arial;
    text-align: center;
}

.table_erreur {
    width: 500px;
    margin: auto;
    background: #FF0022;
}

.table_info {
    width: 500px;
    margin: auto;
    background: #0075FF;
}
</style>
</head>
<body bgcolor="#FFFFFF">
<center>
<h2>Syst&egrave;me de mise &agrave; jour du logiciel</h2>
<div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: #5A7ACF;  padding: 2px; margin-left: 2px; margin-right: 2px; margin-top: 2px; margin-bottom: 0px;  text-align: left;">
   <div style="border-style:solid; border-width:0px; border-color: #6F6968; background-color: #5A7ACF;  padding: 2px; margin: 2px; font-family: Helvetica,Arial,sans-serif; font-weight: bold; color: #FFFFFF;">VERSION STABLE</div>
   <div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: #FFFFFF;  padding: 6px; margin: 2px;">
	<?php if($nouvelle_stable==='oui') { ?><div style="margin: 0px; padding: 0px; float: right; border-style:solid; border-width:1px; border-color: #6F6968; height: 25px;"><form action="fenetre.php?maj_logiciel=oui&amp;maj_type=stable" method="post" onSubmit="showWait('Mise � jour en cours...')"><input type="hidden" name="donnee[]" value="" /><input type="hidden" name="uid_post" value="<?php echo ereg_replace(' ','%20',$uid); ?>" /><input type="submit" value="Mettre � jour vers l� <?php echo $version_stable_serveur[1]; ?>" style="height: 25px; wight: auto;" onclick="return confirmlink(this,'Voulez vous le mettre � jour')" /></form></div><?php } ?>
	<span style=" font-family: Helvetica,Arial,sans-serif; font-weight: bold; color: #000000;">Votre version actuelle : <?php echo $version_stable_client[1]; ?></span><br />
	<span style="font-family: Helvetica,Arial,sans-serif; color: rgb(255, 0, 0); margin-left: 20px;"><?php echo $texte_stable; ?></span><br />
	<div style="font-family: Helvetica,Arial,sans-serif; color: rgb(0, 0, 0); margin-left: 20px; text-align: justify;">descriptif: <a href="<?php echo $changelog[1]; ?>" target="_blank">voir le changelog</a></div>
	<?php if(($message_traitement!='' or $message_erreur!='') and $maj_type==='stable') { ?>
	    <br />
            <table class="<?php if($message_erreur!='') { ?>table_erreur<?php } else { ?>table_info<?php } ?>" border="0" cellpadding="4" cellspacing="2">
              <tr>
                <td style="width: 28px;"><img src="<?php if($message_erreur!='') { ?>../../images/attention.png<?php } else { ?>../../images/info.png<?php } ?>" width="28" height="28" alt="" /></td>
                <td class="<?php if($message_erreur!='') { ?>erreur<?php } else { ?>info<?php } ?>"><strong><?php if($message_erreur!='') { echo $message_erreur; } else { echo $message_traitement; } ?></strong></td>
              </tr>
            </table>
	<?php } ?>
   </div>
</div>
<div style="border-style:solid; border-width:0px; border-color: #6F6968; background-color: #5A7ACF;  padding: 2px; margin-left: 20px; margin-right: 2px; margin-top: 0px; margin-bottom: 2px; text-align: left;">
   <div style="border-style:solid; border-width:0px; border-color: #6F6968; background-color: #5A7ACF;  padding: 0px; margin: 0px; font-family: Helvetica,Arial,sans-serif; font-weight: bold; color: #FFFFFF;">Fichier pouvant �tre mis � jour pour cet version.</div>
   <?php
	$donne_fichier = info_miseajour_fichier($site_de_miseajour, $version_stable_client[1]);
	$info_fichier_base = info_miseajour_base();
	$nb_a = '1';
while(!empty($donne_fichier['nom_fichier'][$nb_a])) { ?>
           <div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: #FFFFFF;  padding: 6px; margin: 2px;">
	   <?php
	   $identitification = $donne_fichier['emplacement_fichier'][$nb_a].''.$donne_fichier['nom_fichier'][$nb_a];
	   $amettreajour='non';
	   if(!empty($info_fichier_base[$identitification]['date']))
	   {
	       if($info_fichier_base[$identitification]['date']===$donne_fichier['date_fichier'][$nb_a])
	        { 
	  	    if($info_fichier_base[$identitification]['heure']===$donne_fichier['heure_fichier'][$nb_a]) { $amettreajour='non'; $info_etat='version du fichier � jour'; }
  		    if($info_fichier_base[$identitification]['heure'] < $donne_fichier['heure_fichier'][$nb_a]) { $amettreajour='oui'; $info_etat='une mise � jour du fichier est disponible'; }
		    if($info_fichier_base[$identitification]['heure'] > $donne_fichier['heure_fichier'][$nb_a]) { $amettreajour='non'; $info_etat='version du fichier � jour'; }
 	        }
	       if($info_fichier_base[$identitification]['date'] < $donne_fichier['date_fichier'][$nb_a]) { $amettreajour='oui'; $info_etat='une mise � jour du fichier est disponible'; }
	       if($info_fichier_base[$identitification]['date'] > $donne_fichier['date_fichier'][$nb_a]) { $amettreajour='non'; $info_etat='version du fichier � jour'; }
	   }
	   if(empty($info_fichier_base[$identitification]['date']))
	   {
	     $amettreajour='oui'; $info_etat='une mise � jour du fichier est disponible';
	   }
	?>
	<?php if($amettreajour==='oui') { ?><div style="margin: 0px; padding: 0px; float: right; border-style:solid; border-width:1px; border-color: #6F6968; height: 25px;"><form action="fenetre.php?maj_fichier=oui&amp;maj_type=fichier" method="post" onSubmit="showWait('Mise � jour en cours...')"><input type="hidden" name="uid_post" value="<?php echo ereg_replace(' ','%20',$uid); ?>" /><?php $tableau_select['source_fichier']['1'] = $donne_fichier['source_fichier'][$nb_a]; $tableau_select['nom_fichier']['1'] = $donne_fichier['nom_fichier'][$nb_a]; $tableau_select['emplacement_fichier']['1'] = $donne_fichier['emplacement_fichier'][$nb_a]; $tableau_select['date_fichier']['1'] = $donne_fichier['date_fichier'][$nb_a]; $tableau_select['heure_fichier']['1'] = $donne_fichier['heure_fichier'][$nb_a]; $tableau_select['md5_fichier']['1'] = $donne_fichier['md5_fichier'][$nb_a]; ?>
		<input type="hidden" name="source_fichier_select" value="<?php echo $donne_fichier['source_fichier'][$nb_a]; ?>" />
		<input type="hidden" name="nom_fichier_select" value="<?php echo $donne_fichier['nom_fichier'][$nb_a]; ?>" />
		<input type="hidden" name="emplacement_fichier_select" value="<?php echo $donne_fichier['emplacement_fichier'][$nb_a]; ?>" />
		<input type="hidden" name="date_fichier_select" value="<?php echo $donne_fichier['date_fichier'][$nb_a]; ?>" />
		<input type="hidden" name="heure_fichier_select" value="<?php echo $donne_fichier['heure_fichier'][$nb_a]; ?>" />
		<input type="hidden" name="md5_fichier_select" value="<?php echo $donne_fichier['md5_fichier'][$nb_a]; ?>" />
		<input type="submit" value="Mettre � jour le fichier" style="height: 25px; wight: auto;" /></form></div><?php } ?>
	<span style=" font-family: Helvetica,Arial,sans-serif; color: #000000;">Fichier <strong><?php echo $donne_fichier['nom_fichier'][$nb_a]; ?></strong> mise � jour du: <strong><?php echo $donne_fichier['date_fichier'][$nb_a].' '.$donne_fichier['heure_fichier'][$nb_a]; ?></strong></span><br />
	<span style="font-family: Helvetica,Arial,sans-serif; color: rgb(255, 0, 0); margin-left: 20px;"><?php echo $info_etat; ?></span><br />
	<div style="font-family: Helvetica,Arial,sans-serif; color: rgb(0, 0, 0); margin-left: 20px; text-align: justify;">descriptif: <?php echo $donne_fichier['descriptif_fichier'][$nb_a]; ?></div>
   </div>
<?php $amettreajour='non'; $info_etat=''; $nb_a++; } ?>
</div>

<?php 
if($affiche_info_rc==='oui')
{ ?>
<br />
<div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: rgb(255, 0, 0);  padding: 2px; margin-left: 2px; margin-right: 2px; margin-top: 2px; margin-bottom: 0px;  text-align: left;">
   <div style="border-style:solid; border-width:0px; border-color: #6F6968; background-color: rgb(255, 0, 0);  padding: 2px; margin: 2px; font-family: Helvetica,Arial,sans-serif; font-weight: bold; color: #FFFFFF;">VERSION RC</div>
   <div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: #FFFFFF;  padding: 6px; margin: 2px;">
	<?php if($nouvelle_rc==='oui') { ?><div style="margin: 0px; padding: 0px; float: right; border-style:solid; border-width:1px; border-color: #6F6968; height: 25px;"><form action="fenetre.php?maj_logiciel=oui&amp;maj_type=stable" method="post" onSubmit="showWait('Mise � jour en cours...')"><input type="hidden" name="donnee[]" value="" /><input type="hidden" name="uid_post" value="<?php echo ereg_replace(' ','%20',$uid); ?>" /><input type="submit" value="Passer en RC <?php echo $version_rc_serveur[1]; ?>" style="height: 25px; wight: auto;" onclick="return confirmlink(this,'Voulez vous le mettre � jour')" /></form></div><?php } ?>
	<span style=" font-family: Helvetica,Arial,sans-serif; font-weight: bold; color: #000000;">Version RC de GEPI a installer: <?php if($version_rc_client[1]!='') { echo 'RC '.$version_rc_client[1]; } else { echo 'aucune RC install�'; } ?></span><br />
	<span style="font-family: Helvetica,Arial,sans-serif; color: rgb(255, 0, 0); margin-left: 20px;"><?php echo $texte_rc; ?></span><br />
	<div style="font-family: Helvetica,Arial,sans-serif; color: rgb(0, 0, 0); margin-left: 20px; text-align: justify;">descriptif: <a href="<?php echo $changelog[1]; ?>" target="_blank">voir le changelog</a></div>
	<?php if(($message_traitement!='' or $message_erreur!='') and $maj_type==='rc') { ?>
	    <br />
            <table class="<?php if($message_erreur!='') { ?>table_erreur<?php } else { ?>table_info<?php } ?>" border="0" cellpadding="4" cellspacing="2">
              <tr>
                <td style="width: 28px;"><img src="<?php if($message_erreur!='') { ?>../../images/attention.png<?php } else { ?>../../images/info.png<?php } ?>" width="28" height="28" alt="" /></td>
                <td class="<?php if($message_erreur!='') { ?>erreur<?php } else { ?>info<?php } ?>"><strong><?php if($message_erreur!='') { echo $message_erreur; } else { echo $message_traitement; } ?></strong></td>
              </tr>
            </table>
	<?php } ?>
   </div>
</div>
<?php } ?>

<?php 
if($affiche_info_beta==='oui')
{ ?>
<br />
<div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: rgb(255, 0, 0);  padding: 2px; margin-left: 2px; margin-right: 2px; margin-top: 2px; margin-bottom: 0px;  text-align: left;">
   <div style="border-style:solid; border-width:0px; border-color: #6F6968; background-color: rgb(255, 0, 0);  padding: 2px; margin: 2px; font-family: Helvetica,Arial,sans-serif; font-weight: bold; color: #FFFFFF;">VERSION BETA</div>
   <div style="border-style:solid; border-width:1px; border-color: #6F6968; background-color: #FFFFFF;  padding: 6px; margin: 2px;">
	<?php if($nouvelle_beta==='oui') { ?><div style="margin: 0px; padding: 0px; float: right; border-style:solid; border-width:1px; border-color: #6F6968; height: 25px;"><form action="fenetre.php?maj_logiciel=oui&amp;maj_type=stable" method="post" onSubmit="showWait('Mise � jour en cours...')"><input type="hidden" name="donnee[]" value="" /><input type="hidden" name="uid_post" value="<?php echo ereg_replace(' ','%20',$uid); ?>" /><input type="submit" value="Passer en BETA <?php echo $version_beta_serveur[1]; ?>" style="height: 25px; wight: auto;" onclick="return confirmlink(this,'Voulez vous le mettre � jour')" /></form></div><?php } ?>
	<span style=" font-family: Helvetica,Arial,sans-serif; font-weight: bold; color: #000000;">Version BETA de GEPI a installer: <?php if($version_beta_client[1]!='') { echo 'BETA '.$version_beta_client[1]; } else { echo 'aucune'; } ?></span><br />
	<span style="font-family: Helvetica,Arial,sans-serif; color: rgb(255, 0, 0); margin-left: 20px;"><?php echo $texte_beta; ?></span><br />
	<div style="font-family: Helvetica,Arial,sans-serif; color: rgb(0, 0, 0); margin-left: 20px; text-align: justify;">descriptif: <a href="<?php echo $changelog[1]; ?>" target="_blank">voir le changelog</a></div>
	<?php if(($message_traitement!='' or $message_erreur!='') and $maj_type==='beta') { ?>
	    <br />
            <table class="<?php if($message_erreur!='') { ?>table_erreur<?php } else { ?>table_info<?php } ?>" border="0" cellpadding="4" cellspacing="2">
              <tr>
                <td style="width: 28px;"><img src="<?php if($message_erreur!='') { ?>../../images/attention.png<?php } else { ?>../../images/info.png<?php } ?>" width="28" height="28" alt="" /></td>
                <td class="<?php if($message_erreur!='') { ?>erreur<?php } else { ?>info<?php } ?>"><strong><?php if($message_erreur!='') { echo $message_erreur; } else { echo $message_traitement; } ?></strong></td>
              </tr>
            </table>
	<?php } ?>
   </div>
</div>
<?php } ?>
<br /><br /><a href="javascript:window.close();">Fermer la fen�tre</a>
</center>
</body>
</html>
<?php } else { echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html lang="fr"><head><title>Erreur</title></head><body><br /><br /><br /><center><strong>mise � jour impossible</strong><br />le serveur de mise � jour n\'est pas disponible</center></body></html>'; } ?>
<?php mysql_close(); ?>
