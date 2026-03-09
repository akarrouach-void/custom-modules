<?php

namespace Drupal\anytown\Plugin\Block;

use Drupal\anytown\Form\NodeSelectorForm;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;


/**
 * Provides a 'Hello' Block.
 */

#[Block(
  id: "node_block",
  admin_label: new TranslatableMarkup("Node block"),
  category: new TranslatableMarkup("Custom")
)]

class NodeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  
  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface */
  private $entityTypeManager;
  
  /** @var \Drupal\Core\State\StateInterface */
  private $state;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $saved_nid = $this->state->get(NodeSelectorForm::SELECTED_NODE);


    $node = $this->entityTypeManager->getStorage('node')->load($saved_nid);

    if ($node) {
      /** @var \Drupal\node\NodeInterface $node */
      $title = $node->label();
      $type = $node->getType();  

      $nids = $this->entityTypeManager->getStorage('node')->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', $type)       
        ->condition('nid', $saved_nid, '<>')  
        ->condition('status', 1)
        ->execute();

      $related_nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      $related_titles = [];
      foreach ($related_nodes as $related_node) {
        $related_titles[] = $related_node->label();
      }

      return [
        '#theme' => 'node_block',
        '#title' => $title,
        '#nodes' => $related_titles,
        '#cache' => [
            'tags' => $node->getCacheTags(), 
        ],
      ];
    }
    else {
      return [
        '#markup' => $this->t('No node selected.'),
      ];
    }
  
  }

  /**
   * {@inheritdoc}
   */
  public static function create($container, array $configuration, $plugin_id, $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('state')
    );
  }
}
