<?php namespace Gloudemans\Gettext;

use Gettext\Extractors\Po as PoExtractor;

class PoFileManager {

	/**
	 * The filesystem
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * The modify manager
	 *
	 * @var Gloudemans\Gettext\LastModifiedManager
	 */
	protected $modifyManager;

	/**
	 * The php file manager
	 *
	 * @var Gloudemans\Gettext\PhpFileManager
	 */
	protected $phpFileManager;

	/**
	 * The app path
	 *
	 * @var string
	 */
	protected $appPath;

	/**
	 * PoFileManager constructor
	 *
	 * @param Illuminate\Filesystem\Filesystem        $filesystem      The filesystem
	 * @param Gloudemans\Gettext\LastModifiedManager  $modifyManager   The modify manager
	 * @param Gloudemans\Gettext\PhpFileManager       $phpFileManager  The php file manager
	 * @param string                                  $appPath         The app path
	 */
	public function __construct($filesystem, $modifyManager, $phpFileManager, $appPath)
	{
		$this->filesystem = $filesystem;
		$this->modifyManager = $modifyManager;
		$this->phpFileManager = $phpFileManager;
		$this->appPath = $appPath;
	}

	/**
	 * Get all the .po files for the current locale
	 *
	 * @return Array
	 */
	public function get()
	{
		$files = $this->filesystem->allFiles($this->appPath . '/lang');

		return array_filter($files, function($file)
		{
			return $file->getExtension() === 'po';
		});
	}

	/**
	 * Get all php files that need to be loaded
	 *
	 * @param  Array  $poFiles  The PO files
	 * @return void
	 */
	public function convert($poFiles)
	{
		if( ! $this->checkLangDirectory())
			throw new NoneExistingLangDirectoryException('The "app/storage/lang" directory doesn\'t exist and could not be created.');

		foreach($poFiles as $file)
		{
			$fullPath = $this->buildFilePath($file);

			if( ! $this->filesystem->exists($fullPath) || $this->modifyManager->isModified($fullPath))
			{
				$translations = $this->extractTranslations($file->getRealPath());
				$this->phpFileManager->create($translations, $fullPath);
			}

			// Update the last modified time
			$this->modifyManager->add($fullPath);
		}

		// We want to update the JSON file with the new values
		return $this->modifyManager->put();
	}

	/**
	 * Check if the lang directory exists in the app/storage directory
	 *
	 * @return boolean
	 */
	protected function checkLangDirectory()
	{
		$langPath = $this->appPath . '/storage/lang';

		if( ! $this->filesystem->isDirectory($langPath))
		{
			return $this->filesystem->makeDirectory($langPath);
		}

		return true;
	}

	/**
	 * Check if the locale directory exists in the app/storage/lang directory
	 *
	 * @param  string  $locale  The locale to check for
	 * @return boolean
	 */
	protected function checkLocaleDirectory($locale)
	{
		$localePath = $this->appPath . '/storage/lang/' . $locale;

		if( ! $this->filesystem->isDirectory($localePath))
		{
			return $this->filesystem->makeDirectory($localePath);
		}

		return true;
	}

	/**
	 * Build the full path for the php file
	 *
	 * @param  SplFileInfo   $file   The original .po file
	 * @return string
	 */
	protected function buildFilePath($file)
	{
		$locale = $file->getRelativePath();

		$this->checkLocaleDirectory($locale);

		$filename = $file->getBasename('.po');

		return $this->appPath . '/storage/lang/' . $locale . '/' . $filename . '.php';
	}

	/**
	 * Extract the translation from the .po file
	 *
	 * @param  string $file The file path
	 * @return Gettext\Entries
	 */
	protected function extractTranslations($file)
	{
		return PoExtractor::extract($file);
	}

}