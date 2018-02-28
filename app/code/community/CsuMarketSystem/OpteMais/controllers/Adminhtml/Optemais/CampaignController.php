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
class CsuMarketSystem_OpteMais_Adminhtml_Optemais_CampaignController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/promo/optemais_campaign');
    }
    
    public function addItemsAction()
    {
        $products = $this->getRequest()->getParam('product');
        if (!is_array($products)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('optemais')->__('Selecione pelo menos um produto'));
            $this->_redirect('adminhtml/catalog_product/index');
            return false;
        } else {
            try {
                /** @var Mage_Catalog_Model_Resource_Product_Collection $productCollection */
                $productCollection = Mage::getModel('catalog/product')->getCollection();
                $productCollection->addIdFilter($products);
                $productCollection->addAttributeToFilter('type_id','simple');
                $count = $productCollection->getSize();
                if($count != count($products)) {
                    throw new Exception(Mage::helper('optemais')->__('Utilize somente [Produtos Simples] para Campanhas'));
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('adminhtml/catalog_product/index');
                return false;
            }
        }
        $this->loadLayout()
            ->_setActiveMenu('promo')
            ->_addBreadcrumb(Mage::helper('optemais')->__('Adicionar itens à Campanha OPTe+'), Mage::helper('optemais')->__('Adicionar itens à Campanha OPTe+'))
            ->_addContent($this->getLayout()->createBlock('optemais/adminhtml_campaign_item'))
            ->renderLayout();
    }

    public function confirmItemsAction()
    {
        /** @var CsuMarketSystem_OpteMais_Model_Campaign $campaign */
        $campaign = Mage::getModel('optemais/campaign');
        if ($this->getRequest()->getParam('action') == CsuMarketSystem_OpteMais_Model_Source_Campaign_Actions::ACTION_EXISTING) {
            try {
                $campaignId = $this->getRequest()->getParam('campaign');
                if($campaign->validateProducts($this->getRequest(),$campaignId)) {
                    $campaign->addItemToCampaign($this->getRequest(), $campaignId);
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('optemais')->__('Itens adicionados à campanha com sucesso'));
                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('optemais')->__('Alguns dos itens já existem nessa campanha'));
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        } else {
            try {
                $campaign->addCampaign($this->getRequest());
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('optemais')->__('Campanha criada com sucesso'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/catalog_product/index');
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo')
            ->_addBreadcrumb(Mage::helper('optemais')->__('Gerenciar Campanhas OPTe+'), Mage::helper('optemais')->__('Gerenciar Campanhas OPTe+'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('optemais/adminhtml_campaign'))
            ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('optemais/campaign')->load($id);
        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('optemais_campaign', $model);
            $this->loadLayout();
            $this->_setActiveMenu('promo');
            $this->_addBreadcrumb(Mage::helper('optemais')->__('Gerenciar Campanhas OPTe+'), Mage::helper('optemais')->__('Gerenciar Campanhas OPTe+'));
            $this->_addBreadcrumb(Mage::helper('optemais')->__('Nova Campanha'), Mage::helper('optemais')->__('Nova Campanha'));
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('optemais/adminhtml_campaign_edit'))
                ->_addLeft($this->getLayout()->createBlock('optemais/adminhtml_campaign_edit_tabs'));
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('optemais')->__('Campanha não encontrada'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('optemais/campaign');
            $model->setData($data)->setId($this->getRequest()->getParam('id'));
            try {
                $model->save();
                if ($this->getRequest()->getParam('id')) {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('optemais')->__('Campanha atualizada com sucesso'));
                } else {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('optemais')->__('Campanha criada com sucesso'));
                }
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('optemais')->__('Não foi possível encontrar a Campanha'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('optemais/campaign')->load($this->getRequest()->getParam('id'));
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('optemais')->__('Campanha excluída com sucesso'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $campaigns = $this->getRequest()->getParam('campaign');
        if (!is_array($campaigns)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('optemais')->__('Selecione pelo menos uma campanha'));
        } else {
            try {
                foreach ($campaigns as $campaignId) {
                    $model = Mage::getModel('optemais/campaign')->load($campaignId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('optemais')->__('Total de %d campanha(s) removida(s)', count($campaigns)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteItemAction()
    {
        $products = $this->getRequest()->getParam('product');
        if (!is_array($products)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('optemais')->__('Selecione pelo menos um produto'));
        } else {
            try {
                foreach ($products as $campaignItemId) {
                    $model = Mage::getModel('optemais/campaign_item')->load($campaignItemId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('optemais')->__('Total de %d produto(s) removido(s)', count($products)));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirectReferer();
    }

    public function massStatusAction()
    {
        $campaigns = $this->getRequest()->getParam('campaign');
        if (!is_array($campaigns)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('optemais')->__('Selecione pelo menos uma campanha'));
        } else {
            try {
                foreach ($campaigns as $campaignId) {
                    Mage::getSingleton('optemais/campaign')
                        ->load($campaignId)
                        ->setIsActive($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total de %d campanha(s) atualizada(s)', count($campaigns))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName = 'optemais_campanhas.csv';
        $content = $this->getLayout()->createBlock('optemais/adminhtml_campaign_grid')->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'optemais_campanhas.xml';
        $content = $this->getLayout()->createBlock('optemais/adminhtml_campaign_grid')->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function productGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('optemais/adminhtml_campaign_edit_tab_product_grid')
                ->toHtml()
        );
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

}