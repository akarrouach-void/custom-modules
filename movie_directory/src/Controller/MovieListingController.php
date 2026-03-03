<?php 

namespace Drupal\movie_directory\Controller;

use Drupal\Core\Controller\ControllerBase;

class MovieListingController extends ControllerBase{
  public function view() {
    
    $movies = $this->listMovies();

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
    ];
  }

  public function listMovies() {
    $movieApiConnector = \Drupal::service('movie_directory.api_connector');
    return $movieApiConnector->fetchMovies();
  }

  public function createMovieCard(array $movies) {
    $movieApiConnector = \Drupal::service('movie_directory.api_connector');
    $movieCard = [];

    if (!empty($movies)) {
      foreach ($movies as $movie) {
        // API returns associative arrays, not objects.
        $content = [ 
          'title' => $movie['title'] ?? 'Unknown Title',
          'description' => $movie['overview'] ?? 'No description available.',
          'movie_id' => $movie['id'] ?? 'N/A', 
          // template expects `content.image`.
          'image' => $movieApiConnector->getImageUrl($movie['poster_path'] ?? ''),  
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