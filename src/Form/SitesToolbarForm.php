<?php

namespace Drupal\dyniva_matomo\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class SitesToolbarForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'dyniva_matomo_sites_toolbar_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['type'] = [
      '#type' => 'select',
      '#options' => [
        'domain' => $this->t('按域名'),
        'title' => $this->t('按名称'),
      ],
    ];
    $form['keyword'] = [
      '#type' => 'textfield',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('搜索'),
    ];
    if (!empty(\Drupal::request()->get('keyword'))) {
      $form['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('返回所有站点'),
        '#submit' => [
          '::resetForm',
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $query = [
      'type' => $values['type'],
      'keyword' => $values['keyword'],
    ];
    $form_state->setRedirectUrl(Url::fromRoute('dyniva_matomo.admin_sites', $query));
  }

  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('dyniva_matomo.admin_sites');
  }

}
