<?php

declare(strict_types=1);

namespace XenialDan\TestPlugin\web;

use InvalidArgumentException;

class Page extends \Threaded
{
	/** @var string */
	private $title;
	/** @var string */
	private $content;

	public function __construct(string $title, string $content = '')
	{
		if(empty(trim($title))) throw new InvalidArgumentException('Title can not be empty');
		$this->title = htmlspecialchars(stripslashes($title));
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return Page
	 */
	public function setTitle(string $title): Page
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * The web page to load. HTML
	 * @return string
	 */
	public function getContent():string {
		return $this->content;
	}

	/**
	 * Invoke web page with parameters on submit (i.e. on POST)
	 * @param array $params
	 */
	public function invoke(array $params = []): void
	{
	}

	public static function provideFromFile(string $path):self{
		if(!file_exists($path)) throw new InvalidArgumentException('File does not exist');
		$resource = fopen($path, 'rb');
		if(!$resource) throw new InvalidArgumentException('Could not read file');
		$title = basename($path);
		$page = self::provideFromResource($title, $resource);
		//fclose($resource);//TODO check for memory leak
		return $page;
	}

	/**
	 * @param string $title
	 * @param resource $resource
	 * @return static
	 */
	public static function provideFromResource(string $title, $resource):self{
		if(!is_resource($resource)) throw new InvalidArgumentException('$resource must be a resource!');
		$content = stream_get_contents($resource);
		fclose($resource);
		return new static($title, $content);
	}

	/**
	 * @param array|array<string, string> $contents
	 * @return string
	 */
	public function insertTemplateContent(array $contents): string
	{
		//todo optimize
		$content = $this->getContent();
		foreach ($contents as $key => $value) {
			$content = str_replace(strtoupper("@$key@"), $value, $content);
		}
		return $content;
	}
}