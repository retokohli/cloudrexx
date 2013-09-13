<?php

use Behat\Behat\Context\BehatContext;

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * @var \Behat\Mink\Session
     */
    public $session;

    /**
     * @var DocumentElement
     */
    public $page;

    /**
     * @var array contrexx`s configuration array
     */
    public $config;


    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/core/Core/init.php';
        require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/lib/doctrine/vendor/Symfony/Component/Yaml/Yaml.php';
        global $_CONFIG;
        init('minimal');
        $this->config = $_CONFIG;

        // check whether the backend has 500 server errors on links
        $this->useContext('frontend500Check', new Frontend500CheckContext());
        $this->useContext('backend500Check', new Backend500CheckContext());
    }

    /**
     * @Given /^I am logged in to backend with login "([^"]*)" and password "([^"]*)"$/
     */
    public function iAmLoggedInToBackendWithLoginAndPassword($username, $password)
    {
        $this->iOpenBrowser();
        $this->iAmInBackend();
        $this->iEnterTheUsername($username);
        $this->iEnterThePassword($password);
        $this->iPressTheSubmitButton();
    }

    /**
     * @Given /^I am in backend$/
     */
    public function iAmInBackend() {
        $this->iAmOn('http://' . $this->config['domainUrl'] . '/contrexx/trunk' . ASCMS_BACKEND_PATH);
//        $this->iAmOn('http://' . $this->config['domainUrl'] . ASCMS_INSTANCE_OFFSET . ASCMS_BACKEND_PATH);
    }

    /**
     * @Given /^I am in frontend$/
     */
    public function iAmInFrontend() {
        $this->iAmOn('http://' . $this->config['domainUrl'] . '/contrexx/trunk');
//        $this->iAmOn('http://' . $this->config['domainUrl'] . ASCMS_INSTANCE_OFFSET);
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
