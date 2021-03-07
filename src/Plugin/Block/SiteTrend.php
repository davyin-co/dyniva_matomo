<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\matomo_reporting_api\MatomoQueryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_site_trend",
 *   admin_label = @Translation("访问趋势"),
 * )
 */
class SiteTrend extends BlockBase implements ContainerFactoryPluginInterface {

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
    // Set a default diff days interval.
    $diff_days = 14;
    if (\Drupal::request()->get('period') == 'range') {
      $param_date = \Drupal::request()->get('date');
      $param_date = explode(',', $param_date);
      $start_date = date_create($param_date[0]);
      $end_date = date_create($param_date[1]);
      $diff_days = date_diff($start_date, $end_date)->days + 1;
    }
    $matomoQuery = $this->matomoQueryFactory->getQuery('VisitsSummary.get');
    $matomoQuery->setParameters([
      'period' => 'day',
      'date' => 'last' . $diff_days,
      'idSite' => $this->routeMatch->getParameter('managed_entity_id')->matomo_site_id->value,
    ]);
    $matomoResponse = $matomoQuery->execute()->getResponse();

    $content = [];

    foreach ($matomoResponse as $key => $response) {
      if (empty($response)) {
        $response = new \stdClass();
      }
      $response->date = $key;
      $content[] = $response;
    }

    return [
      '#theme' => 'dyniva_matomo_site_trend',
      '#content' => $content,
      '#content_json' => Json::encode($content),
    ];
  }

}
