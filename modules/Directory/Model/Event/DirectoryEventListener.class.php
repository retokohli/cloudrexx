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
 * EventListener for Directory
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_directory
 */

namespace Cx\Modules\Directory\Model\Event;

/**
 * EventListener for Directory
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_directory
 */
class DirectoryEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }

    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();

        //For Directory
        $query = "SELECT id, title, description AS content,
                           MATCH (title, description) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_directory_dir
                     WHERE status='1'
                       AND (   title LIKE ('%$term_db%')
                            OR description LIKE ('%$term_db%')
                            OR searchkeys LIKE ('%$term_db%')
                            OR company_name LIKE ('%$term_db%'))";
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'Directory', 'detail', 'id=', $search->getTerm()));
        $search->appendResult($result);

        //For Directory Category
        $categoryQuery = "SELECT id, name AS title, description AS content,
                           MATCH (name, description) AGAINST ('%$term_db%') AS score
                      FROM " . DBPREFIX . "module_directory_categories
                     WHERE status='1'
                       AND (   name LIKE ('%$term_db%')
                            OR description LIKE ('%$term_db%'))";
        $categoryResult = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($categoryQuery, 'Directory', '', 'lid=', $search->getTerm()));
        $search->appendResult($categoryResult);
    }

    /**
     * Clear all Ssi cache
     */
    protected function clearEsiCache($eventArgs)
    {
        if (empty($eventArgs) || $eventArgs != 'Directory') {
            return;
        }
        global $objInit;

        // clear home page cache
        $cache = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getComponent('Cache');
        foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
            $cache->clearSsiCachePage(
                'Directory',
                'getContent',
                array('template' => $lang['themesid'])
            );
        }

        //clear latest entries cache
        $objDirectory = new \Cx\Modules\Directory\Controller\Directory('');
        $entryIds     = $objDirectory->getBlockLatestIds();
        if (!$entryIds) {
            return;
        }

        $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $blockName = 'directoryLatest_row_';
        foreach ($themeRepo->findAll() as $theme) {
            $themesBlock = array();
            $themeId = $theme->getId();
            $searchTemplateFiles = array_merge(
                array('index.html', 'home.html', 'content.html'),
                $objInit->getCustomContentTemplatesForTheme($theme)
            );

            $i = 1;
            while ($i <= 10) {
                foreach ($searchTemplateFiles as $tplFile) {
                    if ($theme->isBlockExistsInfile($tplFile, $blockName.$i)) {
                        $themesBlock[] = array(
                            'file'  => $tplFile,
                            'block' => $i
                        );
                    }
                }
                $i++;
            }

            foreach ($themesBlock as $arrDetails) {
                foreach ($entryIds as $entryId) {
                    $cache->clearSsiCachePage(
                        'Directory',
                        'getBlockById',
                        array(
                            'template' => $themeId,
                            'file'     => $arrDetails['file'],
                            'block'    => $arrDetails['block'],
                            'blockId'  => $entryId
                        )
                    );
                }
            }
        }
    }
}
