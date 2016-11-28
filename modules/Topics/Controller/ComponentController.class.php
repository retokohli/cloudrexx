<?php
/**
 * ComponentController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */

namespace Cx\Modules\Topics\Controller;

/**
 * ComponentController
 * @author      Reto Kohli <reto.kohli@comvation.com>
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController
{
    /**
     * Returns all Controller class names for this component (except this)
     * @return array List of Controller class names (without namespace)
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Why is this method called three(!) times?!
     */
    public function getControllerClasses()
    {
        return array(
            'Frontend', 'Backend', 'Json', 'Settings',
            // Enable custom "Import" when available and required.
            //'Import',
        );
    }

    /**
     * Returns a list of command mode commands provided by this component
     * @return array List of command names
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getCommandsForCommandMode()
    {
        return array('TopicsEntries');
    }

    /**
     * Returns a list of JsonAdapter class names
     * @return array List of ComponentController classes
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getControllersAccessableByJson()
    {
        return array('JsonController');
    }

    /**
     * Returns a description for the given command
     * @param   string  $command
     * @param   boolean $short      Return the short version if true
     *                              (and if available)
     * @return  string
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function getCommandDescription($command, $short = false)
    {
        $short = false; // Ignored
        switch ($command) {
            case 'TopicsEntries':
                return 'Send Topics Entries parsed into HTML'
                    . ' according to the given href parameter value';
        }
    }

    /**
     * Register the mediabrowser.load event listener
     */
    public function registerEventListeners()
    {
        $this->cx->getEvents()->addEventListener(
            'mediabrowser.load',
            new \Cx\Modules\Topics\Model\Event\TopicsEventListener($this->cx)
        );
    }

    /**
     * Unused
     *
     * The resolve() method is not sufficient for the Topics's purposes,
     * as the $parts parameter does not contain the system language:
     *  "If /en/Path/to/Page is the path to a page for this component
     *  a request like /en/Path/to/Page/with/some/parameters will
     *  give an array like array('with', 'some', 'parameters') for $parts
     * And it's not even called in API mode.
     * @param array $parts List of additional path parts
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved virtual page
     */
    public function resolve($parts, $page)
    {
        $parts = $page = null;
    }

    /**
     * Execute an API command
     *
     * Similar to the problem with the resolve() method, the API URL does
     * not contain the system language.
     * Thus, the custom "href" parameter is used to hand along the entire
     * URL required to build any supported view.
     * @param   string  $command        The command name
     * @param   array   $arguments      The arguments
     * @param   array   $dataArguments  The optional additional arguments
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public function executeCommand($command, $arguments,
        $dataArguments = array())
    {
        switch ($command) {
            case 'TopicsEntries':
                $_SERVER['REQUEST_URI'] = (isset($_REQUEST['href'])
                    ? $_REQUEST['href'] : '');
                $theme_folder = (isset($_REQUEST['theme_folder']) ?
                        $_REQUEST['theme_folder'] : null);
                $parameters = new \Cx\Modules\Topics\Entity\FrontendParameter($this->cx);
                $frontendLangId = \FWLanguage::getFrontendIdByLocale($parameters->getLocaleSystem());
                // $_ARRAYLANG has not been initialized in API mode.
                $init = \Env::get('init');
                $init->getComponentSpecificLanguageData('Topics', true, $frontendLangId);
                $template_path = $this->getTemplatePath($theme_folder);
                if (!$template_path) {
                    // Use the default theme if the given folder
                    // is empty or invalid
                    $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
                    $theme_folder = $themeRepo->getDefaultTheme(
                            \Cx\Core\View\Model\Entity\Theme::THEME_TYPE_WEB,
                            $frontendLangId)
                        ->getFoldername();
                    $template_path = $this->getTemplatePath($theme_folder);
                }
                if (!$template_path) {
                    // Use the default template from the module folder
                    $template_path = $this->getDefaultTemplatePath();
                }
                $template = new \Cx\Core\Html\Sigma();
                $template->loadTemplateFile($template_path);
                $controller = $this->getController('Frontend');
                if ($parameters->getSlugEntry()) {
                    $controller->showEntry($template, $parameters);
                    die($template->get('topics_detail_entry'));
                }
                // Must set globals (used in URLs)
                $controller->parseGlobals($template, $parameters);
                $controller->showEntries($template, $parameters);
                die(preg_replace('/\\s\\s+/', ' ',
                    $template->get('topics_list_entries')));
        }
        $arguments = $dataArguments = null; // Intentionally unused
    }

    /**
     * Returns a short version of the text with at most maxlen characters
     *
     * If applicable, replaces the chopped text with the language entry
     * TXT_MODULE_TOPICS_SHORTENER (for "[...]").
     * @param   string      $text       The original text
     * @param   integer     $maxlen     The maximum length
     * @return  string                  The shortened text
     * @author  Reto Kohli <reto.kohli@comvation.com>
     */
    public static function shorten($text, $maxlen)
    {
        global $_ARRAYLANG;
        if (strlen($text) <= $maxlen) {
            return $text;
        }
        $shortener = $_ARRAYLANG['TXT_MODULE_TOPICS_SHORTENER'];
        $maxlen -= strlen($shortener);
        while (strlen($text) > $maxlen) {
            $_text = preg_replace('/\s+\S+$/u', '', $text);
            if ($_text === $text) {
                break;
            }
            $text = $_text;
        }
        return $text . $shortener;
    }

    /**
     * Returns the absolute path to the Default.html template file
     *
     * Note that InitCMS::getCurrentThemesPath() is not initialized
     * in API mode.
     * @param   string          $theme_folder
     * @return  string|false                    The path on success,
     *                                          false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Consider generalizing and moving to some core Component
     *          (Cx or Theme class?)
     */
    protected function getTemplatePath($theme_folder)
    {
        return $this->cx->getClassLoader()->getFilePath(
            $this->cx->getWebsiteThemesPath() // e.g., "C:/contrexx/c_vbv/themes"
            . '/' . $theme_folder // e.g., 'skeleton_3_0'
            . '/modules/Topics/Template/Frontend/Default.html'
        );
    }

    /**
     * Returns the absolute path to the Default.html template file
     *
     * Note that InitCMS::getCurrentThemesPath() is not initialized
     * in API mode.
     * @param   string          $theme_folder
     * @return  string|false                    The path on success,
     *                                          false otherwise
     * @author  Reto Kohli <reto.kohli@comvation.com>
     * @todo    Consider generalizing and moving to some core Component
     *          (Cx or Theme class?)
     */
    protected function getDefaultTemplatePath()
    {
        return $this->cx->getClassLoader()->getFilePath(
            $this->cx->getWebsitePath() // e.g., "C:/contrexx/c_vbv/"
            . '/modules/Topics/View/Template/Frontend/Default.html'
        );
    }

}
