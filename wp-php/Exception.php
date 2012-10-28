<?php

/**
 * Represents an exception
 */
class Wordpress_Exception extends Exception {
    public function __construct($message, $code) {
        parent::__construct($message, $code);
    }
}