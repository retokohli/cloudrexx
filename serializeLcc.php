<?php
//die(serialize(array()));
$dir_tmp = dirname(__FILE__) . '/tmp/';
$dir_data = dirname(__FILE__) . '/core/ClassLoader/Data/';
$filename_sources = 'LegacyClassCache.dat';
$filename_text_tmp = 'LegacyClassCache-tmp.txt';
$filename_text_data = 'LegacyClassCache-data.txt';
if (file_exists($dir_tmp . $filename_text_tmp)) {
    file_put_contents(
        $dir_tmp . $filename_sources,
        serialize(
            eval(
                'return ' .
                file_get_contents(
                    $dir_tmp . $filename_text_tmp
                ) . ';'
            )
        ), true
    );
}
if (file_exists($dir_tmp . $filename_text_data)) {
    file_put_contents(
        $dir_data . $filename_sources,
        serialize(
            eval(
                'return ' .
                file_get_contents(
                    $dir_tmp . $filename_text_data
                ) . ';'
            )
        ), true
    );
}
