<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\matomo_reporting_api\MatomoQueryFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Matomo Block Plugin.
 *
 * @Block(
 *   id = "dyniva_matomo_site_articles_post",
 *   admin_label = @Translation("站点发文量"),
 * )
 */
class SiteArticlesPost extends BlockBase implements ContainerFactoryPluginInterface {

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
    $date = \Drupal::request()->get('date');
    if (!empty($date)) {
      $date_array = explode(',', $date);
      $date1 = strtotime($date_array[0]);
      $date2 = strtotime($date_array[1]) + 3600*24;
    }
    else {
      $date1 = strtotime(date('Y-m-d') . '00:00:00');
      $date2 = strtotime(date('Y-m-d') . '23:59:59');
    }
    $query = \Drupal::entityQueryAggregate('node')->groupBy('site_ref');
    $query->condition('type', 'article');
    if (isset($date1)) {
      $query->condition('created', $date1, '>=');
    }
    if (isset($date2)) {
      $query->condition('created', $date2, '<=');
    }
    $query->aggregate('nid', 'COUNT');
    $result = $query->execute();

    $content = [];

    foreach ($result as $row) {
      if (!empty($row['site_ref_target_id'])) {
        $site = \Drupal::entityTypeManager()->getStorage('node')->load($row['site_ref_target_id']);
        $matomo_site_id = $site->matomo_site_id;
        if (!empty($matomo_site_id) && is_numeric($matomo_site_id)) {
          $matomoQuery = $this->matomoQueryFactory->getQuery('VisitsSummary.get');
          $matomoQuery->setParameters([
            'idSite' => $matomo_site_id,
            'period' => \Drupal::request()->get('period') ?? 'day',
            'date' => \Drupal::request()->get('date') ?? 'today',
          ]);
          $matomoResponse = $matomoQuery->execute()->getResponse();
        }
        else {
          $matomoResponse = NULL;
        }
        $content[] = [
          'label' => $site->label(),
          'count' => $row['nid_count'],
          'stats' => $matomoResponse,
        ];
      }
    }

    return [
      '#theme' => 'dyniva_matomo_site_articles_post',
      '#content' => $content,
    ];
  }

}
