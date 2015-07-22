# CI-Twig

An implementation of Twig template engine and Assetic asset management framework for CodeIgniter 3.0.

---

[![Latest Stable Version](https://poser.pugx.org/dsv/ci-twig/v/stable)](https://packagist.org/packages/dsv/ci-twig) [![Total Downloads](https://poser.pugx.org/dsv/ci-twig/downloads)](https://packagist.org/packages/dsv/ci-twig) [![Latest Unstable Version](https://poser.pugx.org/dsv/ci-twig/v/unstable)](https://packagist.org/packages/dsv/ci-twig) [![License](https://poser.pugx.org/dsv/ci-twig/license)](https://packagist.org/packages/dsv/ci-twig)

##Overview

CI-Twig library supports template inheritance using **Twig** template engine and **Assetic** as an asset management framework in **CodeIgniter 3.0**. This integration it's gonna make your life easier for developing and maintaining structured templates, layouts and even the assets for each section in your application.

---

## Requirements ##

* PHP 5.2.4+
* CodeIgniter 3.x 

**Note**: Codeigniter 2.x it's not supported.

---

##Installation

With Composer:

	composer require "dsv/ci-twig":"^1.1"

CI-Twig is a regular library, so composer should be install it in the **libraries** directory inside your appplication. 

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

**CI-Twig** uses **Assetics** for manage the assets used in every template, so you are gonna need to set the **assets** directory with writable permissions.

###Theme example structure

By default **CI-Twig** uses a **Bootstrap** as a template example. If you like to see this example copy the **dist/bootstrap** directory inside of theme directory.

```
+-APPPATH/
| | +-theme/
| | | +-bootstrap/
```

###Before we continue

Let's take a moment to review the initial project that we created and also the library that is already included.

![Screenshot](img/take_a_look.png)

Now you're ready to start working with CI-Twig.

---

##Getting started

Getting started is like load any CI's Library:

```php
$this->load->library('ci-twig/twig'); 
``` 

CI-Twig mantains all your views in order, so in every theme structure needs to be a layout and a views directory.

Bootstrap theme includes a **container** layout structure. 

```php
$this->twig->set_theme('bootstrap');
$this->twig->set_layout('container');
```

**Note:** chaining method also supported.

```php
$this->twig->set_theme('bootstrap')->set_layout('container');
```

<!-- Also create a directory inside the **views** directory with the name of the controller where it will be used. -->
And the last thing that wee need to do is display the theme.

```php
$this->twig->render();
```

###Example

An example using **CI-Twig** in the Welcome Controller:

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->set_layout('container');
		$this->twig->render();
	}
}
```

Let's take a look in the browser:

![Screenshot](img/hello_world.png)

This is the current output of the render method. Now you can use the bootstrap responsive framework in your application!!

---

##Adding views

So far we've only displayed the default template and layout. You can add views to this layout using the **add_view** command.

```php
$this->twig->add_view($view,$params)
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
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
		$this->twig->add_view('foo');
		$this->twig->render();	
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
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
		$this->twig->add_view('foo');
		$this->twig->add_view('fighters');
		$this->twig->add_view('dave_grohl');
		$this->twig->render();	
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
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
	}
	
	public function index()
	{	
		$this->twig->add_view('foo')->render();	
	}

	public function other()
	{
		$this->twig->add_view('fighters')->render();		
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
		$this->load->library('ci-twig/twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
    }

	public function index()
	{
	    $this->twig->add_path(APPPATH.'views','some');
		$this->twig->add_view('@some/foo')->render();	
	}
}
```

Here's the directory structure for this example.

```
+-application
| +-views/
| | +-foo.php
```

And there you go, adding views to CI-Twig is easy as the CodeIgniter natural method's.

---

##Getting help

To get help with CI-Twig, please use the discussion group or GitLab issues.

<!---
##Related Twig Implementations

[https://github.com/kenjis/codeigniter-ss-twig](https://github.com/kenjis/codeigniter-ss-twig)
-->