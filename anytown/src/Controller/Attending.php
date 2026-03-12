<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for attending page.
 */
class Attending extends ControllerBase {

  /**
   * Callback to display list of vendors attending this week.
   *
   * @return array
   *   List of vendors attending this week.
   */
  public function build(): array {
    // Build a query to get vendor IDs.
    $query = $this->entityTypeManager()->getStorage('node')->getQuery()
      ->accessCheck()
      ->condition('type', 'vendor')
      ->condition('field_vendor_attending', TRUE);

    $node_ids = $query->execute();
    if (count($node_ids) > 0) {
      // Load the actual vendor node entities.
      $nodes = $this->entityTypeManager()->getStorage('node')->loadMultiple($node_ids);

      $view_builder = $this->entityTypeManager()->getViewBuilder('node');

      $vendor_list = [];
      $vendor_teasers = [];

      foreach ($nodes as $vendor) {
        
        $vendor_list[$vendor->id()] = [];
        $vendor_list[$vendor->id()]['name'] = [
          '#markup' => $vendor->label(),
        ];
        /** @var \Drupal\node\NodeInterface $vendor */
        $vendor_list[$vendor->id()]['contact'] = $vendor->get('field_vendor_contact_email')->view(['label' => 'hidden']);

        $vendor_list[$vendor->id()]['#cache'] = [
          'tags' => $vendor->getCacheTags(),
        ];

        $vendor_teasers[$vendor->id()] = $view_builder->view($vendor, 'teaser');
      }

      // Alternatively, we could render teasers for all vendors at once using
      // $vendor_teasers = $view_builder->viewMultiple($nodes, 'teaser');.

      $build = [
        'vendor_list' => [
          '#theme' => 'item_list',
          '#items' => $vendor_list,
        ],
        'vendor_teasers' => $vendor_teasers,
      ];
    }
    else {
      $build = [
        '#markup' => $this->t('No vendors are currently attending this week.'),
      ];
    }

    return $build;
  }

}