<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class Referrers
 *
 * @Block(
 *   id = "dyniva_matomo_site_referrers",
 *   admin_label = @Translation("Matomo site referrers"),
 * )
 */
class SiteReferrers extends MatomoWidgetBase {

  /**
   * {@inheritDoc}
   */
  public function getContent() {
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function getApiCallback() {
    return 'dyniva_matomo_site_referrers_api_callback';
  }

  /**
   * {@inheritDoc}
   */
  public function getApiMethod() {
    return 'Referrers.getAll';
  }

  /**
   * {@inheritDoc}
   */
  public function getApiParams() {
    $params = [
      'expanded' => 1,
      'flat' => 1,
      'filter_limit' => 10,
    ];

    return $params;
  }

}
