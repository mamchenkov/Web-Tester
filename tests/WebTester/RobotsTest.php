<?php
class RobotsTest extends PHPUnit_Framework_TestCase {

	/**
	 * URL for tests to use
	 */
	protected $url;

	/**
	 * Components of the URL
	 */
	protected $components;

	/**
	 * Instance of HTTP client
	 */
	protected $client;

	/**
	 * Setup the configuration
	 *
	 * @return void
	 */
	protected function setUp() {
		global $site;
		
		if (empty($site)) {
			$this->markTestSkipped("No site given in bootstrap.php file");
		}
		$this->url = $site;
		$this->components = parse_url($this->url);
		$this->client = new GuzzleHttp\Client();
	}

	/**
	 * Make sure we have robots.txt
	 * 
	 * Not having robots.txt file might have performance consequences,
	 * as the request might end up in mod_rewrite and SQL matching madness.
	 */
	public function test_robotstxt() {
		
		$components = $this->components;
		$components['path'] = '/robots.txt';
		unset($components['query']);
		unset($components['fragment']);

		// Status code of robots.txt
		$res = $this->client->get(http_build_url($components));
		$statusCode = $res->getStatusCode();
		$this->assertEquals(200, $statusCode, "robots.txt request did not return 200 status code");

		// Content type of robots.txt
		$contentType = $res->getHeader('content-type');
		$this->assertRegExp('#^text/plain#', $contentType, "Content type of robots.txt is not text/plain");

		// Parse content of robots.txt for XML sitemap URLs
		$body = (string) $res->getBody();
		$body = preg_split('/$\R?^/m', $body); // Thanks to: http://stackoverflow.com/a/7498886
		$sitemaps = array();
		foreach ($body as $line) {
			if (preg_match("#^\s*Sitemap:\s*(.*)$#", $line, $matches)) {
				$sitemaps[] = $matches[1];
			}
		}

		$this->assertFalse(empty($sitemaps), "robots.txt contains no links to XML sitemaps");

		// Validate all found sitemap URLs
		foreach ($sitemaps as $sitemap) {
			$res = $this->client->get($sitemap);
			$statusCode = $res->getStatusCode();
			$this->assertEquals(200, $statusCode, "Bad status code [$statusCode] returned for sitemap URL [$sitemap]");
		}

	}
}
?>
