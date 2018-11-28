<?php

namespace SintraPimcoreBundle\Import\Resolvers\Product;

use Pimcore\DataObject\Import\Resolver\AbstractResolver;
use Pimcore\Model\DataObject\Product;

/**
 * Resolve product by Sku
 * 
 * name_column_id: optional additional column Id used for product key generation
 *
 * @author Sintra Consulting
 */
class SkuResolver extends AbstractResolver{

    public function resolve(\stdClass $config, int $parentId, array $rowData){
        $params = json_decode($config->resolverSettings->params,true);
        $nameColumnId = $params["name_column_id"];
        
        $columnId = $this->getIdColumn($config);
        
        $sku = trim($rowData[$columnId]);
        $listing = new Product\Listing();
        $listing->setCondition("sku = ".$listing->quote($sku));
        $listing->setLimit(1);
        
        $products = $listing->load();
        
        if($products){
            $product = $products[0];
        }else{
            $product = new Product();
            $product->setParentId($parentId);
            $product->setSku($sku);
            $product->setPublished(0);
            
            /**
             * Set object key to avoid import error
             * If the "name_column_id" parameter is set, product key is generated
             * combining SKU and the value in the "name_column_id" column
             */
            if($nameColumnId != null && !empty($nameColumnId)){
                $key = trim($rowData[$nameColumnId]);
                $product->setKey(str_replace("/","\\",$sku." - ".$key));
            }else{
                $product->setKey(str_replace("/","\\",$sku));
            }
        }
        
        return $product;
        
    }

}
