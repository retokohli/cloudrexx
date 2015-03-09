<?php

namespace Core_Modules\TemplateEditor\Controller;
use Cx\Core\Json\JsonAdapter;

/**
 * 
 */
class JSONTemplateEditor implements JsonAdapter {

    /**
     * @param array $params
     */
    public function updateOption($params) {
        // TODO implement here
    }

    /**
     * Returns the internal name used as identifier for this adapter
     *
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'Test';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     *
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        // TODO: Implement getAccessableMethods() method.
    }

    /**
     * Returns all messages as string
     *
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        // TODO: Implement getMessagesAsString() method.
    }

    /**
     * Returns default permission as object
     *
     * @return Object
     */
    public function getDefaultPermissions()
    {
        // TODO: Implement getDefaultPermissions() method.
}}