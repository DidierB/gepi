<?php
/*
 * @version $Id$
 *
 * Copyright 2001, 2005 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
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


// Le package phpCAS doit etre stock� dans un sous-r�pertoire ��CAS��
// dans un r�pertoire correspondant a l'include_path du php.ini (exemple : /var/lib/php)
include_once('CAS/CAS.php');

// cas.sso.php est le fichier d'informations de connexions au serveur cas
// Le fichier cas.sso.php doit etre stock� dans un sous-r�pertoire ��CAS��
// dans un r�pertoire correspondant a l'include_path du php.ini (exemple : /var/lib/php)
include('CAS/cas.sso.php');

// declare le script comme un client CAS
// Le dernier argument (true par d�faut) donne la possibilit� � phpCAS d'ouvrir une session php.
// Si tel est le cas, l'authentification CAS n'est pas r�percut�e dans GEPI (et il faut se r�authentifier dans l'appli),
// car le "start_session()" de l'application (environ ligne 232 dans le fichier "session.inc.php") ne marche pas ===> la session a �t� ouverte par phpCAS => inexact dans gepi 1.5.x, le session_start est vers la ligne 375
// et les variables de session positionn�es par la suite par gepi ne sont pas r�cup�rables.

phpCAS::client(CAS_VERSION_2_0,$serveurSSO,$serveurSSOPort,$serveurSSORacine,false);

phpCAS::setLang('french');

// redirige vers le serveur CAS si n�cessaire
phpCAS::forceAuthentication();

// A ce stade, l'utilisateur est authentifi�
$user  = phpCAS::getUser();
$login = phpCAS::getUser();

?>