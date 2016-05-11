<?php
/**
 * paydunya.php
 *
 * Copyright (c) 2016 PAYDUNYA
 *
 * LICENSE:
 *
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 *
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 *
 * @copyright 2016 PAYDUNYA
 * @license   http://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://paydunya.com
 */

class Paydunya extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'paydunya';
        $this->tab = 'payments_gateways';
        $this->version = '0.1';
        $this->author = 'PAYDUNYA';
        $this->bootstrap = true;
        
        parent::__construct();
        
        $this->displayName = $this->l('PAYDUNYA');
        $this->description = $this->l('Acceptez des paiements via Orange Money de manière simple, rapide et sécurisée.');
    }

    public function install()
    {
        if (!parent::install() 
            or !$this->registerHook('displayPayment') 
            or !$this->registerHook('displayPaymentReturn') 
            or !Configuration::updateValue('PAYDUNYA_PAYNOW_TEXT', 'Payer avec Orange Money via PAYDUNYA')
            or !Configuration::updateValue('PAYDUNYA_PAYNOW_DESCRIPTION', 'PAYDUNYA est la passerelle de paiement la plus populaire pour les achats en ligne au Sénégal.')
            or !Configuration::updateValue('PAYDUNYA_MASTER_KEY', '')
            or !Configuration::updateValue('PAYDUNYA_TEST_PRIVATE_KEY', '')
            or !Configuration::updateValue('PAYDUNYA_TEST_TOKEN', '')
            or !Configuration::updateValue('PAYDUNYA_LIVE_PRIVATE_KEY', '')
            or !Configuration::updateValue('PAYDUNYA_LIVE_TOKEN', '')
            or !Configuration::updateValue('PAYDUNYA_MODE', 'test')
            or !Configuration::updateValue('PAYDUNYA_SUCCESS_MESSAGE', 'Félicitations, votre commande a été effectuée avec succès.')
            or !Configuration::updateValue('PAYDUNYA_ERROR_MESSAGE', 'La transaction a échoué.')){
            return false;
        }

        if (!$this->installOrderState()) {
            return false;
        }
        
        return true;
    }

    // Retrocompatibility 1.4/1.5

    public function uninstall()
    {
        unlink(dirname(__FILE__) . '/../../cache/class_index.php');
        return (parent::uninstall()
            and Configuration::deleteByName('PAYDUNYA_PAYNOW_TEXT')
            and Configuration::deleteByName('PAYDUNYA_PAYNOW_DESCRIPTION')
            and Configuration::deleteByName('PAYDUNYA_MASTER_KEY')
            and Configuration::deleteByName('PAYDUNYA_TEST_PRIVATE_KEY')
            and Configuration::deleteByName('PAYDUNYA_TEST_TOKEN')
            and Configuration::deleteByName('PAYDUNYA_LIVE_PRIVATE_KEY')
            and Configuration::deleteByName('PAYDUNYA_LIVE_TOKEN')
            and Configuration::deleteByName('PAYDUNYA_MODE')
            and Configuration::deleteByName('PAYDUNYA_SUCCESS_MESSAGE')
            and Configuration::deleteByName('PAYDUNYA_ERROR_MESSAGE')
        );
    }

    public function installOrderState()
    {
        if (Configuration::get('PS_OS_PAYDUNYA_PAYMENT') < 1)
        {
            $order_state = new OrderState();
            $order_state->send_email = false;
            $order_state->module_name = $this->name;
            $order_state->invoice = false;
            $order_state->color = '#98c3ff';
            $order_state->logable = true;
            $order_state->shipped = false;
            $order_state->unremovable = false;
            $order_state->delivery = false;
            $order_state->hidden = false;
            $order_state->paid = false;
            $order_state->deleted = false;
            $order_state->name = array((int)Configuration::get('PS_LANG_DEFAULT') => pSQL($this->l('PAYDUNYA - En attente de paiement')));

            if ($order_state->add())
            {
                // We save the order State ID in Configuration database
                Configuration::updateValue('PS_OS_PAYDUNYA_PAYMENT', $order_state->id);

                // We copy the module logo in order state logo directory
                copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/os/'.$order_state->id.'.gif');
                copy(dirname(__FILE__).'/logo.gif', dirname(__FILE__).'/../../img/tmp/order_state_mini_'.$order_state->id.'.gif');
            }
            else {
                return false;
            }
        }
        return true;
    }

    public function hookDisplayPayment($params)
    {   
        $controller = $this->getHookController('displayPayment');
        return $controller->run($params);
    }

    public function hookDisplayPaymentReturn($params)
    {
        $controller = $this->getHookController('displayPaymentReturn');
        return $controller->run($params);
    }

    public function getContent()
    {
        $controller = $this->getHookController('getContent');
        return $controller->run();
    }

    public function getHookController($hook_name)
    {
        // Include the controller file
        require_once(dirname(__FILE__).'/controllers/hook/'. $hook_name.'.php');

        // Build dynamically the controller name
        $controller_name = $this->name.$hook_name.'Controller';

        // Instantiate controller
        $controller = new $controller_name($this, __FILE__, $this->_path);

        // Return the controller
        return $controller;
    }
}