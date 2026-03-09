<?php

declare(strict_types=1);

namespace Drupal\anytown\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\State\StateInterface;

class NodeSelectorForm extends FormBase  {

  use AutowireTrait;

  const SELECTED_NODE = 'anytown.selected_node';

  public function getFormId(): string {
    return self::SELECTED_NODE;
  }

  /** @var \Drupal\Core\State\StateInterface */
  private $state;
  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  private $entity_type;

  public function __construct(StateInterface $state, EntityTypeManagerInterface $entity_type) {
    $this->state = $state;
    $this->entity_type = $entity_type;
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state): array {
    $saved_nid = $this->state->get(self::SELECTED_NODE);

    $form['node_id'] = [
      '#type' => 'entity_autocomplete',   
      '#target_type' => 'node',           
      '#title' => $this->t('Select a node'),
      '#description' => $this->t('Start typing to search for a node.'),
      '#required' => TRUE,
      '#default_value' => $saved_nid
        ? $this->entity_type->getStorage('node')->load($saved_nid)
        : NULL,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }


  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state): void {
    $node_id = $form_state->getValue('node_id');
    $this->state->set(self::SELECTED_NODE, $node_id);
    $this->messenger()->addStatus($this->t('Selected node ID %nid has been saved.', ['%nid' => $node_id]));
  }



}