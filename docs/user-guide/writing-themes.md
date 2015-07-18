# Create a new Theme 

Obviously, you can create as many layouts and theme you want, follow me in every step for doing this. 

---

##Create the directory

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

##Create a theme file

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

##Create the layout

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

##Load theme layout and views

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