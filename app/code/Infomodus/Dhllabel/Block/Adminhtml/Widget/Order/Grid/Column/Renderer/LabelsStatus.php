<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Dhllabel\Block\Adminhtml\Widget\Order\Grid\Column\Renderer;

/**
 * Class Status
 */
class LabelsStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var string[]
     */
    protected $labelStatuses;
    protected $labels;
    protected $_collectionFactory;
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $collectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Infomodus\Dhllabel\Model\Items $collectionFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->labelStatuses = $collectionFactory->getListStatuses();
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $ids = [];
            foreach ($dataSource['data']['items'] as $val) {
                $ids[] = $val['entity_id'];
            }
            $confData = $this->getData('config/labelOS');
            $this->labels = $this->_collectionFactory->getCollection();
            $redirect_path = $confData . '_list';
            switch ($confData) {
                case "order":
                    $label_search_id = 'order_id';
                    $labelType = null;
                    break;
                default:
                    $label_search_id = 'shipment_id';
                    $labelType = $confData;
                    break;
            }
            if ($confData === 'order') {
                $this->labels->addFieldToFilter('order_id', ['in' => $ids])->addGroup(['order_id', 'lstatus']);
                $this->labels->getSelect()->columns([new \Zend_Db_Expr('COUNT(order_id) AS corderid')]);
            } else {
                $this->labels->addFieldToFilter('shipment_id',
                    ['in' => $ids])->addFieldToFilter('type', $confData)->addGroup(['shipment_id', 'lstatus']);
                $this->labels->getSelect()->columns([new \Zend_Db_Expr('COUNT(shipment_id) AS corderid, price, currency')]);
            }

            if (count($this->labels)) {
                $labels = [];
                foreach ($this->labels as $val2) {
                    if ($confData === 'order') {
                        $labels[$val2->getOrderId()][$val2->getLstatus()] = $val2->getCorderid();
                        $labels[$val2->getOrderId()][2] = 0;
                        $labels[$val2->getOrderId()][3] = 0;
                    } else {
                        $labels[$val2->getShipmentId()][$val2->getLstatus()] = $val2->getCorderid();
                        $labels[$val2->getShipmentId()][2] = $val2->getPrice();
                        $labels[$val2->getShipmentId()][3] = $val2->getCurrency();
                    }
                }
                foreach ($dataSource['data']['items'] as & $item) {
                    if (isset($labels[$item['entity_id']])) {
                        $item[$this->getData('name')] = '';
                        $html = [];
                        if (isset($labels[$item['entity_id']][0]) && $labels[$item['entity_id']][0] > 0) {
                            if (!empty($labels[$item['entity_id']][2]) && $labels[$item['entity_id']][2] > 0) {
                                $html[] = __('Success') . '(' . $labels[$item['entity_id']][0] . ') ' .
                                    __('Price: ') . $labels[$item['entity_id']][2] . ' ' . $labels[$item['entity_id']][3];
                            } else {
                                $html[] = __('Success') . '(' . $labels[$item['entity_id']][0] . ')';
                            }
                        }
                        if (isset($labels[$item['entity_id']][1]) && $labels[$item['entity_id']][1] > 0) {
                            $html[] = __('Error') . '(' . $labels[$item['entity_id']][1] . ')';
                        }
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href' => $this->urlBuilder->getUrl('infomodus_dhllabel/items/show',
                                    [$label_search_id => $item['entity_id'], 'type' => $labelType,
                                        'redirect_path' => $redirect_path]),
                                'label' => implode(" ", $html),
                            ],
                        ];
                    }
                }
            }
        }
        return $dataSource;
    }
}
