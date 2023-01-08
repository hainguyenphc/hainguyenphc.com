<?php

namespace Drupal\eg_products\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /** 
   * The access() and createAccess() methods in Drupal\Core\Entity\EntityAccessControlHandler (B) 
   * invoke entity access hooks (H).
   * 
   * Our subclass Drupal\eg_products\Access\ProductAccessControlHandler override (B) with
   * its own access() and createAccess (O).
   * 
   * (B) invoke (H). If (H) do not come back with an access denied message, (B) then invoke (O).
   * 
   * Results of (B) invoking (H) are then combined with results of (B) invoking (O)
   * inside an `orIf()` access result.
   * 
   * If one of the hook impl grants access and none denies it, user has access unless our own access
   * handler denies it.
   * 
   * If all hook impl and our own access handler return neutral access, then ultimately, the user is denied.
   */

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view product entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit product entities');
      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete product entities');
    }
    
    return AccessResult::neutral();
  }

  /** 
   * {@inheritDoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add product entities');
  }

  /** 
   * Inject services into access handler
   *  - implements Drupal\Core\Entity\EntityHandlerInterface
   *  - implements createInstance()
   *  - implements __construct()
   */

  /** 
   * {@inheritDoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($entity_type);
    $this->entityTypeManager = $entityTypeManager;
  }

  /** 
   * {@inheritDoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager'),
    );
  }

  public function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, ?FieldItemListInterface $items = NULL) {
    
  }

}
