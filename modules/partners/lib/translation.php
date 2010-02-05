<?PHP

function tr($string) {
    global $_ARRAYLANG, $_CORELANG;
    
    if (isset($_ARRAYLANG[$string])) {
        return $_ARRAYLANG[$string];
    }
    return $_CORELANG[$string];
}

function tr_parse($string, $args) {
    $args = func_get_args();
    $args[0] = tr($string);

    return call_user_func_array('sprintf', $args);
}

