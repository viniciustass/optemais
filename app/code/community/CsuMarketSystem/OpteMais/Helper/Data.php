<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    CsuMarketSystem
 * @package     CsuMarketSystem_OpteMais
 * @copyright   Copyright (c) 2016 CSU MarketSystem [www.csu.com.br]
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsuMarketSystem_OpteMais_Helper_Data extends Mage_Core_Helper_Abstract
{

    const AUTH_REALM = 'CSU MarketSystem OPTe+ Api';

    const MESSAGE_SUCCESS = 'Requisição executada com sucesso';
    const STATUS_CODE_SUCCESS = 200;

    const MESSAGE_NOT_FOUND = 'Recurso não encontrado: %s';
    const STATUS_CODE_NOT_FOUND = 404;

    const MESSAGE_NOT_AUTHORIZED = 'Usuário inválido.';
    const STATUS_CODE_NOT_AUTHORIZED = 401;

    const MESSAGE_INVALID = 'Parâmetros inválidos: %s';
    const STATUS_CODE_INVALID = 405;

    const MESSAGE_ERROR = 'Erro: %s';
    const STATUS_CODE_ERROR = 500;

    public function isActive()
    {
        return Mage::getStoreConfigFlag('optemais/config/active');
    }

    /**
     * @param $cpf
     *
     * @return mixed|string
     */
    public function formatCpf($cpf)
    {
        $cpf = str_replace(array(',', '-', '.', ' '), '', $cpf);
        if (strlen($cpf) == 11) {
            $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
            $cpf = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        return $cpf;
    }

    /**
     * @param $cnpj
     *
     * @return mixed|string
     */
    public function formatCnpj($cnpj)
    {
        $cnpj = str_replace(array(',', '-', '.', ' '), '', $cnpj);
        $cnpj = substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) .
            '.' . substr($cnpj, 5, 3) . '/' .
            substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
        return $cnpj;
    }

    /**
     * @param $shippingMethod
     *
     * @return array
     */
    public function getShippingMethodData($shippingMethod)
    {
        $data = array();
        $carriers = Mage::getSingleton('shipping/config')->getAllCarriers();
        /** @var Mage_Shipping_Model_Carrier_Abstract $carriersModel */
        foreach ($carriers as $carCod => $carriersModel) {
            if (strpos($shippingMethod, $carCod) === false) {
                continue;
            }
            $data['carrier_title'] = $carriersModel->getConfigData('title');
            $data['carrier'] = $carriersModel->getCarrierCode();
            list($carrierCode, $methodCode) = explode($carriersModel->getCarrierCode() . '_', $shippingMethod);
            $data['method'] = $methodCode;
            foreach ($carriersModel->getAllowedMethods() as $metCod => $title) {
                if ($metCod == $methodCode) {
                    $data['method_title'] = $title;
                    break;
                }
            }
        }
        return $data;
    }

    public function getPriceWithInterest($value,$interest,$qtyInstallments) {
        $interest = $interest / 100;
        return round($value * (($interest * pow((1 + $interest), $qtyInstallments)) / (pow((1 + $interest), $qtyInstallments) - 1)), 2);
    }

}
