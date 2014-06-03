/\$_DBCONFIG\['database'\]/ {
    #appends _testing to the database configured
    print substr($0,0,index($0, "';")-1)"_testing';";
    next
}

/.*/ {
    print
}