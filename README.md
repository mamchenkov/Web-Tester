Web-Tester
==========

Web-Tester is a collection of the PHPUnit tests which can be executed against
any publicly available website.  These are thrown together to make sure the 
most common things aren't missed out when deploying web projects:

* 200 status code for the website front page
* 301 redirect for www/no-www
* 200 status code and icon content type for the favicon.ico
* 200 status code and text/plain for the robots.txt
* At least one Sitemap URL in robots.txt
* 200 status code for any Sitemap URL in robots.txt

Install
-------

Install with Composer:

```
{
	require: {
		"mamchenkov/web-tester": "dev-master"
	}
}
```

Usage
-----

Run all tests with default options for a given domain:

```
$ vendor/bin/web_tester.sh http://www.domain.com
```

More control is available via ```web_tester.json``` file.  Here is an example:

```
{
    "site": "http://www.google.com",
    "timeout": "2",
    "skip": [
		"WebTester\\BasicTest",
		"WebTester\\FaviconTest",
		"WebTester\\RobotsTest"
	]
}
```

The URL of the site can be ommitted when it is present in the configuration file:

```
$ vendor/bin/web_tester.sh
```

TODO
----
* [ ] HTTPS check
* [ ] Custom 404 page check
* [ ] Custom 500 page check
* [ ] RSS feed autodiscovery check
* [ ] Absense of 404 links (recursive? long?)
* [ ] HTML/CSS/JS being minimized (recursive? long?)
* [ ] Images are optimized (recursive? long?)
* [ ] Response times check (recursive? long?)
* [ ] SEO checks (page title, description, h1 tags, ALT tags)

