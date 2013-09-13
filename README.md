## Installation

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

```php
"require": {
	"laravel/framework": "4.0.*",
	"gloudemans/gettext": "dev-master"
}
```

Next, run the Composer update command from the Terminal:

    composer update

Now all you have to do is add the service provider of the package and alias the package. To do this open your `app/config/app.php` file.

Add a new line to the `service providers` array:

	'Gloudemans\Gettext\GettextServiceProvider'
