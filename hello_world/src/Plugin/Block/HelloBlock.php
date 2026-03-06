<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\hello_world\Form\HelloSettingsForm;

/**
 * Provides a 'Hello' Block.
 */

#[Block(
  id: "hello_block",
  admin_label: new TranslatableMarkup("Hello block 123"),
  category: new TranslatableMarkup("Custom")
)]

class HelloBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /** @var \Drupal\Core\Session\AccountProxyInterface */
  private $currentUser;
  /** @var \Drupal\Core\Config\ConfigFactoryInterface */
  private $configFactory;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user,ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    //---- before no dependency injection
    // $current_user = \Drupal::currentUser();
    //$config = \Drupal::config(HelloSettingsForm::HELLO_WORLD_SETTINGS);
    
    $config = $this->configFactory->get(HelloSettingsForm::HELLO_WORLD_SETTINGS);

    return [
        '#markup' => $this->t('Hello, @name!', [
          '@name' => $config->get('hello.name') ?: $this->currentUser->getDisplayName() ?: 'Stranger',
        ]),
        '#cache' => [
            'tags' => $config->getCacheTags(),  // invalidates when config changes
            'contexts' => ['user'],             // vary per user
        ],
      ];
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
      $container->get('current_user'),
      $container->get('config.factory'),
    );
  }
}
