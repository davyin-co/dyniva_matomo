<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\dyniva_matomo\Form\RangeToolbarForm;

/**
 * 市县年度发文排行榜Top8
 *
 * @Block(
 *  id = "dyniva_matomo_city_content_publish",
 *  admin_label = "市县年度发文排行榜Top8",
 * )
 */
class CityContentPublish extends MatomoWidgetBase {

  /**
   * {@inheritDoc}
   */
  public function getApiCallback() {
    return 'dyniva_matomo_widget_city_content_publish_api_callback';
  }

  /**
   * {@inheritDoc}
   */
  public function getApiParams() {
    return [
      'segment' => 'eventAction==content.create',
      'flat' => 1,
      'secondaryDimension' => 'eventCategory',
      'period' => 'day',
      'action_segment' => 'content.create',
      'idSite' => 'all',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getApiMethod() {
    return 'Custom.getEventsAction';
  }

  /**
   * {@inheritDoc}
   */
  public function getContent() {
    return [
      '#markup' => '<div class="chart-wrapper"></div>',
      '#attached' => [
        'library' => ['dyniva_admin/echarts'],
      ],
    ];
  }

}
