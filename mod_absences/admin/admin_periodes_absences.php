<?php
/*
 * Copyright 2001, 2002 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Christian Chapel
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
//mes fonctions
include("../lib/functions.php");

// Resume session
$resultat_session = resumeSession();
if ($resultat_session == 'c') {
    header("Location: ../../utilisateurs/mon_compte.php?change_mdp=yes");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../../logout.php?auto=1");
    die();
};

// Check access
if (!checkAccess()) {
    header("Location: ../../logout.php?auto=1");
    die();
}
// header
$titre_page = "D�finition des cr�neaux horaires";
require_once("../../lib/header.inc");

	if (empty($_GET['action_sql']) and empty($_POST['action_sql'])) {$action_sql="";}
	   else { if (isset($_GET['action_sql'])) {$action_sql=$_GET['action_sql'];} if (isset($_POST['action_sql'])) {$action_sql=$_POST['action_sql'];} }
	if (empty($_GET['action']) and empty($_POST['action'])) {exit();}
	   else { if (isset($_GET['action'])) {$action=$_GET['action'];} if (isset($_POST['action'])) {$action=$_POST['action'];} }
	if (empty($_GET['id_periode']) and empty($_POST['id_periode'])) { $id_periode="";}
	   else { if (isset($_GET['id_periode'])) {$id_periode=$_GET['id_periode'];} if (isset($_POST['id_periode'])) {$id_periode=$_POST['id_periode'];} }
	if (empty($_GET['nb_ajout']) and empty($_POST['nb_ajout'])) { $nb_ajout="1";}
	   else { if (isset($_GET['nb_ajout'])) {$nb_ajout=$_GET['nb_ajout'];} if (isset($_POST['nb_ajout'])) {$nb_ajout=$_POST['nb_ajout'];} }
	if (empty($_GET['nom_definie_periode']) and empty($_POST['nom_definie_periode'])) { $nom_definie_periode=""; }
	   else { if (isset($_GET['nom_definie_periode'])) {$nom_definie_periode=$_GET['nom_definie_periode'];} if (isset($_POST['nom_definie_periode'])) {$nom_definie_periode=$_POST['nom_definie_periode'];} }
	if (empty($_GET['heuredebut_definie_periode']) and empty($_POST['heuredebut_definie_periode'])) { $heuredebut_definie_periode="";}
	   else { if (isset($_GET['heuredebut_definie_periode'])) {$heuredebut_definie_periode=$_GET['heuredebut_definie_periode'];} if (isset($_POST['heuredebut_definie_periode'])) {$heuredebut_definie_periode=$_POST['heuredebut_definie_periode'];} }
	if (empty($_GET['heurefin_definie_periode']) and empty($_POST['heurefin_definie_periode'])) { $heurefin_definie_periode="";}
	   else { if (isset($_GET['heurefin_definie_periode'])) {$heurefin_definie_periode=$_GET['heurefin_definie_periode'];} if (isset($_POST['heurefin_definie_periode'])) {$heurefin_definie_periode=$_POST['heurefin_definie_periode'];} }
	if (empty($_GET['suivi_definie_periode']) and empty($_POST['suivi_definie_periode'])) { $suivi_definie_periode = ''; }
	   else { if (isset($_GET['suivi_definie_periode'])) { $suivi_definie_periode = $_GET['suivi_definie_periode']; } if (isset($_POST['suivi_definie_periode'])) { $suivi_definie_periode = $_POST['suivi_definie_periode']; } }


$total = '0'; $verification[0] = '1'; $erreur = '0';

if ($action_sql == "ajouter" or $action_sql == "modifier")
{
   while ($total < $nb_ajout)
      {
            // V�rifcation des variable
              $nom_definie_periode_ins = $_POST['nom_definie_periode'][$total];
              $heuredebut_definie_periode_ins = $_POST['heuredebut_definie_periode'][$total];
              $heurefin_definie_periode_ins = $_POST['heurefin_definie_periode'][$total];
	      $suivi_definie_periode_ins = $_POST['suivi_definie_periode'][$total];
              if ($action_sql == "modifier") { $id_definie_periode_ins = $_POST['id_periode'][$total]; }

            // V�rification des champs nom et prenom (si il ne sont pas vides ?)
            if($nom_definie_periode_ins != "" && $heuredebut_definie_periode_ins != "" && $heurefin_definie_periode_ins != "")
            {
                if($heuredebut_definie_periode_ins != "00:00")
                 {
                     if($heurefin_definie_periode_ins != "00:00")
                     {
                         if($heurefin_definie_periode_ins > $heuredebut_definie_periode_ins)
                         {
                            if($action_sql == "ajouter") { $test = mysql_result(mysql_query("SELECT count(*) FROM ".$prefix_base."absences_creneaux WHERE nom_definie_periode='$nom_definie_periode_ins' OR (heuredebut_definie_periode='$heuredebut_definie_periode_ins' AND heurefin_definie_periode='$heurefin_definie_periode_ins')"),0); }
                            if($action_sql == "modifier") { $test = mysql_result(mysql_query("SELECT count(*) FROM ".$prefix_base."absences_creneaux WHERE id_definie_periode != '$id_definie_periode_ins' AND (nom_definie_periode='$nom_definie_periode_ins' OR (heuredebut_definie_periode='$heuredebut_definie_periode_ins' AND heurefin_definie_periode='$heurefin_definie_periode_ins'))"),0); }
                              if ($test == "0")
                              {
                                 if($action_sql == "ajouter")
                                  {
                                     // Requete d'insertion MYSQL
                                        $requete = "INSERT INTO ".$prefix_base."absences_creneaux (nom_definie_periode,heuredebut_definie_periode,heurefin_definie_periode,suivi_definie_periode) VALUES ('$nom_definie_periode_ins','$heuredebut_definie_periode_ins','$heurefin_definie_periode_ins', '$suivi_definie_periode_ins')";
                                  }
                                 if($action_sql == "modifier")
                                  {
                                     // Requete de mise � jour MYSQL
                                        $requete = "UPDATE ".$prefix_base."absences_creneaux SET
                                                        nom_definie_periode = '$nom_definie_periode_ins',
                                                        heuredebut_definie_periode = '$heuredebut_definie_periode_ins',
                                                        heurefin_definie_periode = '$heurefin_definie_periode_ins',
							suivi_definie_periode = '$suivi_definie_periode_ins'
                                                        WHERE id_definie_periode = '".$id_definie_periode_ins."' ";
                                  }
                                // Execution de cette requete dans la base cartouche
                                  mysql_query($requete) or die('Erreur SQL !'.$sql.'<br />'.mysql_error());
                                  $verification[$total] = 1;
                              } else {
                                        // v�rification = 2 - Ce cr�neaux horaires existe d�jas
                                         $verification[$total] = 2;
                                         $erreur = 1;
                                     }
                         } else {
                                  // v�rification = 5 - L'heure de fin n'est pas d�finie
                                    $verification[$total] = 6;
                                    $erreur = 1;
                                }
                       } else {
                                 // v�rification = 5 - L'heure de fin n'est pas d�finie
                                   $verification[$total] = 5;
                                   $erreur = 1;
                              }
                   } else {
                             // v�rification = 4 - L'heure de d�but n'est pas d�finie
                               $verification[$total] = 4;
                               $erreur = 1;
                          }
            } else {
                     // v�rification = 3 - Tous les champs ne sont pas remplie
                     $verification[$total] = 3;
                     $erreur = 1;
                   }
      $total = $total + 1;
      }

      if($erreur == 0)
       {
          $action = "visualiser";
       } else {
                 $o = 0;
                 $n = 0;
                 while ($o < $nb_ajout)
                  {
                    if($verification[$o] != 1)
                     {
                        $nom_definie_periode_erreur[$n] = $nom_definie_periode[$o];
                        $heuredebut_definie_periode_erreur[$n] = $heuredebut_definie_periode[$o];
                        $heurefin_definie_periode_erreur[$n] = $heurefin_definie_periode[$o];
                        $verification_erreur[$n] = $verification[$o];
                        if ($action_sql == "modifier") { $id_definie_periode_erreur[$n] = $id_periode[$o]; }
                        $n = $n + 1;
                     }
                     $o = $o + 1;
                  }
                  $nb_ajout = $n;
                  if ($action_sql == "ajouter") { $action = "ajouter"; }
                  if ($action_sql == "modifier") { $action = "modifier"; }
              }
}

if ($action_sql == "supprimer")
 {
     //Requete d'insertion MYSQL
     $requete = "DELETE FROM ".$prefix_base."absences_creneaux WHERE id_definie_periode ='$id_periode'";
     // Execution de cette requete
     mysql_query($requete) or die('Erreur SQL !'.$requete.'<br />'.mysql_error());
 }

if ($action == "modifier")
 {
      $requete_modif_periode = 'SELECT * FROM '.$prefix_base.'absences_creneaux WHERE id_definie_periode="'.$id_periode.'"';
      $resultat_modif_periode = mysql_query($requete_modif_periode) or die('Erreur SQL !'.$requete_modif_periode.'<br />'.mysql_error());
      $data_modif_periode = mysql_fetch_array($resultat_modif_periode);
 }


?>
<p class=bold>|
<a href="../../accueil.php">Accueil</a>|
<a href="../../accueil_modules.php">Retour administration des modules</a>|
<a href='index.php'>Retour module absence</a>|
<?php if ($action=="modifier" or $action=="ajouter") echo "<a href=\"admin_periodes_absences.php?action=visualiser\">Retour accueil cr�neaux horaires</a>"; ?>
<?php if ($action=="visualiser") echo "<a href=\"admin_periodes_absences.php?action=ajouter\">Ajouter un cr�neaux horaires</a>";?>|



</p>
<?php if ($action == "visualiser") { ?>
<? /* div de centrage du tableau pour ie5 */ ?>
<div style="text-align:center">
    <table style="border-style:solid; border-width:0px; border-color: #6F6968;" cellpadding="0" cellspacing="1" class="tableau_moyen_centre">
      <tr>
        <td colspan="5" class="fond_bleu_2"><div class="norme_absence_gris_bleu"><strong>D&eacute;finition des cr�neaux horaires</strong></div></td>
      </tr>
      <tr>
        <th class="tableau_moyen_centre_th">cr�neaux</th>
        <th class="tableau_moyen_centre_th">heure de d&eacute;but</th>
        <th class="tableau_moyen_centre_th">heure de fin</th>
        <th class="tableau_moyen_centre_th_25"></th>
        <th class="tableau_moyen_centre_th_25"></th>
      </tr>
    <?php
    $requete_periode = 'SELECT * FROM '.$prefix_base.'absences_creneaux ORDER BY heuredebut_definie_periode, nom_definie_periode ASC';
    $execution_periode = mysql_query($requete_periode) or die('Erreur SQL !'.$requete_periode.'<br />'.mysql_error());
    $i=1;
    while ( $data_periode = mysql_fetch_array( $execution_periode ) ) {
       if ($i === '1') { $i = '2'; $couleur_cellule = 'fond_bleu_3'; } else { $couleur_cellule = 'fond_bleu_4'; $i = '1'; } ?>
        <tr class="<?php echo $couleur_cellule; ?>">
          <td class="centre"><?php echo $data_periode['nom_definie_periode']; ?></td>
         <td class="centre"><?php echo $data_periode['heuredebut_definie_periode']; ?></td>
          <td class="centre"><?php echo $data_periode['heurefin_definie_periode']; ?></td>
          <td class="centre"><a href="admin_periodes_absences.php?action=modifier&amp;id_periode=<?php echo $data_periode['id_definie_periode']; ?>"><img src="../images/modification.png" width="18" height="22" title="Modifier" border="0" alt="Modifier" /></a></td>
          <td class="centre"><a href="admin_periodes_absences.php?action=visualiser&amp;action_sql=supprimer&amp;id_periode=<?php echo $data_periode['id_definie_periode']; ?>" onClick="return confirm('Etes-vous sur de vouloire le supprimer...')"><img src="../images/x2.png" width="22" height="22" title="Supprimer" border="0" alt="Supprimer" /></a></td>
        </tr>
     <?php } ?>
    </table>
<? /* fin du div de centrage du tableau pour ie5 */ ?>
</div>
<?php } ?>


<?php if ($action == "ajouter" or $action == "modifier") { ?>
  <?php if ($action == "ajouter") { ?>
<? /* div de centrage du tableau pour ie5 */ ?>
<div style="text-align:center">
    <form name="form1" method="post" action="admin_periodes_absences.php?action=ajouter">
     <fieldset class="fieldset_efface">
      <table border="0" cellpadding="2" cellspacing="2" class="tableau_moyen_centre">
        <tr class="fond_bleu_2">
          <td class="norme_absence_gris_bleu"><b>Nombre de cr�neaux horaires &agrave; ajouter</b></td>
        </tr>
        <tr class="adroite">
          <td><input name="nb_ajout" type="text" size="5" maxlength="5" value="<?php if(isset($nb_ajout)) { echo $nb_ajout; } else { ?>1<?php } ?>" />&nbsp;&nbsp;&nbsp;<input type="submit" name="Submit2" value="Cr&eacute;er" /></td>
        </tr>
      </table>
     </fieldset>
    </form>
  <?php } ?>
    <form action="admin_periodes_absences.php?action=visualiser&amp;action_sql=<?php if($action=="ajouter") { ?>ajouter<?php } if($action=="modifier") { ?>modifier<?php } ?>" method="post" name="form2" id="form2">
     <fieldset class="fieldset_efface">
      <table border="0" cellpadding="2" cellspacing="2" class="tableau_moyen_centre ">
        <tr class="fond_bleu_2">
          <td colspan="4" class="norme_absence_gris_bleu"><b><?php if($action=="ajouter") { ?>Ajout d'un ou plusieurs cr�neau(x) horaire(s)<?php } if($action=="modifier") { ?>Modifier des cr�neaux horaires<?php } ?></b></td>
        </tr>
        <tr class="fond_bleu_2">
          <td class="norme_absence_gris_bleu_centre">Cr�neau</td>
          <td class="norme_absence_gris_bleu_centre">Heure de d&eacute;but</td>
          <td class="norme_absence_gris_bleu_centre">Heure de fin</td>
          <td class="norme_absence_gris_bleu_centre">Suite logique</td>
        </tr>
        <?php
        $i = 2;
        $nb = 0;
        while($nb < $nb_ajout) {
        if ($i === '1') { $i = '2'; $couleur_cellule="fond_bleu_3"; } else { $couleur_cellule="fond_bleu_4"; $i = '1'; } ?>
        <?php if (isset($verification_erreur[$nb]) and $verification_erreur[$nb] != 1) { ?>
         <tr>
          <td><img src="../images/attention.png" width="28" height="28" alt="" /></td>
          <td colspan="3" class="erreur_rouge_jaune"><b>- Erreur -<br />
          <?php if ($verification_erreur[$nb] == 2) { ?>Ce cr�neau horaire existe d�j�<?php } ?>
          <?php if ($verification_erreur[$nb] == 5) { ?>L'heure de fin n'est pas d�finie<?php } ?>
          <?php if ($verification_erreur[$nb] == 4) { ?>L'heure de d�but n'est pas d�finie<?php } ?>
          <?php if ($verification_erreur[$nb] == 3) { ?>Tous les champs ne sont pas remplie<?php } ?>
          <?php if ($verification_erreur[$nb] == 6) { ?>L'heure de fin ne peut pas �tre plus petite que l'heure de d�but<?php } ?>
          </b><br /></td>
         </tr>
        <?php } ?>
        <tr class="<?php echo $couleur_cellule; ?>">
          <td class="centre"><input name="nom_definie_periode[<?php echo $nb; ?>]" type="text" id="nom_definie_periode" size="10" maxlength="10" value="<?php if($action=="modifier") { echo $data_modif_periode['nom_definie_periode']; } elseif (isset($nom_definie_periode_erreur[$nb])) { echo $nom_definie_periode_erreur[$nb]; } ?>" /></td>
          <td class="centre"><input name="heuredebut_definie_periode[<?php echo $nb; ?>]" type="text" id="heuredebut_definie_periode" size="5" maxlength="5" value="<?php if($action=="modifier") { echo $data_modif_periode['heuredebut_definie_periode']; } elseif (isset($heuredebut_definie_periode_erreur[$nb])) { echo $heuredebut_definie_periode_erreur[$nb]; } else { ?>00:00<?php } ?>" /></td>
          <td class="centre"><input name="heurefin_definie_periode[<?php echo $nb; ?>]" type="text" id="heurefin_definie_periode" size="5" maxlength="5" value="<?php if($action=="modifier") { echo $data_modif_periode['heurefin_definie_periode']; } elseif (isset($heurefin_definie_periode_erreur[$nb])) { echo $heurefin_definie_periode_erreur[$nb]; } else { ?>00:00<?php } ?>" /></td>
	  <td class="centre"><input name="suivi_definie_periode[<?php echo $nb; ?>]" value="1" type="checkbox" <?php if ( $action === 'modifier' and $data_modif_periode['suivi_definie_periode'] === '1' ) { ?>checked="checked"<?php } ?> /></td>
        </tr>
            <?php if($action=="modifier") { ?>
              <input type="hidden" name="id_periode[<?php echo $nb; ?>]" value="<?php if (isset($id_definie_periode_erreur[$nb])) { echo $id_definie_periode_erreur[$nb]; } else { echo $id_periode; } ?>" />
            <?php } ?>
        <?php $nb = $nb + 1; } ?>
        <tr>
          <td colspan="4" class="adroite">
              <input type="hidden" name="nb_ajout" value="<?php echo $nb_ajout; ?>" />
              <input type="submit" name="Submit" value="<?php if($action=="ajouter") { ?>Cr�er cr�neau(x) horaire(s)<?php } if($action=="modifier") { ?>Modifier le cr�neau horaire<?php } ?>" />
          </td>
        </tr>
      </table>
     </fieldset>
    </form>
<? /* fin du div de centrage du tableau pour ie5 */ ?>
</div>
<?php mysql_close(); } ?>

