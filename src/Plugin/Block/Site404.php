<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\matomo_reporting_api\MatomoQueryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_site_404",
 *   admin_label = @Translation("站点404错误"),
 * )
 */
class Site404 extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\matomo_reporting_api\MatomoQueryFactoryInterface
   */
  protected $matomoQueryFactory;

  /**
   * SiteSearchEngines constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\matomo_reporting_api\MatomoQueryFactoryInterface $matomoQueryFactory
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MatomoQueryFactoryInterface $matomoQueryFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->matomoQueryFactory = $matomoQueryFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('matomo.query_factory')
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
    ];
    $config['page_block'] ? $params['filter_limit'] = 10 : NULL;

    $managed_entity = \Drupal::routeMatch()->getParameter('managed_entity_id');
    $managed_entity ? $params['idSite'] = $managed_entity->matomo_site_id->value : '';
    $full_route = Url::fromRoute('dyniva_core.managed_entity.site.matomo404_page', ['managed_entity_id' => $managed_entity->id()], [
      'attributes' => [
        'target' => '_blank',
      ],
    ]);

    $matomoQuery = $this->matomoQueryFactory->getQuery('Actions.getPageTitles');
    $matomoResponse = $matomoQuery->setParameters($params)->execute()->getResponse();

    $content = [];

    foreach ($matomoResponse as $record) {
      if (isset($record->label) && substr($record->label, 0, 8) == ' 404/URL') {
        $label_array = explode('/From =', $record->label);
        $label_source = explode('404/URL = ', $label_array[0]);
        $content[] = [
          'label_link' => $label_source[1],
          'label_from' => $label_array[1],
          'nb_visits' => $record->nb_visits,
          'nb_hits' => $record->nb_hits,
        ];
      }
    }

    return [
      '#theme' => 'dyniva_matomo_site_404',
      '#content' => $content,
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
