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
 * Topics BackendController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    /**
     * Returns an array of available commands (?act=XY)
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getCommands()
    {
        // Don't add 'Default', it's included by default.
        return array('Entry', 'Category', 'Export', 'Settings',
        // Disable/hide custom "Import" for production!
        //'Import',
        );
    }

    /**
     * Parse the backend view
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   array                   $cmd
     * @param   boolean                 $isSingle
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd,
        &$isSingle = false)
    {
        if (!isset($cmd[0])) {
            $cmd[0] = '';
        }
        $controller = null;
        switch ($cmd[0]) {
            case 'Entry':
            case 'Category':
                $id = null;
                if (isset($_GET['add'])) {
                    call_user_func(array($this, 'show' . $cmd[0]), $template,
                        null, $isSingle);
                    break;
                }
                if (isset($_GET['editid'])) {
                    $id = intval(preg_replace('/.+,(\d+)/', '$1', $_GET['editid']));
                    call_user_func(array($this, 'show' . $cmd[0]), $template,
                        $id, $isSingle);
                    break;
                }
                $locale = \FWLanguage::getLocaleByFrontendId(LANG_ID);
                $this->cx->getDb()->getTranslationListener()
                    ->setTranslatableLocale($locale);
                // Note that parent::parsePage($template, $cmd) won't render
                // the view properly (wrong ViewGeneratorOptions).
                $view = new \Cx\Core\Html\Controller\ViewGenerator(
                    '\\Cx\\Modules\\Topics\\Model\\Entity\\' . $cmd[0],
                    $this->getViewGeneratorOptions(null));
                $template->setVariable('ENTITY_VIEW', $view->render());
                break;
            case 'Settings':
                $controller = $this->getController('Settings');
                break;
            case 'Import':
                $controller = $this->getController('Import');
                break;
            case 'Export':
                $controller = $this->getController('Export');
                break;
        }
        if ($controller) {
            $controller->parsePage($template, $this->cx);
        }
    }

    /**
     * Return true here if you want the first tab to be an entity view
     * @return boolean True if overview should be shown, false otherwise
     */
    protected function showOverviewPage()
    {
        return false;
    }

    /**
     * Show the Entry with the given ID for editing
     *
     * Adds a new Entry if given an empty ID.
     * Stores the Entry if present in the POST.
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   integer                 $id
     * @param   boolean                 $isSingle
     * @return  boolean                     True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function showEntry(\Cx\Core\Html\Sigma $template, $id=null,
        &$isSingle = false)
    {
        global $_ARRAYLANG;
        $entry = null;
        $em = $this->cx->getDb()->getEntityManager();
        $entryRepo = $em->getRepository(
            $this->getNamespace()
            . '\\Model\\Entity\\Entry');
        if ($id) {
            $entry = $entryRepo->find($id);
            if (!$entry) {
                return \Message::error(
                    $_ARRAYLANG['TXT_MODULE_TOPICS_ENTRY_NO_MATCH']);
            }
        }
        if (!$entry) {
            $entry = new \Cx\Modules\Topics\Model\Entity\Entry;
        }
        if ($this->storeEntryFromPost($entry)) {
            \Cx\Core\Csrf\Controller\Csrf::redirect('Entry');
        }
        $isSingle = true;
        return $this->parseEntry($template, $entry);
    }

    /**
     * Parse the given single Entry
     * @param   \Cx\Core\Html\Sigma                     $template
     * @param   \Cx\Modules\Topics\Model\Entity\Entry  $entry      The Entry
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function parseEntry(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Model\Entity\Entry $entry)
    {
        global $_ARRAYLANG;
        $em = $this->cx->getDb()->getEntityManager();
        // Parse non-localized properties
        $template->setVariable(array(
            'MODULE_TOPICS_ENTRY_ACTIVE_CHECKED' =>
            ($entry->getActive() ? \Html::ATTRIBUTE_CHECKED : ''),
            // View only!
            'MODULE_TOPICS_ENTRY_ID' => $entry->getId(),
            'MODULE_TOPICS_ENTRY_CREATED' => contrexx_raw2xhtml(
                // Note: Not nullable, but null on creation!
                ($entry->getCreated() ? $entry->getCreated()->format(ASCMS_DATE_FORMAT_DATETIME)
                        : $_ARRAYLANG['TXT_MODULE_TOPICS_UPDATED_NONE'])),
            'MODULE_TOPICS_ENTRY_UPDATED' => contrexx_raw2xhtml(
                ($entry->getUpdated() ? $entry->getUpdated()->format(ASCMS_DATE_FORMAT_DATETIME)
                        : $_ARRAYLANG['TXT_MODULE_TOPICS_UPDATED_NONE'])),
        ));
        $this->cx->getDb()->getTranslationListener()
            ->setTranslatableLocale(
                \FWLanguage::getLocaleByFrontendId(BACKEND_LANG_ID));
        $this->parseEntryCategories($template, $entry);
        // Parse localized properties (for each active locale)
        $active = true;
        foreach (\FWLanguage::getActiveFrontendLanguages() as $language) {
            $languageId = $language['id'];
            $locale = \FWLanguage::getLocaleByFrontendId($languageId);
            if ($entry->getId()) {
                $entry->setTranslatableLocale($locale);
                $em->refresh($entry);
            }
            $description_wysiwyg = new \Cx\Core\Wysiwyg\Wysiwyg(
                'entry[description][' . $locale . ']',
                contrexx_raw2xhtml($entry->getDescription()),
                'small', $languageId);
            $template->setGlobalVariable(array(
                'MODULE_TOPICS_ENTRY_LANGUAGE_ID' => $languageId,
                'MODULE_TOPICS_ENTRY_LANGUAGE' =>
                contrexx_raw2xhtml($language['name']),
                'MODULE_TOPICS_ENTRY_LOCALE' =>
                contrexx_raw2xhtml($locale),
                'MODULE_TOPICS_ENTRY_LOCALE_ACTIVE' =>
                ($active ? 'active' : ''),
                'MODULE_TOPICS_ENTRY_SLUG' =>
                contrexx_raw2xhtml($entry->getSlug()),
                'MODULE_TOPICS_ENTRY_HREF' =>
                contrexx_raw2xhtml($entry->getHref()),
                'MODULE_TOPICS_ENTRY_NAME' =>
                contrexx_raw2xhtml($entry->getName()),
                'MODULE_TOPICS_ENTRY_DESCRIPTION_WYSIWYG' =>
                $description_wysiwyg,
            ));
            $template->touchBlock('entry_locale_tab');
            $template->parse('entry_locale_tab');
            $template->touchBlock('entry_locale_div');
            $template->parse('entry_locale_div');
            $active = false;
            /* TODO: Perhaps more efficient; test with Doctrine 2.3+:
              $article = $em->find('Entity\Article', $article id);
              $repository = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Translation');
              $translations = $repository->findTranslations($article);
              // $translations contains:
              Array (
              [de] => Array(
              [title] => my title in de,
              [content] => my content in de),
              [...]
              )
            If it works, apply to parseCategory() as well. */
        }
    }

    /**
     * Parse available and associated Categories for the given Entry
     * @param   \Cx\Core\Html\Sigma                     $template
     * @param   \Cx\Modules\Topics\Model\Entity\Entry  $entry
     */
    protected function parseEntryCategories(
        \Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Model\Entity\Entry $entry)
    {
        \JS::activate('chosen');
        $em = $this->cx->getDb()->getEntityManager();
        $categoryRepo = $em->getRepository(
            $this->getNamespace()
            . '\\Model\\Entity\\Category');
        $categories = $categoryRepo->findAll();
        $categoryIdsAssociated = array();
        foreach ($entry->getCategories() as $category) {
            $categoryIdsAssociated[$category->getId()] = true;
        }
        $options = $selected = array();
        foreach ($categories as $category) {
            $options[$category->getId()] = $category->getName();
            if (array_key_exists($category->getId(), $categoryIdsAssociated)) {
                $selected[$category->getId()] = true;
            }
        }
        $template->setVariable(
            'MODULE_TOPICS_ENTRY_CATEGORIES',
            \Html::getSelect('entry[category_ids][]', $options, $selected,
                'entry-categories', '', \Html::ATTRIBUTE_MULTIPLE)
        );
    }

    /**
     * Updates the new or edited Entry from the POST and stores it
     *
     * Returns null if no entry has been posted (NOOP).
     * Sets appropriate Message.
     * @param   \Cx\Modules\Topics\Model\Entity\Entry  $entry
     * @return  boolean|null            True on success, null on NOOP,
     *                                  or false otherwise
     */
    protected function storeEntryFromPost(
        \Cx\Modules\Topics\Model\Entity\Entry $entry)
    {
        global $_ARRAYLANG;
        // Note: If the key exists, its value must be an array
        if (!array_key_exists('entry', $_POST)) {
            return null; // NOOP
        }
        /* POST data sample:
          csrf    MTA3NjI4NjMzMw__
          entry[active]             on   [NOTE: Not present unless checked]
          entry[category_ids][]     1
          entry[category_ids][]     2
          entry[category_ids][]     5
          entry[description][de]    <a href="obligationen">Obligationen</a> aus Lateinamerika, [...]
          entry[description][en]    <a href="bonds">Bonds</a> from Latin America, [...]
          entry[description][fr]    <a href="obligation-1">Obligations</a> d'Amérique latine, [...]
          entry[description][it]    <a href="obbligazione">Obbligazioni</a> emessi da Paesi dellAmerica Latina, [...]
          entry[id]                 276
          entry[name][de]           Emerging Economies Bonds
          entry[name][en]           Emerging economies bonds
          entry[name][fr]           Obligations des pays émergents
          entry[name][it]           Emerging economies bonds
          entry[slug][de]           emerging-economies-bonds-1
          entry[slug][en]           emerging-economies-bonds
          entry[slug][fr]           obligations-des-pays-emergents
          entry[slug][it]           emerging-economies-bonds-1
         */
        $em = $this->cx->getDb()->getEntityManager();
        $entry->setActive(array_key_exists('active', $_POST['entry']));
        if ($entry->getId()) {
            $entry->setUpdated(new \DateTime);
        }
        // Note: If the key exists, its value must be an array
        if (array_key_exists('category_ids', $_POST['entry'])) {
            $category_ids_posted = array_flip($_POST['entry']['category_ids']);
            $category_ids_entry = array_flip(array_map(
                    function($category) {
                    return $category->getId();
                }, $entry->getCategories()->toArray()));
            $categoryRepo = $em->getRepository(
                $this->getNamespace()
                . '\\Model\\Entity\\Category');
            $categories = $categoryRepo->findAll();
            foreach ($categories as $category) {
                if (
                    array_key_exists($category->getId(), $category_ids_posted) && !array_key_exists($category->getId(),
                        $category_ids_entry)
                ) {
                    $entry->addCategory($category);
                }
                if (
                    !array_key_exists($category->getId(), $category_ids_posted) && array_key_exists($category->getId(),
                        $category_ids_entry)
                ) {
                    // Note that for bidirectional m:n, the relation
                    // MUST be broken on both sides!
                    $entry->getCategories()->removeElement($category);
                    $category->getEntries()->removeElement($entry);
                }
            }
        }
        $frontendLanguages = self::getFrontendLanguagesOrderedForStoring();
        foreach ($frontendLanguages as $language) {
            $language_id = $language['id'];
            $locale = \FWLanguage::getLocaleByFrontendId($language_id);
            // Skip this locale altogether if the name is empty.
            // The name is required in order to form the (non-null) slug!
            $name = $_POST['entry']['name'][$locale];
            if (!$name) {
                continue;
            }
            $href = (empty($_POST['entry']['href'][$locale])
                ? null : $_POST['entry']['href'][$locale]);
            $description = $_POST['entry']['description'][$locale];
            // NOTE: In order to get a proper slug, it is not sufficient
            // to just add the translations.
            // Instead, the entity must be persisted/flushed for each locale.
            // The default locale is processed last, so that its data
            // is present in the main entry.
            $entry->setTranslatableLocale($locale);
            $entry->setName($name);
            $entry->setHref($href);
            $entry->setDescription($description);
            $em->persist($entry); // Only effective for new Entries
            try {
                $em->flush();
            } catch (\Exception $e) {
                $e = null; // Unused
                return \Message::error(
                    $_ARRAYLANG['TXT_MODULE_TOPICS_ENTRY_ERROR_STORING_FAILED']);
            }
        }
        return \Message::ok(
            $_ARRAYLANG['TXT_MODULE_TOPICS_ENTRY_STORED_SUCCESSFULLY']);
    }

    /**
     * Show the Category with the given ID for editing
     *
     * Adds a new Category if given an empty ID.
     * Stores the Category if present in the POST.
     * @param   \Cx\Core\Html\Sigma     $template
     * @param   integer                 $id
     * @param   boolean                 $isSingle
     * @return  boolean                     True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function showCategory(\Cx\Core\Html\Sigma $template, $id=null,
        &$isSingle = false)
    {
        global $_ARRAYLANG;
        $category = null;
        $em = $this->cx->getDb()->getEntityManager();
        $categoryRepo = $em->getRepository(
            $this->getNamespace()
            . '\\Model\\Entity\\Category');
        if ($id) {
            $category = $categoryRepo->find($id);
            if (!$category) {
                return \Message::error(
                    $_ARRAYLANG['TXT_MODULE_TOPICS_CATEGORY_NO_MATCH']);
            }
        }
        if (!$category) {
            $category = new \Cx\Modules\Topics\Model\Entity\Category;
        }
        if ($this->storeCategoryFromPost($category)) {
            \Cx\Core\Csrf\Controller\Csrf::redirect('Category');
        }
        $isSingle = true;
        return $this->parseCategory($template, $category);
    }

    /**
     * Parse the given single Category
     * @param   \Cx\Core\Html\Sigma                         $template
     * @param   \Cx\Modules\Topics\Model\Entity\Category   $category   The Category
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function parseCategory(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Model\Entity\Category $category)
    {
        global $_ARRAYLANG;
        $em = $this->cx->getDb()->getEntityManager();
        // Parse non-localized properties in the current backend language
        $this->cx->getDb()->getTranslationListener()
            ->setTranslatableLocale(
                \FWLanguage::getLocaleByFrontendId(BACKEND_LANG_ID));
        $categoryOptions =
            '<option value="' . null . '">'
            . $_ARRAYLANG['TXT_MODULE_TOPICS_CATEGORY_PARENT_ROOT']
            . '</option>'
            . self::getCategoryOptions($category);
        $template->setVariable(array(
            'MODULE_TOPICS_CATEGORY_ACTIVE_CHECKED' =>
            ($category->getActive() ? \Html::ATTRIBUTE_CHECKED : ''),
            // View only!
            'MODULE_TOPICS_CATEGORY_ID' => $category->getId(),
            'MODULE_TOPICS_CATEGORY_PARENT' =>
                \Html::getSelectCustom('category[parent_id]', $categoryOptions),
            'MODULE_TOPICS_CATEGORY_CREATED' => contrexx_raw2xhtml(
                // Note: Not nullable, but null on creation!
                ($category->getCreated()
                    ? $category->getCreated()->format(ASCMS_DATE_FORMAT_DATETIME)
                    : $_ARRAYLANG['TXT_MODULE_TOPICS_UPDATED_NONE'])),
            'MODULE_TOPICS_CATEGORY_UPDATED' => contrexx_raw2xhtml(
                ($category->getUpdated()
                    ? $category->getUpdated()->format(ASCMS_DATE_FORMAT_DATETIME)
                    : $_ARRAYLANG['TXT_MODULE_TOPICS_UPDATED_NONE'])),
        ));
        // DISABLED -- too many available options to be useful
        //$this->parseCategoryEntries($template, $category);
        // Parse localized properties (for each active locale)
        $active = true;
        foreach (\FWLanguage::getActiveFrontendLanguages() as $language) {
            $languageId = $language['id'];
            $locale = \FWLanguage::getLocaleByFrontendId($language['id']);
            if ($category->getId()) {
                $category->setTranslatableLocale($locale);
                $em->refresh($category);
            }
            $description_wysiwyg = new \Cx\Core\Wysiwyg\Wysiwyg(
                'category[description][' . $locale . ']',
                contrexx_raw2xhtml($category->getDescription()),
                'small', $languageId);
            $template->setGlobalVariable(array(
                'MODULE_TOPICS_CATEGORY_LANGUAGE_ID' => $languageId,
                'MODULE_TOPICS_CATEGORY_LANGUAGE' =>
                contrexx_raw2xhtml($language['name']),
                'MODULE_TOPICS_CATEGORY_LOCALE' =>
                contrexx_raw2xhtml($locale),
                'MODULE_TOPICS_CATEGORY_LOCALE_ACTIVE' =>
                ($active ? 'active' : ''),
                'MODULE_TOPICS_CATEGORY_SLUG' =>
                contrexx_raw2xhtml($category->getSlug()),
                'MODULE_TOPICS_CATEGORY_NAME' =>
                contrexx_raw2xhtml($category->getName()),
                'MODULE_TOPICS_CATEGORY_DESCRIPTION_WYSIWYG' =>
                $description_wysiwyg,
            ));
            $template->touchBlock('category_locale_tab');
            $template->parse('category_locale_tab');
            $template->touchBlock('category_locale_div');
            $template->parse('category_locale_div');
            $active = false;
        }
    }

    /**
     * DISABLED
     * Parse available and associated Entries for the given Category
     * @param   \Cx\Core\Html\Sigma                         $template
     * @param   \Cx\Modules\Topics\Model\Entity\Category   $category
     */
    protected function parseCategoryEntries(\Cx\Core\Html\Sigma $template,
        \Cx\Modules\Topics\Model\Entity\Category $category)
    {
        \JS::activate('chosen');
        $em = $this->cx->getDb()->getEntityManager();
        $entryRepo = $em->getRepository(
            $this->getNamespace()
            . '\\Model\\Entity\\Entry');
        $entries = $entryRepo->findAll();
        $entryIdsAssociated = array();
        foreach ($category->getEntries() as $entry) {
            $entryIdsAssociated[$entry->getId()] = true;
        }
        $options = $selected = array();
        foreach ($entries as $entry) {
            $options[$entry->getId()] = $entry->getName();
            if (array_key_exists($entry->getId(), $entryIdsAssociated)) {
                $selected[$entry->getId()] = true;
            }
        }
        $template->setVariable(
            'MODULE_TOPICS_CATEGORY_ENTRIES',
            \Html::getSelect('category[entry_ids][]', $options, $selected,
                'category-entries', '', \Html::ATTRIBUTE_MULTIPLE)
        );
    }

    /**
     * Updates the new or edited Category from the POST and stores it
     *
     * Returns null if no category has been posted (NOOP).
     * Sets appropriate Message.
     * @param   \Cx\Modules\Topics\Model\Entity\Category   $category
     * @return  boolean|null            True on success, null on NOOP,
     *                                  or false otherwise
     */
    protected function storeCategoryFromPost(
        \Cx\Modules\Topics\Model\Entity\Category $category)
    {
        global $_ARRAYLANG;
        // Note: If the key exists, its value must be an array
        if (!array_key_exists('category', $_POST)) {
            return null; // NOOP
        }
        /* POST data sample:
         * category[description][de]
         * category[description][en]
         * category[description][fr]
         * category[description][it]
         * DISABLED:
         * //category[entry_ids][]       15
         * //category[entry_ids][]       26
         * //[...]
         * //category[entry_ids][]       1024
         * category[id]                5
         * category[parent_id]         ??
         * category[name][de]          Fonds / Finanz
         * category[name][en]          Fonds / Finance
         * category[name][fr]          Fonds / Finance
         * category[name][it]          Fonds / Finanziarie
         * category[slug][de]          fonds-finanz
         * category[slug][en]          fonds-finance
         * category[slug][fr]          fonds-finance
         * category[slug][it]          fonds-finanziarie
         * csrf                        MTA3OTQ2Njg1Mg__
         */
        $em = $this->cx->getDb()->getEntityManager();
        $category->setActive(array_key_exists('active', $_POST['category']));
        // MUST be either null or integer, or comparisons will fail
        // when building options!
        $parentId = null;
        if (!empty($_POST['category']['parent_id'])) {
            $parentId = intval($_POST['category']['parent_id']);
        }
        $category->setParentId($parentId);
        if ($category->getId()) {
            $category->setUpdated(new \DateTime);
        }
        // DISABLED: entry_ids is not part of the view.
        // Note: If the key exists, its value must be an array
        if (array_key_exists('entry_ids', $_POST['category'])) {
            $entry_ids_posted = array_flip($_POST['category']['entry_ids']);
            $entry_ids_category = array_flip(array_map(
                    function($entry) {
                    return $entry->getId();
                }, $category->getEntries()->toArray()));
            $entryRepo = $em->getRepository(
            $this->getNamespace()
                . '\\Model\\Entity\\Entry');
            $entries = $entryRepo->findAll();
            foreach ($entries as $entry) {
                if (
                    array_key_exists($entry->getId(), $entry_ids_posted) && !array_key_exists($entry->getId(),
                        $entry_ids_category)
                ) {
                    $category->addEntry($entry);
                }
                if (
                    !array_key_exists($entry->getId(), $entry_ids_posted) && array_key_exists($entry->getId(),
                        $entry_ids_category)
                ) {
                    // Note that for bidirectional m:n, the relation
                    // MUST be broken on both sides!
                    $category->getEntries()->removeElement($entry);
                    $entry->getCategories()->removeElement($category);
                }
            }
        }
        $frontendLanguages = self::getFrontendLanguagesOrderedForStoring();
        foreach ($frontendLanguages as $language) {
            $language_id = $language['id'];
            $locale = \FWLanguage::getLocaleByFrontendId($language_id);
            // Skip this locale altogether if the name is empty.
            // The name is required in order to form the (non-null) slug!
            $name = $_POST['category']['name'][$locale];
            if (!$name) {
                continue;
            }
            $description = $_POST['category']['description'][$locale];
            // NOTE: In order to get a proper slug, it is not sufficient
            // to just add the translations.
            // Instead, the entity must be persisted/flushed for each locale.
            // The default locale is processed last, so that its data
            // is present in the main entry.
            $category->setTranslatableLocale($locale);
            $category->setName($name);
            $category->setDescription($description);
            $em->persist($category); // Only effective for new Categories
            try {
                $em->flush();
            } catch (\Exception $e) {
                $e = null; // Unused
                return \Message::error(
                    $_ARRAYLANG['TXT_MODULE_TOPICS_CATEGORY_ERROR_STORING_FAILED']);
            }
        }
        return \Message::ok(
            $_ARRAYLANG['TXT_MODULE_TOPICS_CATEGORY_STORED_SUCCESSFULLY']);
    }

    /**
     * Returns an array of Category names, indexed by their respective IDs
     *
     * This is solely intended for generating the parent Category options.
     * Note that the given Category itself, and any of its subcategories
     * are not present in the array returned, as assigning one of these
     * is not allowed.  This would result in a broken relation, or a
     * circular reference, or both.
     * @param   \Cx\Modules\Topics\Model\Entity\Category    $category
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function getCategoryOptions(
        \Cx\Modules\Topics\Model\Entity\Category $category)
    {
        $categoryRepo = $this->cx->getDb()->getEntityManager()
            ->getRepository(
                $this->getNamespace()
                . '\\Model\\Entity\\Category');
        $categories = $categoryRepo->findAll();
        usort($categories, function($a, $b) {
            return strcmp($a->getSlug(), $b->getSlug());
        });
        $children = array();
        foreach ($categories as $child) {
            if (!array_key_exists($child->getParentId(), $children)) {
                $children[$child->getParentId()] = array();
            }
            $children[$child->getParentId()][] = $child;
        }
        // Options must be custom crafted, as they contain HTML entities.
        // These would be escaped by \HTML::getOptions().
        $options = '';
        self::addCategoryOptions($options, $children, $category);
        return $options;
    }

    /**
     * Append HTML options for the Categories below $parentId
     *
     * Hides the branch of the current $category, and selects its parent.
     * See {@link getCategoryOptions()} for details.
     * @param   string      $options    The options being built, by reference
     * @param   array       $children   The array of child Categories, by parent ID
     * @param   \Cx\Modules\Topics\Model\Entity\Category    $category
     *                                  The Category being edited
     * @param   type        $parentId   The ID of the current parent Category
     * @param   type        $level      The current recursion level
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function addCategoryOptions(&$options, array $children,
        \Cx\Modules\Topics\Model\Entity\Category $category, $parentId = null,
        $level = 0)
    {
        if (!array_key_exists($parentId, $children)) {
            return;
        }
        foreach ($children[$parentId] as $child) {
            // The Category itself and all of its children MUST NOT be
            // available for selection!
            if ($category->getId() === $child->getId()) {
                continue;
            }
            $options .=
                '<option value="' . $child->getId() . '"'
                . ($category->getParentId() === $child->getId()
                    ? \Html::ATTRIBUTE_SELECTED : '')
                . '>'
                . str_repeat('&nbsp;&nbsp;&nbsp;', $level)
                . contrexx_raw2xhtml($child->getName())
                . '</option>';
            self::addCategoryOptions(
                $options, $children, $category, $child->getId(), $level + 1);
        }
    }

    /**
     * Returns an array of active frontend languages
     *
     * The array is ordered for proper storing of Entries and Categories,
     * so that the default frontend language is last.
     * @return  array
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getFrontendLanguagesOrderedForStoring()
    {
        $activeFrontendLanguages = \FWLanguage::getActiveFrontendLanguages();
        $init = \Env::get('init');
        $defaultFrontendLanguageId = $init->getDefaultFrontendLangId();
        $languagesOrdered = array();
        foreach ($activeFrontendLanguages as $language) {
            if ($language['id'] === $defaultFrontendLanguageId) {
                array_push($languagesOrdered, $language);
            } else {
                array_unshift($languagesOrdered, $language);
            }
        }
        return $languagesOrdered;
    }

    /**
     * This function returns the ViewGeneration options for a given entityClass
     * @global  type    $_ARRAYLANG
     * @param   string  $entityClassName
     * @param   string  $dataSetIdentifier
     * @return  array                       The ViewGenerator options
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    /**
     *
     * @global type $_ARRAYLANG
     * @return type
     */
    protected function getViewGeneratorOptions($entityClassName,
        $dataSetIdentifier = '')
    {
        global $_ARRAYLANG;
        $entityClassName = $dataSetIdentifier = null; // Unused
        return array(
            $this->getNamespace() . '\\Model\\Entity\\Entry' => array(
                'header' => $_ARRAYLANG['TXT_MODULE_TOPICS_ACT_ENTRY'],
                'fields' => array(
                    'id' => array(),
                    'active' => array(),
                    'name' => array(),
                    'slug' => array(),
                    'description' => array(
                        'type' => 'text',
                        'showOverview' => false,
// Note: Custom formatting in list view:
// See documentation: http://wiki.contrexx.com/en/index.php?title=View_Autogeneration#Define_callback
//  'table' => array('parse' => *callable*: *mixed*($value, $rowData), ),
// E.g.:
//    function($description) {
//        return ComponentController::shorten(
//                strip_tags($description), 50);
//    },
// Note: Custom formatting in detail view:
//  'formfield' => *callable*: *mixed*($fieldname, $fieldtype, $fieldlength, $fieldvalue, $fieldoptions),
                    ),
                    'categories' => array(
                        'showOverview' => false,
                    ),
                    'created' => array(
// Note: Suppress fields in detail view:
//  'showDetail' => *boolean*,
                    ),
                    'updated' => array(
//  'showDetail' => *boolean*,
                    ),
                ),
                'functions' => array(
                    'add' => true,
                    'edit' => true,
                    'delete' => true,
                    'sorting' => true,
                    'paging' => true,
// TODO: Implement filter and search
                    'filtering' => false,
                    'searching' => false,
                ),
            ),
            $this->getNamespace() . '\\Model\\Entity\\Category' => array(
                'header' => $_ARRAYLANG['TXT_MODULE_TOPICS_ACT_CATEGORY'],
                'functions' => array(
                    'add' => true,
                    'edit' => true,
                    'delete' => true,
                    'sorting' => true,
                    'paging' => true,
// TODO: Implement filter and search
                    'filtering' => false,
                    'searching' => false,
                ),
                'fields' => array(
                    'description' => array(
                        'showOverview' => false,
                    ),
                ),
            ),
        );
    }

}
