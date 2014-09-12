<?php
class FaviconTest extends PHPUnit_Framework_TestCase {

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
	 * Check that we have favicon
	 * 
	 * This is not only important aethetically, but for performance as well,
	 * since a missing favicon.ico request will usually fall into mod_rewrite
	 * madness and a gadzillion SQL requests (in case of WordPress, for example)
	 */
	public function test_favicon() {

		$components = $this->components;
		$components['path'] = '/favicon.ico';
		unset($components['query']);
		unset($components['fragment']);

		$res = $this->client->get(http_build_url($components));
		$statusCode = $res->getStatusCode();
		$this->assertEquals(200, $statusCode);
		
		$allowedTypes = array('image/x-icon', 'image/vnd.microsoft.icon');
		$contentType = $res->getHeader('content-type');
		$this->assertContains($contentType, $allowedTypes, 'favicon.ico must be an icon image');
	}
	
}
?>
