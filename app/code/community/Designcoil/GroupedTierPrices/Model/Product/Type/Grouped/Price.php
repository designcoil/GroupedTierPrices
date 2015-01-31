<?php

class Designcoil_GroupedTierPrices_Model_Product_Type_Grouped_Price extends Mage_Catalog_Model_Product_Type_Price
{

    /**
     * Get product final price
     *
     * @param   double $qty
     * @param   Mage_Catalog_Model_Product $product
     * @return  double
     */
    public function getFinalPrice($qty = null, $product)
    {
        $finalPrice = parent::getFinalPrice($qty, $product);
        if (Mage::registry('current_product')) {
            return $finalPrice;
        }

        if ($product->getTierPriceCount() > 0) {
            $tierPrice = $this->_calcConfigProductTierPricing($product);
            if ($tierPrice < $finalPrice) {
                $finalPrice = $tierPrice;
            }
        }
        return $finalPrice;
    }

    /**
     * Get final price based on grouped products tier pricing structure.
     * Uses qty of parent item to determine price.
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  double
     */
    protected function _calcConfigProductTierPricing($product)
    {
        $tierPrice = PHP_INT_MAX;

        if ($items = $this->_getAllVisibleItems()) {
            $idQuantities = array();
            foreach ($items as $item) {
				$grouped_product_ids = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($item->getProductId());
                if (empty($grouped_product_ids)) {
                    continue;
                }

                $id = $grouped_product_ids[0]; // Parent product ID
                $idQuantities[$id][] = $item->getQty();
            }
			
			$grouped_product_id = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
            if (array_key_exists($grouped_product_id[0], $idQuantities)) {
                $totalQty = array_sum($idQuantities[$grouped_product_id[0]]);
                $tierPrice = parent::getFinalPrice($totalQty, $product);
            }
        }
        return $tierPrice;
    }

    protected function _getAllVisibleItems()
    {
        if (Mage::helper('designcoil_groupedtierprices')->isAdmin()) {
            return Mage::getSingleton('adminhtml/session_quote')->getQuote()->getAllVisibleItems();
        } else {
            return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
        }
    }

}
