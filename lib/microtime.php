<?php
if ($gepiShowGenTime == "yes") {
	$endtime = microtime();
	$result = $endtime - $starttime;
echo "<p class='microtime'>Page g�n�r�e en ". substr($result,0,5) . " sec</p>";
}
?>