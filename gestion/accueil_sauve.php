<?php
/*
 * Last modification  : 18/09/2006
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

// On d�samorce une tentative de contournement du traitement anti-injection lorsque register_globals=on
if (isset($_GET['traite_anti_inject']) OR isset($_POST['traite_anti_inject'])) $traite_anti_inject = "yes";

// On pr�cise de ne pas traiter les donn�es avec la fonction anti_inject
if (isset($_POST["action"]) and ($_POST["action"] == 'protect'))  $traite_anti_inject = 'no';

// Initialisations files
require_once("../lib/initialisations.inc.php");

unset($action);
$action = isset($_POST["action"]) ? $_POST["action"] : (isset($_GET["action"]) ? $_GET["action"] : NULL);

// Resume session
$resultat_session = resumeSession();
//D�commenter la ligne suivante pour le mode "manuel et bavard"
//$debug="yes";

if (!isset($action) or ($action != "restaure")) {
    if ($resultat_session == 'c') {
        header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
        die();
    } else if ($resultat_session == '0') {
        header("Location: ../logout.php?auto=1");
        die();
    };
}

if (!isset($action) or ($action != "restaure")) {
    if (!checkAccess()) {
        header("Location: ../logout.php?auto=1");
        die();
    }
}


// Initialisation du r�pertoire actuel de sauvegarde
$dirname = getSettingValue("backup_directory");

// T�l�chargement d'un fichier vers backup
if (isset($action) and ($action == 'upload'))  {
    $sav_file = isset($_FILES["sav_file"]) ? $_FILES["sav_file"] : NULL;
    if (!isset($sav_file['tmp_name']) or ($sav_file['tmp_name'] =='')) {
        $msg = "Erreur de t�l�chargement.";
    } else if (!file_exists($sav_file['tmp_name'])) {
        $msg = "Erreur de t�l�chargement.";
    } else if (!preg_match('/sql$/',$sav_file['name'])){
        $msg = "Erreur : seuls les fichiers ayant l'extension .sql sont autoris�s.";
    } else {
        $dest = "../backup/".$dirname."/";
        $n = 0;
        $nom_corrige = ereg_replace("[^.a-zA-Z0-9_=-]+", "_", $sav_file['name']);
        if (!deplacer_fichier_upload($sav_file['tmp_name'], "../backup/".$dirname."/".$nom_corrige)) {
            $msg = "Probl�me de transfert : le fichier n'a pas pu �tre transf�r� sur le r�pertoire backup";
        } else {
            $msg = "T�l�chargement r�ussi.";
        }
    }
}



// Suppression d'un fichier
if (isset($action) and ($action == 'sup'))  {
    if (isset($_GET['file']) && ($_GET['file']!='')) {
        if (@unlink("../backup/".$dirname."/".$_GET['file'])) {
            $msg = "Le fichier <b>".$_GET['file']."</b> a �t� supprim�.<br />";
        } else {
            $msg = "Un probl�me est survenu lors de la tentative de suppression du fichier <b>".$_GET['file']."</b>.<br />
            Il s'agit peut-�tre un probl�me de droits sur le r�pertoire backup.<br />";
        }
    }
}

// Protection du r�pertoire backup
if (isset($action) and ($action == 'protect'))  {
    include_once("../lib/class.htaccess.php");
    // Instance of the htaccess class
    $ht = & new htaccess(TRUE);
    $user = array();
    // Get the logins from the password file
    $user = $ht->get_htpasswd();
    // Add an Administrator
    if(empty($_POST['pwd1_backup']) || empty($_POST['pwd2_backup'])) {
        $msg = "Probl�me : les deux mots de passe ne sont pas identiques ou sont vides.";
        $error = 1;
    } elseif ($_POST['pwd1_backup'] != $_POST['pwd2_backup']) {
        $msg = "Probl�me : les deux mots de passe ne sont pas identiques.";
        $error = 1;
    } elseif (empty($_POST['login_backup'])) {
        $msg = "Probl�me : l'identifiant est vide.";
        $error = 1;
    } else {
        $_login = strtolower(unslashes($_POST['login_backup']));
        if(is_array($user)) {
            foreach($user as $key => $value) {
                if($_login == $key) {
                   $ht->delete_user($_login);
                }
            }
        }
    }
    if(!isset($error)) {
        $ht->set_user($_login, $_POST['pwd1_backup']);
        $ht->set_htpasswd();
        $user = array();
        $user = $ht->get_htpasswd();
        clearstatcache();
        if(!is_file('../backup/'.$dirname.'/.htaccess')) {
            $ht->option['AuthName'] = '"PROTECTION BACKUP"';
            $ht->set_htaccess();
        }
    }
}

// Suppression de la protection
if (isset($action) and ($action == 'del_protect'))  {
   if ((@unlink("../backup/".$dirname."/.htaccess")) and (@unlink("../backup/".$dirname."/.htpasswd"))) {
       $msg = "Les fichiers .htaccess et .htpasswd ont �t� supprim�s. Le r�pertoire /backup n'est plus prot�g�";
   }
}

function deplacer_fichier_upload($source, $dest) {
    $ok = @copy($source, $dest);
    if (!$ok) $ok = @move_uploaded_file($source, $dest);
    return $ok;
}


function test_ecriture_backup() {
    $ok = 'no';
    if ($f = @fopen("../backup/test", "w")) {
        @fputs($f, '<'.'?php $ok = "yes"; ?'.'>');
        @fclose($f);
        include("../backup/test");
        $del = @unlink("../backup/test");
    }
    return $ok;
}

function mysql_version2() {
   $result = mysql_query('SELECT VERSION() AS version');
   if ($result != FALSE && @mysql_num_rows($result) > 0)
   {
      $row = mysql_fetch_array($result);
      $match = explode('.', $row['version']);
   }
   else
   {
      $result = @mysql_query('SHOW VARIABLES LIKE \'version\'');
      if ($result != FALSE && @mysql_num_rows($result) > 0)
      {
         $row = mysql_fetch_row($result);
         $match = explode('.', $row[1]);
      }
   }

   if (!isset($match) || !isset($match[0])) $match[0] = 3;
   if (!isset($match[1])) $match[1] = 21;
   if (!isset($match[2])) $match[2] = 0;
   return $match[0] . "." . $match[1] . "." . $match[2];
}

function init_time() {
    global $TPSDEB,$TPSCOUR;
    list ($usec,$sec)=explode(" ",microtime());
    $TPSDEB=$sec;
    $TPSCOUR=0;
}

function current_time() {
    global $TPSDEB,$TPSCOUR;
    list ($usec,$sec)=explode(" ",microtime());
    $TPSFIN=$sec;
    if (round($TPSFIN-$TPSDEB,1)>=$TPSCOUR+1) //une seconde de plus
    {
    $TPSCOUR=round($TPSFIN-$TPSDEB,1);
    flush();
    }
}

function backupMySql($db,$dumpFile, $duree,$rowlimit) {
    global $TPSCOUR,$offsettable,$offsetrow,$cpt,$debug;
    $fileHandle = fopen($dumpFile, "a");
    if(!$fileHandle) {
        echo "Ouverture de $dumpFile impossible<br />";
        return FALSE;
    }
    if ($offsettable==0&&$offsetrow==-1){
        $todump ="#**************** BASE DE DONNEES ".$db." ****************"."\n"
        .date("\#\ \L\e\ \:\ d\ m\ Y\ \a\ H\h\ i")."\n";
        $todump.="# Serveur : ".$_SERVER['SERVER_NAME']."\n";
        $todump.="# Version PHP : " . phpversion()."\n";
        $todump.="# Version mySQL : " . mysql_version2()."\n";
        $todump.="# IP Client : ".$_SERVER['REMOTE_ADDR']."\n";
        $todump.="# Fichier SQL compatible PHPMyadmin\n#\n";
        $todump.="# ******* debut du fichier ********\n";
        fwrite ($fileHandle,$todump);
    }
    $result=mysql_list_tables($db);
    $numtab=0;
    while ($t = mysql_fetch_array($result)) {
        $tables[$numtab]=$t[0];
        $numtab++;
    }
    if (mysql_error()) {
       echo "<hr /><font color='red'>ERREUR lors de la sauvegarde du � un probl�me dans la la base.</font><br />".mysql_error()."<hr/>";
       return false;
       die();
    }

    for (;$offsettable<$numtab;$offsettable++){
        // Dump de la strucutre table
        if ($offsetrow==-1){
            $todump=get_def($db,$tables[$offsettable]);
            if (isset($debug)) echo "<b><br />Dump de la structure de la table ".$tables[$offsettable]."</b><br />";
            fwrite ($fileHandle,$todump);
            $offsetrow++;
            $cpt++;
        }
        current_time();
        if ($duree>0 and $TPSCOUR>=$duree) //on atteint la fin du temps imparti
            return TRUE;
        if (isset($debug)) echo "<b><br />Dump des donn�es de la table ".$tables[$offsettable]."<br /></b>";
        $fin=0;
        while (!$fin){
            $todump =get_content($db,$tables[$offsettable],$offsetrow,$rowlimit);
            $rowtodump=substr_count($todump, "INSERT INTO");
            if ($rowtodump>0){
                fwrite ($fileHandle,$todump);
                $cpt+=$rowtodump;
                $offsetrow+=$rowlimit;
                if ($rowtodump<$rowlimit) $fin=1;
                current_time();
                if ($duree>0 and $TPSCOUR>=$duree) {//on atteint la fin du temps imparti
                    if (isset($debug)) echo "<br /><br /><b>Nombre de lignes actuellement dans le fichier : ".$cpt."</b><br />";
                    return TRUE;
                }
            } else {
                $fin=1;$offsetrow=-1;
            }
        }
        if (isset($debug)) echo "Pour cette table, nombre de lignes sauvegard�es : ".$offsetrow."<br />";
        if ($fin) $offsetrow=-1;
        current_time();
        if ($duree>0 and $TPSCOUR>=$duree) //on atteint la fin du temps imparti
            return TRUE;

    }
    $offsettable=-1;
    $todump.="#\n";
    $todump.="# ******* Fin du fichier - La sauvegarde s'est termin�e normalement ********\n";
    fwrite ($fileHandle,$todump);
    fclose($fileHandle);
    return TRUE;
}
function restoreMySqlDump($dumpFile , $duree) {
    // $dumpFile, fichier source
    // $duree=timeout pour changement de page (-1 = aucun)

    global $TPSCOUR,$offset,$cpt;

    if(!file_exists($dumpFile)) {
         echo "$dumpFile non trouv�<br />";
         return FALSE;
    }
    $fileHandle = fopen($dumpFile, "rb");

    if(!$fileHandle) {
        echo "Ouverture de $dumpFile impossible.<br />";
        return FALSE;
    }

    if ($offset!=0) {
        if (fseek($fileHandle,$offset,SEEK_SET)!=0) { //erreur
            echo "Impossible de trouver l'octet ".number_format($offset,0,""," ")."<br />";
            return FALSE;
        }
        //else
        //    echo "Reprise � l'octet ".number_format($offset,0,""," ")."<br />";
        flush();
    }
    $formattedQuery = "";
    $old_offset = $offset;
    while(!feof($fileHandle)) {
        current_time();
        if ($duree>0 and $TPSCOUR>=$duree) {  //on atteint la fin du temps imparti
            if ($old_offset == $offset) {
                echo "<p align=\"center\"><b><font color=\"#FF0000\">La proc�dure de restauration ne peut pas continuer.
                <br />Un probl�me est survenu lors du traitement d'une requ�te pr�s de :.
                <br />".$debut_req."</font></b></p><hr />";
                return FALSE;
            }
            $old_offset = $offset;
            return TRUE;
        }
        //echo $TPSCOUR."<br />";
        $buffer=fgets($fileHandle);
        if (substr($buffer,strlen($buffer),1)==0)
            $buffer=substr($buffer,0,strlen($buffer)-1);

        //echo $buffer."<br />";

        if(substr($buffer, 0, 1) != "#") {
            if (!isset($debut_req))  $debut_req = $buffer;
            $formattedQuery .= $buffer;
              //echo $formattedQuery."<hr />";
            if ($formattedQuery)
                if (mysql_query($formattedQuery)) {//r�ussie sinon continue � conca&t�ner
                    $offset=ftell($fileHandle);
                    //echo $offset;
                    $formattedQuery = "";
                    unset($debut_req);
                    $cpt++;
                    //echo $cpt;
                }
        }
    }

    if (mysql_error())
        echo "<hr />ERREUR � partir de [$formattedQuery]<br />".mysql_error()."<hr />";

    fclose($fileHandle);
    $offset=-1;
    return TRUE;
}

function get_def($db, $table) {
    $def="#\n# Structure de la table $table\n#\n";
    $def .="DROP TABLE IF EXISTS `$table`;\n";
    // requete de creation de la table
    $query = "SHOW CREATE TABLE $table";
    $resCreate = mysql_query($query);
    $row = mysql_fetch_array($resCreate);
    $schema = $row[1].";";
    $def .="$schema\n";
    return $def;
}

function get_content($db, $table,$from,$limit) {
    $search       = array("\x00", "\x0a", "\x0d", "\x1a");
    $replace      = array('\0', '\n', '\r', '\Z');
    // les donn�es de la table
    $def = '';
    $query = "SELECT * FROM $table LIMIT $from,$limit";
    $resData = @mysql_query($query);
    //peut survenir avec la corruption d'une table, on pr�vient
    if (!$resData) {
        $def .="Probl�me avec les donn�es de $table, corruption possible !\n";
    } else {
        if (@mysql_num_rows($resData) > 0) {
             $sFieldnames = "";
             $num_fields = mysql_num_fields($resData);
              $sInsert = "INSERT INTO $table $sFieldnames values ";
              while($rowdata = mysql_fetch_row($resData)) {
                  $lesDonnees = "";
                  for ($mp = 0; $mp < $num_fields; $mp++) {
                  $lesDonnees .= "'" . str_replace($search, $replace, traitement_magic_quotes($rowdata[$mp])) . "'";
                  //on ajoute � la fin une virgule si n�cessaire
                      if ($mp<$num_fields-1) $lesDonnees .= ", ";
                  }
                  $lesDonnees = "$sInsert($lesDonnees);\n";
                  $def .="$lesDonnees";
              }
        }
     }
     return $def;
}

// Type de fichier
$filetype = "sql";

// Chemin vers /backup
if (!isset($_GET["path"]))
    $path="../backup/" . $dirname . "/" ;
else
    $path=$_GET["path"];



// Dur�e d'une portion
if ((isset($_POST['duree'])) and ($_POST['duree'] > 0)) $_SESSION['defaulttimeout'] = $_POST['duree']  ;
if (!isset($_SESSION['defaulttimeout'])) {
    $max_time=min(get_cfg_var("max_execution_time"),get_cfg_var("max_input_time"));
    if ($max_time>5) {
        $_SESSION['defaulttimeout']=$max_time-2;
    } else {
        $_SESSION['defaulttimeout']=5;
    }
}

// Lors d'une sauvegarde, nombre de lignes trait�es dans la base entre chaque v�rification du temps restant
$defaultrowlimit=10;

//**************** EN-TETE *****************
$titre_page = "Outil de gestion | Sauvegardes/Restauration";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

// Test d'�criture dans /backup
$test_write = test_ecriture_backup();
if ($test_write == 'no') {
    echo "<h3 class='gepi'>Probl�me de droits d'acc�s :</h3>";
    echo "<p>Le r�pertoire \"/backup\" n'est pas accessible en �criture.</p>";
    echo "<P>Vous ne pouvez donc pas acc�der aux fonctions de sauvegarde/restauration de GEPI.
    Contactez l'administrateur technique afin de r�gler ce proibl�me.</p>";
    echo "</body></html>";
    die();
}

// Confirmation de la restauration
if (isset($action) and ($action == 'restaure_confirm'))  {
    echo "<h3>Confirmation de la restauration de la base</h3>";
    echo "Fichier s�lectionn� pour la restauration : <b>".$_GET['file']."</b>";
    echo "<p><b>ATTENTION :</b> La proc�dure de restauration de la base est <b>irr�versible</b>. Le fichier de restauration doit �tre valide. Selon le contenu de ce fichier, tout ou partie de la structure actuelle de la base ainsi que des donn�es existantes peuvent �tre supprim�es et remplac�es par la structure et les donn�es pr�sentes dans le fichier.
    <br /><br /><b>AVERTISSEMENT :</b> Cette proc�dure peut �tre tr�s longue selon la quantit� de donn�es � restaurer.</p>";
    echo "<p><b>Etes-vous s�r de vouloir continuer ?</b></p>";
    echo "<center><table cellpadding=\"5\" cellspacing=\"5\" border=\"0\"><tr><td>";
    echo "<form enctype=\"multipart/form-data\" action=\"accueil_sauve.php\" method=post name=formulaire_oui>";
    echo "<INPUT TYPE=SUBMIT name='confirm' value = 'Oui' />";
    echo "<input type=\"hidden\" name=\"action\" value=\"restaure\" />";
    echo "<input type=\"hidden\" name=\"file\" value=\"".$_GET['file']."\" />";
    echo "</FORM>";
    echo "</td><td>";
    echo "<form enctype=\"multipart/form-data\" action=\"accueil_sauve.php\" method=post name=formulaire_non>";
    echo "<INPUT TYPE=SUBMIT name='confirm' value = 'Non' /></form></td></tr></table></center>";
    echo "</body></html>";
    die();
}

// Restauration
if (isset($action) and ($action == 'restaure'))  {
    unset($file);
    $file = isset($_POST["file"]) ? $_POST["file"] : (isset($_GET["file"]) ? $_GET["file"] : NULL);

    init_time(); //initialise le temps
    //d�but de fichier
    if (!isset($_GET["offset"])) $offset=0;
    else  $offset=$_GET["offset"];

    //timeout
    if (!isset($_GET["duree"])) $duree=$_SESSION['defaulttimeout'];
        else $duree=$_GET["duree"];
    $fsize=filesize($path.$file);
    if(isset($offset)) {
        if ($offset==-1) $percent=100;
           else $percent=min(100,round(100*$offset/$fsize,0));
    }
    else $percent=0;

    if ($percent >= 0) {
        $percentwitdh=$percent*4;
        echo "<div align='center'><table class='tab_cadre' width='400'><tr><td width='400' align='center'><b>Restauration en cours</b><br /><br />Progression ".$percent."%</td></tr><tr><td><table><tr><td bgcolor='red'  width='$percentwitdh' height='20'>&nbsp;</td></tr></table></td></tr></table></div>";
    }
    flush();
    if ($offset!=-1) {
        if (restoreMySqlDump($path.$file,$duree)) {
            if (isset($debug)) echo "<br /><b>Cliquez <a href=\"accueil_sauve.php?action=restaure&file=".$file."&duree=$duree&offset=$offset&cpt=$cpt&path=$path\">ici</a> pour poursuivre la restauration</b>";
            if (!isset($debug))  echo "<br /><b>Redirection automatique sinon cliquez <a href=\"accueil_sauve.php?action=restaure&file=".$file."&duree=$duree&offset=$offset&cpt=$cpt&path=$path\">ici</a></b>";
            if (!isset($debug))  echo "<script>window.location=\"accueil_sauve.php?action=restaure&file=".$file."&duree=$duree&offset=$offset&cpt=$cpt&path=$path\";</script>";
            flush();
            exit;
        }
    } else {

        echo "<div align='center'><p>Restauration Termin�e.<br /><br />Votre session GEPI n'est plus valide, vous devez vous reconnecter<br /><a href = \"../login.php\">Se connecter</a></p></div>";
        echo "</body></html>";
        die();
    }
}

// Sauvegarde
if (isset($action) and ($action == 'dump'))  {
    // SAuvegarde de la base
    $nomsql = $dbDb."_le_".date("Y_m_d_\a_H\hi");
    $cur_time=date("Y-m-d H:i");
    $filename=$path."$nomsql.$filetype";

    if (!isset($_GET["duree"])&&is_file($filename)){
        echo "<font color=\"#FF0000\"><center><b>Le fichier existe d�j�. Patientez une minute avant de retenter la sauvegarde.</b></center></font><hr />";
    } else {
        init_time(); //initialise le temps
        //d�but de fichier
        if (!isset($_GET["offsettable"])) $offsettable=0;
            else $offsettable=$_GET["offsettable"];
        //d�but de fichier
        if (!isset($_GET["offsetrow"])) $offsetrow=-1;
            else $offsetrow=$_GET["offsetrow"];
        //timeout de 5 secondes par d�faut, -1 pour utiliser sans timeout
        if (!isset($_GET["duree"])) $duree=$_SESSION['defaulttimeout'];
            else $duree=$_GET["duree"];
        //Limite de lignes � dumper � chaque fois
        if (!isset($_GET["rowlimit"])) $rowlimit=$defaultrowlimit;
            else  $rowlimit=$_GET["rowlimit"];
         //si le nom du fichier n'est pas en param�tre le mettre ici
         if (!isset($_GET["fichier"])) {
             $fichier=$filename;
         } else $fichier=$_GET["fichier"];


        $tab=mysql_list_tables($dbDb);
        $tot=mysql_num_rows($tab);
        if(isset($offsettable)){
            if ($offsettable>=0)
                $percent=min(100,round(100*$offsettable/$tot,0));
            else $percent=100;
        }
        else $percent=0;

        if ($percent >= 0) {
            $percentwitdh=$percent*4;
            echo "<div align='center'><table width=\"400\" border=\"0\">
            <tr><td width='400' align='center'><b>Sauvegarde en cours</b><br/>
            <br/>A la fin de la sauvegarde, Gepi vous proposera automatiquement de t�l�charger le fichier.
            <br/><br/>Progression ".$percent."%</td></tr><tr><td><table><tr><td bgcolor='red'  width='$percentwitdh' height='20'>&nbsp;</td></tr></table></td></tr></table></div>";
        }
        flush();
        if ($offsettable>=0){
            if (backupMySql($dbDb,$fichier,$duree,$rowlimit)) {
                if (isset($debug)) echo "<br /><b>Cliquez <a href=\"accueil_sauve.php?action=dump&duree=$duree&rowlimit=$rowlimit&offsetrow=$offsetrow&offsettable=$offsettable&cpt=$cpt&fichier=$fichier&path=$path\">ici</a> pour poursuivre la sauvegarde.</b>";
                if (!isset($debug))    echo "<br /><b>Redirection automatique sinon cliquez <a href=\"accueil_sauve.php?action=dump&duree=$duree&rowlimit=$rowlimit&offsetrow=$offsetrow&offsettable=$offsettable&cpt=$cpt&fichier=$fichier&path=$path\">ici</a></b>";
                if (!isset($debug))    echo "<script>window.location=\"accueil_sauve.php?action=dump&duree=$duree&rowlimit=$rowlimit&offsetrow=$offsetrow&offsettable=$offsettable&cpt=$cpt&fichier=$fichier&path=$path\";</script>";
                flush();
                exit;
           }
        } else {

            echo "<div align='center'><p>Sauvegarde Termin�e.<br/>
                <br/><p class=grand><a href='savebackup.php?filename=$fichier'>T�l�charger le fichier g�n�r� par la sauvegarde</a></p>
                <br/><br/><a href = \"accueil_sauve.php\">Retour vers l'interface de sauvegarde/restauration</a><br /></div>";

            echo "</body>";
            echo "</html>";
            die();
        }

    }
}

?><b>|<a href='index.php'>Retour</a>|</b><?php
// Test pr�sence de fichiers htaccess
if (!(file_exists("../backup/".$dirname."/.htaccess")) or !(file_exists("../backup/".$dirname."/.htpasswd"))) {
    echo "<h3 class='gepi'>R�pertoire backup non prot�g� :</h3>";
    echo "<p><font color=\"#FF0000\"><b>Le r�pertoire \"/backup\" n'est actuellement pas prot�g�</b></font>.
    Si vous stockez des fichiers dans ce r�pertoire, ils seront accessibles de l'ext�rieur � l'aide d'un simple navigateur.</p>";
    echo "<form action=\"accueil_sauve.php\" name=\"protect\" method=\"post\">\n";
    echo "<table><tr><td>Nouvel identifiant : </td><td><input type=\"text\" name=\"login_backup\" value=\"\" size=\"20\" /></td></tr>";
    echo "<tr><td>Nouveau mot de passe : </td><td><input type=\"password\" name=\"pwd1_backup\" value=\"\" size=\"20\" /></td></tr>";
    echo "<tr><td>Confirmation du mot de passe : </td><td><input type=\"password\" name=\"pwd2_backup\" value=\"\" size=\"20\" /></td></tr></table>";

    echo "<p align=\"center\"><input type=\"submit\" Value=\"Envoyer\" /></p>";
    echo "<input type=\"hidden\" name=\"action\" value=\"protect\" />";
    echo "</form>\n";
    echo "<hr />";
} else {
    echo "<a href='#' onClick=\"clicMenu('2')\" style=\"cursor: hand\"><b>Protection du r�pertoire backup</b></a>|";
    echo "<div style=\"display:none\" id=\"menu2\">";
    echo "<table border=\"1\" cellpadding=\"5\" bgcolor=\"#C0C0C0\"><tr><td>
    <h3 class='gepi'>Protection du r�pertoire backup :</h3>";
    echo "<p>Le r�pertoire \"/backup\" est actuellement prot�g� par un identifiant et un mot de passe.
    Pour acc�der aux fichiers stock�s dans ce r�pertoire � partir d'un navigateur web, il est n�cessaire de s'authentifier.
    <br /><br />Cliquez sur le bouton ci-dessous pour <b>supprimer la protection</b>
    ou bien pour d�finir un nouvel <b>identifiant et un mot de passe</b></p>";
    echo "<form action=\"accueil_sauve.php\" name=\"del_protect\" method=\"post\">\n";
    echo "<p align=\"center\"><input type=\"submit\" Value=\"Modifier/supprimer la protection du r�pertoire\" /></p>";
    echo "<input type=\"hidden\" name=\"action\" value=\"del_protect\" />";
    echo "</form></tr></td></table>\n";
    echo "<hr /></div>";
}

?>
<form enctype="multipart/form-data" action="accueil_sauve.php" method=post name=formulaire>
<H3>Cr�er un fichier de sauvegarde/restauration de la base <?php echo $dbDb; ?></H3>
<span class='small'><b>Remarques</b> :</span>
<ul>
<li><span class='small'>le r�pertoire "documents" contenant les documents joints aux cahiers de texte ne sera pas sauvegard�.</span></li>
<li><span class='small'>Valeur de la <b>dur�e d'une portion</b> en secondes : <input type="text" name="duree" value="<?php echo $_SESSION['defaulttimeout']; ?>" size="5" />
- <a href='#' onClick="clicMenu('1')" style="cursor: hand">Afficher/cacher l'aide</a>.</span></li>
</ul>

<div style="display:none" id="menu1">
<table border="1" cellpadding="5" bgcolor="#C0C0C0"><tr><td>La <b>valeur de la dur�e d'une portion</b> doit �tre inf�rieure � la
<b>valeur maximum d'ex�cution d'un script</b> sur le serveur (max_execution_time).
<br />
<br />Selon la taille de la base et selon la configuration du serveur,
la sauvegarde ou la restauration peut �chouer si le temps n�cessaire � cette op�ration est sup�rieur
au temps maximum autoris� pour l'ex�cution d'un script (max_execution_time).
<br />
Un message du type "Maximum execution time exceeded" appara�t alors, vous indiquant que le processus a �chou�.
<br /><br />
Pour palier cela, <b>ce script sauvegarde et restaure "par portions" d'une dur�e fix�e par l'utilisateur</b> en reprenant le processus � l'endroit o� il s'est interrompue pr�c�demment
jusqu'� ce que l'op�ration de sauvegarde ou de restauration soit termin�e.
</td></tr></table>
</div>

<input type="hidden" name="action" value="dump" />
<center><input type="submit" value="Lancer la sauvegarde" /></center><hr />
</form>

<?php

$handle=opendir('../backup/' . $dirname);
$tab_file = array();
$n=0;
while ($file = readdir($handle)) {
    if (($file != '.') and ($file != '..') and ($file != 'remove.txt')
    //=================================
    // AJOUT: boireaus
    and ($file != 'csv')
    //=================================
    and ($file != '.htaccess') and ($file != '.htpasswd') and ($file != 'index.html')) {
        $tab_file[] = $file;
        $n++;
    }
}
closedir($handle);
arsort($tab_file);

if ($n > 0) {
    echo "<h3>Fichiers de restauration</h3>";
    echo "<p>Le tableau ci-dessous indique la liste des fichiers de restauration actuellement stock�s dans le r�pertoire \"backup\" � la racine de GEPI.";
    echo "<center><table border=\"1\" cellpadding=\"5\" cellspacing=\"1\"><tr><td><b>Nom du fichier de sauvegarde</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
    $m = 0;
    foreach($tab_file as $value) {
        echo "<tr><td><i>".$value."</i>&nbsp;&nbsp;(". round((filesize("../backup/".$dirname."/".$value)/1024),0)." Ko) </td>";
        echo "<td><a href='accueil_sauve.php?action=sup&amp;file=$value'>Supprimer</a></td>";
        echo "<td><a href='accueil_sauve.php?action=restaure_confirm&amp;file=$value'>Restaurer</a></td>";
        echo "<td><a href='savebackup.php?fileid=$m'>T�l�charger</a></td>";
        echo "<td><a href='../backup/".$dirname."/".$value."'>T�l�ch. direct</a></td>";
        echo "</tr>\n";
        $m++;
    }
    clearstatcache();
    echo "</table></center><hr />";
}

echo "<h3>Uploader un fichier (de restauration) vers le r�pertoire backup</h3>";
echo "<form enctype=\"multipart/form-data\" action=\"accueil_sauve.php\" method=\"post\" name=\"formulaire2\">";
$sav_file="";
echo "Les fichiers de sauvegarde sont sauvegard�s dans un sous-r�pertoire du r�pertoire \"/backup\", dont le nom change de mani�re al�atoire r�guli�rement.
Si vous le souhaitez, vous pouvez uploader un fichier de sauvegarde directement dans ce r�pertoire.
Une fois cela fait, vous pourrez le s�lectionner dans la liste des fichiers de restauration, sur cette page.";
/*
echo "<br />Selon la configuration du serveur et la taille du fichier, l'op�ration de t�l�chargement vers le r�pertoire \"/backup\" peut �chouer
(par exemple si la taille du fichier d�passe la <b>taille maximale autoris�e lors des t�l�chargements</b>).
<br />Si c'est le cas, signalez le probl�me � l'administrateur du serveur.
<br /><br />Vous pouvez �galement directement t�l�charger le fichier par ftp dans le r�pertoire \"/backup\".";
*/
echo "<br />Vous pouvez �galement directement t�l�charger le fichier par ftp dans le r�pertoire \"/backup\".";

echo "<br /><br /><b>Fichier � \"uploader\" </b>: <INPUT TYPE=FILE NAME=\"sav_file\" />
<INPUT TYPE=\"HIDDEN\" name=\"action\" value=\"upload\" />
<INPUT type=\"submit\" value=\"Valider\" name=\"bouton1\" />
</form>
<br />";

$post_max_size=ini_get('post_max_size');
$upload_max_filesize=ini_get('upload_max_filesize');
echo "<p><b>Attention:</b></p>\n";
echo "<p style='margin-left: 20px;'>Selon la configuration du serveur et la taille du fichier, l'op�ration de t�l�chargement vers le r�pertoire \"/backup\" peut �chouer
(<i>par exemple si la taille du fichier d�passe la <b>taille maximale autoris�e lors des t�l�chargements</b></i>).
<br />Si c'est le cas, signalez le probl�me � l'administrateur du serveur.</p>\n";

echo "<table border='1' align='center'>\n";
echo "<tr><td style='font-weight: bold; text-align: center;'>Variable</td><td style='font-weight: bold; text-align: center;'>Valeur</td></tr>\n";
echo "<tr><td style='font-weight: bold; text-align: center;'>post_max_size</td><td style='text-align: center;'>$post_max_size</td></tr>\n";
echo "<tr><td style='font-weight: bold; text-align: center;'>upload_max_filesize</td><td style='text-align: center;'>$upload_max_filesize</td></tr>\n";
echo "</table>\n";


?>
</body>
</html>