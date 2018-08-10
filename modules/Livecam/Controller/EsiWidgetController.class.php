<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Class EsiWidgetController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_livecam
 * @version     1.0.0
 */
namespace Cx\Modules\Livecam\Controller;

/**
 * JsonAdapter Controller to handle EsiWidgets
 * Usage:
 * - Create a subclass that implements parseWidget()
 * - Register it as a Controller in your ComponentController
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_livecam
 * @version     1.0.0
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

