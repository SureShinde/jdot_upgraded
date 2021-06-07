<?php

namespace Aalogics\Lcs\Ui\Component\MassAction;

class ChangeCostCenter implements \Zend\Stdlib\JsonSerializable
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var string
     */
    protected $urlPath;

     /**
     * @var \Aalogics\Lcs\Helper\Data
     */
    protected $_helper;

    /**
     * @var string
     */
    protected $paramName;

    /**
     * @var array
     */
    protected $additionalData = [];

    /**
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */

    protected $serialize;

    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Aalogics\Lcs\Helper\Data $lcsHelper,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        array $data = []
    ) {
        $this->data = $data;
        $this->_lcsHelper = $lcsHelper;
        $this->serialize = $serialize;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            
            $costcenters_data = $this->_lcsHelper->getAdminField('lcs_cod/costcenter_code');

            $costcenters = $this->serialize->unserialize($costcenters_data);

            $centers = array ();
            foreach ( $costcenters as $i => $costcenter ) {
                // if (! $costcenter ['delete']) {
                    $centers [$i] ['value'] = $costcenter ['code'];
                    $centers [$i] ['label'] = $costcenter ['name'];
                // }
            } 

            // $this->_lcsHelper->debug('coscenter value',$centers);

            $this->prepareData();

            foreach ($centers as $option) {
                $this->options[$option['value']] = [
                    'type' => 'visibility_status' . $option['value'],
                    'label' => $option['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$option['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $option['value']]
                    );
                }

                $this->options[$option['value']] = array_merge_recursive(
                    $this->options[$option['value']],
                    $this->additionalData
                );
            }

            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}