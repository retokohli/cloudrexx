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
 * Topics ImportController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     comvation
 * @subpackage  module_topics
 */
class ImportController extends \Cx\Core\Core\Model\Entity\Controller
{
    protected static $source_url_path = 'http://www.vbv.ch/upload/Lexikon/';
    /**
     * FTTB, only language codes are supported,
     * and used in place of the locale.
     *
     * Mind that the order is relevant here; German, the default locale,
     * is processed last in order to have these values left in the
     * Entries after adding all the other translations first.
     * @var array Mapping of letters indicating the language to locale
     */
    protected static $locales = array(
        'e' => 'en', 'f' => 'fr', 'i' => 'it',
        'd' => 'de',
    );
    /**
     * Collection of known Topics HTML source files
     * Note that
     *  b_1_d.htm
     *  b_1_e.htm
     *  b_1_f.htm
     *  b_1_i.htm
     * contain the IDs that refer to all individual entries in the respective
     * language.
     * Other files are included in order to extract translations of all labels
     * for the view.
     * @var array All known HTML files, used to extract text and links
     */
    protected static $prerequisite_file_names = array(
        // Note that
        //  http://www.vbv.ch/upload/Lexikon/
        //  http://www.vbv.ch/upload/Lexikon/index.html
        // both point to the same content.
        'index.html',
        'abc.htm',
        // Entries ("Begriffe") per Category ("Lexikon")
        //  <select onchange="javascript:top.laden(this.name)" name="L">
        //    <option>Globales Lexikon</option> <!-- 1, ID n/a -->
        //    <option>Personenversicherungen</option> <!-- 2, ID 1 -->
        //    <option>Sachversicherungen</option> <!-- 3, ID 2 -->
        //    <option>Haftpflichtversicherungen</option> <!-- 4, ID 3 -->
        //    <option>Motorfahrzeugversicherungen</option> <!-- 5, ID 4 -->
        //    <option>Fonds / Finanz</option> <!-- 6, ID 5 -->
        //    <option>Versicherungsrecht</option> <!-- 7, ID 6 -->
        //  </select>
        'b_1_d.htm', 'b_1_e.htm', 'b_1_f.htm', 'b_1_i.htm',
        // Other Lexica {@see assignCategories()}:
        //  'b_2_d.htm', [...], 'b_7_i.htm',
        'e.htm',
        'head_d.htm',
        'head_e.htm',
        'head_f.htm',
        'head_i.htm',
        'topn_d.htm',
        'topn_f.htm',
        'topn_i.htm',
        'topn_e.htm',
    );
    /**
     * @var array Stores reference => ID
     */
    protected static $ids = array();
    /**
     * @var array Stores reference => description
     */
    protected static $descriptions = array();
    /**
     * @var array Stores reference => name
     */
    protected static $names = array();

    /**
     * OBSOLETE since Sluggable is used.
     * @var array Stores used slugs in keys
     */
//    protected static $slugs = array();
    /**
     * Import all data from the original site
     *
     * Download necessary original topics files,
     * then store all entries, including translations
     * @param   \Cx\Core\Html\Sigma $template   Template containing content
     *                                          of resolved page
     * @param   string  $cmd                    cmd request parameter value
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        ob_end_flush();
        \DBG::activate(DBG_PHP);
        //\DBG::log("ImportController::parsePage(): DEBUG: Entered");

        $this->createTables();
        self::wgetPrerequisiteFiles();
        self::collectContent();
        self::replaceReferences();
        $this->insertEntries();
        $this->assignCategories();
        $this->updateDescriptions();
        global $_ARRAYLANG;
        \Message::ok($_ARRAYLANG['TXT_MODULE_TOPICS_ACT_IMPORT_COMPLETE']);
        // Intentionally unused
        $template->getCurrentBlock(); // Object must not be modified
        $cmd = null;
    }

    /**
     * Download all content HTML files
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function wgetPrerequisiteFiles()
    {
        foreach (self::$prerequisite_file_names as $prerequisite_file_name) {
            self::wgetFile($prerequisite_file_name);
        }
    }

    /**
     * Collect all Entries from the global topics
     *
     * Creates an internal list of references that is used in
     * {@link replaceReferences()} to substitute the new IDs.
     * {@link assignCategories()} will assign Entries to their
     * appropriate Categories.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function collectContent()
    {
        // NOTE: All categories have been tested, and it has been confirmed
        // that the current index files contain no references to missing
        // Entries.
        // This is, however, not the case for Entries referenced by
        // descriptions, see {@link updateDescriptions()}.
        $source_folder_path = self::getSourceFolderPath();
        foreach (self::$locales as $language => $locale) {
            $file_name = 'b_1_' . $language . '.htm';
            $file_path = $source_folder_path . $file_name;
            self::wgetFile($file_name);
            $content = file_get_contents($file_path);
            $matches = null;
            preg_match_all('/ladeER\((\d+)\)\'>([^<]+)<\\/A>/', $content,
                $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {//\DBG::log("Match: ".var_export($match, true)."");
                $reference = intval($match[1]);
                //$name = $match[2];
                $file_name = 'e_' . sprintf('%1$04u', $reference)
                    . '_' . $language . '.htm';
                $file_path = $source_folder_path . $file_name;
                self::wgetFile($file_name);
                $content = file_get_contents($file_path);
                self::extractContent($reference, $locale, $content);
            }
        }
    }

    /**
     * Returns the folder path containing the downloaded HTML files
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @return  string
     */
    protected static function getSourceFolderPath()
    {
        return ASCMS_MODULE_PATH . '/Topics/Data/HTML/';
    }

    /**
     * Create entry and translation tables
     *
     * Drops the tables if they already exist.
     * @return void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function createTables()
    {
        $db = $this->cx->getDb()->getAdoDb();
        $sql = file_get_contents(self::getSqlPath());
        // Strip comments
        $sql = preg_replace('/--.+\r?\n/', '', $sql);
        // Split single queries (terminated by ";" and newline),
        // strip whitespaces and empty lines in between.
        foreach (preg_split('/;\r?\n\s*/', $sql, -1, PREG_SPLIT_NO_EMPTY) as
                $query) {
            $db->Execute($query);
        }
    }

    /**
     * Returns the folder path containing the SQL dump
     *
     * This dump drops and creates all necessary tables for the module,
     * including the translation table.
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function getSqlPath()
    {
        return ASCMS_MODULE_PATH . '/Topics/Data/SQL/install.sql';
    }

    /**
     * Retrieve the file from within the source URL path
     *
     * Prints informal and warning messages.
     * @param   string  $file_name          The name of the file
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function wgetFile($file_name)
    {
        $file_path = self::getSourceFolderPath() . $file_name;
        if (is_file($file_path)) {
            return;
        }
        \DBG::log("ImportController::wgetFile(): DEBUG: File missing: $file_name, downloading...");
        $content = file_get_contents(self::$source_url_path . $file_name);
        if (!$content) {
            \DBG::log("ImportController::wgetFile(): WARNING: Empty file: $file_name, try again");
            return;
        }
        // Mind the encoding (apparently ISO-8859-1-ish)!
        $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        file_put_contents($file_path, $content);
    }

    /**
     * Prepare entry content
     *
     * Extracts name and description from $content, and store them in
     * respective arrays for later use
     * @param   integer $reference  The entry ID
     * @param   integer $locale     The locale
     * @param   string  $content    The raw HTML content
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function extractContent($reference, $locale, $content)
    {
        // Detect duplicate references; this SHOULD NOT happen.
        if (
            array_key_exists($reference, self::$descriptions)
            && array_key_exists($locale, self::$descriptions[$reference])) {
            \DBG::log("ImportController::extractContent(): DEBUG: extractContent($reference, $locale, \$content): WARNING: Already processed, skipping");
        }

        // Sample of input being matched:
        //  [Anything up to the opening body tag,
        //  including the doctype, html, and head tags, is ignored]
        //    <body background='hg_lex.gif'>
        //      <b>Abredeversicherung</b><BR><BR>
        //      Bei einer Abredeversicherung bietet der [...]
        //      <A href='javascript:top.ladeErQuer(557)'>Fondsleitung</A>
        //      [... Content may be multiline!]
        //    </body>
        //  [Anything following the closing body tag,
        //  including the closing html tag, is ignored]
        $match = null;
        // Mind the modifiers:
        // /i is added for safety only; apparently all tag cases are identical.
        // /s in order to match newlines with "."
        // /u for multibyte characters in the subject
        $content = preg_match(
            // Opening body tag with attributes
            '/<body[^>]*>\s*'
            // Entry name in bold, two line breaks
            . '<b>\s*([^<]+?)\s*<\\/b><BR><BR>\s*'
            // Entry description
            . '(.+?)'
            // Closing body tag
            . '\s*<\\/body>/isu', $content, $match);
        if (empty($match[1]) || empty($match[2])) {
            \DBG::log("ImportController::extractContent(): ERROR: Failed to match content of reference $reference, locale $locale");
        }
        self::$names[$reference][$locale] = $match[1];
        // Fix known invalid or malformed HTML
        $description = preg_replace('/<br>/iu', '<br />', $match[2]);
        self::$descriptions[$reference][$locale] = $description;
//\DBG::log("ImportController::extractContent(): DEBUG: Added $reference / {$names[$reference]} / {$descriptions[$reference]}");
    }

    /**
     * Replace references in descriptions with temporary pseudo-links
     *
     * The pseudo-links store the target Entry ID as a data-id attribute.
     * These are replaced again by proper Entry slugs in
     * {@link updateDescriptions()}.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected static function replaceReferences()
    {
        $substitutes = $overlaps = array();
        // First, remap the old references to new, consecutive IDs
        $id = 0;
        foreach (array_keys(self::$descriptions) as $reference) {
            self::$ids[$reference] = ++$id;
        }
        // Search and replace links by span elements,
        // replacing references with IDs
        foreach (self::$descriptions as $reference => $descriptionLocales) {
            foreach ($descriptionLocales as $locale => $description) {
//\DBG::log("ImportController::replaceReferences(): DEBUG: Found $reference / $locale");
                $matches = null;
                // Match and clean up links like
                //  <A href='javascript:top.ladeErQuer(557)'>Fondsleitung</A>
                // Mind the modifiers:
                // /i is added for safety only; apparently all tag cases are identical.
                // /u for multibyte characters in the subject
                preg_match_all(
                    '/<A href=\'javascript:top\.ladeErQuer\((\d+)\)\'>'
                    . '\s*([^<]+?)\s*'
                    . '<\\/A>/iu', $description, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $reference_link = intval($match[1]);
                    $name_link = $match[2];
                    // Mark invalid/unknown references with negative sign,
                    // see {@link updateDescriptions()}.
                    $id_link = -$reference_link;
                    $name = 'INVALID REFERENCE';
                    if (array_key_exists($reference_link, self::$ids)) {
                        $id_link = self::$ids[$reference_link];
                        $name = self::$names[$reference_link][$locale];
                    } else {
                        \DBG::log("ImportController::replaceReferences(): WARNING: Entry ID " . self::$ids[$reference] . " (ref. $reference), Locale $locale: Found unknown reference $reference_link");
                    }
                    if ($name !== $name_link) {
                        // Store "aliases" used as link text
                        if (!array_key_exists($name, $substitutes)) {
                            $substitutes[$name] = array();
                        }
                        $substitutes[$name][$name_link] = true;
                    }
                    // Store link name and -reference in order to detect
                    // overlapping names being used for multiple references
                    if (!array_key_exists($name_link, $overlaps)) {
                        $overlaps[$name_link] = array();
                    }
                    $overlaps[$name_link][$reference_link] = true;
// TODO: Perhaps the (arbitrary or shortened) link name
// should be replaced with the original name ($name).
// E.g.: "Aufsichtsbehörde (Fonds)" instead of "Aufsichtsbehörde"
// This would solve the problem with overlapping names altogether.
                    $description = str_replace($match[0],
                        // Note that the data-id attribute will be replaced
                        // with the generated slug below, after the Entry
                        // has been flush()ed.
                        '<a data-id="' . $id_link . '">'
                        . $name_link //. $name
                        . '</a>', $description);
                }
                self::$descriptions[$reference][$locale] = $description;
            }
        }
// FOR TESTING: Discover overlaps, and terminate
// Note that these cases may also be handled above by replacing
// $name_link with $name.
//        $success = true;
//        foreach ($overlaps as $name_link => $references) {
//            if (count($references) > 1) {
//                $success = false;
//                \DBG::log("ImportController::replaceReferences(): WARNING: Ambiguous link name $name_link for references "
//                . join(', ', array_keys($references)) . "");
//            }
//        }
//        if (!$success) {
//            die("TERMINATED");
//        }
// Results:
//    WARNING: Ambiguous link name Versicherer for references 354, 352
//    WARNING: Ambiguous link name Versicherungsnehmers for references 368, 364
//    WARNING: Ambiguous link name Aufsichtsbehörde for references 30, 484
//    WARNING: Ambiguous link name IV for references 191, 189
//    WARNING: Ambiguous link name AHV for references 9, 435
//    WARNING: Ambiguous link name Obligationen for references 619, 237
//    WARNING: Ambiguous link name Rendite for references 274, 629
//    WARNING: Ambiguous link name Risiko for references 278, 655
//    WARNING: Ambiguous link name Vorsorge for references 390, 136
//    WARNING: Ambiguous link name Rechtsschutzversicherung for references 270, 917
//    WARNING: Ambiguous link name Bonität for references 499, 60
//    WARNING: Ambiguous link name Immobilienfonds for references 578, 699
//    WARNING: Ambiguous link name Säule 3a for references 930, 143
//    WARNING: Ambiguous link name Säule 3b for references 931, 136
//    WARNING: Ambiguous link name Volatilität for references 705, 655
//    WARNING: Ambiguous link name Unfallversicherung for references 967, 238
//    WARNING: Ambiguous link name obligatorischen Krankenpflegeversicherung for references 906, 0
//    WARNING: Ambiguous link name Kündigung for references 739, 593
// FOR TESTING: List substitutes (and terminate?)
// Note that this array also contains an index for "INVALID REFERENCE"!
//    foreach ($substitutes as $name => $names) {
//        if (count($names) > 1) {
//            \DBG::log("ImportController::replaceReferences(): DEBUG: Name $name: Substitute link names "
//            . join(', ', array_keys($names)));
//        }
//    }
// INVALID REFERENCES:
// salaire, responsabilité extracontractuelle, assicurazioni sulla vita,
// Federal Banking Commission, Commission fédérale des banques,
// Commissione federale delle banche, Eidgenössische Bankenkommission,
// prime, Financial loss, facility owner's liability, comptes individuels,
// 'assurance privée, expense ratio, vol, Investment Fund Act,
// loi suisse sur les fonds de placement,
// legge svizzera sui fondi dinvestimento, Anlagefondsgesetz, risk,
// Loi sur les fonds de placement, Legge federale sui fondi,
// Anlagefondsgesetzes, rendita, Assurance technique, Assicurazione tecnica,
// le premier pilier, responsabilità civile extracontrattuale,
// Assurance choses entreprise, Assurance transport,
// Federal Office of Private Insurance, Office fédéral des assurances privées,
// Ufficio federale delle assicurazioni private,
// Bundesamt für Privatversicherungen, financial requirements,
// financial loss, Coûts indirects, (non-life) premium, prime (Non-vie),
// Prämie (Nicht-Leben), elementi naturali, Moveables., negligenza grave,
// balance sheet, Machinery insurance, commercial third-party insurance,
// régime surobligatoire, prévoyance privée, 3ème pilier,
// l'assurance RC d'entreprise, insurer.,
// obligatorischen Krankenpflegeversicherung,
// Agevolazioni del diritto successorio, processus de travail, mobilier,
// immobilier, insurance, assurance des machines, assurance maladie (LA-Mal),
// régime surobligatoire de la prévoyance professionnelle,
// l'Office fédéral des assurances privées, Federal Banking Commission (FBC),
// Commission fédérale des banques (CFB),
// Commissione federale delle banche (CFB),
// Eidgenössische Bankenkommission (EBK), Tax deduction, Tax privileges,
// provision, restricted, pension provision, unrestricted pension
//
//\DBG::log("ImportController::replaceReferences(): DEBUG: Done.");
    }

    /**
     * Create all entries, and persist them
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function insertEntries()
    {
//\DBG::log("ImportController::insertEntries(): DEBUG: Entered");
        $created_at = new \DateTime();
        $em = $this->cx->getDb()->getEntityManager();
        $entryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Entry');
        foreach (self::$locales as $locale) {
            foreach (self::$descriptions as $reference => $descriptionLocales) {
                $id = self::$ids[$reference];
                $entry = $entryRepo->find($id);
                if (!$entry) {
                    $entry = new \Cx\Modules\Topics\Model\Entity\Entry;
                    $entry->__setId($id);
                    $entry->setActive(true);
                    $entry->setCreated($created_at);
                    $entry->setUpdated(null);
                }
                $description = $descriptionLocales[$locale];
                $name = self::$names[$reference][$locale];
                $entry->setDescription($description);
                $entry->setName($name);
// OBSOLETE since Sluggable is used.
//                $slug = self::sluggify($name);
//                $entry->setSlug($slug);
                $entry->setTranslatableLocale($locale);
                $em->persist($entry);
                // Note that the slug is only set on flush(),
                // not after persist()!
//\DBG::log("ImportController::insertEntries(): DEBUG: persisted ID $id / ref $reference / loc $locale / name $name");
            }
            $em->flush();
        }
//\DBG::log("ImportController::insertEntries(): DEBUG: Done.");
    }

    /**
     * Assign Categories
     *
     * Fetch all references to Entries from the other Lexica (Categories),
     * and add relations in the database.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function assignCategories()
    {
//\DBG::log("ImportController::assignCategories(): DEBUG: Entered");
        // NOTE: As the first topics/Category contains all Entries,
        // no Category is created for it.
        // Aktuelles Lexikon (Kategorien)
        $names = array(
            'de' => array(
                1 => 'Globales Lexikon',
                'Personenversicherungen',
                'Sachversicherungen',
                'Haftpflichtversicherungen',
                'Motorfahrzeugversicherungen',
                'Fonds / Finanz',
                'Versicherungsrecht',
            ),
            'fr' => array(
                1 => 'Glossaire général',
                'Assurances de personnes',
                'Assurances choses',
                'Assurances Responsabilité Civile',
                'Assurances Véhicules à moteur',
                'Fonds / Finance',
                'Droit des assurances',
            ),
            'it' => array(
                1 => 'Glossario generale',
                'Assicurazioni di persone',
                'Assicurazioni cose',
                'Assicurazioni Responsabilità civile',
                'Assicurazioni veicoli a motore',
                'Fonds / Finanziarie',
                'Diritto d\'assicurazioni',
            ),
            'en' => array(
                1 => 'Global glossary',
                'Insurances of persons',
                'Insurances of objects',
                'Third-party liability insurances',
                'insurances of motor vehicles',
                'Fonds / Finance',
                'Insurance law',
            ),
        );
        $created_at = new \DateTime();
        $source_folder_path = self::getSourceFolderPath();
        $em = $this->cx->getDb()->getEntityManager();
        $entryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Entry');
        $categoryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Category');
        foreach (self::$locales as $language => $locale) {
//\DBG::log("ImportController::assignCategories(): DEBUG: $language / $locale");
            foreach (range(2, 7) as $topics) {
                $category = $categoryRepo->find($topics - 1);
                if (!$category) {
                    $category = new \Cx\Modules\Topics\Model\Entity\Category;
                    $category->setActive(true);
                    $category->setCreated($created_at);
                    $category->setUpdated(null);
                }
                $name = $names[$locale][$topics];
                $category->setDescription(null);
                $category->setName($name);
// OBSOLETE since Sluggable is used.
//                $category->setSlug(self::sluggify($name));
                $category->setTranslatableLocale($locale);
                $em->persist($category);
            }
            $category = null;
            try {
                $em->flush();
            } catch (\Exception $e) {
                die("ImportController::assignCategories(): EXCEPTION: " .
                    $e->getMessage() . ', ' . $e->getFile() . ', ' . $e->getLine());
            }
        }

        // After translating, Category ID 5 french locale is missing.
        // Apparently this happens because of its slug being identical
        // to the english one, although there is no unique index on that.
        // TODO: Fix the slug translation issue, and remove the following query:
        $adoConnection = $this->cx->getDb()->getAdoDb();
        $adoConnection->Execute("
            INSERT INTO `contrexx_translations`
                (`id`, `locale`, `object_class`, `field`, `foreign_key`, `content`)
            VALUES
                (null, 'fr', 'Cx\\\\Modules\\\\Topics\\\\Model\\\\Entity\\\\Category', 'name', '5', 'Fonds / Finance'),
                (null, 'fr', 'Cx\\\\Modules\\\\Topics\\\\Model\\\\Entity\\\\Category', 'slug', '5', 'fonds-finance'),
                (null, 'fr', 'Cx\\\\Modules\\\\Topics\\\\Model\\\\Entity\\\\Category', 'description', '5', NULL)");
        // Necessary! Without clearing the EM, a server error will occur later.
        $em->clear();

        // NOTE: This part COULD be integrated in the above loop,
        // however, for some unknown reason, PHP-cgi terminates on flush().
        // Possibly there is some Doctrine problem causing it.
        foreach (range(2, 7) as $topics) {
            $category = $categoryRepo->find($topics - 1);
            if (!$category) {
                \DBG::log("ImportController::assignCategories(): ERROR: Could not load Category ID " . ($topics - 1));
                continue;
            }
//\DBG::log("ImportController::assignCategories(): DEBUG: Loaded Category $topics, ID " . $category->getId());
            // Note that The entries are only assigned according to
            // the first language processed (German)!
            $language = 'd';
            $file_name = 'b_' . $topics . '_' . $language . '.htm';
            $file_path = $source_folder_path . $file_name;
            self::wgetFile($file_name);
            $content = file_get_contents($file_path);
            $matches = null;
            //  <a href="javascript:top.ladeER(765)" name="765">Adäquater Kausalzusammenhang</a>
            preg_match_all('/ladeER\((\d+)\)\'>([^<]+)<\\/A>/', $content,
                $matches, PREG_SET_ORDER);
            $entry_ids = array();
            foreach ($matches as $match) {//\DBG::log("Match: ".var_export($match, true)."");
                $reference = intval($match[1]);
                if (!array_key_exists($reference, self::$ids)) {
                    \DBG::log("ImportController::assignCategories(): ERROR: Reference $reference unknown");
                    continue;
                }
                $entry_ids[] = self::$ids[$reference];
            }
//\DBG::log("ImportController::assignCategories(): DEBUG: " . count($entry_ids) . " Entry IDs in Category $topics");//. ": " . join(', ', $entry_ids));
            $qb = $entryRepo->createQueryBuilder('entry');
            $qb->where($qb->expr()->in('entry.id', $entry_ids));
//\DBG::log("ImportController::assignCategories(): DEBUG: Query: " . $qb->getDQL());
            // setEntries() will only accept a Collection!
            $entries = new \Doctrine\Common\Collections\ArrayCollection(
                $qb->getQuery()->getResult()
            );
//\DBG::log("ImportController::assignCategories(): DEBUG: " . count($entries) . " Entries found");
            $category->setEntries($entries);
//\DBG::log("ImportController::assignCategories(): DEBUG: set Entries");
            try {
                $em->flush();
            } catch (\Exception $e) {
                die("ImportController::assignCategories(): EXCEPTION: " .
                    $e->getMessage() . ', ' . $e->getFile() . ', ' . $e->getLine());
            }
//\DBG::log("ImportController::assignCategories(): DEBUG: Added Entries to Category $topics, ID " . $category->getId());
        }
    }

    /**
     * OBSOLETE since Sluggable is used.
     * Returns a unique slug for the given string
     *
     * Replaces any characters outside the ranges 0-9, A-Z, and a-z
     * by a single minus sign.
     * Any resulting two or more consecutive minus signs
     * are then squashed into one.
     * If the resulting slug has already been used, appends an incrementing
     * integer number in parentheses, starting at 1.
     * @param   string  $string     The string
     * @return  string              The slug version of $string
     * @author  Reto Kohli <reto.kohli@comvation.com>
      protected static function sluggify($string)
      {
      $slug = preg_replace('/-{2,}/', '-',
      preg_replace('/[^0-9A-Za-z]/', '-',
      strtolower(utf8_decode($string)))
      );
      $slug_unique = $slug;
      $i = 0;
      while (array_key_exists($slug_unique, self::$slugs)) {
      $slug_unique = $slug . ' (' . ++$i . ')';
      }
      self::$slugs[$slug_unique] = true;
      return $slug_unique;
      }
     */

    /**
     * Update all cross-reference links in localized descriptions
     *
     * Replaces <a> tags referencing the target Entry by ID in data attributes
     * with complete href attributes using slugs.
     */
    protected function updateDescriptions()
    {
        $em = $this->cx->getDb()->getEntityManager();
        $entryRepo = $em->getRepository(
            'Cx\\Modules\\Topics\\Model\\Entity\\Entry');
        foreach (self::$locales as $locale) {
            $em->clear();
            $this->cx->getDb()->getTranslationListener()
                ->setTranslatableLocale($locale);
            $query = $entryRepo->createQueryBuilder('entry')
                ->getQuery();
            $entries = $query->getResult();
            foreach ($entries as $entry) {
//\DBG::log("ImportController::updateDescriptions(): DEBUG: Locale $locale, Entry ID {$entry->getId()}, name {$entry->getName()}");
                $matches = null;
                $description = $entry->getDescription();
                if (preg_match_all(
                        // match indices:
                        //  0 => entire <a> element (to be replaced)
                        //  1 => Entry ID
                        //  2 => Entry link content
                    '/<a data-id="(-?\d+)">([^<]+)<\\/a>/',
                    $description, $matches, PREG_SET_ORDER)) {
//\DBG::log("ImportController::updateDescriptions(): DEBUG: Matches: ".htmlentities(var_export($matches, true)));
                    foreach ($matches as $match) {
//\DBG::log("ImportController::updateDescriptions(): DEBUG: Match: ".htmlentities(var_export($match, true)));
                        $link = $match[0];
                        $id = $match[1];
                        $content = $match[2];
//\DBG::log("ImportController::updateDescriptions(): DEBUG: link ".htmlentities($link).", id $id, content $content");
                        $entry_target = $slug = null;
                        if ($id > 0) {
                            $entry_target = $entryRepo->find($id);
                            $slug = $entry_target->getSlug();
                        }
                        // Catch broken references, i.e.
                        // <A href='javascript:top.ladeErQuer(0)'>Financial loss</A>
                        if ($entry_target) {
                            // OK, noop
                        } else {
                            // This SHOULD NOT occur, but some references
                            // within Entry descriptions are broken!
                            // Note that broken references are detected in
                            // replaceReferences(), and their data-id is set
                            // to the reference number with inverse sign.
                            $slug = '#invalid-reference-' . abs($id);
                            // The references output here are identical with
                            // the ones already reported in replaceReferences().
                            //\DBG::log("ImportController::updateDescriptions(): WARNING: Entry ID " . $entry->getId() . ", Locale $locale: Missing target Entry " . ($id > 0 ? "ID " . $id : "reference " . -$id));
                        }
                        $description = str_replace($link,
                            // Relative links that are completed with the
                            // current view URL in the browser, e.g.
                            //  "ahv" => "http://vbv.ch/en/Topics//it/de/ahv"
                            '<a href="' . $slug . '">' . $content . '</a>',
                            $description);
                    }
//\DBG::log("ImportController::updateDescriptions(): DEBUG: Updating description: ".htmlentities($description, ENT_QUOTES, CONTREXX_CHARSET));
                }

                // One last thing:
                // Search for, and replace ill-encoded characters, as in i.e.:
                //  "Limporto investito è rimborsato al 100%."
                $_description = str_replace(array(
                    // representing an aigu (´), possibly mac encoded,
                    // then converted to utf8 in wgetfile().
                    // Should be an apostrophe!
                    chr(194) . chr(146),
                    ), array(
                    '\'',
                    ), $description);
                if ($_description !== $description) {
                    //\DBG::log("ImportController::updateDescriptions(): DEBUG: description: $description => $_description");
                    $description = $_description;
                }

                $entry->setDescription($description);
            }
            $em->flush();
        }
    }

}
