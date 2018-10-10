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

namespace Cx\Modules\EasyTemp\Model\Entity;

/**
 * Cx\Modules\EasyTemp\Model\Entity\Job
 */
class Job extends \Cx\Model\Base\EntityBase {
    /**
     * @var string $hash
     */
    private $hash;

    /**
     * @var string $inseratid
     */
    private $inseratid;

    /**
     * @var string $organisationid
     */
    private $organisationid;

    /**
     * @var string $firma
     */
    private $firma;

    /**
     * @var string $titel
     */
    private $titel;

    /**
     * @var string $vorspann
     */
    private $vorspann;

    /**
     * @var string $beruf
     */
    private $beruf;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var string $artderarbeit
     */
    private $artderarbeit;

    /**
     * @var string $plz
     */
    private $plz;

    /**
     * @var string $ort
     */
    private $ort;

    /**
     * @var string $filialenr
     */
    private $filialenr;

    /**
     * @var string $kontakt
     */
    private $kontakt;

    /**
     * @var string $telefon
     */
    private $telefon;

    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string $url
     */
    private $url;

    /**
     * @var string $direkt_url
     */
    private $direkt_url;

    /**
     * @var string $direkt_url_post_args
     */
    private $direkt_url_post_args;

    /**
     * @var string $bewerben_url
     */
    private $bewerben_url;

    /**
     * @var string $layout
     */
    private $layout;

    /**
     * @var string $logo
     */
    private $logo;

    /**
     * @var string $region
     */
    private $region;

    /**
     * @var string $rubrikid
     */
    private $rubrikid;

    /**
     * @var string $position
     */
    private $position;

    /**
     * @var string $branche
     */
    private $branche;

    /**
     * @var string $kategorie
     */
    private $kategorie;

    /**
     * @var string $anstellungsgrad
     */
    private $anstellungsgrad;

    /**
     * @var string $anstellungsgrad_bis
     */
    private $anstellungsgrad_bis;

    /**
     * @var string $anstellungsart
     */
    private $anstellungsart;

    /**
     * @var string $eintritt
     */
    private $eintritt;

    /**
     * @var string $sprache
     */
    private $sprache;

    /**
     * @var string $sprachekenntnis_kandidat
     */
    private $sprachekenntnis_kandidat;

    /**
     * @var string $sprachekenntnis_niveau
     */
    private $sprachekenntnis_niveau;

    /**
     * @var string $bildungsniveau
     */
    private $bildungsniveau;

    /**
     * @var string $alter_von
     */
    private $alter_von;

    /**
     * @var string $alter_bis
     */
    private $alter_bis;

    /**
     * @var string $berufserfahrung
     */
    private $berufserfahrung;

    /**
     * @var string $berufserfahrung_position
     */
    private $berufserfahrung_position;

    /**
     * @var string $angebot
     */
    private $angebot;

    /**
     * @var datetime $created
     */
    private $created;

    /**
     * @var boolean $deleted
     */
    private $deleted;


    /**
     * Set hash
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Get hash
     *
     * @return string $hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set inseratid
     *
     * @param string $inseratid
     */
    public function setInseratid($inseratid)
    {
        $this->inseratid = $inseratid;
    }

    /**
     * Get inseratid
     *
     * @return string $inseratid
     */
    public function getInseratid()
    {
        return $this->inseratid;
    }

    /**
     * Set organisationid
     *
     * @param string $organisationid
     */
    public function setOrganisationid($organisationid)
    {
        $this->organisationid = $organisationid;
    }

    /**
     * Get organisationid
     *
     * @return string $organisationid
     */
    public function getOrganisationid()
    {
        return $this->organisationid;
    }

    /**
     * Set firma
     *
     * @param string $firma
     */
    public function setFirma($firma)
    {
        $this->firma = $firma;
    }

    /**
     * Get firma
     *
     * @return string $firma
     */
    public function getFirma()
    {
        return $this->firma;
    }

    /**
     * Set titel
     *
     * @param string $titel
     */
    public function setTitel($titel)
    {
        $this->titel = $titel;
    }

    /**
     * Get titel
     *
     * @return string $titel
     */
    public function getTitel()
    {
        return $this->titel;
    }

    /**
     * Set vorspann
     *
     * @param string $vorspann
     */
    public function setVorspann($vorspann)
    {
        $this->vorspann = $vorspann;
    }

    /**
     * Get vorspann
     *
     * @return string $vorspann
     */
    public function getVorspann()
    {
        return $this->vorspann;
    }

    /**
     * Set beruf
     *
     * @param string $beruf
     */
    public function setBeruf($beruf)
    {
        $this->beruf = $beruf;
    }

    /**
     * Get beruf
     *
     * @return string $beruf
     */
    public function getBeruf()
    {
        return $this->beruf;
    }

    /**
     * Set text
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set artderarbeit
     *
     * @param string $artderarbeit
     */
    public function setArtderarbeit($artderarbeit)
    {
        $this->artderarbeit = $artderarbeit;
    }

    /**
     * Get artderarbeit
     *
     * @return string $artderarbeit
     */
    public function getArtderarbeit()
    {
        return $this->artderarbeit;
    }

    /**
     * Set plz
     *
     * @param string $plz
     */
    public function setPlz($plz)
    {
        $this->plz = $plz;
    }

    /**
     * Get plz
     *
     * @return string $plz
     */
    public function getPlz()
    {
        return $this->plz;
    }

    /**
     * Set ort
     *
     * @param string $ort
     */
    public function setOrt($ort)
    {
        $this->ort = $ort;
    }

    /**
     * Get ort
     *
     * @return string $ort
     */
    public function getOrt()
    {
        return $this->ort;
    }

    /**
     * Set filialenr
     *
     * @param string $filialenr
     */
    public function setFilialenr($filialenr)
    {
        $this->filialenr = $filialenr;
    }

    /**
     * Get filialenr
     *
     * @return string $filialenr
     */
    public function getFilialenr()
    {
        return $this->filialenr;
    }

    /**
     * Set kontakt
     *
     * @param string $kontakt
     */
    public function setKontakt($kontakt)
    {
        $this->kontakt = $kontakt;
    }

    /**
     * Get kontakt
     *
     * @return string $kontakt
     */
    public function getKontakt()
    {
        return $this->kontakt;
    }

    /**
     * Set telefon
     *
     * @param string $telefon
     */
    public function setTelefon($telefon)
    {
        $this->telefon = $telefon;
    }

    /**
     * Get telefon
     *
     * @return string $telefon
     */
    public function getTelefon()
    {
        return $this->telefon;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set direkt_url
     *
     * @param string $direktUrl
     */
    public function setDirektUrl($direktUrl)
    {
        $this->direkt_url = $direktUrl;
    }

    /**
     * Get direkt_url
     *
     * @return string $direktUrl
     */
    public function getDirektUrl()
    {
        return $this->direkt_url;
    }

    /**
     * Set direkt_url_post_args
     *
     * @param string $direktUrlPostArgs
     */
    public function setDirektUrlPostArgs($direktUrlPostArgs)
    {
        $this->direkt_url_post_args = $direktUrlPostArgs;
    }

    /**
     * Get direkt_url_post_args
     *
     * @return string $direktUrlPostArgs
     */
    public function getDirektUrlPostArgs()
    {
        return $this->direkt_url_post_args;
    }

    /**
     * Set bewerben_url
     *
     * @param string $bewerbenUrl
     */
    public function setBewerbenUrl($bewerbenUrl)
    {
        $this->bewerben_url = $bewerbenUrl;
    }

    /**
     * Get bewerben_url
     *
     * @return string $bewerbenUrl
     */
    public function getBewerbenUrl()
    {
        return $this->bewerben_url;
    }

    /**
     * Set layout
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Get layout
     *
     * @return string $layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Set logo
     *
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Get logo
     *
     * @return string $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set region
     *
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * Get region
     *
     * @return string $region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set rubrikid
     *
     * @param string $rubrikid
     */
    public function setRubrikid($rubrikid)
    {
        $this->rubrikid = $rubrikid;
    }

    /**
     * Get rubrikid
     *
     * @return string $rubrikid
     */
    public function getRubrikid()
    {
        return $this->rubrikid;
    }

    /**
     * Set position
     *
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return string $position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set branche
     *
     * @param string $branche
     */
    public function setBranche($branche)
    {
        $this->branche = $branche;
    }

    /**
     * Get branche
     *
     * @return string $branche
     */
    public function getBranche()
    {
        return $this->branche;
    }

    /**
     * Set kategorie
     *
     * @param string $kategorie
     */
    public function setKategorie($kategorie)
    {
        $this->kategorie = $kategorie;
    }

    /**
     * Get kategorie
     *
     * @return string $kategorie
     */
    public function getKategorie()
    {
        return $this->kategorie;
    }

    /**
     * Set anstellungsgrad
     *
     * @param string $anstellungsgrad
     */
    public function setAnstellungsgrad($anstellungsgrad)
    {
        $this->anstellungsgrad = $anstellungsgrad;
    }

    /**
     * Get anstellungsgrad
     *
     * @return string $anstellungsgrad
     */
    public function getAnstellungsgrad()
    {
        return $this->anstellungsgrad;
    }

    /**
     * Set anstellungsgrad_bis
     *
     * @param string $anstellungsgradBis
     */
    public function setAnstellungsgradBis($anstellungsgradBis)
    {
        $this->anstellungsgrad_bis = $anstellungsgradBis;
    }

    /**
     * Get anstellungsgrad_bis
     *
     * @return string $anstellungsgradBis
     */
    public function getAnstellungsgradBis()
    {
        return $this->anstellungsgrad_bis;
    }

    /**
     * Set anstellungsart
     *
     * @param string $anstellungsart
     */
    public function setAnstellungsart($anstellungsart)
    {
        $this->anstellungsart = $anstellungsart;
    }

    /**
     * Get anstellungsart
     *
     * @return string $anstellungsart
     */
    public function getAnstellungsart()
    {
        return $this->anstellungsart;
    }

    /**
     * Set eintritt
     *
     * @param string $eintritt
     */
    public function setEintritt($eintritt)
    {
        $this->eintritt = $eintritt;
    }

    /**
     * Get eintritt
     *
     * @return string $eintritt
     */
    public function getEintritt()
    {
        return $this->eintritt;
    }

    /**
     * Set sprache
     *
     * @param string $sprache
     */
    public function setSprache($sprache)
    {
        $this->sprache = $sprache;
    }

    /**
     * Get sprache
     *
     * @return string $sprache
     */
    public function getSprache()
    {
        return $this->sprache;
    }

    /**
     * Set sprachekenntnis_kandidat
     *
     * @param string $sprachekenntnisKandidat
     */
    public function setSprachekenntnisKandidat($sprachekenntnisKandidat)
    {
        $this->sprachekenntnis_kandidat = $sprachekenntnisKandidat;
    }

    /**
     * Get sprachekenntnis_kandidat
     *
     * @return string $sprachekenntnisKandidat
     */
    public function getSprachekenntnisKandidat()
    {
        return $this->sprachekenntnis_kandidat;
    }

    /**
     * Set sprachekenntnis_niveau
     *
     * @param string $sprachekenntnisNiveau
     */
    public function setSprachekenntnisNiveau($sprachekenntnisNiveau)
    {
        $this->sprachekenntnis_niveau = $sprachekenntnisNiveau;
    }

    /**
     * Get sprachekenntnis_niveau
     *
     * @return string $sprachekenntnisNiveau
     */
    public function getSprachekenntnisNiveau()
    {
        return $this->sprachekenntnis_niveau;
    }

    /**
     * Set bildungsniveau
     *
     * @param string $bildungsniveau
     */
    public function setBildungsniveau($bildungsniveau)
    {
        $this->bildungsniveau = $bildungsniveau;
    }

    /**
     * Get bildungsniveau
     *
     * @return string $bildungsniveau
     */
    public function getBildungsniveau()
    {
        return $this->bildungsniveau;
    }

    /**
     * Set alter_von
     *
     * @param string $alterVon
     */
    public function setAlterVon($alterVon)
    {
        $this->alter_von = $alterVon;
    }

    /**
     * Get alter_von
     *
     * @return string $alterVon
     */
    public function getAlterVon()
    {
        return $this->alter_von;
    }

    /**
     * Set alter_bis
     *
     * @param string $alterBis
     */
    public function setAlterBis($alterBis)
    {
        $this->alter_bis = $alterBis;
    }

    /**
     * Get alter_bis
     *
     * @return string $alterBis
     */
    public function getAlterBis()
    {
        return $this->alter_bis;
    }

    /**
     * Set berufserfahrung
     *
     * @param string $berufserfahrung
     */
    public function setBerufserfahrung($berufserfahrung)
    {
        $this->berufserfahrung = $berufserfahrung;
    }

    /**
     * Get berufserfahrung
     *
     * @return string $berufserfahrung
     */
    public function getBerufserfahrung()
    {
        return $this->berufserfahrung;
    }

    /**
     * Set berufserfahrung_position
     *
     * @param string $berufserfahrungPosition
     */
    public function setBerufserfahrungPosition($berufserfahrungPosition)
    {
        $this->berufserfahrung_position = $berufserfahrungPosition;
    }

    /**
     * Get berufserfahrung_position
     *
     * @return string $berufserfahrungPosition
     */
    public function getBerufserfahrungPosition()
    {
        return $this->berufserfahrung_position;
    }

    /**
     * Set angebot
     *
     * @param string $angebot
     */
    public function setAngebot($angebot)
    {
        $this->angebot = $angebot;
    }

    /**
     * Get angebot
     *
     * @return string $angebot
     */
    public function getAngebot()
    {
        return $this->angebot;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return boolean $deleted
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Returns a shortened version of the text property
     *
     * Removes any tags from the text property, except for <br>.
     * After that, the value is shortened, if necessary, to at most
     * $maxlen characters, including the ellipsis.
     * If applicable, replaces the chopped text with the language entry
     * TXT_MODULE_EASYTEMP_JOB_TITEL_ELLIPSIS (for '[...]').
     * Note that this method presumes that the text is encoded using
     * CONTREXX_CHARSET.
     * @param   integer     $maxlen     The maximum length, in characters
     * @return  string                  The shortened text
     */
    public function shortenedText($maxlen)
    {
        global $_ARRAYLANG;

        $ellipsis = $_ARRAYLANG['TXT_MODULE_EASYTEMP_JOB_TEXT_ELLIPSIS'];
        $ellipsisEscaped = preg_quote($ellipsis);
        // Removing tags must keep linebreaks, but must not tie words.
        // Also reduce multiple to single whitespace.
        $text = $this->text;
        $text = preg_replace('/<br[^>]*>/', '%LFCR%', $text);
        $text = str_replace('<', ' <', $text);
        $text = preg_replace('/\\s\\s+/', ' ', $text);
        $text = strip_tags($text);
        $text = str_replace('%LFCR%', '<br />', $text);
        if (mb_strlen($text, CONTREXX_CHARSET) > $maxlen) {
            $text .= $ellipsis;
            while (mb_strlen($text, CONTREXX_CHARSET) > $maxlen) {
                $_text = preg_replace(
                    '/\\s*\\S+\\s*(?=' . $ellipsisEscaped . '$)/u', '', $text);
                if ($_text === $text) {
                    break;
                }
                $text = $_text;
            }
        }
        return $text;
    }

}
