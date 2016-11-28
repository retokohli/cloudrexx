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

namespace Cx\Modules\Topics\Controller;

/**
 * Specific FrontendController for this Component. Use this to easily create a frontent view
 * @copyright   Cloudrexx AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_topics
 */
class FrontendController
extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController
{
    /**
     * Parse the Topics page
     *
     * Note that the $cmd parameter is ignored, as it does not contain
     * the complete request parameter set.
     * The Component interprets the URL directly in order to determine the
     * current locales and slugs.
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   string                  $cmd        Ignored
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        \JS::activateByVersion('jquery', '2.0.3');
        $parameters = new \Cx\Modules\Topics\Entity\FrontendParameter($this->cx);
        if ($parameters->isUrlInconsistent()) {
            $url_page = $parameters->getUrlPage();
            \Cx\Core\Csrf\Controller\Csrf::redirect($url_page);
        }
        self::parseGlobals($template, $parameters);
        // These are not necessary if content is requested via the API only
        //$this->showEntries($template, $parameters);
        //$this->showEntry($template, $parameters);
        // However, in order for the events to work, the two main container
        // elements must be touched to be present on the initial page.
        $template->touchBlock('topics_list_entries');
        $template->touchBlock('topics_detail_entry');
        $this->showControls($template, $parameters);
        $cmd = null; // Intentionally unused
    }

    /**
     * Parse everything global in the view
     *
     * Includes language entries, view parameters, and corresponding
     * cxjs variables.
     * @global  array                   $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   \Cx\Modules\Topics\Entity\FrontendParameter
     *                                  $parameters
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function parseGlobals(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Entity\FrontendParameter $parameters)
    {
        global $_ARRAYLANG;
        $template->setGlobalVariable($_ARRAYLANG + array(
            'MODULE_TOPICS_URL_BASE' => $parameters->getUrlBase(),
            'MODULE_TOPICS_URL_LOCALE_LIST' => $parameters->getLocaleList(),
            'MODULE_TOPICS_URL_LOCALE_DETAIL' => $parameters->getLocaleDetail(),
            'MODULE_TOPICS_URL_SLUG_CATEGORY' => $parameters->getSlugCategory(),
            'MODULE_TOPICS_URL_SLUG_ENTRY' => $parameters->getSlugEntry(),
        ));
        \Cx\Core\Setting\Controller\Setting::init('Topics', 'config');
        $cxjs = \ContrexxJavascript::getInstance();
        $cxjs->setVariable('url_base', $parameters->getUrlBase(), 'Module/Topics');
        $cxjs->setVariable('locale_system', \FWLanguage::getLocaleById(FRONTEND_LANG_ID), 'Module/Topics');
        $cxjs->setVariable('locale_list', $parameters->getLocaleList(), 'Module/Topics');
        $cxjs->setVariable('locale_detail', $parameters->getLocaleDetail(), 'Module/Topics');
        $cxjs->setVariable('slug_category', $parameters->getSlugCategory(), 'Module/Topics');
        $cxjs->setVariable('slug_entry', $parameters->getSlugEntry(), 'Module/Topics');
        $cxjs->setVariable('frontend_fulltext_enable',
            (boolean)\Cx\Core\Setting\Controller\Setting::getValue('frontend_fulltext_enable'), 'Module/Topics');
    }

    /**
     * Show the Entries selected by the given parameters
     *
     * Note that this MUST be public, as it is called by the API
     * via the ComponentController.
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   \Cx\Modules\Topics\Entity\FrontendParameter
     *                                  $parameters
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function showEntries(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Entity\FrontendParameter $parameters)
    {
        $em = $this->cx->getDb()->getEntityManager();
        // Problems with proper sorting in current locale:
        //  - Using setTranslatableLocale(), the proper locale is loaded,
        //      but the sorting is applied to the default
        //  - Using the TranslationWalker, sorting works, but is extremely slow
        //      (18+ seconds):
        //    $query->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
        //        'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker');
        //    $entries = $query->getResult();
        // See below for the alternative currently in use (which also solves
        // various other issues related to UTF-8 encoding and names starting
        // with non-alphabetic characters).
        $this->cx->getDb()->getTranslationListener()
            ->setTranslatableLocale($parameters->getLocaleList());
        $entries = null;
        if ($parameters->getSlugCategory()) {
            if ($parameters->getCategory()) {
                $category = $parameters->getCategory();
                // getEntries() returns an (Array)Collection
                $entries = $category->getEntries()->toArray();
            } else {
            }
        } else {
            // Should apply only if no Category is selected
            $entryRepo = $em->getRepository(
                'Cx\\Modules\\Topics\\Model\\Entity\\Entry');
            $entries = $entryRepo->findAll();
        }
        if (!$entries) {
            // Either an error occurred, or the Category is empty
            $entries = array();
        }
        // Alternative sorting (mandatory at least while using Doctrine <2.4):
        // Important notes:
        // Sorting by name is slow and prone to errors, due to PHP's inability
        // to natively sort UTF-8 strings. Crutches like iconv() or \Collator
        // might help, but:
        // Even correct sorting by name does not solve the problem of
        // non-alphabet characters, in particular when starting with one,
        // i.e. "(Etre) responsable". As these should not come before "A",
        // and there should be no index for "(", Entries are sorted by
        // their slugs instead. Slugs had any such characters stripped, and
        // spaces substituted, but are otherwise equivalent for the
        // purpose of sorting.
        usort($entries, function($a, $b) {
            return strcmp($a->getSlug(), $b->getSlug());
        });
        $this->parseEntries($template, $entries, $parameters);
    }

    /**
     * Parse the list of entries
     *
     * Includes the clickable index of available starting letters,
     * and respective anchors.
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   array                   $entries    The Entries in list locale
     * @param   \Cx\Modules\Topics\Entity\FrontendParameter
     *                                  $parameters
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function parseEntries(\Cx\Core\Html\Sigma $template,
        array $entries,
        \Cx\Modules\Topics\Entity\FrontendParameter $parameters)
    {
        $letters = array();
        $letter_prev = null;
        $alphaindex = $parameters->getAlphaindex();
        $fulltext = $parameters->getFulltext();
        $url = null;
        if ($alphaindex) {
            // Absolute and complete URL for standalone alpha index
            $url = \Cx\Core\Routing\Url::fromModuleAndCmd('Topics', '',
                    \FWLanguage::getIdByLocale($parameters->getLocaleList()))
                ->toString();
        }
        foreach ($entries as $entry) {
            $name = $entry->getName();
            // Note that Entries are grouped by the first alphabetic
            // character here, which incidentally is identical to
            // the very first character in the slug!
            $letter = strtoupper($entry->getSlug()[0]);
            if ($letter !== $letter_prev) {
                $letter_prev = $letter;
                $letters[$letter] = true;
                if ($alphaindex) {
                    // Loop the entries in order to collect all letters,
                    // but do not parse any part of the list!
                    continue;
                }
                $template->setVariable('MODULE_TOPICS_LETTER', $letter);
                $template->parse('topics_list_row');
            }
            if ($alphaindex) {
                // Loop the entries in order to collect all letters,
                // but do not parse any part of the list!
                continue;
            }
            if ($fulltext) {
                $template->setVariable('MODULE_TOPICS_ENTRY_DESCRIPTION',
                    contrexx_raw2xhtml($entry->getDescription()));
            }
            $template->setGlobalVariable(array(
                'MODULE_TOPICS_ENTRY_SLUG' => $entry->getSlug(),
                'MODULE_TOPICS_ENTRY_ID' => $entry->getId(),
                'MODULE_TOPICS_ENTRY_NAME' => contrexx_raw2xhtml($name),
            ));
            if ($entry->getHref()) {
                $template->setVariable(
                    'MODULE_TOPICS_ENTRY_HREF', $entry->getHref());
            } else {
                $template->touchBlock('topics_list_entry_slug');
            }
            $template->parse('topics_list_row');
        }
        if ($alphaindex) {
            $template->setGlobalVariable('MODULE_TOPICS_URL_VIEW', $url);
        }
        foreach (array_keys($letters) as $letter) {
            $template->setVariable('MODULE_TOPICS_LETTER', $letter);
            $template->parse('topics_letter');
        }
        if (!$entries) {
            global $_ARRAYLANG;
            $template->setGlobalVariable($_ARRAYLANG);
            $template->touchBlock('topics_list_empty');
        }
        // This block won't show in API mode unless it's parsed!
        $template->parse('topics_list_entries');
    }

    /**
     * Show the Entry selected by the given parameters
     *
     * Note that this MUST be public, as it is called by the API
     * via the ComponentController.
     * @global  array                   $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   \Cx\Modules\Topics\Entity\FrontendParameter
     *                                  $parameters
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function showEntry(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Entity\FrontendParameter $parameters)
    {
        if (!$parameters->getEntry()) {
            global $_ARRAYLANG;
            $entry = new \Cx\Modules\Topics\Model\Entity\Entry;
            $entry->setName($_ARRAYLANG['TXT_MODULE_TOPICS_ENTRY_NOT_FOUND']);
            $this->parseEntry($template, $entry);
            return;
        }
        $em = $this->cx->getDb()->getEntityManager();
        $entry = $parameters->getEntry();
        $entry->setTranslatableLocale($parameters->getLocaleDetail());
        $em->refresh($entry);
        $this->parseEntry($template, $entry);
    }

    /**
     * Parse the given single Entry
     * @param   \Cx\Core\Html\Sigma                     $template
     * @param   \Cx\Modules\Topics\Model\Entity\Entry  $entry
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function parseEntry(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Model\Entity\Entry $entry)
    {
        $template->setVariable(array(
            'MODULE_TOPICS_ENTRY_ID' => $entry->getId(),
            'MODULE_TOPICS_ENTRY_NAME' =>
            contrexx_raw2xhtml($entry->getName()),
            'MODULE_TOPICS_ENTRY_DESCRIPTION' => $entry->getDescription(),
            'MODULE_TOPICS_ENTRY_CREATED' =>
                ($entry->getCreated()
                    ? $entry->getCreated()->format(ASCMS_DATE_FORMAT_DATETIME)
                    : ''),
            'MODULE_TOPICS_ENTRY_UPDATED' =>
                ($entry->getUpdated()
                    ? $entry->getUpdated()->format(ASCMS_DATE_FORMAT_DATETIME)
                    : ''),
        ));
        // Both unused in the detail view; set for completeness only
        if ($entry->getHref()) {
            $template->setVariable(
                'MODULE_TOPICS_ENTRY_HREF', $entry->getHref());
        } else {
            $template->setVariable(
                'MODULE_TOPICS_ENTRY_SLUG', $entry->getSlug());
        }
        $template->parse('topics_detail_entry');
    }

    /**
     * Parse the control elements
     *
     * Note that this relies on the active frontend languages using
     * the same codes in the "lang" property as the locales used in
     * this Component, namely "de", "fr", "it", and "en".
     * @global  array   $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   \Cx\Modules\Topics\Entity\FrontendParameter
     *                                  $parameters
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function showControls(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Entity\FrontendParameter $parameters)
    {
        global $_ARRAYLANG;
        $languages = \FWLanguage::getActiveFrontendLanguages();
        $options_language = array();
        foreach ($languages as $language) {
            $options_language[$language['lang']] = $_ARRAYLANG[
                'TXT_MODULE_TOPICS_OPTION_LOCALE_'
                . strtoupper($language['lang'])];
        }
        $em = $this->cx->getDb()->getEntityManager();
        $categoryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Category');
        $this->cx->getDb()->getTranslationListener()
            ->setTranslatableLocale($parameters->getLocaleSystem());
        $categories = $categoryRepo->findAll();
        $options_category = array(
            '' => $_ARRAYLANG['TXT_MODULE_TOPICS_OPTION_CATEGORY_ALL']);
        if ($categories) {
            foreach ($categories as $category) {
                $options_category[$category->getSlug()] = $category->getName();
            }
        }
        $template->setVariable(array(
            'MODULE_TOPICS_OPTIONS_LOCALE_LIST' =>
            \Html::getOptions($options_language, $parameters->getLocaleList()),
            'MODULE_TOPICS_OPTIONS_LOCALE_DETAIL' =>
            \Html::getOptions($options_language, $parameters->getLocaleDetail()),
            'MODULE_TOPICS_OPTIONS_CATEGORY' =>
            \Html::getOptions($options_category, $parameters->getSlugCategory()),
        ));
    }

}
