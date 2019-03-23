<?php

class Nethuns_Nemoexpress_Model_Carrier_Source_Specialconditions
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::SERVICE_DOCUMENTS_RETURNS,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Documents Returns')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::SERVICE_SATURDAY_DELIVERY,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Saturday Delivery')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::SERVICE_MONDAY_DELIVERY,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Monday Delivery')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::SERVICE_OFFICE_DELIVERY,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Office Delivery')
            )
        );
    }
}
