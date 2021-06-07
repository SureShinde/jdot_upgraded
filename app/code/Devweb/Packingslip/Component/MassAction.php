<?php

namespace Devweb\Packingslip\Component;


class MassAction extends \Magento\Ui\Component\MassAction
{
    protected $removeType = ['pdfshipments_order'];
    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $config = $this->getConfiguration();

        foreach ($this->getChildComponents() as $actionComponent) {
            $config['actions'][] = $actionComponent->getConfiguration();
        };

        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $newConfigActions = [];
        foreach ($config['actions'] as $configItem) {
            if(in_array($configItem['type'], $this->removeType)) {
                continue;
            }

            $newConfigActions[] = $configItem;
        }

        $config['actions'] = $newConfigActions;

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }
}