<?php

require_once dirname(__FILE__) . '/../../../tools/helpers/orm/GepiEmptyTestBase.php';

/**
 * Test class for UtilisateurProfessionnel.
 *
 */
class EleveTest extends GepiEmptyTestBase
{
	protected function setUp()
	{
		parent::setUp();
		GepiDataPopulator::populate();
	}

	public function testGetPeriodeNote()
	{
		$florence_eleve = EleveQuery::create()->findOneByLogin('Florence Michu');
		$periode_col = $florence_eleve->getPeriodeNotes();
		$this->assertEquals('3',$periode_col->count());
		$this->assertEquals('1',$periode_col->getFirst()->getNumPeriode());
		$this->assertEquals('3',$periode_col->getLast()->getNumPeriode());
				
		$periode = $florence_eleve->getPeriodeNote();
		$this->assertNull($periode,'� la date en cours, il ne doit y avoir aucune p�riode d assign�');
		
		$periode_2 = $florence_eleve->getPeriodeNoteOuverte();
		$this->assertNotNull($periode_2,'La p�riode de note ouverte de florence ne doit pas �tre nulle');
		$this->assertEquals('2',$periode_2->getNumPeriode());
		
		//on va fermer la p�riode
		//$periode = new PeriodeNote();
		$periode_2->setVerouiller('O');
		$periode_2->save();
		$florence_eleve->reload();
		$periode_col = $florence_eleve->getPeriodeNotes();
		$this->assertEquals('3',$periode_col->count());
		$this->assertNull($florence_eleve->getPeriodeNoteOuverte(),'Apr�s verrouillage la p�riode ouverte de note de florence doit �tre nulle');
		
		$periode = $florence_eleve->getPeriodeNote(new DateTime('2010-10-01'));
		$this->assertNotNull($periode);
		$this->assertEquals('1',$periode->getNumPeriode());
		
		$periode = $florence_eleve->getPeriodeNote(new DateTime('2010-12-05'));
		$this->assertNotNull($periode);
		$this->assertEquals('2',$periode->getNumPeriode());
		
	}
	
	public function testGetClasse()
	{
		$florence_eleve = EleveQuery::create()->findOneByLogin('Florence Michu');
		$classe = $florence_eleve->getClasse(1);//on r�cup�re la classe pour la p�riode 1
		$this->assertNotNull($classe,'La classe de florence ne doit pas �tre nulle pour la p�riode 1');
		$this->assertEquals('6ieme A',$classe->getNom());

		$classe = $florence_eleve->getClasse(5);//on r�cup�re la classe pour la p�riode 1
		$this->assertNull($classe,'La classe de florence doit pas �tre nulle pour la p�riode 5');

		$classe = $florence_eleve->getClasse(new DateTime('2010-10-01'));
		$this->assertNotNull($classe,'La classe de florence ne doit pas �tre nulle pour la date 2010-10-01 (p�riode 1)');
		$this->assertEquals('6ieme A',$classe->getNom());
		
		$classe = $florence_eleve->getClasse(new DateTime('2005-01-01'));
		$this->assertNull($classe,'La classe de florence doit �tre nulle pour la date 2005-01-01');
	}
}
