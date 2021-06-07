<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Dhllabel\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Customer address controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Pdf extends \Magento\Framework\App\Action\Action
{
    protected $_handy;
    protected $_pdf;
    protected $fileFactory;
    protected $_customerSession;
    protected $_conf;
    protected $urlInterfaceFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Infomodus\Dhllabel\Helper\Handy $handy
     * @param \Infomodus\Dhllabel\Helper\Pdf $pdf
     * @param \Infomodus\Dhllabel\Helper\Config $config
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\UrlInterfaceFactory $urlInterfaceFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Infomodus\Dhllabel\Helper\Handy $handy,
        \Infomodus\Dhllabel\Helper\Pdf $pdf,
        \Infomodus\Dhllabel\Helper\Config $config,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\UrlInterfaceFactory $urlInterfaceFactory
    )
    {
        $this->_handy = $handy;
        $this->_pdf = $pdf;
        $this->_conf = $config;
        $this->fileFactory = $fileFactory;
        $this->_customerSession = $customerSession;
        $this->urlInterfaceFactory = $urlInterfaceFactory;
        parent::__construct($context);
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
