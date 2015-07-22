##Extending Twig Environment



---

CI-Twig implements ```Twig_SimpleFunction``` objects that extend the current **Twig Environment**. This is usefull when you're using third-party integrations, helpers or libraries that needed to be called inside the views. Create a new function as follows:

```php
$this->twig->add_function('function_name', closure);
```

Example:

Create a new function inside your controller's method:

```php
$this->twig->add_function('foo_bar', function(){return "foo";});
```

Now you can call it inside your view:

```html
<p>{{foo_bar()}}</p>
```

Functions supporting the same characteristics as the filters, except for the options and preserves_safety pre_escapr.

**References:** 

* [Closure Functions](http://php.net/manual/en/functions.anonymous.php)