<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SintraPimcoreBundle\ApiManager;

use Pimcore\Analytics\Piwik\Api\Exception\ApiException;
use \SpringImport\Swagger\Magento2\Client\ApiException as SwaggerApiException;
use Pimcore\Tool\RestClient\Exception;
use SpringImport\Swagger\Magento2\Client\Api\CatalogCategoryRepositoryV1Api;
use SpringImport\Swagger\Magento2\Client\Model\Body30;

//include_once 'vendor/springimport/swagger-magento2-client/lib/Api/CatalogCategoryRepositoryV1Api.php'
//include_once 'vendor/springimport/swagger-magento2-client/lib/Model/Body30.php';
//include_once 'AbstractAPIManager.php';

/**
 * Magento Rest Category API Manager 
 *
 * @author Marco Guiducci
 */
class CategoryAPIManager extends AbstractAPIManager {
    
    private static $instance;

    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createEntity($entity) {
        
        $apiClient = $this->getMagento2ApiInstance();
        
        $categoryInstance = new CatalogCategoryRepositoryV1Api($apiClient);
        
        try {
            $category = array("category" => $entity);
            $categoryBody = new Body30($category);
            $result = $categoryInstance->catalogCategoryRepositoryV1SavePost($categoryBody);
            return $result;
        } catch (SwaggerApiException $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function deleteEntity($categoryId) {
        $apiClient = $this->getMagento2ApiInstance();
        
        $categoryInstance = new CatalogCategoryRepositoryV1Api($apiClient);
        
        try {
            $result = $categoryInstance->catalogCategoryRepositoryV1DeleteByIdentifierDelete($categoryId);
            return $result;
        } catch (SwaggerApiException $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function getEntityByKey($categoryId) {
        return $this->getEntity($categoryId);
    }
    
    public function getEntity($categoryId, $storeId = null) {
        $apiClient = $this->getMagento2ApiInstance();
        
        $categoryInstance = new CatalogCategoryRepositoryV1Api($apiClient);
        
        try {
            $result = $categoryInstance->catalogCategoryRepositoryV1GetGet($categoryId, $storeId);
            return $result;
        } catch (SwaggerApiException $e) {
            echo $e->getMessage();
            return null;
        }
    }

    public function updateEntity($categoryId, $entity) {
        $apiClient = $this->getMagento2ApiInstance();
        
        $categoryInstance = new CatalogCategoryRepositoryV1Api($apiClient);
        
        try {
            $category = array("category" => $entity);
            $categoryBody = new Body30($category);
            
            $result = $categoryInstance->catalogCategoryRepositoryV1SavePut($categoryId, $categoryBody);
            return $result;
        } catch (SwaggerApiException $e) {
            echo $e->getMessage();
            return null;
        }
    }

}
