<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2016
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



namespace Cx\Core\View\Model\Event;
use Cx\Core\View\Model\Entity\Frontend;

/**
 * LocaleLocaleEventListener
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Nicola Tommasi <nicola.tommasi@comvation.com>
 * @package     cloudrexx
 */
class LocaleLocaleEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }

    /**
     * Adds new frontend entities for each channel
     * when persisting a new locale with a not already used iso1 code
     *
     * Since a locale contains an iso1 code and an optional country
     * iso1 codes can be used several times.
     * Due to this we need to check wether an iso1 code already has frontend entities
     * If the iso1 code is used for the first time, new frontend entities are created for each channel
     * using the theme values of the frontend entities of the default language
     *
     * @param $eventArgs
     */
    public function postPersist($eventArgs) {

        $em = $eventArgs->getEntityManager();
        // get persisted locale
        $persistedLocale = $eventArgs->getEntity();
        $persistedIso1 = $persistedLocale->getSourceLanguage()->getIso1();

        $frontendRepo = $em->getRepository('Cx\Core\View\Model\Entity\Frontend');
        // check if frontend entity with new locale as source language already exists
        $frontendByIso1 = $frontendRepo->findOneBy(
            array('language' => $persistedIso1)
        );
        if ($frontendByIso1) { // frontend entity with this iso1 code already exists
            return;
        } else { // clone frontend entities of default locale
            // get iso1 code of default locale
            $defaultIso1 = \FWLanguage::getLanguageParameter(\FWLanguage::getDefaultLangId(), 'iso1');
            // get default frontends (one for each Cx\Core\View\Model\Entity\Theme channel)
            $defaultFrontends = $frontendRepo->findBy(array('language' => $defaultIso1));
            $langRepo = $em->getRepository('Cx\Core\Locale\Model\Entity\Language');
            foreach ($defaultFrontends as $defaultFrontend) {
                if ($defaultFrontend) {
                    // create new frontend entity
                    $newFrontend = clone $defaultFrontend;
                    // set iso1 of persisted locale
                    $newFrontend->setLanguage($persistedIso1);
                    // set effective association to language entity
                    $newFrontend->setLocaleRelatedByIso1s(
                        $langRepo->find($persistedIso1)
                    );
                    $em->persist($newFrontend);
                }
            }
            $em->flush();
        }
    }

    /**
     * Deletes the frontend entities of a deleted locale if no other locale uses them
     * Locales with the same iso1 code use the same frontend entities
     *
     * @param $eventArgs
     */
    public function preRemove($eventArgs) {

        // check if other locales with same iso1 code exist

        // delete frontend entity with deleted locale's iso1 code
    }
}