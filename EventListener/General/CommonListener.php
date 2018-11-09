<?php

namespace SintraPimcoreBundle\EventListener\General;

use Pimcore\Model\DataObject\Concrete;
use SintraPimcoreBundle\EventListener\InterfaceListener;
use Pimcore\Model\DataObject\Fieldcollection;
use Pimcore\Model\DataObject\Fieldcollection\Data\ServerObjectInfo;
use SintraPimcoreBundle\Utils\EventListenerUtils;

class CommonListener extends ObjectListener implements InterfaceListener {

    /**
     * Get object's exportServers field collections
     * There will be a field collection for every server
     * in which the object must me syncronized.
     *
     * If the field collection for a specific server is missing for the object
     * it will be added so that all field collection are present.
     *
     * @param Concrete $dataObject the object to update
     */
    public function preAddAction($dataObject) {
        if (method_exists($dataObject, 'getExportServers')) {
            $exportServers = $dataObject->getExportServers() != null ? $dataObject->getExportServers() : new Fieldcollection();
            EventListenerUtils::insertMissingFieldCollections($exportServers);

            $dataObject->setExportServers($exportServers);
        }
    }

    /**
     * Implementation of preUpdate event.
     * 
     * @param Concrete $dataObject the object to update
     */
    public function preUpdateAction($dataObject) {
        $this->setIsPublishedBeforeSave($dataObject->isPublished());

        if (method_exists($dataObject, 'getExportServers')) {
            /**
             * Get object's exportServers field collections
             * There will be a field collection for every server
             * in which the object must me syncronized.
             *
             * If the field collection for a specific server is missing for the object
             * it will be added so that all field collection are present.
             */
            $exportServers = $dataObject->getExportServers() != null ? $dataObject->getExportServers() : new Fieldcollection();
            EventListenerUtils::insertMissingFieldCollections($exportServers);


            /**
             * Load the previous version of object in order to check
             * if fields to export are changed in respect to the new values
             */
            $classname = $dataObject->getClassName();
            $getByIdMethod = new \ReflectionMethod("\\Pimcore\\Model\\DataObject\\" . $classname, "getById");

            $oldDataObject = $getByIdMethod->invoke(null, $dataObject->getId(), true);

            /**
             * For each server field changes evaluation is done separately
             * If at least a field to export in the server has changed,
             * mark the object as "to sync" for that server.
             * 
             * If at least one required field for the server is empty
             * mark the object as not completed for that server
             */
            foreach ($exportServers as $exportServer) {
                $this->updateServerObjectInfo($exportServer, $dataObject, $oldDataObject);
            }

            $dataObject->setExportServers($exportServers);
        }
    }

    /**
     * @param Concrete $dataObject
     */
    public function postUpdateAction($dataObject) {
        
    }

    /**
     * @param Concrete $dataObject
     */
    public function postDeleteAction($dataObject, $isUnpublished = false) {
        
    }

    private function updateServerObjectInfo(ServerObjectInfo &$exportServer, Concrete $dataObject, Concrete $oldDataObject) {
        if ($exportServer->getExport() && ($oldDataObject == null || EventListenerUtils::checkServerUpdate($exportServer, $dataObject, $oldDataObject))) {
            $exportServer->setSync(false);
        }

        if ($exportServer->getExport() && EventListenerUtils::checkImagesChanged($exportServer, $dataObject)) {
            $exportServer->setImages_sync(false);
        }

        $complete = EventListenerUtils::checkObjectCompleted($exportServer, $dataObject);
        $exportServer->setComplete($complete);
    }

}
