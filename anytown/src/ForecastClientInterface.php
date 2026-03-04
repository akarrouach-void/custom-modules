<?php

declare(strict_types=1);

namespace Drupal\anytown;

interface ForecastClientInterface {

  /**
   * Fetches the weather forecast data.
   *
    * @param string $url
    *   The URL to fetch the forecast data from.
    *
    * @return array|null
    *   An associative array of forecast data, or null if the fetch fails.
    */
  public function getForecastData(string $url) : ?array;

} 