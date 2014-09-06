<?php
use \Cx\Core\Json\Adapter\Block\JsonBlock as JsonBlock;

class BlockTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase {
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