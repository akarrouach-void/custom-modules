<?php

namespace Drupal\movie_directory;

use Drupal\Core\Http\ClientFactory;
use Drupal\movie_directory\Form\MovieApi;

class MovieApiConnector {
  private $client;

  private $query;

  public function __construct(ClientFactory $client) {
    $this->client = $client;
    $movieApiConfig = \Drupal::state()->get(MovieApi::MOVIE_API_CONFIG_PAGE, []);
    $api_url = $movieApiConfig['api_base_url'] ?? 'https://api.themoviedb.org/'; 
    $api_key = $movieApiConfig['api_key'] ?? ''; 

    $this->query = ['api_key' => $api_key];
    $this->client= $client->fromOptions([
      'base_uri' => $api_url,
      "query" => $this->query,
    ]);

  }

  public function fetchMovies() {
    try {
      
      $response = $this->client->get('3/discover/movie', ['query' => $this->query]);
      $data = json_decode($response->getBody()->getContents(), true);
      return $data['results'] ?? [];
    } catch (\GuzzleHttp\Exception\RequestException $e) {
      \Drupal::logger('movie_directory')->error($e->getMessage());
      return [];
    }
  }
  public function getImageUrl ($imagePath)  {
    $baseImageUrl = 'https://image.tmdb.org/t/p/w500/';
    return $baseImageUrl . $imagePath;
  }
} 