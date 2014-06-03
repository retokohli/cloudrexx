/\$_DBCONFIG\['database'\]/ {
    print substr($0,27,index($0, "';")-1-27+1);
}