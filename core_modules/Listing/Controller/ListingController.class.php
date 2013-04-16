<?php
namespace Cx\Core_Modules\Listing\Controller;

class ListingException extends \Exception {}

/**
 * Creates rendered lists (paging, filtering, sorting)
 * @author ritt0r <drissg@gmail.com>
 *
 */
class ListingController {
	/**
	 * Turn paging off
	 * @var int
	 */
	const PAGING_OFF = 0;
	/**
	 * No AJAX, traditional page requests
	 * @var int
	 */
	const PAGING_NON_AJAX = 1;
	/**
	 * Paged HTML is transferred via AJAX
	 * @var int
	 */
	const PAGING_HTML_AJAX = 2;
	/**
	 * Only data is transferred via AJAX
	 * @var int
	 */
	const PAGING_DATA_AJAX = 3;
	/**
	 * Paging is done client side, no additional data is transferred after page load
	 * @var unknown
	 */
	const PAGING_CLIENT_ONLY = 4;
	const SORTING_OFF = 8;
	const SORTING_NON_AJAX = 9;
	const SORTING_HTML_AJAX = 10;
	const SORTING_DATA_AJAX = 11;
	const SORTING_CLIENT_ONLY = 12;
	const FILTERING_OFF = 16;
	const FILTERING_NON_AJAX = 17;
	const FILTERING_HTML_AJAX = 18;
	const FILTERING_DATA_AJAX = 19;
	const FILTERING_CLIENT_ONLY = 20;
	
	/**
	 * How many lists are there for this request
	 * @var int
	 */
	protected static $listNumber = 0;
	
	/**
	 * Entity class name
	 * @var String
	 */
	protected $entityClass = null;
	
	/**
	 * Callback function to get data
	 * @var Callable
	 */
	protected $callback = null;
	
	/**
	 * Offset to start from
	 * @var int
	 */
	protected $offset = 0;
	
	/**
	 * How many results are returned
	 * @var int
	 */
	protected $count = 0;
	
	/**
	 * Order by array($field=>asc/desc)
	 * @var Array
	 */
	protected $order = array();
	
	/**
	 * Criteria the result must match
	 * @var Array
	 */
	protected $criteria = array();
	
	/**
	 * Constructs a new Lister
	 * @todo Initialize $offset (request) and $count (settings-db)
	 */
	public function __construct($entityClass, $crit = array(), $options = array()) {
		// init handlers (filtering, paging and sorting)
		$this->handlers = array(
			new FilteringController(),
			new SortingController(),
			new PagingController,
		);
		
		if (is_callable($entityClass)) {
			\DBG::msg('Init ListingController using callback function');
			$this->callback = $entityClass;
		} else {
			\DBG::msg('Init ListingController using entity class');
			$this->entityClass = $entityClass;
		}
		$this->criteria = $crit;
	}
	
	/**
	 * Initializes listing for the given object
	 * @param Cx\Core_Modules\Listing\Model\Listable $listableObject
	 * @param int $mode (optional) A combination of the paging, sorting and filtering modes above (use |)
	 */
	public function getData() {
		foreach ($this->handlers as $handler) {
			$handler->handle($this->offset, $this->count, $this->order, $this->criteria);
		}
		
		// handle ajax requests
		if (false /* ajax request for this listing */) {
			$jd = new \Cx\Core\Json\JsonData();
			$jd->json(array(
				'filtering' => $this->getAjaxFilteringData(),
				'sorting' => $this->getAjaxSortingData(),
				'paging' => $this->getAjaxPagingData(),
			), true);
			// JsonData->json() does call die() itself
		}
		
		// If a callback was specified, use it:
		if (is_callable($this->callback)) {
			$callable = $this->callback;
			return $callable($this->offset, $this->count, $this->order, $this->criteria);
		}
		
		if (!class_exists($this->entityClass)) {
			throw new ListingException('No such entity "' . $this->entityClass . '"');
		}
		
		$repository = \Env::get('em')->getRepository($this->entityClass);
		if (!$repository) {
			throw new ListingException('No such entity "' . $this->entityClass . '"');
		}
		$entities = $repository->findBy($this->criteria);
		
		// @todo: check if entities should be encapsulated in a class
		$data = new \Cx\Core_Modules\Listing\Model\DataSet($entities);
		
		// return calculated data
		return $data;
	}
	
	/**
	 * @todo: implement, this is just a draft!
	 */
	public function toHtml() {
		return '';//$this->getPagingControl();
	}
	
	public function __toString() {
		return $this->toHtml();
	}
	
    /**
     * Calculates the paging
     * @throws PagingException when paging type is unknown (see class constants)
     * @return mixed Array for type DATA_AJAX, HTML as string otherwise
     * @todo NON_AJAX mode
     */
    protected function getPaging() {
    	switch ($this->listableObject->getType()) {
    		case DATA_AJAX:
    			return $this->listableObject->getData($this->offset, $this->count);
    			break;
    		case HTML_AJAX:
    			$html = $this->listableObject->preRender($this->offset, $this->count);
    			for ($i = $this->offset; $i < ($this->offset + $this->count); $i++) {
    				$html .= $this->listableObject->renderEntry($i);
    			}
    			$html .= $this->listableObject->postRender($this->offset, $this->count);
    			return $html;
    			break;
    		case NON_AJAX:
    			break;
    		default:
    			throw new PagingException('Unknown paging type "' . $this->listableObject->getType() . '"');
    			break;
    	}
    }
    
    /**
     * This renders the template for paging control element
     * @todo templating!
     * @todo show only a certain number of pages
     */
    protected function getPagingControl() {
    	$numberOfPages = ceil($this->listableObject->getCount() / $this->count);
    	$activePageNumber = ceil($this->offset / $this->count);
    	$html = '';
    	// render goto start
    	for ($pageNumber = 1; $pageNumber <= $numberOfPages; $pageNumber++) {
    		if ($pageNumber == $activePageNumber) {
    			// render page without link
    			$html .= $pageNumber;
    			continue;
    		}
    		// render page with link
    		$html .= $pageNumber;
    	}
    	// render goto end
    }
}
