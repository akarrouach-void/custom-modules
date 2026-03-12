<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\anytown\ForecastClientInterface;
use Drupal\anytown\Form\SettingsForm;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class WeatherController extends ControllerBase {

  use StringTranslationTrait;
  
  /** @var \Drupal\anytown\ForecastClientInterface */
  private $forecast_client;

  public function __construct(ForecastClientInterface $forecast_client ) {
    $this->forecast_client = $forecast_client;
  }

  public function content() : array{

    $settings = $this->config(SettingsForm::SETTINGS);
    $location = $settings->get('location');


    $url = 'https://raw.githubusercontent.com/DrupalizeMe/module-developer-guide-demo-site/main/backups/weather_forecast.json';
    // if ($location) {
    //   $url = $url . strtolower($location);
    // }

    $forecast_data = $this->forecast_client->getForecastData($url);
    $rows = [];

    if ($forecast_data) {
      foreach ($forecast_data as $item) {
        [
          'weekday' => $weekday,
          'description' => $description,
          'high' => $high,
          'low' => $low,
          'icon' => $icon,
        ] = $item;

        $rows[] = [
          $weekday,
          [
            'data' => [
              '#markup' => '<img alt="' . $description . '" src="' . $icon . '" width="20" height="20" />',
            ],
          ],
          [
            'data' => [
              '#markup' => $this->t("<em>@description</em> with a high of @high and a low of @low", [
                '@description' => $description,
                '@high' => $high,
                '@low' => $low,
              ]),
            ],
          ],
        ];
      }

      $weather_forecast = [
        '#type' => 'table',
        '#header' => [
          $this->t('Day'),
          '',
          $this->t('Forecast'),
        ],
        '#rows' => $rows,
        '#attributes' => [
          'class' => ['weather_page--forecast-table'],
        ],
      ];

    }
    else {
      // Or, display a message if we can't get the current forecast.
      $weather_forecast = ['#markup' => $this->t('<p>Could not get the weather forecast. Dress for anything.</p>')]; 
    }

     $build = [
      // Which theme hook to use for this content. See anytown_theme().
      '#theme' => 'weather_page',
      '#attached' => [
        'library' => [
          'anytown/forecast',
        ],
      ],
      '#weather_intro' => [
        '#markup' => $this->t("<p>Check out this weekend's weather forecast and come prepared. The market is mostly outside, and takes place rain or shine.</p>"),
      ],
      '#weather_forecast' => $weather_forecast,
      '#weather_closures' => [
        '#theme' => 'item_list',
        '#title' => $this->t('Weather related closures'),
        '#items' => explode(PHP_EOL, $settings->get('weather_closures') ?? ''),
      ],
      '#cache' => [
        'tags' => $settings->getCacheTags(),
        'contexts' => ['url']
        ],
    ];

    return $build;
   
  }


  
  public function details($city) :array {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Detailed weather information for @city will be displayed here.', ['@city' => $city]),
    ];
    
  }

}