<?php
//require_once dirname(__FILE__) . '/ContrexxTesting.php';

/**
 * Features context.
 */
class Backend500CheckContext extends \Behat\Behat\Context\BehatContext
{













    ///// START PART FOR CONTREXX_CONTEXT
    /**
     * @var \Behat\Mink\Session
     */
    protected $session;

    /**
     * @var DocumentElement
     */
    protected $page;

    /**
     * @var array contrexx`s configuration array
     */
    protected $config;

    /**
     * Init the contrexx minimal mode
     */
    public function __construct() {
        // @todo: use configuration file for domainUrl and Offset, in method down below
//        require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/config/configuration.php';
//        global $_CONFIG;
//        $this->config = $_CONFIG;
    }

    /**
     * @Given /^I am logged in to backend with login "([^"]*)" and password "([^"]*)"$/
     */
    public function iAmLoggedInToBackendWithLoginAndPassword($username, $password)
    {
        // @todo: use configuration file here
        $this->iOpenBrowser();
        $this->iAmInBackend();
        $this->iEnterTheUsername($username);
        $this->iEnterThePassword($password);
        $this->iPressTheSubmitButton();
    }

    public function iAmInBackend() {
        $this->iAmOn('http://localhost/contrexx/trunk/cadmin');
    }

    public function iOpenBrowser() {
        $driver = new \Behat\Mink\Driver\Selenium2Driver(
            'firefox',
            array (
                'javascriptEnabled' => true,
            )
        );
        $this->session = new \Behat\Mink\Session($driver);
        $this->session->start();
    }

    /**
     * @Given /^I am on "([^"]*)"$/
     */
    public function iAmOn($arg1)
    {
        if (!$this->session) {
            $this->iOpenBrowser();
        }
        $url = $arg1;
        $this->session->visit($url);
        $this->page = $this->session->getPage();
    }

    /**
     * @Given /^I enter the username "([^"]*)"$/
     */
    public function iEnterTheUsername(&$username)
    {
        $input = $this->objectById('input', 'username');
        $input->setValue($username);
    }

    /**
     * @Given /^I enter the password "([^"]*)"$/
     */
    public function iEnterThePassword($password)
    {
        $input = $this->objectById('input', 'password');
        $input->setValue($password);
    }

    /**
     * @Given /^I press the submit button$/
     */
    public function iPressTheSubmitButton()
    {
        $button = $this->objectByCssSelector('input[type="submit"]');
        $button->click();
    }

    /**
     * @Then /^I close the session$/
     */
    public function iCloseTheSession()
    {
        $this->session->stop();
    }

    /**
     * Get a html element by id attribute value
     * @param string $tag the tag name of the element, e.g. input, div, span
     * @param string $id the id attribute value of the field to get
     * @return mixed
     * @throws Behat\Mink\Exception\ElementNotFoundException Element not found
     */
    public function objectById($tag, $id) {
        return $this->objectByCssSelector($tag . '#' . $id);
    }

    /**
     * Get a html element by css selector notation
     * @param string $cssSelector the css selector path
     * @return mixed
     * @throws Behat\Mink\Exception\ElementNotFoundException
     */
    public function objectByCssSelector($cssSelector) {
        $element = $this->page->find('css', $cssSelector);
        if ($element == null) {
            // @todo: add contrexx exception
            throw new \Behat\Mink\Exception\ElementNotFoundException($this->session, 'input', 'css', 'input[type="submit"]');
        }
        return $element;
    }


    ///// END PART FOR CONTREXX_CONTEXT








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
        $this->visitNextLink(0, $lastLinkIndex, $linkTags);
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
