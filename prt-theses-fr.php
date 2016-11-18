<?php
/*
Plugin Name: Prt-Theses-fr
Description: Plugin d'accès à theses.fr basé sur le code de Julien Sicot
Version: 0.1
Author: JM Le Goff
*/
class Prt_Theses_Fr_Plugin
{
	private $thesesRenderer;
	
    public function __construct()
    {
		// Création du menu		
		add_action('admin_menu', array($this, 'add_admin_menu'));
		// Ajout du shortcode pour l'affichage des dernières thèses
		add_shortcode('theses-last', array($this, 'theses_last'));
		// Ajout du shortcode pour l'affichage de l'ensemble des thèses avec outils de recherche et de tri
		add_shortcode('theses-page', array($this, 'theses_page'));
		// Ajout du shortcode pour l'affichage du nombre de thèses
		add_shortcode('theses-count', array($this, 'theses_count'));		
		// Short-code temporaire pour test
		add_shortcode('theses-test', array($this, 'theses_master'));
		// Enregistrement des paramétres du plugin 
		add_action( 'admin_init', array($this, 'add_prt_theses_fr_settings') );
		// Hook pour le header de page
		add_action('wp_head', array($this, 'header'));
		// Hook pour le footer de page
		add_action('wp_footer', array($this, 'footer'));
    }
	
	function add_prt_theses_fr_settings() {
		register_setting( 'prt_theses_fr-settings-group', 'prt_subset_request' );
		register_setting( 'prt_theses_fr-settings-group', 'prt_last_theses' );
	}

	// Ajout du menu d'administration
	public function add_admin_menu()
	{
		// Le menu principal
		add_menu_page('Administration du plugin Prt-Theses-fr', 'Prt-Theses-fr', 'manage_options', 'theses-master', array($this, 'menu_html'));
		// Le sous-menu 'Aperçu' qui décrit le fonctionnement général du plugin
        add_submenu_page('Prt-Theses-fr', 'Le Plugin Prt-Theses-fr', 'Aperçu', 'manage_options', 'Prt-Theses-fr', array($this, 'menu_html'));		
	}
	
	public function menu_html()
	{
		echo '<h1>'.get_admin_page_title().'</h1>';
		echo '<p>Le plugin Prt-Theses-Fr offre une fenêtre sur theses.fr depuis le portail PRT. Il est basé sur le code de Julien Sicot.</p>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'prt_theses_fr-settings-group' ); 
		do_settings_sections( 'prt_theses_fr-settings-group' ); 
		echo '<table class="form-table">';
		echo '<tr valign="top">';
        echo '<th scope="row">Requête de génération du sous-ensemble PRT</th>';
        echo '<td><textarea rows="20" cols="180" name="prt_subset_request">'. esc_attr( get_option('prt_subset_request')) . '</textarea></td>';
        echo '</tr>';
		echo '<tr valign="top">';
        echo '<th scope="row">Nombre de réponses affichées par le short-code last</th>';
        echo '<td><input type="text" name="prt_last_theses" value="' . esc_attr( get_option('prt_last_theses')).'" size="2"/></td>';
        echo '</tr>';
		echo '</table>';
		submit_button();
		echo '</form>';
	}
	
	public function header() {
		$output = "<link 						href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,300italic,700,600,800' rel='stylesheet' type='text/css'>";
		$output .= "<link href='" . plugins_url('/css/custom.css',__FILE__) . "' rel='stylesheet'>";
		$output .= "<link href='//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' rel='stylesheet'>";
		echo $output;
	}
	
	public function footer() {
		$output = "<script src='//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>";
		$output .= "<script src='//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js'></script>";
		$output .= "<script type='text/javascript' src='" . plugins_url('/js/scripts.js',__FILE__) . "'></script>";
		echo $output;
	}
	
	public function theses_master()
	{
		ob_start( );
		include('theses_master.php');
		$output = ob_get_clean();
		return  $output ;
	}

	public function buildPrtRequest() {
		require_once("include/utils.php");
		include_once 'classes/ThesesFrRequest.php';
		include_once 'classes/PrtRequest.php';
		include_once 'classes/RequestFactory.php';
		include_once 'classes/ThesesRenderer.php';

		// Construction de l'abstraction de la requête courante
		$prtRequest = RequestFactory::buildPrtRequestFromGETParameters();
		// Positionnement de la partie PRT de la requête (qui définit le sous-ensemble des 
		// thèses concernées par le portail)
		$prtRequest->setPrtSubset(get_option('prt_subset_request'));
		// '(disciplines:traduction OR disciplines:traductologie)');	
		return $prtRequest;
	}
	public function theses_count() {
		if (is_null($this->thesesRenderer)) {
			$prtRequest = $this->buildPrtRequest();
			// Envoi de la requête à theses.fr et récupération du résultat
			$json = getResponse($prtRequest->tfr->getUrl(), '', ''); 
			$theses = json_decode($json);
			$this->thesesRenderer = new ThesesRenderer($prtRequest, $theses);
		}
		return $this->thesesRenderer->nbTheses;
	}
	
	public function theses_page()
	{
		if (is_null($this->thesesRenderer)) {
			$prtRequest = $this->buildPrtRequest();
			// Envoi de la requête à theses.fr et récupération du résultat
			$json = getResponse($prtRequest->tfr->getUrl(), '', ''); 
			$theses = json_decode($json);
			$this->thesesRenderer = new ThesesRenderer($prtRequest, $theses);
		}
		$thesesRenderer = $this->thesesRenderer;
		// Construction de la page résultat
		ob_start( );
		include('theses_renderer.php');
		$output = ob_get_clean();
		return  $output ;
	}

	public function theses_last()
	{
		if (is_null($this->thesesRenderer)) {
			$prtRequest = $this->buildPrtRequest();
			// Modification du nombre max d'éléments retournés par la requête
			$prtRequest->tfr->setParameter(ThesesFrRequest::PARAM_MAX_NUMBER, get_option('prt_last_theses'));
			// Envoi de la requête à theses.fr et récupération du résultat
			$json = getResponse($prtRequest->tfr->getUrl(), '', ''); 
			$theses = json_decode($json);
			$this->thesesRenderer = new ThesesRenderer($prtRequest, $theses);
		}
		$thesesRenderer = $this->thesesRenderer;
		// Construction de la page résultat
		ob_start( );
		include('theses_last.php');
		$output = ob_get_clean();
		return  $output ;
	}

	
}

new Prt_Theses_Fr_Plugin();
?>

