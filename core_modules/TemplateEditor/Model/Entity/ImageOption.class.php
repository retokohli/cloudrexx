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
 

namespace Cx\Core_Modules\TemplateEditor\Model\Entity;

/**
 * Class ImageOption
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Robin Glauser <robin.glauser@cloudrexx.com>
 * @package     contrexx
 * @subpackage  core_module_templateeditor
 */
class ImageOption extends Option
{

    /**
     * Url to image
     *
     * @var String
     */
    protected $url;

    /**
     * @param String $name         Name of the option
     * @param array  $translations Array with translations for option.
     * @param array  $data         the specific data for this option
     * @param String $type         the type of the option
     * @param Group  $group        the group of the option
     * @param bool   $series       handle the elements as series if true
     */
    public function __construct(
        $name,
        $translations,
        $data,
        $type,
        $group,
        $series = false
    ) {
        parent::__construct($name, $translations, $data, $type, $group, $series);
        $this->url = $data['url'];
    }

    /**
     * Render the option field in the backend.
     *
     * @return \Cx\Core\Html\Sigma    the template
     */
    public function renderOptionField()
    {
        global $_ARRAYLANG;
        $mediaBrowser =
            new \Cx\Core_Modules\MediaBrowser\Model\Entity\MediaBrowser();
        $mediaBrowser->setOptions(
            array(
                'views' => 'uploader,filebrowser',
                'startView' => 'filebrowser',
            )
        );
        $mediaBrowser->setCallback('callback_' . $this->name);
        return parent::renderOptionField(
            array(
                'TEMPLATEEDITOR_OPTION_VALUE' => $this->getAbsoluteUrl(),
                'MEDIABROWSER_BUTTON'         =>
                    $mediaBrowser->getXHtml(
                        $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_CHANGE_PICTURE']
                    ),
            ),
            $_ARRAYLANG
        );
    }

    /**
     * Render the option in the frontend.
     *
     * @param \Cx\Core\Html\Sigma $template
     */
    public function renderTheme($template)
    {
        $template->setVariable(
            'TEMPLATE_EDITOR_' . strtoupper($this->name),
            contrexx_raw2xhtml($this->getAbsoluteUrl())
        );
    }

    /**
     * Handle a change of the option.
     *
     * @param array $data Data from frontend javascript
     *
     * @return array Changed data for the frontend javascript
     *
     * @throws OptionValueNotValidException If the data which the option should
     *                                      handle is invalid this exception
     *                                      will be thrown.
     *                                      It gets caught by the JsonData
     *                                      class and gets handled by the
     *                                      javascript callback in the frontend.
     */
    public function handleChange($data)
    {
        global $_ARRAYLANG;
        $url = \Cx\Core\Routing\Url::fromMagic($data);
        if (!$url->isInternal()) {
            return array('url' => $data);
        }
        // remove offsetPath and leading slash to ensure installation relocation
        $urlPath = ltrim(
            str_replace(
                $this->cx->getWebsiteOffsetPath(),
                '',
                $data
            ),
            '/'
        );
        if (
            !file_exists($this->cx->getCodeBaseDocumentRootPath() . '/' . $urlPath)
            && !file_exists($this->cx->getWebsitePath() . '/' . $urlPath)
        ) {
            throw new OptionValueNotValidException(
                sprintf(
                    $_ARRAYLANG['TXT_CORE_MODULE_TEMPLATEEDITOR_IMAGE_FILE_NOT_FOUND'],
                    $data
                )
            );
        }
        return array('url' =>  $urlPath);
    }

    /**
     * Get the url
     *
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the url
     *
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Gets the current value of the option.
     *
     * @return array
     */
    public function getValue()
    {
        return array('url' => $this->url);
    }

    /**
     * Return the absoulte url or the absolute url path
     * @return String
     */
    protected function getAbsoluteUrl() {
        if (empty($this->url)) {
            return '';
        }
        // remove the leading slash. It will be added again if url is internal
        $this->url = ltrim($this->url, '/');
        $url = \Cx\Core\Routing\Url::fromMagic($this->url);
        if ($url->isInternal()) {
            return $this->cx->getWebsiteOffsetPath() . '/' . $this->url;
        }
        return $this->url;
    }
}