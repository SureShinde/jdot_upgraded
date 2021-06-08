<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Export controller
 */
namespace Remmote\Facebookpixelremarketing\Controller\Adminhtml\Productcatalog;
 
class Export extends \Magento\Framework\App\Action\Action
{
    /**
     * Set permission to show and list notifications
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-18
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_AdminNotification::show_list');
    }

    /**
     * Export product catalog to media/facebook_productcatalog folder
     * Fields exported are the required fields to create standard dynamic ads in Facebook
     * @return [type]
     * @author Remmote.com
     * @date   2017-07-18
     */
    public function execute()
    {
        //Getting website id
        $websiteId      = (int) $this->getRequest()->getParam('websiteId', 0);
        
        //Instance product catalog model
        $productcatalog = $this->_objectManager->get('\Remmote\Facebookpixelremarketing\Model\Productcatalog');

        //Call method that exports the product catalog
        $productcatalog->exportCatalog($websiteId);
        
        //Add message to session
        $this->messageManager->addSuccess(__('Product catalog was succesfully generated.'));
    }

}