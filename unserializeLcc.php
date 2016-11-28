<?php
$dir_tmp = dirname(__FILE__) . '/tmp/';
$dir_data = dirname(__FILE__) . '/core/ClassLoader/Data/';
$filename_sources = 'LegacyClassCache.dat';
$filename_text_tmp = 'LegacyClassCache-tmp.txt';
$filename_text_data = 'LegacyClassCache-data.txt';
file_put_contents(
    $dir_tmp . $filename_text_tmp,
    var_export(
        unserialize(
            file_get_contents(
                $dir_tmp . $filename_sources
            )
        ), true
    )
);
file_put_contents(
    $dir_tmp . $filename_text_data,
    var_export(
        unserialize(
            file_get_contents(
                $dir_data . $filename_sources
            )
        ), true
    )
);
