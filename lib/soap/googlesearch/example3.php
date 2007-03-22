<?php

/*
* Example to access Google cached pages through GoogleSearch for PHP.
*/

require_once 'GoogleSearch.php';

$gs = new GoogleSearch();

//set Google licensing key
$gs->setKey("0wVp6PZQFHII57dTjNYLK+sXXzDLADZA");

$gs->setQueryString("Astalavista secure CMS");	//set query string to search.

//set few other parameters (optional)
$gs->setMaxResults(10);	//set max. number of results to be returned.
$gs->setSafeSearch(true);	//set Google "SafeSearch" feature.

//call search method on GoogleSearch object
$search_result = $gs->doSearch();

//check for errors
if(!$search_result)
{
	if($err = $gs->getError())
	{
		echo "<br>Error: " . $err;
		exit("<br>Exiting...");
	}
}

//output results to browser

//output info common to the whole search
echo "Document Filtering: " . $search_result->getDocumentFiltering() . "<br>";
echo "Search Comments: " . $search_result->getSearchComments() . "<br>";
echo "EstimatedTotalResultsCount: " . $search_result->getEstimatedTotalResultsCount() . "<br>";
echo "Is Estimate Exact: " . $search_result->getEstimateIsExact() . "<br>";
echo "Search Query: " . $search_result->getSearchQuery() . "<br>";
echo "Start Index: " . $search_result->getStartIndex() . "<br>";
echo "End Index: " . $search_result->getEndIndex() . "<br>";
echo "Search Tips: " . $search_result->getSearchTips() . "<br>";
echo "Search Time: " . $search_result->getSearchTime() . "<br>";


echo "<br><b>Directory Categories: </b>";

$dcat = $search_result->getDirectoryCategories();
foreach($dcat as $direlement)
{
	echo "<br>Full Viewable Name: " . $direlement->getFullViewableName();
	echo " Special Encoding: " . $direlement->getSpecialEncoding();
}

//output individual components of each result
echo "<br><b>Result Elements: </b>";

$re = $search_result->getResultElements();

foreach($re as $element)
{
	echo "<p>";
	echo "<br>Title: " . $element->getTitle();
	echo " URL: " . $element->getURL();
	echo "<br>Snippet: " . $element->getSnippet();
	echo "<br>Summary: " . $element->getSummary();
	echo "<br>Host Name: " . $element->getHostName();
	echo " Related Information Present?: " . $element->getRelatedInformationPresent();
	echo " Cached Size: " . $element->getCachedSize();
	echo "<br>Directory Title: " . $element->getDirectoryTitle();

	$dircat = $element->getDirectoryCategory();

	echo "<br>Full Viewable Name: " . $dircat->getFullViewableName();
	echo " Special Encoding: " . $dircat->getSpecialEncoding();
}

?>
