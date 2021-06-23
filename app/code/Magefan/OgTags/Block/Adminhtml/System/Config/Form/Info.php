<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
namespace Magefan\OgTags\Block\Adminhtml\System\Config\Form;


/**
 * Class Info
 * @package Magefan\OgTags\Block\Adminhtml\System\Config\Form
 */
class Info extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return extension url
     * @return string
     */
    protected function getModuleUrl()
    {
        return 'https://mage' . 'fan.com?utm_source=m2admin_og_config&utm_medium=link&utm_campaign=regular';
    }
    /**
     * Return extension title
     * @return string
     */
    protected function getModuleTitle()
    {
        return 'Open Graph Extension';
    }
}
