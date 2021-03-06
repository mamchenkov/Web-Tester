<?php
namespace WebTester;

class BasicTest extends \PHPUnit_Framework_TestCase {

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
		$this->url = $config['site'];
		$this->components = parse_url($this->url);
		$this->client = new \GuzzleHttp\Client();
		$this->timeout = $config['timeout'];
	}

	/**
	 * Site should return 200 status code
	 * 
	 * This actually tests several things:
	 * - Site is alive
	 * - Site is using redirect for the front page
	 */
	public function test_urlStatusCode() {
		try {
			$res = $this->client->get($this->url, ['timeout' => $this->timeout]);
			$statusCode = $res->getStatusCode();
		} catch (\Exception $e) {
			$this->fail("Failed fetching URL [" . $this->url . "] : " . $e->getMessage());
		}
		$this->assertEquals(200, $statusCode, "URL [" . $this->url . "] didn't return 200 status code");
	}

	/**
	 * Test that www/no-www is handled correctly
	 * 
	 * If the site is using a www URL, then no-www should redirect to www.
	 * If the site is using a non-www URL, then www should redirect to no-www.
	 * 
	 * The redirect should be done with 301 permanent redirect status
	 * code.  And once the user follows the redirect, he should end up on
	 * the correct page.
	 */
	public function test_wwwRedirect() {

		$components = $this->components;
		$pos = strpos($components['host'], 'www.');
		if (($pos === false) || ($pos > 0)) {
			$components['host'] = 'www.' . $components['host'];
		}
		else {
			$components['host'] = substr($components['host'], 4);
		}

		$wwwUrl = http_build_url($components);

		try {
			$res = $this->client->get($wwwUrl, ['allow_redirects' => false, 'timeout' => $this->timeout]);
			$statusCode = $res->getStatusCode();
		} catch (\Exception $e) {
			$this->fail("Failed fetching URL [$wwwUrl] : " . $e->getMessage());
		}

		$this->assertEquals(301, $statusCode, "The www/no-www redirect did not return 301 status code");
		
		try {
			$res = $this->client->get($wwwUrl, ['timeout' => $this->timeout]);
			$statusCode = $res->getStatusCode();
		} catch (\Exception $e) {
			$this->fail("Failed fetching URL [$wwwUrl] : " . $e->getMessage());
		}
		
		$this->assertEquals(200, $statusCode, "The www/no-www redirect did not end up in 200 status code");

	}

}
?>
