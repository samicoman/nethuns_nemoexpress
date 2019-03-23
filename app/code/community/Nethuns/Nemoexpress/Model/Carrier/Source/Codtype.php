<?php

class Nethuns_Nemoexpress_Model_Carrier_Source_Codtype
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::TYPE_BANK_ACCOUNT,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Direct transfer to bank account')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::TYPE_CASH,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Cash delivered by the carrier')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::TYPE_CHEQUE,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Cheque delivered by the carrier')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::TYPE_TICKET,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Ticket delivered by the carrier')
            ),
            array(
                'value' => Nethuns_Nemoexpress_Model_Carrier_Nemoexpress::TYPE_TICKET_OR_CHEQUE,
                'label' => Mage::helper('nethuns_nemoexpress')->__('Ticket or cheque delivered by the carrier')
            ),
        );
    }
}
