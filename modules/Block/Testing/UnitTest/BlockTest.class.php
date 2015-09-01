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
 * BlockTest
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  module_block
 */

namespace Cx\Modules\Block\Testing\UnitTest;
use \Cx\Core\Json\Adapter\Block\JsonBlock as JsonBlock;

/**
 * BlockTest
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  module_block
 */
class BlockTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase {
    /**
     * @covers \Cx\Core\Json\Adapter\Block\JsonBlock::getBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NoPermissionException
     */
    public function testGetBlockContentNoPermission() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? \cmsSession::getInstance() : $sessionObj;
        $jsonBlock = new JsonBlock();
        $jsonBlock->getBlockContent(array('get' => array('block' => 1, 'lang' => 'de')));
    }
    
    /**
     * @covers \Cx\Core\Json\Adapter\Block\JsonBlock::getBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NotEnoughArgumentsException
     */
    public function testGetBlockContentNotEnoughArguments() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? \cmsSession::getInstance() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->getBlockContent(array());
    }
    
    /**
     * @covers \Cx\Core\Json\Adapter\Block\JsonBlock::getBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NoBlockFoundException
     */
    public function testGetBlockContentNoBlockFound() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? \cmsSession::getInstance() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->getBlockContent(array('get' => array('block' => 999, 'lang' => 'de')));
    }
    
    /**
     * @covers \Cx\Core\Json\Adapter\Block\JsonBlock::getBlockContent
     */
    public function testGetBlockContent() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? \cmsSession::getInstance() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $result = $jsonBlock->getBlockContent(array('get' => array('block' => 32, 'lang' => 'de')));
        $this->assertArrayHasKey('content', $result);
    }
    
    /**
     * @covers \Cx\Core\Json\Adapter\Block\JsonBlock::saveBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NotEnoughArgumentsException
     */
    public function testSaveBlockContentNotEnoughArguments() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? \cmsSession::getInstance() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->saveBlockContent(array());
    }
    
    /**
     * @covers \Cx\Core\Json\Adapter\Block\JsonBlock::saveBlockContent
     */
    public function testSaveBlockContent() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? \cmsSession::getInstance() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->saveBlockContent(array('get' => array('block' => 32, 'lang' => 'de'), 'post' => array('content' => 'bla')));
        
        $result = $jsonBlock->getBlockContent(array('get' => array('block' => 32, 'lang' => 'de')));
        $this->assertEquals('bla', $result['content']);
    }
}
