<?php

namespace Drupal\dyniva_matomo\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Defines custom matomo analytics field.
 *
 * @ViewsField("dyniva_matomo_bounce_rate")
 */
class BounceRate extends FieldPluginBase {

  /**
   * {@inheritDoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritDoc}
   */
  public function defineOptions() {
    return parent::defineOptions();
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $row) {
    $entity = $this->getEntity($row);
    return $entity->matomo_visits_summary ? $entity->matomo_visits_summary->bounce_rate : '';
  }

}
