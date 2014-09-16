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
	 * Timeout
	 */
	protected $timeout;

	/**
	 * Setup the configuration
	 *
	 * @return void
	 */
	protected function setUp() {
		global $config;
		
		if (!empty($config['skip']) && in_array(__CLASS__, $config['skip'])) {
			$this->markTestSkipped("Skipping " . __CLASS__ . " due to configuration");
		}
	
		if (empty($config['site'])) {
			$this->markTestSkipped("No site given in bootstrap.php file");
		}
		
		$this->client = new \GuzzleHttp\Client();
		$this->timeout = $config['timeout'];
		
		$components = parse_url($config['site']);
		$components['path'] = '/robots.txt';
		unset($components['query']);
		unset($components['fragment']);

		// Shared response
		$url = http_build_url($components);
		try {
			$this->response = $this->client->get($url, ['timeout' => $this->timeout]);
		} catch (\Exception $e) {
			$this->fail("Failed fetching URL [$url] : " . $e->getMessage());
		}

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
			// Must be a full URL http://www.sitemaps.org/protocol.html#submit_robots
			$this->assertRegExp('#^http:#', $sitemap, "Sitemap URL [$sitemap] is not a full URL");
			try {
				$res = $this->client->get($sitemap, ['timeout' => $this->timeout]);
				$statusCode = $res->getStatusCode();
			} catch (\Exception $e) {
				$this->fail("Failed fetching URL [$sitemap] : " . $e->getMessage());
			}
			$this->assertEquals(200, $statusCode, "Bad status code [$statusCode] returned for sitemap URL [$sitemap]");
		}

	}
}
?>
