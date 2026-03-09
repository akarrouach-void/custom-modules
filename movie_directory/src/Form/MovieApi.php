<?php

namespace Drupal\movie_directory\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;

class MovieApi extends ConfigFormBase {
  use AutowireTrait;

  const MOVIE_API_CONFIG_PAGE = 'movie_directory.settings';

  /** @var StateInterface */
  private $state;

  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  protected function getEditableConfigNames(): array {
    return [self::MOVIE_API_CONFIG_PAGE];
  }
  
  public function getFormId() {
    return self::MOVIE_API_CONFIG_PAGE;
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // $values = \Drupal::state()->get(self::MOVIE_API_CONFIG_PAGE, []);
    // $values = $this->state->get(self::MOVIE_API_CONFIG_PAGE, []);
    $config = $this->config(static::MOVIE_API_CONFIG_PAGE);
    $form = [];

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base URL'),
      '#description' => $this->t('The base URL for the movie API.'),
      '#required' => TRUE,
      '#default_value' => $config->get('api_base_url') ?? '', 
      
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key (v3 auth)'),
      '#description' => $this->t('The API key for the movie API.'),
      '#required' => TRUE,
      // '#default_value' => $values['api_key'] ?? '', 
      '#default_value' => $config->get('api_key') ?? '',
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // $submitted_values = $form_state->cleanValues()->getValues();
    // Drupal::state()->set(self::MOVIE_API_CONFIG_PAGE, $submitted_values);
    // $messenger = \Drupal::messenger();
    // $messenger->addMessage($this->t('Movie API configuration saved.'));
    // $this->state->set(self::MOVIE_API_CONFIG_PAGE, $form_state->cleanValues()->getValues());
    
    $this->config(static::MOVIE_API_CONFIG_PAGE)
      ->set('api_base_url', $form_state->getValue('api_base_url'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();
    Cache::invalidateTags(['movie_api_config']);  
    $this->messenger()->addStatus($this->t('Movie API configuration saved.'));

  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
      $api_base_url = $form_state->getValue('api_base_url');
      if (!filter_var($api_base_url, FILTER_VALIDATE_URL)) {
        $form_state->setErrorByName('api_base_url', $this->t('The API Base URL must be a valid URL.'));
      }
  
      $api_key = $form_state->getValue('api_key');
      if (empty($api_key)) {
        $form_state->setErrorByName('api_key', $this->t('The API Key cannot be empty.'));
      }


    return parent::validateForm($form, $form_state);
  }
}