<?php
namespace Aalogics\Lcs\Ui\Component\MassAction;

class Listing extends \Magento\Ui\Component\MassAction
{
    private $authorization;
    protected $_lcsHelper;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        $components,
        array $data,
        \Aalogics\Lcs\Helper\Data $lcsHelper
    ) {
        $this->authorization = $authorization;
        $this->_lcsHelper = $lcsHelper;
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();
        $config = $this->getConfiguration();
        if (!$this->_lcsHelper->isEnabled() || !$this->_lcsHelper->isShippingEnabled() ) {
            $allowedActions = [];
            foreach ($config['actions'] as $action) {
                if ('lcs_ship' != $action['type'] && 'lcs_ship_invoice' != $action['type']) {
                    $allowedActions[] = $action;
                }
            }
            $config['actions'] = $allowedActions;
        }
        $this->setData('config', (array) $config);
    }
}
