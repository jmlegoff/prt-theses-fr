<?php
/*
	Requête vers theses.fr
	- le patron de l'url 
	- les valeurs des différents paramètres de la requête
	- les facettes qui filtrent le résultat de la requête
	
	Note : L'utilisation de MessageFormat nécessite l'activation de php_intl dans php.ini	
*/
class ThesesFrRequest {
	
	/*
	 * Url de base vers les thèses soutenues
	 */
	private static $baseUrlThesesSoutenues = "http://www.theses.fr/";

	/*
	 * Url de base vers les thèses en cours
	 */
	private static $baseUrlThesesEnCours = "http://www.theses.fr/sujets/";
	
	/* 
	 *	Patron de la liste des paramètres pour la recherche dans les thèses soutenues
	 */
	private static $parametersThesesSoutenues = "q={0}&checkedfacets={1}&maxnumber={2}&sort={3}&fq=dateSoutenance:{4}&start={5}&status=status:{6}&access=accessible:{7}&type=avance&format=json&facet.mincount=1&facet.limit=50";

	/* 
	 *	Patron de la liste des paramètres pour la recherche dans les thèses en cours
	 */
	private static $parametersThesesEnCours = "q={0}&checkedfacets={1}&maxnumber={2}&sort={3}&fq=dateSoutenance:{4}&start={5}&type=avance&format=json&facet.mincount=1&facet.limit=50";

	/*
	 * Indicateur thèses en cours ou soutenues
	 */
	private $thesesEnCours = false; 
	
	/*
	 * Met à jour l'indicateur thesesEnCours
	 */
	public function setThesesEnCours($thesesEnCours) {
		$this->thesesEnCours = $thesesEnCours;
	}
	
	/*
	 * La requête comporte le paramètre 'q' qui défini le sous-ensemble des
	 * thèses que l'on souhaite consulter et filtrer (avec les autres 
	 * paramètres)
	 * La paramètre 'q' est ici constitué de la composante prt et la 
	 * composateur utilisateur qui définissent le sous-ensemble final :
	 * q=(prtSubset) AND (userSearch)
	 * par exemple
	 * q=(disciplines:traduction) AND (motCleRAs:Terminologie)
	 */
	 
	/*
	 * Noms des paramètres pris en compte dans la requête.
	 */
	const PARAM_Q = 'q';
	const PARAM_CHECKED_FACETS = 'checkedfacets';
	const PARAM_MAX_NUMBER = 'maxnumber';
	const PARAM_SORT = 'sort';
	const PARAM_DATE_SOUTENANCE = 'dateSoutenance';
	const PARAM_START = 'start';	
	const PARAM_STATUS = 'status';
	const PARAM_ACCESS = 'access';
	
	/*
	 * Les paramètres de la requête : nom -> valeur
	 * Tableau initialisé avec les valeurs par défaut 
	 */
	public $parameters = array
		(
		self::PARAM_Q=>'',
		self::PARAM_CHECKED_FACETS=>'',
		self::PARAM_MAX_NUMBER=>'10',
		self::PARAM_SORT=>'dateSoutenance desc',
		self::PARAM_DATE_SOUTENANCE=>'',
		self::PARAM_START=>'',
		self::PARAM_STATUS=>'',
		self::PARAM_ACCESS=>'',
		);
	
	/*
	 * Met à jour la valeur d'un paramètre
	 */
	public function setParameter($name, $value) {
		$this->parameters[$name] = $value;
	}	

	/*
	 * Noms des paramètres pris en compte dans la requête et qui sont mis 
	 * à jour par l'utilisateur.
	 * Les autres noms ne sont pas définis en const afin de ne pas 
	 * être accessibles
	 */
	const FACET_ETABLISSEMENT = 'etablissement';
	const FACET_DISCIPLINE = 'discipline';
	const FACET_ECOLE_DOCTORALE = 'ecoleDoctorale';
	const FACET_LANGUE_THESE = 'langueThese';
	const FACET_DIRECTEUR_THESE_NP = 'directeurTheseNP';	

	/*
	 * Les facettes de la requêtes
	 */
	public $facets = array
		(
		self::FACET_ETABLISSEMENT=>array(),
		self::FACET_DISCIPLINE=>array(),
		self::FACET_ECOLE_DOCTORALE=>array(),
		self::FACET_LANGUE_THESE=>array(),
		self::FACET_DIRECTEUR_THESE_NP=>array(),
		);
	 
	/*
	 * Ajoute une facette
	 */
	public function addFacetValue($facetName, $facetValue) {
		$this->facets[$facetName][] = $facetValue;
	}
	
	/*
	 * Retourne vrai si il y a au moins une valeur dans les facettes 
	 */
	public function hasFacetsValues() {
		return count($this->facets, COUNT_RECURSIVE) > count($this->facets);
	}
	
	public function __construct() {
		// Initialisation de la fenêtre des dates de soutenance prises en compte
		$dateSoutenance = "[1965-01-01T23:59:59Z TO ".date("Y")."-12-31T23:59:59Z]";
		$this->setParameter(self::PARAM_DATE_SOUTENANCE, $dateSoutenance);
    }
	
	/*
	 * Retourne la partie de l'url constituée des paramètres 
	 */ 
	public function getUrlParameters() {
		// Mise à jour des facettes
		$checkedFacets = '';
		foreach($this->facets as $key => $facetValues) {
			foreach($facetValues as $value) {
				$checkedFacets .= $key."=".$value.";";
			}  
		}
		$this->setParameter(self::PARAM_CHECKED_FACETS, $checkedFacets);
		// Url 
		if ($this->thesesEnCours) {
			$urlParameters = self::$parametersThesesEnCours;
		} else {
			$urlParameters = self::$parametersThesesSoutenues;
		}		
		// echo MessageFormatter::formatMessage('fr_FR', $urlParameters, array_values($this->parameters)) . '<br>';
		// Remplissage du patron de la requête avec les valeurs encodées des paramètres 
		return MessageFormatter::formatMessage('fr_FR', $urlParameters, array_map('urlencode', array_values($this->parameters)));		
	}
	
	/*
	 *	Retourne l'url forgée à partir du patron de la requête 
	 *  et de la valeur encodées des paramètres
	 */
	public function getUrl() {
		// Url 
		if ($this->thesesEnCours) {
			$url = self::$baseUrlThesesEnCours . '?' . $this->getUrlParameters();
		} else {
			$url = self::$baseUrlThesesSoutenues . '?' . $this->getUrlParameters();
		}
		return $url;
	}
	

	
}
?>