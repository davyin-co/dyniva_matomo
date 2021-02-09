<?php

namespace Drupal\dyniva_matomo\TwigExtension;

class VisitsTimeExtension extends \Twig_Extension {

  /**
   * {@inheritDoc}
   */
  public function getName() {
    return 'dyniva_matomo.visits_time_extension';
  }

  /**
   * @return \Twig_SimpleFilter[]
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('visits_time_transform', [$this, 'visitsTimeTransform']),
    ];
  }

  /**
   * @param $context
   *
   * @return false|string
   */
  public function visitsTimeTransform($context) {
    if (is_numeric($context)) {
      $context = gmdate("H:i:s", $context);;
    }

    return $context;
  }

}
