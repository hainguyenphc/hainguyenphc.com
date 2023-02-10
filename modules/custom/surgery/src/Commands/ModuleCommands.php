<?php

namespace Drupal\surgery\Commands;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\surgery\Constants\Constants as K;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class ModuleCommands extends DrushCommands {
    /**
     * @var ModuleHandler $moduleHandler
     */
    protected $moduleHandler;

    /**
     * @var ModuleExtensionList $moduleExtensionList
     */
    protected $moduleExtensionList;

    /**
     * Constructor
     *
     * @param ModuleHandler $moduleHandler
     */
    public function __construct(ModuleHandler $moduleHandler, ModuleExtensionList $moduleExtensionList) {
        $this->moduleHandler = $moduleHandler;
        $this->moduleExtensionList = $moduleExtensionList;
    }

    /**
     * Checks if a module is installed.
     * 
     * @command     surgery:check-if-module-installed
     * @aliases     surgery:cimi
     * @param       string $module
     * @options     $options
     * @option      bool $is_machine_name
     * @usage       surgery:cimi
     *   Checks if a module is installed.
     * 
     * Examples:
     *  - ddev drush surgery:cimi ban
     *  - ddev drush surgery:check-if-module-installed "Admin Toolbar" --is_machine_name=false
     */
    public function checkIfModuleInstalled(string $module, array $options = ['is_machine_name' => K::TRUE_STRING]) {
        $installed = FALSE;
        if (isset($options['is_machine_name']) && $options['is_machine_name'] === K::TRUE_STRING) {
            $installed = $this->moduleHandler->moduleExists($module);
        } else {
            $info = $this->moduleExtensionList->getAllInstalledInfo();
            foreach ($info as $each) {
                if (strtolower($each['name']) == strtolower($module)) $installed = TRUE;
            }
        }

        $this->output()->writeln($installed ? "<info>$module is installed.</info>" : "<comment>$module is not installed.</comment>");
    }
}
