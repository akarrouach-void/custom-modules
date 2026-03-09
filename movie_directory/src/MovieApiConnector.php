<?php

namespace Drupal\movie_directory;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\State\StateInterface;
use Drupal\movie_directory\Form\MovieApi;
use GuzzleHttp\Exception\RequestException;

class MovieApiConnector {
  private $clientFactory;
  private $loggerFactory;
  private $configFactory;

  public function __construct(ClientFactory $clientFactory, LoggerChannelFactoryInterface $loggerFactory , ConfigFactory $configFactory) {
    $this->clientFactory = $clientFactory;
    $this->loggerFactory = $loggerFactory;
    $this->configFactory = $configFactory;
  }

  public function fetchMovies() {
    try {
      // $movieApiConfig = $this->state->get(MovieApi::MOVIE_API_CONFIG_PAGE, []);
      // $api_url = $movieApiConfig['api_base_url'] ?? 'https://api.themoviedb.org/'; 
      // $api_key = $movieApiConfig['api_key'] ?? ''; 

      $config = $this->configFactory->get(MovieApi::MOVIE_API_CONFIG_PAGE);
      $api_url = $config->get('api_base_url') ?? 'https://api.themoviedb.org/';
      $api_key = $config->get('api_key') ?? '';

      $query = ['api_key' => $api_key];
      $httpClient = $this->clientFactory->fromOptions([ 'base_uri' => $api_url,"query" => $query]);

      $response = $httpClient->get('3/discover/movie', ['query' => $query]);
      $data = json_decode($response->getBody()->getContents(), true);
      return $data['results'] ?? [];
    } catch (RequestException $e) {
      // \Drupal::logger('movie_directory')->error($e->getMessage());
      $this->loggerFactory->get('movie_directory')->error('Failed to fetch movies: @message', ['@message' => $e->getMessage()]);
      return [];
    }
    catch (\Exception $e) {
      // This catches non-Guzzle errors (like json_decode failing)
      $this->loggerFactory->get('movie_directory')->error('General error: @message', [
        '@message' => $e->getMessage()
      ]);
      return [];
    }
  }
  
  public function getImageUrl ($imagePath)  {
    $baseImageUrl = 'https://image.tmdb.org/t/p/w500/';
    return $baseImageUrl . $imagePath;
  }
} 