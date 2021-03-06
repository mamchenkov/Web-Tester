<?php
namespace WebTester;

define('WEB_TESTER_NAME', 'Web Tester');
define('WEB_TESTER_VERSION', '1.0.0');
define('WEB_TESTER_AUTHOR', 'Leonid Mamchenkov');
define('WEB_TESTER_CONFIG', 'web_tester.json');
define('WEB_TESTER_TIMEOUT', 5);

// Figure out if we are installed with composer
$composerInstall = true;
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor')) {
	$composerInstall = false;
}

// Default config file
$configFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . WEB_TESTER_CONFIG;
$localConfigFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . WEB_TESTER_CONFIG;
if ($composerInstall && file_exists($localConfigFile)) {
	$configFile = $localConfigFile;
}
$config = loadConfigFromJson($configFile);

$envSite = getenv('WEB_TESTER_SITE');
if (!empty($envSite)) {
	$config['site'] = $envSite;
}

// We do a lot of HTTP requests. Having a sane timeout default is useful.
if (empty($config['timeout'])) {
	$config['timeout'] = WEB_TESTER_TIMEOUT;
}

print WEB_TESTER_NAME . " " . WEB_TESTER_VERSION . " by " . WEB_TESTER_AUTHOR . "\n\n";
if (empty($config['site'])) {
	die("No site given in configuration or command line ... existing.\n");
}
print "Site: " . $config['site'] . "\n\n";

////////////////////////////////////////////
// Do not change anything below this line //
////////////////////////////////////////////

// Autoload composer libraries
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor')) {
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
}
else {
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';
}

/**
 * Load configuration array from JSON file
 * 
 * @param string $file Path to JSON file
 * @return array Associative array of settings
 */
function loadConfigFromJson($file) {
	$result = array();
	
	if (file_exists($file) && is_readable($file)) {
		$result = json_decode(file_get_contents($file), true);
	}

	// Make sure that we still return an empty array, if something broke
	if (empty($result)) {
		$result = array();
	}

	return $result;
}

// For those situations when PECL http extensions is not installed
// Verbatim copy from: https://github.com/jakeasmith/http_build_url/blob/master/src/http_build_url.php

/**
 * URL constants as defined in the PHP Manual under "Constants usable with
 * http_build_url()".
 *
 * @see http://us2.php.net/manual/en/http.constants.php#http.constants.url
 */
if (!defined('HTTP_URL_REPLACE')) {
	define('HTTP_URL_REPLACE', 1);
}
if (!defined('HTTP_URL_JOIN_PATH')) {
	define('HTTP_URL_JOIN_PATH', 2);
}
if (!defined('HTTP_URL_JOIN_QUERY')) {
	define('HTTP_URL_JOIN_QUERY', 4);
}
if (!defined('HTTP_URL_STRIP_USER')) {
	define('HTTP_URL_STRIP_USER', 8);
}
if (!defined('HTTP_URL_STRIP_PASS')) {
	define('HTTP_URL_STRIP_PASS', 16);
}
if (!defined('HTTP_URL_STRIP_AUTH')) {
	define('HTTP_URL_STRIP_AUTH', 32);
}
if (!defined('HTTP_URL_STRIP_PORT')) {
	define('HTTP_URL_STRIP_PORT', 64);
}
if (!defined('HTTP_URL_STRIP_PATH')) {
	define('HTTP_URL_STRIP_PATH', 128);
}
if (!defined('HTTP_URL_STRIP_QUERY')) {
	define('HTTP_URL_STRIP_QUERY', 256);
}
if (!defined('HTTP_URL_STRIP_FRAGMENT')) {
	define('HTTP_URL_STRIP_FRAGMENT', 512);
}
if (!defined('HTTP_URL_STRIP_ALL')) {
	define('HTTP_URL_STRIP_ALL', 1024);
}

if (!function_exists('http_build_url')) {

	/**
	 * Build a URL.
	 *
	 * The parts of the second URL will be merged into the first according to
	 * the flags argument.
	 *
	 * @param mixed $url     (part(s) of) an URL in form of a string or
	 *                       associative array like parse_url() returns
	 * @param mixed $parts   same as the first argument
	 * @param int   $flags   a bitmask of binary or'ed HTTP_URL constants;
	 *                       HTTP_URL_REPLACE is the default
	 * @param array $new_url if set, it will be filled with the parts of the
	 *                       composed url like parse_url() would return
	 * @return string
	 */
	function http_build_url($url, $parts = array(), $flags = HTTP_URL_REPLACE, &$new_url = array())
	{
		is_array($url) || $url = parse_url($url);
		is_array($parts) || $parts = parse_url($parts);

		isset($url['query']) && is_string($url['query']) || $url['query'] = null;
		isset($parts['query']) && is_string($parts['query']) || $parts['query'] = null;

		$keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

		// HTTP_URL_STRIP_ALL and HTTP_URL_STRIP_AUTH cover several other flags.
		if ($flags & HTTP_URL_STRIP_ALL) {
			$flags |= HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS
				| HTTP_URL_STRIP_PORT | HTTP_URL_STRIP_PATH
				| HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT;
		} elseif ($flags & HTTP_URL_STRIP_AUTH) {
			$flags |= HTTP_URL_STRIP_USER | HTTP_URL_STRIP_PASS;
		}

		// Schema and host are alwasy replaced
		foreach (array('scheme', 'host') as $part) {
			if (isset($parts[$part])) {
				$url[$part] = $parts[$part];
			}
		}

		if ($flags & HTTP_URL_REPLACE) {
			foreach ($keys as $key) {
				if (isset($parts[$key])) {
					$url[$key] = $parts[$key];
				}
			}
		} else {
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH)) {
				if (isset($url['path']) && substr($parts['path'], 0, 1) !== '/') {
					$url['path'] = rtrim(
							str_replace(basename($url['path']), '', $url['path']),
							'/'
						) . '/' . ltrim($parts['path'], '/');
				} else {
					$url['path'] = $parts['path'];
				}
			}

			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY)) {
				if (isset($url['query'])) {
					parse_str($url['query'], $url_query);
					parse_str($parts['query'], $parts_query);

					$url['query'] = http_build_query(
						array_replace_recursive(
							$url_query,
							$parts_query
						)
					);
				} else {
					$url['query'] = $parts['query'];
				}
			}
		}

		foreach ($keys as $key) {
			$strip = 'HTTP_URL_STRIP_' . strtoupper($key);
			if ($flags & constant($strip)) {
				unset($url[$key]);
			}
		}

		$parsed_string = '';

		if (isset($url['scheme'])) {
			$parsed_string .= $url['scheme'] . '://';
		}

		if (isset($url['user'])) {
			$parsed_string .= $url['user'];

			if (isset($url['pass'])) {
				$parsed_string .= ':' . $url['pass'];
			}

			$parsed_string .= '@';
		}

		if (isset($url['host'])) {
			$parsed_string .= $url['host'];
		}

		if (isset($url['port'])) {
			$parsed_string .= ':' . $url['port'];
		}

		if (!empty($url['path'])) {
			$parsed_string .= $url['path'];
		} else {
			$parsed_string .= '/';
		}

		if (isset($url['query'])) {
			$parsed_string .= '?' . $url['query'];
		}

		if (isset($url['fragment'])) {
			$parsed_string .= '#' . $url['fragment'];
		}

		$new_url = $url;

		return $parsed_string;
	}
}
?>
