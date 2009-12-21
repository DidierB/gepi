<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
/*
 * $Id: gestion_generale_template.php $
*/
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>

<!-- on inclut l'ent�te -->
	<?php include('./../templates/origine/header_template.php');?>
	
	<link rel="stylesheet" type="text/css" href="./../accessibilite.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="./../accessibilite_print.css" media="print" />

	<link rel="stylesheet" type="text/css" href="./../templates/origine/css/accueil.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="./../templates/origine/css/bandeau.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="./../templates/origine/css/gestion_generale.css" media="screen" />
	
<!-- corrections internet Exploreur -->	
	<!--[if lte IE 7]>
		<link title='bandeau' rel='stylesheet' type='text/css' href='./templates/origine/css/accueil_ie.css' media='screen' />
		<link title='bandeau' rel='stylesheet' type='text/css' href='./templates/origine/css/bandeau_ie.css' media='screen' />
	<![endif]-->
	<!--[if lte IE 6]>
		<link title='bandeau' rel='stylesheet' type='text/css' href='./templates/origine/css/accueil_ie6.css' media='screen' />
	<![endif]-->
	<!--[if IE 7]>
		<link title='bandeau' rel='stylesheet' type='text/css' href='./templates/origine/css/accueil_ie7.css' media='screen' />
	<![endif]-->

<!-- Fin des styles -->
</head>

<body onload="show_message_deconnexion();">	

<!-- on inclut le bandeau -->
	<?php include('./../templates/origine/bandeau_template.php');?>
	
<!-- fin bandeau_template.html      -->

<div id='container'>
	<p class='ariane'>
		<a href="<?php echo $tbs_ariane[0]['lien'];?>">
			<?php echo $tbs_ariane[0]['titre'];?>
		</a>
	</p>
	<p class='bold'>
		<a href="<?php echo $tbs_retour;?>">
			<img src='../images/icons/back.png' alt='Retour' class='back_link' /> Retour
		</a>
	</p>

<a name="contenu" class="invisible">D�but de la page</a>	
	
<?php	
				if (count($tbs_menu)) {
				$menu=array_values($tbs_menu);
				if ($menu[0]['texte']!="") {
					foreach ($tbs_menu as $value) {
						echo "
	<h2 class='$value[classe]' style='margin-bottom:0;'> 
		<img src='$value[image]' alt='' /> - $value[texte]
	</h2>
				";
						if ($value['texte']=="Administration") {
							echo "
<!-- sauvegarde -->	
	<div class=\"div_tableau cellule_1\">
		<form enctype=\"multipart/form-data\" action=\"gestion/accueil_sauve.php\" method=\"post\" id=\"formulaire\" >
			<p>
				<input type='hidden' name='action' value='system_dump' />
				<input type=\"submit\" value=\"Lancer une sauvegarde de la base de donn�es\" />
			</p>
		</form>
		<p class='small'>
			Les r�pertoires \"documents\" (<em>contenant les documents joints aux cahiers de texte</em>) et \"photos\" (<em>contenant les photos du trombinoscope</em>) ne seront pas sauvegard�s.<br />
			Un outil de sauvegarde sp�cifique se trouve en bas de la page <a href='./gestion/accueil_sauve.php#zip'>gestion des sauvegardes</a>.
		</p>
	</div>	
				";
						}
							echo "
<!-- autres menus -->		
<!-- accueil_menu_template.php -->
				";
						if (count($value['entree'])) {
							foreach ($value['entree'] as $newentree) {
								include('./../templates/origine/accueil_menu_template.php');
							}
						}
							echo "
<!-- Fin menu	g�n�ral -->
				";
					}
				}
			}
	?>
</div>
</body>
</html>
