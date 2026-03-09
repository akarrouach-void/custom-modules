<?php 

namespace Drupal\movie_directory\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\movie_directory\MovieApiConnector;

class MovieListingController extends ControllerBase{

  use AutowireTrait;

  /** @var \Drupal\movie_directory\MovieApiConnector */
  private $movieApi;

  public function __construct(MovieApiConnector $movieApi) {
      $this->movieApi = $movieApi;
  } 

  public function view() {    
    $movies = $this->movieApi->fetchMovies();
    

    $content = [];
    $content['movies'] = $this->createMovieCard($movies);

    return [
      '#theme' => 'movie_listing',
      '#content' => $content,
      '#attached' => [
        'library' => [
          'movie_directory/movie-directory-styling',
        ],
      ], 
      '#cache' => ['tags' => ['movie_api_config']],
    ];
  }

  public function createMovieCard(array $movies) {
    
    $movieCard = [];

    if (!empty($movies)) {
      foreach ($movies as $movie) {
        // API returns associative arrays, not objects.
        $content = [ 
          'title' => $movie['title'] ?? 'Unknown Title',
          'description' => $movie['overview'] ?? 'No description available.',
          'movie_id' => $movie['id'] ?? 'N/A', 
          // template expects `content.image`.
          'image' => $this->movieApi->getImageUrl($movie['poster_path'] ?? ''),  
        ];
        $movieCard[] = [
          '#theme' => 'movie_card',
          '#content' => $content,
        ]; 
      }
    }
    
    return $movieCard;
  }
  
}