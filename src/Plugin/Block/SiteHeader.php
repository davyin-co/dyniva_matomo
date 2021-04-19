<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_site_header",
 *   admin_label = @Translation("站点头部"),
 * )
 */
class SiteHeader extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build() {
    $managed_entity = \Drupal::routeMatch()->getParameter('managed_entity_id');
    $managed_plugin = \Drupal::routeMatch()->getRouteObject()->getDefaults()['plugin_id'];

    $plugin = \Drupal::service('plugin.manager.managed_entity_plugin');
    $plugin_definitions = $plugin->getDefinitions();
    $plugin_label = $plugin_definitions[$managed_plugin]['label']->__toString();

    $admin_url = Url::fromRoute('dyniva_matomo.admin_sites');

    $content = [
      'label' => $plugin_label . ': ' . $managed_entity->label(),
      'today' => \Drupal::service('date.formatter')->format(time(), 'custom', 'Y-m-d'),
      'admin' => Link::fromTextAndUrl(t('返回站点列表'), $admin_url)->toString(),
    ];

    return [
      '#theme' => 'dyniva_matomo_site_header',
      '#content' => $content,
    ];
  }

}
