<?php
/**
 * Search response class
 *
 * Helper class for the search response. Is going to be
 * turned into a JSON object for communcation through ajax.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */

namespace Cx\Modules\Knowledge\Controller;

/**
 * Search response class
 *
 * Helper class for the search response. Is going to be
 * turned into a JSON object for communcation through ajax.
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */
class SearchResponse
{
    /**
     * Status code
     *
     * @var int
     */
    public $status = 1;

    /**
     * Response
     *
     * @var string
     */
    public $content = "";
}
