<?PHP
//il reste � traiter le num�ro RNE de l'�tablissement
//==> session si multi site ???

//Les dossiers contenant les mod�les Gepi et mes mod�les. Il faut un / � la fin du chemin.
//$rne='0610559L/';
$rne='';
$nom_dossier_modeles_ooo_par_defaut='modeles_gepi/'; 
$nom_dossier_modeles_ooo_mes_modeles='mes_modeles/';

//traitement du mod�le (par defaut ou mod�le personnel ) On fait un test  sur l'existance du fichier dans le dossier mes_modeles/
 if  (file_exists($nom_dossier_modeles_ooo_mes_modeles.$rne.$nom_fichier_modele_ooo))   {
    $nom_dossier_modele_a_utiliser = $nom_dossier_modeles_ooo_mes_modeles.$rne; //Le dossier dans mes mod�les et eventuellement RNE
 } else {
    $nom_dossier_modele_a_utiliser = $nom_dossier_modeles_ooo_par_defaut; //le dossier contenant les mod�les par defaut.
 }
 
//echo $nom_dossier_modele_a_utiliser;

?>