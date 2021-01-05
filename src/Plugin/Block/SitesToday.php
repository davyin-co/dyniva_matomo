<?php


namespace Drupal\dyniva_matomo\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * 网站目录
 *
 * @Block(
 *  id = "dyniva_matomo_sites_today",
 *  admin_label = "网站目录",
 * )
 */
class SitesToday extends MatomoWidgetBase {

  /**
   * {@inheritDoc}
   */
  public function getContent() {
    $table = '<div class="sites-total"><h2 class="title">' . t('All sites dashboard') . '</h2><span class="detail">(' . t('Total views') . ': <strong class="pageviews">0</strong> ' . t('Page views') . ', <strong class="visits">0</strong> ' . t('Visits') . ')</span> </div>';
    $table .= '<table data-striping="1" class="full-table">';
    $table .= '<thead><tr><th>' . t('Site Name') . '</th><th>' . t('Views(PV)') . '</th><th>' . t('Trend') . '</th><th>' . t('Visits(UV)') . '</th><th>' . t('Trend') . '</th><th>' . t('Operations') . '</th></tr></thead>';
    $table .= '<tbody>';
    $table .= '</tbody>';
    $table .= '</table>';
    $build['#markup'] = $table;

    return [
      '#markup' => $table,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getApiCallback() {
    return $this->configuration['api_callback'];
  }

  /**
   * {@inheritDoc}
   */
  public function getApiParams() {
    $params = [
      'period' => $this->configuration['period'],
      'enhanced' => 1,
      'flat' => 1,
    ];
    return $params;
  }

  /**
   * {@inheritDoc}
   */
  public function getApiMethod() {
    return $this->configuration['api_method'];
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'api_callback' => 'dyniva_matomo_sites_visits_summary_api_callback',
      'api_method' => 'Custom.getSitesVisits',
      'period' => 'day',
    ];
  }
}
