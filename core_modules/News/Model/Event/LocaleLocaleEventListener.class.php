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



namespace Cx\Core_Modules\News\Model\Event;

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
     * * Fills the new news locales (news, categories, types and settings) with the default locale's values
     * when adding a new Cx\Core\Locale\Model\Entity\Locale
     *
     * @param $eventArgs
     */
    public function postPersist($eventArgs) {
        global $objDatabase;

        // get persisted locale
        $persistedLocale = $eventArgs->getEntity();

        $defaultLocaleId = \FWLanguage::getDefaultLangId();
        $localeId = $persistedLocale->getId();

        // Add new news locales
        $newsQuery = 'INSERT IGNORE INTO
                                `' . DBPREFIX . 'module_news_locale`
                                (   `news_id`,
                                    `lang_id`,
                                    `is_active`,
                                    `title`,
                                    `text`,
                                    `teaser_text`
                                )
                                SELECT `news_id`,
                                        ' . $localeId . ',
                                        0,
                                        `title`,
                                        `text`,
                                        `teaser_text`
                                    FROM `' . DBPREFIX . 'module_news_locale`
                                    WHERE lang_id = ' . $defaultLocaleId;
        $objDatabase->Execute($newsQuery);

        // Add new category locales
        $catQuery = 'INSERT IGNORE INTO
                                `' . DBPREFIX . 'module_news_categories_locale`
                                (   `category_id`,
                                    `lang_id`,
                                    `name`
                                )
                                SELECT `category_id`,
                                        ' . $localeId . ',
                                        `name`
                                    FROM `' . DBPREFIX . 'module_news_categories_locale`
                                    WHERE lang_id = ' . $defaultLocaleId;
        $objDatabase->Execute($catQuery);

        // Add new type locales
        $typeQuery = 'INSERT IGNORE INTO
                                `' . DBPREFIX . 'module_news_types_locale`
                                (   `type_id`,
                                    `lang_id`,
                                    `name`
                                )
                                SELECT `type_id`,
                                        ' . $localeId . ',
                                        `name`
                                    FROM `' . DBPREFIX . 'module_news_types_locale`
                                    WHERE lang_id = ' . $defaultLocaleId;
        $objDatabase->Execute($typeQuery);

        // Add new settings locales
        $settingsQuery = 'INSERT IGNORE INTO
                                `' . DBPREFIX . 'module_news_settings_locale`
                                (   `name`,
                                    `lang_id`,
                                    `value`
                                )
                                SELECT `name`,
                                        ' . $localeId . ',
                                        `value`
                                    FROM `' . DBPREFIX . 'module_news_settings_locale`
                                    WHERE lang_id = ' . $defaultLocaleId;
        $objDatabase->Execute($settingsQuery);
    }

    /**
     * Deletes the news locales (news, categories, types settings)
     * when deleting a Cx\Core\Locale\Model\Entity\Locale
     *
     * @param $eventArgs
     */
    public function preRemove($eventArgs) {
        global $objDatabase;

        // get locale, which will be deleted
        $delLocale = $eventArgs->getEntity();

        $localeId = $delLocale->getId();

        // Delete the news locales
        $newsQuery = 'DELETE FROM `' . DBPREFIX . 'module_news_locale`
                                WHERE lang_id = ' . $localeId;
        $objDatabase->Execute($newsQuery);

        // Delete the category locales
        $catQuery = 'DELETE FROM `' . DBPREFIX . 'module_news_categories_locale`
                                WHERE lang_id = ' . $localeId;
        $objDatabase->Execute($catQuery);

        // Delete the type locales
        $typeQuery = 'DELETE FROM `' . DBPREFIX . 'module_news_types_locale`
                                            WHERE lang_id = ' . $localeId;
        $objDatabase->Execute($typeQuery);

        // Update the news settings locale
        $settingsQuery = 'DELETE FROM `' . DBPREFIX . 'module_news_settings_locale`
                                            WHERE lang_id = ' . $localeId;
        $objDatabase->Execute($settingsQuery);
    }
}