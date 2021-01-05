<?php


namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\dyniva_matomo\Form\MonthToolbarForm;
use Drupal\dyniva_matomo\Form\AnalyticsToolbarForm;

/**
 * 月度发文统计总览
 *
 * @Block(
 *  id = "dyniva_matomo_publish_month_summary",
 *  admin_label = "月度发文统计总览",
 * )
 */
class PublishMonthSummary extends ToolbarWidgetBase {

  /**
   * {@inheritDoc}
   */
  public function getToolbar() {
    try {
      $entity = $this->getContextValue('entity');
    } catch (\Exception $e) {
      $entity = null;
    }
    $form = \Drupal::formBuilder()->getForm(AnalyticsToolbarForm::class, $entity);
    $id = $this->getWidgetId();
    $form['#attributes']['data-id'] = $id;
    $form['#attached']['drupalSettings']['dyniva_matomo']['params'][$id] = [
      'period' => $form['period']['#default_value'],
    ];
    $form['period']['#title'] = t('Granularity');
    unset($form['date']);
    unset($form['date1']);
    unset($form['date2']);
    unset($form['segment']);
    unset($form['idSite']);
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function getApiCallback() {
    return 'dyniva_matomo_widget_publish_month_summary_api_callback';
  }

  /**
   * {@inheritDoc}
   */
  public function getApiParams() {
    return [
      'segment' => 'eventAction==content.create',
      //'period' => 'day',
      'action_segment' => 'content.create',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getApiMethod() {
    return 'Custom.getEventsName';
  }

  /**
   * Get block render content.
   */
  public function getContent() {
    $content = parent::getContent();
    $content['#attached']['library'] = ['dyniva_admin/echarts'];
    $content['#prefix'] = "<div class='matomo-widget' id='{$this->getWidgetId()}'>";
    $content['#markup'] = '<div class="chart-wrapper"></div>';
    $content['#suffix'] = "</div>";
    return $content;
  }

}
