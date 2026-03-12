<?php

declare(strict_types=1);

namespace Drupal\drupal_advanced\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;

class ArticleApiController extends ControllerBase {
  const NODE_IDS = [2, 7, 31];

  public function getArticles() {
    $storage = $this->entityTypeManager()->getStorage('node');
    $data = [];

    foreach (self::NODE_IDS as $nid) {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $storage->load($nid);
      if ($node) {
        $data[] = [
          'nid' => $node->id(),
          'title' => $node->getTitle(),
        ];
      }
    }

    $response = new CacheableJsonResponse($data);
    
    $cache = new CacheableMetadata();
    $cache->setCacheMaxAge(60);
    // ['node_list'] // Add 'node_list' tag to invalidate when any node is added/updated/deleted 
    // we dont need to add this tag because we are only interested in specific nodes
    $cache->setCacheTags(array_map(fn($nid) => 'node:' . $nid, self::NODE_IDS));
    $response->addCacheableDependency($cache);
    
    return $response;
  }

}