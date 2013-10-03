<?php namespace Gloudemans\Gettext;

class LastModifiedManager {

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
	 * Holds the last modified times
	 *
	 * @var array
	 */
	protected $lastModified = array();

	/**
	 * LastModifiedManager constructor
	 *
	 * @param Illuminate\Filesystem\Filesystem        $filesystem      The filesystem
	 * @param string                                  $appPath         The app path
	 */
	public function __construct($filesystem, $appPath)
	{
		$this->appPath = $appPath;
		$this->filesystem = $filesystem;

		$this->load();
	}

	/**
	 * Load the last modified JSON file and decodes it to an array
	 *
	 * @return Array
	 */
	public function load()
	{
		$file = $this->appPath . '/storage/lang/languages.json';

		if( ! $this->filesystem->exists($file)) return array();

		$lastModified = $this->filesystem->get($file);

		$this->lastModified = json_decode($lastModified, true);
	}

	/**
	 * Get the last modified time for the given file key
	 *
	 * @param  string  $fileKey  The file key
	 * @return string
	 */
	public function get($fileKey)
	{
		return (isset($this->lastModified[$fileKey])) ? $this->lastModified[$fileKey] : 0;
	}

	/**
	 * Add or update the modified entry for the given file
	 *
	 * @param  string   $fullPath   The full path of the file
	 * @return void
	 */
	public function add($fullPath)
	{
		$lastModified = $this->filesystem->lastModified($fullPath);

		$this->lastModified[sha1($fullPath)] = $lastModified;
	}

	/**
	 * Write the last modified information as JSON string to file
	 *
	 * @return void
	 */
	public function put()
	{
		$lastModifiedJSON = json_encode($this->lastModified);

		$this->filesystem->put($this->appPath . '/storage/lang/languages.json', $lastModifiedJSON);
	}

	/**
	 * Check if a file is modified since the last convertion
	 *
	 * @param  string   $fullPath   The full path of the file
	 * @return boolean
	 */
	public function isModified($fullPath)
	{
		$lastModified = $this->get(sha1($fullPath));
		$fileModified = $this->filesystem->lastModified($fullPath);

		return ($lastModified < $fileModified);
	}

}