$("#submit-horses").click(function(event){ //event handler for submit button of horse name form
    event.preventDefault(); //stops default action of submit button/textarea stays populated after click
    var horse_array = $("#horses-list").val().split("\n"); //List of horse names taken from HTML form split by next line rows in the textarea element
    var data_array = [ //heading names for excel spreadsheet and array to which values are pushed for final output in .csv
        ["Horse Name",
        "Other Horse Name",
        "Inbreeding Stats",
        "Crosses",
        "Lines",
        "Blood %",
        "Influence",
        "AGR"/*,
		"Testfield1",
		"Testfield2"*/]
    ];

    horse_array = $.map(horse_array, function(item) {
        return item.toUpperCase();
    }); //returns the name of the horse(s) from the textarea in all caps
	//may change this to use .charat(0) to capitalize only the first letter of each word with a .split of "\s"

    
    var horses = { //JS Object for JSON weirdness
        horses: horse_array //assign values of horse array to horses object
    };
    var horse_json = JSON.stringify(horses); //JSON.stringify turns a Javascript object into JSON text and stores that JSON text in a string.
	//In this instance, the horses object with the horse_array values into horse_json

    var serverUrl = //"http://localhost:8080/scraper_example.php";
	"https://hidden-fjord-34453.herokuapp.com/server.php"; //declares the server URL for .post...need to write a new php file and find a server/web host
	

    $.post(serverUrl, horse_json, function(data){ //jQuery.post( url [, data ] [, success ] [, dataType ] )
        $.each(data, function(key, value){ //jQuery.each( array, callback )
            if (isValidPage(value)){ //if page is valid...
                var processed_html = processHTML(value, key); //declare processed_html var with value of processHTML function
                $.each(processed_html, function(index, value){
                    data_array.push(value);
                });
            }
        }); //end of .each callback function

        var processed_csv = arrayToCSV(data_array); //call arrayToCSV function
        downloadCSV(processed_csv); //call downloadCSV function
    }, "json"); //end of .post success function and json datatype
}); //end of submit event handler

function isValidPage(html){ //check to see if the page is valid
    if (html.search("not on record") === -1){
        return true;
    } else {
        return false;
    }
}

function processHTML(html_string, name){ //start processHTML function
    var horse_rows = []; //declare horse_rows array
    html_string = html_string.replace(/\\"/g, '"'); //.replace(searchvalue, newvalue)
    html_string = html_string.replace(/\\'/g, '');

    html_string = html_string.match(/<table\b[^>]*>([\s\S]*?)<\/table>/g);
    html_string = html_string[3];
    if (html_string === ""){ //if the string is empty, return nothing
        return false;
    }

    var tbody = $.parseHTML(html_string);
    $("#processor").html(tbody);
    $("tr:eq(0)").remove();
    $("tr:eq(0)").remove();
    $("tr:eq(0)").remove();
    $("tr:eq(-1)").remove();
    $("tr:eq(-1)").remove();

    $("tr").each(function(){
        var row = [name];

        var other_horse = 		$("td:nth-child(1)", $(this)).children("a").text();
        row.push(other_horse);
        var inbreeding_stats = 	$("td:nth-child(2)", $(this)).text();
        row.push(inbreeding_stats);
        var crosses = 			$("td:nth-child(3)", $(this)).text();
        row.push(crosses);
        var lines = 			$("td:nth-child(4)", $(this)).text();
        row.push(lines);
        var blood = 			$("td:nth-child(5)", $(this)).text();
        row.push(blood);
        var influence = 		$("td:nth-child(6)", $(this)).text();
        row.push(influence);
        var agr = 				$("td:nth-child(7)", $(this)).text();
        row.push(agr);

        horse_rows.push(row);
    });
    $("#processor").html("");
    return horse_rows;
}; //end processHTML function

function arrayToCSV(multi_array){
    var csv = "data:text/csv;charset=utf-8,";
    $.each(multi_array, function(index, value){
        var row = value.join(",");
        row += "\n";
        csv += row;
    });

    return csv;
}

function downloadCSV(csv){
    var encoded_uri = encodeURI(csv);
    var anchor = document.createElement("a");
    anchor.setAttribute("download", "horse_data.csv");
    anchor.setAttribute("href", encoded_uri);
    anchor.setAttribute("target", "_blank");
    anchor.click();
}

function htmlEncode(value){
    return $('<div/>').text(value).html();
}