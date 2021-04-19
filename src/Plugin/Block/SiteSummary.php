<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\matomo_reporting_api\MatomoQueryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_site_summary",
 *   admin_label = @Translation("站点访问概况"),
 * )
 */
class SiteSummary extends BlockBase implements ContainerFactoryPluginInterface {

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
    $matomoQuery = $this->matomoQueryFactory->getQuery(' VisitTime.getVisitInformationPerLocalTime');
    $matomoQuery->setParameters([
      'period' => 'day',
      'date' => 'today',
      'idSite' => \Drupal::routeMatch()->getParameter('managed_entity_id')->matomo_site_id->value,
    ]);
    $matomoResponse = $matomoQuery->execute()->getResponse();

    $managed_entity = \Drupal::routeMatch()->getParameter('managed_entity_id');
    $full_route = Url::fromRoute('dyniva_core.managed_entity.site.trend_page', [
      'managed_entity_id' => $managed_entity->id(),
    ]);

    return [
      '#theme' => 'dyniva_matomo_site_summary',
      '#content_json' => Json::encode($matomoResponse),
      '#full_link' => Link::fromTextAndUrl(t('更多'), $full_route)->toString(),
    ];
  }

}
