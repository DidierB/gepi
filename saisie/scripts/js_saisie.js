/**
 * Fonction qui r�cup�re l'appr�ciation d'un textarea pr�cis pour le sauvegarder
 * @access public
 * @return void
 **/
function ajaxAppreciations(eleveperiode, enseignement, textId){
	var essai = $(textId);
	var contenu = $F(textId);
	var url = "ajax_appreciations.php";
	o_options = new Object();

	// beaucoup de choses restent � revoir

	o_options = {postBody: 'var1='+eleveperiode+'&var2='+enseignement+'&var3='+contenu};
	var laRequete = new Ajax.Request(url,o_options);
	//alert(enseignement+' \n'+eleveperiode+' \n'+textId+' \n Essai = ' +essai+' \nContenu = '+contenu);
}