<?php

namespace Drupal\surgery\Commands;

use Drupal\Core\Url;
use Drush\Commands\DrushCommands;
use Drupal\system\Entity\Menu;
use Symfony\Component\Console\Helper\Table;

/**
 * A Drush commandfile.
 */
class MenuCommands extends DrushCommands {

  /**
   * @command   surgery:get-all-menus
   * @aliases   surgery:gam
   * @usage     surgery:gam
   *    Gets all menus.
   */
  public function getAllMenus() {
    $rows = [];
    /** @var string $menu_name */
    /** @var Drupal\system\Entity\Menu $menu */
    foreach (Menu::loadMultiple() as $menu_name => $menu) {
      // $absolute_link = Url::fromRoute('entity.menu.edit_form', ['menu' => $menu_name], ['absolute' => TRUE, 'https' => TRUE])->toString();
      $absolute_link = $menu->toUrl('edit-form', ['absolute' => TRUE])->toString();
      $rows[] = [
        $menu->label(),
        "<info>{$absolute_link}</info>",
        $menu->status(),
      ];
    }
    // @see vendor/drush/drush/src/Drupal/Commands/config/ConfigImportCommands.php file.
    $table = new Table($this->output());
    $table->setHeaders([
      'Menu',
      'Link',
      'Status',
    ]);
    $table->setRows($rows);
    $table->render();
  }

}
