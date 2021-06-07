<?php
/*
 * Author Rudyuk Vitalij Anatolievich
 * Email rvansp@gmail.com
 * Blog www.cervic.info
 */

namespace Infomodus\Dhllabel\Helper;
class Pdf extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_conf;
    protected $messageManager;
    protected $items;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Infomodus\Dhllabel\Helper\Config $conf,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Infomodus\Dhllabel\Model\ItemsFactory $items
    )
    {
        $this->_conf = $conf;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->items = $items;
    }

    public function createManyPDF($labels)
    {
        $pdf = new \Zend_Pdf();
        foreach ($labels as $label) {
            $pdf = $this->createPDF($label, $pdf);
        }
        return $this->fileFactory->create(
            'dhl_shipping_labels.pdf',
            $pdf->render(),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }

    public function createPDF($label_id, $pdf = null)
    {
        $img_path = $this->_conf->getBaseDir('media') . 'dhllabel/label/';
        if (is_string($label_id)) {
            $label = $this->items->create()->load($label_id);
        } else {
            $label = $label_id;
        }
        if ($label && file_exists($img_path . $label->getLabelname()) && filesize($img_path . $label->getLabelname()) > 256) {
            if ($label->getTypePrint() == "pdf") {
                $pdf2 = \Zend_Pdf::load($img_path . $label->getLabelname());
                foreach ($pdf2->pages as $k => $page) {
                    $template2 = clone $pdf2->pages[$k];
                    $page2 = new \Zend_Pdf_Page($template2);
                    $pdf->pages[] = $page2;
                }
            }
            $label->setRvaPrinted(1);
            $label->save();
        } else {
            $this->messageManager->addErrorMessage(__('To order a ' . $label->getOrderIncrementId() . ' Not Found label ' . $label->getLabelname()));
        }
        return $pdf;
    }
}