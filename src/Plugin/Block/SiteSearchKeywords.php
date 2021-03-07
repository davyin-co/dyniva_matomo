<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_site_search_keywords",
 *   admin_label = @Translation("关键词统计"),
 * )
 */
class SiteSearchKeywords extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $params = [
      'period' => \Drupal::request()->get('period') ?? 'day',
      'date' => \Drupal::request()->get('date') ?? 'today',
    ];
    $config['page_block'] ? $params['filter_limit'] = 10 : NULL;

    $managed_entity = \Drupal::routeMatch()->getParameter('managed_entity_id');
    $managed_entity ? $params['idSite'] = $managed_entity->matomo_site_id->value : '';
    $current_route = Url::fromRoute('dyniva_core.managed_entity.site.keywords_page', ['managed_entity_id' => $managed_entity->id()], [
      'attributes' => [
        'target' => '_blank',
      ],
    ]);

    $matomoQueryFactory = \Drupal::service('matomo.query_factory');
    $matomoQuery = $matomoQueryFactory->getQuery('Actions.getSiteSearchKeywords');
    $matomoResponse = $matomoQuery->setParameters($params)->execute()->getResponse();

    $config = $this->getConfiguration();

    return [
      '#theme' => 'dyniva_matomo_site_search_keywords',
      '#content' => $matomoResponse,
      '#config' => $config,
      '#full_link' => Link::fromTextAndUrl(t('更多'), $current_route)->toString(),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'page_block' => 0,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['page_block'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('页面区块'),
      '#default_value' => $this->configuration['page_block'],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->setConfigurationValue('page_block', $form_state->getValue('page_block'));
  }

}
