<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
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
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

use \Cx\Core\Json\Adapter\Block\JsonBlock as JsonBlock;

include_once(ASCMS_TEST_PATH.'/testCases/ContrexxTestCase.php');

class BlockTest extends \ContrexxTestCase {
    /**
     * @covers JsonBlock::getBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NoPermissionException
     */
    public function testGetBlockContentNoPermission() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? new \cmsSession() : $sessionObj;
        $jsonBlock = new JsonBlock();
        $jsonBlock->getBlockContent(array('get' => array('block' => 1, 'lang' => 'de')));
    }
    
    /**
     * @covers JsonBlock::getBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NotEnoughArgumentsException
     */
    public function testGetBlockContentNotEnoughArguments() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? new \cmsSession() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->getBlockContent(array());
    }
    
    /**
     * @covers JsonBlock::getBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NoBlockFoundException
     */
    public function testGetBlockContentNoBlockFound() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? new \cmsSession() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->getBlockContent(array('get' => array('block' => 999, 'lang' => 'de')));
    }
    
    /**
     * @covers JsonBlock::getBlockContent
     */
    public function testGetBlockContent() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? new \cmsSession() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $result = $jsonBlock->getBlockContent(array('get' => array('block' => 32, 'lang' => 'de')));
        $this->assertArrayHasKey('content', $result);
    }
    
    /**
     * @covers JsonBlock::saveBlockContent
     * @expectedException \Cx\Core\Json\Adapter\Block\NotEnoughArgumentsException
     */
    public function testSaveBlockContentNotEnoughArguments() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? new \cmsSession() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->saveBlockContent(array());
    }
    
    /**
     * @covers JsonBlock::saveBlockContent
     */
    public function testSaveBlockContent() {
        global $sessionObj;
        $sessionObj = !$sessionObj ? new \cmsSession() : $sessionObj;
        $user = \FWUser::getFWUserObject()->objUser->getUser(1);
        \FWUser::loginUser($user);
        
        $jsonBlock = new JsonBlock();
        $jsonBlock->saveBlockContent(array('get' => array('block' => 32, 'lang' => 'de'), 'post' => array('content' => 'bla')));
        
        $result = $jsonBlock->getBlockContent(array('get' => array('block' => 32, 'lang' => 'de')));
        $this->assertEquals('bla', $result['content']);
    }
}