<?php
/**
 * NOTICE OF LICENSE
 * You may not sell, distribute, sub-license, rent, lease or lend complete or portion of software to anyone.
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @package   RLTSquare_TcsShipping
 * @copyright Copyright (c) 2018 RLTSquare (https://www.rltsquare.com)
 * @contacts  support@rltsquare.com
 * @license  See the LICENSE.md file in module root directory
 */

namespace RLTSquare\TcsShipping\Helper;

/**
 * Class Data
 * @package RLTSquare\TcsShipping\Helper
 * @author Umar Chaudhry <umarch@rltsquare.com>
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ENABLED = 'carriers/tcsshipping/active';
    const XML_PATH_DEBUG   = 'carriers/tcsshipping/debug';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->_logger                  = $context->getLogger();
        $this->_moduleList              = $moduleList;

        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDebugStatus()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getExtensionVersion()
    {
        $moduleCode = 'RLTSquare_TcsShipping';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    /**
     * @param $message
     * @param bool $useSeparator
     */
    public function log($message, $useSeparator = false)
    {
        if ($this->getDebugStatus() ) {
            if ($useSeparator) {
                $this->_logger->debug(str_repeat('=', 100));
            }

            $this->_logger->debug($message);
        }
    }
}
