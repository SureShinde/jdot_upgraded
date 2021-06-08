<?php

namespace RLTSquare\TcsShipping\Ui\Component\MassAction;

/**
 * Class Listing
 * @package RLTSquare\TcsShipping\Ui\Component\MassAction
 */
class Listing extends \Magento\Ui\Component\MassAction
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;
    /**
     * @var \RLTSquare\TcsShipping\Helper\Data
     */
    protected $tcsHelper;

    /**
     * Listing constructor.
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param $components
     * @param array $data
     * @param \RLTSquare\TcsShipping\Helper\Data $tcsHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $components,
        array $data,
        \RLTSquare\TcsShipping\Helper\Data $tcsHelper
    ) {
        $this->authorization = $authorization;
        $this->tcsHelper = $tcsHelper;
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();
        $config = $this->getConfiguration();
        if (!$this->tcsHelper->isEnabled()) {
            $allowedActions = [];
            foreach ($config['actions'] as $action) {
                if ('tcs_ship' != $action['type'] && 'tcs_ship_invoice' != $action['type']) {
                    $allowedActions[] = $action;
                }
            }
            $config['actions'] = $allowedActions;
        }
        $this->setData('config', (array) $config);
    }
}
