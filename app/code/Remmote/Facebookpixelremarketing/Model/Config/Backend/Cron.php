<?php
/**
 * @extension   Remmote_Facebookpixelremarketing
 * @author      Remmote
 * @copyright   2017 - Remmote.com
 * @descripion  Cron
 */
namespace Remmote\Facebookpixelremarketing\Model\Config\Backend;

class Cron extends \Magento\Framework\App\Config\Value
{
    const CRON_EXPRESSION_PATH = 'remmote/facebookpixelremarketing/schedule/cron_expr';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    public $configValueFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    /**
     * Save the cron expression in core_config table
     * @return [type]
     * @author Remmote
     * @date   2017-06-12
     */
    public function afterSave()
    {
        // Get values from backend
        $frequencyDays = $this->getFieldsetDataValue('frequency') ?:
            $this->_config->getValue('remmote_facebookpixelremarketing/scheduled_uploads/frequency');
        $timeValue     = $this->getValue();

        $timeValue  = explode(',', $timeValue);
        $hours      = (int) $timeValue[0];
        $minutes    = (int) $timeValue[1];

        //Prepare cron expression (Run every N days at X time)
        $cron_expression = $minutes . " " . $hours . " */" . $frequencyDays . " * *";

        //Save cron expression in data base
        try {
            $this->configValueFactory
                ->create()
                ->load(self::CRON_EXPRESSION_PATH, 'path')
                ->setValue($cron_expression)
                ->setPath(self::CRON_EXPRESSION_PATH)
                ->save();
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save new option.'));
        }

        return parent::afterSave();
    }
}