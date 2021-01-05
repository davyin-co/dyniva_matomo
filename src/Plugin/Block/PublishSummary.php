<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\dyniva_matomo\Form\RangeToolbarForm;

/**
 * 发文总览
 *
 * @Block(
 *  id = "dyniva_matomo_publish_summary",
 *  admin_label = "发文总览",
 * )
 */
class PublishSummary extends MatomoWidgetBase {

  /**
   * {@inheritDoc}
   */
  public function getApiCallback() {
    return 'dyniva_matomo_widget_publish_summary_api_callback';
  }
  /**
   * {@inheritDoc}
   */
  public function getApiParams() {
    return [
      'segment' => 'eventAction==content.create',
      //'secondaryDimension' => 'eventName',
      'period' => 'day',
      'action_segment' => 'content.create'
    ];
  }
  /**
   * {@inheritDoc}
   */
  public function getApiMethod() {
    return 'Custom.getEventsCategory';
  }

  public function getContent() {
    $content = parent::getContent();
    $html = '<p class="total-counter">发文总数<span data-action="total-counter">-</span>篇</p>';
    $html .= '<p class="category-counter" data-action="category-counter" data-prefix="其中" data-separator=", " data-template="{0}发文量{1}篇"></p>';
    $html .= '</p>';
    $content['#markup'] = $html;
    return $content;
  }
}
