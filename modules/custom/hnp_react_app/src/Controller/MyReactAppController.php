<?php

namespace Drupal\hnp_react_app\Controller;

use Drupal\Core\Controller\ControllerBase;

/** 
 * Controller for My React App. 
 */
class MyReactAppController extends ControllerBase {
  /** 
   * Renders the react app. 
   * @return array 
   * The render array. 
   */
  public function overview() {
    $build = [];

    // @TODO - do dev / prod here. (/react or /react-dev)
    // Ideally, make this configurable somehow. 
    $build['#attached']['library'][] = 'hnp_react_app/frontend';

    // Finally, drop your main mount point for React. 
    // This ID can be whatever you use in your app. 
    $build['#markup'] = '<div id="root"></div>';

    return $build;
  }
}
