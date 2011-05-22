<?php
/*
 *
 * Copyright 2011 Pascal Fautrero
 *
 * This file is part of GEPi.
 *
 * GEPi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GEPi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GEPi; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
class calendar {

/*******************************************************************
 *
 *		echo $calendar::getCurrentWeek();
 *		result = string
 *		renvoie les dates du lundi et du samedi de la semaine courante
 *
 *******************************************************************/
	public static function getCurrentWeek() {
		$result = '';
        $ts = time();
        while (date("D", $ts) != "Mon") {
        $ts-=86400;
        }
        setlocale (LC_TIME, 'fr_FR','fra');
        $result .= strftime("%d %b ", $ts);
        $ts+=86400*5;
        $result.= " - ";
        $result .=strftime("%d %b %Y", $ts);
		return $result;
	}
	
/*******************************************************************
 *
 *		echo $calendar::getTypeCurrentWeek();
 *		result = string
 *		renvoie le type de la semaine courante (Semaine A ou Semaine B par exemple)
 *
 *******************************************************************/
	public static function getTypeCurrentWeek(){
		$retour = '';
		$numero_sem_actu = date("W");
		$query = mysql_query("SELECT type_edt_semaine FROM edt_semaines WHERE num_edt_semaine = '".$numero_sem_actu."'");
		if (count($query) == 1) {
			$type = mysql_result($query, 0);
			$retour = $type;
		}
		return $retour;
	}

/*******************************************************************
 *
 *		echo $calendar::getPeriodName(time());
 *		result = string
 *		renvoie le nom des p�riodes contenant le timestamp sp�cifi� (si d�finies dans les edt)
 *
 *******************************************************************/

 	public static function getPeriodName($date_ts)
	{
		$req_periode = mysql_query("SELECT * FROM edt_calendrier");
		$endprocess = false;
		$result = '';
		while (($rep_periode = mysql_fetch_array($req_periode)) AND (!$endprocess)) {
			if (($rep_periode['debut_calendrier_ts'] <= $date_ts) AND ($rep_periode['fin_calendrier_ts'] >= $date_ts)) { 
				$result.= "<p>".$rep_periode['nom_calendrier']."</p>";
				//$endprocess = true;
			}
		}	
		return $result;
	}
/*******************************************************************
 *
 *		echo $calendar::getNumLastWeek();
 *		result = integer
 *		Renvoie le num�ro de la derni�re semaine de l'ann�e civile (52 ou 53)
 *
 *******************************************************************/	

	public static function getNumLastWeek() {

		if (date("m") >= 8) {
			$derniere_semaine=date("W",mktime(0, 0, 0, 12, 28, date("Y")));
		}else{
			$derniere_semaine=date("W",mktime(0, 0, 0, 12, 28, (date("Y")-1)));
		}
		return $derniere_semaine;
	} 

/*******************************************************************
 *
 *		result = array
 *      R�cup�re les dates des lundis et vendredis de toutes les semaines de l'ann�e scolaire courante
 *      Usage : 
 *      $tab = $calendar::getDaysTable();
 *      echo $tab[0]["lundis"];         // renvoie la date du lundi de la semaine 01     
 *      echo $tab[5]["vendredis"];      // renvoie la date du vendredi de la semaine 06 
 *
 *******************************************************************/

	public static function getDaysTable () {

    $tab_select_semaine = array();
    setlocale (LC_TIME, 'fr_FR','fra');
    
    if ((1<=date("n")) AND (date("n") <=8)) {
	    $annee = date("Y");
    }
    else {
	    $annee = date("Y")+1;
    }
    $ts = mktime(0,0,0,1,4,$annee); // d�finition ISO de la semaine 01 : semaine du 4 janvier.
    while (date("D", $ts) != "Mon") {
	    $ts-=86400;
    }

    $semaine = calendar::getNumLastWeek();
    $ts_ref = $ts;
	while ($semaine >=33) {
		$ts-=86400*7;
		$semaine--;
	}
	$i = 0;
	$tab_select_semaine[$i]["lundi"] = strftime("%d", $ts);
    $tab_select_semaine[$i]["mardi"] = strftime("%d", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi"] = strftime("%d", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi"] = strftime("%d", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi"] = strftime("%d", $ts+86400*4);
    $tab_select_semaine[$i]["samedi"] = strftime("%d", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche"] = strftime("%d", $ts+86400*6);

    $tab_select_semaine[$i]["lundi-mois"] = strftime("%m", $ts);
    $tab_select_semaine[$i]["mardi-mois"] = strftime("%m", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi-mois"] = strftime("%m", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi-mois"] = strftime("%m", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi-mois"] = strftime("%m", $ts+86400*4);
    $tab_select_semaine[$i]["samedi-mois"] = strftime("%m", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche-mois"] = strftime("%m", $ts+86400*6);
    while ($semaine <=calendar::getNumLastWeek()) {
	    $ts+=86400*7;
	    $semaine++;
		$i++;
	$tab_select_semaine[$i]["lundi"] = strftime("%d", $ts);
    $tab_select_semaine[$i]["mardi"] = strftime("%d", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi"] = strftime("%d", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi"] = strftime("%d", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi"] = strftime("%d", $ts+86400*4);
    $tab_select_semaine[$i]["samedi"] = strftime("%d", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche"] = strftime("%d", $ts+86400*6);

    $tab_select_semaine[$i]["lundi-mois"] = strftime("%m", $ts);
    $tab_select_semaine[$i]["mardi-mois"] = strftime("%m", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi-mois"] = strftime("%m", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi-mois"] = strftime("%m", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi-mois"] = strftime("%m", $ts+86400*4);
    $tab_select_semaine[$i]["samedi-mois"] = strftime("%m", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche-mois"] = strftime("%m", $ts+86400*6);
    }
	
    $semaine = 1;
    $ts_ref = $ts;
	$i++;
	$tab_select_semaine[$i]["lundi"] = strftime("%d", $ts);
    $tab_select_semaine[$i]["mardi"] = strftime("%d", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi"] = strftime("%d", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi"] = strftime("%d", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi"] = strftime("%d", $ts+86400*4);
    $tab_select_semaine[$i]["samedi"] = strftime("%d", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche"] = strftime("%d", $ts+86400*6);

    $tab_select_semaine[$i]["lundi-mois"] = strftime("%m", $ts);
    $tab_select_semaine[$i]["mardi-mois"] = strftime("%m", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi-mois"] = strftime("%m", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi-mois"] = strftime("%m", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi-mois"] = strftime("%m", $ts+86400*4);
    $tab_select_semaine[$i]["samedi-mois"] = strftime("%m", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche-mois"] = strftime("%m", $ts+86400*6);
    while ($semaine <=30) {
	    $ts+=86400*7;
	    $semaine++;
		$i++;
	$tab_select_semaine[$i]["lundi"] = strftime("%d", $ts);
    $tab_select_semaine[$i]["mardi"] = strftime("%d", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi"] = strftime("%d", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi"] = strftime("%d", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi"] = strftime("%d", $ts+86400*4);
    $tab_select_semaine[$i]["samedi"] = strftime("%d", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche"] = strftime("%d", $ts+86400*6);

    $tab_select_semaine[$i]["lundi-mois"] = strftime("%m", $ts);
    $tab_select_semaine[$i]["mardi-mois"] = strftime("%m", $ts+86400*1);
    $tab_select_semaine[$i]["mercredi-mois"] = strftime("%m", $ts+86400*2);
	$tab_select_semaine[$i]["jeudi-mois"] = strftime("%m", $ts+86400*3);
    $tab_select_semaine[$i]["vendredi-mois"] = strftime("%m", $ts+86400*4);
    $tab_select_semaine[$i]["samedi-mois"] = strftime("%m", $ts+86400*5);
    $tab_select_semaine[$i]["dimanche-mois"] = strftime("%m", $ts+86400*6);
    }

    return $tab_select_semaine;
}	
	
	
}
?>