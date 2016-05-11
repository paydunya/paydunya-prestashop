<?php
/**
 * displayPayment.php
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


class PaydunyaDisplayPaymentController
{
    public function __construct($module, $file, $path)
    {
        $this->file = $file;
        $this->module = $module;
        $this->context = Context::getContext();
        $this->_path = $path;
    }

    public function run($params)
    {
        $this->context->smarty->assign(array(
            'paydunya_paynow_text'        => Configuration::get('PAYDUNYA_PAYNOW_TEXT'),
            'paydunya_paynow_description' => Configuration::get('PAYDUNYA_PAYNOW_DESCRIPTION')
        ));

        $this->context->controller->addCSS($this->_path.'views/css/paydunya.css', 'all');
        return $this->module->display($this->file, 'displayPayment.tpl');
    }
}