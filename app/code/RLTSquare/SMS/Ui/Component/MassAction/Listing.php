<?php
namespace RLTSquare\SMS\Ui\Component\MassAction;

class Listing extends \Magento\Ui\Component\MassAction
{
    private $authorization;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $components,
        array $data
    ) {
        $this->authorization = $authorization;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();
        $config = $this->getConfiguration();
        $isEnable = $this->scopeConfig->getValue(
            'general/api_credentials/isEnableDisable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($isEnable == 1) {
            $allowedActions = [];
            foreach ($config['actions'] as $action) {
                if ('bulk_sms_send' != $action['type']) {
                    $allowedActions[] = $action;
                }
            }
            $config['actions'] = $allowedActions;
        }
        $this->setData('config', (array) $config);
    }
}
