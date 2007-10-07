function position(e) {
	// Acquisition de la position de la souris et affectation des variables xMousePos et yMousePos
	if (navigator.appName.substring(0,3) == "Net") {
		xMousePos = e.pageX;
		yMousePos = e.pageY;
	}
	else {
		xMousePos = event.x+document.body.scrollLeft;
		yMousePos = event.y+document.body.scrollTop;
	}

	/*
	if(temporisation_chargement=="ok"){
		window.status="xMousePos="+xMousePos+" yMousePos="+yMousePos+" pageYOffset="+pageYOffset+" pageXOffset="+pageXOffset
		//window.status="em2px="+em2px
	}
	*/
	//temporisation=true;
}

if(navigator.appName.substring(0,3) == "Net") document.captureEvents(Event.MOUSEMOVE);

document.onmousemove = position;

function afficher_div(id,positionner,dx,dy) {
	// id: identifiant du DIV
	// positionner: 'y' ou 'n'
	//              Avec 'y', le DIV est positionn� d'apr�s la position de la souris.
	//              Avec 'n', le DIV est affich� � sa position initiale indiqu�e dans le style
	// dx: d�calage en abscisse par rapport � la position de la souris
	// dy: d�calage en ordonn�e par rapport � la position de la souris

	if(temporisation_chargement=="ok"){
		if(positionner=='y'){

			tmp_x=xMousePos
			tmp_y=yMousePos

			// Correction de la position horizontale si le DIV sort de la fen�tre:
			largeur_div=document.getElementById(id).style.width
			//alert("largeur_div="+largeur_div)
			// PROBLEME: La largeur du DIV est donn�e en 'em' et ici il faudrait des 'px'...
			if(browser.isIE){
				// Rien � faire...
			}
			else{
				// On supprime le suffixe 'px'
				largeur_div=largeur_div.substring(0,largeur_div.length-2)
			}
			largeur_div=eval(largeur_div)
			// em2px est calcul�e dans le footer.inc.php
			largeur_div=largeur_div*em2px

			//if(eval(largeur_div+tmp_x)>window.innerWidth){
			if(eval(largeur_div+tmp_x-pageXOffset)>window.innerWidth){
				tmp_x=Math.max(xMousePos-2*dx-largeur_div,0)
			}

			// Correction de la position verticale si le DIV sort de la fen�tre:
			hauteur_div=document.getElementById(id).style.height
			// Probl�me, on ne r�cup�re pas la hauteur du DIV si la propri�t� n'a pas �t� fix�e dans la partie style='' (souvent on ne fixe pas cette valeur pour laisser le DIV s'ajuster/r�duire jusqu'au minimum requis en hauteur)
			if(hauteur_div==0){
				hauteur_div=100
			}
			//if(eval(tmp_y+hauteur_div+dx)>window.innerHeight){
			if(eval(tmp_y+hauteur_div+dx-pageYOffset)>window.innerHeight){
				tmp_y=Math.max(yMousePos-2*dy-hauteur_div,0)
			}

			if(browser.isIE){
				//document.getElementById(id).style.top=yMousePos+dy;
				//document.getElementById(id).style.left=xMousePos+dx;
				document.getElementById(id).style.top=Math.max(tmp_y+dy,0);
				document.getElementById(id).style.left=Math.max(tmp_x+dx,0);
			}
			else{
				//document.getElementById(id).style.top=yMousePos+dy+'px';
				//document.getElementById(id).style.left=xMousePos+dx+'px';
				document.getElementById(id).style.top=Math.max(tmp_y+dy,0)+'px';
				document.getElementById(id).style.left=Math.max(tmp_x+dx,0)+'px';
			}
		}
		document.getElementById(id).style.display='';
	}
}

function cacher_div(id) {
	// id: identifiant du DIV

	if(temporisation_chargement=="ok"){
		document.getElementById(id).style.display='none';
	}
}
