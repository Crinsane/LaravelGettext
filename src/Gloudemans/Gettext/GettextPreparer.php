<?php namespace Gloudemans\Gettext;

use Gettext\Translator as Translator;

class GettextPreparer {

	/**
	 * The po file manager
	 *
	 * @var Gloudemans\Gettext\PoFileManager
	 */
	protected $poFileManager;

	/**
	 * The php file manager
	 *
	 * @var Gloudemans\Gettext\PhpFileManager
	 */
	protected $phpFileManager;

	/**
	 * The current locale
	 *
	 * @var string
	 */
	protected $locale;

	/**
	 * GettextPreparer constructor
	 *
	 * @param Gloudemans\Gettext\PoFileManager   $poFileManager   The po file manager
	 * @param Gloudemans\Gettext\PhpFileManager  $phpFileManager  The php file manager
	 * @param string                             $locale          The current locale
	 */
	public function __construct($poFileManager, $phpFileManager, $locale)
	{
		$this->poFileManager = $poFileManager;
		$this->phpFileManager = $phpFileManager;
		$this->locale = $locale;
	}

	/**
	 * Prepare the translation files
	 *
	 * @return void
	 */
	public function prepare()
	{
		// Load the .po files
		$poFiles = $this->poFileManager->get();

		// If there are none, then there's nothing for us to do
		if(empty($poFiles)) return;

		// Now we need to convert the .po files to .php files
		$this->poFileManager->convert($poFiles);

		// And finally we load the language files for the current locale
		$this->loadTranslations($this->locale);
	}

	/**
	 * Function only for the facade for loading new translation files for the locale
	 *
	 * @param  string $locale The locale to load the files for
	 * @return void
	 */
	public function load($locale)
	{
		$this->loadTranslations($locale);

		return;
	}

	/**
	 * Load the translations
	 *
	 * @param  string  $locale  The locale to load the files for
	 * @return void
	 */
	protected function loadTranslations($locale)
	{
		// Get the .php files for the current locale
		$phpFiles = $this->phpFileManager->get($locale);

		// Load all the files
		foreach($phpFiles as $file)
		{
			Translator::loadTranslations($file);
		}
	}

}