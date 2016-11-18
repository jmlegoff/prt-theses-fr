<?php
/*
 * Fabrique d'objets construits à partir des paramètres de la requête http en cours
 */
class RequestFactory {
	/* 
	 * Construit une requête vers theses.fr à partir des paramètres  
	 * contenus dans la requête courante
	 */
	public static function buildThesesFrRequestFromGETParameters() {
		$tfr = new ThesesFrRequest();
		// Mise à jour des paramètres de requêtes identiques
		// entre la requête utilisateur et l'appel à theses.fr
		foreach ($tfr->parameters as $name => $initValue){
			$value = self::getParamValue($name);
			if ($value != '') {
				$tfr->parameters[$name] = $value;
				if ($name == 'checkedfacets') {
					self::extractCheckedFacetsFromUrl($tfr, $value);
				}
			}
		}
		return $tfr;
	}

	/*
	 * Extrait les facets de l'url courante et les ajoute à la requête tfr
	 * Exemple : langueThese=fr;etablissement=Paris%201;
	 * Découpage par ";" puis par "="
	 */
	private static function extractCheckedFacetsFromUrl($tfr, $checkedfacetsString) {
		$checkedfacets = explode(";", $checkedfacetsString);
		foreach ($checkedfacets as $checkedfacetString) {
			if (!empty($checkedfacetString)) {
				$checkedfacet = explode("=", $checkedfacetString);
				$tfr->addFacetValue($checkedfacet[0], $checkedfacet[1]);				
			}
		}		
	}
	
	/* 
	 * Construit une abstratction de la requête en cours 
	 */
	public static function buildPrtRequestFromGETParameters() {		
		$tfr = self::buildThesesFrRequestFromGETParameters();
		$prtRequest = new prtRequest($tfr);
		// Mise à jour des parties de l'url pour construction des rendus
		$pageURL .= 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		list($urlpart, $qspart) = array_pad(explode('?', $pageURL), 2, '');
		parse_str($qspart, $qsvars);
		$prtRequest->baseUrl = $urlpart;
		$prtRequest->qsvars = $qsvars;
		// Mise à jour de la partie utilisateur de la requête
		$prtRequest->userSearch = self::getParamValue(PrtRequest::PARAM_USER_SEARCH);
		// Mise à jour du statut des requêtes consultées
		$prtRequest->setThesesStatut(self::getParamValue(PrtRequest::PARAM_STATUT));
		// Mise à jour de la page courante
		$pageNumber = self::getParamValue(PrtRequest::PARAM_PAGE_NUMBER);
		if ($pageNumber != '') {
			$prtRequest->setPageNumber($pageNumber);
		} else {
			$prtRequest->setPageNumber(1);
		}
		return $prtRequest;
	}
	
	private static function getParamValue($paramName) {
		if (isset($_GET[$paramName])){
		  return $_GET[$paramName];
		}
		else{
		  return '';
		}		
	}
	
}
?>