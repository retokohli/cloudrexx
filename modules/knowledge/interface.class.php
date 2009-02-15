<?php

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/knowledge/lib/knowledgeLib.class.php';

class KnowledgeInterface extends KnowledgeLibrary 
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Replace the placeholders
     *
     * @param unknown_type $content
     */
    public function parse(&$content)
    {
        $content = preg_replace("/{KNOWLEDGE_TAG_CLOUD}/i", $this->getTagCloud(), $content);
        $content = preg_replace("/{KNOWLEDGE_MOST_READ}/i", $this->getMostRead(), $content);
        $content = preg_replace("/{KNOWLEDGE_BEST_RATED}/i", $this->getBestRated(), $content);
    }
    
    /**
     * Return a tag cloud
     *
     * @return string
     */
    public function getTagCloud()
    {
        global $_LANGID, $objInit;
        
        $tpl = new HTML_Template_Sigma();
        $tpl->setErrorHandling(PEAR_ERROR_DIE);
        $template = $this->settings->formatTemplate($this->settings->get("tag_cloud_sidebar_template"));
        $tpl->setTemplate($template);
        
        require_once ASCMS_MODULE_PATH."/knowledge/lib/TagCloud.class.php";
        
        $highestFontSize = 20;
        $lowestFontSize = 10;
        
        try {
            $tags_pop = $this->tags->getAllOrderByPopularity($_LANGID);
            $tags = $this->tags->getAll($_LANGID);
        } catch (DatabaseError $e) {
            echo $e->plain();
        }
        
        $tagCloud = new TagCloud();
        $tagCloud->setTags($tags);
        $tagCloud->setTagVals($tags_pop[0]['popularity'], $tags_pop[count($tags_pop)-1]['popularity']);
        $tagCloud->setFont(20, 10);
        $tagCloud->setUrlFormat("index.php?section=knowledge".MODULE_INDEX."&amp;tid=%id");
        
        
        $tpl->setVariable("CLOUD", $tagCloud->getCloud());
        
        //$tpl->parse("cloud");
        return $tpl->get();
    }
    
    /**
     * Return the most popular
     *
     * @return unknown
     */
    public function getBestRated()
    {
        global $_LANGID;
        
        try {
            $articles = $this->articles->getBestRated($_LANGID, $this->settings->get('best_rated_sidebar_amount'));
        } catch (DatabaseError $e) { 
            return;
        }
        
        $template = $this->settings->formatTemplate($this->settings->get("best_rated_sidebar_template"));
        
        $objTemplate = &new HTML_Template_Sigma(ASCMS_THEMES_PATH);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        
        $objTemplate->setTemplate($template);
        
        $max_length = $this->settings->get("best_rated_sidebar_length");
        foreach ($articles as $key => $article) {
            $question = $article['content'][$_LANGID]['question'];
            if (strlen($question) >= $max_length) {
                $question = substr($question, 0, $max_length-3)."...";
            }
            $objTemplate->setVariable(array(
                "URL"        => "index.php?section=knowledge&cmd=article&id=".$key,
                "ARTICLE"   => $question
            ));
            $objTemplate->parse("article");
        }
        return $objTemplate->get();
    }
    
    /**
     * Get the best rated articles
     *
     */
    public function getMostRead()
    {
        global $_LANGID;
        
        try {
           $articles = $this->articles->getMostRead($_LANGID, $this->settings->get('best_rated_sidebar_amount')); 
        } catch (DatabaseError $e) {
            return;
        }
        
        $template = $this->settings->formatTemplate($this->settings->get("most_read_sidebar_template"));
        
        $objTemplate = &new HTML_Template_Sigma(ASCMS_THEMES_PATH);
        $objTemplate->setErrorHandling(PEAR_ERROR_DIE);
        
        $objTemplate->setTemplate($template);
        
        
        $max_length = $this->settings->get("most_read_sidebar_length");
        foreach ($articles as $key => $article) {
            $question = $article['content'][$_LANGID]['question'];
            if (strlen($question) >= $max_length) {
                $question = substr($question, 0, $max_length-3)."...";
            }
            $objTemplate->setVariable(array(
                "URL"       => "index.php?section=knowledge&cmd=article&id=".$key,
                "ARTICLE"   => $question
            ));
            $objTemplate->parse("article");
        }
        return$objTemplate->get();
    }
}
