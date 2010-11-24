<?php

/**
 * Contains the class for article operations
 *
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */


/**
 * Provide an abstract layer for the articles
 *
 * Provide an abstract layer for the articles, including operations for
 * reading, editing, adding and deleting articles. Also provide some special
 * functions to return the most read or the most popular articles.
 * @author Stefan Heinemann <sh@comvation.com>
 * @copyright Comvation AG <info@comvation.com>
 */
class KnowledgeArticles
{
    public $articles = null;
    /**
     * The basis query.
     *
     * @var string
     */
    private $basequery = "";

    /**
     * Save the base query
     */
    public function __construct()
    {
        $this->basequery = "
            SELECT articles.id,
                   articles.active,
                   articles.hits,
                   articles.votes,
                   articles.votevalue as value,
                   articles.category_ids,
                   articles.date_created,
                   articles.date_updated,
                   content.lang,
                   content.answer,
                   content.question,
                   content.`index` as `index`,
                   content.id as cid
              FROM `".DBPREFIX."module_knowledge_articles` AS articles
             INNER JOIN `".DBPREFIX."module_knowledge_article_content` AS content
                ON articles.id=content.article";
    }

    /**
     * Get all messages from database
     *
     * Read the messages out of the database but only if they
     * are not already read or if the argument is given true.
     * If an id is given, only read that one from the database.
     * With the query parameter can be an alternative query given, useful
     * for special conditions. In this case the results will be returned instead
     * of being saved in the $articles member variable. Whith the 4h parameter
     * this behaviour can be set implicitly.
     * @param bool $override
     * @param int $lang
     * @param int $id
     * @param string $alt_query
     * @global $objDatabase
     * @throws DatabaseError
     * @return mixed Return an array on success
     */
    public function readArticles($override = true, $lang=0, $id = 0, $alt_query="")
    {
        if ($override === false && isset($this->articles)) {
            // the messages are already read out and override is not given
            return null;
        }

        global $objDatabase;

        if (!empty($alt_query)) {
            $query = $alt_query;
        } else {
            $query = $this->basequery;
            // if only one article should be read add a where to the query
            if ($id > 0) {
                $id = intval($id);
                $query .= " WHERE articles.id = ".$id;
            }

            if ($lang > 0) {
                // only get one language
                if ($id > 0) {
                    $query .= " AND lang = ".$lang;
                } else {
                    $query .= " WHERE lang = ".$lang;
                }
            }

            // add some order.
            $query .= " ORDER BY sort ASC";
        }
        $objRs = $objDatabase->Execute($query);
        if ($objRs === false) {
            throw new DatabaseError("read articles failed");
        }

        $articles = array();
        while (!$objRs->EOF) {

            $curId = $objRs->fields['id'];
            if (isset($articles[$curId])) {
                $articles[$curId]['content'][$objRs->fields['lang']]['question'] = $objRs->fields['question'];
                $articles[$curId]['content'][$objRs->fields['lang']]['answer'] = $objRs->fields['answer'];
                $articles[$curId]['content'][$objRs->fields['lang']]['index'] = $objRs->fields['index'];
		$articles[$curId]['content'][$objRs->fields['lang']]['id'] = $objRs->fields['cid'];
            } else {
		$cid = intval($objRs->fields['cid']);
                $articles[$curId] = array(
                    'id'            => intval($objRs->fields['id']),
                    'active'        => intval($objRs->fields['active']),
                    'hits'          => intval($objRs->fields['hits']),
                    'votes'         => intval($objRs->fields['votes']),
                    'votevalue'     => intval($objRs->fields['value']),
//                    'category'      => intval($objRs->fields['category']),
                    'category'      => $objRs->fields['category_ids'],
                    'date_created'  => intval($objRs->fields['date_created']),
                    'date_updated'  => intval($objRs->fields['date_updated']),
                    'content'       => array(
                        $objRs->fields['lang'] => array(
                            'question'      => stripslashes($objRs->fields['question']),
                            'answer'        => stripslashes($objRs->fields['answer']),
                            'index'         => $objRs->fields['index'],
			    'id'            => $cid
                        )
                    )
                );
            }
            $objRs->MoveNext();
        }

        if (empty($alt_query)) {
            $this->articles = $articles;
            return null;
        }
        return $articles;
    }

    /**
     * Get articles by category
     *
     * Return an array with all articles that are assigned
     * to the given category.
     *
     * @param intval $catid
     * @return array
     */
    public function getArticlesByCategory($catid)
    {
        if (!isset($this->articles)) {
            $this->readArticles();
        }

        $arr = array();
        foreach ($this->articles as $id => $article) {
            if (FWValidator::is_value_in_comma_separated_list(
                $catid, $article['category'])
            ) {
                $arr[$id] = $article;
            }
        }
        return $arr;
    }

    public function getNewestArticles()
    {
        $query = $this->basequery;

        //$query .= " ORDER BY id DESC LIMIT 10";
        // cannot be done like this, because there are several rows
        // because all languages are returned
        $query .= " ORDER BY id DESC";
        return array_slice($this->readArticles(true, 0, 0, $query), 0, 10);
    }

    /**
     * Activate an article
     *
     * @param int $id
     * @global $objDatabase
     * @param int $id
     * @throws DatabaseError
     */
    public function activate($id)
    {
        global $objDatabase;

        $id = intval($id);
        $query = "  UPDATE ".DBPREFIX."module_knowledge_articles
                    SET active = 1
                    WHERE id = ".$id;
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to activate article");
        }
    }

    /**
     * Deactivate an article
     *
     * @param int $id
     * @global $objDatabase
     * @param int $id
     * @throws DatabaseError
     */
    public function deactivate($id)
    {
        global $objDatabase;

        $id = intval($id);
        $query = "  UPDATE ".DBPREFIX."module_knowledge_articles
                    SET active = 0
                    WHERE id = ".$id;
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to deactivate article");
        }
    }

    /**
     * Delete a single article
     *
     * @param int $id
     * @global $objDatabase
     * @throws DatabaseError
     */
    public function deleteOneArticle($id)
    {
        global $objDatabase;

        $id = intval($id);
        $query = "
            DELETE FROM ".DBPREFIX."module_knowledge_article_content
             WHERE article=$id";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to delete the content of a article");
        }
        $query = "
            DELETE FROM ".DBPREFIX."module_knowledge_articles
             WHERE id=$id";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to delete a article");
        }
    }


    /**
     * Delete the articles of a whole category
     *
     * @param int $catid
     * @global $objDatabase
     * @throws DatabaseError
     */
    public function deleteArticlesByCategory($catid)
    {
        global $objDatabase;

        $catid = intval($catid);
        $query = "
            DELETE FROM ".DBPREFIX."module_knowledge_article_content
             WHERE article IN (
                SELECT id FROM ".DBPREFIX."module_knowledge_articles
                WHERE (   `category_ids`='$catid'
                       OR `category_ids` LIKE '$catid,%'
                       OR `category_ids` LIKE '%,$catid,%'
                       OR `category_ids` LIKE '%,$catid'))";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to delete an article's content");
        }
        $query = "
            DELETE FROM ".DBPREFIX."module_knowledge_articles
             WHERE (   `category_ids`='$catid'
                    OR `category_ids` LIKE '$catid,%'
                    OR `category_ids` LIKE '%,$catid,%'
                    OR `category_ids` LIKE '%,$catid')";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to delete a article");
        }
    }


    /**
     * Insert a new article
     * @param   array   $category_ids   The array of assigned category IDs
     * @param int $active
     * @global $objDatabase
     * @throws DatabaseError
     * @return int
     */
    public function insert($category_ids, $active)
    {
        global $objDatabase;

        $category_ids =
            FWValidator::get_comma_separated_list_from_array($category_ids);
        $active = intval($active);
        $query = "
            INSERT INTO ".DBPREFIX."module_knowledge_articles (
                category_ids, active, date_created, date_updated
            ) VALUES (
                '$category_ids', $active, ".time().", ".time()."
            )";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to insert a new article");
        }
        $id = $objDatabase->Insert_ID();
        $this->insertContent($id);
        return $id;
    }


    /**
     * Update article
     * @param int $id
     * @param   array   $category_ids     The array of assigned category IDs
     * @param int $active
     * @throws DatabaseError
     * @global $objDatabase
     */
    public function update($id, $category_ids, $active)
    {
        global $objDatabase;

        $id = intval($id);
        $category_ids =
            FWValidator::get_comma_separated_list_from_array($category_ids);
        $active = intval($active);
        $query = "
            UPDATE ".DBPREFIX."module_knowledge_articles
               SET category_ids='$category_ids',
                   active=$active,
                   date_updated=".time()."
             WHERE id=$id";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to update the article");
        }
        $this->deleteContent($id);
        $this->insertContent($id);
    }

    /**
     * Add content that is to be inserted
     *
     * @param int $lang
     * @param string $question
     * @param string $answer
     */
    public function addContent($lang, $question, $answer, $index)
    {
        $this->insertContent[] = array(
            'lang' => intval($lang),
            'question' => contrexx_addslashes($question),
            'answer' => contrexx_addslashes($answer),
            'index' => $index
        );
    }


    /**
     * Count the entries of a category
     *
     * @param int $id
     * @global $objDatabase
     * @throws DatabaseError
     * @return int
     */
    public function countEntriesByCategory($category_id)
    {
        global $objDatabase;

        $category_id = intval($category_id);
        $query = "
            SELECT COUNT(*) AS amount
              FROM ".DBPREFIX."module_knowledge_articles
             WHERE (   `category_ids`='$category_id'
                    OR `category_ids` LIKE '$category_id,%'
                    OR `category_ids` LIKE '%,$category_id,%'
                    OR `category_ids` LIKE '%,$category_id')";
        $objRs = $objDatabase->Execute($query);
        if ($objRs === false) {
            throw new DatabaseError("Error getting amount of entries of a category");
        }
        return intval($objRs->fields['amount']);
    }

    /**
     * Return just one article
     *
     * @param int $id Id of the article to get
     * @return boolean/array
     */
    public function getOneArticle($id)
    {
        $this->readArticles(true, 0, $id);
        $article = array_pop($this->articles);
        if (!isset($article)) {
            return false;
        }
        return $article;
    }

    /**
     * Set the sort value of an article
     *
     * @param int $id
     * @param int position
     * @throws DatabaseError
     * @global $objDatabase
     */
    public function setSort($id, $position)
    {
        global $objDatabase;

        $id = intval($id);
        $position = intval($position);

        $query = "
            UPDATE ".DBPREFIX."module_knowledge_articles
               SET sort=$position
             WHERE id=$id";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error sorting the article ".$id);
        }
    }


    /**
     * Hit an article
     *
     * If an article is viewed, increment the hit count value.
     * @param int $id
     * @throws DatabaseError
     * @global $objDatabase
     */
    public function hit($id)
    {
        global $objDatabase;

        $id = intval($id);
        $query = "
            UPDATE ".DBPREFIX."module_knowledge_articles
               SET hits=hits+1
             WHERE id=$id";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error 'hitting' an article ");
        }
    }

    /**
     * Save a user's vote
     *
     * @param int $value
     * @throws DatabaseError
     * @global $objDatabase
     */
    public function vote($id, $value)
    {
        global $objDatabase;

        $value = intval($value);
        $id = intval($id);

        $query = "
            UPDATE ".DBPREFIX."module_knowledge_articles
               SET votes=votes+1,
                   votevalue=votevalue+$value
             WHERE id=$id";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("error voting an article");
        }
    }

    /**
     * Return the most read items
     *
     * Instead of doing a new request PHP could do the job itself, but
     * for performance reasons I let MySQL do it.
     * @param int $lang
     * @param int $amount If amount is null, all rows are returned
     * @return array
     */
    public function getMostRead($lang=1, $amount=5)
    {
        $amount = intval($amount);
        $lang = intval($lang);

        $query = $this->basequery;
        $query .= "
            WHERE lang=$lang ORDER BY hits DESC";
        if ($amount != null) {
           $query .= " LIMIT $amount";
        }
        $articles = $this->readArticles(true, 0, 0, $query);
        return $articles;
    }

    /**
     * Return the best reted articles
     *
     * @param int $lang
     * @param int $amount if amount is null, all rows are returned
     * @return array
     */
    public function getBestRated($lang=1, $amount=5)
    {
        $amount = intval($amount);
        $lang = intval($lang);
        $query = "
            SELECT articles.id, articles.active,
                   articles.hits, articles.votes,
                   articles.votevalue AS value,
                   articles.category_ids,
                   articles.date_created, articles.date_updated,
                   content.lang, content.answer,
                   content.question, content.`index`,
                   (votevalue/votes) AS rating
              FROM `".DBPREFIX."module_knowledge_articles` AS articles
             INNER JOIN `".DBPREFIX."module_knowledge_article_content`
                   AS content ON articles.id=content.article
             WHERE lang=$lang
             ORDER BY rating DESC";
        if ($amount != null) {
            $query .= " LIMIT $amount";
        }
        $articles = $this->readArticles(true, 0, 0, $query);
        return $articles;
    }


    /**
     * Reset all votes
     * @global $objDatabase
     * @throws DatabaseError
     */
    public function resetVotes()
    {
        global $objDatabase;

        $query = "
            UPDATE ".DBPREFIX."module_knowledge_articles
               SET votes=0, votevalue=0";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("failed to reset the votes");
        }
    }


    /**
     * Insert the content of an article
     * @param int $id
     * @global $objDatabase
     * @throws DatabaseError
     */
    private function insertContent($id)
    {
        global $objDatabase;

        foreach ($this->insertContent as $values) {
            $lang = $values['lang'];
            $question = $values['question'];
            $answer = $values['answer'];
            $index = $values['index'];
            $query = "
                INSERT INTO ".DBPREFIX."module_knowledge_article_content (
                    article, lang, question, answer, `index`
                ) VALUES (
                    $id, $lang, '$question',
                    '$answer', '$index'
                )";
            if ($objDatabase->Execute($query) === false) {
                throw new DatabaseError("inserting category content failed");
            }
        }
    }


    /**
     * Delete the content of an article
     * @param int $id
     * @global $objDatabase
     * @throws DatabaseError
     */
    private function deleteContent($id)
    {
        global $objDatabase;

        $id = intval($id);
        $query = "
            DELETE FROM ".DBPREFIX."module_knowledge_article_content
             WHERE article=$id";
        if ($objDatabase->Execute($query) === false) {
            throw new DatabaseError("deleting article content failed");
        }
    }


    /**
     * Return the glossary entries
     * @access public
     * @param $lang The Language id
     * @return array
     */
    public function getGlossary($lang)
    {
        global $objDatabase;

        $query = $this->basequery."
            WHERE content.`index`!='0'
              AND articles.active=1
            ORDER BY content.`index`";
        $articles = $this->readArticles(true, $lang, 0, $query);
        $ret = array();
        foreach ($articles as $article) {
            $index = $article['content'][$lang]['index'];
            if (array_key_exists($index, $ret)) {
                $ret[$index][] = $article;
            } else {
                $ret[$index] = array($article);
            }
        }
        return $ret;
    }


    /**
     * Search in the articles for names
     *
     * Search the question  for the given
     * searchterm. Why not the answer? Well, if the system finds somethign
     * because the word is contained in the answer, the user doesn't see
     * that and could get confused.
     * @param string $searchterm
     * @param int $lang
     * @global $objDatabase
     * @return array
     */
    public function searchArticles($searchterm, $lang)
    {
        global $objDatabase;

        $searchterm = addslashes($searchterm);
        $query = $this->basequery ."
            WHERE question LIKE '%".$searchterm."%'";
        $articles = $this->readArticles(true, $lang, 0, $query);
        return $articles;
    }

}

?>
