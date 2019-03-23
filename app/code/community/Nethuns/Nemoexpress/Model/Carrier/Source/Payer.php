<?php

class Nethuns_Nemoexpress_Model_Carrier_Source_Payer
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::CLIENT,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Sender')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::RECIPIENT,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Recipient')
            )
        );
    }
}
