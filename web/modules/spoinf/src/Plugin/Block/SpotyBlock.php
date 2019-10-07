<?php


namespace Drupal\spoinf\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\spoinf\inc\SpoInfWs;


/**
 * Provides a 'Hello' Block.
 *
 * @Block(
 *   id = "SpotyBlock",
 *   admin_label = @Translation("Spotify block"),
 *   category = @Translation("Hello World"),
 * )
 */
class SpotyBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
  	
  	$spotifyWshandler = new SpoInfWs();
	$response = $spotifyWshandler->getCategories();
	echo '<pre>cecec'; print_r($response); echo '</pre>';

    return [
      '#markup' => 'Hello, World! dfsdf' .  print_r($response, true),
    ];
  }

}