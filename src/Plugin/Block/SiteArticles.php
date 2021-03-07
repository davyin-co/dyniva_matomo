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
 *   id = "dyniva_matomo_site_articles",
 *   admin_label = @Translation("站点文章"),
 * )
 */
class SiteArticles extends BlockBase implements ContainerFactoryPluginInterface {

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
      'expanded' => 1,
      'flat' => 1,
    ];
    $config['page_block'] ? $params['filter_limit'] = 10 : NULL;

    $managed_entity = \Drupal::routeMatch()->getParameter('managed_entity_id');
    $current_route = Url::fromRoute('dyniva_core.managed_entity.site.articles_page', ['managed_entity_id' => $managed_entity->id()], [
      'attributes' => [
        'target' => '_blank',
      ],
    ]);

    $managed_entity ? $params['idSite'] = $managed_entity->matomo_site_id->value : '';

    //$matomoQuery = $this->matomoQueryFactory->getQuery('CustomVariables.getCustomVariablesValuesFromNameId');
    $matomoQuery = $this->matomoQueryFactory->getQuery('Actions.getPageUrls');
    $matomoResponse = $matomoQuery->setParameters($params)->execute()->getResponse();

    $content = [];

//    foreach ($matomoResponse as $response) {
//      $label = explode('/', $response->label);
//      if (isset($label[1]) && is_numeric($label[1])) {
//        // Node ID, but this nid is its id in original website.
//        $node = \Drupal::entityQuery('node')
//          ->condition('type', 'article')
//          ->condition('subentity_id', $label[1])
//          ->execute();
//
//        foreach ($node as $nid) {
//          $article = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
//          if ($article) {
//            $response->nid = $nid;
//            $response->site = !empty($article->site_ref) ? $article->site_ref->entity->title->value : NULL;
//            $response->title = $article->label();
//            $content[] = $response;
//          }
//        }
//      }
//    }

    return [
      '#theme' => 'dyniva_matomo_site_articles',
      '#content' => $matomoResponse,
      '#config' => $config,
      '#full_link' => Link::fromTextAndUrl(t('更多'), $current_route)->toString(),
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
