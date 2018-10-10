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
 * Cx\Modules\EasyTemp\Model\Entity\Application
 */
class Application extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $anrede
     */
    private $anrede;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $vorname
     */
    private $vorname;

    /**
     * @var string $zusatzadresse
     */
    private $zusatzadresse;

    /**
     * @var string $strasse
     */
    private $strasse;

    /**
     * @var string $plz
     */
    private $plz;

    /**
     * @var string $ort
     */
    private $ort;

    /**
     * @var string $staat
     */
    private $staat;

    /**
     * @var string $geschlecht
     */
    private $geschlecht;

    /**
     * @var string $zivilstand
     */
    private $zivilstand;

    /**
     * @var string $heimatort
     */
    private $heimatort;

    /**
     * @var string $geburtsdatum
     */
    private $geburtsdatum;

    /**
     * @var string $muttersprache
     */
    private $muttersprache;

    /**
     * @var string $fahrzeug
     */
    private $fahrzeug;

    /**
     * @var string $fuehrerschein
     */
    private $fuehrerschein;

    /**
     * @var string $telefong
     */
    private $telefong;

    /**
     * @var string $telefonp
     */
    private $telefonp;

    /**
     * @var string $telefonm
     */
    private $telefonm;

    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string $sozialversnr
     */
    private $sozialversnr;

    /**
     * @var string $kuendigungsfrist
     */
    private $kuendigungsfrist;

    /**
     * @var string $verfuegbarab
     */
    private $verfuegbarab;

    /**
     * @var string $bewilstatus
     */
    private $bewilstatus;

    /**
     * @var string $bewilnr
     */
    private $bewilnr;

    /**
     * @var string $bewilverfall
     */
    private $bewilverfall;

    /**
     * @var string $beruferlernt
     */
    private $beruferlernt;

    /**
     * @var string $wunschregion
     */
    private $wunschregion;

    /**
     * @var string $wunschberuf
     */
    private $wunschberuf;

    /**
     * @var string $wunschbranche
     */
    private $wunschbranche;

    /**
     * @var string $bemerkung
     */
    private $bemerkung;

    /**
     * @var string $sprache
     */
    private $sprache;

    /**
     * @var string $anstellungsgrad
     */
    private $anstellungsgrad;

    /**
     * @var string $anstellungsart
     */
    private $anstellungsart;

    /**
     * @var string $noattachment
     */
    private $noattachment;

    /**
     * @var string $file1
     */
    private $file1;

    /**
     * @var string $file2
     */
    private $file2;

    /**
     * @var string $file3
     */
    private $file3;


    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set anrede
     *
     * @param string $anrede
     */
    public function setAnrede($anrede)
    {
        $this->anrede = $anrede;
    }

    /**
     * Get anrede
     *
     * @return string $anrede
     */
    public function getAnrede()
    {
        return $this->anrede;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set vorname
     *
     * @param string $vorname
     */
    public function setVorname($vorname)
    {
        $this->vorname = $vorname;
    }

    /**
     * Get vorname
     *
     * @return string $vorname
     */
    public function getVorname()
    {
        return $this->vorname;
    }

    /**
     * Set zusatzadresse
     *
     * @param string $zusatzadresse
     */
    public function setZusatzadresse($zusatzadresse)
    {
        $this->zusatzadresse = $zusatzadresse;
    }

    /**
     * Get zusatzadresse
     *
     * @return string $zusatzadresse
     */
    public function getZusatzadresse()
    {
        return $this->zusatzadresse;
    }

    /**
     * Set strasse
     *
     * @param string $strasse
     */
    public function setStrasse($strasse)
    {
        $this->strasse = $strasse;
    }

    /**
     * Get strasse
     *
     * @return string $strasse
     */
    public function getStrasse()
    {
        return $this->strasse;
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
     * Set staat
     *
     * @param string $staat
     */
    public function setStaat($staat)
    {
        $this->staat = $staat;
    }

    /**
     * Get staat
     *
     * @return string $staat
     */
    public function getStaat()
    {
        return $this->staat;
    }

    /**
     * Set geschlecht
     *
     * @param string $geschlecht
     */
    public function setGeschlecht($geschlecht)
    {
        $this->geschlecht = $geschlecht;
    }

    /**
     * Get geschlecht
     *
     * @return string $geschlecht
     */
    public function getGeschlecht()
    {
        return $this->geschlecht;
    }

    /**
     * Set zivilstand
     *
     * @param string $zivilstand
     */
    public function setZivilstand($zivilstand)
    {
        $this->zivilstand = $zivilstand;
    }

    /**
     * Get zivilstand
     *
     * @return string $zivilstand
     */
    public function getZivilstand()
    {
        return $this->zivilstand;
    }

    /**
     * Set heimatort
     *
     * @param string $heimatort
     */
    public function setHeimatort($heimatort)
    {
        $this->heimatort = $heimatort;
    }

    /**
     * Get heimatort
     *
     * @return string $heimatort
     */
    public function getHeimatort()
    {
        return $this->heimatort;
    }

    /**
     * Set geburtsdatum
     *
     * @param string $geburtsdatum
     */
    public function setGeburtsdatum($geburtsdatum)
    {
        $this->geburtsdatum = $geburtsdatum;
    }

    /**
     * Get geburtsdatum
     *
     * @return string $geburtsdatum
     */
    public function getGeburtsdatum()
    {
        return $this->geburtsdatum;
    }

    /**
     * Set muttersprache
     *
     * @param string $muttersprache
     */
    public function setMuttersprache($muttersprache)
    {
        $this->muttersprache = $muttersprache;
    }

    /**
     * Get muttersprache
     *
     * @return string $muttersprache
     */
    public function getMuttersprache()
    {
        return $this->muttersprache;
    }

    /**
     * Set fahrzeug
     *
     * @param string $fahrzeug
     */
    public function setFahrzeug($fahrzeug)
    {
        $this->fahrzeug = $fahrzeug;
    }

    /**
     * Get fahrzeug
     *
     * @return string $fahrzeug
     */
    public function getFahrzeug()
    {
        return $this->fahrzeug;
    }

    /**
     * Set fuehrerschein
     *
     * @param string $fuehrerschein
     */
    public function setFuehrerschein($fuehrerschein)
    {
        $this->fuehrerschein = $fuehrerschein;
    }

    /**
     * Get fuehrerschein
     *
     * @return string $fuehrerschein
     */
    public function getFuehrerschein()
    {
        return $this->fuehrerschein;
    }

    /**
     * Set telefong
     *
     * @param string $telefong
     */
    public function setTelefong($telefong)
    {
        $this->telefong = $telefong;
    }

    /**
     * Get telefong
     *
     * @return string $telefong
     */
    public function getTelefong()
    {
        return $this->telefong;
    }

    /**
     * Set telefonp
     *
     * @param string $telefonp
     */
    public function setTelefonp($telefonp)
    {
        $this->telefonp = $telefonp;
    }

    /**
     * Get telefonp
     *
     * @return string $telefonp
     */
    public function getTelefonp()
    {
        return $this->telefonp;
    }

    /**
     * Set telefonm
     *
     * @param string $telefonm
     */
    public function setTelefonm($telefonm)
    {
        $this->telefonm = $telefonm;
    }

    /**
     * Get telefonm
     *
     * @return string $telefonm
     */
    public function getTelefonm()
    {
        return $this->telefonm;
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
     * Set sozialversnr
     *
     * @param string $sozialversnr
     */
    public function setSozialversnr($sozialversnr)
    {
        $this->sozialversnr = $sozialversnr;
    }

    /**
     * Get sozialversnr
     *
     * @return string $sozialversnr
     */
    public function getSozialversnr()
    {
        return $this->sozialversnr;
    }

    /**
     * Set kuendigungsfrist
     *
     * @param string $kuendigungsfrist
     */
    public function setKuendigungsfrist($kuendigungsfrist)
    {
        $this->kuendigungsfrist = $kuendigungsfrist;
    }

    /**
     * Get kuendigungsfrist
     *
     * @return string $kuendigungsfrist
     */
    public function getKuendigungsfrist()
    {
        return $this->kuendigungsfrist;
    }

    /**
     * Set verfuegbarab
     *
     * @param string $verfuegbarab
     */
    public function setVerfuegbarab($verfuegbarab)
    {
        $this->verfuegbarab = $verfuegbarab;
    }

    /**
     * Get verfuegbarab
     *
     * @return string $verfuegbarab
     */
    public function getVerfuegbarab()
    {
        return $this->verfuegbarab;
    }

    /**
     * Set bewilstatus
     *
     * @param string $bewilstatus
     */
    public function setBewilstatus($bewilstatus)
    {
        $this->bewilstatus = $bewilstatus;
    }

    /**
     * Get bewilstatus
     *
     * @return string $bewilstatus
     */
    public function getBewilstatus()
    {
        return $this->bewilstatus;
    }

    /**
     * Set bewilnr
     *
     * @param string $bewilnr
     */
    public function setBewilnr($bewilnr)
    {
        $this->bewilnr = $bewilnr;
    }

    /**
     * Get bewilnr
     *
     * @return string $bewilnr
     */
    public function getBewilnr()
    {
        return $this->bewilnr;
    }

    /**
     * Set bewilverfall
     *
     * @param string $bewilverfall
     */
    public function setBewilverfall($bewilverfall)
    {
        $this->bewilverfall = $bewilverfall;
    }

    /**
     * Get bewilverfall
     *
     * @return string $bewilverfall
     */
    public function getBewilverfall()
    {
        return $this->bewilverfall;
    }

    /**
     * Set beruferlernt
     *
     * @param string $beruferlernt
     */
    public function setBeruferlernt($beruferlernt)
    {
        $this->beruferlernt = $beruferlernt;
    }

    /**
     * Get beruferlernt
     *
     * @return string $beruferlernt
     */
    public function getBeruferlernt()
    {
        return $this->beruferlernt;
    }

    /**
     * Set wunschregion
     *
     * @param string $wunschregion
     */
    public function setWunschregion($wunschregion)
    {
        $this->wunschregion = $wunschregion;
    }

    /**
     * Get wunschregion
     *
     * @return string $wunschregion
     */
    public function getWunschregion()
    {
        return $this->wunschregion;
    }

    /**
     * Set wunschberuf
     *
     * @param string $wunschberuf
     */
    public function setWunschberuf($wunschberuf)
    {
        $this->wunschberuf = $wunschberuf;
    }

    /**
     * Get wunschberuf
     *
     * @return string $wunschberuf
     */
    public function getWunschberuf()
    {
        return $this->wunschberuf;
    }

    /**
     * Set wunschbranche
     *
     * @param string $wunschbranche
     */
    public function setWunschbranche($wunschbranche)
    {
        $this->wunschbranche = $wunschbranche;
    }

    /**
     * Get wunschbranche
     *
     * @return string $wunschbranche
     */
    public function getWunschbranche()
    {
        return $this->wunschbranche;
    }

    /**
     * Set bemerkung
     *
     * @param string $bemerkung
     */
    public function setBemerkung($bemerkung)
    {
        $this->bemerkung = $bemerkung;
    }

    /**
     * Get bemerkung
     *
     * @return string $bemerkung
     */
    public function getBemerkung()
    {
        return $this->bemerkung;
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
     * Set noattachment
     *
     * @param string $noattachment
     */
    public function setNoattachment($noattachment)
    {
        $this->noattachment = $noattachment;
    }

    /**
     * Get noattachment
     *
     * @return string $noattachment
     */
    public function getNoattachment()
    {
        return $this->noattachment;
    }

    /**
     * Set file1
     *
     * @param string $file1
     */
    public function setFile1($file1)
    {
        $this->file1 = $file1;
    }

    /**
     * Get file1
     *
     * @return string $file1
     */
    public function getFile1()
    {
        return $this->file1;
    }

    /**
     * Set file2
     *
     * @param string $file2
     */
    public function setFile2($file2)
    {
        $this->file2 = $file2;
    }

    /**
     * Get file2
     *
     * @return string $file2
     */
    public function getFile2()
    {
        return $this->file2;
    }

    /**
     * Set file3
     *
     * @param string $file3
     */
    public function setFile3($file3)
    {
        $this->file3 = $file3;
    }

    /**
     * Get file3
     *
     * @return string $file3
     */
    public function getFile3()
    {
        return $this->file3;
    }
}
