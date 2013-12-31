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

## SSL for the User Control Panel (ucp.php) using Apache's .htaccess
First you need to make a new vhost with ssl. Then do symlinks in to your webserver's ssl directory.

Example:
```bash
	# Directory to https
	cd /var/ssl/ask.meikodis.org
	# Symlinks from http to https
	ln -s /var/www/ask.meikodis.org/* .
```

Then do two .htaccess files. One for http to https.

```bash
cat /var/www/ask.meikodis.org/.htaccess
```
```apache
	RewriteEngine On

	RewriteCond %{REQUEST_URI} ucp.php [NC]
	RewriteRule .* https://%{SERVER_NAME}%{REQUEST_URI} [L]
```


One for https to http.

```bash
cat /var/ssl/ask.meikodis.org/.htaccess
```
```apache
	RewriteEngine On

	RewriteCond %{REQUEST_URI} index.php [NC]
	RewriteRule .* http://%{SERVER_NAME}%{REQUEST_URI} [L]
```
