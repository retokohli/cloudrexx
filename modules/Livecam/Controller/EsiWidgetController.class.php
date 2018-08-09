<?php

/**
 * Main controller for Livecam
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_livecam
 */
namespace Cx\Modules\Livecam\Controller;

/**
 * Main controller for Livecam
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_livecam
 */
class EsiWidgetController
    extends \Cx\Core_Modules\Widget\Controller\EsiWidgetController
{
    /**
     * Parses a widget
     *
     * @param string  $name Widget name
     * @param \Cx\Core\Html\Sigma $template Widget template
     * @param \Cx\Core\Routing\Model\Entity\Response $response Current response
     * @param $params array $params Array of params
     */
    public function parseWidget($name, $template, $response, $params)
    {
        if ($name == 'LIVECAM_CURRENT_IMAGE_B64') {
            $livecam = new \Cx\Modules\Livecam\Controller\Livecam('');
            $camSettings = $livecam->getCamSettings($livecam);

            $imageSize = getimagesize(
                $camSettings['currentImagePath']
            );
            $imageType = image_type_to_mime_type($imageSize[2]);

            $imageSrc = $imageType . ';base64,'. base64_encode(
                file_get_contents($camSettings['currentImagePath'])
            );

            $response->setExpirationDate(new \DateTime('+1minute'));
            $template->setVariable($name, $imageSrc);

            return;
        }
    }
}

