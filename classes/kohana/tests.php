<?php
/**
 * PHPUnit testsuite for kohana application
 *
 * @package    Unittest
 * @author     Kohana Team
 * @author     BRMatt <matthew@sigswitch.com>
 * @author	   Paul Banks
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Tests
{
	static protected $cache = array();

	/**
	 * Loads test files if they cannot be found by kohana
	 * @param <type> $class
	 */
	static function autoload($class)
	{
		$file = str_replace('_', '/', $class);

		if($file = Kohana::find_file('tests', $file))
		{
			require_once $file;
		}
	}

	/**
	 * Configures the enviroment for testing
	 *
	 * Does the following:
	 *
	 * * Loads the phpunit framework (for the web ui)
	 * * Restores exception phpunit error handlers (for cli)
	 * * registeres an autoloader to load test files
	 */
	static public function configure_enviroment($do_whitelist = TRUE, $do_blacklist = TRUE)
	{
		if( ! class_exists('PHPUnit_Util_Filter', FALSE))
		{
			// Make sure the PHPUnit classes are available
			require_once 'PHPUnit/Autoload.php';
		}

		if(Kohana::$is_cli)
		{
			restore_exception_handler();
			restore_error_handler();
		}
		
		spl_autoload_register(array('Kohana_Tests', 'autoload'));

		Kohana_Tests::$cache = ($cache = Kohana::cache('phpunit_whitelist_cache')) === NULL ? array() : $cache;

		$config = Kohana::$config->load('phpunit');

		if($do_whitelist AND $config->use_whitelist)
		{
			self::whitelist();
		}

		if($do_blacklist AND count($config['blacklist']))
		{
			foreach($config->blacklist as $item)
			{
				if(is_dir($item))
				{
					PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist($item);
				}
				else
				{
					PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist($item);
				}
			}
		}
	}

	/**
	 * Helper function to see if phpunit is enabled in the config
	 *
	 * @return boolean
	 */
	static function enabled()
	{
		$p_enviroment = Kohana::$config->load('phpunit.enviroment');
		$k_enviroment = Kohana::$environment;

		return  (is_array($p_enviroment) AND in_array($k_enviroment, $p_enviroment))
				OR
				($k_enviroment === $p_enviroment);
	}

	/**
	 * Creates the test suite for kohana
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	static function suite()
	{
		static $suite = NULL;

		if($suite instanceof PHPUnit_Framework_TestSuite)
		{
			return $suite;
		}
		
		$files = Kohana::list_files('utests');

		$suite = new PHPUnit_Framework_TestSuite();

		self::addTests($suite, $files);

		return $suite;
	}

	/**
	 * Add files to test suite $suite
	 *
	 * Uses recursion to scan subdirectories
	 *
	 * @param PHPUnit_Framework_TestSuite  $suite   The test suite to add to
	 * @param array                        $files   Array of files to test
	 */
	static function addTests(PHPUnit_Framework_TestSuite $suite, array $files)
	{
		foreach($files as $file)
		{
			if(is_array($file))
			{
				self::addTests($suite, $file);
			} 
			else 
			{		
				if(is_file($file))
				{
					// The default PHPUnit TestCase extension
					if(! strpos($file, 'TestCase'.EXT))
					{			
						$suite->addTestFile($file);
					}
					else
					{
						require_once($file);
					}

					PHP_CodeCoverage_Filter::getInstance()->addDirectoryToBlacklist($file);
				}
			}
		}
	}

	/**
	 * Sets the whitelist
	 *
	 * If no directories are provided then the function'll load the whitelist
	 * set in the config file
	 *
	 * @param array $directories Optional directories to whitelist
	 */
	static public function whitelist(array $directories = NULL)
	{
		if(empty($directories))
		{
			$directories = self::get_config_whitelist();
		}

		if(count($directories))
		{
			foreach($directories as &$directory)
			{
				$directory = realpath($directory).'/';
			}

			// Only whitelist the "top" files in the cascading filesystem
			self::set_whitelist(Kohana::list_files('classes', $directories));
		}
	}

	/**
	 * Works out the whitelist from the config
	 * Used only on the CLI
	 *
	 * @returns array Array of directories to whitelist
	 */
	static protected function get_config_whitelist()
	{
		$config = Kohana::$config->load('phpunit');
		$directories = array();

		if($config->whitelist['app'])
		{
			$directories['k_app'] = APPPATH;
		}

		if($modules = $config->whitelist['modules'])
		{
			$k_modules = Kohana::modules();

			// Have to do this because kohana merges config...
			// If you want to include all modules & override defaults then TRUE must be the first
			// value in the modules array of your app/config/phpunit file
			if(array_search(TRUE, $modules, TRUE) === (count($modules) - 1))
			{
				$modules = $k_modules;
			}
			elseif(array_search(FALSE, $modules, TRUE) === FALSE)
			{
				$modules = array_intersect_key($k_modules, array_combine($modules, $modules));
			}
			else
			{
				// modules are disabled
				$modules = array();
			}

			$directories += $modules;
		}

		if($config->whitelist['system'])
		{
			$directories['k_sys'] = SYSPATH;
		}

		return $directories;
	}

	/**
	 * Recursively whitelists an array of files
	 *
	 * @param array $files Array of files to whitelist
	 */
	static protected function set_whitelist($files)
	{
		foreach($files as $file)
		{
			if(is_array($file))
			{
				self::set_whitelist($file);
			}
			else
			{
				if( ! isset(Kohana_Tests::$cache[$file]))
				{
					$relative_path	= substr($file, strrpos($file, 'classes/') + 8, -strlen(EXT));
					$cascading_file = Kohana::find_file('classes', $relative_path);

					// The theory is that if this file is the highest one in the cascading filesystem
					// then it's safe to whitelist
					Kohana_Tests::$cache[$file] =  ($cascading_file === $file);
				}
		
				if(Kohana_Tests::$cache[$file])
				{
					PHPUnit_Util_Filter::addFileToWhitelist($file);
				}
			}
		}
	}

}
