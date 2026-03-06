<?php 

namespace Drupal\anytown\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\movie_directory\Form\MovieApi;

class FormHooks {

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    if ($form_id === MovieApi::MOVIE_API_CONFIG_PAGE) {
        $form['my_custom_field'] = [
          '#type' => 'textfield',
          '#title' => 'My Custom Field',
          '#description' => 'Added by anytown module Form Alter hook',
          '#weight' => 99,
        ];  
    }
    
  }
 
}
