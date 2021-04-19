<?php

namespace Drupal\dyniva_matomo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Html;


/**
 * Class AdminController.
 *
 * @package Drupal\dyniva_matomo\Controller
 */
class AdminController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Layout manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface
   */
  protected $layoutManager;

  /**
   * Block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * AdminController constructor.
   *
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layoutManager
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   */
  public function __construct(LayoutPluginManagerInterface $layoutManager, BlockManagerInterface $blockManager) {
    $this->layoutManager = $layoutManager;
    $this->blockManager = $blockManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.core.layout'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Real time page
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function realTime(Request $request) {
    $realTimeBlock = [
      '#theme' => 'dyniva_matomo_block_real_time',
      '#label' => '实时访问',
      '#content' => [
        'left' => $this->renderBlock('dyniva_matomo_real_time_visitor', [
          'lastMinutes' => 120,
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
        ]),
        'right' => $this->renderBlock('dyniva_matomo_visit_real_time_of_day', [
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
        ]),
      ],
    ];
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_toolbar', [
          'date_hide' => 1,
          'period_hide' => 1,
          'segment_hide' => 1,
        ]),
        $realTimeBlock,
        $this->renderBlock('dyniva_matomo_pages', [
          'label' => t('Visit Details') . '-' . t('Page Titles'),
          'period' => 'day',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'filter_limit' => 50,
          'api_callback' => 'dyniva_matomo_site_pages_api_callback',
          'api_method' => 'Actions.getPageTitles',
          'total' => TRUE,
          'visit_log' => TRUE,
        ]),
        $this->renderBlock('dyniva_matomo_pages', [
          'label' => t('Visit Details') . '-' . t('Pages'),
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'filter_limit' => 50,
          'period' => 'day',
          'api_callback' => 'dyniva_matomo_site_pages_api_callback',
          'total' => TRUE,
          'visit_log' => TRUE,
        ]),
      ],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

  public function sites(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_toolbar', [
          'period_hide' => 1,
          'segment_hide' => 1,
          'idSite_all' => 1,
        ]),
        $this->renderBlock('dyniva_matomo_sites_today', [
          'auto_refresh' => TRUE,
          'refresh_interval' => 60 * 5,
        ])],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

  public function sitesList(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_sites_filter'),
        $this->renderBlock('dyniva_matomo_analytics_sites'),
      ],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

  public function visits(Request $request) {
    $tags = $this->renderBlock('dyniva_matomo_custom', [
        'label' => '热门标签',
        'auto_refresh' => FALSE,
        'refresh_interval' => 60,
        'date' => '2020-01-01,' . date('Y-m-d'),
        'table_headers' => '名称,热度',
        'api_callback' => 'dyniva_matomo_events_list_api_callback',
        'segment' => 'eventAction==content.tags',
        'api_method' => 'Custom.getEventsName',
        'filter_limit' => 50,
        'action_segment' => 'content.tags',
        'class' => 'table-widget',
      ]
    );
    $tags['#attributes']['class'][] = "table-wrapper";
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_toolbar', [
          'period_hide' => 1,
          'segment_hide' => 1,
          'date_range' => 1,
        ]),
        $this->renderBlock('dyniva_matomo_site_page_views', [
          'label' => t('Site Dashboard'),
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
        ]),
        $this->renderBlock('dyniva_matomo_pages', [
          'label' => t('Page Titles'),
          'period' => 'range',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'filter_limit' => 50,
          'api_callback' => 'dyniva_matomo_site_pages_api_callback',
          'api_method' => 'Actions.getPageTitles',
          'total' => TRUE,
          'visit_log' => TRUE,
          'evolution' => TRUE,
        ]),
        $this->renderBlock('dyniva_matomo_pages', [
          'label' => t('Pages'),
          'period' => 'range',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'filter_limit' => 50,
          'api_callback' => 'dyniva_matomo_site_pages_api_callback',
          'total' => TRUE,
          'visit_log' => TRUE,
          'evolution' => TRUE,
        ]),
        $tags,
        $this->renderBlock('dyniva_matomo_search_keywords', [
          'label' => '搜索关键词',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'filter_limit' => 50,
        ]),
        $this->renderBlock('dyniva_matomo_search_keywords_no_results', [
          'label' => '无结果搜索关键词',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'filter_limit' => 50,
        ]),
      ],
    ];

    return $this->renderLayout('layout_onecol', $regions);
  }

  /**
   * 排名
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function rank(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_toolbar', [
          'date_hide' => 1,
          'period_hide' => 1,
          'segment_hide' => 1,
          'idSite_all' => 1,
        ]),
        $this->renderBlock('dyniva_matomo_views_rank', [
          'label' => '市县访问量排行榜Top8',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'filter_limit' => 8,
        ]),
        $this->renderBlock('dyniva_matomo_custom_date_range', [
          'label' => '内容访问量排行Top10',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'api_callback' => 'dyniva_matomo_events_list_api_callback',
          'segment' => 'eventAction==content.view',
          'api_method' => 'Custom.getEventsCategory',
          'filter_limit' => 10,
          'action_segment' => 'content.view',
        ]),
      ],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

  public function contentPublish(Request $request) {
    $regions = [
      'top' => [
        $this->renderBlock('dyniva_matomo_analytics_toolbar', [
          //'date_hide' => 1,
          'period_hide' => 1,
          'segment_hide' => 1,
          'idSite_all' => 1,
          'date_range' => 1,
        ]),
        $this->renderBlock('dyniva_matomo_publish_summary', [
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
        ]),
        $this->renderBlock('dyniva_matomo_publish_month_summary', [
          'label' => '趋势图',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
        ]),
        $this->renderBlock('dyniva_matomo_custom', [
          'label' => '用户发文统计',
          'auto_refresh' => FALSE,
          'refresh_interval' => 60,
          'api_callback' => 'dyniva_matomo_user_content_create_api_callback',
          'segment' => 'eventAction==user.content.create',
          'period' => 'range',
          'api_method' => 'Custom.getEventsAction',
          'action_segment' => 'user.content.create',
        ]),
      ],
    ];
    return $this->renderLayout('layout_twocol', $regions);
  }

  public function siteArticlesPost(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_filter'),
        $this->renderBlock('dyniva_matomo_site_articles_post'),
      ],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

  public function usersReport(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_toolbar', [
          'date_range' => 1,
          'period_hide' => 1,
          'segment_hide' => 1,
          'idSite_all' => 1,
        ]),
      ],
    ];

    $html = '<p class="bg-counter">中共贵州省委组织部网站在此期间统一身份认证用户<span data-action="total-counter">-</span>个</p>';
    $html .= '<p class="category-counter" data-action="role-counter" data-prefix="其中" data-separator=", " data-template="{0}{1}个"></p>';
    $regions['content'][] = $this->renderBlock('dyniva_matomo_custom', [
      'auto_refresh' => FALSE,
      'refresh_interval' => 60,
      'api_callback' => 'dyniva_matomo_users_summary_api_callback',
      'segment' => 'eventAction==user.create',
      'period' => 'day',
      'api_method' => 'Custom.getEventsName',
      'action_segment' => 'user.create',
      'content' => [
        '#markup' => $html,
      ],
    ]);

    $html = '<p class="bg-counter">发文总数<span data-action="total-content-create">-</span>篇</p>';
    $regions['content'][] = $this->renderBlock('dyniva_matomo_custom', [
      'auto_refresh' => FALSE,
      'refresh_interval' => 60,
      'api_callback' => 'dyniva_matomo_users_summary2_api_callback',
      'segment' => 'eventAction==content.create',
      'period' => 'day',
      'api_method' => 'Custom.getEventsCategory',
      'action_segment' => 'content.create',
      'content' => [
        '#markup' => $html,
      ],
    ]);

    $regions['content'][] = $this->renderBlock('dyniva_matomo_events_category', [
      'label' => '年度发文统计',
      'auto_refresh' => FALSE,
      'refresh_interval' => 60,
      'segment' => 'eventAction==content.create',
    ]);

    $regions['content'][] = $this->renderBlock('dyniva_matomo_city_content_publish', [
      'label' => '市县发文排行榜Top8',
      'auto_refresh' => FALSE,
      'refresh_interval' => 60,
    ]);

    return $this->renderLayout('layout_onecol', $regions);
  }

  private function renderLayout($layout, $regions, $params = []) {
    return $this->layoutManager
      ->createInstance($layout, $params)
      ->build($regions);
  }

  private function renderBlock($id, $params = []) {
    $content = $this->blockManager
      ->createInstance($id, $params)
      ->build();
    return $this->theme($content, $id, $params);
  }

  private function theme($content, $id, $config = []) {
    $config['#attributes']['id'] = Html::getUniqueId('block-' . $id);
    $config['#attributes']['class'][] = 'block';
    $config['#attributes']['class'][] = Html::cleanCssIdentifier('block-' . $id);
    $label = $config['label'] ?? '';
    $attributes = $config['#attributes'];
    return [
      '#theme' => 'dyniva_matomo_block_renderer',
      '#content' => $content,
      '#attributes' => [
        'id' => $attributes['id'],
        'class' => $attributes['class'],
      ],
      '#label' => $label,
    ];
  }

  /**
   * 站点发文统计：中大融合门户使用.
   */
  public function contentSummary(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_filter'),
        $this->renderBlock('dyniva_matomo_site_articles_post'),
      ],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

  /**
   * 站点访问统计：中大融合门户使用.
   */
  public function visitsSummary(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_filter'),
        $this->renderBlock('dyniva_matomo_sites_analytics_summary'),
      ],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

}
