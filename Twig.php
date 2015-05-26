<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Assetic\AssetManager;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Asset\AssetCollection;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\Worker\CacheBustingWorker;
use Assetic\Extension\Twig\AsseticExtension;

use Assetic\AssetWriter;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Assetic\Factory\LazyAssetManager;

use Assetic\FilterManager;
use Assetic\Filter\CssRewriteFilter;
use Assetic\Filter\LessFilter;
use Assetic\Filter\Yui;

use \RuntimeException;

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
 * CodeIgniter Twig Class
 *
 * Templating with this class is done by layering the standard CI view system and extending 
 * it to asset management with Assetic. The basic idea is that for every single CI view 
 * there are individual CSS, Javascript and View files that correlate to it and this 
 * structure is conected with the Twig Class.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries 
 * @category	Libraries
 * @author		David Sosa Valdes
 * @link		https://gitlab.com/david-sosa-valdes/ci-twig
 * @copyright   Copyright (c) 2014, David Sosa Valdes.
 * @version 	1.1.0
 *
 */

class Twig
{
	/**
	 * Master layout template name
	 * @var string
	 */
	protected $_default_template = 'theme';

	/**
	 * Directory structure 
	 * @var array
	 */
	protected $_directories = array('theme','assets','modules');

	/**
	 * HMVC environment class (modular,default)
	 * @var object
	 */
	protected $_hmvc;

	/**
	 * Twig_Environment auto reload attr 
	 * @var boolean
	 */
	protected $_auto_reload = FALSE;

	/**
	 * Codeigniter directives used as global functions.
	 * @var mixed
	 */
	protected $_directives;

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
	 * The syntax set used for personalize Twig Lexer
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
  	 * Loads the HMVC environment, CI Instance, Filesystem paths and directives.
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
        	$this->_set($config);
        	$this->_set_hmvc_environment();
        	$this->_set_installation_paths();	
        } 
        catch (Exception $e) 
        {
        	$this->_show_error($e->getMessage());
        }

        $this->_directives = array(
			'CI::router_fetch_class' => function(){
				return $this->_ci->router->fetch_class();
			},
			'CI::config_item' => function($item = ""){
				return config_item($item);
			}
        );
	}

	/**
	 * Initialize CI Environment
	 * 
	 * Create an modular environment if HMVC Class is loaded else default CI method.
	 * @return void
	 */
	private function _set_hmvc_environment()
	{
        $this->_hmvc = New StdClass();
        $this->_hmvc->method = $this->_ci->router->fetch_method();
        
        if ($modules_locations = config_item('modules_path')) 
        {
        	if (($this->_hmvc->module = $this->_ci->router->fetch_class()) !== NULL) 
        	{
        		$this->_hmvc->path = $modules_locations.$this->_hmvc->module;
        		return;
        	}
        }
        $this->_hmvc->module = NULL;
        $this->_hmvc->controller = $this->_ci->router->fetch_class();
        $this->_hmvc->path = APPPATH;
	}

	/**
	 * Initialize absolute filesystem paths
	 *
	 * Depends of the HMVC Environment.
	 * 
	 * @return void
	 */
	private function _set_installation_paths()
	{
		foreach ($this->_directories as $key => $directory) 
		{
			$directory = rtrim($directory,'/');			
			if ($directory == 'modules') 
			{
				$path = config_item('modules_path');
			}
			else
			{
				$path = FCPATH . $directory . '/';
			}
			if (! file_exists($path)) 
			{
				throw new Exception("Directory {$path} currently not exist.");
			}
			else 
			{
				$this->_paths[$directory] = $path;
			}
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
		try {
			if (! preg_match('/^.*\.(twig|php.twig|html|html.twig)$/i', $new_extension)) {
				throw new Exception("Extension is not valid, use (.twig|.php.twig|.html|.html.twig)");
			}
		} catch (Exception $e) {
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
	private function _show_error($error = "", $title = 'Twig Error')
	{
		return show_error($error,404,$title);
	}

	/**
	 * Set HMVC view paths
	 *
	 * Loading all the CI view files Used in Twig Filesystem with especial notation
	 *
	 * @return void 
	 */
	private function _add_module_view_paths()
	{
		if ($this->_hmvc->module !== NULL) 
		{
			$path = "{$this->_paths['modules']}{$this->_hmvc->module}/views";
			$this->add_path($path,'module', FALSE, FALSE);
		}
		else 
		{
			$path = VIEWPATH . $this->_hmvc->controller;
			$this->add_path($path,'module',FALSE,FALSE);
		}
        return $this;
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
		switch ($type) {
			case 'filesystem':
				$directory = $this->_paths['theme'].$value;
				$this->_loader = new Twig_Loader_Filesystem($directory);
				
				$params['debug']       = $this->_debug;

				# Currently not working
				# 
				# $params["cache"]       = $this->_paths["cache"];
				# $params['auto_reload'] = $this->_auto_reload;
				
				$this->_environment = new Twig_Environment($this->_loader,$params);
				break;
			case 'array':
				if (is_array($value)) 
				{
					$this->_loader = new Twig_Loader_Array($value);
					$this->_environment = new Twig_Environment($this->_loader);
				}
				break;
			default:
				$this->_loader = new Twig_Loader_String();
				$this->_environment = new Twig_Environment($this->_loader);
				break;
		}
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
		try {
			if (!is_a($this->_environment, 'Twig_Environment')) 
			{
				throw new Exception("Twig_Environment is not set correctly.");
			}
			foreach ($lexer as $tag => $value) 
			{
				if (! in_array($tag, $this->_available_lexer)) 
				{
					throw new Exception("Lexer tag is not acceptable.");
				}
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}		
		$new_lexer = new Twig_Lexer($this->_environment, $lexer);
		$this->_environment->setLexer($new_lexer);	
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
		try {
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
	 * @param string  $space_name Space name without the '@'
	 * @param boolean $prepend    Prepend method mode
	 * @return void 
	 */
	public function add_path($path = "", $space_name = "", $prepend = FALSE, $need_abs_path = TRUE)
	{
		$path = rtrim($path, '/').'/';
		$absolute_path = ($need_abs_path)? FCPATH."../{$path}": $path;
		
		try {
			if (!is_a($this->_loader, 'Twig_Loader_Filesystem')) 
			{
				throw new Exception("Loader is not set correctly.");
			}
			if (!file_exists($path)) 
			{
				throw new Exception("{$path} not currently exist.");
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}

		if ($prepend === TRUE) 
		{
			if (strlen($space_name) >= 1) 
			{
				$this->_loader->prependPath($absolute_path,$space_name);
			}
			else 
			{
				$this->_loader->prependPath($absolute_path);
			}
		}
		else
		{
			if (strlen($space_name) >= 1) 
			{
				$this->_loader->addPath($absolute_path,$space_name);
			}
			else 
			{
				$this->_loader->addPath($absolute_path);
			}
		}
		return $this;
	}

	/**
	 * Load Twig Template
	 * 
	 * Loads a template by name
	 * 
	 * @param  string $path Filename
	 * @return mixed       
	 */
	public function load_template($path = "")
	{
		try {
			if (! is_a($this->_loader, 'Twig_Loader_Filesystem')) 
			{
				throw new Exception("Need the Twig_Loader_Filesystem before loading.");
			}	
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}
		$template_path = "{$path}{$this->_extension}";
		return $this->_environment->loadTemplate($template_path);
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
	 * Set Ci-Twig Theme
	 *
	 * Set the absolute path in the Twig loader filesystem method.
	 * 
	 * @param string $name Theme name
	 */
	public function set_theme($name = "")
	{
		$name = rtrim($name.'/');
		$this->_theme = $name;
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
		try {
			if (in_array($view, $this->_views)) {
				throw new Exception("Not possible to add a view twice.");
			}
			elseif (!is_string($view)) {
				throw new Exception("Need the view path in string format.");
			}
			elseif (empty($this->_childs)) {
				throw new Exception("There is no layout, run first add_layout.");
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}
		$path = "@module/{$this->_hmvc->method}/{$view}{$extension}";
		$this->_views[$path] = (array) $params;
		return $this;
	}

	/**
	 * Set Theme global vars
	 *
	 * For each global var it evals if is a CI-Twig directive, else set global param.
	 *
	 * Note: All global vars are declared in <theme>/config/config.php file and autoload
	 * 		 before the render method.
	 * @return void
	 */
	private function _set_global_vars()
	{
		try {
			if ($this->_theme === NULL) 
			{
				throw new Exception("Cannot set global vars when theme is not set.");
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage);
		}
		$config_file = "{$this->_paths['theme']}{$this->_theme}/config/config.php";

		if (file_exists($config_file)) 
		{
			require_once($config_file);

			if (isset($config)) 
			{
				foreach ($config as $key => $directive) 
				{
					if (count($directive) == 1) 
					{
						$directive[] = NULL;
					}
					
					list($function,$params) = $directive;
					
					if (isset($this->_directives[$function]))
					{
						$function = $this->_directives[$function];
						$this->set_param($key,$function($params));
					}
					elseif ($function === NULL) 
					{
						$this->set_param($key,$params);
					}
				}				
			}
		}
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
			'form_open' => function($path, $params = array()){ 
				return form_open($path, $params); 
			},
			'form_open_multipart' => function($path, $params = array()){
				return form_open_multipart($path,$params);
			},
			'form_close' => function(){
				return form_close();
			}
		);
		foreach ($functions as $name => $function) 
		{
			if (function_exists($name)) 
			{
				$this->add_function($name,$function);
			}
		}
	}

	/**
	 * Render the Twig template with some variables
	 *
	 * If Twig Loader method is string, we can render view as string template and
	 * set the params, else there is no need to declare params in this method.
	 * 
	 * @param  string $view   
	 * @param  array  $params 
	 * @return void         
	 */
	public function render($view = "", $params = array())
	{	
		$this->_set_global_vars();
		$this->_set_ci_functions();
		$this->_add_module_view_paths();
		
		# Twig environment (master of puppets)
		$twig = $this->_environment;

		try {
			if (! is_a($twig, 'Twig_Environment')) 
			{
				throw new Exception("Twig_Environment is not loaded properly.");
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}		
		
		# Secure stuff
		$escaper = new Twig_Extension_Escaper('html');
		$twig->addExtension($escaper);		

		# Declare asset manager and add global paths
		$am = new AssetManager();
		$absolute_path = rtrim("{$this->_paths['modules']}{$this->_hmvc->module}",'/');
		
		$globals = array(
			'module_js'  => 'assets/js',
			'module_css' => 'assets/css',
			'global_css' => 'assets/global/css',
			'global_js'  => 'assets/global/js'
		);
		# Is a global or module path, maybe we can solve it
		foreach ($globals as $global => $global_path) 
		{
			if (strpos($global_path, 'global') !== FALSE) 
			{
				$path = "{$absolute_path}/{$global_path}/*";
				$am->set($global, new GlobAsset($path));			
			}
			elseif ($this->_hmvc->module === NULL) 
			{
				$path = "{$absolute_path}/{$global_path}/{$this->_hmvc->controller}/{$this->_hmvc->method}/*";
				$am->set($global, new GlobAsset($path));
			}
			else 
			{
				$path = "{$absolute_path}/{$global_path}/{$this->_hmvc->method}/*";
				$am->set($global, new GlobAsset($path));	
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

		$absolute_path = rtrim($this->_paths['assets'],'/');
		$factory->setDefaultOutput($absolute_path);
		$twig->addExtension(new AsseticExtension($factory));

		$am = new LazyAssetManager($factory);
		$am->setLoader('twig', new TwigFormulaLoader($twig));

		$resource = new TwigResource($this->_loader, $this->_default_template.$this->_extension);
		$am->addResource($resource, 'twig');

		# Write all assets files in the output directory in one or more files
		try {
			$writer = new AssetWriter($absolute_path);
			$writer->writeManagerAssets($am);
		} catch (\RuntimeException $e) {
			$this->_show_error($e->getMessage());
		}	
		
		# Render all childs
		if (! empty($this->_childs)) 
		{
			foreach ($this->_childs as $child => $params) 
			{
				$this->_params['views'] = $this->_views;
				try 
				{
					echo $twig->render($child, array_merge($params,$this->_params));
				} 
				catch (Twig_Error_Syntax $e) 
				{
					$this->_show_error($e->getMessage());	
				}
			}
			# Stop using childs when another render is call
			$this->_childs = array();
		}
		# Else render params as string format (if declared)
		elseif (strlen($view) <= 1) 
		{
			echo $twig->render($this->_default_template.$this->_extension, $params);	
		}
	}
}

/* End of file Twig.php */
/* Location: ./application/libraries/Twig.php */