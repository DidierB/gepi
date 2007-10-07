<?php
	// ========================================
	// Astuce http://www.ehow.com/how_2000413_convert-em-px-sizes.html
	// pour calculer le rapport em/px et corriger le positionnement des infobulles (taille fix�e en 'em')
	echo "<div id='EmSize' style='visibility:hidden; position:absolute; left:1em; top:1em;'></div>

	<script type='text/javascript'>
		var ele=document.getElementById('EmSize');
		var em2px=ele.offsetLeft
		//alert('1em == '+em2px+'px');
	</script>\n";
	// ========================================



	// On a stock� les DIV dans un tableau et on parcourt le tableau PHP en fin de page pour afficher les infobulles en dehors du coeur de la page.
	// Les infobulles apparaissent ainsi un peu comme des notes de bas de page.
	// On pourrait cependant ins�rer l'infobulle au milieu du texte avec:
	//       echo creer_div("div1","1er DIV","Test de petit texte",12,"y","y","n");
	// au risque de perturber l'affichage de la page si Javascript est d�sactiv�.

	if(count($tabdiv_infobulle)>0){
		for($i=0;$i<count($tabdiv_infobulle);$i++){
			echo $tabdiv_infobulle[$i]."\n";
		}
	}

	// T�moin destin� � tester la fin de chargement de la page pour �viter des erreurs JavaScript avant la fin de chargement de tous les �l�ments.
	// Par exemple: la fonction cacher_div() utilis�e plus bas teste cette variable pour ne tenter les op�rations que si la variable est � 'ok'
	echo "<script type='text/javascript'>
	temporisation_chargement='ok';
</script>\n";

	if(count($tabid_infobulle)>0){
		// On cache les DIV en fin de chargement de la page (il faut qu'ils existent pour qu'il soit possible de les cacher).
		// Il me semble qu'il n'est pas possible d'initialiser le 'display' � 'none' et de modifier ce display ensuite via JavaScript.
		echo "<script type='text/javascript'>\n";
		for($i=0;$i<count($tabid_infobulle);$i++){
			echo "cacher_div('".$tabid_infobulle[$i]."');\n";
		}
		echo "</script>\n";

		/*
		// Remarques:
		echo "<p><i>Remarque:</i></p><blockquote><p>Pour tester l'effet infobulle-&gt;note de bas de page, d�sactivez JavaScript et rechargez la page.<br />Par exemple, avec l'extension WebDevelopper de Firefox, cliquez sur la barre: Disable/Disable javascript/All javascripts et rechargez la page.</p></blockquote>\n";
		*/

		/*
		// Pour afficher le code source de la page:
		echo "<div style='width: 800px; border: 1px solid black;'>\n";
		show_source($_SERVER['SCRIPT_FILENAME']);
		echo "</div>\n";
		*/
	}

	if(getSettingValue("gepi_pmv")!="n"){
		if (file_exists($gepiPath."/pmv.php")) require ($gepiPath."/pmv.php");
	}
?>
</div>
</body>
</html>