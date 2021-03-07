<?php

namespace Drupal\dyniva_matomo\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

class AnalyticsFilterForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'dyniva_matomo_analytics_filter_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EntityInterface $entity = NULL) {
    $route = \Drupal::routeMatch();
    $query_params = [
      'period' => 'day',
      'date' => 'today',
    ];
    if ($route->getParameter('managed_entity_id')) {
      $query_params['managed_entity_id'] = $route->getParameter('managed_entity_id')->id();
    }
    $query_period = \Drupal::request()->query->get('period');
    $query_date = \Drupal::request()->query->get('date');
    if ($query_period == 'range') {
      $query_range = explode(',', $query_date);
    }

    $form['period'] = [
      '#type' => 'details',
      '#title' => $this->t('时间'),
      '#open' => TRUE,
    ];
    $form['period']['today'] = [
      '#type' => 'item',
      '#markup' => Link::fromTextAndUrl(t('今天'), Url::fromRoute($route->getRouteName(), $query_params))->toString(),
    ];
    $form['period']['date1'] = [
      '#type' => 'date',
      '#date_date_format' => 'Y-m-d',
      '#default_value' => !empty($query_range) ? $query_range[0] : NULL,
    ];
    $form['period']['date2'] = [
      '#type' => 'date',
      '#date_date_format' => 'Y-m-d',
      '#default_value' => !empty($query_range) ? $query_range[1] : NULL,
    ];
    $form['period']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('查询'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $date1 = $form_state->getValue('date1');
    $date2 = $form_state->getValue('date2');

    $params = [];
    if ($managed_entity = \Drupal::routeMatch()->getParameter('managed_entity_id')) {
      $params['managed_entity_id'] = $managed_entity->id();
    }

    if (!empty($date1) && !empty($date2)) {
      $params['period'] = 'range';
      $params['date'] = $date1 . ',' . $date2;
    }

    $route = \Drupal::routeMatch()->getRouteName();
    $form_state->setRedirect($route, $params);
  }

}
