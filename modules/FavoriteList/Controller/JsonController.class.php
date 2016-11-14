<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2016
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
 * Json
 * Json controller for FavoriteList
 *
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */

namespace Cx\Modules\FavoriteList\Controller;

/**
 * Json
 * Json controller for FavoriteList
 *
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */
class JsonController extends \Cx\Core\Core\Model\Entity\Controller implements \Cx\Core\Json\JsonAdapter
{

    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName()
    {
        return 'FavoriteList';
    }

    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods()
    {
        return array(
            'getCatalog' => new \Cx\Core_Modules\Access\Model\Entity\Permission(null, null, false),
            'addFavorite',
            'removeFavorite',
            'editFavoriteMessage',
        );
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString()
    {
        return implode('<br />', $this->messages);
    }

    /**
     * Returns default permission as object
     * @return Object
     */
    public function getDefaultPermissions()
    {
        return null;
    }

    /**
     * get catalog by session
     *
     * @return json result
     */
    public function getCatalog($data = array())
    {
        $lang = contrexx_input2raw($data['get']['lang']);
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        $_ARRAYLANG = \Env::get('init')->getComponentSpecificLanguageData($this->getName(), true, $langId);

        $themeId = contrexx_input2raw($data['get']['themeId']);
        $theme = $this->getTheme($themeId);
        $templateFile = $theme->getFilePath(strtolower($this->getName()) . '_block_list.html');
        $template = new \Cx\Core\Html\Sigma(dirname($templateFile));
        $template->loadTemplateFile(strtolower($this->getName()) . '_block_list.html');

        $em = $this->cx->getDb()->getEntityManager();
        $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
        $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));

        if (empty($catalog)) {
            $template->setVariable(array(
                strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_NO_CATALOG' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_MESSAGE_NO_CATALOG'],
            ));
            $template->parse(strtolower($this->getName()) . '_block_list_no_catalog');
        } else {
            $favorites = $catalog->getFavorites();
            $template->parse(strtolower($this->getName()) . '_block_list');
            if (!$favorites->count()) {
                $template->setVariable(array(
                    strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_NO_ENTRIES' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_MESSAGE_NO_ENTRIES'],
                ));
                $template->parse(strtolower($this->getName()) . '_block_list_no_entries');
            } else {
                $totalPrice = 0;
                foreach ($favorites as $favorite) {
                    $template->setVariable(array(
                        strtoupper($this->getName()) . '_BLOCK_LIST_ENTITY' => 'favoriteListBlockListEntity',
                        strtoupper($this->getName()) . '_BLOCK_LIST_NAME' => contrexx_raw2xhtml($favorite->getTitle()),
                        strtoupper($this->getName()) . '_BLOCK_LIST_EDIT_LINK' => \Cx\Core\Routing\Url::fromModuleAndCmd($this->getName()) . '/?editid=' . urlencode('{0,' . $favorite->getId() . '}'),
                        strtoupper($this->getName()) . '_BLOCK_LIST_DELETE_ACTION' => 'cx.favoriteListRemoveFavorite(' . $favorite->getId() . ');',
                    ));
                    $template->setVariable(array(
                        strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE' => contrexx_raw2xhtml($favorite->getMessage()),
                        strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_NAME' => 'favoriteListBlockListEntityMessage',
                        strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_ACTION' => 'cx.favoriteListEditFavoriteMessage(' . $favorite->getId() . ', this);',
                        strtoupper($this->getName()) . '_BLOCK_LIST_MESSAGE_SAVE' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_BLOCK_SAVE'],
                    ));
                    $template->parse(strtolower($this->getName()) . '_block_list_row_message');
                    $template->parse(strtolower($this->getName()) . '_block_list_row');
                    $totalPrice += contrexx_raw2xhtml($favorite->getPrice());
                }
                $template->setVariable(array(
                    strtoupper($this->getName()) . '_BLOCK_TOTAL_PRICE' => number_format($totalPrice, 2, '.', '\''),
                    strtoupper($this->getName()) . '_BLOCK_TOTAL_PRICE_LABEL' => $_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName()) . '_BLOCK_TOTAL_PRICE_LABEL'],
                ));
            }
        }

        return $template->get();
    }

    /**
     * adds Favorite to a Catalog
     *
     * @return json result
     */
    public function addFavorite($data = array())
    {
        $lang = contrexx_input2raw($data['get']['lang']);
        $langId = \FWLanguage::getLanguageIdByCode($lang);
        $_ARRAYLANG = \Env::get('init')->getComponentSpecificLanguageData($this->getName(), true, $langId);

        $title = contrexx_input2db($data['get']['title']);
        if (isset($title)) {
            $link = contrexx_input2db($data['get']['link']);
            $description = contrexx_input2db($data['get']['description']);
            $message = contrexx_input2db($data['get']['message']);
            $price = contrexx_input2db($data['get']['price']);
            $image1 = contrexx_input2db($data['get']['image_1']);
            $image2 = contrexx_input2db($data['get']['image_2']);
            $image3 = contrexx_input2db($data['get']['image_3']);

            $em = $this->cx->getDb()->getEntityManager();

            $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
            $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));

            if (!$catalog) {
                $catalog = new \Cx\Modules\FavoriteList\Model\Entity\Catalog;
                $dateTimeNow = new \DateTime('now');
                $dateTimeNowFormat = $dateTimeNow->format('d.m.Y H:i:s');
                $catalog->setName($_ARRAYLANG['TXT_MODULE_' . strtoupper($this->getName())] . ' ' . $dateTimeNowFormat);
                $em->persist($catalog);
            }

            $favorite = new \Cx\Modules\FavoriteList\Model\Entity\Favorite;
            $favorite->setCatalog($catalog);
            $favorite->setTitle($title);
            $favorite->setLink($link);
            $favorite->setDescription($description);
            $favorite->setMessage($message);
            $favorite->setPrice($price);
            $favorite->setImage1($image1);
            $favorite->setImage2($image2);
            $favorite->setImage3($image3);

            $em->persist($favorite);
            $em->flush();
            $em->clear();

            if (isset($data['get']['lang'])) {
                return $this->getCatalog($data);
            }
        }
    }

    /**
     * removes a Favorite from Catalog by id
     *
     * @return json result
     */
    public function removeFavorite($data = array())
    {
        $id = contrexx_input2raw($data['get']['id']);
        if ($id) {
            $em = $this->cx->getDb()->getEntityManager();
            $catalogRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Catalog');
            $catalog = $catalogRepo->findOneBy(array('sessionId' => $this->getComponent('Session')->getSession()->sessionid));
            $favorite = $catalog->getFavorites()->filter(
                function ($favorite) use ($id) {
                    return $favorite->getId() == $id;
                }
            )->first();
            if ($favorite) {
                $em->remove($favorite);
                $em->flush();
                $em->clear();

                if (isset($data['get']['lang'])) {
                    return $this->getCatalog($data);
                }
            }
        }
    }

    /**
     * edits the message of a favorite
     *
     * @return json result
     */
    public function editFavoriteMessage($data = array())
    {
        $id = contrexx_input2db($data['get']['id']);
        if (isset($id)) {
            $message = contrexx_input2db($data['get']['message']);

            $em = $this->cx->getDb()->getEntityManager();
            $favoriteRepo = $em->getRepository($this->getNamespace() . '\Model\Entity\Favorite');
            $favorite = $favoriteRepo->findOneBy(array('id' => $id));

            $favorite->setMessage($message);

            $em->persist($favorite);
            $em->flush();
            $em->clear();

            if (isset($data['get']['lang'])) {
                return $this->getCatalog($data);
            }
        }
    }

    /**
     * Get theme by theme id
     *
     * @param array $params User input array
     * @return \Cx\Core\View\Model\Entity\Theme Theme instance
     * @throws JsonListException When theme id empty or theme does not exits in the system
     */
    protected function getTheme($id = null)
    {
        $themeRepository = new \Cx\Core\View\Model\Repository\ThemeRepository();
        if (empty($id)) {
            return $themeRepository->getDefaultTheme();
        }
        $theme = $themeRepository->findById($id);
        if (!$theme) {
            throw new JsonListException('The theme id ' . $id . ' does not exists.');
        }
        return $theme;
    }
}
