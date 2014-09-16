<?php
namespace WebTester;

class FaviconTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Shared response from the /favicon.ico request
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
		
		if (!empty($config['skip']) && in_array(__CLASS__, $config['skip'])) {
			$this->markTestSkipped("Skipping " . __CLASS__ . " due to configuration");
		}
	
		if (empty($config['site'])) {
			$this->markTestSkipped("No site given in bootstrap.php file");
		}
		
		$this->client = new \GuzzleHttp\Client();
		
		$components = parse_url($config['site']);
		$components['path'] = '/favicon.ico';
		unset($components['query']);
		unset($components['fragment']);

		// Shared response
		$url = http_build_url($components);
		try {
			$this->response = $this->client->get($url, ['timeout' => $config['timeout']]);
		} catch (\Exception $e) {
			$this->fail("Failed fetching URL [$url] : " . $e->getMessage());
		}

	}

	/**
	 * Check that we have favicon
	 * 
	 * This is not only important aethetically, but for performance as well,
	 * since a missing favicon.ico request will usually fall into mod_rewrite
	 * madness and a gadzillion SQL requests (in case of WordPress, for example)
	 */
	public function test_Favicon_ICO() {

		// Status code of favicon.ico
		$statusCode = $this->response->getStatusCode();
		$this->assertEquals(200, $statusCode, "Favicon request did not return 200 status code");
		
		$allowedTypes = array('image/x-icon', 'image/vnd.microsoft.icon');
		$contentType = $this->response->getHeader('content-type');
		$this->assertContains($contentType, $allowedTypes, 'favicon.ico must be an icon image');
	}
	
}
?>
