<?php

class foo {
    var $name;
    function foo($name) {
        $this->name = $name;
    }
    function getName($prefix) {
        return "$prefix $this->name";
    }
}
$foo = new foo("hallo\n");
$bar = 'ork ork, ';
$result = eval('return $foo->getName($bar);');
echo("result: ");echo($result);echo("<br />");

?>
