<?php
	//for (i=0;i<$horse_array_length;i++)
	/*
	{
		$horse_array_length = horse_array.length;
		$horsename = $horse_array[i];
		$generations will be a number from 4 to 10 selected by drop down
		$crosses will be a number from 2 to 10 selected by drop down
		$influence will be a dropdown with various relationship comparisons
		$filter will be all horses, cross dups, dups only, males only, or females only and selected by drop down
		$url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horsename."&g=".$generations."&crosses=".$crosses."&inf=".$influence."&all=".$filter."&sort=inf&t=&username=rfgandy@yahoo.com&password=dante@14";
	} 
	*/
	
    
    // Defining the basic cURL function
    function curl($url) {
        // Assigning cURL options to an array
        $options = Array(
            CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
            CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
            CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
            CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
            CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
            CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  // Setting the useragent
            CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
        );
         
        $ch = curl_init();  // Initialising cURL 
        curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
        $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
        curl_close($ch);    // Closing cURL 
        return $data;   // Returning the data from the function 
    }


	 function scrape_between($data, $start, $end){
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }
	
    $scraped_website = curl("http://www.allbreedpedigree.com");  // Executing our curl function to scrape the webpage http://www.example.com and return the results into the $scraped_website variable

	$horsename="check+cathy";
	//use reg ex and a loop to change white space in horse names to + or find function that does that
    $url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horsename."&g=9&crosses=2&inf=0&all=Dups+Only&sort=inf&t=&username=rfgandy@yahoo.com&password=dante@14";    // Assigning the URL we want to scrape to the variable $url
    $results_page = curl($url); // Downloading the results page using our curl() funtion
     
    /*$results_page = scrape_between($results_page, "<div id=\"main\">", "<div id=\"sidebar\">"); // Scraping out only the middle section of the results page that contains our results
     
    $separate_results = explode("<td class=\"image\">", $results_page);   // Expploding the results into separate parts into an array
         
    // For each separate result, scrape the URL
    foreach ($separate_results as $separate_result) {
        if ($separate_result != "") {
            $results_urls[] = "http://www.imdb.com" . scrape_between($separate_result, "href=\"", "\" title="); // Scraping the page ID number and appending to the IMDb URL - Adding this URL to our URL array
        }
    }
     
    print_r($results_urls); // Printing out our array of URLs we've just scraped*/
	
	echo $results_page;
?>