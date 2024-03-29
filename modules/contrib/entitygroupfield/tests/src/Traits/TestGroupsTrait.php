<?php

namespace Drupal\Tests\entitygroupfield\Traits;

use Drupal\group\PermissionScopeInterface;

/**
 * Provides group types and group entities for use in test classes.
 *
 * This trait provides protected members to store 2 group types ('a' and 'b')
 * and then 2 groups of each type. Calling initializeTestGroups() will
 * initialize everything and populate all the protected member variables.
 */
trait TestGroupsTrait {

  /**
   * A dummy group type with ID 'a'.
   *
   * @var \Drupal\group\Entity\GroupTypeInterface
   */
  protected $groupTypeA;

  /**
   * A dummy group type with ID 'b'.
   *
   * @var \Drupal\group\Entity\GroupTypeInterface
   */
  protected $groupTypeB;

  /**
   * Test group A1, of type 'a'.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $groupA1;

  /**
   * Test group A2, of type 'a'.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $groupA2;

  /**
   * Test group A3, of type 'a'.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $groupA3;

  /**
   * Test group B1, of type 'b'.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $groupB1;

  /**
   * Test group B2, of type 'b'.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $groupB2;

  /**
   * Test group B3, of type 'b'.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $groupB3;

  /**
   * Test admin role for type 'a'.
   *
   * @var \Drupal\group\Entity\GroupRoleInterface
   */
  protected $adminRoleA;

  /**
   * Test admin role for type 'b'.
   *
   * @var \Drupal\group\Entity\GroupRoleInterface
   */
  protected $adminRoleB;

  /**
   * Initializes all the test group types and groups.
   */
  protected function initializeTestGroups() {
    // Create the group types.
    $this->groupTypeA = $this->createGroupType([
      'id' => 'a',
      'label' => 'Type A',
      'creator_membership' => FALSE,
    ]);
    $this->groupTypeB = $this->createGroupType([
      'id' => 'b',
      'label' => 'Type B',
      'creator_membership' => FALSE,
    ]);

    // Create the groups.
    $this->groupA1 = $this->createGroup(['label' => 'group-A1', 'type' => 'a']);
    $this->groupA2 = $this->createGroup(['label' => 'group-A2', 'type' => 'a']);
    $this->groupA3 = $this->createGroup(['label' => 'group-A3', 'type' => 'a']);
    $this->groupB1 = $this->createGroup(['label' => 'group-B1', 'type' => 'b']);
    $this->groupB2 = $this->createGroup(['label' => 'group-B2', 'type' => 'b']);
    $this->groupB3 = $this->createGroup(['label' => 'group-B3', 'type' => 'b']);
    $this->adminRoleA = $this->createGroupRole([
      'group_type' => $this->groupTypeA->id(),
      'scope' => PermissionScopeInterface::INDIVIDUAL_ID,
      'admin' => TRUE,
    ]);

    $this->adminRoleB = $this->createGroupRole([
      'group_type' => $this->groupTypeB->id(),
      'scope' => PermissionScopeInterface::INDIVIDUAL_ID,
      'admin' => TRUE,
    ]);
  }

}
