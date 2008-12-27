<?php
/**
 * @version $Id$
 *
 * @copyright 2008
 *
 */

/**
 * Classe qui permet de dire � Gepi les liens qui existent entre plusieurs tables
 * Il faut pr�ciser le champ de la table1 qui fait r�f�rence � la table2, puis table3,...
 *
 * @author Julien Jocal
 */
class tableMapGepi {
  /**
   * Propri�t� de l'objet qui stocke les diff�rentes tables sous la forme d'un tableau php
   * exemple : $_tables = array(table2', 'table3');
   * o� table2 et table3 sont des tables li�es � une autre par un champ pr�cis (avec ou sans contrainte de cl� �trang�re)
   *
   * @access protected
   * @property array $_tablesfk
   */
    protected $tmg_tablesfk;

  /**
   * Propri�t� de l'objet qui stocke la cl� �trang�re (m�me si elle n'est pas d�finie comme telle dans la table)
   * exemple : $_fk[] = array('champ_table1_vers_table2', 'champ_table2');
   * $_fk[] = array('champ_table1_vers_table3', 'champ_table3');
   * Si la base est construite sous la forme champ id_utilisateurs vers la table utilisateurs
   * Cette propri�t� est remplie automatiquement par la m�thode setFk.
   *
   * @access protected
   * @property array $_fk
   */
    protected $tmg_fk;

  /**
   *�Propri�t� de l'objet qui d�finit la table principale qui est li�e avec $_tables et $_fk
   * Permet ensuite de construire dynamiquement les requ�tes compl�tes
   *
   * @access protected
   * @property string $_table
   */
    protected $tmg_table;

    /**
     * Propri�t�s pour garder quelque part le from, le where, order_by et le limit de la requ�te
     *
     * @access private
     */
    private $tmg_from = NULL;
    private $tmg_where = NULL;
    private $tmg_order_by = NULL;
    private $tmg_limit = NULL;


    /**
     * Constructeur
     * @param string $construct_table
     * @param array $construct_tablesfk
     * @param array $construct_fk
     */

    public function __construct($construct_table, array $construct_tablesfk , array $construct_fk){
        if (!is_string($construct_table) OR !is_array($construct_tablesfk) OR !is_array($construct_fk)){
            throw new Exception('Un des param�tres de construction de ' . __CLASS__ . ' n\'est pas conforme.');
        }else{
            $this->tmg_table       = $construct_table;
            $this->tmg_tablesfk    = $construct_tablesfk;
            $this->tmg_fk          = $construct_fk;

            $this->constructFrom();
            $this->constructWhere();
        }
    }

    /**
     * M�thode qui renvoit la requ�te enti�re
     *
     * @access public
     */
    public function returnRequest(array $test_id = NULL){
        if (isset($test_id)){
            $this->constructWhere($test_id);
        }
        return 'SELECT * ' . $this->tmg_from . $this->tmg_where . $this->tmg_order_by . $this->tmg_limit;
    }

    /**
     * M�thode qui construit la requ�te pour renvoyer � __call de ActiveRecordGepi
     * pour constuire les m�thodes dynamiques qui utilisent les cl� �trang�res
     *
     * @param string $option
     */
    public function returnCall($option){
        if (in_array($option, $this->tmg_tablesfk)){
            $retour = 'SELECT * FROM ' . $this->tmg_table . ', ' . $option;
            // on d�termine maintenant le where qui va bien
        }
    }

    /**
     * On va coder les relations qui existes pour pouvoir r�cup�rer toutes les informations
     * sur des jointure qui utilisanet un table de jointure.
     * Exemple : utilisateurs+j_groupes_professeurs+groupes
     *
     * @param array $_tables_join
     */
    public function addTableJoin(array $_tables_join){

    }

    public function addClauses($limit = NULL, array $_ordre = NULL){
        $this->constructLimit($limit);
        $this->constructOrderBy($_ordre);
    }

    /**
     * M�thode qui permet de construire le from de la requ�te
     *
     * @access private
     * @return void
     */
    private function constructFrom(){
        $from  = array();
        $from[] = $this->tmg_table;
        $nbre = count($this->tmg_tablesfk);
        $i = 0;
        for($i = 0 ; $i < $nbre ; $i++){
            $from[] = $this->tmg_tablesfk[$i];
        }
        $this->tmg_from = ' FROM ' . join(", ", $from);
    }

    /**
     * M�thode qui permet de construire la clause where en tenant compte de toutes les informations
     * 
     * @access private
     * @return void
     */
    private function constructWhere(array $test_id = NULL){

        $clause_where = array ();

        $nbre = count($this->tmg_tablesfk);
        for($a = 0 ; $a < $nbre ; $a++){
            // Si la cle de jointure n'est pas pr�cis�e pour la table origine
            // alors elle est de la forme id_nomdelatableausingulier
            $_cle_1 = isset($this->tmg_fk[$a][0]) ? $this->tmg_fk[$a][0] : 'id_' . substr($this->tmg_tablesfk[$a], 0, -1);
            // Si la cl� de la table appel�e n'est pas pr�cis�e, alors c'est id
            $_cle_2 = isset($this->tmg_fk[$a][1]) ? $this->tmg_fk[$a][1] : 'id';

            $clause_where[] = $this->tmg_table . '.' . $_cle_1 . '=' . $this->tmg_tablesfk[$a] . '.' . $_cle_2;

        }

        // Ici, on ajoute la demande pr�cise de la requ�te
        // $test_id[0] est le champ de la table et $test-id[1] est sa valeur recherch�e.
        if (isset($test_id) AND is_array($test_id)){
            $clause_where[] = $this->tmg_table . '.' . $test_id[0] . ' = ' . stripcslashes($test_id[1]);
        }else{
            $clause_where[] = 'id.' . $this->tmg_table . '= *';
        }

        $this->tmg_where = ' WHERE ' . join(" AND ", $clause_where);
    }

    /**
     * M�thode qui permet de construire la clause order_by de la requ�te MySql
     * TODO Il manque la possibilit� d'ajouter ASC/DESC
     *
     * @param array $_ordre
     * @return void
     */

    private function constructOrderBy(array $_ordre = NULL){
        if (!isset($_ordre)){
        }else{
            $this->tmg_order_by = ' ORDER BY ' . join(', ', $_ordre);
        }
    }

    /**
     * M�thode qui renvoie le LIMIT de la requ�te
     *
     * @param integer $limite
     * @return string
     */
    private function constructLimit($limite = NULL){
        if (isset($limite) AND is_numeric($limite)){
            $this->tmg_limit = ' LIMIT ' . $limite;
        }else{
        }
    }

}

/* ========= TESTS ==============

$tables = array('groupes', 'salle_cours');
$cles = array('', array('id_salle', 'id_salle'));
$test = new tableMapGepi('edt_cours', $tables, $cles);
$test->addClauses('1', array('id_definie_periode')); // On ajoute des �l�ments � la requ�te
try{
       include('lib/erreurs.php');
    if ($cnx = new PDO('mysql:host=localhost;dbname=gepi', 'root', 'matteo')){

    }else{
        throw new Exception('Impossible d\'ouvrir une connexion avec Mysql');
    }
    echo $test->returnRequest(array('id_groupe', '1')) . '<br />Le r�sultat est : <br />';
    if ($_query = $cnx->query($test->returnRequest(array('id_groupe', '1')))){
    //if ($_query = $cnx->query("SELECT * FROM edt_cours, groupes, salle_cours WHERE edt_cours.id_groupe=groupes.id AND edt_cours.id_salle=salle_cours.id_salle AND edt_cours.id_groupe = 1 ORDER BY id_definie_periode DESC")){
        // rien
    }else{
        throw new Exception("La requ�te ne doit pas �tre bonne || " . $test->returnRequest(array('id_groupe', '1')));
    }

    $testeur = $_query->fetchAll(PDO::FETCH_OBJ);
    echo '<pre>';
    print_r($testeur);
    echo '</pre>';
    
}catch (Exception $e){
    echo '<pre>';
    print_r($e);
    echo '</pre>';
    affExceptions($e);
}
*/
?>
