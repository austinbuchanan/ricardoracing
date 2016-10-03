# ricardoracing
	
	Repository for CSCI 4940 
	Austin Peay State University, Clarksville, TN 
	Fall 2016

	David Pridemore (me@davidpridemore.com)
	Austin Buchanan (abuchanan5@my.apsu.edu)

	We completed this project for a local company and for a course credit internship. 
	This is a web scraper project that pulls data from [allbreedpedigree.com]: http://www.allbreedpedigree.com. You 
	must have a working account with allbreedpedigree.com for the application to 
	return data.

	See license.md for MIT licensing information.


## about the app	
	This app will generate horse inbreeding statistics for quarter horses based on user input. 
	This app uses PHP to scrape [allbreedpedigree.com](http://www.allbreedpedigree.com) 
	for information on each horse the user enters, then outputs that information into a .csv file.

## getting started
	Upon first accessing the app, you will be greeted with a login screen. 
	Enter your allbreedpedigree.com username and password to continue to the horse entry form. 
	If you enter the wrong password or username, you will still be able to access the app, 
	but you will not be able to generate a report upon querying the website.

## main page
	The main page for the app contains a text area field where horse names can be entered, 
	and 4 drop down menus for additional constraints. These constraints include:
	..*the kind of report you want to generate (Cross Dups, Dups Only, All Horses, Males Only, Females Only)
	..*the number of generations you want to include (4-20)
	..*the number of crosses you want to do (2-10)
	..*the influence (All, >2x3, >3x3, >3x4, >4x4, >4x5, >5x5, >5x6)
	When you are finished adding horse names, and you have selected your constraint values, click "Submit Query."
	The app will then scrape allbreedpedigree.com to look for the information that you entered, and output that
	information into a .csv file.
## the .csv file
	The .csv file organizes information by horse in the order that you entered it in a table-style fashion.
	These tables include the horse's name and an inbreeding coefficient at the top of each table, and varying rows
	of data consisting of:
	..*the horse's relative's name 
	..*the inbreeding stats
	..*the number of crosses
	..*the lines
	..*the blood %
	..*the influence
	..*the AGR
	If there is no data in the table, it means that you have either entered the wrong username or password.
	If there is no data in 1 or more tables, but there are inbreeding coeffecients, it means that allbreedpedigree.com
	does not have a record of that horse's relatives for the specified constraints.
