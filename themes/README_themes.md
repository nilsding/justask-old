# How To Create A Theme

justask uses [RainTPL](http://www.raintpl.com) for templates/themes. All themes are located in /themes in a subfolder with the theme name.

A theme folder with a few themes in it can look like this:

     + themes
     |
     +--+ classic
     |  |
     |  +-- answers.html
     |  |
     |  +-- footer.html
     |  |
     |  +-- header.html
     |  |
     |  +-- style.css
     |  |
     |  +-- screenshot.png
     |  |
     |  +-- theme.md
     |
     +--+ another_theme
     |  |
     |  +-- answers.html
     |  |
     |  +-- footer.html
     |  |
     |  +-- header.html
     |  |
     |  +-- style.css
     |  |
     |  +-- theme.md
     |
    ...

For each theme you may provide a `theme.md` file which contains a small description and the license of your theme.  
You may use the `classic` theme as a reference for your own theme. 

Every .php file which makes use of a theme will require its own file. These are:

    index.php       -> answers.html
    view_answer.php -> single_answer.html
    update_jak.php  -> generic.html
    ucp.php         -> ucp.html | ucp-front.html | ucp-inbox.html | ucp-answers.html | ucp-settings.html | ucp-login.html
    
Depending on the files, you can use the following variables in your theme. 

<table>
<tr><th>Variable</th><th>Description</th><th>Can be used on these pages</th></tr>
<tr><td>$pages</td><td> **Array** - The number of items in this array is equal to the number of pages.</td><td>Everywhere where a pagination is needed</td></tr>
<tr><td>$pagenum</td><td>The current page number.</td><td>Everywhere where a pagination is needed</td></tr>
<tr><td>$answers</td><td>Contains an **array** with the answers of the current page.</td><td>`index.php` and `ucp.php?p=answers`</td></tr>
<tr><td>$message</td><td>Contains the message that will be displayed.</td><td>Everywhere!</td></tr>
<tr><td>$gravatar</td><td>Is Gravatar enabled?</td><td>Everywhere!</td></tr>
<tr><td>$user_name</td><td>The user name of the user who owns this installation of justask</td><td>Everywhere!</td></tr>
<tr><td>$last_page</td><td>The number of the last page.</td><td>Everywhere where a pagination is needed</td></tr>
<tr><td>$file_name</td><td>Current file name.</td><td>Everywhere</td></tr>
<tr><td>$is_message</td><td> **bool** - Will we display a message to the user?</td><td>Everywhere!</td></tr>
<tr><td>$page_self</td><td>Equivalent to `$_SERVER['PHP_SELF']`.</td><td>Everywhere!</td></tr>
<tr><td>$site_name</td><td>The current site name.</td><td>Everywhere!</td></tr>
<tr><td>$anon_questions</td><td>Are anonymous questions enabled?</td><td>Everywhere!</td></tr>
<tr><td>$user_gravatar_email</td><td>Gravatar URL of the user who owns this installation of justask</td><td>Everywhere!</td></tr>
<tr><td>$current_page</td><td>Current page.</td><td>`ucp.php` and `generic.html` template</td></tr>
<tr><td>$logged_in</td><td> **bool** - Is the user even logged in?</td><td>Everywhere!</td></tr>
<tr><td>$content</td><td>Content of the page.</td><td>`generic.html` template</td></tr>
<tr><td>$ucp_menu</td><td> **Array** - The menu bar entries of the user control panel.</td><td>`ucp.php`</td></tr>
</table>

...and many more. Remember to take a look at the `classic` theme.