<?php

class Frontend500CheckContext extends \Behat\Behat\Context\BehatContext
{
    private $failedLinks = array();

    /**
     * @Then /^I don\'t get the http status code "([^"]*)" in frontend$/
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
     * @When /^I visit all links I find on each page in frontend$/
     */
    public function iVisitAllLinksIFindOnEachPage()
    {
        // wait otherwise the page is not loaded
        $this->waitForFrontend();
        $linkTags = $this->getLinkTagsOfNavigation();
        foreach ($linkTags as $linkTag) {
            $url = $linkTag->getAttribute('href');
            if (preg_match('/\.xml/', $url)) {
                continue;
            }
            if (!$this->testLink($url)) {
                $this->getMainContext()->session->back();
                $this->waitForFrontend();
            } else {
                // check links of content
                try {
                    $contentLinks = $this->getMainContext()->page->findAll('css', '#page a');
                    foreach ($contentLinks as $contentLink) {
                        $url = $contentLink->getAttribute('href');
                        if (!$this->testLink($url)) {
                        }
                        $this->getMainContext()->session->back();
                        $this->waitForFrontend();
                    }
                } catch (\Exception $e) {}
            }
        }
    }

    private function testLink($url) {
        $this->getMainContext()->iAmOn($url);
        if (preg_match('/http/', $url)) {
            return true;
        }
        $this->waitForFrontend();
        if ($this->currentPageIsMaintenance()) {
            $this->failedLinks[] = $url;
            return false;
        }
        return true;
    }

    private function currentPageIsMaintenance() {
        $html = $this->getMainContext()->page->find('css', 'body')->getHtml();
        if (preg_match('/maintenance mode/', $this->getMainContext()->page->find('css', 'body')->getHtml()) || empty($html)) {
            return true;
        }
        return false;
    }

    private function getLinkTagsOfNavigation() {
        return $this->getMainContext()->page->findAll('css', '#navigation a');
    }

    private function waitForFrontend(){
        $this->getMainContext()->session->wait(2000, 'document.getElementsByTagName("body")[0] !== undefined');
    }
}
