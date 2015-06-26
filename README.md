# Codeigniter Twig #
---

[![Latest Stable Version](https://poser.pugx.org/dsv/ci-twig/v/stable)](https://packagist.org/packages/dsv/ci-twig) [![Total Downloads](https://poser.pugx.org/dsv/ci-twig/downloads)](https://packagist.org/packages/dsv/ci-twig) [![Latest Unstable Version](https://poser.pugx.org/dsv/ci-twig/v/unstable)](https://packagist.org/packages/dsv/ci-twig) [![License](https://poser.pugx.org/dsv/ci-twig/license)](https://packagist.org/packages/dsv/ci-twig)


CI-Twig it's an implementation of Twig/Assetic template engine for CodeIgniter 3.0. It supports theme instances, layouts, functions, filters and lexers for regular apps and also for apps that use HMVC. It's gonna make your life easier for developing and maintaining your CodeIgniter applications where structured templates are necessary.

With CI-Twig you can separately set the theme, layout and even the assets for each page. Also this does not replace CodeIgniter's default views, so you can still load views as such as: $this->load->view().

## Requirements ##

* PHP 5.2.4+
* CodeIgniter 3.x 

**Note**: Codeigniter 2.x is not supported.

# How to install #
---

## With composer:

```
composer require "dsv/ci-twig":"^1.1"
```

**Note**: Remember to config the composer autoload file inside your Codeigniter `application/config/config.php` file.

# How to use it
---

## 1. Set up directory structure

**Create a directory structure:**

```
+-APPPATH/
| +-theme/
+-FCPATH 
| +-index.php
| +-assets/
| | +-css/
| | +-js/
```
**Notes**: 

* `APPPATH` is Codeigniter's principal directory, where all your controllers, models and views are placed.
* `FCPATH` is Codeigniter's secured installation directory, where your ```index.php``` file is placed (normally outside the ```application``` directory).
* `CI-Twig` uses `Assetics` for manage the assets used in every theme, so you are gonna need to set the `assets` directory with writable permissions.
* You don't need to worry if you don't understand what's going on here, CI-Twig will show an error for every installation path and also how to fix it.

**Copy the theme example structure.**

By default CI-Twig uses a `Bootstrap` instance. Copy the `dist/bootstrap` directory to `theme`.

```
| +-application/
| | +-theme/
| | | +-bootstrap/
```
## 1. Load the library ##

Move to your controller and load the library.

```php
$this->load->library('ci-twig/twig'); 
``` 

## 3. Set a theme and layout 

Bootstrap theme includes a 'container' layout structure. 

```php
$this->twig->set_theme('bootstrap');
$this->twig->add_layout('container');
```

**Note**: Chaining method also supported.

```php
$this->twig->set_theme('bootstrap')->add_layout('container');
```

## 4. Display the theme

```php
$this->twig->render();
```

A full example using `CI-Twig` in the Welcome Controller:

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
		$this->twig->render();
	}
}
```

## 5. What's next? ##

In the example above we only displayed the default template and layout. You can add views to this layout using the command: 
```php
$this->twig->add_view($view,$params)
```
It's exactly like the Codeigniter's method: 

```php
$this->load->view($view,$params)
```

You don't need to create the same template structure every time a method is called (header, sidebar, breadcrumbs, container, footer, etc), only add the view's you're gonna need in a controller's method. 

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
		$this->twig->add_view('welcome_message')->render();	
	}
}
```

**Note**: before you can add a view inside the 'index' method you're gonna need a directory structure inside your `views` folder:

```
+-views/
| +-welcome/
| | +-index/
| | | +-welcome_message.php
```

Or call it inside the constructor.

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
    }

	public function index()
	{	
		$this->twig->add_view('foo')->render();	
	}
	
	public function other()
	{
	    # because foo bar is too mainstream
	    $this->twig->add_view('fighters')->render();
	}
}

```

Here's the folder structure for this example.

```
+-views/
| +-welcome/
| | +-index/
| | | +-foo.php
| | +-other
| | | +-fighters.php
```

And there you go, you can add many views as you want before the render method call.

# Create a new Theme 
---

Obviously, you can create as many layouts and theme you want, follow me in every step for doing this. 

## 1. Create the directory

Create a new directory structure inside the `theme` folder:

```
+-theme/
| +-new_theme/
| | +-assets (all your theme asset files needed)
| | | +- css/* 
| | | +- js/*
| | +-layout
| | | +-new_layout.twig
| | +- theme.twig
```

## 2. Create a theme file

You are gonna need to create a new `theme.twig` file structure, this is the default template used in every `CI-Twig` theme instance:

```
<!DOCTYPE html>
<html>
	<head>
		{% block head %}
			<title>{% block title %}{% endblock %} - {{system_fullname|title}}</title>
		{% endblock %}
		{% block stylesheets %}
			{% stylesheets 'css/*' '@global_css' '@module_css' filter='cssrewrite' %}
				<link href="{{ base_url('assets/' ~ asset_url) }}" type="text/css" rel="stylesheet" />
			{% endstylesheets %}		
		{% endblock %}
	</head>
	<body class="{{skin_color}}">
		{% block content %}{% endblock %}
		<div id="footer">
			{% block footer %}{% endblock %}
		</div>
		{% block javascripts %}
			{% javascripts 'js/*' '@global_js' '@module_js' %}
				<script src="{{ base_url('assets/' ~ asset_url) }}"></script>
			{% endjavascripts %}
		{% endblock %}
	</body>
</html>
```

Use it as a basic template and create something unique.

## 3. Create the layout

Same as `theme.twig`, the `layouts/new_layout.twig` default template: 

```php
{% extends "theme.twig" %}
{% block title %}{{'new_layout'|capitalize}}{% endblock %}

{% block content %}
	{% for view,params in views %}
		{% include view with params %}
	{% endfor %}
{% endblock %}
```

Anything can be a layout, check the [twig extends docs](http://twig.sensiolabs.org/doc/tags/extends.html).

# 4. Load theme layout and views

Set the new theme and structure, add the views and load it before sending the output to the browser.

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('new_theme')->add_layout('new_layout');
		$this->twig->add_view('welcome_message')->render();	
	}
}
```

**Notes**: 

* Notice that you only need to specify the name of the template (without the extension `*.twig`).

There is much more cool stuff that you should check out by visiting the [docs (anytime soon)](#).

## CHANGELOG ##
---

### 1.1.6

* Fix the hmvc directory paths bugs

### 1.1.3

* Update installation paths (even if you are in a safe installation)

### 1.1.0 ###

* Document all the principal class (finally)
* Fix some bugs with CI global paths

### 1.0.7 ###

* Include global assets
* Catch Assetic RunTimeExceptions in Writter

### 1.0.4 ###

* Fix bugs in HMVC Mode add_path
* Catch add_path errors
* Autoload url codeigniter helper (used as default)

## Other Twig Implementations for Codeigniter ##

[https://github.com/kenjis/codeigniter-ss-twig](https://github.com/kenjis/codeigniter-ss-twig)