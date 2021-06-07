<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Arpatech\GridColumn\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use  Magento\Ui\Component\Listing\Columns\Column;


class HighlightOrder extends Column
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
        \Magento\Sales\Model\Order $order,

        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->_order = $order;
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
    protected function prepareItem(array $item)
    {
        $comment = '-';
        $entityId = array_key_exists('entity_id', $item) ? $item['entity_id'] : null;
        $order = $this->_order->load($entityId);
        $_history = $order->getVisibleStatusHistory();
        if (count($_history)) {
            $counter = 0;
            foreach ($_history as $_historyItem) {
                if ($_historyItem->getOrderFrom() == "frontend")
                    $counter++;
            }
            if($counter > 0)
                return $comment = '<span class="comments-filters-active">'.$counter.'</span>';
        }
        return $comment;
    }
}