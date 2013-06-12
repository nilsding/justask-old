# justask
justask is more or less something like [Formspring](http://formspring.me) or [Ask.fm](http://ask.fm), except it's single-user only.
Think of it as viewing an user on one of these platforms

## Features
* Answer questions
* Ask questions
* Ask questions anonymously
* Use Gravatar for profile icons
* Post new answers to Twitter
* Themes!

## Requirements
As always, a web server (any should work) with PHP5 and MySQL installed. I recommend using the latest version of everything.

Oh, and IIS is horrible. Don't try this at home.

## Installation
Before you ask "hurrrr wher is z config.php", I would recommend you to take a look at the install.php. It will generate a
`config.php` for you and even does all the SQL stuff (creating tables and so on…). After finished installing, be sure to
delete the `install.php` and edit your login data using the `ucp.php`. The default user name is "user" and the default
password is "password".

## Upgrading
Upgrading is easy! In most cases, just running the curent `update_jak.php` will work.