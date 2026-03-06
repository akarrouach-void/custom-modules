<?php

declare(strict_types=1);

namespace Drupal\anytown\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

final class SettingsForm extends ConfigFormBase {

  const SETTINGS = 'anytown.settings';

  public function getFormId(): string {
    return self::SETTINGS;
  }
  
  protected function getEditableConfigNames(): array {
    return [self::SETTINGS];
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state): array {
    $config = $this->config(self::SETTINGS);

    $form['display_forecast'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display Forecast'),
      '#description' => $this->t('Check to display the weather forecast.'),
      '#default_value' => $config->get('display_forecast') ?? FALSE,
    ];

    $form['location'] = [
      '#type' => 'textfield',
       '#title' => $this->t('Location'),
      '#description' => $this->t('Enter the location for which to display weather forecast.'),
      '#default_value' => $config->get('location') ?? '',
      "#placeholder" => '90210 ',
    ];

    $form['weather_closures'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Weather Closures'),
      '#description' => $this->t('Enter a message to display when weather conditions are unfavorable.'),
      '#default_value' => $config->get('weather_closures') ?? '',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $location = $form_state->getValue('location');
    $value= filter_var($location, FILTER_VALIDATE_INT);
    if (!$value || strlen( (string) $location) !== 5) {
      $form_state->setErrorByName('location', $this->t('Location can only contain numbers and must be exactly 5 characters.'));
    } 
  

    return parent::validateForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(self::SETTINGS)
      ->set('display_forecast', $form_state->getValue('display_forecast'))
      ->set('location', $form_state->getValue('location'))
      ->set('weather_closures', $form_state->getValue('weather_closures'))
      ->save();
 
    $this->messenger()->addStatus($this->t('Settings saved successfully.'));
  }
}
