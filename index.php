<?php
/**
 * This loads Contrexx in auto (/normal) mode.
 * 
 * @version 3.1.0
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

require_once dirname(__FILE__).'/core/Core/init.php';

/**
 * If you activate debugging here, it will be activated for all normal usage
 * (front- and backend).
 */
//\DBG::activate(DBG_PHP);

init();
