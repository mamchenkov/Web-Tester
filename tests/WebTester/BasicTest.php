<?php
class BasicTest extends PHPUnit_Framework_TestCase {

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
	 * Site should return 200 status code
	 * 
	 * This actually tests several things:
	 * - Site is alive
	 * - Site is using redirect for the front page
	 */
	public function test_urlStatusCode() {
		$res = $this->client->get($this->url);
		$statusCode = $res->getStatusCode();
		$this->assertEquals(200, $statusCode);
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
		$pos = strpos('www.', $components['host']);
		if (($pos === false) || ($pos > 0)) {
			$components['host'] = 'www.' . $components['host'];
		}
		else {
			$components['host'] = substr($components['host'], 4);
		}

		$res = $this->client->get(http_build_url($components), ['allow_redirects' => false]);
		$statusCode = $res->getStatusCode();
		$this->assertEquals(301, $statusCode);
		
		$res = $this->client->get(http_build_url($components));
		$statusCode = $res->getStatusCode();
		$this->assertEquals(200, $statusCode);

	}

}
?>
