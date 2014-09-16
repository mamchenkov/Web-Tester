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

1. Edit the ```web_tester.json``` file.  At least change the site URL.
2. Execute: vendor/bin/phpunit

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

