<?php

namespace Drupal\spoinf\inc;

use Drupal\spoinf\inc\ProcessMessage;
use Drupal\config_pages\Entity\ConfigPages;

class SpoInfWs{
	
	var $clientId;
	var $clientSecret;
	var $messageHandle;
	var $accessTokenInfo;
	var $tempstore;

	var $url = "https://api.spotify.com/v1/playlists/";
	var $urlArt;
	var $urlLastReleases;
	var $urlToken;
	var $urlAlbum;
	var $urlCategoryList;
	

	/**
	 * Constructor de la clase, se encarga de realizar inicializaciones de variables
	 * desde la configuración de spotify
	 */
	public function __construct(){

		$configuration = ConfigPages::config( 'spotify_config' );

		$this->messageHandler = ProcessMessage::getInstance();
		$this->tempstore = \Drupal::service('user.private_tempstore')->get('spoinf');

		// client id
		$this->clientId = $configuration->get('field_clientid')->value;
		
		// client secret		
		$this->$clientSecret = $configuration->get('field_clientsecret')->value;;

		// url de albums
		$this->urlAlbum = $configuration->get('field_urlalbum')->value;

		// url de peticion de token
		$this->urlToken = $configuration->get('field_urltoken')->value;

		// url de lista de ultimos lanzamientos
		$this->urlLastReleases = $configuration->get('field_urllastreleases')->value;

		// url de artista
		$this->urlArt = $configuration->get('field_urlart')->value;

		// url de categorias
		$this->urlCategoryList = $configuration->get('field_urlcategorylist')->value;

		if( $this->checkvalidToken() == false )
		{
			$this->requestAccessToken();
		}
		
	}

	/** 
	 * Verifica que exista un acces token
	 * @return [String] Acces token
	 */
	private function checkvalidToken(){
		
		return( $this->tempstore->get( 'spotify_access_token' ) != '' && isset( $_COOKIE['ValidTime'] ) );

	}

	/**
	 * Realiza un request solicitando un acces token válido 
	 * usa las credenciales de clicnt_id y client_secret de la aplicación creada en spotify
	 * al obtenerlo, almacena una cookie con validés igual al tiempo de duración del accestoken, una vez que 
	 * este tiempo finalizó, el token a expirado y se solicita uno nuevo
	 * se almacena en el storage session de drupal
	 */
	private function requestAccessToken(){

		// Inicializo la respuesta
		$return = false;

		// Envia una petición por access token
		if( $responseGetToken = $this->sendCurlAuth( $this->urlToken, '', 'auth' ) ){

			// If se ontiene una respuesta válida se almacena el token y se crea la cookie con la durabilidad del token, el formato es el siguiente:
			// "token_type":"Bearer", "expires_in":3600,"scope":""}
			$responseJson = json_decode( $responseGetToken );

			if( $responseJson && $responseJson->access_token != '' )
			{
				$this->accessTokenInfo = $responseJson->access_token;

				// Se almacena en session
				$this->tempstore->set('spotify_access_token', $responseGetToken);

				// seteo Una cookie para almacenar el tiempo que dura el access token, una vez que la cookie no sea válida
				// se tiene que solicitar un nuevo access token
				setcookie("ValidTime", "token_obtained", time() + $responseJson->expires_in );

			}

		}
	}

	/**
	 * Obtiene la lista con los ultimos lanzamientos desde spotify
	 * @return [Array] Contiene la respuesta de las listas desde el servicio de spotify
	 */
	public function getReleases(){
		
	 	$response = $this->sendCurlApi( $this->urlLastReleases, '', '' );
	 	
	 	$response = json_decode($response);

	 	return $response;
	}

	/**
	 * Obtiene la lista con los ultimos lanzamientos desde spotify
	 * @return [Array] Contiene la respuesta de las listas desde el servicio de spotify
	 */
	public function getCategories(){
		
	 	$response = $this->sendCurlApi( $this->urlCategoryList, '', '' );
	 	$response = json_decode($response);

	 	return $response;
	}

	

	/**
	 * Obtiene la lista con los ultimos lanzamientos desde spotify
	 * @return [Array] Contiene la respuesta de las listas desde el servicio de spotify
	 */
	public function getAlbum( $idAlbum ){
		
	 	$response = $this->sendCurlApi( $this->urlAlbum, $idAlbum, '' );
	 	
	 	$response = json_decode($response);

	 	return $response;

	}

	/**
	 * Obtiene un detalle de un artista desde spotify
	 * 
	 * @param [String] $isArtist El id del artista en spotify
	 * @return [Array] Contiene la respuesta de la información del artista desde el servicio de spotify
	 */
	public function getArtist( $idArtis ){
	 	
	 	$response = $this->sendCurlApi( $this->urlArt, $idArtis, '' );
	 	$response = json_decode($response);

	 	return $response;
	}

	/**
	 * Envía una petición al api de spotify para obtener un access token
	 * 
	 * @param  [String] $url      [description]
	 * @param  [String] $resource [description]
	 * @return [type]           [description]
	 */
	public function sendCurlAuth( $url, $resource ){

		$return = null;
		$client_id = '95a06ead2e2f47618483cfe78a48bfff'; 
		$client_secret = 'a1d5fda53ca74ef5914610c5636ac70d'; 

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,            $url );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch, CURLOPT_POSTFIELDS,     'grant_type=client_credentials' ); 
		curl_setopt($ch, CURLOPT_POST,           1 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Basic ' . base64_encode( $this->clientId.':'.$this->$clientSecret ) ) ); 

		$result  =curl_exec($ch);
		$err     = curl_errno( $ch );
	    $errmsg  = curl_error( $ch );
	    $header  = curl_getinfo( $ch );

		if( empty( $err ) ){

			$return = $result;

		}
		else{
			$this->messageHandler->addMessage( $err, $errmsg );
			$return = false;
		}
	    return $return;

	}

	/**
	 * Realiza un request por curl a la api de soptify con el fin de obtener recursos
	 * @param  [String] $url      Url del servicio en spotify
	 * @param  [String] $resource Representa el id del resurso solicitado en spotify
	 * @return [type]           [description]
	 */
	public function sendCurlApi( $url, $resource )
	{
		$accessToken = json_decode($this->tempstore->get( 'spotify_access_token' ) );
		
		 $options = array(
	        CURLOPT_RETURNTRANSFER => true,     // return web page
	        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
	        CURLOPT_ENCODING       => "",       // handle all encodings
	        CURLOPT_TIMEOUT        => 120,      // timeout on response
	        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
	        CURLOPT_SSL_VERIFYPEER => false,    // Disabled SSL Cert checks
	        CURLOPT_HTTPHEADER     => array(
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Bearer ' .  $accessToken->access_token
			)
	    );
	    $ch      = curl_init( $url.$resource );
	    curl_setopt_array( $ch, $options );
	    $content = curl_exec( $ch );
	    $err     = curl_errno( $ch );
	    $errmsg  = curl_error( $ch );
	    $header  = curl_getinfo( $ch );
	    curl_close( $ch );


	    return $content;

	}
}