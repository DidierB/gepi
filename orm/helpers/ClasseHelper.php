<?php

/**
 * Description of ClasseHelper
 *  Classe qui implemente des methodes statiques pour g�r� un groupe ou un tableau de groupe
 *
 * @author joss
 */
class ClasseHelper {

 	/**
	 * Compare deux groupes par ordre alphab�tique de leur nom (avec les noms de classes d'eleves associ�e)
	 *
	 * @param      Classe $a Le premier groupe a coparer
	 * @param      Classe $a Le deuxieme groupe a comparer
	 * @return     int un entier, qui sera inf�rieur, �gal ou sup�rieur � z�ro suivant que le premier argument est consid�r� comme plus petit, �gal ou plus grand que le second argument.
	 */
	public static function compareClasse($a, $b) {
		//echo($a->getDescriptionAvecClasses());
		return strcmp($a->getNomComplet(), $b->getNomComplet());
	}

 	/**
	 *
	 * Classe un tableau de groupe par ordre alphab�tique de leur nom (avec les noms de classes d'eleves associ�e)
	 *
	 * @param      array $groupes Le tableau de groupes
	 * @return     array $groupes Un tableau de groupe ordonn�s
	 * @throws     PropelException - if unable to parse/validate the date/time value.
	 */
	public static function orderByNomComplet(PropelObjectCollection $classes) {
		$classes->uasort(array("ClasseHelper", "compareClasse"));
		return $classes;
	}
}
?>
