<?php

declare(strict_types=1);

namespace Drupal\anytown\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hooks related to theming and content output.
 */
class AnytownTheme {

  /**
   * Implements hook_theme().
   * Defines a custom theme hook for the weather page, specifying the variables that will be available in the template.
   */
  #[Hook('theme')]
  public function theme() : array {
    return [
      'weather_page' => [
        'variables' => [
          'weather_intro' => '',
          'weather_forecast' => '',
          'short_forecast' => '',
          'weather_closures' => '',
        ],
      ],
    ];
  }

  /**
   * Implements preprocess_page().
   * Adds a variable to the page template to indicate if the current page is the front page.
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables) : void {
      $variables['is_front'] = \Drupal::service('path.matcher')->isFrontPage();
      // \Drupal::messenger()->addMessage('is_front = ' . ($variables['is_front'] ? 'TRUE' : 'FALSE'));
  }

  /**
 * Implements hook_page_attachments_alter().
 * Adds a viewport meta tag to the page attachments for responsive design.
 */
  #[Hook('page_attachments_alter')]
  public function pageAttachmentsAlter(array &$attachments): void {
    $attachments['#attached']['html_head'][] = [
      [
        '#tag' => 'meta',
        '#attributes' => [
          'name' => 'viewport',
          'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no',
        ],
      ],
      'viewport',
    ];
  }

  /**
 * Implements hook_preprocess_menu().
 * Adds a custom class to all menu items for styling purposes.
 */
  #[Hook('preprocess_menu')]
  public function preprocessMenu(array &$variables): void {
    foreach ($variables['items'] as &$item) {
      $item['attributes']->addClass('my-custom-class');
    }
  }
  
  /**
 * Implements hook_preprocess_block().
 * Adds a custom class to all block items for styling purposes.
 */
  #[Hook('preprocess_block')]
  public function preprocessBlock(array &$variables): void {

      if ($variables['plugin_id'] == 'system_branding_block') {

          $variables['site_slogan'] = 'Modified by anytown hook!';
      }
  }

}