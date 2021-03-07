<?php

namespace Drupal\dyniva_matomo\Plugin\ManagedEntity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\dyniva_core\Entity\ManagedEntity;
use Drupal\dyniva_core\Plugin\ManagedEntityPluginBase;

/**
 * ManagedEntity Plugin.
 *
 * @ManagedEntityPlugin(
 *   id = "keywords",
 *   label = @Translation("关键字统计"),
 *   weight = 5,
 * )
 */
class MatomoKeywords extends ManagedEntityPluginBase {

  /**
   * @param \Drupal\dyniva_core\Entity\ManagedEntity $managedEntity
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function buildPage(ManagedEntity $managedEntity, EntityInterface $entity) {
    $view = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId())->view($entity, 'matomo_keywords');
    return $view;
  }

  /**
   * {@inheritDoc}
   */
  public function isMenuTask(ManagedEntity $managedEntity) {
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function isMenuAction(ManagedEntity $managedEntity) {
    return FALSE;
  }

}
