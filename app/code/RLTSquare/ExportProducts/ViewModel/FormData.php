<?php
namespace RLTSquare\ExportProducts\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Data\Form\FormKey;

class FormData implements ArgumentInterface
{
    private $formKey;

    public function __construct(
        FormKey $formKey
    )
    {
        $this->formKey = $formKey;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

}
