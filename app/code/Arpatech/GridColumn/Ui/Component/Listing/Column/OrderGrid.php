<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Arpatech\GridColumn\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use  Magento\Ui\Component\Listing\Columns\Column;


class OrderGrid extends Column
{
    private $_order;

    private $_helper = null;

    /**
     * Constructor
     *
     * @param ContextInterface               $context
     * @param UiComponentFactory             $uiComponentFactory
     * @param \Magento\Sales\Model\Order     $order
     * @param \WeltPixel\Maxmind\Helper\Data $helper
     * @param array                          $components
     * @param array                          $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        \Magento\Sales\Model\OrderFactory $orderFactory,

        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->_orderFactory = $orderFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }
        return $dataSource;
    }
    protected function prepareItem(array $item){

        $telephone = '';
        $entityId = array_key_exists('entity_id', $item) ? $item['entity_id'] : null;
        if($entityId) {
            $order = $this->_orderFactory->create()->load($entityId);
            //$order = $this->_order->load($entityId);
            if($order->getId()) {
                $address = $order->getBillingAddress();
		if(is_object($address))
                	$telephone = $address->getTelephone();
		
                return $telephone;
            }
        }
        return $telephone;
    }
}
