<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

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
 *   id = "dyniva_matomo_analytics_sites",
 *   admin_label = @Translation("站点总览"),
 * )
 */
class AnalyticsSites extends BlockBase implements ContainerFactoryPluginInterface {

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
    $query_type = \Drupal::request()->get('type');
    $query_keyword = \Drupal::request()->get('keyword');

    $query = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('type', 'site');

    if ($query_type == 'domain') {
      $query->condition('domain', '%' . $query_keyword . '%', 'LIKE');
    }
    if ($query_type == 'title') {
      $query->condition('title', '%' . $query_keyword . '%', 'LIKE');
    }

    $result = $query->execute();

    $return = [];

    foreach ($result as $nid) {
      $site = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      if (isset($site->matomo_site_id) && !empty($site->matomo_site_id->value)) {
        $matomoQuery = $this->matomoQueryFactory->getQuery('VisitsSummary.get');
        $todayStats = $matomoQuery->setParameters([
          'period' => 'day',
          'date' => 'today',
          'idSite' => $site->matomo_site_id->value,
        ])->execute()->getResponse();
        $yesterdayStats = $matomoQuery->setParameters([
          'period' => 'day',
          'date' => 'yesterday',
          'idSite' => $site->matomo_site_id->value,
        ])->execute()->getResponse();

        $summary_url = Url::fromRoute('dyniva_core.managed_entity.site.analytics_page', ['managed_entity_id' => $site->id()], [
          'attributes' => [
            'target' => '_blank',
          ],
        ]);
        $summary_link = Link::fromTextAndUrl(t('查看报告'), $summary_url)->toString();

        $return[] = [
          'label' => $site->label(),
          'domain' => $site->domain->value,
          'today' => $todayStats,
          'yesterday' => $yesterdayStats,
          'summary' => $summary_link
        ];

      }
    }

    return [
      '#theme' => 'dyniva_matomo_analytics_sites',
      '#sites' => $return,
    ];
  }

}
