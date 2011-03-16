<?php

if (!class_exists("SearchInterface")) {
    abstract class SearchInterface {
        abstract public function search($term);
    }
}