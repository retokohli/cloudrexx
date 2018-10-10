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
 * ImportController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @copyright   Comvation AG
 * @link        http://www.comvation.com/
 * @package     cloudrexx
 * @subpackage  module_easytemp
 */
class ImportController extends \Cx\Core\Core\Model\Entity\Controller
{
    /**
     * Store error messages
     * @var array
     */
    protected $messages = [];

    /**
     * Stores codes and respective values that have been encountered already.
     *
     * Improves import performance, and provides a complete set of codes
     * that are referenced by at least one Job.
     * @var array
     */
    protected $codeCache = null;

    /**
     * Parse the view
     *
     * Sets the ENTITY_VIEW placeholder of the default template
     * @param   \Cx\Core\Html\Sigma     $template   Ignored
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd)
    {
        global $_ARRAYLANG;

        if (empty($cmd[1])) {
            $cmd[1] = '<unknown>';
        }
        switch ($cmd[1]) {
            case 'Jobs':
                // /cadmin/EasyTemp/Import/Jobs
                // Both must be run hourly, and in order:
                $this->importJobs();
                $this->updateTextStatus();
                break;
            case 'Applicationcodes':
                // /cadmin/EasyTemp/Import/Applicationcodes
                // Both must be run daily, and in order:
                $this->cleanJobs();
                $this->importApplicationCodes();
                break;
            case 'Jobcodes':
                // /cadmin/EasyTemp/Import/Jobcodes
                $this->importJobCodes();
                break;
            default:
                \Message::error(
                    $_ARRAYLANG['TXT_MODULE_EASYTEMP_IMPORT_ERROR_NO_CLASS']);
                break;
        }
        if ($this->messages) {
            $template->setVariable('MODULE_EASYTEMP_IMPORT_MESSAGES',
                join('<br />', $this->messages));
        } else {
        \Message::ok(sprintf(
            $_ARRAYLANG['TXT_MODULE_EASYTEMP_IMPORT_SUCCESS'], $cmd[1]));
        }
    }

    /**
     * Import Jobs from the EasyTemp server
     *
     * Should be run hourly.
     * After the Import, Jobs no longer present in the source data will be
     * marked as deleted.  See {@see cleanJobs()}.
     * After importing the Jobs, marks unused Text entries as inactive,
     * so that dropdown menus only show options bearing any results.
     * Logs an error Message on failure.
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function importJobs()
    {
        \Cx\Core\Setting\Controller\Setting::init('EasyTemp', 'setting');
        $url = \Cx\Core\Setting\Controller\Setting::getValue('jobs_xml_url');
        if (!$url) {
            $this->logError('TXT_MODULE_EASYTEMP_IMPORT_ERROR_NO_JOBS_URL');
            return false;
        }
        $xmlContent = $this->getContent($url);
        if (!$xmlContent) {
            $this->logError('TXT_MODULE_EASYTEMP_IMPORT_ERROR_EMPTY_JOBS_XML');
            return false;
        }
        $em = $this->cx->getDb()->getEntityManager();
        $jobRepo = $em->getRepository(
            '\\' . $this->getNamespace() . '\\Model\\Entity\\Job');
        if (!$jobRepo) {
            $this->logError('TXT_MODULE_EASYTEMP_IMPORT_ERROR_NO_JOB_REPOSITORY');
            return false;
        }
        $qb = $jobRepo->createQueryBuilder('job');
        $qb->update('\\' . $this->getNamespace() . '\\Model\\Entity\\Job', 'job')
            ->set('job.deleted', true);
        $q = $qb->getQuery();
        try {
            $q->execute();
        } catch (\Exception $e) {
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
        $xml = new \SimpleXMLIterator($xmlContent);
        // Mind: current() will yield NULL unless you rewind()!
        $xml->rewind();
        $inserate = $xml->getChildren();
        $inserate->rewind();
        $imported = 0;
        while ($inserate->valid()) {
            $inserat = $inserate->current();
            $job = $jobRepo->find($inserat->HASH);
            if (!$job) {
                $job = new \Cx\Modules\EasyTemp\Model\Entity\Job;
                $job->setCreated(new \DateTime);
            }
            $job->setOrganisationid($inserat->ORGANISATIONID);
            $job->setHash($inserat->HASH); //$job->setHash(uniqid());
            $job->setInseratid($inserat->INSERATID); //$job->setInseratid(uniqid());
            $job->setFirma($inserat->FIRMA);
            $job->setTitel($inserat->TITEL);
            $job->setVorspann($inserat->VORSPANN);
            $job->setBeruf($inserat->BERUF);
            $job->setText($inserat->TEXT);
            $job->setArtderarbeit($inserat->ARTDERARBEIT);
            $job->setPlz($inserat->PLZ);
            $job->setOrt($inserat->ORT);
            $job->setFilialenr($inserat->FILIALENR);
            $job->setKontakt($inserat->KONTAKT);
            $job->setTelefon($inserat->TELEFON);
            $job->setEmail($inserat->EMAIL);
            $job->setUrl($inserat->URL);
            $job->setDirektUrl($inserat->DIREKT_URL);
            $job->setDirektUrlPostArgs($inserat->DIREKT_URL_POST_ARGS);
            $job->setBewerbenUrl($inserat->BEWERBEN_URL);
            $job->setLayout($inserat->LAYOUT);
            $job->setLogo($inserat->LOGO);
            $job->setRegion(
                $this->codeToString('job-region', $inserat->REGION));
            $job->setRubrikid(
                $this->codeToString('job-rubrikid', $inserat->RUBRIKID));
            $job->setPosition(
                $this->codeToString('job-position', $inserat->POSITION));
            $job->setBranche(
                $this->codeToString('job-branche', $inserat->BRANCHE));
            // Ensure that there are no spaces in a comma separated list,
            // as the filter presumes.  See JobRepository::addCriteria().
            $job->setKategorie(empty($inserat->KATEGORIE)
                ? null : str_replace(' ', '', $inserat->KATEGORIE));
            $job->setAnstellungsgrad($inserat->ANSTELLUNGSGRAD);
            $job->setAnstellungsgradBis($inserat->ANSTELLUNGSGRAD_BIS);
            $job->setAnstellungsart(
                $this->codeToString('job-anstellungsart',
                    $inserat->ANSTELLUNGSART));
            $job->setEintritt($inserat->EINTRITT);
            $job->setSprache(
                $this->codeToString('job-sprache', $inserat->SPRACHE));
            $job->setSprachekenntnisKandidat(
                $this->codeToString('job-sprachekenntnis_kandidat',
                    $inserat->SPRACHEKENNTNIS_KANDIDAT));
            $job->setSprachekenntnisNiveau(
                $this->codeToString('job-sprachekenntnis_niveau',
                    $inserat->SPRACHEKENNTNIS_NIVEAU));
            $job->setBildungsniveau(
                $this->codeToString('job-bildungsniveau',
                    $inserat->BILDUNGSNIVEAU));
            $job->setAlterVon($inserat->ALTER_VON);
            $job->setAlterBis($inserat->ALTER_BIS);
            $job->setBerufserfahrung($this->codeToString('job-berufserfahrung',
                    $inserat->BERUFSERFAHRUNG));
            $job->setBerufserfahrungPosition(
                $this->codeToString('job-berufserfahrung_position',
                    $inserat->BERUFSERFAHRUNG_POSITION));
            $job->setAngebot($inserat->ANGEBOT);
            $job->setDeleted(false);
            $em->persist($job);
            $inserate->next();
            ++$imported;
        }
        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
        return $this->updateTextStatus();
    }

    /**
     * Delete Jobs marked as deleted for good
     *
     * Logs an error Message on failure.
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function cleanJobs()
    {
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->delete('\\' . $this->getNamespace() . '\\Model\\Entity\\Job', 'job')
            ->where($qb->expr()->eq('job.deleted', 1));
        $q = $qb->getQuery();
        try {
            $q->execute();
        } catch (\Exception $e) {
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
        return true;
    }

    /**
     * Mark Text entries not present in the current codeCache as inactive
     *
     * Logs an error Message on failure.
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function updateTextStatus()
    {
        if (empty($this->codeCache)) {
            return true;
        }
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder();
        // All Texts are activated first, in order to avoid empty dropdowns
        $qb
            ->update('\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text')
            ->set('text.active', true);
        $q = $qb->getQuery();
        try {
            $q->execute();
        } catch (\Exception $e) {
            \Env::get('init')->loadLanguageData('EasyTemp');
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
        $qb = $em->createQueryBuilder();
        $qb
            ->update('\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text')
            ->set('text.active', 0);
        $i = 0;
        foreach ($this->codeCache as $code => $ids) {
            $ids = array_keys($ids);
            if (empty($ids)) {
                continue;
            }
            $qb->orWhere(
                $qb->expr()->eq('text.code', ':code' . $i)
                . ' AND '
                . $qb->expr()->notIn('text.id', $ids)
            );
            $qb->setParameter('code' . $i, $code);
            ++$i;
        }
        $q = $qb->getQuery();
        try {
            $q->execute();
        } catch (\Exception $e) {
            \Env::get('init')->loadLanguageData('EasyTemp');
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
            return false;
        }
        return true;
    }

    /**
     * Import Application codes
     *
     * Should be run daily.
     * Deletes all existing Application codes first.
     * Then, deletes any Jobs already marked as deleted.
     * Logs an error Message on failure.
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function importApplicationCodes()
    {
        \Cx\Core\Setting\Controller\Setting::init('EasyTemp', 'setting');
        $url = \Cx\Core\Setting\Controller\Setting::getValue('applicationcodes_xml_url');
        if (!$url) {
            $this->logError('TXT_MODULE_EASYTEMP_IMPORT_ERROR_NO_APPLICATIONCODES_URL');
        }
        $xmlContent = $this->getContent($url);
        if (!$xmlContent) {
            $this->logError('TXT_MODULE_EASYTEMP_IMPORT_ERROR_EMPTY_APPLICATIONCODES_XML');
        }
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->delete('\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text')
            ->where('text.code LIKE :code');
        $qb->setParameter('code', 'application-%');
        $q = $qb->getQuery();
        try {
            $q->execute();
        } catch (\Exception $e) {
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
        }
        $this->cleanJobs();
        $codes = new \SimpleXMLIterator($xmlContent);
        // Mind: current() will yield NULL unless you rewind()!
        $codes->rewind();
        while ($codes->valid()) {
            $code = $codes->current();
            $contents = $code->content;
            $contents->rewind();
            while ($contents->valid()) {
                $content = $contents->current();
                $text = new \Cx\Modules\EasyTemp\Model\Entity\Text;
                $text->setId($content['id']->__toString());
                $text->setCode('application-' . $code['name']->__toString());
                // Mind that there are no translations for these entries (yet)!
                $text->setDe($content->__toString());
                $text->setEn($content->__toString());
                $text->setFr($content->__toString());
                $text->setIt($content->__toString());
                $text->setActive(true);
                $em->persist($text);
                $contents->next();
            }
            $codes->next();
        }
        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return true;
    }

    /**
     * Import Job codes
     *
     * It shouldn't be necessary to run this, except for when the Jobs codes
     * are updated -- which presumably happens next to never.
     * For the initial setup, import the installer dump instead.
     * Deletes all existing Job codes first.
     * Logs an error Message on failure.
     * @return  boolean                 True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function importJobCodes()
    {
        $import_folder = $this->getDirectory() . '/Data/JobCodes/';
        $import_filenames = array(
            'job-region' => 'jobsArbeitsregion_werte.csv',
            'job-rubrikid' => 'jobsrubrikcodes-berufcodes.csv',
            'job-position' => 'jobsPosition_werte.csv',
            'job-branche' => 'jobsBranche_werte.csv',
            'job-anstellungsart' => 'jobsAnstellungsart_werte.csv',
            'job-sprache' => 'jobssprachencodes.csv',
            'job-sprachekenntnis_kandidat' => 'jobsSprachkenntnis_werte.csv',
            'job-sprachekenntnis_niveau' => 'jobsSprachkenntnis_Niveau_werte.csv',
            // The last two are duplicates
            // of rubrikid and position, respectively
            'job-berufserfahrung' => 'jobsrubrikcodes-berufcodes.csv',
            'job-berufserfahrung_position' => 'jobsPosition_werte.csv',
        );
        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->delete('\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text')
            ->where('text.code LIKE :code');
        $qb->setParameter('code', 'job-%');
        $q = $qb->getQuery();
        try {
            $q->execute();
        } catch (\Exception $e) {
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
        }
        foreach ($import_filenames as $code => $filename) {
            // Note that the CSV files are encoded in ISO-8859
            $content = utf8_encode(
                file_get_contents($import_folder . $filename));
            foreach (preg_split('/\r?\n/u', $content, -1, PREG_SPLIT_NO_EMPTY)
            as $line) {
                list($id, $de, $en, $fr, $it) = preg_split('/\s*;\s*/u', $line);
                $text = new \Cx\Modules\EasyTemp\Model\Entity\Text;
                $text->setId($id);
                $text->setCode($code);
                $text->setDe($de);
                $text->setEn($en);
                $text->setFr($fr);
                $text->setIt($it);
                $text->setActive(true);
                $em->persist($text);
            }
        }
        // There is no source for bildungsniveau codes, thus:
        $text = new \Cx\Modules\EasyTemp\Model\Entity\Text;
        $text->setId(1);
        $text->setCode('job-bildungsniveau');
        $text->setDe('Beruflehre/Matura');
        $text->setFr('Beruflehre/Matura');
        $text->setEn('Beruflehre/Matura');
        $text->setIt('Beruflehre/Matura');
        $text->setActive(true);
        $em->persist($text);
        $text = new \Cx\Modules\EasyTemp\Model\Entity\Text;
        $text->setId(193);
        $text->setCode('job-bildungsniveau');
        $text->setDe('Weiterbildungen (inkl. FH)');
        $text->setFr('Weiterbildungen (inkl. FH)');
        $text->setEn('Weiterbildungen (inkl. FH)');
        $text->setIt('Weiterbildungen (inkl. FH)');
        $text->setActive(true);
        $em->persist($text);
        $text = new \Cx\Modules\EasyTemp\Model\Entity\Text;
        $text->setId(543);
        $text->setCode('job-bildungsniveau');
        $text->setDe('Uni/ETH/Fachhochschulen');
        $text->setFr('Uni/ETH/Fachhochschulen');
        $text->setEn('Uni/ETH/Fachhochschulen');
        $text->setIt('Uni/ETH/Fachhochschulen');
        $text->setActive(true);
        $em->persist($text);
        try {
            $em->flush();
        } catch (\Exception $e) {
            $this->logError('TXT_MODULE_EASYTEMP_EXCEPTION_FORMAT',
                $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return true;
    }

    /**
     * Returns the text for the given code and list of IDs
     *
     * Multiple entries are joined by ', ' (comma and space).
     * @param   string  $code       The Text code
     * @param   string  $list       The double-colon separated list of IDs
     * @param   integer $langId     The optional language ID.  Defaults to
     *                              the default frontend language
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function codeToString($code, $list, $langId = null)
    {
        $ids = preg_split('/:/', $list, -1, PREG_SPLIT_NO_EMPTY);
        if (!$ids) {
            return '';
        }
        if (empty($this->codeCache[$code])) {
            $this->codeCache[$code] = [];
        }
        $idsMissing = [];
        foreach ($ids as $id) {
            if (empty($this->codeCache[$code][$id])) {
                $idsMissing[] = $id;
            }
        }
        if ($idsMissing) {
            $this->loadTexts($code, $idsMissing, $langId);
        }
        $joined = '';
        foreach ($ids as $id) {
            if (empty($this->codeCache[$code][$id])) {
                continue;
            }
            $joined .= ($joined ? ', ' : '') . $this->codeCache[$code][$id];
        }
        return $joined;
    }

    /**
     * Load Text entries for the given code and IDs from the database
     * into the codeCache
     * @param   string  $code       The Application or Job code
     * @param   array   $ids        The IDs
     * @param   integer $langId     The optional language ID.  Defaults to
     *                              the default frontend language
     * @return  boolean             True on success, false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    protected function loadTexts($code, array $ids, $langId = null)
    {
        $em = $this->cx->getDb()->getEntityManager();
        $textRepo = $em->getRepository(
            '\\' . $this->getNamespace() . '\\Model\\Entity\\Text');
        if (!$textRepo) {
            $this->logError(
                'TXT_MODULE_EASYTEMP_IMPORT_ERROR_NO_TEXT_REPOSITORY');
        }
        $qb = $em->createQueryBuilder();
        $qb->select('text');
        $qb->from('\\' . $this->getNamespace() . '\\Model\\Entity\\Text', 'text');
        $qb->andWhere($qb->expr()->eq('text.code', ':code'));
        $qb->andWhere($qb->expr()->in('text.id', $ids));
        $qb->setParameter('code', $code);
        $texts = $qb->getQuery()->getResult();
        if (!$langId) {
            // Mind: LANG_ID is not defined when running as a Cronjob
            $langId = \Env::get('init')->getDefaultFrontendLangId();
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
            $this->codeCache[$code][$text->getId()] = $strText;
        }
        return true;
    }

    /**
     * Log the error message
     *
     * Presumes that logging is active.
     * Loads the module language data beforehand.
     * @internal Mind that this method uses pre-PHP7 code for retrieving its
     *      argument values (func_get_args()).
     *      When using PHP7 exclusively,  the "...$substitutions" syntax may
     *      be used instead.
     * @param   string  $message        Language entry index, or arbitrary text
     * @param   string  $substitutions  Any number of optional values
     *                                  (passed on to sprintf())
     * @return  boolean                 False.  Always.
     */
    protected function logError($message, $substitutions='')
    {
        static $_ARRAYLANG = null;

        if (!$_ARRAYLANG) {
            $_ARRAYLANG = \Env::get('init')->loadLanguageData('EasyTemp');
        }
        $arguments = func_get_args();
        if (array_key_exists($arguments[0], $_ARRAYLANG)) {
            $arguments[0] = $_ARRAYLANG[$arguments[0]];
        }
        $message = call_user_func_array('sprintf', $arguments);
        $this->messages[] = $message;
        \DBG::log($message);
        return false;
    }

    /**
     * Returns the response body from the HTTP/GET request to the given URL
     * @param   string  $url
     * @return type
     * @throws \HTTP_Request2_Exception
     */
    protected function getContent($url)
    {
        $xmlContent = null;
        $request = new \HTTP_Request2($url, \HTTP_Request2::METHOD_GET,
            array(
                // Ignore certificate chain error.
                // Necessary if the server isn't configured to access
                // the CA certificate
                'ssl_verify_peer' => false,
                // Ignore wrong subject (common name).
                // Only necessary if the certificate is indeed invalid
                //'ssl_verify_host' => false,
            ));
        try {
            $response = $request->send();
            if ($response->getStatus() === 200) {
                $xmlContent = $response->getBody();
            }
        } catch (\HTTP_Request2_Exception $exception) {
            throw $exception;
        }
        return $xmlContent;
    }

}
