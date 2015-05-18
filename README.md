# Codeigniter Twig #
---

CI-Twig it's a simple implementation of Twig/Assetic template engine for CodeIgniter 3.0. It supports themes, layouts, templates for regular apps and also for apps that use HMVC. It's gonna make your life easier for developing and maintaining your CodeIgniter applications where themes and structured templates are necessary.

With CI-Twig you can separately set the theme, layout, template and even the assets for each page. Also this does not replace CodeIgniter's default views, so you can still load views as such as: $this->load->view().

## Requirements ##

* PHP 5.2.4+
* CodeIgniter 3.x 

Notes: Codeigniter 2.x is not supported.

# How to use it
---

## 1. Load library ##

```$this->load->library('ci-twig/twig'); ``` 

## 2. Set up directory structure

**Create a directory structure:**

```
+-FCPATH/
| +-themes/
| +-assets/
| | +-css/
| | +-js/
```

**Notes** 

* `FCPATH` is Codeigniter's principal directory, outside the `application` directory where all your controllers and models are placed.
* `CI-Twig` uses `Assetics` for manage the assets used in every theme, so you are gonna need to set the `assets` directory with writable permissions.

**Copy the theme example structure.**

By default CI-Twig uses a `Bootstrap theme`, so that you can create a similar structure in your new themes. 

* Copy the `dist/bootstrap` directory to `themes`.

You should end up with a structure like this:

```
+-FCPATH/
| +-application/
| +-system/
| +-themes/
| | +-bootstrap/
| +-assets/
| | +-css/
| | +-js/
```

## 3. Set a theme and layout 

Bootstrap theme includes a 'container' layout structure. 

```
$this->twig->set_theme('bootstrap');
$this->twig->add_layout('container');
```

**Note**: Chaining method also supported.

```
$this->twig->set_theme('bootstrap')->add_layout('container');
```

## 4. Display the theme

```$this->twig->render();```


A full example using `CI-Twig` in the Welcome Controller:

```
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
		$this->twig->render();
	}
}
```

## 5. What's next? ##

In the example above we only displayed the default template and layout. You can add views to this layout using ```$this->twig->add_view($view,$params)```command. It's exactly like the Codeigniter's method ```$this->load->view($view,$params)``` used for loading views.

CI-Twig View's are using the layout created in the theme, so there is no need to load the same structure files every time a method is called, only the view you gonna need. 

```
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
		$this->twig->add_view('welcome_message')->render();	
	}
}
```

**Note**: before you can add a view inside the 'index' method you gonna need a directory structure inside your `views` folder:

```
+-views/
| +-welcome/
| | +-index/
| | | +-welcome_message.php
```

And there you go, you can add many views as you want before the render method occurs.

# Create a new Theme 
---

Obviously, you can create as many layouts and themes you want, follow me in every step for doing this. 

## 1. Create the directory

Create a new directory structure inside the `themes` folder:

```
+-FCPATH/
| +-themes/
| | +-new_theme/
| | | +-assets (all your theme asset files needed)
| | | | +- css/ 
| | | | +- js/
| | | +-layout
| | | | +-new_layout.twig
| | | +- theme.twig
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
		{% stylesheets 'css/*' '@module_css' filter='cssrewrite' %}
		    <link href="{{ base_url('assets/' ~ asset_url) }}" type="text/css" rel="stylesheet" />
		{% endstylesheets %}				
	</head>
	<body>
		{% block content %}{% endblock %}
        <div id="footer">{% block footer %}{% endblock %}</div>
    	{% javascripts 'js/*' '@module_js' %}
        	<script src="{{ base_url('assets/' ~ asset_url) }}"></script>
    	{% endjavascripts %}	
	</body>
</html>
```

## 3. Create the layout

Same as `theme.twig`, the `layouts/new_layout.twig` default template: 

```
{% extends "theme.twig" %}
{% block title %}{{'new layout'|capitalize}}{% endblock %}

{% block content %}
	{% for view,params in views %}
		{% include view with params %}
	{% endfor %}
{% endblock %}
```

# 4. Load theme layout and views

Set the new theme and structure, add the views and load it before sending the output to the browser.

```
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('twig');
		$this->twig->set_theme('bootstrap')->add_layout('container');
		$this->twig->add_view('welcome_message')->render();	
	}
}
```

Notice that you only need to specify the name of the template (without the extension `*.twig`).

There is much more cool stuff that you should check out by visiting the [docs (anytime soon)](#).

# CHANGELOG
---

### 1.0.0 ###

* First version released (nothing to see here).


# COPYRIGHT #
---

Copyright (c) 2015 David Sosa Valdes

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.