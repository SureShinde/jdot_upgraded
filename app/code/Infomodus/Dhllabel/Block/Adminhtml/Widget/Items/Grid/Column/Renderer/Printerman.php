<?php

namespace Infomodus\Dhllabel\Block\Adminhtml\Widget\Items\Grid\Column\Renderer;

class Printerman extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $_config;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Infomodus\Dhllabel\Helper\Config $config,
        array $data = []
    ) {
        $this->_config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function setColumn($column)
    {
        parent::setColumn($column);
        return $this;
    }

    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getLstatus() == 1) {
            return;
        }
        $path_url = $this->_config->getBaseUrl('media') . 'dhllabel/label/';
        $path_dir = $this->_config->getBaseDir('media') . '/dhllabel/label/';
        if ($row->getTypePrint() == "pdf") {
            $pdf = '<a href="' . $this->getUrl('infomodus_dhllabel/pdflabels/one',
                    ['label_name'=>$row->getLabelname()]) . '" target="_blank">PDF</a>';
        } else {
            if($this->_config->getStoreConfig('dhllabel/printing/automatic_printing')==1) {
                $pdf = '<a href="' . $this->getUrl('infomodus_dhllabel/items/autoprint',
                        ['label_id' => $row->getId(), 'order_id' => $row->getOrderId()]) . '" target="_blank">'
                    . __('Print Label') . '</a>';
            } else {
                $printersText = $this->_config->getStoreConfig('dhllabel/printing/printer_name');
                $printers = explode(",", $printersText);
                $pdf = '<a class="thermal-print-file" data-printer="'.(trim($printers[0])).'" href="' . $this->getUrl('infomodus_dhllabel/items/autoprint', ['label_id' => $row->getId(), 'order_id' => $row->getOrderId(), 'type_print' => 'manual']) . '">' . __('Print thermal') . '</a>';
            }
        }
        $invoice="";
        if(file_exists($path_dir.'invoice_'.$row->getTrackingnumber().'.pdf')){
            $invoiceType = "Commercial";
            if($row->getInvoiceType() != 'CMI'){
                $invoiceType = "Proforma";
            }
            $invoice = ' <br><br><a href="' . ($path_url.'invoice_'.$row->getTrackingnumber().'.pdf') . '" target="_blank">'.$invoiceType.' Invoice</a>';
        }
        return $pdf.$invoice;
    }
}
