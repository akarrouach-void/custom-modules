<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\hello_world\Form\HelloSettingsForm;

/**
 * Provides a 'Hello' Block.
 */

#[Block(
  id: "hello_block",
  admin_label: new TranslatableMarkup("Hello block"),
  category: new TranslatableMarkup("Hello World")
)]

class HelloBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
      $current_user = \Drupal::currentUser();
      $config = \Drupal::config(HelloSettingsForm::HELLO_WORLD_SETTINGS);
      return [
          '#markup' => $this->t('Hello, @name!', [
            '@name' => $config->get('hello.name') ?: $current_user->getDisplayName() ?: 'Stranger',
          ]),
        ];
  }


}
