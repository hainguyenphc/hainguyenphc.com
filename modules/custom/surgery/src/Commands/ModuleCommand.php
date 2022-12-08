<?php

namespace Drupal\surgery\Commands;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandler;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class ModuleCommand extends DrushCommands {
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
     */
    public function checkIfModuleInstalled(string $module, array $options = ['is_machine_name' => TRUE]) {
        $installed = FALSE;
        if ($options['is_machine_name']) {
            $installed = $this->moduleHandler->moduleExists($module);
        } else {
            
            $info = $this->moduleExtensionList->getAllInstalledInfo();
            foreach ($info as $each) {
                if (strtolower($each['name']) == strtolower($module)) {
                    $installed = TRUE;
                }
            }
        }
        if ($installed) {
            $this->output()->writeln("<info>$module is installed.</info>");
        } else {
            $this->output()->writeln("<comment>$module is not installed.</comment>");
        }
    }
}
