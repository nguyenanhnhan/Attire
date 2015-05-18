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

class Twig
{
	/**
	 * [$_default_template description]
	 * @var string
	 */
	protected $_default_template = 'theme';
	/**
	 * [$_directories description]
	 * @var array
	 */
	protected $_directories = array('theme','assets','modules');
	/**
	 * [$_modules_path description]
	 * @var [type]
	 */
	protected $_hmvc;
	/**
	 * [$_auto_reload description]
	 * @var boolean
	 */
	protected $_auto_reload = FALSE;
	/**
	 * [$_directives description]
	 * @var [type]
	 */
	protected $_directives;
	/**
	 * [$_theme description]
	 * @var [type]
	 */
	protected $_theme;
	/**
	 * [$_module description]
	 * @var [type]
	 */
	protected $_module;

	/**
	 * [$_views description]
	 * @var array
	 */
	protected $_views = array();

	/**
	 * [$_available_lexer description]
	 * @var array
	 */
	protected $_available_lexer = array('tag_comment','tag_block','tag_variable');
	/**
	 * [$_params description]
	 * @var array
	 */
	protected $_params = array();

	/**
	 * [$_extension description]
	 * @var string
	 */
	protected $_extension = '.twig';

	/**
	 * [$_childs description]
	 * @var array
	 */
	protected $_childs = array();
	/**
	 * [$_paths description]
	 * @var array
	 */
	protected $_paths = array();

	/**
	 * [$_debug description]
	 * @var boolean
	 */
	protected $_debug = FALSE;

	/**
	 * [$_environment description]
	 * @var [type]
	 */
	protected $_environment;

	/**
	 * [$_loader description]
	 * @var [type]
	 */
	protected $_loader;

	/**
	 * [$_ci description]
	 * @var [type]
	 */
  	protected $_ci;

  	/**
  	 * [__construct description]
  	 * @param array $config [description]
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
	 * [_set_hmvc_environment description]
	 */
	private function _set_hmvc_environment()
	{
        $this->_hmvc = New StdClass();
        $this->_hmvc->method = $this->_ci->router->fetch_method();
        
        if ($modules_locations = config_item('modules_locations')) 
        {
        	list($this->_hmvc->path) = array_keys($modules_locations);
        	if (! method_exists($this->_ci->router, 'fetch_module')) 
        	{
        		throw new Exception("HMVC Module is not installed.");
        	}
        	$this->_hmvc->module = $this->_ci->router->fetch_module();
        }
        else
        {
        	$this->_hmvc->module = NULL;
        	$this->_hmvc->controller = $this->_ci->router->fetch_class();
        	$this->_hmvc->path = APPPATH;
        }
	}

	/**
	 * [_set_installation_paths description]
	 */
	private function _set_installation_paths()
	{
		foreach ($this->_directories as $key => $directory) 
		{
			$directory = rtrim($directory,'/');
			
			if ($this->_hmvc->module === NULL) 
			{
				if ($directory == 'modules') 
				{
					$path = FCPATH;
				}
				else
				{
					$path = FCPATH . $directory . '/';
				}
			}
			else
			{
				$path = FCPATH . $directory . '/';
			}
			
			if (! file_exists($path)) 
			{
				throw new Exception("{$path} currently not exist.");
			}
			else 
			{
				$this->_paths[$directory] = $path;
			}
		}
	}	

	public function set_extension($new_extension)
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
	 * [set_envoirment description]
	 * @param string $name [description]
	 */
	private function _set($params = array())
	{
		foreach ($params as $key => $val)
		{
			$this->{'_'.$key} = $val;
		}	
	}	

	/**
	 * [_show_error description]
	 * @param  string $error [description]
	 * @param  string $title [description]
	 * @return [type]        [description]
	 */
	private function _show_error($error = "", $title = 'Twig Error')
	{
		return show_error($error,404,$title);
	}

	/**
	 * [_add_module_view_paths description]
	 */
	private function _add_module_view_paths()
	{
		if ($this->_module !== NULL) 
		{
			$path = "{$this->_paths['modules']}/{$this->_module}/views";
			$this->add_path($path,'module', FALSE, FALSE);
			// $module_view_paths = $this->_ci->load->get_module_paths($this->_module,'views');
   //      	foreach ($module_view_paths as $key => $path) 
   //      	{
   //      		$this->add_path($path,'module');	
   //      	}
		}
		else 
		{
			$path = 'application/views/'. $this->_hmvc->controller;
			$this->add_path($path,'module');
		}
        return $this;
	}

	/**
	 * [set_loader description]
	 * @param string  $type   [description]
	 * @param [type]  $value  [description]
	 * @param boolean $option [description]
	 */
	public function set_loader($value, $type = "",$option = FALSE)
	{
		$params = array();
		switch ($type) {
			case 'filesystem':
				$directory = $this->_paths['theme'].$value;
				$this->_loader = new Twig_Loader_Filesystem($directory);
				
				$params['debug']       = $this->_debug;
				#$params["cache"]       = $this->_paths["cache"];
				#$params['auto_reload'] = $this->_auto_reload;
				
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
	 * [set_lexer description]
	 * @param array $lexer [description]
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
	 * [add_function description]
	 * @param string $name     [description]
	 * @param [type] $function [description]
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
	 * [add_path description]
	 * @param string  $path       [description]
	 * @param string  $space_name [description]
	 * @param boolean $prepend    [description]
	 */
	public function add_path($path = "", $space_name = "", $prepend = FALSE, $need_abs_path = TRUE)
	{
		$path = rtrim($path, '/').'/';
		$absolute_path = ($need_abs_path)? FCPATH."{$path}": $path;
		try {
			if (!is_a($this->_loader, 'Twig_Loader_Filesystem')) 
			{
				throw new Exception("Loader is not set correctly.");
			}
			if (!file_exists($path)) 
			{
				throw new Exception("{$path} is not currently exist.");
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
	 * [load_template description]
	 * @param  string $path [description]
	 * @return [type]       [description]
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
	 * [set_param description]
	 * @param string $name  [description]
	 * @param [type] $value [description]
	 */
	public function set_param($name = "",$value = NULL)
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
	 * [set_theme description]
	 * @param string $type          [description]
	 */
	public function set_theme($name = "")
	{
		$name = rtrim($name.'/');
		$this->_theme = $name;
		$this->set_loader($name,'filesystem');
		return $this;
	}

	/**
	 * [add_child description]
	 * @param string $type       [description]
	 * @param array  $params     [description]
	 * @param string $child_path [description]
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
	 * [add_layout description]
	 * @param string $type   [description]
	 * @param array  $params [description]
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
	 * [add_view description]
	 * @param string $view      [description]
	 * @param array  $params    [description]
	 * @param string $extension [description]
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
	 * [_set_global_vars description]
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
	 * [_set_ci_functions description]
	 */
	private function _set_ci_functions()
	{
		if (function_exists('base_url')) 
		{
			$this->add_function('base_url', function($path){
				return base_url($path);
			});
		}
		if (function_exists('site_url')) {
			$this->add_function('site_url',function($path){
				return site_url();
			});
		}
		if (function_exists('form_open')) 
		{
			$this->add_function('form_open',function($url, $params = array()){
				return form_open($url, $params);
			});
		}
		if (function_exists('form_close')) 
		{
			$this->add_function('form_close',function(){
				return form_close();
			});
		}
	}

	/**
	 * [render description]
	 * @param  string $view   [description]
	 * @param  array  $params [description]
	 * @return [type]         [description]
	 */
	public function render($view = "", $params = array())
	{
		$this->_ci->load->helper('url');
		
		$this->_set_global_vars();
		$this->_set_ci_functions();
		$this->_add_module_view_paths();
		
		$twig = $this->_environment;

		try {
			if (! is_a($twig, 'Twig_Environment')) 
			{
				throw new Exception("Twig_Environment is not loaded properly.");
			}
		} catch (Exception $e) {
			$this->_show_error($e->getMessage());
		}		
		
		$escaper = new Twig_Extension_Escaper('html');
		$twig->addExtension($escaper);		

		$am = new AssetManager();

		$absolute_path = rtrim("{$this->_paths['modules']}{$this->_module}",'/');
		$globals = array(
			'module_js'  => 'assets/js',
			'module_css' => 'assets/css',
			'module_img' => 'assets/img',
		);

		foreach ($globals as $global => $global_path) 
		{
			if ($this->_hmvc->module === NULL) 
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

		$fm = new FilterManager();
		$fm->set('cssrewrite', new CssRewriteFilter());
		
		$absolute_path = rtrim("{$this->_paths["theme"]}{$this->_theme}",'/').'/assets';
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

		$writer = new AssetWriter($absolute_path);
		$writer->writeManagerAssets($am);	
		
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
			# Stop using childs when another render is set.
			$this->_childs = array();
		}
		elseif (strlen($view) <= 1) 
		{
			echo $twig->render($this->_default_template.$this->_extension, $params);	
		}
	}

}

/* End of file Twig.php */
/* Location: ./application/libraries/Twig.php */
