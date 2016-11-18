<div id="theses_last">
<ul>
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

		if ($doc->accessible == "oui") {
			$uri = 'http://www.theses.fr/'.$doc->num.'/document';
		} else{
			$uri = 'http://www.theses.fr/'.$doc->num;
		}
		
		echo '<li>';
		echo '<a target="_blank" href="' . $uri . '">' . $doc->titre .'</a>';
		echo '<span class="these-author"> - '.$doc->auteur.'</span>';
		echo '<span> - (le '. date('d/m/Y', strtotime($doc->dateSoutenance)) . ')</span>';
		echo '</li>';
	}
}
?>
</ul>
</div>