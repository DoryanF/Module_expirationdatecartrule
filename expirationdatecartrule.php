<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

// require_once 'classes/CartRule.php';

class ExpirationDateCartRule extends Module
{

    public function __construct()
    {
        $this->name = 'expirationdatecartrule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Doryan Fourrichon';
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];

        parent::__construct();
        $this->bootstrap = true;

        $this->displayName = $this->l('Expiration Date Cart Rule');
        $this->description = $this->l('Module pour supprimer les codes promos périmés');
        $this->confirmUninstall = $this->l('Do you want to delete this module');
        
        

    }

    public function install()
    {
        if(!parent::install() ||
        !Configuration::updateValue('DATE_EXPIRATION','')
        )
        {
            return false;
        }

            return true;
    }

    public function uninstall()
    {
        if(!parent::uninstall() ||
        !Configuration::deleteByName('DATE_EXPIRATION')
        )
        {
            return false;
        }

            return true;
    }

    public function getContent()
    {

        return $this->postProcess().$this->renderForm();
    }

    public function renderForm()
    {
        $field_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings Language'),
            ],
            'input' => [
                [
                    'type' => 'date',
                    'name' => 'DATE_EXPIRATION',
                    'label' => $this->l('Ajout date'),
                    'required' => true,
                ]
            ],
            'submit' => [
                'name' => 'send',
                'class' => 'btn btn-primary',
                'title' => $this->l('Validate')
            ]

        ];

        $helper = new HelperForm();
        $helper->module  = $this;
        $helper->name_controller = $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['DATE_EXPIRATION'] = Configuration::get('DATE_EXPIRATION');

        return $helper->generateForm($field_form);
    }

    public function postProcess()
    {

        
        if(Tools::isSubmit('send'))
        {
            if(!Tools::isEmpty(Tools::getValue('DATE_EXPIRATION')))
            {
                if(Validate::isDate(Tools::getValue('DATE_EXPIRATION')))
                {
                    Configuration::updateValue('DATE_EXPIRATION',Tools::getValue('DATE_EXPIRATION'));
                    return $this->displayConfirmation('The date is in the correct format');
                }
                else
                {
                    return $this->displayError('The length is not correct');
                }
            }
            else
            {
                return $this->displayError('The field Date is empty !');
            }
            
        }

        $cartrules = CartRule::getAllCustomerCartRules(0);

        foreach($cartrules as $cart)
        {
            $cartRule = new CartRule($cart['id_cart_rule']);

            if ($cartRule->date_to < Configuration::get('DATE_EXPIRATION')) {
                $cartRule->delete();
            }
        }
    }
}