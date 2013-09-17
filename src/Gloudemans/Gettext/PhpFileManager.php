<?php namespace Gloudemans\Gettext;

use Gettext\Generators\PhpArray as PhpArrayGenerator;

class PhpFileManager {

	/**
	 * The filesystem
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;

	/**
	 * The app path
	 *
	 * @var string
	 */
	protected $appPath;

	/**
	 * PhpFileManager constructor
	 *
	 * @param Illuminate\Filesystem\Filesystem        $filesystem      The filesystem
	 * @param string                                  $appPath         The app path
	 */
	public function __construct($filesystem, $appPath)
	{
		$this->filesystem = $filesystem;
		$this->appPath = $appPath;
	}

	/**
	 * Create a PHP file from translations
	 *
	 * @param  Gettext\Entries $translations   The PO file path
	 * @param  string          $fullPath       The PHP file path
	 * @return void
	 */
	public function create($translations, $fullPath)
	{
		return PhpArrayGenerator::generateFile($translations, $fullPath);
	}

	/**
	 * Get the php translation files for the supplied locale
	 *
	 * @param  string  $locale  The locale to get the translation files from
	 * @return Array
	 */
	public function get($locale)
	{
		$files = $this->filesystem->allFiles($this->appPath . '/storage/lang/' . $locale);

		return array_filter($files, function($file)
		{
			return $file->getExtension() === 'php';
		});
	}

}