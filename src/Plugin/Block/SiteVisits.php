<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\matomo_reporting_api\MatomoQueryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_sites_analytics_summary",
 *   admin_label = @Translation("站点访问统计"),
 * )
 */
class SiteVisits extends BlockBase implements ContainerFactoryPluginInterface {

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
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'site')
      ->condition('matomo_site_id', NULL, '<>');
    $result = $query->execute();

    $content = [];

    foreach ($result as $nid) {
      $site = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $matomoQuery = $this->matomoQueryFactory->getQuery('VisitsSummary.get');
      $matomoQuery->setParameters([
        'idSite' => $site->matomo_site_id->value,
        'period' => \Drupal::request()->get('period') ?? 'day',
        'date' => \Drupal::request()->get('date') ?? 'today',
      ]);
      $matomoResponse = $matomoQuery->execute()->getResponse();

      $content[] = [
        'label' => $site->label(),
        'stats' => $matomoResponse,
      ];
    }

    return [
      '#theme' => 'dyniva_matomo_sites_analytics_summary',
      '#content' => $content,
    ];
  }

}
