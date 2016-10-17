<?php
session_start();
/*
	Licensed under The MIT License

	Copyright (c) 2016 David Pridemore, Austin Buchanan
	* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the
	* Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
	* and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
	* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

	* Redistributions of files must retain the above copyright notice.
	*
	* @author David Pridemore <me@davidpridemore.com>
	* @author Austin Buchanan <abuchanan5@my.apsu.edu>
*/

	//open .csv file for writing and create headings for each horse's data
	$output = fopen('horses.csv', 'w');
	$progress = fopen('progress.txt', 'w');
	$heading_array = Array('Horse', 'Relative', 'Inbreeding Stats', 'Crosses', 'Lines', 'Blood%', 'Influence', 'AGR');

    //include the amazing simple dom parser that allows us to search through the html of the returned data
	include('simple_html_dom.php');

	//turn off notices related to dom conversion errors
	libxml_use_internal_errors(true);

	//take in values from index.htm for each filter and horse name list
	$horsenames = $_REQUEST['horsenames'];
	$filter = $_REQUEST['filter'];
	$crosses = $_REQUEST['crosses'];
	$gens = $_REQUEST['gens'];
	$influence = $_REQUEST['influence'];

	$inf_num = 0;
	//influence is weird. the filter menu on allbreedpedigree displays in NumxNum format but requires a decimal in the url
	switch($influence)
	{
		case "All":
			break;
		case "> 2x3":
			$inf_num = 9.375;
			break;
		case "> 3x3":
			$inf_num = 6.25;
			break;
		case "> 3x4":
			$inf_num = 4.6875;
			break;
		case "> 4x4":
			$inf_num = 3.125;
			break;
		case "> 4x5":
			$inf_num = 2.34275;
			break;
		case "> 5x5":
			$inf_num = 1.5625;
			break;
		case "> 5x6":
			$inf_num = 1.171875;
			break;
		case "All2":
			$inf_num = 0.78125;
			break;
	}

	//put each horse's name in an array based on a single horse's name entered on each line
	$horse_name_array = (preg_split('/[\n]+/', $horsenames));
	fwrite($progress, "Horse names sent to server. \n");

	/* declare urls, checkbreed and display_names arrays. urls will contain URLs to send to allbreedpedigree.com, display
	names is for .csv formatting, and checkbreed will contain horse names to check <td>s for "Thoroughbred" or "Quarter
	Horse" */
	$urls = Array();
	$checkbreed = Array();
	$display_names = Array();

	//loop through each horse's name and remove annoying whitespace and plus signs (+) from the horses' names for the URL
	for ($i = 0; $i < count($horse_name_array); $i++)
	{
        array_push($display_names, $horse_name_array[$i]);
		$horse_name_array[$i] = str_replace(' ', '+', $horse_name_array[$i]);
		$horse_name_array[$i] = trim(preg_replace('/\s+/', '', $horse_name_array[$i]));
	}



	//make display names in .csv uppercase based on legacy app and client preference
	$display_names = array_map('strtoupper', $display_names);

	//replace excess whitespace for .csv display purposes
    for($i = 0; $i < count($display_names); $i++)
    {
        $display_names[$i] = trim(preg_replace('/\s+/', ' ', $display_names[$i]));
    }

	fwrite($progress, "Horse names formatted for database query. \n");
	//begin checking breeds
  for ($j = 0; $j < count($horse_name_array); $j++)
	{
		array_push($checkbreed, "http://www.allbreedpedigree.com/".$horse_name_array[$j]);
	}

	fwrite($progress, "Checking results for invalid names and non-Quarter Horses. \n");
		//start output buffer
    ob_start();

    $page_data = '';
		//curling function for breed checking purposes
    function curl($url)
    {
        // assigning curl options to array
        $options = Array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_AUTOREFERER => TRUE,
            CURLOPT_CONNECTTIMEOUT => 1200,
            CURLOPT_TIMEOUT => 1200,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  //setting the useragent
            CURLOPT_URL => $url, //setting curl's url option with the $url variable passed into the function
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
		//curl each url in the checkbreed array
    foreach ($checkbreed as $cb)
    {
        $page_data = curl($cb);
        echo $page_data;
	}
	fwrite($progress, "Horse names sent to database. \n");

	$cbpage = ob_get_clean(); //set contents of output buffer equal to page
	fwrite($progress, "Combing through data to delete non-Quarter Horses. \n");
	$cbpage = html_entity_decode($cbpage); //get rid of entities

	$first_column = array(); //used in .csv file to display horse name in first column of every horse table
	$cbhtml = str_get_html($cbpage); //convert ob string to html
    $breed = Array(); //used to store page titles
		//get each title from each html page
    foreach($cbhtml->find('title') as $title)
    {
        $breed[] = $title->plaintext;
    }
    //go through each page title
    $incrementer = 1;

    for($i = 0; $i < count($horse_name_array); $i++)
    {
        fwrite($progress, "Horse ". $incrementer ." of ".count($horse_name_array)." checked \n");
        $incrementer++;
				//if the page title does have the word Quarter Horse in it, perform these operations
        if(strpos($breed[$i], "Quarter Horse") !== FALSE)
        {
						//check to see if there are multiple horses with the same name
            $curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
						//start output buffer
						ob_start();
						//curl url and store contents in $page_data
						$page_data = curl($curl_url);
            echo $page_data;
						//store contents of output buffer into $search_page and convert it to html
            $search_page = ob_get_clean();
            $search_html = str_get_html($search_page);
						//search for "legend" tag. this tag only appears if there are multiple horses with the same name.
            $legend = $search_html->find('legend');
						//if there is no legend tag, the horse is unique and is a quarter horse. store the above url into urls array to be used later.
            if (!$legend)
            {
                array_push($urls, $curl_url);
								//push horse name to first_column array to be used in .csv output
                array_push($first_column, $display_names[$i]);
            }
						//if there is a legend tag, the horse is not unique. search for the horse that is a quarter horse within the page.
            else
            {
								//get "Multiple Horse Occurrence" page
                $curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
								//start output buffer
								ob_start();
								//curl url and store contents in $page_data
                $page_data = curl($curl_url);
                echo $page_data;
								//store contents of output buffer into $search_page and convert it to html
                $search_page = ob_get_clean();
                $search_html = str_get_html($search_page);
								//comb through tds to find the one whose innertext equals "Quarter Horse"
                foreach($search_html->find('td') as $col)
                {
										//if the td contains the name of the horse, peek at the adjacent td and look at its contents.
                    if ($col->plaintext == $display_names[$i])
                    {
                        $next_col = $col->next_sibling();
												//if the next td contains the words "Quarter Horse", get the link from the td you are currently in
                        if ($next_col->plaintext == "QUARTER HORSE")
                        {
                            foreach($col->find('a') as $link)
                            {
                                $href = $link->href;
                            }
														//format the link to be consistent with the urls array and push it to the urls array to be used later
                            $formatted_link = "http://www.allbreedpedigree.com/".$href;
                            $formatted_link = str_replace(' ', '+', $formatted_link);
                            array_push($urls, $formatted_link);
														//push horse name to first_column array to be used in .csv output
                            array_push($first_column, $display_names[$i]);
                        }

                    }
                }
            }
        }
				//if the page title contains the word "Appendix" within it, perform these operations
        else if(strpos($breed[$i], "Appendix") !== FALSE)
        {
              //check to see if there are multiple horses with the same name
              $curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
              //start output buffer
              ob_start();
              //curl url and store contents in $page data
              $page_data = curl($curl_url);
              echo $page_data;
              //store contents of output buffer into $search_page and convert it to html
              $search_page = ob_get_clean();
              $search_html = str_get_html($search_page);
              //search for "legend" tag. this tag only appears if there are multiple horses with the same name.
              $legend = $search_html->find('legend');
              /*if there is no legend tag, and if the title contains the words "appendix", the horse is unique and is a quarter horse.
              store the above url into urls array to be used later. i know that the logic is weird considering this block of code only
              executes if the title doesn't contain "appendix", but it serves to filter out non-quarter horses from the query.
              if the horse has no breed in the title, it will be added to the urls array as well.*/
              if (!$legend && strpos($breed[$i], "Appendix") !== FALSE)
              {
                  array_push($urls, $curl_url);
                  //push horse name to first_column array to be used in .csv output
                  array_push($first_column, $display_names[$i]);
              }
              //if there is a legend tag, the horse is not unique. search for the horse that is an appendix within the page.
              //this logic automatically assumes that you are taken to the "Multiple Horse Occurrence" page, so no curling is needed
              else
              {
                //get "Multiple Horse Occurrence" page
                $curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
                //start output buffer
                ob_start();
                //curl url and store contents in $page data
                $page_data = curl($curl_url);
                echo $page_data;
                //store contents of output buffer into $search_page and convert it to html
                $search_page = ob_get_clean();
                $search_html = str_get_html($search_page);
                  //comb through tds to find the one that contains the words "appendix"
                  foreach($search_html->find('td') as $col)
                  {
                    //if the td contains the name of the horse, peek at the adjacent td and look at its contents.
                      if ($col->plaintext == $display_names[$i])
                      {
                          $next_col = $col->next_sibling();
                          //if the next td contains the words "appendix", get the link from the td you are currently in

                              foreach($col->find('a') as $link)
                              {
                                  $href = $link->href;
                              }
                              //format the link to be consistent with the urls array and push it to the urls array to be used later
                              $formatted_link = "http://www.allbreedpedigree.com/".$href;
                              $formatted_link = str_replace(' ', '+', $formatted_link);
                              array_push($urls, $formatted_link);
                              //push horse name to first_column array to be used in .csv output
                              array_push($first_column, $display_names[$i]);

                      }
                  }
              }
        }
				//if the page title does not have the words "Quarter Horse" within it, and the horse is not an appendix, perform these operations
        else if(strpos($breed[$i], "Quarter Horse") === FALSE)
        {
						//check to see if there are multiple horses with the same name
						$curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
						//start output buffer
						ob_start();
						//curl url and store contents in $page data
						$page_data = curl($curl_url);
            echo $page_data;
						//store contents of output buffer into $search_page and convert it to html
            $search_page = ob_get_clean();
            $search_html = str_get_html($search_page);
						//search for "legend" tag. this tag only appears if there are multiple horses with the same name.
            $legend = $search_html->find('legend');
						/*if there is no legend tag, the horse is unique and is a quarter horse.*/
            if (!$legend)
            {
                array_push($urls, $curl_url);
								//push horse name to first_column array to be used in .csv output
                array_push($first_column, $display_names[$i]);
            }
						//if there is a legend tag, the horse is not unique. search for the horse that is a quarter horse within the page.
            //this logic automatically assumes that you are taken to the "Multiple Horse Occurrence" page, so no curling is needed
            else
            {
								//comb through tds to find the one that contains the words "Quarter Horse"
                foreach($search_html->find('td') as $col)
                {
										//if the td contains the name of the horse, peek at the adjacent td and look at its contents.
                    if ($col->plaintext == $display_names[$i])
                    {
                        $next_col = $col->next_sibling();
												//if the next td contains the words "Quarter Horse", get the link from the td you are currently in
                        if ($next_col->plaintext == "QUARTER HORSE")
                        {
                            foreach($col->find('a') as $link)
                            {
                                $href = $link->href;
                            }
														//format the link to be consistent with the urls array and push it to the urls array to be used later
                            $formatted_link = "http://www.allbreedpedigree.com/".$href;
                            $formatted_link = str_replace(' ', '+', $formatted_link);
                            //echo $formatted_link;
                            array_push($urls, $formatted_link);
														//push horse name to first_column array to be used in .csv output
                            array_push($first_column, $display_names[$i]);
                        }
                    }
                }
            }
        }
    }
	fwrite($progress, "Checked for non-Quarter Horses and removed them from the data. \n");
	ob_start();


	//go curling
	$incrementer = 0;

	foreach ($urls as $url)
	{
		$urlcount = count($urls);
		$incrementer++;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1200);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1200);

		curl_exec($ch);
		curl_close($ch);
		fwrite($progress, "Horse ". $incrementer ." of ".$urlcount." processed \n");
	}

	//output returned curl data as $page
	$page = ob_get_clean();

	//get rid of all the &lt; and &gt; and other entities in the data
	$page = html_entity_decode($page);

	//parse the string of data in $page as html in $html
	$html = str_get_html($page);


	//start output buffering again
	ob_start();

	//declare full_data array
    $full_data = array();
	fwrite($progress, "Beginning to format data for display in .csv. \n");
	//simple dom parser find for all images and get rid of them with empty strings
	foreach($html->find('img') as $images)
	{
		$images->outertext = '';
	}

	//get rid of header class <td> elements
	foreach($html->find('.header') as $headers)
	{
		$headers->outertext = '';
	}

	//find <td> elements and get rid of all their attributes
	foreach($html->find('td') as $cells)
	{
		$cells->class = null;
		$cells->onmousedown = null;
		$cells->align = null;
		$cells->colspan = null;
	}


	//find all the data between the <center> elements without a class
	foreach($html->find('center[!class]') as $horse)
	{
        //echo $horse;
		//remove all the html besides <td><b><tr> and store in $horse
		$horse = strip_tags($horse,'<td><b><tr>');
		//parse $horse as HTML again
		$horse = str_get_html($horse);
		//find all the empty <b><td><tr> and get rid of them
		foreach($horse->find('b, td, tr') as $element)
		{
			if(trim($element->innertext) == '')
			{
				$element->outertext = '';
			}
		}

		//declare rowData array
		$rowData = array();

		//find all the <tr> and <b> elements to find the rows
		foreach($horse->find('tr, b') as $row)
		{
			//declare horse_data array
			$horse_data = array();

			//store <td> element values in plaintext in $horse data
			foreach($row->find('td') as $cell)
			{
				$horse_data[] = $cell->plaintext;
			}

			//put the <td> stuff from $horse_data[] into "rows" in $rowData[]
			$rowData[] = $horse_data;
		}

		//put the "rows" into $full_data
        $full_data[] = $rowData;

	}
	//declare raw_data array
    $raw_data = array();

	//loop through $full_data and return the big list of unchunked horse data
    foreach ($full_data as $fd)
    {
        $raw_data[] = $fd['3'];
    }
	//cut up the horse data into chunks of 7 <td>'s (cells) for display in .csv
    $chunked_data = array();
    foreach($raw_data as $rd)
    {
        $chunked_data[] = array_chunk($rd, 7);
    }

    //adds display names to the first column of each row for the corresponding horse

	for ($i = 0; $i < count($chunked_data); $i++)
    {
        for ($j = 0; $j < count($chunked_data[$i]); $j++)
        {
            array_unshift($chunked_data[$i][$j], $first_column[$i]);
        }
    }
	//go through the chunked data
	$i=0;
	foreach ($chunked_data as $cd)
    {
		//remove the inbreeding coefficient number from the bottom of the results and place as the top per client preference

		$coef = array_pop($cd);
		$coef = implode($coef);
		$coef = preg_replace("/[^\d,.]/", "", $coef);$coef = $coef."%";

        $sort_array = array($first_column[$i], substr($coef, 1), "***");
        fputcsv($output, $sort_array);
		//output headings after coefficient number
		fputcsv($output, $heading_array);

		//remove numbers from horse names for duplicate horses.
        foreach ($cd as $second)
        {
                $second['1'] = preg_replace('/\d*$/', '', $second['1']);
                fputcsv($output, $second);
        }
		fwrite($output, "\n");
		$i++;
    }


	//scraped tables contains all the formatted data for .csv
	$scraped_tables = ob_get_clean();

	//output the data for .csv
	fwrite($output, $scraped_tables);
	fwrite($progress, ".csv is ready. \n");
	//print_r($chunked_data);
	ob_end_clean();
	unlink('progress.txt');
	fwrite($progress, "Downloading. \n");
	fwrite($progress, "Query finished. \n");
	exit();

?>
