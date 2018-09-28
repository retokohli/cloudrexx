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

/**
 * Main controller for Shop
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_shop
 */

namespace Cx\Modules\Shop\Controller;

/**
 * Main controller for Shop
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_shop
 */
class ComponentController extends \Cx\Core\Core\Model\Entity\SystemComponentController {

    /**
     * Return a list of Controller Classes.
     * @return array
     */
    public function getControllerClasses()
    {
        return array('Backend');
    }

    /**
     * Load your component. It is needed for this request.
     *
     * This loads your Frontend or BackendController depending on the
     * mode Cx runs in. For modes other than frontend and backend, nothing is
     * done. This method is overwritten because the frontend view is loaded
     * without frontend controller and directly with the ShopManager
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page The resolved page
     */
    public function load(\Cx\Core\ContentManager\Model\Entity\Page $page)
    {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                $page->setContent(Shop::getPage($page->getContent()));

                // show product title if the user is on the product details page
                $metaTitle = Shop::getPageTitle();
                if ($metaTitle) {
                    $page->setTitle($metaTitle);
                    $page->setContentTitle($metaTitle);
                    $page->setMetaTitle($metaTitle);
                }
                $metaDesc = Shop::getPageMetaDesc();
                if ($metaDesc) {
                    $page->setMetadesc($metaDesc);
                }
                $metaImage = Shop::getPageMetaImage();
                if ($metaImage) {
                    $page->setMetaimage($metaImage);
                }
                $metaKeys = Shop::getPageMetaKeys();
                if ($metaKeys) {
                    $page->setMetakeys($metaKeys);
                }
                break;

            case \Cx\Core\Core\Controller\Cx::MODE_BACKEND:
                parent::load($page);
                break;
        }
    }

    /**
     * Do something after content is loaded from DB
     *
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page       The resolved page
     */
    public function postContentLoad(\Cx\Core\ContentManager\Model\Entity\Page $page) {
        switch ($this->cx->getMode()) {
            case \Cx\Core\Core\Controller\Cx::MODE_FRONTEND:
                // Show the Shop navbar in the Shop, or on every page if configured to do so
                if (!Shop::isInitialized()
                // Optionally limit to the first instance
                // && MODULE_INDEX == ''
                ) {
                    \Cx\Core\Setting\Controller\Setting::init('Shop', 'config');
                    if (\Cx\Core\Setting\Controller\Setting::getValue('shopnavbar_on_all_pages', 'Shop')) {
                        Shop::init();
                        Shop::setNavbar();
                    }

                    // replace global product blocks
                    $page->setContent(
                        preg_replace_callback(
                            '/<!--\s+BEGIN\s+(block_shop_products_category_(?:\d+)\s+-->).*<!--\s+END\s+\1/s',
                            function ($matches) {
                                $blockTemplate = new \Cx\Core\Html\Sigma();
                                $blockTemplate->setTemplate($matches[0]);
                                Shop::parse_products_blocks($blockTemplate);
                                return $blockTemplate->get();
                            },
                            $page->getContent()
                        )
                    );
                }
                break;
        }
    }

    /**
     * Called for additional, component specific resolving
     * 
     * If /en/Path/to/Page is the path to a page for this component
     * a request like /en/Path/to/Page/with/some/parameters will
     * give an array like array('with', 'some', 'parameters') for $parts
     * 
     * This may be used to redirect to another page
     * @param array $parts List of additional path parts
     * @param \Cx\Core\ContentManager\Model\Entity\Page $page Resolved virtual page
     */
    public function resolve($parts, $page) {
        $canonicalUrl = \Cx\Core\Routing\Url::fromPage($page, $this->cx->getRequest()->getUrl()->getParamArray());
        header('Link: <' . $canonicalUrl->toString() . '>; rel="canonical"');
    }

    /**
     * {@inheritdoc}
     */
    public function adjustResponse(\Cx\Core\Routing\Model\Entity\Response $response) {
        // in case of an ESI request, the request URL will be set through Referer-header
        $headers = $response->getRequest()->getHeaders();
        if (isset($headers['Referer'])) {
            $refUrl = new \Cx\Lib\Net\Model\Entity\Url($headers['Referer']);
        } else {
            $refUrl = new \Cx\Lib\Net\Model\Entity\Url($response->getRequest()->getUrl()->toString());
        }

        $page   = $response->getPage();
        $params = $refUrl->getParamArray();
        unset($params['section']);
        unset($params['cmd']);
        $canonicalUrl = \Cx\Core\Routing\Url::fromPage($page, $params);
        $response->setHeader(
            'Link',
            '<' . $canonicalUrl->toString() . '>; rel="canonical"'
        );

        if (
            !$page ||
            $page->getModule() !== $this->getName() ||
            !in_array(
                $page->getCmd(),
                array('', 'details', 'lastFive', 'products')
            )
        ) {
            return;
        }

        Shop::getPage('');
        // show product title if the user is on the product details page
        $page_metatitle = Shop::getPageTitle();
        if ($page_metatitle) {
            $page->setTitle($page_metatitle);
            $page->setContentTitle($page_metatitle);
            $page->setMetaTitle($page_metatitle);
        }

        $metaImage = Shop::getPageMetaImage();
        if ($metaImage) {
            $page->setMetaimage($metaImage);
        }
    }

    /**
     * Register your event listeners here
     *
     * USE CAREFULLY, DO NOT DO ANYTHING COSTLY HERE!
     * CALCULATE YOUR STUFF AS LATE AS POSSIBLE.
     * Keep in mind, that you can also register your events later.
     * Do not do anything else here than initializing your event listeners and
     * list statements like
     * $this->cx->getEvents()->addEventListener($eventName, $listener);
     */
    public function registerEventListeners() {
        $eventListener = new \Cx\Modules\Shop\Model\Event\ShopEventListener($this->cx);
        $this->cx->getEvents()->addEventListener('SearchFindContent',$eventListener);
        $this->cx->getEvents()->addEventListener('mediasource.load', $eventListener);
    }

    public function preFinalize(\Cx\Core\Html\Sigma $template)
    {
        if (    $this->cx->getMode()
            !== \Cx\Core\Core\Controller\Cx::MODE_FRONTEND) {
            return;
        }
        Shop::parse_products_blocks($template);
    }

}
