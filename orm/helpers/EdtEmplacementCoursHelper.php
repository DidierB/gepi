<?php
/**
 * Description of EdtEmplacementCoursHelper
 *  Classe qui implemente des methodes statiques pour g�r� un groupe ou un tableau de groupe
 *
 * @author joss
 */
class EdtEmplacementCoursHelper {

 	/**
	 * Compare deux edtEmplacementCours par ordre chronologique
	 *
	 * @param      EdtEmplacementCours $groupeA Le premier EdtEmplacementCours a coparer
	 * @param      EdtEmplacementCours $groupeB Le deuxieme EdtEmplacementCours a comparer
	 * @return     int un entier, qui sera inf�rieur, �gal ou sup�rieur � z�ro suivant que le premier argument est consid�r� comme plus petit, �gal ou plus grand que le second argument.
	 */
	function compareEdtEmplacementCours($a, $b) {
		throw new PropelException("Pas encore implemente");
		return 0;
	}

 	/**
	 *
	 * Classe un tableau de groupe par ordre alphab�tique de leur nom (avec les noms de classes d'eleves associ�e)
	 *
	 * @param      PropelObjectCollection $edtEmplacementCours La collection d'emplacement de cours
	 * @return     PropelObjectCollection $edtEmplacementCours Un collection ordonn�s d'emplacement de cours
	 * @throws     PropelException - si les types d'entr�es ne sont pas bon.
	 */
	public static function orderChronologically(PropelObjectCollection $edtEmplacementCours) {
		$edtEmplacementCours->uasort(array("EdtEmplacementCoursHelper", "compareEdtEmplacementCours"));
		return $edtEmplacementCours;
	}
}
?>

