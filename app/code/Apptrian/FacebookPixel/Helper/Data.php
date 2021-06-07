<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 */
 
namespace Apptrian\FacebookPixel\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    public $moduleList;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->moduleList  = $moduleList;
        
        parent::__construct($context);
    }
    
    /**
     * Returns extension version.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        $moduleCode = 'Apptrian_FacebookPixel';
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }
}
