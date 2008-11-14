<?php
/**
 *
 *
 * @version $Id$
 *
 * Copyright 2001, 2007 Thomas Belliard, Laurent Delineau, Eric Lebrun, Stephane Boireau, Julien Jocal
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

// ====== SECURITE =======

if (!$_SESSION["login"]) {
    header("Location: ../logout.php?auto=2");
    die();
}

echo '
	<ol id="essaiMenu">
    <li><a href="saisir_absences.php">Saisie</a></li>
		<li><a href="suivi_absences.php">Suivi</a></li>
		<li><a href="envoi_absences.php">Envoi aux familles</a></li>
		<li><a href="stats_absences.php">Statistiques</a></li>
		<li><a href="exports_absences.php">Exports</a></li>
	</ol>
	';
?>
