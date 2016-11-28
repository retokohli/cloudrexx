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
 * JSON Adapter for Topics
 *
 * Used in the backend to supply Entries to the MediaBrowser.
 * See {@link core_modules/MediaBrowser/View/Script/MediaBrowser.js},
 * especially the "TopicsEntryController".
 * @copyright   Cloudrexx AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_json
 */

namespace Cx\Modules\Topics\Controller;

/**
 * JSON Adapter for Topics
 * @copyright   Cloudrexx AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class JsonController
extends \Cx\Core\Core\Model\Entity\SystemComponentController
implements \Cx\Core\Json\JsonAdapter
{
    protected $message;

    /**
     * Set the message
     * @param   string  $message
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Returns the Adapter name
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getName()
    {
        return 'Topics';
    }

    /**
     * Returns an array of method names accessible from a JSON request
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getAccessableMethods()
    {
        return array(
            'getEntries',
        );
    }

    /**
     * Returns the message
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getMessagesAsString()
    {
        return $this->message;
    }

    /**
     * Not implemented
     * @return  null
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getDefaultPermissions()
    {
        return null;
    }

    /**
     * Get Topics Entries, including all available translations
     *
     * The array produced only contains the slug and name properties
     * for each locale available, and has the structure:
     *  array(
     *      locale => array(
     *          array(
     *              'slug' => slug,
     *              'name' => name,
     *          ),
     *          [... more ...]
     *      ),
     *      [... more ...]
     *  )
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Sort entries alphabetically, perhaps in view (angular)?
     */
    public function getEntries()
    {
        $db = $this->cx->getDb();
        $em = $db->getEntityManager();
        $entryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Entry');
        $response = array();
        foreach (\FWLanguage::getActiveFrontendLanguages() as $languages) {
            $languageId = $languages['id'];
            $locale = \FWLanguage::getLocaleById($languageId);
            $db->getTranslationListener()
                ->setTranslatableLocale($locale);
            $em->clear();
            $entries = $entryRepo->findAll();
            $response[$locale] = array();
            foreach ($entries as $entry) {
                $response[$locale][] = array(
                    'slug' => $entry->getSlug(),
                    'name' => $entry->getName(),
                );
            }
        }
        return $response;
    }

}
