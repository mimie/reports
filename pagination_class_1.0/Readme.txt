---------------------------
  PHP Pagination Class 1.0
---------------------------

Author:	Dane Gardow <dane.g87@gmail.com>
Date:	01 January 2013
Version:	1.0
License:  Free


This class displays a pagination navigation bar complete with links to first, last, previous, next, and all pages. This class handles cookie setting, page bounds checking/redirection, appropriate error reporting, CSS styling, and POST/GET retrieval all internally. This class works with MySQL and flat-file databases and can support multiple pagination objects concurrently.

PHP version 5



Files included:

- Readme.txt		This file
- Pagination.php		The pagination class
- styles.css		CSS style examples
- example_mysql.php	Example using MySQL
- example_flatfile.php	Example using a flat-file
- flatfile-data.txt		Data for flat-file example


----------------------------
  Installation & Instructions
----------------------------

1. Place Pagination.php somewhere where it can be accessed by other scripts. Include the above file file in any page that needs pagination. The include statement should be somewhere at the top of the page, or before any output.

Example:
<?php include "some_location/Pagination.php"; ?>


2. Instantiate a new pagination object with appropriate arguments. The arguments are in this order: total entries, pages per section, display options, page marker id, CSS style 1 for links, style 2 for links, style for <select>, and style for error reporting. The last 5 arguments are optional.

Example:
<?php $Pagination = new Pagination(100, 10, array(5, 10, 25, "All"), "comments", "pageOff", "pageOn", "pageSelect", "pageErrors"); ?>


3. Call the display functions to output pagination links and display settings. Don’t forget to use echo.

Example:
<?php
echo $Pagination->display();
echo $Pagination->displaySelectInterface()
?>


4. Supply your data retrieval method (e.g., MySQL query) with the appropriate limits derived from the class.

Example:
<?php
$start = $Pagination->getEntryStart();
$end = $Pagination->getEntryEnd();
$query = mysql_query("SELECT * FROM table LIMIT $start,$end");
?>


5. That’s it! All the pagination is taken care of; the only thing left is implementing how the data is displayed.


All in one:

<?php
include "some_location/Pagination.php";

$Pagination = new Pagination(100, 10, array(5, 10, 25, "All"), "comments", "pageOff", "pageOn", "pageSelect", "pageErrors"); ?>

echo $Pagination->display();
echo $Pagination->displaySelectInterface()

$start = $Pagination->getEntryStart();
$end = $Pagination->getEntryEnd();

$query = mysql_query("SELECT * FROM table LIMIT $start,$end");
?>

-----------------------------
  Class Properties & Methods
-----------------------------

Class Properties

1. DEFAULT_ENTRIES_DISPLAY
Public
Constant integer
Default number of entries to display per page.

2. PAGE_GETVAR_NAME
Public
Constant string
Name of the GET variable name used in the URL for retrieving page numbers (i.e., example.php?page=1).

3. $_currentPage
Private
Integer
Integer value of the current page that is being accessed by the viewer.

4. $_displayOptions
Private
Array
An array of integer values for the selection of display options. The array can contain the string element "All" for displaying all entries.

5. $_entriesPerPage
Private
Integer or string (e.g., "All")
Number of entries to display per page.

6. $_pagesPerSection
Private
Integer
Number of pages to display per section.

7. $_paginationID
Private
String
Pagination object ID name, used in GET/POST, cookie, and form name entities.

8. $_styles[pageOff, pageOn, select, errors]
Array
An associative array that contains CSS style class names for pagination styling. pageOff and pageOn refer to navigation links that are currently being viewed (on) or not (off). Select refers to the styling of the <select> element, and errors to the class’s error reporting display style.

9. $_totalEntries
Private
Integer
Total number of entries.



--------------------------------
  Class Methods: Set Functions
--------------------------------

1. setCurrentPage()
Public
No arguments
*Important: This function sends headers (error-handling redirection)! This function must be called after setEntriesPerPage(). This function sets the current page and retrieves any GET information, if available (otherwise sets the current page to the first page).

2. setDisplayOptions(array)
Public
1 array argument that contains integers and possibly one string value ("All"). This argument is supplied by the webmaster.
The array contains an option of integer values of how many entries are to be displayed.

3. setEntriesPerPage()
Public
No arguments
*Important: This function sends headers (cookies). This function contains the logic which processes viewer-specified display settings using POST method form submission. Cookies are also set, which remember the viewer’s choice.

4. setPagesPerSection(int)
Public
1 integer argument, number of pages to be displayed per section. This argument is supplied by the webmaster.
This function determines how many pages are to be displayed at a time.

5. setPaginationID(string)
Public
1 string argument, id name for pagination object. This argument is supplied by the webmaster.
This function sets the ID for the pagination object, which is used for the form name, POST/GET variable names, and cookie name, and also for internal page link auto-scrolling (i.e., example.php?page=1#commentsID). The class only sets the ID name, it does not output the element with that ID (this was left to the webmaster for flexibility).

6. setStyles(string, string, string, string)
Public
4 string arguments, each being a CSS class name for styling purposes. These arguments are supplied by the webmaster and enable CSS styling for pagination nagivation bar. See styles[] for more details.

7. setTotalEntries(int)
Public
1 integer, total number of entries.
The argument would typically be derived from a database and sets the total amount of entries that will be paginated.



--------------------------------
  Class Methods: Get Functions
--------------------------------

1. _getIDGETVarName()
Private
No arguments
Returns the GET variable name for pagination navigation bar page links.

2. _getPOSTVarName()
Private
No arguments
Returns the POST variable name for the <select> and cookie entities.

3. getCurrentPage()
Public
No arguments
Returns the current page being accessed.

4. getEntryEnd()
Public
No arguments
Returns the particular ending entry number (integer) for the page. This is required for MySQL database LIMIT end value.

5. getEntryEndFF()	
Public
No arguments
Returns the particular ending entry number (integer) for the page. This is required for the flat-file database end value.

6. getEntryStart()
Public
No arguments
Returns the particular starting entry number (integer) for the page. This is required for the database LIMIT start value.

7. getPagesPerSection()
Public
No arguments
Returns an integer of how many pages are displayed per section. If the remainder of pages is less than the predefined amount, the remainder will be returned, instead.

8. getPaginationID()
Public
No arguments
Returns the ID name for the pagination object.

9. getTotalPages()
Public
No arguments
Returns an integer of the total number of pages.



-----------------------------------
  Class Methods: Display Functions
-----------------------------------

1. display()
Public
No arguments
This function returns the HTML output of the pagination bar, including the pages and navigation links (first, previous, next, last).

2. displayErrors()
Public
No arguments
This function returns the HTML output of any encountered errors.

3. displaySelectInterface()
Public
No arguments
This function returns the HTML output of the selection interface which allows the viewer to specify display settings.



----------------------------------
  Class Methods: Utility Functions
----------------------------------

1. _getURL(int)
Private
1 integer argument, the destination page.
This function returns the appropriate URL with all its necessary GET variables, needed for various other functions throughout the class.

2. _isError()
Private
No arguments
This function checks if an error has been encountered and, if so, registers the error, and then returns true or false.

3. _navBox(string, int, int)
Private
3 arguments, a string for the link text, an integer for the page destination, and an integer that determines if the link is an end value (i.e., first or last page).
This function is used within the display() function to display links in the pagination bar, and returns HTML output.

4. _validEntry(int)
Private
1 argument, an integer input value.
This function determines if the specified input value is valid for processing within the class, and returns true or false.

5. deleteCookie()
Public
No arguments
This function is provided for the deletion of the cookies that the class sets, and simply deletes the cookie.
