<!doctype html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
	</head>
	<body>
<?php
	require_once("../include/utils.php");
	include_once 'ThesesFrRequest.php';
	include_once 'PrtRequest.php';
	include_once 'RequestFactory.php';

	// Construction de l'abstraction de la requête courante
	$prtRequest = RequestFactory::buildPrtRequestFromGETParameters();
	// Positionnement de la partie PRT de la requête (qui définit le sous-ensemble des 
	// thèses concernées par le portail)
	$prtRequest->setPrtSubset('(disciplines:traduction OR disciplines:traductologie)');
	// Envoi de la requête à theses.fr et récupération du résultat
	$json = getResponse($prtRequest->tfr->getUrl(), '', ''); 
	// print_r($json);
	$theses = json_decode($json);
	
	if(is_object($theses)){
		$numFound = $theses->response->numFound;
	 }
	else  {
		$numFound = '';
	}    
	
	echo 'Nombre de résultats : ' . $numFound . '<br>';
	var_dump($theses);
	$code = 'etablissement';
	foreach ($theses->facet_counts->facet_fields->$code as $facet){
		echo 'Valeur : ' . print_r($facet) . '<br>';
	}

?>

</body>
</html>