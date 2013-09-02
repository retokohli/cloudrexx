<?php
require_once dirname(__FILE__) . '/ContrexxContext.php';

/**
 * Features context.
 */
class Backend500CheckContext extends ContrexxContext
{
    private $failedLinks = array();

    /**
     * @Then /^I don\'t get the http status code "([^"]*)"$/
     */
    public function iDonTGetTheHttpStatusCode($httpStatusCode)
    {
        if (!empty($this->failedLinks)) {
            $message = "The following links failed:\r\n";
            foreach ($this->failedLinks as $failedLink) {
                $message .= "* " . $failedLink . "\r\n";
            }
            throw new \Exception($message);
        }
//        $errorOutput = "";
//        foreach($this->resultStatusCodes as $i => $statusCodes) {
//            if ($statusCodes[0] === true) { // exception ($i != 1) for Website Ansicht Link
//                $errorOutput .= "The link " . $statusCodes[1] . " returns a " . $httpStatusCode . " http status code\r\n";
//            }
//        }
//        if (empty($errorOutput)) {
//            print 'YESS!!!! EVERYTHING IS FINE HERE!';
//        } else {
//            print $errorOutput;
//        }
    }

    /**
     * @When /^I visit all links I find on each page$/
     */
    public function iVisitAllLinksIFindOnEachPage()
    {
        // wait otherwise the page is not loaded
        $this->session->wait(2000);
        $lastLinkIndex = count($this->page->findAll('css', '.navigation_level_2 > li > a')) - 1;

        // visit first link
        // skip the first two links
        $linkTags = array();
        $this->visitNextLink(2, $lastLinkIndex, $linkTags);
    }

    private function currentPageIsMaintenance() {
        if (preg_match('/maintenance mode/', $this->page->find('css', 'body')->getHtml())) {
            return true;
        }
        return false;
    }

    private function visitNextLink($currentLinkIndex, $lastLinkIndex, &$linkTags) {
        if ($currentLinkIndex > $lastLinkIndex) {
            return;
        }

        $linkTags = $this->page->findAll('css', '.navigation_level_2 > li > a');
        $url = $linkTags[$currentLinkIndex]->getAttribute('href');
        $this->iAmOn($url);
        $this->session->wait(2000);

        if (!$this->currentPageIsMaintenance()) {
            // refresh current site

            $subNavigationLinksCount = count($this->page->findAll('css', '#subnavbar_level1 td.navi > a'));
            for($i = 0; $i < $subNavigationLinksCount; $i++) {
                $subNavigationLinks = $this->page->findAll('css', '#subnavbar_level1 td.navi > a');
                $url = $subNavigationLinks[$i]->getAttribute('href');
                $this->iAmOn($url);
                $this->session->wait(2000);

                if ($this->currentPageIsMaintenance()) {
                    $this->failedLinks[] = $url;
                    $this->iAmOn($linkTags[$currentLinkIndex]->getAttribute('href'));
                    $this->session->wait(2000);
                }
            }

            $this->visitNextLink(++$currentLinkIndex, $lastLinkIndex, $linkTags);
            return;
        }


        // failed

        $this->failedLinks[] = $url;

//        $this->resultStatusCodes[] = array(
//            $this->currentPageIsMaintenance(),
//            $linkTags[$currentLinkIndex]->getAttribute('href'),
//        );

        // reload backend
        // due to csrf
        $this->iAmInBackend();
        $this->session->wait(2000);

        $this->visitNextLink(++$currentLinkIndex, $lastLinkIndex, $linkTags);
    }
}
