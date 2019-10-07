<?php

namespace Drupal\spoinf\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\spoinf\inc\SpoInfWs;
use Drupal\spoinf\inc\ProcessMessage;
// use Drupal\image\Entity\theme;

/**
 * Defines SpoInfController class.
 */
class SpoInfController extends ControllerBase {

	// La url base de esto
	var $messageHandler;
	var $spotifyWshandler;

	/**
	 * Constructor de la clase
	 */
	public function __construct(){

		$this->messageHandler = ProcessMessage::getInstance();
	 	$this->spotifyWshandler = new SpoInfWs();
	}


  	/**
	 * dibuja la pagina principal del módulo.
	 *
	 * @return array
	 *   Return markup array.
	*/
	public function draw() {
	 	
		// Obtiene una lista de releases del webservice de soptify
	 	$response = $this->spotifyWshandler->getReleases();
	 	
	 	$bufferContent = "f";

	 	// Inicalizo variable para contener los rows de la tabla
	 	$rows = array();

	 	// Inicializo header de la tabla, imagen , nombre y artistas relacionados
	 	$header = array( 'Image', 'Name', 'Related artist' );
	 	
	 	foreach( $response->albums->items as $key=>$item ){


	 		list($spotify_, $type_, $idAlbum) = explode(":", $item->uri );
	 		
	 		


	 		// Columna del nombre
	 		$name = $item->name;

	 		// Columna de artista, Se prepara un string de links para cada artista, se obtiene id y se pasa a la página de artistas
	 		// @see SpoInfController::artist
		 	$bufferArtist = '';

	 		// se construyen los links para cada artista
	 		foreach( $artist as $art ){

	 			$linkArr = explode("/",$art->href);
	 			$lastElement = count( $linkArr ) - 1;
		 		$bufferArtist .= '<a href="artista/'.$linkArr[ $lastElement ].'-'.$idAlbum.'">'.$art->name.'</a>';

	 		}

 			$artist = $item->artists;

	 		// A cada elemento de la lista principal de lanzamientos se agrega un listado de artistas
	 		$elementArt[ $key ]['#markup'] = $bufferArtist;

 			// Se renderiza la lista de artistas para agregarla como un markup en el array final
			$artists_ = \Drupal::service('renderer')->render( $elementArt[ $key ] );

			// Columna de imagen
	 		$images = $item->images;
	 		$mainImage = $images[0]->url;
	 		$image = '<img src="'.$mainImage.'" width="150">';
	 		$elementImg[ $key ]['#markup'] = $image;


	 		// Armado de filas de la tabla
	 		$rows[] = array( 
	 			\Drupal::service('renderer')->render($elementImg[ $key ] ),
	 			$name,
	 			$artists_ 
	 		);
	 	}

		// $renders[] = [
		// 	'#type' => 'markup',
		// 	'#markup' => $bufferContent
		// ];


	 	// Construccion final de la tabla, se agregan $headser y $rows
	 	$renders[] = [
			'#type' => 'table',
			'#header' => $header,
			'#rows' => $rows
		];


		// $renders[] = [
		// 	'#type' => 'markup',
		// 	'#markup' =>  '<pre>'.print_r($response->albums->items, true).'</pre>',
		// ];
		
		// imprime mensages si los tiene
		if( $this->messageHandler->hasMesages() )
	 	{
	 		$this->messageHandler->printMessages();
	 	}

		return $renders;
	}

  	/**
  	 * [artist description]
  	 * @return [type] [description]
  	 */
  	public function artist( $art = '' ){

  		$current_path = \Drupal::service('path.current')->getPath();
		$path_args = explode('/', $current_path);

		// Obtengo el id del artista desde la url
		list($idArtis, $idAlbum) = explode("-", $path_args[2]);

		// Obtengo información del album
		$album = $this->spotifyWshandler->getAlbum( $idAlbum );

		// Obtengo informacion del album
		// spotify:album:4ow6xJwn49gpWz7iHpOzWY

  		$response = $this->spotifyWshandler->getArtist( $idArtis );

  		$name = $response->name;
	 	$image = '<img src="'.$response->images[0]->url .'" width="200">';

	 	$bufferContent = "<div><h2>$name</h2></div><div>$image</div>";

	 	$renders[] = [
			'#type' => 'markup',
			'#markup' => $bufferContent
		];
		
		// Inicializo header de la tabla información album, imagen , nombre 
	 	$header = array( 'Imagen Album', 'Nombre' );

	 	/// columna nombre
	 	$nameAlbum = $album->name;

	 	// Columna de imagen
 		$images = $album->images;
 		$mainImage = $images[0]->url;
 		$image = '<img src="'.$mainImage.'" width="75">';
 		$elementImg[ 0 ]['#markup'] = $image;

	 	// Armado de filas de la tabla con información del album
 		$rows[] = array( 
 			\Drupal::service('renderer')->render($elementImg[ 0 ] ),
 			$nameAlbum,
 		);

 		// Construccion final de la tabla, se agregan $headser y $rows
	 	$renders[] = [
			'#type' => 'table',
			'#header' => $header,
			'#rows' => $rows
		];

		return $renders;

  	}

  

}


