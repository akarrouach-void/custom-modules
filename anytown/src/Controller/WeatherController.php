<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\anytown\ForecastClientInterface;
use Drupal\Core\Controller\ControllerBase;
 
class WeatherController extends ControllerBase {
  
  /** @var \Drupal\anytown\ForecastClientInterface */
  private $forecast_client;

  public function __construct(ForecastClientInterface $forecast_client ) {
    $this->forecast_client = $forecast_client;
  }

  public function content() : array{
    $url = 'https://raw.githubusercontent.com/DrupalizeMe/module-developer-guide-demo-site/main/backups/weather_forecast.json';
    $forecast_data = $this->forecast_client->getForecastData($url);
    if ($forecast_data) {
      $forecast = '<ul>';
      foreach ($forecast_data as $item) {
        [
          'weekday' => $weekday,
          'description' => $description,
          'high' => $high,
          'low' => $low,
        ] = $item;
        $forecast .= "<li>$weekday will be <em>$description</em> with a high of $high and a low of $low.</li>";
      }
      $forecast .= '</ul>';
    }
    else {
      $forecast = '<p>Could not get the weather forecast. Dress for anything.</p>';
    }

    $output = "<p>Check out this weekend's weather forecast and come prepared. The market is mostly outside, and takes place rain or shine.</p>";
    $output .= $forecast;
    $output .= '<h3>Weather related closures</h3></h3><ul><li>Ice rink closed until winter - please stay off while we prepare it.</li><li>Parking behind Apple Lane is still closed from all the rain last week.</li></ul>';

    return ['#markup' => $output];
   
  }

  public function details($city) :array {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Detailed weather information for @city will be displayed here.', ['@city' => $city]),
    ];
    
  }

}