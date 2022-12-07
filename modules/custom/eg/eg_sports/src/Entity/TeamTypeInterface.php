<?php

namespace Drupal\eg_sports\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Team bundle interface.
 * A team bundle (team type) implements this interface.
 */
interface TeamTypeInterface extends ConfigEntityInterface {

  /**
   * @return boolean
   *  Determines if the team is a single-player team.
   */
  public function isSinglePlayer();

}
