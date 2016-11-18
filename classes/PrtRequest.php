<?php
/*
	Abstraction d'une requête vers le site prt composée 
	- de la requête vers theses.fr associée
	- du numéro de page courant
	- du statut des thèses consultées
	- de la recherche utilisateur
	
*/
class PrtRequest {
	/*
	 * L'url de base de la page
 	 */
	public $baseUrl;
	
	/*
	 * Les paramètres
	 */ 
	public $qsvars;
	 
	/*
	 * La requête vers theses.fr associée
	 */
	public $tfr;

	/*
	 * Noms des paramètres pris en compte dans la requête Prt.
	 */
	const PARAM_USER_SEARCH = 'userSearch';
	const PARAM_STATUT = 'statut';
	const PARAM_PAGE_NUMBER = 'pageNumber';	

	/*
	 * Partie de la requête qui définit le sous-ensemble utilisateur des 
	 * thèses consultées
	 */
	public $userSearch;
	
	/*
	 * Partie de la requête qui définit le sous-ensemble des thèses consultées
	 * dédiées à la traductologie
	 */
	public $prtSubset;
	
	/*
	 * Page courante de la consultation des résultats de la requête
	 */
	public $pageNumber = 1;
	
	
	/*
	 * Statut des thèses consultées
	 * - encours
	 * - fini
	 * - finidispo
	 * Ce statut impacte plusieurs paramètres de la requête : sujet (ajouté
	 * en début d'url), status et access
	 */
	public $thesesStatut;
	public static $STATUTS = array('encours', 'fini','finidispo');
	
	/*
	 * Met à jour les paramètres de la requête vers theses.fr à partir 
	 * du statut des thèses à sélectionner
	 */
	public function setThesesStatut($statut) {
		$this->thesesStatut = $statut;
		switch ($statut) {
			case self::$STATUTS[0] :
				$this->tfr->setThesesEnCours(true); 
				break;
			case self::$STATUTS[1] :
				$this->tfr->setParameter(ThesesFrRequest::PARAM_STATUS, 'soutenue'); break;		
			case self::$STATUTS[2] :
				$this->tfr->setParameter(ThesesFrRequest::PARAM_ACCESS, 'oui'); break;
		}
	}
	
	public function setPageNumber($pageNumber) {
		$this->pageNumber = $pageNumber;
		// Mise à jour du numéro de premier résultat consulté
		$this->tfr->setParameter(ThesesFrRequest::PARAM_START, ($this->pageNumber - 1) * $this->tfr->parameters[ThesesFrRequest::PARAM_MAX_NUMBER]);
	}
	
	public function setPrtSubset($prtSubset) {
		$this->prtSubset = $prtSubset;
		// Mise à jour de la requête theses.fr à partir des composantes prt et utilisateurs
		$q = $this->prtSubset;
		if ($this->userSearch != '') {
			$q = '(' . $q . ') AND ' . $this->userSearch;
		}
		$this->tfr->setParameter(ThesesFrRequest::PARAM_Q, $q);		
	}
	
	public function __construct($tfr) {
		$this->tfr = $tfr;
	}
	
	/*
	 * L'url forgée à partir de l'objet courant
	 */
	public function getUrl() {
		return $this->baseUrl . '?' . http_build_query($this->qsvars);
	}

	/*
	 * L'url forgée à partir de l'objet courant et des nouvelles valeurs de paramètres 
	 */
	public function getUrlWithNewQsvars($newqsvars) {
		$qsvars = $this->qsvars;
		foreach ($newqsvars as $qsvarName => $qsvarValue) {
			$qsvars[$qsvarName] = $qsvarValue;
		}
		return $this->baseUrl . '?' . http_build_query($qsvars);
	}
	
	/*
	 * L'url forgée à partir de l'objet courant en enlevant un paramètre 
	 */
	public function getUrlWithoutQsvar($qsvarName) {
		$qsvars = $this->qsvars;
		unset($qsvars[$qsvarName]);
		return $this->baseUrl . '?' . http_build_query($qsvars);
	}
	
	/*
	 * L'url forgée à partir de l'objet courant en enlevant une valeur de facette 
	 */
	public function getUrlWithoutFacetValue($key, $value) {
		$qsvars = $this->qsvars;
		$checkedFacets = $qsvars[ThesesFrRequest::PARAM_CHECKED_FACETS];
		$checkedFacets = str_replace($key.'='.$value.';', '', $checkedFacets);
		$qsvars[ThesesFrRequest::PARAM_CHECKED_FACETS] = $checkedFacets;
		return $this->baseUrl . '?' . http_build_query($qsvars);
	}

	/*
	 * L'url forgée à partir de l'objet courant en ajoutant une valeur de facette 
	 */
	public function getUrlWithNewFacetValue($key, $value) {
		$qsvars = $this->qsvars;
		$checkedFacets = $qsvars[ThesesFrRequest::PARAM_CHECKED_FACETS];
		if (!empty($checkedFacets)) {
			$checkedFacets .= ';';
		}
		$checkedFacets .= $key.'='.$value.';';
		$qsvars[ThesesFrRequest::PARAM_CHECKED_FACETS] = $checkedFacets;
		return $this->baseUrl . '?' . http_build_query($qsvars);
	}

		/*
	 * L'url forgée à partir de l'objet courant en modifiant la valeur de la page cible 
	 */
	public function getUrlWithPageNumber($pageNumber) {
		$qsvars = $this->qsvars;
		$qsvars[self::PARAM_PAGE_NUMBER] = $pageNumber;
		return $this->baseUrl . '?' . http_build_query($qsvars);
	}

}
?>