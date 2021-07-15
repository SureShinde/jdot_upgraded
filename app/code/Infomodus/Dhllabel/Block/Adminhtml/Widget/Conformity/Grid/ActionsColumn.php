<?php

/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 30.01.2016
 * Time: 23:04
 */
namespace Infomodus\Dhllabel\Block\Adminhtml\Widget\Conformity\Grid;

class ActionsColumn extends \Magento\Backend\Block\Widget\Grid\Column
{
    public function isDisplayed()
    {
        if (!$this->_authorization->isAllowed('Infomodus_Dhllabel::conformity_delete')) {
            return false;
        }
        return true;
    }
}
