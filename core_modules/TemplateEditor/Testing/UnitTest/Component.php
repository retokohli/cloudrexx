<?php
return array (
    'DlcInfo' =>
        array (
            'name' => 'Standard',
            'description' => 'Default theme',
            'type' => 'template',
            'publisher' => 'Comvation AG',
            'versions' =>
                array (
                    'state' => 'stable',
                    'number' => '1.0.0',
                    'releaseDate' => '',
                ),
            'dependencies' =>
                array (
                    0 =>
                        array (
                            'name' => 'jquery',
                            'type' => 'lib',
                            'minimumVersionNumber' => '2.0.3',
                            'maximumVersionNumber' => '2.0.3',
                        ),
                    1 =>
                        array (
                            'name' => 'twitter-bootstrap',
                            'type' => 'lib',
                            'minimumVersionNumber' => '3.2.0',
                            'maximumVersionNumber' => '3.2.0',
                        ),
                ),
            'options' =>
                array (
                    0 =>
                        array (
                            'name' => 'main_title',
                            'specific' =>
                                array (
                                    'regex' => '/[a-z0-9]+/i',
                                    'textvalue' => 'test',
                                ),
                            'type' => '\\Core_Modules\\TemplateEditor\\Model\\Entity\\TextOption',
                        ),
                    1 =>
                        array (
                            'name' => 'main_color',
                            'specific' =>
                                array (
                                    'color' => '#efefef',
                                ),
                            'type' => '\\Core_Modules\\TemplateEditor\\Model\\Entity\\ColorOption',
                        ),
                ),
        ),
);