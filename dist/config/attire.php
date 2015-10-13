<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Theme Path
|--------------------------------------------------------------------------
|
| Path to your attire themes folder.
| Typically, it will be within your application path.
| Also, writing permission is required.
|
*/
$config['theme_path'] = APPPATH.'themes/';

/*
|--------------------------------------------------------------------------
| Assets Path
|--------------------------------------------------------------------------
|
| Path to your assets folder.
| Typically, it will be outside your application path.
| Also, writing permission is required.
|
*/
$config['assets_path'] = FCPATH.'assets/';

/*
|--------------------------------------------------------------------------
| Twig-Codeigniter Functions
|--------------------------------------------------------------------------
|
| Allows to add Codeigniter functionality in Twig Environment that come 
| from other libraries or helpers. Example:
|
| $config['twig_ci_functions'] = array(
|	'base_url' => function($path = ""){ return base_url($path); },
| );
|
| Finally load the library or helper bafore the render method. 
| Call the functions in Twig environment:
|		
|	{{base_url('foo_fighters')}}
|
*/
$config['twig_ci_functions'] = array();

/*
|--------------------------------------------------------------------------
| Twig Global Vars 
|--------------------------------------------------------------------------
|
| Global variables can be registered in Twig extension. Same as declare a 
| function:
|
| $config['global_vars'] = array(
| 	'text' => new Text(),
| );
|
*/
$config['global_vars'] = array();
