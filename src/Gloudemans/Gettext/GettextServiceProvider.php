<?php namespace Gloudemans\Gettext;

use Illuminate\Support\ServiceProvider;
use Gettext\Extractors\Po as PoExtractor;
use Gettext\Generators\PhpArray as PhpArrayGenerator;
use Gettext\Translator as Translator;

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
		$this->appPath = $this->app['path'];
		$this->filesystem = $this->app['files'];
		$this->locale = $this->app['config']->get('app.locale');

		$poFiles = $this->getPOFiles();

		if(empty($poFiles)) return;

		$phpFiles = $this->getPHPFiles($poFiles);

		$this->loadTranslations($phpFiles);
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

	/**
	 * Get all the .po files for the current locale
	 *
	 * @return Array
	 */
	protected function getPOFiles()
	{
		$filesystem = $this->filesystem;

		$files = $filesystem->files($this->appPath . '/lang/' . $this->locale);

		return array_filter($files, function($f) use ($filesystem)
		{
			return $filesystem->extension($f) == 'po';
		});
	}

	/**
	 * Get all php files that need to be loaded
	 * @param  Array  $poFiles  The PO files
	 * @return Array
	 */
	protected function getPHPFiles($poFiles)
	{
		if( ! $this->checkLangDirectory())
			throw new NoneExistingLangDirectoryException('The "app/storage/lang" directory doesn\'t exist and could not be created.');

		foreach($poFiles as $file)
		{
			$filename = pathinfo($file, PATHINFO_FILENAME);
			$fullPath = $this->appPath . '/storage/lang/' . $filename . '.php';

			if( ! $this->filesystem->exists($fullPath))
			{
				$this->generatePHPFile($file, $fullPath);
			}

			$phpFiles[] = $fullPath;
		}

		if(empty($phpFiles))
			throw new NoTranslationFilesExistException('No PHP translation files where found');

		return $phpFiles;
	}

	/**
	 * Load the translations
	 *
	 * @param  Array $phpFiles The translation files to load
	 * @return void
	 */
	protected function loadTranslations($phpFiles)
	{
		foreach($phpFiles as $file)
		{
			Translator::loadTranslations($file);
		}
	}

	/**
	 * Check if the lang directory exists in the app/storage directory
	 *
	 * @return void
	 */
	protected function checkLangDirectory()
	{
		if( ! $this->filesystem->isDirectory($this->appPath . '/storage/lang'))
		{
			return $this->filesystem->makeDirectory($this->appPath . '/storage/lang');
		}

		return true;
	}

	/**
	 * Generate a PHP file from the PO file
	 *
	 * @param  string $file     The PO file path
	 * @param  string $fullPath The PHP file path
	 * @return void
	 */
	protected function generatePHPFile($file, $fullPath)
	{
		$translations = PoExtractor::extract($file);

		PhpArrayGenerator::generateFile($translations, $fullPath);
	}

}

class NoneExistingLangDirectoryException extends \Exception {}
class NoTranslationFilesExistException extends \Exception {}