<?php

namespace Drupal\dyniva_matomo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Matomo analytics filter.
 *
 * @Block(
 *   id = "dyniva_matomo_analytics_filter",
 *   admin_label = @Translation("统计分析过滤"),
 * )
 */
class AnalyticsFilter extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * AnalyticsFilter constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }


  /**
   * {@inheritDoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $entity = $this->routeMatch->getParameter('managed_entity_id');

    $form = \Drupal::formBuilder()->getForm('Drupal\dyniva_matomo\Form\AnalyticsFilterForm', $entity);
    if ($config['hide_keyword']) {
      unset($form['keyword']);
    }
    if ($config['hide_period']) {
      unset($form['period']);
    }
    if ($config['hide_device']) {
      unset($form['device']);
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'hide_keyword' => 0,
      'hide_period' => 0,
      'hide_device' => 0,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['hide_keyword'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('隐藏关键词'),
      '#default_value' => $this->configuration['hide_keyword'],
    ];
    $form['hide_period'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('隐藏日期筛选'),
      '#default_value' => $this->configuration['hide_period'],
    ];
    $form['hide_device'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('隐藏设备筛选'),
      '#default_value' => $this->configuration['hide_device'],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->setConfigurationValue('hide_keyword', $form_state->getValue('hide_keyword'));
    $this->setConfigurationValue('hide_period', $form_state->getValue('hide_period'));
    $this->setConfigurationValue('hide_device', $form_state->getValue('hide_device'));
  }

}
