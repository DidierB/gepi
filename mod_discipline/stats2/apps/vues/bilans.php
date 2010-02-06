<?php
/*
 * $Id$
 *
 * Copyright 2001, 2010 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Gabriel Fischer, Didier Blanqui
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
?>
<div id="result">
    <div id="wrap" >
        <h3><font class="red">Bilans des incidents pour la p�riode du: <?php echo $_SESSION['stats_periodes']['du'];?> au <?php echo $_SESSION['stats_periodes']['au'];?> </font> </h3>
        <div class="bilans">
            <table class="boireaus"><tr><td  class="nouveau">Choisir le mode de repr�sentation</td>
                    <td><a href="index.php?ctrl=bilans&action=affiche_details"><img src="apps/img/simple.png" alt="simple" title="simplifi�"/></a>&nbsp;
                        <a href="index.php?ctrl=bilans&action=affiche_details&value=ok"><img src="apps/img/details.png" title="d�taill�" alt="d�taill�"/></a>&nbsp;</td></tr>
                <tr><td class="nouveau"> Choisir les filtres </td><td><a href="index.php?ctrl=bilans&action=choix_filtres"><img src="apps/img/filtres.png" alt="filtres" title="filtrer"/></a></td></tr></table>
        </div>
        <div class="bilans">
            <table class="boireaus"><tr><td  class="nouveau">Mode de repr�sentation actif</td>
                    <td colspan="2"><?php if($mode_detaille) {?>D�taill�<?php }else {?>Simplifi� <?php }?> </td></tr>
                <tr><td rowspan="4" class="nouveau"><p>Filtres actifs :<br /> Cliquer sur les items activ�s pour les supprimer de la s�lection.</p></td><td><?php if($filtres_categories) { ?><a href="index.php?ctrl=bilans&action=maj_filtre&type=categories" class="supp_filtre" title="Cliquez pour vider" >Cat�gories</a><?php } else echo'Cat�gories'; ?></td>
                    <td><?php if($filtres_categories) {
                            foreach($libelles_categories as $categorie) { ?>
                        <a href="index.php?ctrl=bilans&action=maj_filtre&type=categories&choix=<?php echo $categorie?>" class="supp_filtre" title="Cliquez pour supprimer"><?php echo $categorie,' - '; ?></a>
                                <?php    }
                        }else {
                            echo'Aucun';
                        }
                        ?>
                    </td></tr>
                <tr><td><?php if($filtres_mesures) { ?><a href="index.php?ctrl=bilans&action=maj_filtre&type=mesures" class="supp_filtre" title="Cliquez pour vider" >Mesures prises</a><?php } else echo 'Mesures prises';?></td>
                    <td>
                        <?php if($filtres_mesures) {
                            foreach($libelles_mesures as $mesure) {?>
                        <a href="index.php?ctrl=bilans&action=maj_filtre&type=mesures&choix=<?php echo $mesure?>" class="supp_filtre" title="Cliquez pour supprimer"><?php echo $mesure,' - '?></a>
                                <?php    }
                        }else {
                            echo'Aucun';
                        } ?></td></tr>
                <tr><td><?php if($filtres_sanctions) {?><a href="index.php?ctrl=bilans&action=maj_filtre&type=sanctions" class="supp_filtre" title="Cliquez pour vider" >Sanctions</a><?php } else echo 'Sanctions';?></td>
                    <td>
                        <?php if($filtres_sanctions) {
                            foreach($filtres_sanctions as $sanction) { ?>
                        <a href="index.php?ctrl=bilans&action=maj_filtre&type=sanctions&choix=<?php echo $sanction?>" class="supp_filtre" title="Cliquez pour supprimer"><?php echo $sanction,' - '?></a>
                                <?php    }
                        }else {
                            echo'Aucun';
                        } ?></td></tr>
                <tr><td><?php if($filtres_roles) {?><a href="index.php?ctrl=bilans&action=maj_filtre&type=roles" class="supp_filtre" title="Cliquez pour vider" >R�les</a><?php } else echo 'R�les';?></td><td><?php if($filtres_roles) {
                            foreach($filtres_roles as $role) {?>
                        <a href="index.php?ctrl=bilans&action=maj_filtre&type=roles&choix=<?php echo $role;?>" class="supp_filtre" title="Cliquez pour supprimer"><?php if($role=="") echo "Aucun r�le affect� - "; else echo $role,' - ';?></a>
                                <?php    }
                        }else {
                            echo'Aucun';
                        } ?></td></tr>
            </table>
        </div>
    </div>

    <div id="tableaux">
        <?php if (isset($incidents)) {?>
        <div id="banner">
          <ul  class="css-tabs"  id="menutabs">
                            <?php  $i=0;
                            foreach ($incidents as $titre=>$incidents_titre) {
                                if($titre=='L\'Etablissement') {
                                    if($affichage_etab) { ?>
                        <li><a href="#tab<?php echo $i;?>" title="Bilan des incidents"><?php echo $titre;?></a></li>
                                        <?php $i=$i+1; }
                                } else if ($titre=='Tous les �l�ves' ||$titre=='Tous les personnels' ){ ?>
                              <li><a href="#tab<?php echo $i;?>" title="Bilan des incidents"><?php echo $titre;?> </a></li>
                              <?php  $i=$i+1; } else { ?>
                              <li><a href="#tab<?php echo $i;?>" title="Bilan des incidents">
                                            <?php if (isset($infos_individus[$titre])) {
                                                echo substr($infos_individus[$titre]['prenom'],0,1).'.'.$infos_individus[$titre]['nom'];
                                                if (isset($infos_individus[$titre]['classe'])) echo'('.$infos_individus[$titre]['classe'].')';
                                            }
                                            else echo $titre;?></a></li>
                            <?php if (isset($infos_individus[$titre]['classe'])|| !isset($infos_individus[$titre])) { ?>
                              <li><a href="#tab<?php echo $i+1;?>"><img src="apps/img/user.png" alt="Synth�se par �l�ve" title="Synth�se par �l�ve"/></a>&nbsp;&nbsp;</li>
                                    <?php 
                                   }
                                   $i=$i+2; }
                            }?>
                    </ul>      
        </div>
        <div class="css-panes" id="containDiv">
                <?php
                $i=0;
                foreach ($incidents as $titre=>$incidents_titre) { 
                    if ($titre!=='L\'Etablissement' || ($titre=='L\'Etablissement' && $affichage_etab) ) { ;?>
            <div class="panel" id="tab<?php echo $i;?>">
                        <?php
                        if (isset($incidents_titre['error'])) {?>
                <table class="boireaus">
                    <tr ><td class="nouveau"><font class='titre'>Bilan des incidents concernant :</font>
                                        <?php if (isset($infos_individus[$titre])) {
                                            echo $infos_individus[$titre]['prenom'].' '.$infos_individus[$titre]['nom'];
                                            if (isset($infos_individus[$titre]['classe'])) echo'('.$infos_individus[$titre]['classe'].')';
                                        }
                                        else echo $titre;?>
                        </td></tr>
                    <tr><td class='nouveau'>Pas d'incidents avec les crit�res s�lectionn�s...</td></tr>
                </table><br /><br />

                            <?php echo'</div>';?>
               <?php if ($titre!=='L\'Etablissement' || $titre=='Tous les �l�ves' ||$titre=='Tous les personnels') {?>
                <div class="panel" id="tab<?php echo $i+1;?>">
                    <table class="boireaus">
                    <tr><td class="nouveau"><strong>Bilan individuel</strong> </td></tr>
                    <tr><td class="nouveau">Pas d'incidents avec les crit�res s�lectionn�s...</td></tr>
                    </table>
                </div>
                            <?php
                            $i=$i+2;
                         }
                        }
                        else { ?>                      

                <table class="boireaus">
                    <tr ><td rowspan="5"  colspan="5" class='nouveau'>
                            <p><font class='titre'>Bilan des incidents concernant : </font>
                                            <?php if (isset($infos_individus[$titre])) {
                                                echo $infos_individus[$titre]['prenom'].' '.$infos_individus[$titre]['nom'];
                                                if (isset($infos_individus[$titre]['classe'])) echo'('.$infos_individus[$titre]['classe'].')';
                                            }
                                            else echo $titre;?>
                            </p>
                                        <?php if($filtres_categories||$filtres_mesures||$filtres_roles||$filtres_sanctions) { ?><p>avec les filtres selectionn�s</p><?php }?></td>
                        <td  <?php if ($titre=='L\'Etablissement' ) {?> colspan="3" <?php }?> class='nouveau'><font class='titre'>Nombres d'incidents sur la p�riode:</font> <?php echo $totaux[$titre]['incidents']; ?></td><?php if ($titre!=='L\'Etablissement' ) {?> <td  class='nouveau' > <font class='titre'>% sur la p�riode/Etab: </font> <?php echo round((100*($totaux[$titre]['incidents']/$totaux['L\'Etablissement']['incidents'])),2);?></td><?php } ?></tr>
                    <tr><td  <?php if ($titre=='L\'Etablissement' ) {?> colspan="2" <?php }?> class='nouveau'><font class='titre'>Nombre total de mesures prises pour ces incidents :</font> <?php echo $totaux[$titre]['mesures']; ?></td><?php if ($titre!=='L\'Etablissement' ) {?> <td  class='nouveau' > <font class='titre'>% sur la p�riode/Etab: </font> <?php if($totaux['L\'Etablissement']['mesures']) echo round((100*($totaux[$titre]['mesures']/$totaux['L\'Etablissement']['mesures'])),2); else echo'0';?></td><?php } ?></tr>
                    <tr><td  <?php if ($titre=='L\'Etablissement' ) {?> colspan="2" <?php }?> class='nouveau'><font class='titre'>Nombre total de sanctions prises pour ces incidents:</font> <?php echo $totaux[$titre]['sanctions']; ?></td><?php if ($titre!=='L\'Etablissement' ) {?> <td  class='nouveau' > <font class='titre'>% sur la p�riode/Etab: </font> <?php if($totaux['L\'Etablissement']['sanctions']) echo round((100*($totaux[$titre]['sanctions']/$totaux['L\'Etablissement']['sanctions'])),2); else echo'0';?></td><?php } ?></tr>
                    <tr><td  <?php if ($titre=='L\'Etablissement' ) {?> colspan="2" <?php }?> class='nouveau'><font class='titre'>Nombre total d'heures de retenues pour ces incidents:</font> <?php echo $totaux[$titre]['heures_retenues']; ?></td><?php if ($titre!=='L\'Etablissement' ) {?> <td  class='nouveau' > <font class='titre'>% sur la p�riode/Etab: </font> <?php if($totaux['L\'Etablissement']['heures_retenues']) echo round((100*($totaux[$titre]['heures_retenues']/$totaux['L\'Etablissement']['heures_retenues'])),2); else echo '0'; ?></td><?php } ?></tr>
                    <tr><td  <?php if ($titre=='L\'Etablissement' ) {?> colspan="2" <?php }?> class='nouveau'><font class='titre'>Nombre total de jours d'exclusions pour ces incidents:</font> <?php echo $totaux[$titre]['jours_exclusions']; ?></td><?php if ($titre!=='L\'Etablissement' ) {?> <td  class='nouveau' > <font class='titre'>% sur la p�riode/Etab: </font> <?php if($totaux['L\'Etablissement']['jours_exclusions']) echo round((100*($totaux[$titre]['jours_exclusions']/$totaux['L\'Etablissement']['jours_exclusions'])),2); else echo '0'; ?></td><?php } ?></tr>
                </table>
            <?php if($mode_detaille) { ?>
                <table class="sortable resizable " id="table<?php echo $i;?>">
                    <thead>
                    <tr><th><font class='titre'>Date</font></th><th class="text"><font class='titre'>D�clarant</font></th><th><font class='titre'>Heure</font></th><th class="text"><font class='titre'>Nature</font></th>
                        <th><font class='titre' title="Cat�gories">Cat.</font></th><th class="text" ><font class='titre'>Description</font></th><th  width="50%" class="nosort"><font class='titre'>Suivi</font></th></tr>
                    </thead>
                                    <?php $alt_b=1;
                                    foreach($incidents_titre as  $incident) {
                                        $alt_b=$alt_b*(-1);?>
                    <tr class='lig<?php echo $alt_b;?>'><td><?php echo $incident->date; ?></td><td><?php echo $incident->declarant; ?></td><td><?php echo $incident->heure; ?></td>
                        <td><?php echo $incident->nature; ?></td><td><?php if(!is_null($incident->id_categorie))echo $incident->sigle_categorie;else echo'-'; ?></td><td><?php echo $incident->description; ?></td>
                        <td class="nouveau"><?php if(!isset($protagonistes[$incident->id_incident]))echo'<h3 class="red">Aucun protagoniste d�fini pour cet incident</h3>';
                                                else { ?>
                            <table class="boireaus" width="100%" >
                                                        <?php foreach($protagonistes[$incident->id_incident] as $protagoniste) {?>
                                <tr><td>
                                                                    <?php echo $protagoniste->prenom.' '.$protagoniste->nom.' <br/>  ';
                                                                    echo $protagoniste->statut.' ';
                                                                    if($protagoniste->classe) echo $protagoniste->classe .' - '; else echo ' - ' ;
                                                                    if($protagoniste->qualite=="") echo'<font class="red">Aucun r�le affect�.</font><br />';
                                                                    else echo $protagoniste->qualite.'<br />';
                                                                    ?></td><td ><?php
                                                                    if (isset($mesures[$incident->id_incident][$protagoniste->login])) { ?>
                                        <p><strong>Mesures :</strong></p>
                                        <table class="boireaus" >
                                            <tr><th><font class='titre'>Nature</font></th><th><font class='titre'>Mesure</font></th></tr>
                                                                            <?php
                                                                            $alt_b=1;
                                                                            foreach ($mesures[$incident->id_incident][$protagoniste->login] as $mesure) {
                                                                                $alt_b=$alt_b*(-1); ?>
                                            <tr class="lig<?php echo $alt_b;?>"><td><?php echo $mesure->mesure; ?></td>
                                                <td><?php echo $mesure->type.' par '.$mesure->login_u; ?></td></tr> <?php } ?>
                                        </table>
                                                                        <?php  }
                                                                    if (isset($sanctions[$incident->id_incident][$protagoniste->login])) { ?>
                                        <p><strong>Sanctions :</strong></p>
                                        <table class="boireaus" width="100%">
                                            <tr><th><font class='titre'>Nature</font></th><th><font class='titre'>Effectu�e</font></th><th><font class='titre'>Date</font></th><th><font class='titre'>Dur�e</font></th></tr>
                                                                            <?php
                                                                            $alt_b=1;
                                                                            foreach ($sanctions[$incident->id_incident][$protagoniste->login] as $sanction) {
                                                                                $alt_b=$alt_b*(-1); ?>
                                            <tr class="lig<?php echo $alt_b;?>"><td><?php echo $sanction->nature; ?></td>
                                                <td><?php echo $sanction->effectuee; ?></td>
                                                <td><?php if($sanction->nature=='retenue')echo $sanction->ret_date;
                                    if($sanction->nature=='exclusion')echo 'Du '.$sanction->exc_date_debut.' au '.$sanction->exc_date_fin;?></td>
                                                <td><?php if($sanction->nature=='retenue'){echo $sanction->ret_duree.' heure';if ($sanction->ret_duree >1) echo 's'; }
                                                                                if($sanction->nature=='exclusion') {echo $sanction->exc_duree.' jour'; if ($sanction->exc_duree >1) echo 's'; }?></td>
                                            </tr>
                                                                            <?php } ?>
                                        </table>
                                                                <?php } ?>
                                    </td></tr>
                                                <?php  } ?></table>
                                            <?php } ?></td></tr>
                    <?php } 
            }?>
                </table>
                <br /><br /><a href="#wrap"><img src="apps/img/retour_haut.png" alt="simple" title="simplifi�"/>Retour aux selections </a>
            </div>
            <?php if ($titre!=='L\'Etablissement' && $titre!=='Tous les �l�ves' && $titre!=='Tous les personnels'  ){ ?>
            <div class="panel" id="tab<?php echo $i+1;?>">                
               <table class="boireaus"> <tr><td class="nouveau" colspan="11"><strong>Bilan individuel</strong></td></tr></table>
                <table  class="sortable resizable ">
                    <thead>
                      <tr><th colspan="2" <?php if (!isset($totaux_indiv[$titre])) {?> <?php }?>>Individu</th><th >Incidents</th><th colspan="2" <?php if (!isset($totaux_indiv[$titre])) {?> <?php }?>>Mesures prises</th><th colspan="2" <?php if (!isset($totaux_indiv[$titre])) {?> <?php }?>>Sanctions prises</th>
                     <th colspan="2" <?php if (!isset($totaux_indiv[$titre])) {?> <?php }?>>Heures de retenues</th><th colspan="2" <?php if (!isset($totaux_indiv[$titre])) {?> <?php }?>>Jours d'exclusion</th></tr>
                 <tr><th>Nom</th><th>Pr�nom</th><th>Nombre</th><th>Nombre</th><th>%/Etab</th><th>Nombre</th><th>%/Etab</th><th>Nombre</th><th>%/Etab</th><th>Nombre</th><th>%/Etab</th></tr>
                    </thead>
                    <tbody>
              <?php
                 $alt_b=1;
                 foreach ($liste_eleves[$titre] as $eleve){                 
                 $alt_b=$alt_b*(-1);?>                
                 <tr <?php if ($alt_b==1) echo"class='alt'";?>><td><?php echo $totaux_indiv[$eleve]['nom']; ?></td><td><?php echo $totaux_indiv[$eleve]['prenom']; ?></td><td><?php echo $totaux_indiv[$eleve]['incidents']; ?></td><td><?php echo $totaux_indiv[$eleve]['mesures']; ?></td><td><?php echo round(100*($totaux_indiv[$eleve]['mesures']/$totaux['L\'Etablissement']['mesures']),2);?></td>
                     <td><?php echo $totaux_indiv[$eleve]['sanctions']; ?></td><td><?php echo round(100* ($totaux_indiv[$eleve]['sanctions']/$totaux['L\'Etablissement']['sanctions']),2);?></td>
                     <td><?php echo $totaux_indiv[$eleve]['heures_retenues']; ?></td><td><?php echo round(100*($totaux_indiv[$eleve]['heures_retenues']/$totaux['L\'Etablissement']['heures_retenues']),2);?></td>
                     <td><?php echo $totaux_indiv[$eleve]['jours_exclusions']; ?></td><td><?php echo round(100*($totaux_indiv[$eleve]['jours_exclusions']/$totaux['L\'Etablissement']['jours_exclusions']),2);?></td></tr>
                 <?php }?>
                 </tbody>
                  <?php if (!isset($totaux_indiv[$titre])) { ?>
                 <tfoot>
                     <tr><td colspan="2">Total</td><td><?php echo $totaux_par_classe[$titre]['incidents']?></td><td><?php echo $totaux_par_classe[$titre]['mesures']?></td><td><?php echo round(100*($totaux_par_classe[$titre]['mesures']/$totaux['L\'Etablissement']['mesures']),2);?></td>
                     <td><?php echo $totaux_par_classe[$titre]['sanctions']?></td><td><?php echo round(100*($totaux_par_classe[$titre]['sanctions']/$totaux['L\'Etablissement']['sanctions']),2);?></td>
                     <td><?php echo $totaux_par_classe[$titre]['heures_retenues']?></td><td><?php echo round(100*($totaux_par_classe[$titre]['heures_retenues']/$totaux['L\'Etablissement']['heures_retenues']),2);?></td>
                     <td><?php echo $totaux_par_classe[$titre]['jours_exclusions']?></td><td><?php echo round(100*($totaux_par_classe[$titre]['jours_exclusions']/$totaux['L\'Etablissement']['jours_exclusions']),2);?></td>
                     </tr>
                 </tfoot>
                 <?php }?>
                </table>                            
            </div>
                        <?php  $i=$i+2;
                        }
                        else{
                            $i=$i+1;
                        }
                    }
                }
    }
}else echo 'S�lectionnez en premier lieu des donn�es � traiter'; ?>

        </div>
    </div>
</div>

