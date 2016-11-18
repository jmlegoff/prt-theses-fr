<!-- Le formulaire de recherche -->
<div id="search-navbar" class="navbar navbar-default navbar-static-top " role="navigation">
	<div class="container">
		<form accept-charset="UTF-8" action="<?php echo $base_url; ?>" class="search-query-form form-inline clearfix navbar-form" method="get">
			<div class="input-group">
				<label for="q" class="sr-only">Votre recherche...</label>
				<input class="search_q q form-control" id="q" name='userSearch' placeholder="Votre recherche..." type="text" autofocus="autofocus"  
				<?php if ($prtRequest->userSearch != '') echo 'value="'.$prtRequest->userSearch . '"'; ?>
				/>
				<span class="input-group-btn">
					<button type="submit" class="btn btn-primary search-btn" id="search">
						<span class="fa fa-search"></span>
					</button>
				</span>
			</div>
		</form>
	</div>
</div>

<!-- Conteneur principal -->
<div id="main-container" class="container">
    
<!-- Sidebar -->
<div class="row">
	<div id="sidebar" class="col-md-3">
		<!-- Facettes -->  
		<div id="facets" class="facets sidenav">
			<div class="top-panel-heading panel-heading">
				<button type="button" class="facets-toggle" data-toggle="collapse" data-target="#facet-panel-collapse">
					<span class="sr-only">Déplier les facettes</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<h4>Affiner les résultats</h4>
			</div>
			<div id="facet-panel-collapse" class="panel-group collapse" style="height: 0px;">
				<div class="panel panel-default facet_limit theses-status">
					<div data-target="#facet-status" data-toggle="collapse" class="collapse-toggle panel-heading">
						<h5 class="panel-title"><a href="#" data-no-turbolink="true">Statut</a></h5>
					</div>
				<div class="panel-collapse facet-content collapse in" id="facet-status" style="height: auto;">
					<div class="panel-body facet-panel">
						<ul class="facet-values list-unstyled">
							<?php echo $thesesRenderer->renderFacetStatut();?>
						</ul>
					</div>
				</div>
			</div>
			<?php echo $thesesRenderer->renderFacet(ThesesFrRequest::FACET_ETABLISSEMENT);?>
			<?php echo $thesesRenderer->renderFacet(ThesesFrRequest::FACET_ECOLE_DOCTORALE);?>
			<?php echo $thesesRenderer->renderFacet(ThesesFrRequest::FACET_DISCIPLINE);?>
			<?php echo $thesesRenderer->renderFacet(ThesesFrRequest::FACET_DIRECTEUR_THESE_NP);?>
		</div>
		<!-- ADS THESES.FR --> 
		<div class="well" style='background:white'>
            Consultez l'ensemble des thèses françaises depuis 1985, soutenues et en cours de préparation sur :<br/><br/>
            <a href='http://www.theses.fr'><img class="thesefr-img" src='<?php echo plugins_url('/img/logo_thesesfr.gif',__FILE__); ?>'/></a>
		</div>
	</div>
</div>

<!-- Contenu principal -->
<div id="content" class="col-md-8">

	<!-- Fenêtre de recherche -->
	<h2 class="sr-only top-content-title">Search</h2>
	<?php
	if(!empty($prtRequest->userSearch) || !empty($prtRequest->thesesStatut) || $prtRequest->tfr->hasFacetsValues()){
	?>
	<div id="appliedParams" class="clearfix constraints-container">
		<div class="pull-right">
		  <a class="catalog_startOverLink btn btn-sm btn-text" href="<?php echo $base_url; ?>" id="startOverLink">Voir toutes les thèses</a>
		</div>
		<span class="constraints-label">Votre recherche :</span>
		<?php echo $thesesRenderer->renderCurrentFilters();?>
	</div>
	<?php
	}
	// Les fonctions de tri n'apparaissent que s'il y a des résultats
	if (!(empty($thesesRenderer->nbTheses)) && $thesesRenderer->nbTheses > 0) {
	?>
	<!-- Fonctions de tri -->
	<div id="sortAndPerPage" class="clearfix">
		<div class="page_links">
			<?php echo $thesesRenderer->renderPreviousPageLink(); ?> 
			<span class="page_entries">
				<?php echo $thesesRenderer->renderPageEntries(); ?> 
			</span>
			<?php echo $thesesRenderer->renderNextPageLink(); ?>
		</div>
		<div class="search-widgets pull-right">
			<div class="btn-group" id="sort-dropdown">
				<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
					<a href="#"><?php echo $thesesRenderer->renderSortLabel(); ?></a> <span class="caret"></span>
				</button>
				<ul role="menu" class="dropdown-menu">
					<?php echo $thesesRenderer->renderSortDropdownItems(); ?>
				</ul>
			</div>	
			<span class="sr-only">Nombre de résultats par page</span>
			<div class="btn-group" id="per_page-dropdown">
				<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
					<a href="#"><?php echo $prtRequest->tfr->parameters[ThesesFrRequest::PARAM_MAX_NUMBER]; ?> par page</a>
					<span class="caret"></span>
				</button>
				<ul role="menu" class="dropdown-menu">
					<?php echo $thesesRenderer->renderMaxnumberDropdownItems(); ?>					
				</ul>
			</div>
		</div>
	</div>  
	<!-- Fin des fonctions de tri et du if(!empty($thesesRenderer->nbTheses)) -->
	<?php 
	} else { 
	?>
	<!-- Pas de résultats -->  
	<h2>Aucun résultat trouvé</h2>
	<div class="noresults" id="documents">
		<h3>Essayez de modifier votre recherche</h3>
		<ul>
			<li>Utilisez moins de mots-clés  puis affinez les résultats à l'aide des liens disponibles sur la gauche de la page.</li>
		</ul>
	</div> 
	<?php	 
	} // Fin du else
	?>	

	<!-- Les résultats -->
	
<?php	
if(is_object($theses)){
	foreach ($theses->response->docs as $doc)
	{
		$urlRdf = 'http://www.theses.fr/'.$doc->num.'.rdf';
		


		$rdf = getResponse($urlRdf, $proxy_server, $proxy_port);
		$rdf = str_replace('rdf:', 'rdf_', $rdf);
		$rdf = str_replace('dc:', 'dc_', $rdf);  
		$rdf = str_replace('dcterms:', 'dcterms_', $rdf);
		$rdf = str_replace('skos:', 'skos_', $rdf);  
		$rdf = str_replace('isbd:', 'isbd_', $rdf);  
		$rdf = str_replace('marcrel:', 'marcrel_', $rdf);  
		$rdf = str_replace('foaf:', 'foaf_', $rdf);  
		$rdf = str_replace('bibo:', 'bibo_', $rdf);  
		$xml = simplexml_load_string($rdf); // load a SimpleXML object
		$jsonrdf = json_decode(json_encode($xml)); // use json to get all values into an array

		if ($doc->accessible == "oui"){$uri = 'http://www.theses.fr/'.$doc->num.'/document';}
			else{$uri = 'http://www.theses.fr/'.$doc->num;}

				print "<div class='document' id='$doc->num'>";
				print '<div class="documentHeader row">'."\n";

				if ($doc->status == "soutenue" ){ 
					print "<div class='nnt' style='display:none'>".$doc->num."</div>";
				}

				print "<h4 class='index_title titre_". $doc->num ." col-sm-10 col-lg-10'><a target='_blank' href='".$uri."'>".$doc->titre."</a></h4>";
				print "</div>";
				print '<div class="documentDetail row">'."\n";
				print '<div class="index-document-functions col-sm-10 col-lg-10">'."\n";
				print '<dl class="document-metadata dl-horizontal dl-invert">';
				print '<dt class="these-author_display">Auteur</dt>';
				print '<dd class="these-author_display">'.$doc->auteur.'</dd>';

				if(isset($jsonrdf->bibo_Thesis->dcterms_abstract)){
					if(is_array($jsonrdf->bibo_Thesis->dcterms_abstract)){
						print '<dt class="these-abstract_display">Résumé</dt>';
						print '<dd class="these-abstract_display"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAbstract'. $doc->num .'">Lire le résumé</a></dd>';
						$first = true;
						foreach ($jsonrdf->bibo_Thesis->dcterms_abstract as $abstract){
							if ( $first ){
								print ' <dd id="collapseAbstract'. $doc->num .'" class="panel-collapse collapse"><div class="abstract">'.$abstract.'</div></dd>';
								$first = false;

							}
						}
					}
					else {
						if (is_string($jsonrdf->bibo_Thesis->dcterms_abstract)){
							print '<dt class="these-abstract_display">Résumé</dt>';
							print '<dd class="these-abstract_display"><a data-toggle="collapse" data-parent="#accordion" href="#collapseAbstract'. $doc->num .'">Lire le résumé</a></dd>';
							print ' <dd id="collapseAbstract'. $doc->num .'" class="panel-collapse collapse"><div class="abstract">'.$jsonrdf->bibo_Thesis->dcterms_abstract.'</div></dd>';
						}
					}
				}	
				print '<dt class="these-subject">Disicipline</dt>';
				print '<dd class="these-subject"><a href="'.$base_url.'?checkedfacets=discipline='.urlencode($doc->discipline).';">'.$doc->discipline.'</a></dd>';
				print '<dt class="these-author_display">Date</dt>';
				if (isset($doc->dateSoutenance)){
					print "<dd class='these-date_display'>Soutenue le ".date('d/m/Y', strtotime($doc->dateSoutenance))."</dd>";
				}
				if (isset($doc->sujDatePremiereInscription)){
					print "<dd class='these-date_display'>En préparation depuis le ".date('d/m/Y', strtotime($doc->sujDatePremiereInscription))."</dd>";
				}
				if (isset($doc->sujDateSoutenancePrevue)){
					print "<dd class='these-date_display'>Soutenance prévue le ".date('d/m/Y', strtotime($doc->sujDateSoutenancePrevue))."</dd>";
				}
				print '<dt class="these-director_display">Sous la direction de</dt>';
				$i =0;
				foreach ($doc->directeurTheseNP as $directeurTheseNP){
					print '<dd class="these-director_display"><a href="'.$base_url.'?checkedfacets=directeurTheseNP='.urlencode($directeurTheseNP).';">'.$doc->directeurThese[$i].'</a></dd>';
					$i++;
				}
				print '<dt class="these-org">Organisme</dt>';
				if(is_array($jsonrdf->bibo_Thesis->marcrel_dgg)){
					foreach ($jsonrdf->bibo_Thesis->marcrel_dgg as $org){
						print '<dd class="these-org">'.$org->foaf_Organization->foaf_name.'</dd>';
					}
				}
				else {
					print '<dd class="these-org">'.$jsonrdf->bibo_Thesis->marcrel_dgg->foaf_Organization->foaf_name.'</dd>';

				}
				if ($doc->status == "soutenue" && $doc->accessible == "oui"){		
					print '<dt class="these-holdings_display"></dt>';

					print '<dd class="index-document-functions acces_'.$doc->num.'">'."\n";
					print '<div class="availability"><span class="label label-primary online">Web</span><a href="'.$uri.'" title="Disponible en ligne" target="_blank"> Accès libre en ligne</a></div>';
					print '</dd>';
				}	
				print '</dl>';
				print '</div>';
				print '<div class="col-sm-1 col-lg-1">'."\n";
				if ($doc->status == "soutenue"){
					print "<img src='" . plugins_url('/img/soutenue.png',__FILE__) . "' alt='Thèse soutenue' title='Thèse soutenue'/>";
					if ($doc->accessible == "oui"){
					}
				}
				elseif ($doc->status  == "enCours"){
					print "<img src='"  . plugins_url('/img/preparation.png',__FILE__) . "'  alt='Thèse en préparation' title='Thèse en préparation'/>"; 


				}
				print '</div>';	  
				print '</div>'; 
				print '</div>'; 
			}

		}
		else {
?>
<!-- ERROR RETRIEVING THESES.FR  --> 
			<div class="noresults" id="documents">
			  <h3>Le site <a href='theses.fr' target='_blank'>theses.fr</a> rencontre actuellement des difficultés</h3>
			  <ul>
			    <li>Merci de réessayer plus tard.</li>
			  </ul>
			</div> 	
			<?php	
			}
			?>
			</div>	
</div>
 