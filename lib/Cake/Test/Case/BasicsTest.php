<?php
/**
 * BasicsTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @package       Cake.Test.Case
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

require_once CAKE . 'basics.php';
App::uses('Folder', 'Utility');
App::uses('CakeResponse', 'Network');

/**
 * BasicsTest class
 *
 * @package       Cake.Test.Case
 */
class BasicsTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		App::build(array(
			'Locale' => array(CAKE . 'Test' . DS . 'test_app' . DS . 'Locale' . DS)
		));
	}

/**
 * test the array_diff_key compatibility function.
 *
 * @return void
 */
	public function testArrayDiffKey() {
		$one = array('one' => 1, 'two' => 2, 'three' => 3);
		$two = array('one' => 'one', 'two' => 'two');
		$result = array_diff_key($one, $two);
		$expected = array('three' => 3);
		$this->assertEquals($expected, $result);

		$one = array('one' => array('value', 'value-two'), 'two' => 2, 'three' => 3);
		$two = array('two' => 'two');
		$result = array_diff_key($one, $two);
		$expected = array('one' => array('value', 'value-two'), 'three' => 3);
		$this->assertEquals($expected, $result);

		$one = array('one' => null, 'two' => 2, 'three' => '', 'four' => 0);
		$two = array('two' => 'two');
		$result = array_diff_key($one, $two);
		$expected = array('one' => null, 'three' => '', 'four' => 0);
		$this->assertEquals($expected, $result);

		$one = array('minYear' => null, 'maxYear' => null, 'separator' => '-', 'interval' => 1, 'monthNames' => true);
		$two = array('minYear' => null, 'maxYear' => null, 'separator' => '-', 'interval' => 1, 'monthNames' => true);
		$result = array_diff_key($one, $two);
		$this->assertSame(array(), $result);
	}

/**
 * testHttpBase method
 *
 * @return void
 */
	public function testEnv() {
		$this->skipIf(!function_exists('ini_get') || ini_get('safe_mode') === '1', 'Safe mode is on.');

		$server = $_SERVER;
		$env = $_ENV;

		$_SERVER['HTTP_HOST'] = 'localhost';
		$this->assertEquals(env('HTTP_BASE'), '.localhost');

		$_SERVER['HTTP_HOST'] = 'com.ar';
		$this->assertEquals(env('HTTP_BASE'), '.com.ar');

		$_SERVER['HTTP_HOST'] = 'example.ar';
		$this->assertEquals(env('HTTP_BASE'), '.example.ar');

		$_SERVER['HTTP_HOST'] = 'example.com';
		$this->assertEquals(env('HTTP_BASE'), '.example.com');

		$_SERVER['HTTP_HOST'] = 'www.example.com';
		$this->assertEquals(env('HTTP_BASE'), '.example.com');

		$_SERVER['HTTP_HOST'] = 'subdomain.example.com';
		$this->assertEquals(env('HTTP_BASE'), '.example.com');

		$_SERVER['HTTP_HOST'] = 'example.com.ar';
		$this->assertEquals(env('HTTP_BASE'), '.example.com.ar');

		$_SERVER['HTTP_HOST'] = 'www.example.com.ar';
		$this->assertEquals(env('HTTP_BASE'), '.example.com.ar');

		$_SERVER['HTTP_HOST'] = 'subdomain.example.com.ar';
		$this->assertEquals(env('HTTP_BASE'), '.example.com.ar');

		$_SERVER['HTTP_HOST'] = 'double.subdomain.example.com';
		$this->assertEquals(env('HTTP_BASE'), '.subdomain.example.com');

		$_SERVER['HTTP_HOST'] = 'double.subdomain.example.com.ar';
		$this->assertEquals(env('HTTP_BASE'), '.subdomain.example.com.ar');

		$_SERVER = $_ENV = array();

		$_SERVER['SCRIPT_NAME'] = '/a/test/test.php';
		$this->assertEquals(env('SCRIPT_NAME'), '/a/test/test.php');

		$_SERVER = $_ENV = array();

		$_ENV['CGI_MODE'] = 'BINARY';
		$_ENV['SCRIPT_URL'] = '/a/test/test.php';
		$this->assertEquals(env('SCRIPT_NAME'), '/a/test/test.php');

		$_SERVER = $_ENV = array();

		$this->assertFalse(env('HTTPS'));

		$_SERVER['HTTPS'] = 'on';
		$this->assertTrue(env('HTTPS'));

		$_SERVER['HTTPS'] = '1';
		$this->assertTrue(env('HTTPS'));

		$_SERVER['HTTPS'] = 'I am not empty';
		$this->assertTrue(env('HTTPS'));

		$_SERVER['HTTPS'] = 1;
		$this->assertTrue(env('HTTPS'));

		$_SERVER['HTTPS'] = 'off';
		$this->assertFalse(env('HTTPS'));

		$_SERVER['HTTPS'] = false;
		$this->assertFalse(env('HTTPS'));

		$_SERVER['HTTPS'] = '';
		$this->assertFalse(env('HTTPS'));

		$_SERVER = array();

		$_ENV['SCRIPT_URI'] = 'https://domain.test/a/test.php';
		$this->assertTrue(env('HTTPS'));

		$_ENV['SCRIPT_URI'] = 'http://domain.test/a/test.php';
		$this->assertFalse(env('HTTPS'));

		$_SERVER = $_ENV = array();

		$this->assertNull(env('TEST_ME'));

		$_ENV['TEST_ME'] = 'a';
		$this->assertEquals(env('TEST_ME'), 'a');

		$_SERVER['TEST_ME'] = 'b';
		$this->assertEquals(env('TEST_ME'), 'b');

		unset($_ENV['TEST_ME']);
		$this->assertEquals(env('TEST_ME'), 'b');

		$_SERVER = $server;
		$_ENV = $env;
	}

/**
 * Test h()
 *
 * @return void
 */
	public function testH() {
		$string = '<foo>';
		$result = h($string);
		$this->assertEquals('&lt;foo&gt;', $result);

		$in = array('this & that', '<p>Which one</p>');
		$result = h($in);
		$expected = array('this &amp; that', '&lt;p&gt;Which one&lt;/p&gt;');
		$this->assertEquals($expected, $result);

		$string = '<foo> & &nbsp;';
		$result = h($string);
		$this->assertEquals('&lt;foo&gt; &amp; &amp;nbsp;', $result);

		$string = '<foo> & &nbsp;';
		$result = h($string, false);
		$this->assertEquals('&lt;foo&gt; &amp; &nbsp;', $result);

		$string = '<foo> & &nbsp;';
		$result = h($string, 'UTF-8');
		$this->assertEquals('&lt;foo&gt; &amp; &amp;nbsp;', $result);

		$arr = array('<foo>', '&nbsp;');
		$result = h($arr);
		$expected = array(
			'&lt;foo&gt;',
			'&amp;nbsp;'
		);
		$this->assertEquals($expected, $result);

		$arr = array('<foo>', '&nbsp;');
		$result = h($arr, false);
		$expected = array(
			'&lt;foo&gt;',
			'&nbsp;'
		);
		$this->assertEquals($expected, $result);

		$arr = array('f' => '<foo>', 'n' => '&nbsp;');
		$result = h($arr, false);
		$expected = array(
			'f' => '&lt;foo&gt;',
			'n' => '&nbsp;'
		);
		$this->assertEquals($expected, $result);

		// Test that boolean values are not converted to strings
		$result = h(false);
		$this->assertFalse($result);

		$arr = array('foo' => false, 'bar' => true);
		$result = h($arr);
		$this->assertFalse($result['foo']);
		$this->assertTrue($result['bar']);

		$obj = new stdClass();
		$result = h($obj);
		$this->assertEquals('(object)stdClass', $result);

		$obj = new CakeResponse(array('body' => 'Body content'));
		$result = h($obj);
		$this->assertEquals('Body content', $result);
	}

/**
 * Test am()
 *
 * @return void
 */
	public function testAm() {
		$result = am(array('one', 'two'), 2, 3, 4);
		$expected = array('one', 'two', 2, 3, 4);
		$this->assertEquals($expected, $result);

		$result = am(array('one' => array(2, 3), 'two' => array('foo')), array('one' => array(4, 5)));
		$expected = array('one' => array(4, 5), 'two' => array('foo'));
		$this->assertEquals($expected, $result);
	}

/**
 * test cache()
 *
 * @return void
 */
	public function testCache() {
		$_cacheDisable = Configure::read('Cache.disable');
		$this->skipIf($_cacheDisable, 'Cache is disabled, skipping cache() tests.');

		Configure::write('Cache.disable', true);
		$result = cache('basics_test', 'simple cache write');
		$this->assertNull($result);

		$result = cache('basics_test');
		$this->assertNull($result);

		Configure::write('Cache.disable', false);
		$result = cache('basics_test', 'simple cache write');
		$this->assertTrue((boolean)$result);
		$this->assertTrue(file_exists(CACHE . 'basics_test'));

		$result = cache('basics_test');
		$this->assertEquals('simple cache write', $result);
		@unlink(CACHE . 'basics_test');

		cache('basics_test', 'expired', '+1 second');
		sleep(2);
		$result = cache('basics_test', null, '+1 second');
		$this->assertNull($result);

		Configure::write('Cache.disable', $_cacheDisable);
	}

/**
 * test clearCache()
 *
 * @return void
 */
	public function testClearCache() {
		$cacheOff = Configure::read('Cache.disable');
		$this->skipIf($cacheOff, 'Cache is disabled, skipping clearCache() tests.');

		cache('views' . DS . 'basics_test.cache', 'simple cache write');
		$this->assertTrue(file_exists(CACHE . 'views' . DS . 'basics_test.cache'));

		cache('views' . DS . 'basics_test_2.cache', 'simple cache write 2');
		$this->assertTrue(file_exists(CACHE . 'views' . DS . 'basics_test_2.cache'));

		cache('views' . DS . 'basics_test_3.cache', 'simple cache write 3');
		$this->assertTrue(file_exists(CACHE . 'views' . DS . 'basics_test_3.cache'));

		$result = clearCache(array('basics_test', 'basics_test_2'), 'views', '.cache');
		$this->assertTrue($result);
		$this->assertFalse(file_exists(CACHE . 'views' . DS . 'basics_test.cache'));
		$this->assertFalse(file_exists(CACHE . 'views' . DS . 'basics_test.cache'));
		$this->assertTrue(file_exists(CACHE . 'views' . DS . 'basics_test_3.cache'));

		$result = clearCache(null, 'views', '.cache');
		$this->assertTrue($result);
		$this->assertFalse(file_exists(CACHE . 'views' . DS . 'basics_test_3.cache'));

		// Different path from views and with prefix
		cache('models' . DS . 'basics_test.cache', 'simple cache write');
		$this->assertTrue(file_exists(CACHE . 'models' . DS . 'basics_test.cache'));

		cache('models' . DS . 'basics_test_2.cache', 'simple cache write 2');
		$this->assertTrue(file_exists(CACHE . 'models' . DS . 'basics_test_2.cache'));

		cache('models' . DS . 'basics_test_3.cache', 'simple cache write 3');
		$this->assertTrue(file_exists(CACHE . 'models' . DS . 'basics_test_3.cache'));

		$result = clearCache('basics', 'models', '.cache');
		$this->assertTrue($result);
		$this->assertFalse(file_exists(CACHE . 'models' . DS . 'basics_test.cache'));
		$this->assertFalse(file_exists(CACHE . 'models' . DS . 'basics_test_2.cache'));
		$this->assertFalse(file_exists(CACHE . 'models' . DS . 'basics_test_3.cache'));

		// checking if empty files were not removed
		$emptyExists = file_exists(CACHE . 'views' . DS . 'empty');
		if (!$emptyExists) {
			cache('views' . DS . 'empty', '');
		}
		cache('views' . DS . 'basics_test.php', 'simple cache write');
		$this->assertTrue(file_exists(CACHE . 'views' . DS . 'basics_test.php'));
		$this->assertTrue(file_exists(CACHE . 'views' . DS . 'empty'));

		$result = clearCache();
		$this->assertTrue($result);
		$this->assertTrue(file_exists(CACHE . 'views' . DS . 'empty'));
		$this->assertFalse(file_exists(CACHE . 'views' . DS . 'basics_test.php'));
		if (!$emptyExists) {
			unlink(CACHE . 'views' . DS . 'empty');
		}
	}

/**
 * test __()
 *
 * @return void
 */
	public function testTranslate() {
		Configure::write('Config.language', 'rule_1_po');

		$result = __('Plural Rule 1');
		$expected = 'Plural Rule 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __('Plural Rule 1 (from core)');
		$expected = 'Plural Rule 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __('Some string with %s', 'arguments');
		$expected = 'Some string with arguments';
		$this->assertEquals($expected, $result);

		$result = __('Some string with %s %s', 'multiple', 'arguments');
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);

		$result = __('Some string with %s %s', array('multiple', 'arguments'));
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);

		$result = __('Testing %2$s %1$s', 'order', 'different');
		$expected = 'Testing different order';
		$this->assertEquals($expected, $result);

		$result = __('Testing %2$s %1$s', array('order', 'different'));
		$expected = 'Testing different order';
		$this->assertEquals($expected, $result);

		$result = __('Testing %.2f number', 1.2345);
		$expected = 'Testing 1.23 number';
		$this->assertEquals($expected, $result);
	}

/**
 * test __n()
 *
 * @return void
 */
	public function testTranslatePlural() {
		Configure::write('Config.language', 'rule_1_po');

		$result = __n('%d = 1', '%d = 0 or > 1', 0);
		$expected = '%d = 0 or > 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __n('%d = 1', '%d = 0 or > 1', 1);
		$expected = '%d = 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __n('%d = 1 (from core)', '%d = 0 or > 1 (from core)', 2);
		$expected = '%d = 0 or > 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __n('%d item.', '%d items.', 1, 1);
		$expected = '1 item.';
		$this->assertEquals($expected, $result);

		$result = __n('%d item for id %s', '%d items for id %s', 2, 2, '1234');
		$expected = '2 items for id 1234';
		$this->assertEquals($expected, $result);

		$result = __n('%d item for id %s', '%d items for id %s', 2, array(2, '1234'));
		$expected = '2 items for id 1234';
		$this->assertEquals($expected, $result);
	}

/**
 * test __d()
 *
 * @return void
 */
	public function testTranslateDomain() {
		Configure::write('Config.language', 'rule_1_po');

		$result = __d('default', 'Plural Rule 1');
		$expected = 'Plural Rule 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __d('core', 'Plural Rule 1');
		$expected = 'Plural Rule 1';
		$this->assertEquals($expected, $result);

		$result = __d('core', 'Plural Rule 1 (from core)');
		$expected = 'Plural Rule 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __d('core', 'Some string with %s', 'arguments');
		$expected = 'Some string with arguments';
		$this->assertEquals($expected, $result);

		$result = __d('core', 'Some string with %s %s', 'multiple', 'arguments');
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);

		$result = __d('core', 'Some string with %s %s', array('multiple', 'arguments'));
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);
	}

/**
 * test __dn()
 *
 * @return void
 */
	public function testTranslateDomainPlural() {
		Configure::write('Config.language', 'rule_1_po');

		$result = __dn('default', '%d = 1', '%d = 0 or > 1', 0);
		$expected = '%d = 0 or > 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __dn('core', '%d = 1', '%d = 0 or > 1', 0);
		$expected = '%d = 0 or > 1';
		$this->assertEquals($expected, $result);

		$result = __dn('core', '%d = 1 (from core)', '%d = 0 or > 1 (from core)', 0);
		$expected = '%d = 0 or > 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __dn('default', '%d = 1', '%d = 0 or > 1', 1);
		$expected = '%d = 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __dn('core', '%d item.', '%d items.', 1, 1);
		$expected = '1 item.';
		$this->assertEquals($expected, $result);

		$result = __dn('core', '%d item for id %s', '%d items for id %s', 2, 2, '1234');
		$expected = '2 items for id 1234';
		$this->assertEquals($expected, $result);

		$result = __dn('core', '%d item for id %s', '%d items for id %s', 2, array(2, '1234'));
		$expected = '2 items for id 1234';
		$this->assertEquals($expected, $result);
	}

/**
 * test __c()
 *
 * @return void
 */
	public function testTranslateCategory() {
		Configure::write('Config.language', 'rule_1_po');

		$result = __c('Plural Rule 1', 6);
		$expected = 'Plural Rule 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __c('Plural Rule 1 (from core)', 6);
		$expected = 'Plural Rule 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __c('Some string with %s', 6, 'arguments');
		$expected = 'Some string with arguments';
		$this->assertEquals($expected, $result);

		$result = __c('Some string with %s %s', 6, 'multiple', 'arguments');
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);

		$result = __c('Some string with %s %s', 6, array('multiple', 'arguments'));
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);
	}

/**
 * test __dc()
 *
 * @return void
 */
	public function testTranslateDomainCategory() {
		Configure::write('Config.language', 'rule_1_po');

		$result = __dc('default', 'Plural Rule 1', 6);
		$expected = 'Plural Rule 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __dc('default', 'Plural Rule 1 (from core)', 6);
		$expected = 'Plural Rule 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __dc('core', 'Plural Rule 1', 6);
		$expected = 'Plural Rule 1';
		$this->assertEquals($expected, $result);

		$result = __dc('core', 'Plural Rule 1 (from core)', 6);
		$expected = 'Plural Rule 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __dc('core', 'Some string with %s', 6, 'arguments');
		$expected = 'Some string with arguments';
		$this->assertEquals($expected, $result);

		$result = __dc('core', 'Some string with %s %s', 6, 'multiple', 'arguments');
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);

		$result = __dc('core', 'Some string with %s %s', 6, array('multiple', 'arguments'));
		$expected = 'Some string with multiple arguments';
		$this->assertEquals($expected, $result);
	}

/**
 * test __dcn()
 *
 * @return void
 */
	public function testTranslateDomainCategoryPlural() {
		Configure::write('Config.language', 'rule_1_po');

		$result = __dcn('default', '%d = 1', '%d = 0 or > 1', 0, 6);
		$expected = '%d = 0 or > 1 (translated)';
		$this->assertEquals($expected, $result);

		$result = __dcn('default', '%d = 1 (from core)', '%d = 0 or > 1 (from core)', 1, 6);
		$expected = '%d = 1 (from core translated)';
		$this->assertEquals($expected, $result);

		$result = __dcn('core', '%d = 1', '%d = 0 or > 1', 0, 6);
		$expected = '%d = 0 or > 1';
		$this->assertEquals($expected, $result);

		$result = __dcn('core', '%d item.', '%d items.', 1, 6, 1);
		$expected = '1 item.';
		$this->assertEquals($expected, $result);

		$result = __dcn('core', '%d item for id %s', '%d items for id %s', 2, 6, 2, '1234');
		$expected = '2 items for id 1234';
		$this->assertEquals($expected, $result);

		$result = __dcn('core', '%d item for id %s', '%d items for id %s', 2, 6, array(2, '1234'));
		$expected = '2 items for id 1234';
		$this->assertEquals($expected, $result);
	}

/**
 * test LogError()
 *
 * @return void
 */
	public function testLogError() {
		@unlink(LOGS . 'error.log');

		// disable stderr output for this test
		if (CakeLog::stream('stderr')) {
			CakeLog::disable('stderr');
		}

		LogError('Testing LogError() basic function');
		LogError("Testing with\nmulti-line\nstring");

		if (CakeLog::stream('stderr')) {
			CakeLog::enable('stderr');
		}

		$result = file_get_contents(LOGS . 'error.log');
		$this->assertRegExp('/Error: Testing LogError\(\) basic function/', $result);
		$this->assertNotRegExp("/Error: Testing with\nmulti-line\nstring/", $result);
		$this->assertRegExp('/Error: Testing with multi-line string/', $result);
	}

/**
 * test fileExistsInPath()
 *
 * @return void
 */
	public function testFileExistsInPath() {
		if (!function_exists('ini_set')) {
			$this->markTestSkipped('%s ini_set function not available');
		}

		$_includePath = ini_get('include_path');

		$path = TMP . 'basics_test';
		$folder1 = $path . DS . 'folder1';
		$folder2 = $path . DS . 'folder2';
		$file1 = $path . DS . 'file1.php';
		$file2 = $folder1 . DS . 'file2.php';
		$file3 = $folder1 . DS . 'file3.php';
		$file4 = $folder2 . DS . 'file4.php';

		new Folder($path, true);
		new Folder($folder1, true);
		new Folder($folder2, true);
		touch($file1);
		touch($file2);
		touch($file3);
		touch($file4);

		ini_set('include_path', $path . PATH_SEPARATOR . $folder1);

		$this->assertEquals(fileExistsInPath('file1.php'), $file1);
		$this->assertEquals(fileExistsInPath('file2.php'), $file2);
		$this->assertEquals(fileExistsInPath('folder1' . DS . 'file2.php'), $file2);
		$this->assertEquals(fileExistsInPath($file2), $file2);
		$this->assertEquals(fileExistsInPath('file3.php'), $file3);
		$this->assertEquals(fileExistsInPath($file4), $file4);

		$this->assertFalse(fileExistsInPath('file1'));
		$this->assertFalse(fileExistsInPath('file4.php'));

		$Folder = new Folder($path);
		$Folder->delete();

		ini_set('include_path', $_includePath);
	}

/**
 * test convertSlash()
 *
 * @return void
 */
	public function testConvertSlash() {
		$result = convertSlash('\path\to\location\\');
		$expected = '\path\to\location\\';
		$this->assertEquals($expected, $result);

		$result = convertSlash('/path/to/location/');
		$expected = 'path_to_location';
		$this->assertEquals($expected, $result);
	}

/**
 * test debug()
 *
 * @return void
 */
	public function testDebug() {
		ob_start();
		debug('this-is-a-test', false);
		$result = ob_get_clean();
		$expectedText = <<<EXPECTED
%s (line %d)
########## DEBUG ##########
'this-is-a-test'
###########################
EXPECTED;
		$expected = sprintf($expectedText, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 8);

		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', true);
		$result = ob_get_clean();
		$expectedHtml = <<<EXPECTED
<div class="cake-debug-output">
<span><strong>%s</strong> (line <strong>%d</strong>)</span>
<pre class="cake-debug">
&#039;&lt;div&gt;this-is-a-test&lt;/div&gt;&#039;
</pre>
</div>
EXPECTED;
		$expected = sprintf($expectedHtml, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 10);
		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', true, true);
		$result = ob_get_clean();
		$expected = <<<EXPECTED
<div class="cake-debug-output">
<span><strong>%s</strong> (line <strong>%d</strong>)</span>
<pre class="cake-debug">
&#039;&lt;div&gt;this-is-a-test&lt;/div&gt;&#039;
</pre>
</div>
EXPECTED;
		$expected = sprintf($expected, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 10);
		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', true, false);
		$result = ob_get_clean();
		$expected = <<<EXPECTED
<div class="cake-debug-output">

<pre class="cake-debug">
&#039;&lt;div&gt;this-is-a-test&lt;/div&gt;&#039;
</pre>
</div>
EXPECTED;
		$expected = sprintf($expected, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 10);
		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', null);
		$result = ob_get_clean();
		$expectedHtml = <<<EXPECTED
<div class="cake-debug-output">
<span><strong>%s</strong> (line <strong>%d</strong>)</span>
<pre class="cake-debug">
&#039;&lt;div&gt;this-is-a-test&lt;/div&gt;&#039;
</pre>
</div>
EXPECTED;
		$expectedText = <<<EXPECTED
%s (line %d)
########## DEBUG ##########
'<div>this-is-a-test</div>'
###########################
EXPECTED;
		if (php_sapi_name() == 'cli') {
			$expected = sprintf($expectedText, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 17);
		} else {
			$expected = sprintf($expectedHtml, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 19);
		}
		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', null, false);
		$result = ob_get_clean();
		$expectedHtml = <<<EXPECTED
<div class="cake-debug-output">

<pre class="cake-debug">
&#039;&lt;div&gt;this-is-a-test&lt;/div&gt;&#039;
</pre>
</div>
EXPECTED;
		$expectedText = <<<EXPECTED

########## DEBUG ##########
'<div>this-is-a-test</div>'
###########################
EXPECTED;
		if (php_sapi_name() == 'cli') {
			$expected = sprintf($expectedText, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 17);
		} else {
			$expected = sprintf($expectedHtml, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 19);
		}
		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', false);
		$result = ob_get_clean();
		$expected = <<<EXPECTED
%s (line %d)
########## DEBUG ##########
'<div>this-is-a-test</div>'
###########################
EXPECTED;
		$expected = sprintf($expected, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 8);
		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', false, true);
		$result = ob_get_clean();
		$expected = <<<EXPECTED
%s (line %d)
########## DEBUG ##########
'<div>this-is-a-test</div>'
###########################
EXPECTED;
		$expected = sprintf($expected, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 8);
		$this->assertEquals($expected, $result);

		ob_start();
		debug('<div>this-is-a-test</div>', false, false);
		$result = ob_get_clean();
		$expected = <<<EXPECTED

########## DEBUG ##########
'<div>this-is-a-test</div>'
###########################
EXPECTED;
		$expected = sprintf($expected, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 8);
		$this->assertEquals($expected, $result);

		ob_start();
		debug(false, false, false);
		$result = ob_get_clean();
		$expected = <<<EXPECTED

########## DEBUG ##########
false
###########################
EXPECTED;
		$expected = sprintf($expected, str_replace(CAKE_CORE_INCLUDE_PATH, '', __FILE__), __LINE__ - 8);
		$this->assertEquals($expected, $result);
	}

/**
 * test pr()
 *
 * @return void
 */
	public function testPr() {
		ob_start();
		pr('this is a test');
		$result = ob_get_clean();
		$expected = "<pre>this is a test</pre>";
		$this->assertEquals($expected, $result);

		ob_start();
		pr(array('this' => 'is', 'a' => 'test'));
		$result = ob_get_clean();
		$expected = "<pre>Array\n(\n    [this] => is\n    [a] => test\n)\n</pre>";
		$this->assertEquals($expected, $result);
	}

/**
 * test stripslashes_deep()
 *
 * @return void
 */
	public function testStripslashesDeep() {
		$this->skipIf(ini_get('magic_quotes_sybase') === '1', 'magic_quotes_sybase is on.');

		$this->assertEquals(stripslashes_deep("tes\'t"), "tes't");
		$this->assertEquals(stripslashes_deep('tes\\' . chr(0) . 't'), 'tes' . chr(0) . 't');
		$this->assertEquals(stripslashes_deep('tes\"t'), 'tes"t');
		$this->assertEquals(stripslashes_deep("tes\'t"), "tes't");
		$this->assertEquals(stripslashes_deep('te\\st'), 'test');

		$nested = array(
			'a' => "tes\'t",
			'b' => 'tes\\' . chr(0) . 't',
			'c' => array(
				'd' => 'tes\"t',
				'e' => "te\'s\'t",
				array('f' => "tes\'t")
				),
			'g' => 'te\\st'
		);
		$expected = array(
			'a' => "tes't",
			'b' => 'tes' . chr(0) . 't',
			'c' => array(
				'd' => 'tes"t',
				'e' => "te's't",
				array('f' => "tes't")
				),
			'g' => 'test'
		);
		$this->assertEquals($expected, stripslashes_deep($nested));
	}

/**
 * test stripslashes_deep() with magic_quotes_sybase on
 *
 * @return void
 */
	public function testStripslashesDeepSybase() {
		if (!(ini_get('magic_quotes_sybase') === '1')) {
			$this->markTestSkipped('magic_quotes_sybase is off');
		}

		$this->assertEquals(stripslashes_deep("tes\'t"), "tes\'t");

		$nested = array(
			'a' => "tes't",
			'b' => "tes''t",
			'c' => array(
				'd' => "tes'''t",
				'e' => "tes''''t",
				array('f' => "tes''t")
				),
			'g' => "te'''''st"
			);
		$expected = array(
			'a' => "tes't",
			'b' => "tes't",
			'c' => array(
				'd' => "tes''t",
				'e' => "tes''t",
				array('f' => "tes't")
				),
			'g' => "te'''st"
			);
		$this->assertEquals($expected, stripslashes_deep($nested));
	}

/**
 * test pluginSplit
 *
 * @return void
 */
	public function testPluginSplit() {
		$result = pluginSplit('Something.else');
		$this->assertEquals(array('Something', 'else'), $result);

		$result = pluginSplit('Something.else.more.dots');
		$this->assertEquals(array('Something', 'else.more.dots'), $result);

		$result = pluginSplit('Somethingelse');
		$this->assertEquals(array(null, 'Somethingelse'), $result);

		$result = pluginSplit('Something.else', true);
		$this->assertEquals(array('Something.', 'else'), $result);

		$result = pluginSplit('Something.else.more.dots', true);
		$this->assertEquals(array('Something.', 'else.more.dots'), $result);

		$result = pluginSplit('Post', false, 'Blog');
		$this->assertEquals(array('Blog', 'Post'), $result);

		$result = pluginSplit('Blog.Post', false, 'Ultimate');
		$this->assertEquals(array('Blog', 'Post'), $result);
	}

/**
 * Test that buildQuery() is working properly.
 *
 * Also, test PHP5's http_build_query() to contrast the difference.
 *
 * @return void
 */
	public function testBuildQuery() {
		$expected = 'framework=cakephp';
		$this->assertEquals($expected, buildQuery('framework=cakephp'));
		$this->assertEquals($expected, buildQuery('?framework=cakephp'));

		$test = array(
			'First' => 'value1',
			'Second' => array(
				'Key1' => array(
					'Key2' => 'value 2'
				),
				'Key3' => array(
					'Key4' => 'lála'
				)
			),
			'True' => true,
			'False' => false,
			'EmptyString1' => '',
			'NestedArray.1' => array(
				'' => 'value.3'
			),
			'NestedArray.2' => array(
				'EmptyString2' => '',
				'EmptyArray1' => array()
			),
			'EmptyArray2' => array(),
			'Null' => null
		);

		// Separator not being set
		// 'First=value1&Second[Key1][Key2]=value+2&Second[Key3][Key4]=lála&True=1&False=0&EmptyString1=&NestedArray.1[]=value.3&NestedArray.2[EmptyString2]=';
		// 'First=value1&Second[Key1][Key2]=value+2&Second[Key3][Key4]=lála&True=1&False=0&EmptyString1=&NestedArray.1[]=value.3&NestedArray.2[EmptyString2]=&NestedArray.2[EmptyArray1]=&EmptyArray2=&Null=';
		$expected     = 'First=value1&Second%5BKey1%5D%5BKey2%5D=value+2&Second%5BKey3%5D%5BKey4%5D=l%C3%A1la&True=1&False=0&EmptyString1=&NestedArray.1%5B%5D=value.3&NestedArray.2%5BEmptyString2%5D=';
		$cakeExpected = 'First=value1&Second%5BKey1%5D%5BKey2%5D=value+2&Second%5BKey3%5D%5BKey4%5D=l%C3%A1la&True=1&False=0&EmptyString1=&NestedArray.1%5B%5D=value.3&NestedArray.2%5BEmptyString2%5D=&NestedArray.2%5BEmptyArray1%5D=&EmptyArray2=&Null=';
		$query1 = http_build_query($test, null, '&');
		$this->assertEquals($expected, $query1);
		$query2 = buildQuery($test);  // not set, use default
		$this->assertEquals($cakeExpected, $query2);

		// Unbuild (aka parse)
		$parsedExpected1 = array(
			'First' => 'value1',
			'Second' => array(
				'Key1' => array(
					'Key2' => 'value 2'
				),
				'Key3' => array(
					'Key4' => 'lála'
				)
			),
			'True' => '1',
			'False' => '0',
			'EmptyString1' => '',
			'NestedArray.1' => array(
				0 => 'value.3'
			),
			'NestedArray.2' => array(
				'EmptyString2' => ''
			)
		);
		$parsedExpected2 = array(
			'First' => 'value1',
			'Second' => array(
				'Key1' => array(
					'Key2' => 'value 2'
				),
				'Key3' => array(
					'Key4' => 'lála'
				)
			),
			'True' => '1',
			'False' => '0',
			'EmptyString1' => '',
			'NestedArray.1' => array(
				0 => 'value.3'
			),
			'NestedArray.2' => array(
				'EmptyString2' => '',
				'EmptyArray1' => ''
			),
			'EmptyArray2' => '',
			'Null' => ''
		);
		$this->assertEquals($parsedExpected1, parseQuery($query1));
		$this->assertEquals($parsedExpected2, parseQuery($query2));

		// Setting separator
		$expected     = 'First=value1&amp;Second%5BKey1%5D%5BKey2%5D=value+2&amp;Second%5BKey3%5D%5BKey4%5D=l%C3%A1la&amp;True=1&amp;False=0&amp;EmptyString1=&amp;NestedArray.1%5B%5D=value.3&amp;NestedArray.2%5BEmptyString2%5D=';
		$cakeExpected = 'First=value1&amp;Second%5BKey1%5D%5BKey2%5D=value+2&amp;Second%5BKey3%5D%5BKey4%5D=l%C3%A1la&amp;True=1&amp;False=0&amp;EmptyString1=&amp;NestedArray.1%5B%5D=value.3&amp;NestedArray.2%5BEmptyString2%5D=&amp;NestedArray.2%5BEmptyArray1%5D=&amp;EmptyArray2=&amp;Null=';
		$query = http_build_query($test, null, '&amp;');
		$this->assertEquals($expected, $query);
		$query = buildQuery($test, '&amp;');  // set
		$this->assertEquals($cakeExpected, $query);

		// Unsetting separator
		$separator = '|';
		$oldSeparator = ini_set('arg_separator.output', $separator);
		$this->assertEquals($separator, ini_get('arg_separator.output'));
		$expected     = 'First=value1|Second%5BKey1%5D%5BKey2%5D=value+2|Second%5BKey3%5D%5BKey4%5D=l%C3%A1la|True=1|False=0|EmptyString1=|NestedArray.1%5B%5D=value.3|NestedArray.2%5BEmptyString2%5D=';
		$cakeExpected = 'First=value1|Second%5BKey1%5D%5BKey2%5D=value+2|Second%5BKey3%5D%5BKey4%5D=l%C3%A1la|True=1|False=0|EmptyString1=|NestedArray.1%5B%5D=value.3|NestedArray.2%5BEmptyString2%5D=|NestedArray.2%5BEmptyArray1%5D=|EmptyArray2=|Null=';
		$query = http_build_query($test);
		$this->assertEquals($expected, $query);
		$query = buildQuery($test, null);  // unset, rely on PHP's 'arg_separator.output' configuration option
		$this->assertEquals($cakeExpected, $query);

		// Booleans and Empty values
		$test = array(
			'True1' => true,
			'True2' => 1,
			'True3' => '1',
			'False1' => false,
			'False2' => 0,
			'False3' => '0',
			'Empty' => ''
		);
		$expected     = 'True1=1&True2=1&True3=1&False1=0&False2=0&False3=0&Empty=';
		$cakeExpected = 'True1=1&True2=1&True3=1&False1=0&False2=0&False3=0&Empty=';
		$query1 = http_build_query($test, null, '&');
		$this->assertEquals($expected, $query1);
		$query2 = buildQuery($test);
		$this->assertEquals($cakeExpected, $query2);
	}

/**
 * Asserts that HttpSocket::parseQuery is working properly
 *
 * @return void
 */
	public function testParseQuery() {
		$query = parseQuery(array('framework' => 'cakephp'));
		$this->assertEquals(array('framework' => 'cakephp'), $query);

		$query = parseQuery('');
		$this->assertEquals(array(), $query);

		$query = parseQuery('framework=cakephp');
		$this->assertEquals(array('framework' => 'cakephp'), $query);

		$query = parseQuery('?framework=cakephp');
		$this->assertEquals(array('framework' => 'cakephp'), $query);

		$query = parseQuery('a&b&c');
		$this->assertEquals(array('a' => '', 'b' => '', 'c' => ''), $query);

		$query = parseQuery('value=12345');
		$this->assertEquals(array('value' => '12345'), $query);

		$query = parseQuery('a[0]=foo&a[1]=bar&a[2]=cake');
		$this->assertEquals(array('a' => array(0 => 'foo', 1 => 'bar', 2 => 'cake')), $query);

		$query = parseQuery('a[]=foo&a[]=bar&a[]=cake');
		$this->assertEquals(array('a' => array(0 => 'foo', 1 => 'bar', 2 => 'cake')), $query);

		$query = parseQuery('a[][]=foo&a[][]=bar&a[][]=cake');
		$expectedQuery = array(
			'a' => array(
				0 => array(
					0 => 'foo'
				),
				1 => array(
					0 => 'bar'
				),
				array(
					0 => 'cake'
				)
			)
		);
		$this->assertEquals($expectedQuery, $query);

		$query = parseQuery('a[][]=foo&a[bar]=php&a[][]=bar&a[][]=cake');
		$expectedQuery = array(
			'a' => array(
				array('foo'),
				'bar' => 'php',
				array('bar'),
				array('cake')
			)
		);
		$this->assertEquals($expectedQuery, $query);

		$query = parseQuery('user[]=jim&user[3]=tom&user[]=bob');
		$expectedQuery = array(
			'user' => array(
				0 => 'jim',
				3 => 'tom',
				4 => 'bob'
			)
		);
		$this->assertEquals($expectedQuery, $query);

		$queryStr = 'user[0]=foo&user[0][items][]=foo&user[0][items][]=bar&user[][name]=jim&user[1][items][personal][]=book&user[1][items][personal][]=pen&user[1][items][]=ball&user[count]=2&empty';
		$query = parseQuery($queryStr);
		$expectedQuery = array(
			'user' => array(
				0 => array(
					'items' => array(
						'foo',
						'bar'
					)
				),
				1 => array(
					'name' => 'jim',
					'items' => array(
						'personal' => array(
							'book',
							'pen'
						),
						'ball'
					)
				),
				'count' => '2'
			),
			'empty' => ''
		);
		$this->assertEquals($expectedQuery, $query);

		$query = 'openid.ns=example.com&foo=bar&foo=baz';
		$result = parseQuery($query);
		$expected = array(
			'openid.ns' => 'example.com',
			'foo' => array('bar', 'baz')
		);
		$this->assertEquals($expected, $result);
	}
}
