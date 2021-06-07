<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Dhllabel\Controller\Adminhtml\Boxes;

class NewAction extends \Infomodus\Dhllabel\Controller\Adminhtml\Boxes
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
