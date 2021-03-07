<?php

namespace Drupal\dyniva_matomo\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VisitsController
 *
 * @package Drupal\dyniva_matomo\Controller
 */
class VisitsController extends ControllerBase implements ContainerInjectionInterface {

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
   * VisitsController constructor.
   *
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layoutManager
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   */
  public function __construct(LayoutPluginManagerInterface $layoutManager, BlockManagerInterface $blockManager) {
    $this->layoutManager = $layoutManager;
    $this->blockManager = $blockManager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.core.layout'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * 站点概况
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function summary(Request $request) {
    $regions = [
      'content' => [
        $this->renderBlock('dyniva_matomo_analytics_toolbar', [
          'period_hide' => 1,
          'segment_hide' => 1,
          'date_hide' => 1,
        ]),
        $this->renderBlock('dyniva_matomo_site_keywords', [
          'period' => 'year',
          'auto_refresh' => false,
          'refresh_interval' => 60,
          'filter_limit' => 10,
        ]),
        $this->renderBlock('dyniva_matomo_site_referrers', [
          'period' => 'year',
          'auto_refresh' => false,
          'refresh_interval' => 60,
          'filter_limit ' => 10,
        ]),
        $this->renderBlock('dyniva_matomo_page_visits', [
          'period' => 'year',
          'auto_refresh' => false,
          'refresh_interval' => 60,
          'filter_limit' => 10,
        ]),
      ],
    ];
    return $this->renderLayout('layout_onecol', $regions);
  }

  /**
   * @param $layout
   * @param $regions
   * @param array $params
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function renderLayout($layout, $regions, $params = []) {
    return $this->layoutManager->createInstance($layout, $params)->build($regions);
  }

  /**
   * @param $id
   * @param array $params
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function renderBlock($id, $params = []) {
    $content = $this->blockManager->createInstance($id, $params)->build();
    return $this->theme($content, $id, $params);
  }

  /**
   * @param $content
   * @param $id
   * @param array $config
   *
   * @return array
   */
  private function theme($content, $id, $config = []) {
    $label = $config['label'] ?? '';
    $config['#attributes']['id'] = Html::getUniqueId('block-' . $id);
    $config['#attributes']['class'][] = 'block';
    $config['#attributes']['class'][] = Html::cleanCssIdentifier('block-' . $id);
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

}
