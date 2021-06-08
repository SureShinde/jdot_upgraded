<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Export Catalog for Cron
 */
namespace Remmote\Facebookpixelremarketing\Cron;

class ExportCatalog
{
    /**
     * @var \Remmote\Facebookpixelremarketing\Model\Productcatalog
     */
    public $productCatalog;

    /**
     * @var \Remmote\Facebookpixelremarketing\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;
    
    public function __construct(
        \Remmote\Facebookpixelremarketing\Model\Productcatalog $productCatalog,
        \Remmote\Facebookpixelremarketing\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->productCatalog  = $productCatalog;
        $this->helper          = $helper;
        $this->storeManager    = $storeManager;
    }

    public function execute()
    {
        //Load websites
        $websites = $this->storeManager->getWebsites();
        if (count($websites) > 1) {
            foreach ($websites as $website) {
                //Check if extension is enable for website
                if ($this->helper->syncEnabled($website->getId())) {
                    //Call method that exports the product catalog
                    $this->productCatalog->exportCatalog($website->getId());
                }
            }
        } else {
            //Call method that exports the product catalog
            if ($this->helper->syncEnabled()) {
                $this->productCatalog->exportCatalog();
            }
        }
    }
}