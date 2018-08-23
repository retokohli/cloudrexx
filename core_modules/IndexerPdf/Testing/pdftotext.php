<?php

/**
 * This is a dummy endpoint for testing the "pdftotext" request
 */
passthru(
    'echo "hello world\nwith lf\ndone."'
// That package isn't installed by ./cx
//    'pdftotext ' . $_FILES['pdffile']['tmp_name'] . ' -'
//    'pdftotext ' . $_FILES['pdffile']['tmp_name'] . ' -  2>&1'
);
unlink($_FILES['pdffile']['tmp_name']);
