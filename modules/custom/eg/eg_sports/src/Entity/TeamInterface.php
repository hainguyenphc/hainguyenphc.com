<?php

namespace Drupal\eg_sports\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

interface TeamInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the team name.
   */
  public function getTeamName();

  /**
   * Sets the team name.
   */
  public function setTeamName($name);

}
