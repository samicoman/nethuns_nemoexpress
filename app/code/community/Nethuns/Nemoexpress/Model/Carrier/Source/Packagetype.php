<?php

class Nethuns_Nemoexpress_Model_Carrier_Source_Packagetype
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PACKAGE,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Package')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_ENVELOPE,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Envelope')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::PACKAGE_TYPE_PALLET,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Pallet')
            )
        );
    }
}
