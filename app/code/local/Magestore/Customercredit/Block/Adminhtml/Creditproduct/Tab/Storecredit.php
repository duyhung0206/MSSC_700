<?php

class Magestore_Customercredit_Block_Adminhtml_Creditproduct_Tab_Storecredit extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected function _prepareForm()
    {
        $product = Mage::registry('current_product');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('storecredit_');
        $fieldset = $form->addFieldset('description_fieldset', array(
            'legend' => Mage::helper('customercredit')->__('Description')
        ));

        $fieldset->addField('hidden', 'hidden', array(
            'name' => 'hidden',
            'after_element_html' => '
                <script type="text/javascript">
                    //Add validate data
                    $("storecredit_value").className+=" validate-number validate-greater-than-zero";
                    $("storecredit_from").className+=" validate-number validate-credit-range";
                    $("storecredit_to").className+=" validate-zero-or-greater ";
                    $("storecredit_dropdown").className+=" validate-credit-dropdown ";

                    Event.observe(window, "load", function(){hidesettingSC();});
                    if ($("storecredit_type")) {
                        Event.observe($("storecredit_type"), "change", function(){
                            hidesettingSC();
                        });
                    }   
                    function hidesettingSC(){
                        $("credit_amount").disabled=true;
                        $("credit_value").disabled=true;
                        $("credit_amount").up("tr").hide();
                        $("credit_value").up("tr").hide();
                        
                        if($("storecredit_type").value == ' 
                            . Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_FIX . ')
                        {
                            $("storecredit_value").disabled=false;
                            $("storecredit_from").disabled=true;
                            $("storecredit_to").disabled=true;
                            $("storecredit_dropdown").disabled=true;
                            $("storecredit_value").up("tr").show();
                            $("storecredit_from").up("tr").hide();
                            $("storecredit_to").up("tr").hide();
                            $("storecredit_dropdown").up("tr").hide();
                                                
                        }
                        else if($("storecredit_type").value == ' 
                            . Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_RANGE . ')
                        {
                            $("storecredit_value").disabled=true;
                            $("storecredit_dropdown").disabled=true;
                            $("storecredit_from").disabled=false;
                            $("storecredit_to").disabled=false;
                            $("storecredit_dropdown").up("tr").hide();
                            $("storecredit_value").up("tr").hide();
                            $("storecredit_from").up("tr").show();
                            $("storecredit_to").up("tr").show();                                              
						}
                        else if($("storecredit_type").value == ' 
                            . Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_DROPDOWN . ')
                        {
                            $("storecredit_value").disabled=true;
                            $("storecredit_from").disabled=true;
                            $("storecredit_to").disabled=true;
                            $("storecredit_dropdown").disabled=false;
                            $("storecredit_value").up("tr").hide();
                            $("storecredit_from").up("tr").hide();
                            $("storecredit_to").up("tr").hide();
                            $("storecredit_dropdown").up("tr").show();
						}
                    }   
                    
                    error_range ="' 
            . Mage::helper("customercredit")->__("Minimum Credit value must be lower than maximum Credit value.") . '";
                    Validation.add("validate-credit-range", error_range, function(v) {
                       if(parseInt($("storecredit_from").value)>parseInt($("storecredit_to").value))
                       return false;
                       else return true;
                    });
                    error_dropdown ="' . Mage::helper("customercredit")->__("Input not correct") . '";
                    Validation.add("validate-credit-dropdown", error_dropdown, function(v) {
                       parten=/^(\d,{0,1})+$/;

                       return (parten.test($("storecredit_dropdown").value));
                    });
                    Validation.add("validate-credit-dropdown-price", error_dropdown, function(v) {
                        if($("storecredit_dropdown").value && $("storecredit_type").value == ' 
                            . Magestore_Customercredit_Model_Storecredittype::CREDIT_TYPE_DROPDOWN . ')
                        {
                            cnt_credit_dropdown=$("storecredit_dropdown").value.split(",").length-1;
                            if($("storecredit_price").value)
                            {
                                cnt_creditprice=$("storecredit_price").value.split(",").length-1;
                                if(cnt_credit_dropdown!==cnt_creditprice)
                                {
                                return false;
                                }
                                else return true;
                            }
                        } else {
                            return true;
                        }    
                    });
				  </script>',
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return Mage::helper('customercredit')->__('Store Credit');
    }

    public function getTabTitle()
    {
        return Mage::helper('customercredit')->__('Store Credit');
    }

    public function canShowTab()
    {
        if (Mage::registry('current_product')->getTypeId() == 'customercredit') {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        return true;
    }

}
