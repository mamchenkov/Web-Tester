<?php
namespace WebTester;

class RobotsTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Shared response from the /robots.txt request
	 */
	protected $response;

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
		global $config;
		
		if (empty($config[__CLASS__]) || !$config[__CLASS__]) {
			$this->markTestSkipped("Skipping " . __CLASS__ . " due to configuration");
		}
	
		if (empty($config['site'])) {
			$this->markTestSkipped("No site given in bootstrap.php file");
		}
		
		$this->client = new \GuzzleHttp\Client();
		
		$components = parse_url($config['site']);
		$components['path'] = '/robots.txt';
		unset($components['query']);
		unset($components['fragment']);

		// Shared response
		$this->response = $this->client->get(http_build_url($components));
	
	}

	/**
	 * Make sure we have robots.txt
	 * 
	 * Not having robots.txt file might have performance consequences,
	 * as the request might end up in mod_rewrite and SQL matching madness.
	 */
	public function test_Robots_TXT() {
		
		// Status code of robots.txt
		$statusCode = $this->response->getStatusCode();
		$this->assertEquals(200, $statusCode, "robots.txt request did not return 200 status code");

		// Content type of robots.txt
		$contentType = $this->response->getHeader('content-type');
		$this->assertRegExp('#^text/plain#', $contentType, "Content type of robots.txt is not text/plain");
	}

	/**
	 * Make sure we have XML sitemap
	 */
	public function test_XML_Sitemaps() {

		// Parse content of robots.txt for XML sitemap URLs
		$body = (string) $this->response->getBody();
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
