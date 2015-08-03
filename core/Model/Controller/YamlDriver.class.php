<?php
/**
 * Wrapper class for Doctrine YAML Driver
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     4.0.0
 * @package     contrexx
 * @subpackage  core_model
 */

namespace Cx\Core\Model\Controller;

/**
 * Wrapper class for Doctrine YAML Driver
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     4.0.0
 * @package     contrexx
 * @subpackage  core_model
 */
class YamlDriver extends \Doctrine\ORM\Mapping\Driver\YamlDriver
{
    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $element = $this->getElement($className, true);
        // Customizing for Contrexx: YamlEntity extension
        if ($element['type'] == 'YamlEntity') {
            $metadata->setCustomRepositoryClass(
                isset($element['repositoryClass']) ? $element['repositoryClass'] : null
            );
            $metadata->isMappedSuperclass = true;
        }
        parent::loadMetadataForClass($className, $metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getElement($className, $raw = false)
    {
        $result = $this->_loadMappingFile($this->_findMappingFile($className));
        if (!$raw && $result[$className]['type'] == 'YamlEntity') {
            $result[$className]['type'] = 'entity';
        }
        return $result[$className];
    }
}

