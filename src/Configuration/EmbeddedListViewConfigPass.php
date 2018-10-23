<?php

namespace AlterPHP\EasyAdminExtensionBundle\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigPassInterface;

/**
 * Initializes the configuration for all the views of each object of type "%s", which is
 * needed when some object of type "%s" relies on the default configuration for some view.
 */
class EmbeddedListViewConfigPass implements ConfigPassInterface
{
    private $defaultOpenNewTab;

    public function __construct($defaultOpenNewTab)
    {
        $this->defaultOpenNewTab = $defaultOpenNewTab;
    }

    public function process(array $backendConfig)
    {
        $backendConfig = $this->processSortingConfig($backendConfig);
        $backendConfig = $this->processOpenNewTabConfig($backendConfig);

        return $backendConfig;
    }

    /**
     * @param array $backendConfig
     *
     * @return array
     */
    private function processOpenNewTabConfig(array $backendConfig)
    {
        foreach (array('entities', 'documents') as $objectTypeRootKey) {
            if (isset($backendConfig[$objectTypeRootKey]) && \is_array($backendConfig[$objectTypeRootKey])) {
                foreach ($backendConfig[$objectTypeRootKey] as $objectName => $objectConfig) {
                    if (!isset($objectConfig['embeddedList']['open_new_tab'])) {
                        $backendConfig[$objectTypeRootKey][$objectName]['embeddedList']['open_new_tab'] = $this->defaultOpenNewTab;
                    }
                }
            }
        }

        return $backendConfig;
    }

    /**
     * @param array $backendConfig
     *
     * @return array
     */
    private function processSortingConfig(array $backendConfig)
    {
        foreach (array('entities', 'documents') as $objectTypeRootKey) {
            if (isset($backendConfig[$objectTypeRootKey]) && \is_array($backendConfig[$objectTypeRootKey])) {
                foreach ($backendConfig[$objectTypeRootKey] as $objectName => $objectConfig) {
                    if (
                        !isset($objectConfig['embeddedList']['sort'])
                        && isset($objectConfig['list']['sort'])
                    ) {
                        $backendConfig[$objectTypeRootKey][$objectName]['embeddedList']['sort'] = $objectConfig['list']['sort'];
                    } elseif (isset($objectConfig['embeddedList']['sort'])) {
                        $sortConfig = $objectConfig['embeddedList']['sort'];
                        if (!\is_string($sortConfig) && !\is_array($sortConfig)) {
                            throw new \InvalidArgumentException(sprintf('The "sort" option of the "embeddedList" view of the "%s" object contains an invalid value (it can only be a string or an array).', $objectName));
                        }

                        if (\is_string($sortConfig)) {
                            $sortConfig = array('field' => $sortConfig, 'direction' => 'DESC');
                        } else {
                            $sortConfig = array('field' => $sortConfig[0], 'direction' => \strtoupper($sortConfig[1]));
                        }

                        $backendConfig[$objectTypeRootKey][$objectName]['embeddedList']['sort'] = $sortConfig;
                    }
                }
            }
        }

        return $backendConfig;
    }
}
