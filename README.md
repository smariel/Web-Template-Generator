#Web-Template-Generator

Creating a new web project by donwloading a ZIP
[See demo here](http://dev.otmax.fr/WebTemplateGenerator/)

You can add and configure :
*A default index page
*All folders you need
*Frameworks or files


## configuration

Use config.json to add new checkboxes on the UI : folders and frameworks/files.

First, you can add defaults folders. For exemple, if you want a "Video" checkbox to add a "video" folder, just write :
`{
	"name":"video",
	"disp":"Video",
	"checked":0
}`

Then, you can add new frameworks or file in the second part of config.json.
The script only understand JS and CSS. But there is 3 ways to add them :

*Using a URL to a .js or .css file
*Using a URL to a .zip file. The script will search for all js and css
*Using a URL to a .js.php or .css.php file that will be executed to generate a css or a js

Note for ZIP files : if there is a XXX.js and a XXX.min.js, the script will only include the XXX.min.js
Note for PHP files : it is sometime usefull to create a script that will analyse a web page to download the latest version of the framework. You can look at the less.min.js.php file.
