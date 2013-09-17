<?php namespace Gloudemans\Gettext;

use Illuminate\Support\ServiceProvider;

class GettextServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$appPath = $this->app['path'];
		$filesystem = $this->app['files'];
		$locale = $this->app['config']->get('app.locale');

		$lastModified = new LastModifiedManager($filesystem, $appPath);
		$phpFileManager = new PhpFileManager($filesystem, $appPath);
		$poFileManager = new PoFileManager($filesystem, $lastModified, $phpFileManager, $appPath);
		$getTextPreparer = new GettextPreparer($poFileManager, $phpFileManager, $locale);

		$getTextPreparer->prepare();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}

class NoneExistingLangDirectoryException extends \Exception {}
class NoneExistingLocaleLangDirectoryException extends \Exception {}
class NoTranslationFilesExistException extends \Exception {}