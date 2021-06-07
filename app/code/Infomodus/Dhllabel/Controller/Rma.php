<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Dhllabel\Controller;

use Magento\Email\Model\Template\SenderResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

/**
 * Customer address controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Rma extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $_dataProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_handy;
    protected $urlInterfaceFactory;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerModel;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_inlineTranslation;
    /**
     * @var SenderResolver
     */
    protected $senderResolver;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Infomodus\Dhllabel\Helper\Handy $handy
     * @param \Magento\Framework\UrlInterfaceFactory $urlInterfaceFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Infomodus\Dhllabel\Helper\Handy $handy,
        \Magento\Framework\UrlInterfaceFactory $urlInterfaceFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder, StateInterface $inlineTranslation,
        SenderResolver $senderResolver
    )
    {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_formFactory = $formFactory;
        $this->_dataProcessor = $dataProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_handy = $handy;
        $this->urlInterfaceFactory = $urlInterfaceFactory;
        parent::__construct($context);
        $this->_customerModel = $customerModel;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_inlineTranslation = $inlineTranslation;
        $this->senderResolver = $senderResolver;
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    /*public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }*/

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        /** @var \Magento\Framework\UrlInterface $urlBuilder */
        $urlBuilder = $this->urlInterfaceFactory->create();
        return $urlBuilder->getUrl($route, $params);
    }
}
