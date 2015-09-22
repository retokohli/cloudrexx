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


class UpdateContentTree extends ContentTree {
    function buildTree($node = null, $level = 0, $pathSoFar = '') {
        if (!$node) {
            $node = $this->em->getRepository('Cx\Core\ContentManager\Model\Entity\Node')->getRoot();
        }
        $nodes = $node->getChildren();
        foreach ($nodes as $node) {//$title => $entry) {
            $page = $node->getPage($this->langId);
            if (!$page) {
                continue;
            }
            $alias = $pathSoFar . $page->getSlug();
            $this->tree[$node->getLft()] = $this->convert($page, $alias);
            $this->tree[$node->getLft()]['level'] = $level;
            ksort($this->tree);

            $this->buildTree($node, $level + 1, $alias . '/');
        }
    }

}
