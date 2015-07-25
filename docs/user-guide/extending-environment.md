Extending Twig Environment
========

Twig is flexible enough for all your needs, even the most complex ones. CI-Twig implement tags, filters, functions and even operators with ease thanks to for an open architecture.

---

##Globals

A global variable it's available in all the views used in the template:

```php
$this->twig->add_global('text', new Text());
```

Then you can use it as follows:

```php
{{ text.lipsum(40) }}
```
---

##Filters

CI-Twig implements ```Twig_SimpleFilter``` objects. This is usefull when you're integrating third-party libraries, helpers or libraries that needed inside the views.

```php
// Closure function
$this->twig->add_filter('rot13',function ($string) {
    return str_rot13($string);
}));

// Or a simple php function
$this->twig->add_filter('rot13','str_rot13')


// Or a class method
$this->twig->add_filter('rot13',array('SomeClass', 'rot13Filter'));
```

The first argument passed is the name of the *filter* and the second it's the *closure function*. Inside the view you can call the filter:

```php
{{ 'Twig'|rot13 }}
{# output Gjvt #}
```

When called by Twig, the PHP executable on the left side receives the filter (before the vertical bar |) as the first argument and the extra arguments passed to the filter (within parentheses ()) as an extra argument.

---

##Functions

Functions supporting the same characteristics as the **filters**, except for the options preserves_safety and pre_escapr.

```php
$this->twig->add_function('function_name', closure_function);
```

**Example**

Create a new function inside your controller's method:

```php
$this->twig->add_function('foo_bar', function(){return "foo";});
```

Now you can call the function:

```html
<p>{{foo_bar()}}</p>
```

---

##References

* [Closure Functions](http://php.net/manual/en/functions.anonymous.php)

---