<?php

namespace IWD\OrderManager\Ui\Component\MassAction\Status;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use IWD\OrderManager\Model\Config\Source\Order\Statuses;

class Options implements JsonSerializable
{
    /**
     * @var Statuses
     */
    private $statuses;

    /**
     * @var array
     */
    private $options;

    /**
     * Additional options params
     *
     * @var array
     */
    private $data;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    private $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    private $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    private $additionalData = [];


    protected $authSession;

    /**
     * @param UrlInterface $urlBuilder
     * @param Statuses $statuses
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        UrlInterface $urlBuilder,
        Statuses $statuses,
        array $data = []
    ) {
        $this->authSession=$authSession;
        $this->data = $data;
        $this->statuses = $statuses;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {

        $applicableId = [
            '1',
            '4356'
        ];
        $userRoleId=$this->authSession->getUser()->getRole()->getId();
        if ($this->options === null) {
            $options = $this->statuses->toOptionArray();
            $this->prepareData();

            if (!in_array($userRoleId, $applicableId)) {


                unset($options[1]);
            }
            foreach ($options as $optionCode) {
                $value = $optionCode['value'];

                $this->options[$value] = [
                    'type' => $value,
                    'label' => $optionCode['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$value]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $value]
                    );
                }

                $this->options[$value] = array_merge_recursive(
                    $this->options[$value],
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
    private function prepareData()
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
