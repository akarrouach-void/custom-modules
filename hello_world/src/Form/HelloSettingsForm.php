<?php

namespace Drupal\hello_world\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Hello World settings form.
 */
class HelloSettingsForm extends ConfigFormBase {

  const HELLO_WORLD_SETTINGS = 'hello_world.settings';

  protected function getEditableConfigNames() {
    return [static::HELLO_WORLD_SETTINGS];
  }

  public function getFormId() {
    return 'hello_world_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::HELLO_WORLD_SETTINGS);

    $current_user = \Drupal::currentUser()->getDisplayName();
  
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('Enter your name to be greeted in the sidebar.'),
      '#default_value' => $config->get('hello.name') ?: $current_user, 
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Configuration'),
      '#button_type' => 'primary',
    ];

    return parent::buildForm($form, $form_state);
  }

   public function submitForm(array &$form, FormStateInterface $form_state) {
    $submitted_values = $form_state->cleanValues()->getValues();

    $this->config(static::HELLO_WORLD_SETTINGS)
    ->set('hello.name', $form_state->getValue('name'))
    ->save();

    $messenger = \Drupal::messenger();
    $messenger->addMessage($this->t('Name saved. You will be greeted as @name in the sidebar.', ['@name' => $submitted_values['name']]));

    parent::submitForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('name');
    if (strlen($name) > 50 || strlen($name) < 3) {
      $form_state->setErrorByName('name', $this->t('Name cannot be longer than 50 characters or less than 3 characters.'));
    }
    parent::validateForm($form, $form_state);
  }
}