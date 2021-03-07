<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\matomo_reporting_api\MatomoQueryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_site_search_engines",
 *   admin_label = @Translation("搜索引擎"),
 * )
 */
class SiteSearchEngines extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\matomo_reporting_api\MatomoQueryFactoryInterface
   */
  protected $matomoQueryFactory;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * SiteSearchEngines constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\matomo_reporting_api\MatomoQueryFactoryInterface $matomoQueryFactory
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MatomoQueryFactoryInterface $matomoQueryFactory, RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->matomoQueryFactory = $matomoQueryFactory;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('matomo.query_factory'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $params = [
      'period' => \Drupal::request()->get('period') ?? 'day',
      'date' => \Drupal::request()->get('date') ?? 'today',
      'idSite' => $this->routeMatch->getParameter('managed_entity_id')->matomo_site_id->value,
    ];
    $config['page_block'] ? $params['filter_limit'] = 10 : NULL;

    $full_route = Url::fromRoute('dyniva_core.managed_entity.site.search_engines_page', [
      'managed_entity_id' => $this->routeMatch->getParameter('managed_entity_id')->id(),
    ], ['attributes' => ['target' => '_blank']]);

    $matomoQuery = $this->matomoQueryFactory->getQuery('Referrers.getSearchEngines');
    $matomoResponse = $matomoQuery->setParameters($params)->execute()->getResponse();

    return [
      '#theme' => 'dyniva_matomo_site_search_engines',
      '#content' => $matomoResponse,
      '#config' => $config,
      '#full_link' => Link::fromTextAndUrl(t('更多'), $full_route)->toString(),
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
