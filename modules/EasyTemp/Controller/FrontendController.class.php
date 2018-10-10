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

namespace Cx\Modules\EasyTemp\Controller;

/**
 *
 * Controller for the EasyTemp frontend
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     cloudrexx
 * @subpackage  module_easytemp
 */
class FrontendController extends \Cx\Core\Core\Model\Entity\SystemComponentFrontendController
{
    /**
     * Custom page title
     *
     * Should replace the default page title if non-empty.
     * @var string
     */
    protected $pageTitle = null;
    /**
     * Module settings
     *
     * Initialized in initSettings() in order to avoid calling
     * Settings::init() multiple times
     * @var array
     */
    protected $settings = null;
    /**
     * Names of all module settings used
     * @var array
     */
    protected $settingNames = [
        'application_hash_default',
        'application_post_url',
        'jobs_filter_anstellungsart_active',
        'jobs_filter_anstellungsgrad_active',
        'jobs_filter_anstellungsgrad_bis_active',
        'jobs_filter_berufserfahrung_active',
        'jobs_filter_berufserfahrung_position_active',
        'jobs_filter_bildungsniveau_active',
        'jobs_filter_branche_active',
        'jobs_filter_position_active',
        'jobs_filter_region_active',
        'jobs_filter_rubrikid_active',
        'jobs_filter_sprache_active',
        'jobs_filter_sprachekenntnis_kandidat_active',
        'jobs_filter_sprachekenntnis_niveau_active',
        'jobs_fulltext_artderarbeit_active',
        'jobs_fulltext_beruf_active',
        'jobs_fulltext_firma_active',
        'jobs_fulltext_text_active',
        'jobs_fulltext_titel_active',
        'jobs_fulltext_vorspann_active',
        'jobs_numof_container',
        'jobs_numof_frontend',
        'jobs_organisationid',
    ];
    /**
     * Names of all accepted filter criteria
     *
     * Any key or property not included here will be ignored by the search
     * @var array
     */
    protected $filterCriteria = [
        // General search term, not a Job property
        'term',
        // Primary key; the presence of a valid hash forces detail view
        'hash',
        // Individual filters from here:
        'plz', 'firma', 'titel', 'vorspann',
        'beruf', 'text', 'artderarbeit', 'ort', 'region',
        'rubrikid', 'position', 'branche', 'kategorie', 'anstellungsart',
        'sprache', 'sprachekenntnis_kandidat', 'sprachekenntnis_niveau',
        'bildungsniveau', 'berufserfahrung', 'berufserfahrung_position',
        'anstellungsgrad', 'alter_von', 'anstellungsgrad_bis', 'alter_bis',
        // Ignore:
        //'filialenr', 'kontakt', 'telefon', 'email', 'url',
        //'direkt_url', 'direkt_url_post_args', 'bewerben_url',
        //'layout', 'logo', 'eintritt',
    ];

    /**
     * Initialize module settings
     *
     * Avoids calling \Cx\Core\Setting\Controller\Setting::init()
     * multiple times.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function initSettings()
    {
        if (isset($this->settings)) {
            return;
        }
        \Cx\Core\Setting\Controller\Setting::init('EasyTemp', 'setting');
        foreach ($this->settingNames as $name) {
            $this->settings[$name] =
                \Cx\Core\Setting\Controller\Setting::getValue($name);
        }
    }

    /**
     * Parse the view
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string              $cmd        The cmd parameter value
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, $cmd)
    {
        $this->initSettings();
        // See parseApplication(), sendApplication()
        if (isset($_GET['act'])) {
            $cmd = $_GET['act'];
        }
        if (empty($cmd)) {
            $cmd = '';
        }
        switch ($cmd) {
            case 'applicationSend': // Legacy
            case 'ApplicationSend':
                $this->sendApplication();
                break;
            case 'application': // Legacy
            case 'Application':
                $this->showApplication($template);
                break;
            default:
                $this->showJobs($template);
                break;
        }
        // Show Job title if in the details, or application form views
        $pagetitle = $this->getPageTitle();
        if ($pagetitle) {
            $page = $this->cx->getPage();
            $page->setTitle($pagetitle);
            $page->setContentTitle($pagetitle);
            $page->setMetaTitle($pagetitle);
        }
        \JS::activate('cx'); // Includes jQuery/-UI
    }

    /**
     * Parse the EasyTemp container headlines or search placeholders
     *
     * If the corresponding placeholder is present, reads
     * easytemp-headlines.html and/or easytemp-search.html from the
     * current theme folder, and parses them.
     * Applies the given criteria from the callback widget, as specified in
     * the placeholder, e.g.:
     *    func_easytemp_headlines_file("anstellungsart", "festanstellung")
     * Template blocks and placeholders are identical to those used
     * in {@see parseJobs()}.
     * Parses the resulting content into the EASYTEMP_HEADLINES_FILE
     * or EASYTEMP_SEARCH_FILE placeholder of the template, respectively.
     * @param   string                              $name       Placeholder name
     * @param   \Cx\Core\Html\Sigma                 $template
     * @param   \Cx\Core\View\Model\Entity\Theme    $theme
     * @param   array                               $criteria
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parseContainer($name, \Cx\Core\Html\Sigma $template,
        \Cx\Core\View\Model\Entity\Theme $theme, array $criteria)
    {
        if ($template->placeholderExists('EASYTEMP_HEADLINES_FILE')) {
            $path = $theme->getFilePath(
                $theme->getFolderName() . '/' . 'easytemp-headlines.html');
            $template_file = new \Cx\Core\Html\Sigma;
            $template_file->loadTemplateFile($path);
            $this->initSettings();
            $this->settings['jobs_numof_frontend'] =
                $this->settings['jobs_numof_container'];
            $this->parseJobs($template_file, $criteria, false);
            $template->setVariable($name, $template_file->get());
        }
        if ($template->placeholderExists('EASYTEMP_SEARCH_FILE')) {
            $path = $theme->getFilePath(
                $theme->getFolderName() . '/' . 'easytemp-search.html');
            $template_file = new \Cx\Core\Html\Sigma();//'', '', null, true);
            $template_file->loadTemplateFile($path);
            $this->initSettings();
            if (!$criteria) {
                $criteria = $this->getFilterCriteria(
                    \Cx\Core\Routing\Url::fromRequest()->getParamArray());
            }
            $this->parseJobFilters($template_file, $criteria);
            $template->setVariable($name, $template_file->get());
        }
    }

    /**
     * Set up the Jobs view
     * @global  array               $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function showJobs(\Cx\Core\Html\Sigma $template)
    {
        global $_ARRAYLANG;
        $template->setGlobalVariable($_ARRAYLANG);
        $criteria = $this->getFilterCriteria($_GET);
        $isList = $this->parseJobs($template, $criteria);
        // If the hash is present in the criteria, then it's a detail view.
        // Only display filters in the list view.
        if ($isList) {
            $this->parseJobFilters($template, $criteria);
        }
    }

    /**
     * Return an array of selected filter criteria picked from $_POST
     *
     * Note that not all Job properties are applicable for the filter.
     * Also, empty string values are ignored.
     * You may pass either of $_GET, $_POST, or $_REQUEST as an argument.
     * @param   array   $request    The parameter array from the request
     * @return  array               The filter criteria
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function getFilterCriteria($request)
    {
        $criteria = [];
        foreach (contrexx_input2raw($request) as $name => $value) {
            if (is_array($value)) {
                $value = array_map('urldecode', $value);
            } else {
                $value = urldecode($value);
            }
            // Only accept known names
            if (in_array($name, $this->filterCriteria)) {
                $criteria[$name] = $value;
            }
        }
        return $criteria;
    }

    /**
     * Parse the Job filter form elements
     * @global  array               $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   array               $criteria   The current filter criteria
     * @param   type                $langId     The optional language ID.
     *                                          Defaults to the LANG_ID constant
     *                                          if empty.
     * @return  boolean                         True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parseJobFilters(\Cx\Core\Html\Sigma $template,
        array $criteria, $langId = null)
    {
        global $_ARRAYLANG;

        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->CreateQueryBuilder(
            '\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text');
        if (!$qb) {
            return false;
        }
        $qb->select('text')
            ->from('\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text')
            ->where('text.active=1 AND ' . $qb->expr()->like('text.code', ':job'))
            ->setParameter('job', 'job-%');
        $texts = $qb->getQuery()->getResult();
        $codes = [];
        if (!$langId) {
            $langId = LANG_ID;
        }
        $strText = '';
        foreach ($texts as $text) {
            switch ($langId) {
                case 2:
                    $strText = $text->getEn();
                    break;
                case 3:
                    $strText = $text->getFr();
                    break;
                case 4:
                    $strText = $text->getIt();
                    break;
                // case 1:
                default:
                    $strText = $text->getDe();
                    break;
            }
            $property = preg_replace('/^job-/', '', $text->getCode());
            if (empty($codes[$property])) {
                $codes[$property] = [];
            }
            $codes[$property][$text->getId()] = $strText;
        }
        foreach ($codes as $property => $options) {
            if (!$this->settings['jobs_filter_' . $property . '_active']) {
                continue;
            }
            $options = array_combine($options, $options);
            $upName = strtoupper($property);
            $options = array(
                '' => $_ARRAYLANG['TXT_MODULE_EASYTEMP_FILTER_PLEASE_CHOOSE_' . $upName]
                ) + $options;
            $template->setVariable(array(
                'MODULE_EASYTEMP_FILTER_' . strtoupper($property) =>
                    \Html::getSelect($property, $options,
                        (isset($criteria[$property])
                            ? $criteria[$property] : ''),
                        'filter-' . $property, '', 'tabindex=""'),
                'MODULE_EASYTEMP_FILTER_VALUE_' . strtoupper($property) =>
                    (isset($criteria[$property])
                        ? $criteria[$property] : ''),
            ));
        }
        $template->setVariable(array(
            'MODULE_EASYTEMP_FILTER_TERM' =>
                \Html::getInputText('term',
                    (isset($criteria['term']) ? $criteria['term'] : ''),
                    'filter-term',
                    'tabindex=""'
                    . ' placeholder="'
                    . $_ARRAYLANG['TXT_MODULE_EASYTEMP_FILTER_TERM_PLACEHOLDER']
                    . '"'),
            'MODULE_EASYTEMP_FILTER_VALUE_TERM' =>
                (isset($criteria['term']) ? $criteria['term'] : ''),
        ));
        if (isset($criteria['kategorie'])) {
            $template->setVariable(array(
                'MODULE_EASYTEMP_FILTER_KATEGORIE' =>
                    \Html::getHidden('kategorie', $criteria['kategorie']),
                'MODULE_EASYTEMP_FILTER_VALUE_KATEGORIE' =>
                    (isset($criteria['kategorie'])
                        ? $criteria['kategorie'] : ''),
            ));
        }
        return true;
    }

    /**
     * Find and parse all Jobs matching the filter criteria
     *
     * Returns boolean true if there's no valid hash property set in the
     * criteria.  In other words, when the list view with matching results
     * is shown.
     * For the container view, if the $criteria array contains the key
     * "ignore", both the filter criteria and the Paging offset are ignored.
     * $usePaging is ignored in the detail view.
     * @global  array               $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   array               $criteria   The current filter criteria
     * @param   boolean             $usePaging  Show paging if true.
     *                                          Defaults to true
     * @return  boolean                         True if in list view,
     *                                          false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function parseJobs(\Cx\Core\Html\Sigma $template, $criteria,
        $usePaging = true)
    {
        global $_ARRAYLANG;
        // When the hash parameter value is empty, it's the list view.
        // Otherwise, a distinct Job is selected.
        $isList = empty($criteria['hash']);
        // In case the module settings are not complete
        if (!$this->settings['jobs_numof_frontend']) {
            $this->settings['jobs_numof_frontend'] = 15;
        }
        $offset = 0;
        if (array_key_exists('ignore', $criteria)) {
            $criteria = [];
        } else {
            $offset = \Paging::getPosition('joboffset');
        }
        $order = 'created';
        $direction = 'DESC';
        if ($isList) {
            $criteria['organisationid'] = $this->settings['jobs_organisationid'];
            $criteria['deleted'] = 0;
        }
        $count = null;
        $em = $this->cx->getDb()->getEntityManager();
        $repo = $em->getRepository(
            '\\' . $this->getNamespace() . '\\Model\\Entity\\Job');
        $jobs = $repo->findByCriteria(
            $count, $this->settings, $criteria, $offset,
            $this->settings['jobs_numof_frontend'], $order, $direction);
        if ($count < 1) {
            if ($template->blockExists('easytemp_nojobs')) {
                $template->touchBlock('easytemp_nojobs');
                $template->parse('easytemp_nojobs');
            }
            return $isList;
        }
        if ($isList) {
            if ($template->blockExists('easytemp_detail_job')) {
                $template->hideBlock('easytemp_detail_job');
            }
        } else {
            if ($template->blockExists('easytemp_job')) {
                $template->hideBlock('easytemp_job');
            }
        }
        foreach ($jobs as $job) {
            $arrjob = (array)$job;
            foreach ($arrjob as $name => $value) {
                // Strip private property names, as in
                // '\000Cx\Modules\EasyTemp\Model\Entity\Job\000vorspann'
                $name = preg_replace('/.+?(\w+)$/u', '$1', $name);
                // Properties that need to be formatted
                switch ($name) {
                    case 'created':
                        $value = $value->format(ASCMS_DATE_FORMAT_DATE);
                        break;
                    case 'anstellungsgrad_bis':
                        if ($value === $job->getAnstellungsgrad()) {
                            continue 2;
                        }
                    case 'inseratid':
                        // Strip the organization ID for the short version
                        // (which is the same for all Jobs):
                        // 1234-123456-1-1 => 123456-1-1
                        $template->setVariable(
                            'MODULE_EASYTEMP_JOB_' . strtoupper($name)
                            . '_SHORTENED',
                            preg_replace('/^\d+-/', '', $value)
                        );
                        break;
                }
                // Mind that all text properties come preformatted using
                // HTML tags by the imported data.
                $template->setVariable(
                    'MODULE_EASYTEMP_JOB_' . strtoupper($name),
                    //contrexx_raw2xhtml($value)
                    $value
                );
            }
            $short = $job->shortenedText(255);
            $template->setVariable(
                'MODULE_EASYTEMP_JOB_TEXT_SHORTENED', $short
            );
            if ($isList) {
                $template->parse('easytemp_job');
            } else {
                if ($template->blockExists('easytemp_detail_job')) {
                    $template->parse('easytemp_detail_job');
                }
                $this->pageTitle = $job->getTitel();
            }
        }
        if ($isList && $usePaging) {
            // Pass the query string with the search parameters only, if any
            $query = preg_replace('/^[^?]+\??(.*)$/', '$1',
                \Cx\Core\Routing\Url::fromRequest());
            $template->setGlobalVariable('MODULE_EASYTEMP_PAGING',
                \Paging::get($query,
                    $_ARRAYLANG['TXT_MODULE_EASYTEMP_PAGING'], $count,
                    $this->settings['jobs_numof_frontend'], false,
                    null, 'joboffset')
            );
            if ($template->blockExists('easytemp_paging_top')) {
                $template->touchBlock('easytemp_paging_top');
            }
            if ($template->blockExists('easytemp_paging_bottom')) {
                $template->touchBlock('easytemp_paging_bottom');
            }
            if ($template->blockExists('easytemp_heading')) {
                $template->touchBlock('easytemp_heading');
            }
        }
        \ContrexxJavascript::getInstance()->setVariable(
            'application_base_url',
            \Cx\Core\Routing\Url::fromModuleAndCmd('EasyTemp', 'application')
            ->realPath, 'EasyTemp');
        return $isList;
    }

    /**
     * Set up the Application view
     * @global  array               $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function showApplication(\Cx\Core\Html\Sigma $template)
    {
        global $_ARRAYLANG;

        $template->setGlobalVariable($_ARRAYLANG);
        $hash = null;
        if (isset($_GET['hash'])) {
            $hash = urldecode(contrexx_input2raw($_GET['hash']));
        }
        $this->parseApplication($template, $hash);
    }

    /**
     * Parse the Application form for the Job specified by the given hash
     * @global  array               $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   string              $hash       The optional Job hash
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function parseApplication(\Cx\Core\Html\Sigma $template,
        $hash = null)
    {
        // When the hash parameter value is empty, use the default for
        // spontaneous Applications.
        // Otherwise, a distinct Job is selected.
        if ($hash) {
            $this->parseJobs($template, array('hash' => $hash));
        } else {
            $hash = $this->settings['application_hash_default'];
            // In case the module settings are not complete
            if (!$hash) {
                $hash = 'INVALID';
            }
            $template->setVariable('MODULE_EASYTEMP_JOB_HASH', $hash);
        }
        if (!$this->settings['application_post_url']) {
            $this->settings['application_post_url'] = 'INVALID';
        }
        $this->parseApplicationFields($template);
        \ContrexxJavascript::getInstance()->setVariable(
            'application_post_url',
            // No JSONP / cross site access is permitted.
            \Cx\Core\Routing\Url::fromModuleAndCmd('EasyTemp')
            . '?act=applicationSend', 'EasyTemp');
    }

    /**
     * Parse the elements for the Application form
     * @global  array               $_ARRAYLANG
     * @param   \Cx\Core\Html\Sigma $template
     * @param   type                $langId     The optional language ID.
     *                                          Defaults to the LANG_ID constant
     *                                          if empty.
     * @return  void
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */

    public function parseApplicationFields(\Cx\Core\Html\Sigma $template,
        $langId = null)
    {
        global $_ARRAYLANG;

        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->CreateQueryBuilder(
            '\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text');
        if (!$qb) {
            throw new ImportException('Failed to obtain the Text QueryBuilder');
        }
        $qb->select('text')
            ->from('\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text')
            ->where('text.active=1 AND ' . $qb->expr()->like('text.code',
                    ':application'))
            ->setParameter('application', 'application-%');
        $texts = $qb->getQuery()->getResult();
        $codes = [];
        if (!$langId) {
            $langId = LANG_ID;
        }
        $strText = '';
        foreach ($texts as $text) {
            switch ($langId) {
                case 2:
                    $strText = $text->getEn();
                    break;
                case 3:
                    $strText = $text->getFr();
                    break;
                case 4:
                    $strText = $text->getIt();
                    break;
                // case 1:
                default:
                    $strText = $text->getDe();
                    break;
            }
            if (empty($codes[$text->getCode()])) {
                $codes[$text->getCode()] = [];
            }
            $property = preg_replace('/^application-/', '', $text->getCode());
            $codes[$property][$text->getId()] = $strText;
        }
        // Dropdown select fields
        foreach ($codes as $property => $options) {
            $options = array('' => $_ARRAYLANG['TXT_MODULE_EASYTEMP_APPLICATION_PLEASE_CHOOSE'])
                + $options;
            $template->setVariable(
                'MODULE_EASYTEMP_APPLICATION_' . strtoupper($property),
                \Html::getSelect($property, $options, '',
                    'application-' . $property, '', 'tabindex=""')
            );
        }
        // Text input fields
        foreach (array(
            'name', 'vorname', 'zusatzadresse', 'strasse',
            'plz', 'ort', 'heimatort',
            'geburtsdatum', 'fuehrerschein',
            'telefong', 'telefonp', 'telefonm', 'email',
            'sozialversnr', 'kuendigungsfrist', 'verfuegbarab',
            'bewilnr', 'bewilverfall',
            'beruferlernt', 'anstellungsgrad',
        ) as $property) {
            $template->setVariable(
                'MODULE_EASYTEMP_APPLICATION_' . strtoupper($property),
                \Html::getInputText($property, '',
                    'application-' . $property, 'tabindex=""')
            );
        }
        // Textarea input fields
        foreach (array('bemerkung',) as $property) {
            $template->setVariable(
                'MODULE_EASYTEMP_APPLICATION_' . strtoupper($property),
                \Html::getTextarea($property, '', '', '',
                    'maxlength="1000" tabindex=""')
            );
        }
        // Checkbox fields
        foreach (array('noattachment',) as $property) {
            $template->setVariable(
                'MODULE_EASYTEMP_APPLICATION_' . strtoupper($property),
                \Html::getCheckbox($property, '1', 'application-' . $property,
                    false, '', 'tabindex=""')
            );
        }
        // File upload fields
        foreach (array('file1', 'file2', 'file3',) as $property) {
            $template->setVariable(
                'MODULE_EASYTEMP_APPLICATION_' . strtoupper($property),
                \Html::getInputFileupload($property, 'application-' . $property,
                    '', 'application/pdf', 'tabindex=""')
                . '<button class="removefile" name="removefile"
                    data-file="application-' . $property . '">'
                . $_ARRAYLANG['TXT_MODULE_EASYTEMP_APPLICATION_FILE_REMOVE']
                . '</button>'
            );
        }
    }

    /**
     * Return the current page title
     * @return  string              The page title
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Send the Application form data from the POST request to the
     * EasyTemp server
     *
     * Passes the response through and dies.
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function sendApplication()
    {
        $header = array(
            'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'],
            //'Content-Type: multipart/form-data'
        );
        // Remove Cloudrexx parameter names.
        // EasyTemp does not accept them, and reports a global error.
        unset($_POST['section']);
        unset($_POST['cmd']);
        unset($_POST['csrf']);
        foreach ($_FILES as $parametername => $arrFile) {
            if ($arrFile['error'] !== 0
                || $arrFile['name'] === ''
                || $arrFile['size'] === 0) {
                continue;
            }
            $curlfile = new \CURLFile(
                $arrFile['tmp_name'], 'application/pdf', $arrFile['name']
            );
            $_POST[$parametername] = $curlfile;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->settings['application_post_url']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $_POST);
// In case your webserver is properly set up with
        //  curl.cainfo = 'path_to_cert\cacert.pem'
// already, you can omit this:
        //curl_setopt($curl, CURLOPT_CAINFO,
        //    dirname(dirname(dirname(dirname(__FILE__))))
        //    . '/customizing/cacert.pem');
// Only if the certifiate chain validation does not work properly,
// set this to false:
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        die($response);
    }

}
