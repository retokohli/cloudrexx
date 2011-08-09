<?php 
require_once(ASCMS_CORE_PATH.'/PageTree.class.php');

class JsonPageTree extends PageTree {

    protected $levelFrom = 1;
    protected $levelTo = 0; //0 means unbounded
    protected $navigationIds = array();


    protected $lastLevel = 0; //level of last item, used to remember how much closing tags we need.
    
    protected function preRender() {
        // checks which levels to use
        // default is 1+ (all)
        $match = array();
        if (preg_match('/levels_([1-9])([1-9\+]*)/', '', $match)) {
            $this->levelFrom = $match[1];
            if($match[2] != '+')
                $this->levelTo = intval($match[2]);
        }
    }

    protected function renderElement($title, $level, $hasChilds, $lang, $path, $current, $page) {
        $output = "{\r";

        //are we inside the layer bounds?
        if($level >= $this->levelFrom && ($level <= $this->levelTo || $this->levelTo == 0)) {

            if (!isset($this->navigationIds[$level]))
                $this->navigationIds[$level] = 0;
            else
                $this->navigationIds[$level]++;

	    $output .= "\"attr\" : { \"id\" : \"node_".$page->getNode()->getId()."},\n";
	    $output .= "\"data\" : [\n";
		// lang data here
	    $output .= "]";

	    if ($hasChilds) {
	        $output .= ",\n\"children\" : [\n";
    	    }
            else {
  		$output .= "\n";
            }
        }

	$output .= "}\n";
	return $output;
    }

    protected function renderHeader() {
        return "[{\n";
    }

    protected function renderFooter() {
	return "}]";
    }

    protected function getClosingTags($level = 0) {
        if($this->lastLevel == 0 || $level >= $this->lastLevel)
            return '';

        return str_repeat("\n}", $this->lastLevel - $level);

    }


}

?>
