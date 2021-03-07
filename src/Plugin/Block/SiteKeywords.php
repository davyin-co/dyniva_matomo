<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class SiteKeywords
 *
 * @Block(
 *   id = "dyniva_matomo_site_keywords",
 *   admin_label = @Translation("Matomo site keywords"),
 * )
 */
class SiteKeywords extends MatomoWidgetBase {

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
    return 'dyniva_matomo_site_keywords_api_callback';
  }

  /**
   * {@inheritDoc}
   */
  public function getApiMethod() {
    return 'Referrers.getKeywords';
  }

  /**
   * {@inheritDoc}
   */
  public function getApiParams() {
    $params = [
      'expanded' => 1,
      'flat' => 1,
      'filter_limit' => $this->configuration['filter_limit'],
    ];

    if (!empty($this->configuration['period'])) {
      $params['period'] = $this->configuration['period'];
    }

    return $params;
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $form['filter_limit'] = array(
      '#title' => t('Rows limit'),
      '#type' => 'number',
      '#max' => 50,
      '#default_value' => !empty($this->configuration['filter_limit']) ? $this->configuration['filter_limit'] : 10,
    );
    return $form;
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->setConfigurationValue('filter_limit', $form_state->getValue('filter_limit'));
  }

}
