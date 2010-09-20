<?php

/**
 * Contains the class that provides the tag operations
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */

/**
 * Provide all database operations for the tags
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */
class KnowledgeTags
{
    /**
     * The tags
     *
     * @var array
     */
    private $tags = array();

    /**
     * Wrapper function for getAllOrderAlphabetically()
     *
     * @return array
     */
    public function getAll($lang)
    {
        return $this->getAllOrderAlphabetically($lang);
    }

    /**
     * Return all tags ordered by popularity
     *
     * Return all available tags for the current language and order
     * it by their popularity.
     * @param $lang
     * @global $objDatabase
     * @return array
     */
    public function getAllOrderByPopularity($lang)
    {
        if (count($this->tags) != 0) {
            return $this->tags;
        }

        global $objDatabase;

        $lang = intval($lang);

        $query = "  SELECT
                        tags.id AS id,
                        tags.name AS name,
                        count( tags_articles.id ) AS popularity
                    FROM ".DBPREFIX."module_knowledge_tags AS tags
                    LEFT OUTER JOIN ".DBPREFIX."module_knowledge_tags_articles AS tags_articles
                    ON tags.id = tags_articles.tag
                    WHERE tags.lang = ".$lang."
                    GROUP BY tags.id
                    ORDER BY popularity DESC";
        $rs = $objDatabase->Execute($query);
        if ($rs === false) {
            throw new DatabaseError("error getting all tags");
        }
        $arr = array();
        if ($rs->RecordCount()) {
            while (!$rs->EOF) {
                $popularity = $rs->fields['popularity'];
                $arr[] = array(
                    "id"    => $rs->fields['id'],
                    "name"  => $rs->fields['name'],
                    "popularity" => ($popularity == 0) ? 1 : $popularity);
                $rs->MoveNext();
            }
        }

        return $arr;
    }

    /**
     * Return all Tags ordered alphabetically
     *
     * @param int $lang
     * @throws DatabaseError
     * @global $objDatabase
     * @return array
     */
    public function getAllOrderAlphabetically($lang)
    {
        if (count($this->tags) != 0) {
            return $this->tags;
        }

        global $objDatabase;

        $lang = intval($lang);
        $query = "  SELECT  tags.id AS id,
                            tags.name AS name,
                            count( tags_articles.id ) AS popularity
                    FROM ".DBPREFIX."module_knowledge_tags AS tags
                    LEFT OUTER JOIN ".DBPREFIX."module_knowledge_tags_articles AS tags_articles
                    ON tags.id = tags_articles.tag
                    WHERE tags.lang = ".$lang."
                    GROUP BY tags.id
                    ORDER BY name";
        $rs = $objDatabase->Execute($query);
        if ($rs === false) {
            throw new DatabaseError("error getting all tags");
        }
        $arr = array();
        if ($rs->RecordCount()) {
            while (!$rs->EOF) {
                $popularity = $rs->fields['popularity'];
                $arr[] = array(
                    "id"    => $rs->fields['id'],
                    "name"  => $rs->fields['name'],
                    "popularity" => ($popularity == 0) ? 1 : $popularity
                );
                $rs->MoveNext();
            }
        }

        return $arr;
    }

    /**
     * Get tags by article id
     *
     * Warning: returns any tag no matter what language
     * @param int $id
     * @param int $lang
     * @global $objDatabase
     * @throws DatabaseError
     * @return array
     */
    public function getByArticle($id, $lang=0)
    {
        global $objDatabase;

        $id = intval($id);

        $query = "  SELECT  tags.id as id,
                            tags.name as name,
                            tags.lang as lang
                    FROM `".DBPREFIX."module_knowledge_tags_articles` as relation
                    INNER JOIN `".DBPREFIX."module_knowledge_tags` as tags
                    ON relation.tag = tags.id
                    WHERE relation.article = ".$id;
        if ($lang != 0) {
            $query .= " AND lang = ".intval($lang);
        }
        $rs = $objDatabase->Execute($query);
        if ($rs === false) {
            throw new DatabaseError("error getting tags by article id");
        }

        $tags = array();
        while(!$rs->EOF) {
            $tags[$rs->fields['id']] = array(
                "name"  => $rs->fields['name'],
                "lang"  => $rs->fields['lang']);
            $rs->MoveNext();
        }
        return $tags;
    }

    /**
     * Get all article ids of a tag
     *
     * Return all article ids that have the given tag
     * assigned. Also return the tags name.
     * @param int $id
     * @global $objDatabase
     * @throws DatabaseError
     * @return array
     */
    public function getArticlesByTag($id)
    {
        global $objDatabase;

        $query = "  SELECT  tags.name as tagname,
                            relation.article as articleid
                    FROM `".DBPREFIX."module_knowledge_tags_articles` as relation
                    INNER JOIN `".DBPREFIX."module_knowledge_tags` as tags
                    ON relation.tag = tags.id
                    WHERE tags.id = ".$id;

        $rs = $objDatabase->Execute($query);
        if ($rs === false) {
            throw new DatabaseError("error getting articleids by tagid");
        }

        $articles = array();
        if (count($rs->RecordCount()) > 0) {
            $tagname = $rs->fields['tagname'];

            while(!$rs->EOF) {
                $articles[] = $rs->fields['articleid'];
                $rs->MoveNext();

            }
            return array(
                "name" => $tagname,
                "articles" => $articles);
        }
    }

    /**
     * Insert tags from a string
     *
     * @param int $article_id
     * @param int $string
     * @param int $lang
     */
    public function insertFromString($article_id, $string, $lang)
    {
        $lang = intval($lang);

        $tags = preg_split("/\s*,\s*/i", $string);
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $res = $this->search_tag($tag, $lang);
                if ($res === false) {
                    $tag_id = $this->insert($tag, $lang);
                } else {
                    $tag_id = $res;
                }
                $this->connectWithArticle($article_id, $tag_id);
            }
        }
    }

    /**
     * Insert a tag
     *
     * @param string $tag
     * @param int $lang
     * @global $objDatabase
     * @throws DatabaseError
     * @return $objDatabase
     */
    public function insert($tag, $lang)
    {
        global $objDatabase;

        $tag = contrexx_addslashes($tag);

        $query = "  INSERT INTO ".DBPREFIX."module_knowledge_tags
                    (name, lang)
                    VALUES
                    ('".$tag."', ".$lang.")";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error inserting new tag");
        }

        return $objDatabase->Insert_ID();
    }

    /**
     * Search tags
     *
     * Rearrange the tags array so the built-in array_search function
     * can be used (i think its faster)
     * @param string $tag
     * @param int $lang
     * @return mixed
     */
    private function search_tag($tag, $lang)
    {
        $allTags = $this->getAll($lang);
        foreach ($allTags as $compare) {
            if ($compare['name'] == $tag) {
                return $compare['id'];
            }
        }

        return false;
    }

    /**
     * Connect with an article
     *
     * @param int $article_id
     * @param int $tag_id
     * @global $objDatabase
     * @throws DatabaseError
     */
    private function connectWithArticle($article_id, $tag_id)
    {
        global $objDatabase;

        $article_id = intval($article_id);
        $tag_id = intval($tag_id);

        $query = "  INSERT INTO ".DBPREFIX."module_knowledge_tags_articles
                    (article, tag)
                    VALUES
                    (".$article_id.", ".$tag_id.")";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error tagging an article");
        }
    }

    /**
     * Remove all relations from an article to tags
     *
     * @param int $article_id
     * @global $objDatabase
     * @throws DatabaseError
     */
    public function clearTags($article_id)
    {
        global $objDatabase;

        $article_id = intval($article_id);

        $query = "  DELETE FROM ".DBPREFIX."module_knowledge_tags_articles
                    WHERE article = ".$article_id;
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error deleting all references of an article");
        }
    }

    /**
     * Remove all Tags that are not used
     *
     * @global $objDatabase
     * @throws DatabaseError
     */
    public function tidy()
    {
        global $objDatabase;

        $query = "  SELECT tags.id
                    FROM ".DBPREFIX."module_knowledge_tags_articles AS relation
                    RIGHT JOIN ".DBPREFIX."module_knowledge_tags AS tags ON tags.id = relation.tag
                    WHERE relation.id IS NULL";
        $rs = $objDatabase->Execute($query);
        if ($rs === false) {
            throw new DatabaseError("error getting unused tags ");
        }

        if ($rs->RecordCount() > 0) {
            $ids = "";
            while (!$rs->EOF) {
                $ids .= " ".$rs->fields['id'].",";
                $rs->MoveNext();
            }

            $ids = substr($ids, 0, -1);

            $query = " DELETE FROM ".DBPREFIX."module_knowledge_tags
                        WHERE id IN (".$ids.")";
            if ($objDatabase->Execute($query) === false) {
                throw new DatabaseError("error deleteting unused tags");
            }
        }
    }

}

?>
