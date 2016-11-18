<?php
/*
 * Classe de génération du rendu html des thèses 		
 */
class ThesesRenderer {
	
	/*
	 * La requête à l'origine de ce rendu
	 */
	private $prtRequest;
	
	/*
	 * Les thèses sous la forme d'un objet json
	 */
	private $theses;
	
	/*
	 * Le nombre de thèses résultat de la requête
	 */
	public $nbTheses;
	
	/*
	 * Le nombre de page résultats
	 */
	private $nbPages = 1;
	
	private $facetsLabels = array
		(
		ThesesFrRequest::FACET_ETABLISSEMENT=>'Établissements',
		ThesesFrRequest::FACET_DISCIPLINE=>'Disciplines',
		ThesesFrRequest::FACET_ECOLE_DOCTORALE=>'Écoles Doctorales',
		ThesesFrRequest::FACET_LANGUE_THESE=>'Langues',
		ThesesFrRequest::FACET_DIRECTEUR_THESE_NP=>'Directeurs de thèse',
		);

	public $sortLabels = array
		(
			'dateSoutenance desc' => 'Par date de soutenance',
			'titreTri asc' => 'Par titre',
			'disciplineTri asc' => 'Par discipline'
		);
		
	public function __construct($prtRequest, $theses) {
        $this->prtRequest = $prtRequest;
        $this->theses = $theses;	
		$this->nbTheses =  $this->theses->response->numFound;	
		if ($this->nbTheses > 0) {
			$this->nbPages = ceil($this->nbTheses/$this->prtRequest->tfr->parameters[ThesesFrRequest::PARAM_MAX_NUMBER]);			
		}		
    }
	
	private function labelStatut($statut) {
		$labelStatut = '';
		switch($statut) {
			case PrtRequest::$STATUTS[0] : $labelStatut = 'En préparation'; break;
			case PrtRequest::$STATUTS[1] : $labelStatut = 'Soutenue'; break;
			case PrtRequest::$STATUTS[2] : $labelStatut = 'Soutenue et accessible en ligne'; break;			
		}
		return $labelStatut;
	}
	
	
	/*
	 * Affiche les facettes de type statut 
	 */
	public function renderFacetStatut() {
		$out = '';
		foreach(PrtRequest::$STATUTS as $STATUT) {
			if ($STATUT == $this->prtRequest->thesesStatut) {
				$out .= $this->renderFacetStatutSelected($STATUT);
			} else {
				$out .= $this->renderFacetStatutNotSelected($STATUT);
			}
		}
		return $out;
	}
	
	/*
	 * Affiche une facette de type statut quand elle est sélectionnée
	 */
	private function renderFacetStatutSelected($statut) {
		$url = $this->prtRequest->getUrlWithoutQsvar(PrtRequest::PARAM_STATUT);
		$out = '<li><span class="facet-label"><span class="selected">'.$this->labelStatut($statut).'</span>';
		$out .= '<a href="'.$url .'" class="remove"><span class="fa fa-times-circle"></span><span class="sr-only">[remove]</span></a>';
		$out .= '</span></li>'; 
		return $out;
	}
	
	/*
	 * Affiche une facette de type statut quand elle n'est pas sélectionnée
	 */
	private function renderFacetStatutNotSelected($statut) {
		$url = $this->prtRequest->getUrlWithNewQsvars(array(PrtRequest::PARAM_STATUT => $statut));
		$out = '<li><span class="facet-label"><a href="'.$url.'">'.$this->labelStatut($statut).'</a></span></li>'; 
		return $out;
	}

	/*
	 * Les valeurs de facettes sont stockées dans un tableau : 
	 * ['valeur 1', nb valeurs 1, 'Valeur 2', nb valeurs 2]
	 * Ex : ['Paris 1', 324, 'Rennes 2', 27]
	 * Nous récupérons les valeurs dans un tableau, les comptes dans un autre
	 */
	private function getFacetValuesAndCount($jsonFacet) {
		$facetValuesAndCount = array();
		$facetValues = array_filter($jsonFacet, "is_string");
		foreach($facetValues as $facetIndex => $facetValue) {
			$facetValuesAndCount[$facetValue] = $jsonFacet[$facetIndex + 1];
		}
		return $facetValuesAndCount;
	}
	
	/*
	 * Affiche dans un cadre la liste des valeurs disponibles pour une facette 
	 */
	public function renderFacet($facetName) {
		$out = '';
		$selectedFacetValues = $this->prtRequest->tfr->facets[$facetName];
		// Si la facette est présente dans la réponse de theses.fr, nous créons un cadre
		if($this->theses->facet_counts->facet_fields->$facetName)  { 
			
			// Si l'utilisateur a sélectionné l'une des valeurs de cette facette
			// nous développons le cadre correspondant
			if(count($selectedFacetValues) > 0){
				$classFacet = "facet_limit-active";
				$collapse = "collapse in";
			}
			else {
				$classFacet = "";
				$collapse = "collapse";
			}
			$out .='<div class="panel panel-default facet_limit theses-'.$facetName.' '. $classFacet.'">';
			$out .='<div data-target="#facet-'.$facetName.'" data-toggle="collapse" class="collapse-toggle panel-heading collapsed ">';
			$out .='<h5 class="panel-title"><a href="#" data-no-turbolink="true">'. $this->facetsLabels[$facetName].'</a></h5>';
			$out .='</div>';
			$out .='<div class="panel-collapse facet-content '. $collapse .'" id="facet-'.$facetName.'" style="height: auto;">';
			$out .='<div class="panel-body facet-panel">';
			$out .='<ul class="facet-values list-unstyled">';
			// Pour toutes les valeurs de facette retournées 
			$facetValuesAndCount = $this->getFacetValuesAndCount($this->theses->facet_counts->facet_fields->$facetName);
			foreach ($facetValuesAndCount as $facetValue => $facetCount){
				$out .= "<li><span class='facet-label'>";
				// Si la valeur fait partie de celles sélectionnées par l'utilisateur
				if(in_array($facetValue, $selectedFacetValues)) {
					$out .= "<span class='selected'>".	$facetValue ."</span>";
					$out .= '<a href="'.$this->prtRequest->getUrlWithoutFacetValue($facetName, $facetValue).'" class="remove"><span class="fa fa-times-circle"></span><span class="sr-only">[remove]</span></a>';
					$class = 'selected';
				}
				else {
					$class = '';
					$out .= "<a href='".$this->prtRequest->getUrlWithNewFacetValue($facetName, $facetValue)."' class='facet_select'>".	$facetValue ."</a>";
				}
				$out .= "</span>";

				$out .= "<span class='".$class." facet-count'>".$facetCount."</span>";
				$out .= "</li>";
			}
			$out .= "</ul>";
			$out .= "</div>";
			$out .= "</div>";
			$out .= "</div>";
		}		
		return $out;
	}
	
	/*
	 * Affiche les filtres courant
	 */ 
	public function renderCurrentFilters() {
		$out = '';
		if(!empty($this->prtRequest->userSearch)){
			$url = $this->prtRequest->getUrlWithoutQsvar(PrtRequest::PARAM_USER_SEARCH);
			$label = 'Mots-clés';
			$value = $this->prtRequest->userSearch;
			$out.= $this->renderCurrentFilterButton($url, $label, $value);
		}
		if(!empty($this->prtRequest->thesesStatut)){
			$url = $this->prtRequest->getUrlWithoutQsvar(PrtRequest::PARAM_STATUT);
			$label = 'Statut';
			$value = $this->labelStatut($this->prtRequest->thesesStatut);
			$out.= $this->renderCurrentFilterButton($url, $label, $value);
		}
		foreach($this->prtRequest->tfr->facets as $key => $facetValues) {
			foreach($facetValues as $value) {
				// La facette langue n'est pas prise en compte (seulement les thèses en français)
				if ($key != ThesesFrRequest::FACET_LANGUE_THESE) {
					$label = $this->facetsLabels[$key];
					$url = $this->prtRequest->getUrlWithoutFacetValue($key, $value);
					$out.= $this->renderCurrentFilterButton($url, $label, $value);
				}
			}  
		}
		return $out;
	}
	
	/*
	 * Affiche un bouton de suppression pour un filtre 
	 */
	private function renderCurrentFilterButton($url, $label, $value) {
		$out='<span class="btn-group appliedFilter constraint filter filter-keywords">';
		$out.='<a class="constraint-value btn btn-sm btn-default btn-disabled" href="">';
		$out.='<span class="filterName">'.$label.'</span> ';
		$out.=' <span class="filterValue">'.$value.'</span>';
		$out.=' </a>';
		$out.=' <a href="'.$url.'" class="btn btn-default btn-sm remove dropdown-toggle"><span class="fa fa-times-circle"></span><span class="sr-only">Retirer</span></a>
		</span>';
		return $out;
	}
	
	/*
	 * Affiche le lien vers la page de résultat précédente
	 */
	public function renderPreviousPageLink() {
		$out = '';
		if ($this->prtRequest->pageNumber > 1) {
			$url = $this->prtRequest->getUrlWithPageNumber($this->prtRequest->pageNumber - 1);
			$out .='<a href="'. $url .'"> &laquo; Précédent</a>  |';
		}
		return $out;
	}                    
                    
	/*
	 * Affiche le lien vers la page de résultat suivante
	 */
	public function renderNextPageLink() {
		$out = '';
		if ($this->prtRequest->pageNumber < $this->nbPages) {
			$url = $this->prtRequest->getUrlWithPageNumber($this->prtRequest->pageNumber + 1);
			$out .='<a href="'. $url .'"> |  Suivant &raquo;</a>';
		}
		return $out;
	}
	
	/*
	 * Affiche les index de début et de fin des résultats courants par rapport au nombre total
	 */
	public function renderPageEntries() {
		$pageNumber = $this->prtRequest->pageNumber;
		$maxNumber = $this->prtRequest->tfr->parameters[ThesesFrRequest::PARAM_MAX_NUMBER];
		if ($pageNumber == 1 && $pageNumber != $this->nbPages) {
			$firstResult = "1";
			$lastResult =  $maxNumber;
		} elseif ($pageNumber == $this->nbPages){
			$firstResult = (($pageNumber-1)*$maxNumber)+1;
			$lastResult = $firstResult + (($maxNumber - 1) - (($pageNumber * $maxNumber) -  $this->nbTheses));			
		} else {
			$firstResult = (($pageNumber-1) * $maxNumber)+1;
			$lastResult = ($pageNumber * $maxNumber);						
		}
		$out = '<strong>'.$firstResult.'</strong> - ';
		$out .= '<strong>'.$lastResult.'</strong> sur ';
		$out .= '<strong>'.$this->nbTheses.'</strong>';
		return $out;
	}
	
	/*
	 * Affiche le libellé du tri en cours
	 */
	public function renderSortLabel() {
		return $this->sortLabels[$this->prtRequest->tfr->parameters[ThesesFrRequest::PARAM_SORT]];
	}
	
	/*
	 * Affiche les puces avec les liens pour application de tri
	 * L'application d'un tri fait passer à la page 1
	 */
	public function renderSortDropdownItems() {
		$out = '';
		foreach ($this->sortLabels as $sort => $label) {
			$url = $this->prtRequest->getUrlWithNewQsvars(array(ThesesFrRequest::PARAM_SORT => $sort, PrtRequest::PARAM_PAGE_NUMBER => 1));
			$out .= '<li><a href="'.$url.'">'.$label.'</a></li>';
		}
		return $out;
	}

	/*
	 * Affiche les puces avec les liens pour application d'un nouveau max par page
	 */
	public function renderMaxnumberDropdownItems() {
		$maxnumberValues = array(10, 20, 50);
		$out = '';
		foreach ($maxnumberValues as $maxnumber) {
			$out .= $this->renderMaxnumberDropdownItem($maxnumber);
		}
		return $out;
	}
	
	/*
	 * Affiche une puce avec le lien pour l'application d'un nouveau maxnumber par page
	 * L'application d'un tri fait passer à la page 1
	 */
	private function renderMaxnumberDropdownItem($maxnumber) {
		$url = $this->prtRequest->getUrlWithNewQsvars(array(ThesesFrRequest::PARAM_MAX_NUMBER => $maxnumber, PrtRequest::PARAM_PAGE_NUMBER => 1));
		return '<li><a href="'.$url.'">'.$maxnumber.'<span class="sr-only"> par page</span></a></li>';
	} 

}
?>