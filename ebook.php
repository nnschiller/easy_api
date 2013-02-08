
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Test eBook search</title>
		<link rel="stylesheet" href="themes/CougCouver.min.css" />
		<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile.structure-1.2.0.min.css" />
		<script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
		<script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
		<?php require_once('magpierss/rss_fetch.inc'); ?>
	</head>
	<body>
		<div data-role="page" id="page1" data-theme="a">
			<div data-role="header" data-position="inline">
				<h1>WSU Vancouver eBook Search</h1>
			</div> <!-- Header Div -->
			<div data-role="content" data-theme="a" class="main">
				<!-- <div class="main"> -->
				
	<!--My JQueryMobile page structure is fully borked. This needs troubleshooting.
		Areas of concern:
			x multiple pages of returns (not artificially limited to 10 results
			x footer on results page that return user for a new search
			 -->			
	
	<?php //Begin PHP
    //turn on full error reports for development purposes - should be turned off for production environment
    //error_reporting(E_ALL);
    
    
// set Variables:    
    //set user API key for Worldcat API
    $WorldCatAPIKey = '6ACsgajYuGZI77QFqh32LdId8J6yWAvhLGbTF0MaCfHzLYbF4EnOPDLpbG27hAifTp7UxfiC3uue4CZD';
	//set LibraryThing API Key (not currently being used)
	$librarything_key = '674972a3af83ad9967f477aa81800d20'; // replace with your LibraryThing Key
	//set default query
	$q = isset($_REQUEST['AddWorldCatSearch-SearchString']) ? $_REQUEST['AddWorldCatSearch-SearchString'] : null;
	//set default search type
		// $type = 'srw.kw';
	$type = isset($_REQUEST['AddWorldCatSearch-SearchType']) ? $_REQUEST['AddWorldCatSearch-SearchType'] : null;	
	//number of results to display
	//$limit = isset($_REQUEST['AWC_libraryLimit']) ? $_REQUEST['AWC_libraryLimit'] : null;
	$limit = 'ws2';
	$boole = '+and+srw.mt+any+%22ebk%22+not+srw.mt+any+%22gpb%22+not+srw.mt+any+%22cnp%22+not+srw.mt+any+%22deg%22';
	$format = 'rss'; // rss and atom are the available options
	// $cformat = 'chicago'; 
	// The valid citation format values for the cformat field are apa, chicago, harvard, mla, turabian, or all. 
	$cformat = isset($_REQUEST['AddWorldCatSearch-format']) ? $_REQUEST['AddWorldCatSearch-format'] : null;
									
//Build Opensearch/SRU compliant search query
if ( strlen($q) > 0) {
		$searchURL = 'http://worldcat.org/webservices/catalog/search/worldcat/opensearch?q='; 
			// $searchURL .=  $type .'%3D%22' ;
			// Can be set to allow users to choose search type (also check $type variable on above)
		$searchURL .= $_REQUEST['AddWorldCatSearch-SearchType'] .'%3D%22';
		$searchURL .=  urlencode($_REQUEST['AddWorldCatSearch-SearchString']); 
		$searchURL .=  '%22';
    	$searchURL .=  '+and+srw.li+any+%22' .$limit;
    	$searchURL .=  '%22' .$boole;
    		//Can be set to search any WorldCat Symbol, set it to your library.
    		//$searchURL .= $_REQUEST['AddWorldCatSearch-LibraryLimit']; 
    		//$searchURL .=  '%22';
    	$searchURL .= '&maximumRecords=5';
    	$searchURL .= '&format='.$format; 
    	$searchURL .= '&cformat='.$cformat; 
    	$searchURL .= '&wskey='. $WorldCatAPIKey;
    		//Likely need to tweak this w/ max records and start to get multiple pages of returns working
    
    
	// display URL for testing purposes only.
		 //echo $searchURL;
		 
	// fetch the OpenSearch result
	$rss = fetch_rss($searchURL);
	echo "<ul data-role='listview' data-theme='c'>";
	// if there was a result
	if ($rss) {
		// for each item
		foreach ($rss->items as $item) {
			// get descriptive data
			$title = $item['title'];
			$author = $item['author_name'];
			$summary = $item['description'];
			$link = $item['link'];
			$content = $item['content']['encoded'];
			//$oclcNum = $item['oclcterms']['recordIdentifier']
			// I don't know why the above line doesn't work. The 2 lines below do the job, but are inelegant.
			$oclcNum = $item['link'];
			$oclcNum = ltrim($oclcNum, 'http://worldcat.org/oclc/');
			
			
			
			// add item values to an array 
				//and display in a list (is there a better way to do this w/ the $r array below?)
			echo '<li><h3><a href="http://griffin.wsu.edu/search~S/o?';
			echo $oclcNum ;
			echo '">';
			echo $title ;
			echo '</a></h3><p></p>';
			//echo $author;
			//echo $summary;
			echo $content ;
			echo '</li>';
			
			$a=array("author"=>$author,"title"=>$title,"summary"=>$summary,"link"=>$link,"content"=>$content, "oclcNum"=>$oclcNum);
			// add the item to the result array
			$r[$c] = $a;
			// increment the result counter
			$c++;	  
		} // end foreach item
		
		// Wrap below in a footer?
	echo "Try another search? (make this a link)"; } // end if rss
	echo "</ul>";	
	
	// return the result array
	return $r;
	

} else {
?>
<!--Build form to capture user search terms-->
<form action="<?php echo basename(__FILE__); ?>" method="get">
 <!-- Get Users search query --> 
<div>
    <label for="AddWorldCatSearch-SearchString">Search for eBooks in WSU libraries: </label>
    <input type="search" id="AddWorldCatSearch-SearchString" name="AddWorldCatSearch-SearchString" value="" />
</div>

<!-- This allows users to choose a citation style -->

	<div data-role='fieldcontain' data-theme="c">
    	<label for="AddWorldCatSearch-format">Citation Style:</label>
    	<select data-inset="true" name="AddWorldCatSearch-format" id="AddWorldCatSearch-format">
    	<option value="chicago">Chicago</option>
    	<option value="apa">APA</option>
    	<option value="harvard">Harvard</option>
    	<option value="mla">MLA</option>
    	<option value="turabian">Turabian</option>
    	</select>
    </div>
<!-- This allows users to choose kw, title, or author field searches -->    	
	<div data-role='fieldcontain' data-theme="c">
		<label for="AddWorldCatSearch-SearchType">Search Type:</label>
    	<select name="AddWorldCatSearch-SearchType" id="AddWorldCatSearch-SearchType" >
   		<option value="srw.kw">Keyword</option>
    	<option value="srw.ti">Title</option>
    	<option value="srw.au">Author</option>
    </select>
	</div>
	 <!-- Our form is manually set to one OCLC symbol for simplicity's sake.
<div data-role='fieldcontain'>
    <label for="AddWorldCatSearch-LibraryLimit">Limit by Specific Library (OCLC Symbol)</label>
    <input type="text" id="AddWorldCatSearch-LibraryLimit" name="AddWorldCatSearch-LibraryLimit" value="" />
</div>
-->

<div>	
<input type="submit" value="Search"/>
</div>

</div><!-- /page -->
</form>
<?php
}
?>

<!-- RESULTS PAGE 
This obviously is in need of much work.
<div data-role="page" id="results_one" data-title="results_one">
-->

 <!--content div-->
<div data-role="footer" data-theme="a">
	<h4><a href="http://library.vancouver.wsu.edu">Library Home</a> <a href="http://www.worldcat.org/" class="ui-btn-right" target="_blank"><img border="0" src="http://www.worldcat.org/images/wc_badge_80x15.gif?ai=Washington_nnschiller" 
		width="80" height="15" alt="WorldCat lets people access the collections of libraries worldwide [WorldCat.org]" title="WorldCat lets people access the collections of libraries worldwide [WorldCat.org]"></a></h4>
</div><!--footer div-->
			
	</div> <!--main div-->
	</body>
</html>