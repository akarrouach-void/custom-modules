<?php

declare(strict_types=1);

namespace Drupal\anytown;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use \Drupal\Core\Cache\CacheBackendInterface;

class ForecastClient implements ForecastClientInterface {

  use AutowireTrait;

  /** @var \GuzzleHttp\ClientInterface */
  private $httpClient;
  /** @var \Psr\Log\LoggerInterface */
  private $logger;
  /** @var CacheBackendInterface */
  private $cache;

  /* 
   * @param \GuzzleHttp\ClientInterface $httpClient
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   */
  public function __construct(ClientInterface $httpClient, LoggerChannelFactoryInterface $logger_factory, CacheBackendInterface $cache) {
    $this->httpClient = $httpClient;
    $this->logger = $logger_factory->get('anytown'); 
    $this->cache = $cache;
  } 


    /**
   * {@inheritDoc}
   */
  public function getForecastData(string $url, $reset_cache = false) : ?array {
    $cache_id = 'anytown_forecast:' . md5($url);
    
    $data = $this->cache->get($cache_id);

    if ($data && !$reset_cache) {
      // var_dump('Cache hit');
      $forecast = $data->data;
    } else {
      // var_dump('Cache miss');
      try {
        $response = $this->httpClient->request('GET', $url);
        $json = json_decode($response->getBody()->getContents());
      }
      catch (GuzzleException $e) {
        $this->logger->warning($e->getMessage());
        return NULL;
      }

      $forecast = [];
      foreach ($json->list as $day) {
        $forecast[$day->day] = [
          'weekday' => ucfirst($day->day),
          'description' => $day->weather[0]->description,
          'high' => $this->kelvinToFahrenheit($day->main->temp_max),
          'low' => $this->kelvinToFahrenheit($day->main->temp_min),
          'icon' => $day->weather[0]->icon,
        ];
      } 

      $this->cache->set($cache_id, $forecast, strtotime('+1 hour'));
    }

    

    return $forecast;
  }


  /** 
   * Converts a temperature from Kelvin to Fahrenheit.
   *
   * @param float $kelvin
   *   The temperature in Kelvin.
   *
   * @return float
   *   The temperature in Fahrenheit.
   */
  public static function KelvinToFahrenheit(float $kelvin) : float {
    return round(($kelvin - 273.15) * 9 / 5 + 32);
  }

} 