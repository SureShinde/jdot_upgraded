<?php

namespace RLTSquare\BulkDownloadConsignment\Controller\Adminhtml\Download;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use iio\libmergepdf\Merger;
use iio\libmergepdf\Pages;

/**
 * Class BulkDownloadConsignment
 * @package RLTSquare\BulkDownloadConsignment\Controller\Adminhtml\Download
 */
class BulkDownloadConsignment extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * BulkShipInvoice constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        FileFactory $fileFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        parent::__construct($context, $filter);
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->collectionFactory = $collectionFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    protected function massAction(AbstractCollection $collection)
    {
        $noShipment = array();
        $shipments =  0;
        $merger = new Merger;

        foreach ($collection->getItems() as $order) {
            $shipment = $order->getShipmentsCollection()->getFirstItem();
            $shipmentIncrementId = $shipment->getIncrementId();
            if ($shipmentIncrementId == null && count($shipment->getTracksCollection()) == 0) {
                $noShipment [] = $order->getIncrementId();
            }
            else{
                ++$shipments;
                switch ($shipment->getTracksCollection()->getFirstItem()->getCarrierCode()){
                    case "aalcs":
                        $url = $shipment->getTracksCollection()->getFirstItem()->getData('awb');
                        $merger->addRaw($this->get_content($url));
                        break;

                    case "callcourier":
                        $trackNumber = $shipment->getTracksCollection()->getFirstItem()->getData('track_number');
                        $merger->addRaw($this->get_content('http://cod.callcourier.com.pk/Booking/AfterSavePublic/'.$trackNumber));
                        break;

                    case "dhl":
                        $merger->addRaw($shipment->getShippingLabel());
                        break;

                }
            }
        }

        $message = '';
        if (count ( $noShipment ) > 0) {
            $message = __( "No consignment slip present for the following orders(s) : " );
            for($x = 0; $x < count ( $noShipment ); $x ++) {
                $message .= $noShipment[$x];
                if (! $x == (count ( $noShipment ) - 1))
                    $message .= ', ';
            }
        }

        if(!empty($message)) {
            $this->messageManager->addErrorMessage($message);
        }

        if(!$shipments){
            $this->_redirect ( 'sales/order/' );
        }

        $createdPdf = $merger->merge();



        return $this->fileFactory->create(
            sprintf('consignment%s.pdf', $this->dateTime->date('Y-m-d_H-i-s')),
            $createdPdf,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    function get_content($URL){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $URL);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
