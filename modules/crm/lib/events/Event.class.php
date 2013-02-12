<?php
class Event
{
    protected $name;
    protected $context;
    protected $cancelled = false;
    protected $info;

    function __construct($name, $context = null, $info = null) {
        $this->setName($name);
        $this->setContext($context);
        $this->setInfo($info);
    }

    public function setContext($context) {
        $this->context = $context;
    }

    public function getContext() {
        return $this->context;
    }

    public function setInfo($info) {
        $this->info = $info;
    }

    public function getInfo() {
        return $this->info;
    }

    function cancel() {
        $this->cancelled = true;
    }

    public function isCancelled() {
        return $this->cancelled;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
}
 
