#Creating your themes 

Create as many layouts and templates you need in your project. 

---

##Directory structure

Create a new directory structure inside the theme directory:

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

##Theme (master template)

You are gonna need to create a new structured **theme.twig** file. This is the default template used in every **Attire** theme instance:

```twig
<!DOCTYPE html>
<html>
	<head>
		{% block head %}
			<title>{% block title %}{% endblock %} - {{app_fullname|title}}</title>
		{% endblock %}
		{% block stylesheets %}
			{% stylesheets 'css/*' filter='cssrewrite' %}
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
			{% javascripts 'js/*' %}
				<script src="{{ base_url('assets/' ~ asset_url) }}"></script>
			{% endjavascripts %}
		{% endblock %}
	</body>
</html>
```

Use it as a basic template and create something unique.

##Layout

Same as **theme.twig**, the **layouts/new_layout.twig** default template: 

```twig
{% extends "theme.twig" %}
{% block title %}{{'new_layout'|capitalize}}{% endblock %}

{% block content %}
	{% for view,params in views %}
		{% include view with params %}
	{% endfor %}
{% endblock %}
```

Anything can be a layout, check the [twig extends docs](http://twig.sensiolabs.org/doc/tags/extends.html).

##Rendering the theme

Set the new theme and structure, add the views and load it before sending the output to the browser.

```php
<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller 
{
	public function index()
	{	
		$this->load->library('attire/attire');
		$this->attire->set_theme('new_theme')->add_layout('new_layout');
		$this->attire->add_view('welcome_message')->render();	
	}
}
```

Notice that you only need to specify the name of the template (without the extension `*.twig`).