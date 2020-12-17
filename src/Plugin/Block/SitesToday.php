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
class SitesToday extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $uuid_service = \Drupal::service('uuid');
    $build = [];
    $build['#attached']['library'][] = 'dyniva_matomo/toolbar';
    $settings = [
      'params' => [
        'dyniva-matomo-analytics-toolbar' => [
          'period' => 'day',
          'segment' => '',
        ],
      ],
      'api' => Url::fromRoute('dyniva_matomo.matomo_api')->toString(),
    ];
    $build['#attached']['drupalSettings']['dyniva_matomo'] = $settings;

    $sites = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'site',
        'status' => 1,
      ]);
    $rows = [];
    $run = [];
    $today = 'today';
    $yesterday = 'yesterday';

    foreach ($sites as $site) {
      $id = $site->get('matomo_site_id')->value;// $uuid_service->generate();
      $url = Url::fromRoute('dyniva_core.managed_entity.site.analytics_page', ['managed_entity_id' => $site->id()]);
      $rows [] = [
        "<div>{$site->domain->value}</div><div>{$site->label()}</div>",
        "<div>今天</div><div>昨天</div>",
        "<div id=\"nb_actions_{$today}_{$id}\">-</div><div id=\"nb_actions_{$yesterday}_{$id}\">-</div>",
        "<div id=\"nb_visits_{$today}_{$id}\">-</div><div id=\"nb_visits_{$yesterday}_{$id}\">-</div>",
        "<a target=\"_blank\" href=\"{$url->toString()}\">查看详情</a>",
      ];
    }
    $widget_id = 'dyniva_matomo_sites_today';
    $settings = [
      'auto_refresh' => FALSE,
      'refresh_interval' => 500,
      'api_method' => 'Custom.getSitesVisits',
      'api_callback' => 'dyniva_matomo_sites_visits_summary_api_callback',
      'params' => [
        'id' => $widget_id,
        'date' => "{$yesterday},{$today}",
        'idSite' => 'all',
      ],
    ];
    $build['#attached']['drupalSettings']['dyniva_matomo']['widgets'][$widget_id] = $settings;
    $build['#attached']['drupalSettings']['dyniva_matomo']['run'][] = $widget_id;

    $table = '<table data-striping="1">';
    $table .= '<thead><tr><th>网站名称</th><th></th><th>浏览量(PV)</th><th>访客数(UV)</th><th>操作</th></tr></thead>';
    $table .= '<tbody>';
    foreach ($rows as $row) {
      $table .= '<tr>';
      foreach ($row as $col) {
        $table .= "<td>$col</td>";
      }
      $table .= '</tr>';
    }
    $table .= '</tbody>';
    $table .= '</table>';
    $build['#markup'] = $table;

    return $build;
  }

}
