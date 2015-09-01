<div style="text-align: center">
	<img src="img/attire_logo.png" alt="alt text" width="120px" height="120px">
</div>
# Attire

An implementation of Twig template engine combined with Assetic asset management framework for CodeIgniter 3.0.

---

[![Latest Stable Version](https://poser.pugx.org/dsv/attire/v/stable)](https://packagist.org/packages/dsv/attire) [![Total Downloads](https://poser.pugx.org/dsv/attire/downloads)](https://packagist.org/packages/dsv/attire) [![Latest Unstable Version](https://poser.pugx.org/dsv/attire/v/unstable)](https://packagist.org/packages/dsv/attire) [![License](https://poser.pugx.org/dsv/attire/license)](https://packagist.org/packages/dsv/attire)

##Overview

Attire library supports template inheritance using **Twig** template engine and **Assetic** as an asset management framework in **CodeIgniter 3.0**. This integration is used for developing and maintaining structured templates, layouts and the assets for each section/view/content in your application.

---

## Requirements ##

* PHP 5.2.4+
* CodeIgniter 3.x 

**Note**: Codeigniter 2.x it's not supported.

---

##Installation

With Composer:

	composer require "dsv/attire":"^1.2"

Attire is a regular library, so composer should be install it in the **libraries** directory inside your appplication. 

---

##Setting up the environment

Before we start, let me remind you to check the configuration of **composer_autoload** inside your **application/config/config.php** file.

Now we need to set the environment where all your templates are stored properly.

###Directory structure

First create this directory structure inside your CodeIgniter application:

```
+-APPPATH/
| +-theme/
+-FCPATH 
| +-assets/
| | +-css/
| | +-js/
```

* **APPPATH** is Codeigniter's principal directory, where all your controllers, models and views are placed.
* **FCPATH** is Codeigniter's secured installation directory, where your **index.php** file is placed (normally outside the application directory).

###Assets permissions

**Assetics** manage the assets used in every template, so you are gonna need to set the **assets** directory with writable permissions.

###Theme example structure

By default **Attire** uses a **Bootstrap** as a template example. If you like to see this example copy the **dist/bootstrap** directory inside of theme directory.

```
+-APPPATH/
| | +-theme/
| | | +-bootstrap/
```

###Config File

**Attire** use one config file to retrieve configuration preferences. Copy the **dist/config/attire.php** file inside your config directory:

```
+-APPPATH/
| | +-config/
| | | +-attire.php
```

These preferences come with the default configuration.

###Before we continue

Let's take a moment to review the initial project that we created and also the library that is already included.

![Screenshot](img/take_a_look.png)

Now you're ready to start working with Attire.

---

##Getting started

Getting started is like load any CI's Library:

```php
$this->load->library('attire/attire'); 
``` 

Attire mantains all your views in order, so in every theme structure needs to be a layout and a views directory.

Bootstrap theme includes a **container** layout structure. 

```php
$this->attire->set_theme('bootstrap');
$this->attire->set_layout('container');
```

**Note:** chaining method also supported.

```php
$this->attire->set_theme('bootstrap')->set_layout('container');
```

<!-- Also create a directory inside the **views** directory with the name of the controller where it will be used. -->
And the last thing that wee need to do is display the theme.

```php
$this->attire->render();
```

###Example

An example using **Attire** in the Welcome Controller:

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('attire/attire');
		$this->attire->set_theme('bootstrap')->set_layout('container');
		$this->attire->render();
	}
}
```

Let's take a look in the browser:

![Screenshot](img/hello_world.png)

This is the current output of the render method. Now you can use the **Bootstrap** responsive framework in your application.

---

##Adding views

So far we've only displayed the default template and layout. You can add views to this layout using the **add_view** command.

```php
$this->attire->add_view($view,$params)
```
Where ```$view``` is the view file name and ```$params``` is an array of variables used inside the view interface.

It's exactly like the Codeigniter's method: 

```php
$this->load->view($view,$params)
``` 


###Example

Using the `Welcome` controler as an example and the `index` as method, let's create a directory called **index** inside the **welcome** view directory:

```
+-APPPATH/
| | +-views/
| | | +-welcome/
| | | | +-index/
```

Then create a view inside the **index** directory called **foo.php**:

```html
<!-- views/welcome/index/foo.php -->
<h2>Header</h2>
<p>paragraph<p>

```

Next add the view without specifing the directory app and the extension:

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('attire/attire');
		$this->attire->set_theme('bootstrap')->add_layout('container');
		$this->attire->add_view('welcome/index/foo');
		$this->attire->render();	
	}
}
```
And this is the output:

![Screenshot](img/add_view.png)

And that's how you add views inside your controller's method. Also you can add as many views as you want using the same function multiple times. 

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('attire/attire');
		$this->attire->set_theme('bootstrap')
					 ->set_layout('container')
					 ->add_view('welcome/index/foo')
					 ->add_view('welcome/index/fighters')
					 ->render();	
	}
}
```
---

## Theming our application

You don't need to create the same template structure every time a method is called (header, sidebar, breadcrumbs, container, footer, etc), only add the view's you're gonna need in a controller's method. 

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function __construct()
	{
		$this->load->library('attire/attire');
		$this->attire->set_theme('bootstrap')->add_layout('container');
	}
	
	public function index()
	{	
		$this->attire->add_view('welcome/index/foo')->render();	
	}

	public function other()
	{
		$this->attire->add_view('welcome/other/fighters')->render();		
	}
}
```


Here's the directory structure for this example.

```
+-application
| +-views/
| | +-welcome/
| | | +-index/
| | | | +-foo.php
| | | +-other
| | | | +-fighters.php
```

Or you can specify your view path and add views in Twig style:

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
		$this->load->library('attire/attire');
		$this->attire->set_theme('bootstrap')->add_layout('container');
    }

	public function index()
	{
	    $this->attire->add_path('<outside-viewpath>','some');
		$this->attire->add_view('@some/foo')->render();	
	}
}
```

And there you go, adding views to Attire is easy as the CodeIgniter natural method's.

---

##Getting help

To get help with Attire, please use the discussion group or GitLab issues.

<!---
##Related Twig Implementations

[https://github.com/kenjis/codeigniter-ss-twig](https://github.com/kenjis/codeigniter-ss-twig)
-->