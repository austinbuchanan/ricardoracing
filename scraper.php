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
	//headers for .csv output
	//header('Content-Type: text/csv; charset=utf-8');
	//header('Content-Disposition: attachment; filename=horses.csv');
	
	//open .csv file for writing and create headings for each horse's data
    $output = fopen('horses.csv', 'w');
	$heading_array = Array('Horse', 'Relative', 'Inbreeding Stats', 'Crosses', 'Lines', 'Blood%', 'Influence', 'AGR');
	
    //include the amazing simple dom parser that allows us to search through the html of the returned data
	include('simple_html_dom.php');
	
	//turn off notices related to dom conversion errors
	libxml_use_internal_errors(true);
	
	//take in values from index.htm for each filter and horse name list
	$horsenames = $_POST['horsenames'];
	$filter = $_POST['filter'];
	$crosses = $_POST['crosses'];
	$gens = $_POST['gens'];
	$influence = $_POST['influence'];

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
    
    for ($j = 0; $j < count($horse_name_array); $j++)
	{	
		array_push($checkbreed, "http://www.allbreedpedigree.com/".$horse_name_array[$j]);
	}
    
    
    ob_start();

    $page_data = '';
    
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

    foreach ($checkbreed as $cb)
    {
        $page_data = curl($cb);
        echo $page_data;
    }
	$cbpage = ob_get_clean();
	
	$cbpage = html_entity_decode($cbpage); //get rid of entities
	
	$first_column = array();
	$cbhtml = str_get_html($cbpage); 
    $breed = Array();
    foreach($cbhtml->find('title') as $title)
    {
        $breed[] = $title->plaintext;
    }
    //print_r($breed);
    for($i = 0; $i < count($horse_name_array); $i++)
    {
        if(strpos($breed[$i], "Quarter Horse") !== FALSE)
        {
             $curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
            //echo $curl_url."<br>";
            ob_start();
            $page_data = curl($curl_url);
            echo $page_data;
            $search_page = ob_get_clean();
            $search_html = str_get_html($search_page);
            $legend = $search_html->find('legend');
            if (!$legend)
            {
                array_push($urls, $curl_url);
                array_push($first_column, $display_names[$i]);
            }
            else
            {
                $curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
                //echo $curl_url."<br>";
                ob_start();
                $page_data = curl($curl_url);
                echo $page_data;
                $search_page = ob_get_clean();
                $search_html = str_get_html($search_page);
                //echo $search_html;
                foreach($search_html->find('td') as $col)
                {
                
                    if ($col->plaintext == $display_names[$i])
                    {
                        $next_col = $col->next_sibling();
                        if ($next_col->plaintext == "QUARTER HORSE")
                        {
                            foreach($col->find('a') as $link)
                            {
                                $href = $link->href;
                            }
                            $formatted_link = "http://www.allbreedpedigree.com/".$href;
                            $formatted_link = str_replace(' ', '+', $formatted_link);
                            //echo $formatted_link;
                            array_push($urls, $formatted_link);
                            array_push($first_column, $display_names[$i]);
                        }

                    }
                }
            }
        }
        else if(strpos($breed[$i], "Quarter Horse") === FALSE)
        {
            $curl_url = "http://www.allbreedpedigree.com/index.php?query_type=check&search_bar=linebreeding&hypo_sire=&hypo_dam=&what=done&sort=inf&border=0&h=".$horse_name_array[$i]."&g=".$gens."&crosses=".$crosses."&inf=".$inf_num."&all=".$filter."&sort=inf&t=&username=".$_SESSION['username']."&password=".$_SESSION['password'];
            //echo $curl_url."<br>";
            ob_start();
            $page_data = curl($curl_url);
            echo $page_data;
            $search_page = ob_get_clean();
            $search_html = str_get_html($search_page);
            $legend = $search_html->find('legend');
            if (!$legend && strpos($breed[$i], "Quarter Horse" !== FALSE))
            {
                array_push($urls, $curl_url);
                array_push($first_column, $display_names[$i]);
            }
            else
            {
                foreach($search_html->find('td') as $col)
                {
                    if ($col->plaintext == $display_names[$i])
                    {
                        $next_col = $col->next_sibling();
                        if ($next_col->plaintext == "QUARTER HORSE")
                        {
                            foreach($col->find('a') as $link)
                            {
                                $href = $link->href;
                            }
                            $formatted_link = "http://www.allbreedpedigree.com/".$href;
                            $formatted_link = str_replace(' ', '+', $formatted_link);
                            //echo $formatted_link;
                            array_push($urls, $formatted_link);
                            array_push($first_column, $display_names[$i]);
                        }
                    }
                }
            }
        }
    }
    
	ob_start();
	
	
	//go curling
	foreach ($urls as $url) 
	{ 
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1200);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
	
		curl_exec($ch); 
		curl_close($ch);
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
	//print_r($chunked_data);
	
	exit();
	
?>