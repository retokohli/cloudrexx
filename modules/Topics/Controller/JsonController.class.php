<?php
/**
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */

namespace Cx\Modules\Topics\Controller;

/**
 * Topics JSON Adapter
 *
 * Used in the backend to supply Entries to the MediaBrowser.
 * See {@link core_modules/MediaBrowser/View/Script/MediaBrowser.js},
 * especially the "TopicsEntryController".
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */
class JsonController
extends \Cx\Core\Core\Model\Entity\Controller
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
            $locale = \FWLanguage::getLocaleByFrontendId($languageId);
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
