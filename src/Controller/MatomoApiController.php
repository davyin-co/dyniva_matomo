<?php

namespace Drupal\dyniva_matomo\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\matomo_reporting_api\MatomoQueryFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class MatomoApiController.
 *
 * @package Drupal\dyniva_matomo\Controller
 */
class MatomoApiController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Matomo query service.
   *
   * @var MatomoQueryFactoryInterface
   */
  protected $matomoQueryFactory;

  /**
   * Constructs.
   * @param MatomoQueryFactoryInterface $matomoQueryFactory
   */
  public function __construct(MatomoQueryFactoryInterface $matomoQueryFactory) {
    $this->matomoQueryFactory = $matomoQueryFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('matomo.query_factory')
    );
  }

  /**
   * Matomo query.
   */
  public function query(Request $request) {
    $method = $request->get('api_method');
    $response = [];
    if ($method) {
      $params = $request->get('params', []);
      $parts = explode('.', $method);
      if ($parts[0] == 'Custom') {
        if (method_exists($this, $parts[1])) {
          $response = $this->{$parts[1]}($params);
        }
      } elseif (!empty($params['_over_time'])) {
        unset($params['_over_time']);
        $response = $this->getApiOverTime($method, $params);
      } else {
        switch ($method) {
          default:
            $query = $this->matomoQueryFactory->getQuery($method);
            $query->setParameters($params);
            $response = $query->execute()->getResponse();
            break;
        }
      }
    }
//     if(is_array($response) && isset($response[0]->logo)) {
//       global $base_url;
//       $module_path = drupal_get_path('module', 'dyniva_matomo');
//       $prefix = $base_url . '/' . $module_path . '/icons';
//       foreach ($response as &$item) {
//         $item->icon = str_replace('plugins/Morpheus/icons/dist', $prefix, $item->logo);
//       }
//     }
//    sleep(60*5);
    return new JsonResponse($response);
  }

  /**
   *
   * @param array $params
   * @return mixed[]|mixed
   */
  public function getEventsData(array $params) {
    $method = $params['_method'];
    $action = $params['_action'];
    unset($params['_method']);
    unset($params['_action']);

    $action_id = FALSE;
    $query = $this->matomoQueryFactory->getQuery('Events.getAction');
    $query->setParameters($params);
    $actions = $query->execute()->getResponse();
    foreach ($actions as $item) {
      if ($item->label == $action) {
        $action_id = $item->idsubdatatable;
      }
    }
    $response = [];
    if ($action_id) {
      $params['idSubtable'] = $action_id;
      if (!empty($params['_over_time'])) {
        unset($params['_over_time']);
        $date_range = $this->getDateRange($params);
        $category = [];
        $datas = [];
        foreach ($date_range as $item) {
          $params['date'] = $item['date'];
          $query = $this->matomoQueryFactory->getQuery($method);
          $query->setParameters($params);
          $data = $query->execute()->getResponse();
          $convert = ['label' => $item['label']];
          if (!empty($data)) {
            foreach ($data as $cat) {
              $category[$cat->label] = t($cat->label, [], ['context' => 'Matomo Event']);
              $convert[$cat->label] = $cat->nb_events;
            }
          }
          $datas[] = $convert;
        }
        foreach ($datas as $index => $item) {
          foreach ($category as $key => $label) {
            if (!isset($item[$key])) {
              $datas[$index][$key] = 0;
            }
          }
        }
        $response['category'] = $category;
        $response['data'] = $datas;
      } else {
        $query = $this->matomoQueryFactory->getQuery($method);
        $query->setParameters($params);
        $response = $query->execute()->getResponse();
      }
    }
    return $response;
  }

  /**
   * Api data over time.
   *
   * @param string $method
   * @param array $params
   * @return mixed[]
   */
  public function getApiOverTime(string $method, array $params) {
    $date_range = $this->getDateRange($params);
    $data = [];
    foreach ($date_range as $item) {
      $params['date'] = $item['date'];
      $query = $this->matomoQueryFactory->getQuery($method);
      $query->setParameters($params);
      $response = $query->execute()->getResponse();
      $response->label = $item['label'];
      $data[] = $response;
    }
    return $data;
  }

  /**
   *
   * @param array $params
   * @return array
   */
  public static function getDateRange(array $params) {
    $period = $params['period'];
    $date = $params['date'];
    $dateObject = new DrupalDateTime($date);
    $date_range = [];
    switch ($period) {
      case 'day':
        for ($i = 0; $i < 30; $i++) {
          $date_range[] = [
            'label' => $dateObject->format('m-d'),
            'date' => $dateObject->format('Y-m-d'),
          ];
          $dateObject->modify('-1 day');
        }
        break;
      case 'week':
        for ($i = 0; $i < 12; $i++) {
          $date_range[] = [
            'label' => $dateObject->format('W周'),
            'date' => $dateObject->format('Y-m-d'),
          ];
          $dateObject->modify('-1 week');
        }
        break;
      case 'month':
        for ($i = 0; $i < 12; $i++) {
          $date_range[] = [
            'label' => $dateObject->format('Y-m'),
            'date' => $dateObject->format('Y-m-d'),
          ];
          $dateObject->modify('-1 month');
        }
        break;
      case 'year':
        for ($i = 0; $i < 6; $i++) {
          $date_range[] = [
            'label' => $dateObject->format('Y'),
            'date' => $dateObject->format('Y-m-d'),
          ];
          $dateObject->modify('-1 year');
        }
        break;
    }
    $date_range = array_reverse($date_range);
    return $date_range;
  }

  /**
   * Return sites views rank.
   * @param $params
   * @return mixed
   */
  public function getViewsRank($params) {
    $all = $params['idSite'] == 'all';
    $query = $this->matomoQueryFactory->getQuery('MultiSites.getAll');
    $params['module'] = 'MultiSites';
    $params['action'] = 'getAllWithGroups';
    $params['format'] = 'JSON';
    $query->setParameters($params);
    $url = $this->matomoQueryFactory->getQueryFactory()->getHttpClient()->getUrl();
    $query_params = [];
    $response = [];
    foreach ($query->getParameters() as $key => $val) {
      if ($val) {
        $query_params[] = $key . '=' . $val;
      }
    }
    $url .= '?' . join('&', $query_params);
    try {
      $fetched = file_get_contents($url);
      $content = json_decode($fetched, TRUE);
      if ($content && is_array($content) && isset($content['sites'])) {
        $response = $content['sites'];
      }
    } catch (\Exception $e) {

    }

    return $all ? [$response] : $response;
  }

  /**
   * Array to object.
   * @param $array
   * @return object|void
   */
  function arrayToObject($array) {
    if (gettype($array) != 'array') return;
    foreach ($array as $k => $v) {
      if (gettype($v) == 'array' || getType($v) == 'object')
        $array[$k] = (object) $this->arrayToObject($v);
    }
    return (object) $array;
  }

  /**
   * Return events category.
   * @param $params
   * @return mixed
   */
  public function getEventsCategory($params) {
    $all = $params['idSite'] == 'all';
    $query = $this->matomoQueryFactory->getQuery('Events.getCategory');
    $params['flat'] = 1;
    $query->setParameters($params);
    $response = $query->execute()->getResponse();
    $types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    $type_list = [];
    foreach ($types as $type) {
      $type_list[$type->id()] = $type->label();
    }
    $data = (object) [];
    if ($all) {
      foreach ($response as $sid => $site) {
        foreach ($site as $date => $item) {
          foreach ($item as $key => $val) {
            $label = array_key_exists($val->Events_EventCategory, $type_list) ? $type_list[$val->Events_EventCategory] : $val->Events_EventCategory;
            $val->label = $label;
            $val->Events_EventCategory = $label;
            if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $val->Events_EventAction)) {
              $data->$sid->$date[] = $val;
            }
          }
        }
      }
    } else {
      foreach ($response as $date => &$item) {
        foreach ($item as $key => $val) {
          $label = array_key_exists($val->Events_EventCategory, $type_list) ? $type_list[$val->Events_EventCategory] : $val->Events_EventCategory;
          $val->label = $label;
          $val->Events_EventCategory = $label;
          if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $val->Events_EventAction)) {
            $data->$date[] = $val;
          }
        }
      }
    }
    return $data;
  }

  /**
   * Return sites visits.
   * @param $params
   * @return array
   */
  public function getSitesVisits($params) {
    $data = [];
    $all = $params['idSite'] == 'all';
    $query = $this->matomoQueryFactory->getQuery($all ? 'MultiSites.getAll' : 'MultiSites.getOne');
    $query->setParameters($params);
    $response = $query->execute()->getResponse();
    $sites = $this->getSites($all ? [] : $params['idSite']);
    foreach ($response as $site) {
      if (array_key_exists($site->idsite, $sites)) {
        $site->label = $sites[$site->idsite]['title'];
        $site->analytics = Url::fromRoute('dyniva_core.managed_entity.site.analytics_page', ['managed_entity_id' => $sites[$site->idsite]['nid']])->toString();
        $data[] = $site;
      }
    }
    return $all ? [$data] : $data;
  }


  /**
   * Return events name.
   * @param $params
   * @return mixed
   */
  public function getEventsName($params) {
    $all = $params['idSite'] == 'all';
    $query = $this->matomoQueryFactory->getQuery('Events.getName');
    $params['flat'] = 1;
    $query->setParameters($params);
    $response = $query->execute()->getResponse();
    $data = (object) [];
    if ($all) {
      foreach ($response as $sid => $site) {
        foreach ($site as $date => $item) {
          $data->$sid->$date = [];
          foreach ($item as $key => $val) {
            $val->label = $val->Events_EventName;
            if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $val->Events_EventAction)) {
              $data->$sid->$date[] = $val;
            }
          }
        }
      }
    } else {
      foreach ($response as $date => &$item) {
        $data->$date = [];
        foreach ($item as $key => $val) {
          $val->label = $val->Events_EventName;
          if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $val->Events_EventAction)) {
            $data->$date[] = $val;
          }
        }
      }
    }
    return $data;
  }


  /**
   * Return events action.
   * @param $params
   * @return mixed
   */
  public function getEventsAction($params) {
    $all = $params['idSite'] == 'all';

    $params['flat'] = 1;
    $query = $this->matomoQueryFactory->getQuery('Events.getAction');
    $query->setParameters($params);
    $response = $query->execute()->getResponse();

    $data = (object) [];
    $types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    $type_list = [];
    foreach ($types as $type) {
      $type_list[$type->id()] = $type->label();
    }
    if ($all) {
      $sites = $this->getSites(array_keys($response));
      foreach ($response as $sid => $site) {
        if (array_key_exists($sid, $sites)) {
          $sid = $sites[$sid]['title'];
        } else {
          continue;
        }
        foreach ($site as $date => $item) {
          if ($params['period'] == 'range') {
            $item->Events_EventCategory = isset($type_list[$item->Events_EventCategory]) ? $type_list[$item->Events_EventCategory] : $item->Events_EventCategory;
            if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $item->Events_EventAction)) {
              $data->$sid->$date[] = $item;
            }
          } else {
            foreach ($item as $key => $val) {
              $val->Events_EventCategory = isset($type_list[$val->Events_EventCategory]) ? $type_list[$val->Events_EventCategory] : $val->Events_EventCategory;
              if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $val->Events_EventAction)) {
                $data->$sid->$date[] = $val;
              }
            }
          }
        }
      }
      return [$data];
    } else {
      foreach ($response as $date => &$item) {
        if ($params['period'] == 'range') {
          $item->Events_EventCategory = isset($type_list[$item->Events_EventCategory]) ? $type_list[$item->Events_EventCategory] : $item->Events_EventCategory;
          if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $item->Events_EventAction)) {
            $data->$date[] = $item;
          }
        } else {
          foreach ($item as $key => $val) {
            $val->Events_EventCategory = isset($type_list[$val->Events_EventCategory]) ? $type_list[$val->Events_EventCategory] : $val->Events_EventCategory;
            if (empty($params['action_segment']) || ($params['action_segment'] && $params['action_segment'] == $val->Events_EventAction)) {
              $data->$date[] = $val;
            }
          }
        }
      }
    }
    return $data;
  }

  /**
   * Return sites.
   * @param $motomo_sites
   * @return \Drupal\Core\Entity\EntityInterface[]|static[]
   */
  private function getSites($motomo_sites) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'site')
      ->condition('status', 1)
      ->condition('_deleted', 0);
    if ($motomo_sites) {
      $query->condition('matomo_site_id', $motomo_sites, 'in');
    }
    $data = [];
    foreach (Node::loadMultiple($query->execute()) as $entity) {
      $data[$entity->get('matomo_site_id')->value] = [
        'title' => $entity->getTitle(),
        'nid' => $entity->id(),
      ];
    }
    return $data;
  }

}
