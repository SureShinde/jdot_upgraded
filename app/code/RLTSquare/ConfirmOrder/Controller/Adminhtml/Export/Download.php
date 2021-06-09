<?php

namespace RLTSquare\ConfirmOrder\Controller\Adminhtml\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Magento\Setup\Exception;

use Magento\Framework\App\Bootstrap;

//require __DIR__ . '/app/bootstrap.php';

class Download extends \Magento\Framework\App\Action\Action
{

    protected $csvWriter;

    protected $fileFactory;

    protected $directoryList;

    protected $_postFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvWriter,
        DateTime $dateTime,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \RLTSquare\ConfirmOrder\Model\PostFactory $postFactory
    )
    {
        $this->csvWriter = $csvWriter;
        $this->_postFactory = $postFactory;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;

        parent::__construct($context);
    }

    /**
     * Upload and save file
     *
     */
    public function execute()
    {


        $data[] = array(
            'Order Id',
            'Created At'
        );


        $post = $this->_postFactory->create();
        $collection = $post->getCollection();
        foreach ($collection as $item) {
            $data[] = array(
                $item->getOrderId(),
                $item->getCreatedAt()
            );
        }

        $fileDirectory = DirectoryList::VAR_DIR;
        $fileName = sprintf('Confirmed_Orders_%s.csv', $this->dateTime->date('Y-m-d_H-i-s'));
        $filePath = $this->directoryList->getPath($fileDirectory) . "/" . $fileName;

        $this->csvWriter
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath, $data);


        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
            DirectoryList::VAR_DIR,
            'text/csv'
        );
    }
}
