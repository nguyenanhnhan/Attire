<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\FilterManager;
use Assetic\Filter\CssRewriteFilter;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */

/**
 * CodeIgniter Attire
 *
 * Templating with this class is done by layering the standard CI view system with and extending 
 * it to asset management with Assetic. The basic idea is that for every single CI view 
 * there are individual CSS, Javascript and View files that correlate to it and this 
 * structure is conected with the Twig Class.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries 
 * @category	Libraries
 * @author		David Sosa Valdes
 * @link		https://gitlab.com/david-sosa-valdes/attire
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version 	1.1.0
 *
 */

class Attire
{
	/**
	 * [$_assets description]
	 * @var array
	 */
	protected $_assets = array();

	/**
	* Restricted view files mode
	* @var bool
	*/
	protected $_restricted_mode = FALSE;
	
	/**
	 * Twig current lexer established
	 * @var array
	 */
	protected $_current_lexer = array();

	/**
	 * Master layout template name
	 * @var string
	 */
	protected $_default_template = 'theme';

	/**
	 * Twig_Environment auto reload attr 
	 * @var boolean
	 */
	protected $_auto_reload = FALSE;

	/**
	 * Codeigniter directives used as global functions.
	 * @var mixed
	 */
	protected $_directives = array();

	/**
	 * Theme selected as interface
	 * @var string
	 */
	protected $_theme;

	/**
	 * Set of views used Twig_Filesystem
	 * @var array
	 */
	protected $_views = array();

	/**
	 * The syntax set used for personalize Twig_Lexer
	 * @var array
	 */
	protected $_available_lexer = array('tag_comment','tag_block','tag_variable');

	/**
	 * Set of global params
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Twig file extension
	 * @var string
	 */
	protected $_extension = '.twig';

	/**
	 * Twig layout childs
	 * @var array
	 */
	protected $_childs = array();

	/**
	 * Set of global paths
	 * @var array
	 */
	protected $_paths = array();

	/**
	 * Twig_Environment debug mode
	 * @var boolean
	 */
	protected $_debug = FALSE;

	/**
	 * Twig_Environment object
	 * @var object
	 */
	protected $_environment;

	/**
	 * Twig_Loader_Filesystem object
	 * @var object
	 */
	protected $_loader;

	/**
	 * CI Singleton
	 * @var [type]
	 */
  	protected $_ci;

  	/**
  	 * Class Constuctor
  	 *
  	 * Loads the CI Instance, Filesystem and Paths.
  	 * 
  	 * @param array $config [description]
  	 * @return void 
  	 */
	public function __construct($config = array())
	{
        $this->_ci =& get_instance();
        Twig_Autoloader::register();
        try 
        {
	        $this->_ci->load->config('attire', TRUE);   
        	$this->_set($config);

			# Set absolute of assets and theme instances
			$default_paths = array(
				'assets'  => $this->_ci->config->item('assets_path', 'attire', TRUE),
				'theme'   => $this->_ci->config->item('theme_path', 'attire', TRUE),
				'bower'	  => $this->_ci->config->item('bower_path','attire', TRUE)
			);
			foreach ($default_paths as $key => $path) 
			{
				if ($path !== '' && (! file_exists($path))) 
				{
					throw new Exception("Directory {$path} currently not exist.");
				}
				else 
				{
					$this->_paths[$key] = $path;
				}
			}	
        } 
        catch (Exception $e) 
        {
        	$this->_show_error($e->getMessage());
        }
	}

	/**
	 * Set Twig Extension
	 *
	 * Used (only) in every file in Theme directory.
	 * 
	 * @param string $new_extension 
	 * @return void 
	 */
	public function set_extension($new_extension = "")
	{
		try 
		{
			if (! preg_match('/^.*\.(twig|php.twig|html|html.twig)$/i', $new_extension)) 
			{
				throw new Exception("Extension is not valid, use .twig|.php.twig|.html|.html.twig");
			}
		} 
		catch (Exception $e) 
		{
			$this->_show_error($e->getMessage());		
		}
		$this->_extension = $new_extension;
	}

	/**
	 * Initialize global attributes
	 * 
	 * @param array $params
	 * @return void
	 */
	private function _set($params = array())
	{
		foreach ($params as $key => $val)
		{
			$this->{'_'.$key} = $val;
		}	
	}	

	/**
	 * Show CI Error
	 *
	 * Used in every try/catch 
	 * 
	 * @param  string $error description of the error
	 * @param  string $title title error
	 * @return void
	 */
	private function _show_error($error = "", $title = 'Attire Error')
	{
		return show_error($error,404,$title);
	}

	/**
	 * Set Twig Loader
	 *
	 * The loaders are responsible for loading templates from a resource such as the Filesystem.
	 * 
	 * @param mixed  $value  Twig Loader first param 
	 * @param string  $type  The current type of Twig Loader
	 * @param boolean  $option 
	 * @return void 
	 */
	public function set_loader($value, $type = "",$option = FALSE)
	{
		$params = array();
		try {
			if ($type === 'filesystem') 
			{
				$directory = $this->_paths['theme'].$value;
				$this->_loader = new Twig_Loader_Filesystem($directory);
				/**
				 * @todo Cache/auto_reload currently not working
				 *
				 * $params["cache"]       = $this->_paths["cache"];
				 * $params['auto_reload'] = $this->_auto_reload;	
				 */
				$params['debug'] = $this->_debug;
			}
			elseif ($type === 'array') 
			{
				if (is_array($value)) 
				{
					throw new Exception("Twig_Loader_Array needs an array structure as first param.");
				}
				else
				{
					$this->_loader = new Twig_Loader_Array($value);
				}
			}
			else 
			{
				$this->_loader = new Twig_Loader_String();
			}			
		} 
		catch (Exception $e) 
		{
			$this->_show_error($e->getMessage());
		}
		$this->_environment = new Twig_Environment($this->_loader,$params);
		return $this;
	}

	/**
	 * Set Twig Lexer
	 *
	 * Change the default Twig Lexer syntax, depends of available lexer declared
	 * in the class
	 * 
	 * @param array $lexer 
	 * @return self 
	 */
	public function set_lexer($lexer = array())
	{
		try 
		{
			foreach ($lexer as $tag => $value) 
			{
				if (! in_array($tag, $this->_available_lexer)) 
				{
					throw new Exception("Lexer tag is not available.");
				}
			}
		} 
		catch (Exception $e) 
		{
			$this->_show_error($e->getMessage());
		}		
		$this->_current_lexer = $lexer;
		return $this;	
	}

	/**
	 * Add Twig Functions
	 *
	 * The functions can be called to generate content. The functions are called by his name 
	 * and can have arguments
	 * 
	 * @param string $name  	Name of the function
	 * @param mixed $function   Variable function
	 * @return self
	 */
	public function add_function($name = "", $function)
	{
		try 
		{
			if (! is_callable($function)) 
			{
				throw new Exception("Variable can't be called as a function.");
			}
			elseif (! is_string($name)) 
			{
				throw new Exception("'add_function' first param needs to be a string (the function name).");
			}
			elseif (! is_a($this->_environment, 'Twig_Environment')) 
			{
				throw new Exception("Twig_Environment isn't set correctly.");
			}
		} 
		catch (Exception $e) 
		{
			$this->_show_error($e->getMessage());
		}
		$function = new Twig_SimpleFunction($name,$function);
		$this->_environment->addFunction($function);
		return $this;
	}

	/**
	 * Add global path
	 *
	 * Prepend or append Twig Loader global path
	 * 
	 * @param string  $path       Relative path
	 * @param string  $namespace Space name without the '@'
	 * @param boolean $prepend    Prepend method mode
	 * 
	 * @return self
	 */
	public function add_path($path = '', $namespace = '__main__', $prepend = FALSE)
	{
		try 
		{
			if (! is_a($this->_loader, 'Twig_Loader_Filesystem')) 
			{
				throw new Exception('Loader not set correctly.');
			}
			# Checking if directory path exist
			$absolute_path = str_replace('//', '/', realpath(rtrim($path, '/').'/'));
			if (! file_exists($path)) 
			{
				throw new Exception("{$path} currently not exist.");
			}
		} 
		catch (Exception $e) 
		{
			$this->_show_error($e->getMessage());
		}
		# Prepend or append?
		($prepend !== FALSE)? 
			$this->_loader->prependPath($absolute_path, $namespace):
			$this->_loader->addPath($absolute_path, $namespace);
		return $this;
	}

	/**
	 * Load Twig Template
	 * 
	 * Loads a template by name
	 * 
	 * @param  string $namepath Filename (includes path)
	 * @return mixed       
	 */
	public function load_template($namepath = "")
	{
		try 
		{
			if (! is_a($this->_loader, 'Twig_Loader_Filesystem')) 
			{
				throw new Exception('Need the Twig_Loader_Filesystem before loading.');
			}	
		} 
		catch (Exception $e) 
		{
			$this->_show_error($e->getMessage());
		}
		$template_path = "{$namepath}{$this->_extension}";
		return $this->_environment->loadTemplate($template_path);
	}

	/**
	 * Add global param in Twig 
	 * 
	 * @param string $name  Param name
	 * @param mixed $value Param value
	 */
	public function add_global($name, $value = NULL)
	{
		return $this->set_param($name,$value);
	}

	/**
	 * Add global params in Twig
	 * 
	 * @param string $name  Param name
	 * @param mixed $value Param value
	 */
	public function set_param($name = "", $value = NULL)
	{
		try {
			if (!is_string($name)) {
				throw new Exception("Set param as string.");
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}
		$this->_params[$name] = $value;
		return $this;
	}

	/**
	 * Set Attire Theme
	 *
	 * Set the absolute path in the Twig loader filesystem method.
	 * 
	 * @param string $name Theme name
	 */
	public function set_theme($name = "")
	{
		$this->_theme = rtrim($name.'/');
		$this->set_loader($name,'filesystem');
		return $this;
	}

	/**
	 * Add a child in Twig (Deprecated)
	 *
	 * All child files are rendered in the output in the order they added.
	 *
	 * @param string $type       filename
	 * @param array  $params     child view params
	 * @param string $child_path global child inside theme directory
	 * @return void 
	 */
	public function add_child($type = "", $params = array(), $child_path = "childs")
	{
		try {
			if (in_array($type, $this->_childs)) 
			{
				throw new Exception("Redefinition of a child.");
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}
		$path = "{$child_path}/{$type}{$this->_extension}";
		$this->_childs[$path] = (array) $params;
		return $this;
	}

	/**
	 * Add a layout in Twig
	 *
	 * All layout files are rendered in the output in the order they added.
	 *
	 * @param string $type    filename
	 * @param array  $params  child view params
	 * @return void 
	 */
	public function add_layout($type = "", $params = array())
	{
		try {
			if (in_array($type, $this->_childs)) 
			{
				throw new Exception("Redefinition of a child.");
			}			
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}
		$path = "layout/{$type}{$this->_extension}";
		$this->_childs[$path] = (array) $params;
		return $this;
	}

	/**
	 * Cloned method from add_layout
	 */
	public function set_layout($type = "", $params = array())
	{
		return $this->add_layout($type,$params);
	}

	/**
	 * Add CI View file
	 *
	 * Every view file added (in the order they added) is rendered at last
	 * 
	 * Note: this method is equivalente to codeigniter load_view method.
	 * 
	 * @param string $view      filename
	 * @param array  $params    view params
	 * @param string $extension file extension used as default
	 * @return void 
	 */
	public function add_view($view = "", $params = array(), $extension = '.php')
	{
		try 
		{
			if (in_array($view, $this->_views)) 
			{
				throw new Exception("Not possible to add a view twice.");
			}
			elseif (!is_string($view)) 
			{
				throw new Exception("Need the view path in string format.");
			}
			elseif (empty($this->_childs)) 
			{
				throw new Exception("There is no layout, run first add_layout.");
			}
		} 
		catch (Exception $e) 
		{
			$this->_show_error($e->getMessage());
		}
		if ($this->_restricted_mode !== FALSE) 
		{
			$class = $this->_ci->router->fetch_class();
			$method = $this->_ci->router->fetch_method();
			$path = "@VIEWPATH/{$class}/{$method}/{$view}{$extension}";
		}
		elseif (strpos($view, '@') === FALSE) 
		{
			$path = "@VIEWPATH/{$view}{$extension}";
		}
		else
		{
			$path = "{$view}{$extension}";
		}
		$this->_views[$path] = (array) $params;
		return $this;
	}

	/**
	 * Set CI Helper functions
	 *
	 * Used as a Twig function in every template if a specific helper is loaded in controller.
	 *
	 * Note: Some other functions are needed to add, this are the principal functions used in 
	 * 		 every CI Project.
	 * @return void 
	 */
	private function _set_ci_functions()
	{
		$functions = array(	
			'base_url' => function($path){ 
				return base_url($path); 
			},
			'site_url' => function($path){ 
				return site_url($path); 
			},
			'current_url' => function(){
				return current_url();
			},
			'anchor' => function($uri = '', $title = '', $attributes = ''){
				return anchor($uri, $title, $attributes);
			},
			'form_open' => function($path, $params = array()){ 
				return form_open($path, $params); 
			},
			'form_open_multipart' => function($path, $params = array()){
				return form_open_multipart($path, $params);
			},
			'form_close' => function(){
				return form_close();
			},
            'javascript_tag' => function($content = null, $html_attributes = array()){
                return javascript_tag($content = null, $html_attributes = array());
            },
            'stylesheet_tag' => function($content = null, $html_attributes = array()){
                return stylesheet_tag($content = null, $html_attributes = array());
            },
            'include_javascript' => function($file, $additional = null){
                return include_javascript($file, $additional = null);
            },
            'include_stylesheet' => function($file, $additional = null){
                return include_stylesheet($file, $additional = null);
            },
            'lang' => function($line, $for = '', $attributes = array()){
            	return lang($line, $for, $attributes);
            }		
		);
        $config_functions = $this->_ci->config->item('twig_ci_functions', 'attire', TRUE);
        is_array($config_functions) && $functions = array_merge($functions,$config_functions);
		foreach ($functions as $name => $function) 
		{
			if (function_exists($name)) 
			{
				$this->add_function($name,$function);
			}
		}
	}

	/**
	 * Add Twig filters same as functions
	 * 
	 * @param [type] $name     [description]
	 * @param [type] $function [description]
	 */
	public function add_filter($name, $function=NULL)
	{
		try 
		{
			if ((!is_callable($function)) && (!is_string($name))) 
			{
				throw new Exception("Cannot set function, check params.");
			}
			elseif (! is_a($this->_environment, 'Twig_Environment')) 
			{
				throw new Exception("Twig_Environment is not set correctly.");
			}			
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}
		$filter = new Twig_SimpleFilter($name,$function);
		return $this;
	}

	/**
	 * [add_asset description]
	 * @param [type] $module_name [description]
	 * @param [type] $path        [description]
	 */
	public function add_asset($module_name, $path)
	{
		$this->_assets[$module_name] = array(
			'path' => $path,
			'type' => 'Assetic\Asset\FileAsset'
		);
		return $this;
	}

	/**
	 * [add_bower_package description]
	 * @param [type] $module_name [description]
	 * @param [type] $path        [description]
	 */
	public function add_bower_package($module_name, $path)
	{
		return $this->add_asset($module_name,$this->_paths['bower'].$path);
	}

	/**
	 * Render the Twig template with Assetic manager
	 *
	 * If Twig Loader method is string, we can render view as string template and
	 * set the params, else there is no need to declare params or view in this method.
	 * 
	 * @param  string $view   
	 * @param  array  $params 
	 * @return void         
	 */
	public function render($view = "", $params = array())
	{
		$this->_ci->benchmark->mark('AttireRender_start');
		# Autoload url helper (required)
		$this->_ci->load->helper('url');		
		# Set current Theme global vars
		$globals = $this->_ci->config->item('global_vars', 'attire', TRUE);
		foreach ($globals as $key => $value) 
		{
			(is_callable($value))?
				$this->set_param($key,call_user_func($value)):
				$this->set_param($key,$value);
		}
		# Set Codeigniter Helper functions inside Attire environment
		$this->_set_ci_functions();
		# Add default view path
		$this->add_path(VIEWPATH, 'VIEWPATH');
		# Twig environment (master of puppets)
		$twig = &$this->_environment;				
		$escaper = new Twig_Extension_Escaper('html');
		$twig->addExtension($escaper);	
		# Declare asset manager and add global paths
		$am = new AssetManager();
		# Assets global paths
		if ($bundles_path = config_item('bundles_path')) 
		{
			$class  = $this->_ci->router->fetch_class();
			$method = $this->_ci->router->fetch_method();
			$directory = $this->_ci->router->directory;

			$absolute_path = rtrim($bundles_path.$directory.'assets','/');
			$global_assets = array(
				'module_js'  => array(
					'path' => "{$absolute_path}/js/{$class}/{$method}/*",
					'type' => 'Assetic\Asset\GlobAsset'
				),
				'module_css' => array(
					'path' => "{$absolute_path}/css/{$class}/{$method}/*",
					'type' => 'Assetic\Asset\GlobAsset'
				),
				'global_css' => array(
					'path' => "{$absolute_path}/css/{$class}/*",
					'type' => 'Assetic\Asset\GlobAsset'
				),
				'global_js'  => array(
					'path' => "{$absolute_path}/js/{$class}/*",
					'type' => 'Assetic\Asset\GlobAsset'
				)
			);
			foreach (array_merge($global_assets,$this->_assets) as $global => $params) 
			{
				$class_name = $params['type'];
				$am->set($global, new $class_name($params['path']));
			}
		}
		# Declare filters manager
		$fm = new FilterManager();
		$fm->set('cssrewrite', new CssRewriteFilter());
		$absolute_path = rtrim("{$this->_paths["theme"]}{$this->_theme}",'/').'/assets';
		# Declare assetic factory with filters and assets
		$factory = new AssetFactory($absolute_path);
		$factory->setAssetManager($am);
		$factory->setFilterManager($fm);
		$factory->setDebug($this->_debug);
		# Add assetic extension to factory
		$absolute_path = rtrim($this->_paths['assets'],'/');
		$factory->setDefaultOutput($absolute_path);
		$twig->addExtension(new AsseticExtension($factory));
		# This is too lazy, we need a lazy asset manager...
		$am = new LazyAssetManager($factory);
		$am->setLoader('twig', new TwigFormulaLoader($twig));
		# Adding the Twig resource (following the assetic documentation)
		$resource = new TwigResource($this->_loader, $this->_default_template.$this->_extension);
		$am->addResource($resource, 'twig');
		# Write all assets files in the output directory in one or more files
		try {
			$writer = new AssetWriter($absolute_path);
			$writer->writeManagerAssets($am);
		} catch (\RuntimeException $e) {
			$this->_show_error($e->getMessage());
		}		
		# Set current lexer
		if (!empty($this->_current_lexer)) 
		{
			$lexer = new Twig_Lexer($this->_environment, $this->_current_lexer);
			$twig->setLexer($lexer);
		}
		try {
			# Render all childs
			if (! empty($this->_childs)) 
			{
				foreach ($this->_childs as $child => $params) 
				{
					$this->_params['views'] = $this->_views;
					echo $twig->render($child, array_merge($params,$this->_params));
				}
				# Remove childs after the use
				$this->_childs = array();
			}
			# Else render params as string format (Twig_Loader_String)
			elseif (strlen($view) <= 1 && ($this->_loader instanceof Twig_Loader_String)) 
			{
				echo $twig->render($this->_default_template.$this->_extension, $params);	
			}			
		} 
		catch (Twig_Error_Syntax $e) 
		{
			$this->_show_error($e->getMessage());	
		}		
		$this->_ci->benchmark->mark('AttireRender_end');
	}
}

/* End of file Twig.php */
/* Location: ./application/libraries/Twig.php */
