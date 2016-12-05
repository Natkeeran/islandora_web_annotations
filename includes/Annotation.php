<?php

/**
 * @file
 * Annotation implementation based on Web Annotation Protocol
 */

require_once('AnnotationConstants.php');
require_once('AnnotationUtil.php');
require_once('interfaceAnnotation.php');


class Annotation implements interfaceAnnotation
{
    var $repository;

    function __construct($i_repository) {
        if($i_repository != null){
            $this->repository = $i_repository;
        } else {
            $connection = islandora_get_tuque_connection();
            $this->repository = $connection->repository;
        }
    }

    public function createAnnotation($annotationContainerID, $annotationData, $annotationMetadata){

        try {
            $object = $this->repository->constructObject("islandora");

            $target = $annotationData["context"];
            $targetPID = substr($target, strrpos($target, '/') + 1);

            $object->label = "Annotation for " . $targetPID;
            $object->models = array(AnnotationConstants::WADM_CONTENT_MODEL);
            $object->relationships->add(FEDORA_RELS_EXT_URI, 'isMemberOfCollection', AnnotationConstants::WADM_CONTENT_MODEL);
            $object->relationships->add(FEDORA_RELS_EXT_URI, 'isMemberOfAnnotationContainer', $annotationContainerID);
            $dsid = AnnotationConstants::WADM_DSID;

            $annotationJsonLDData = $this->getAnnotationJsonLD("create", $annotationData, $annotationMetadata);
            $annotationJsonLD = json_encode($annotationJsonLDData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


            $ds = $object->constructDatastream($dsid, 'M');
            $ds->label = $dsid;
            $ds->mimetype = AnnotationConstants::ANNOTATION_MIMETYPE;
            $ds->setContentFromString($annotationJsonLD);
            $object->ingestDatastream($ds);
            $this->repository->ingestObject($object);
            $annotationPID =  $object->id;

            // Create Derivative
            $contentXML = $this->generateDerivativeContent($annotationData, $annotationMetadata);
            $this->createUpdateDerivative($object, $contentXML);

            // Update JsonLD with PID info
            $annotationJsonLDData["pid"] = $annotationPID;
            $annotationJsonLD = json_encode($annotationJsonLDData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            watchdog(AnnotationConstants::MODULE_NAME , 'Annotation : createAnnotation: Added new annotation @annotationPID', array("@annotationPID" => $annotationPID), WATCHDOG_INFO);
        } catch (Exception $e) {
            watchdog(AnnotationConstants::MODULE_NAME, 'Error adding annotation object: %t', array('%t' => $e->getMessage()), WATCHDOG_ERROR);
            throw $e;
       }

        return array($annotationPID, $annotationJsonLD);
    }

    public function updateAnnotation($annotationData, $annotationMetadata){
        $annotationPID = $annotationData["pid"];

        $annotationJsonLDData = $this->getAnnotationJsonLD("update", $annotationData, $annotationMetadata);
        $updatedContent = json_encode($annotationJsonLDData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);


        $object = $this->repository->getObject($annotationPID);
        $WADMObject = $object->getDatastream(AnnotationConstants::WADM_DSID);
        $WADMObject->content = $updatedContent;


        // Update Derivative
        $contentXML = $this->generateDerivativeContent($annotationData, $annotationMetadata);
        $this->createUpdateDerivative($object, $contentXML);

        return $updatedContent;
    }

    public function deleteAnnotation($annotationID){
        $this->repository->purgeObject($annotationID);
        watchdog(AnnotationConstants::MODULE_NAME, 'Annotation: deleteAnnotation: Annotation with id @deleteAnnotation was deleted from repoistory.', array('@annotationID'=>$annotationID), WATCHDOG_INFO);
    }

    private function getAnnotationJsonLD($actionType, $annotationData, $annotationMetadata)
    {
        $target = $annotationData["context"];
        $data = array(
            "@context" => array(AnnotationConstants::ONTOLOGY_CONTEXT_ANNOTATION),
            "@id" => AnnotationUtil::generateUUID(),
            "@type" => AnnotationConstants::ANNOTATION_CLASS_1,
            "body" => (object) $annotationData,
            "target" => $target,
            "pid" => "New"

        );

        if($actionType == "create") {
            $now = date("Y-m-d H:i:s");
            $metadata = array('creator' => $annotationMetadata["creator"], 'created' => $now);
        }
        if($actionType == "update"){
            $now = date("Y-m-d H:i:s");

            $metadata = array('creator' => $annotationMetadata["creator"], 'created' => $annotationMetadata["created"], 'modifiedBy' => $annotationMetadata["author"], 'modified' => $now);
        }

        $data = array_merge($data, $metadata);
        return $data;
    }

    function createUpdateDerivative(AbstractObject $object, $contentXML){
        try {

            $dsid = 'WADM_SEARCH';
            $datastream = isset($object[$dsid]) ? $object[$dsid] : $object->constructDatastream($dsid);
            $datastream->label = 'WADM_SEARCH';
            $datastream->mimeType = 'text/xml';

            $filename = $dsid . '.xml';
            $dest = file_build_uri($filename);

            $file = file_save_data($contentXML, $dest, FILE_EXISTS_REPLACE);


            AnnotationUtil::add_datastream($object, $dsid, $file->uri);


            file_delete($file);

        } catch (exception $e) {
            watchdog(AnnotationConstants::MODULE_NAME, 'Unable to create vtt indexing datastream: ' . $e->getmessage());
        }
    }

    function generateDerivativeContent($annotationData, $annotationMetadata){
        $target = $annotationData["context"];
        $pos = strrpos($target, '/');
        $targetID = $pos === false ? $target : substr($target, $pos + 1);

        $textvalue = $annotationData["text"];
        $creator = $annotationMetadata["creator"];

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><annotation></annotation>');

        $xml->addChild('title', "Annotation for " . $targetID);
        $xml->addChild('target', $target);
        $xml->addChild('creator', $creator);
        $xml->addChild('textvalue', $textvalue);

        $contentXML = $xml->asXML();

        return $contentXML;
    }

}
