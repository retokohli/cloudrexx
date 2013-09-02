<?php

/**
 * Features context.
 */
class ContrexxContext extends \Behat\Behat\Context\BehatContext
{
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
        $this->iAmOn('http://localhost/contrexx/cx/trunk/cadmin');
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
}
