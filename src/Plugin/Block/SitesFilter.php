<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Class SitesFilter
 *
 * @Block(
 *   id = "dyniva_matomo_sites_filter",
 *   admin_label = "统计站点过滤",
 * )
 */
class SitesFilter extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\dyniva_matomo\Form\SitesToolbarForm');
    return $form;
  }

}
