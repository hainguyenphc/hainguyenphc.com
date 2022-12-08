<?php

namespace Drupal\surgery\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InstalledModulesPage extends ControllerBase {
    /**
     * @var ModuleExtensionList $moduleExtensionList
     */
    protected $moduleExtensionList;

    /**
     * @var array
     */
    protected $installedInfo;

    /**
     * Constructor
     *
     * @param ModuleExtensionList $moduleExtensionList
     */
    public function __construct(ModuleExtensionList $moduleExtensionList) {
        $this->moduleExtensionList = $moduleExtensionList;    
        $this->installedInfo = $this->moduleExtensionList->getAllInstalledInfo();
    }

    /**
     * {@inheritDoc}
     */
    public static function create(ContainerInterface $container) {
        return new static (
            $container->get('extension.list.module'),
        );
    }

    /**
     * Builds the /surgery/installed-modules page.
     *
     * @return array
     */
    public function buildPage(): array {
        $header = [
            'name' => $this->t('Name'),
            'machine_name' => $this->t('Machine name'),
            'package' => $this->t('Package'),
            'version' => $this->t('Version'),
            'configure' => $this->t('Configure'),
            'path' => $this->t('Path'),
        ];
        $rows = [];
        foreach ($this->installedInfo as $key => $each) {
            $row = [];
            $row[] = $each['name'] ?? '';
            $row[] = $key;
            $row[] = $each['package'] ?? '';
            $row[] = $each['version'] ?? '';
            $configure_url = $each['configure'] ?? '';
            if (!empty($configure_url)) {
                $configure_url = Url::fromRoute($configure_url, [], ['absolute' => TRUE]);
                // $configure_url_str = $configure_url->toString();
                $configure_url = Link::fromTextAndUrl($this->t('Configure'), $configure_url);
                $row[] = $configure_url;
            } else {
                $row[] = $this->t('N/A');
            }
            $path = $this->moduleExtensionList->getPath($key);
            $config_install_path = "$path/config/install";
            $config_files = scandir($config_install_path);
            $config_files_names = [];
            if (is_array($config_files) || !empty($config_files)) {
                foreach ($config_files as $config_file) {
                    if ($config_file !== '.' && $config_file !== '..') {
                        $config_files_names[] = $config_file;
                    }
                }
                $row[] = new FormattableMarkup(Markup::create(implode('<br/>', $config_files_names)), []);
            } else {
                $row[] = $this->t('N/A');
            }
            $rows[$key] = $row;
        }
        $build = [
            '#type' => 'table',
            '#header' => $header,
            '#rows' => $rows,
        ];
        return $build;
    }
}
