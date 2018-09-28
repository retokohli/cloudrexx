<?php
// PHP7 only
//declare(strict_types = 1);

/**
 * Cloudrexx App by Comvation AG
 *
 * PHP Version 7.2
 *
 * @category  CloudrexxApp
 * @package   C7NIndexerPdf
 * @author    Comvation AG <info@comvation.com>
 * @copyright 2018 Comvation AG
 * @link      https://www.comvation.com
 *
 * Unauthorized copying, changing or deleting
 * of any file from this app is strictly prohibited
 *
 * Authorized copying, changing or deleting
 * can only be allowed by a separate contract
 */

/**
 * Endpoint for the "pdftotext" request
 *
 * Hands the uploaded file to pdftotext, and outputs its result.
 * Mind that the pdftotext package isn't installed by ./cx.
 *
 * Installation:
 *  - Copy this file to a PHP capable web server with "pdftotext" installed.
 *  - Configure the PDF Indexer with the proper path to this script.
 * @copyright   Comvation AG
 * @author      Reto Kohli <reto.kohli@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_module_c7nindexerpdf
 */
passthru(
    // Plain result: the empty string on error:
    'pdftotext ' . $_FILES['pdffile']['tmp_name'] . ' -'
    // Verbose result: includes the error message (use with care!):
    //'pdftotext ' . $_FILES['pdffile']['tmp_name'] . ' -  2>&1'
);
// Note:
//unlink($_FILES['pdffile']['tmp_name']);
// Copernicus says,
//  "Warning: unlink(): open_basedir restriction in effect.
//  File is not within the allowed path(s):
//  /home/httpd/vhosts/comvation-webinterfaces.com/:/tmp/ [...]"
// I suppose the /tmp folder will be cleared occasionally anyway.
