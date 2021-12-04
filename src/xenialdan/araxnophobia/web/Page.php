<?php

declare(strict_types=1);

namespace xenialdan\araxnophobia\web;

use InvalidArgumentException;

class Page/* implements Serializable*/
{
	/** @var string */
	private $title;
	/** @var string */
	private $content;
	/** @var string */
	private $statusCode = '200';

	public function __construct(string $title, string $content = '')
	{
		if (empty(trim($title))) throw new InvalidArgumentException('Title can not be empty');
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
	public function getContent(): string
	{
		return $this->content;
	}

	/**
	 * Invoke web page with parameters on submit (i.e. on POST)
	 * @param array $params
	 * @return self
	 */
	public function invoke(array $params = []): Page
	{
		return $this;
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content): void
	{
		$this->content = $content;
	}

	public static function provideFromFile(string $path): self
	{
		if (!file_exists($path)) throw new InvalidArgumentException('File does not exist');
		$resource = fopen($path, 'rb');
		if (!$resource) throw new InvalidArgumentException('Could not read file');
		$title = basename($path);
		return self::provideFromResource($title, $resource);
	}

	/**
	 * @param string $title
	 * @param resource $resource
	 * @return static
	 */
	public static function provideFromResource(string $title, $resource): self
	{
		if (!is_resource($resource)) throw new InvalidArgumentException('$resource must be a resource!');
		$content = stream_get_contents($resource);
		fclose($resource);
		return new static($title, $content);
	}

	/**
	 * @param string $template
	 * @param string $navigation
	 * @return string
	 */
	public function applyTemplatePlaceholders(string $template, string $navigation): string
	{
		//todo optimize
		$placeholder = [
			'title' => $this->title,
			'content' => $this->content,
			'navigation' => $navigation,
			'statusCode' => $this->statusCode,
		];
		foreach ($placeholder as $key => $value) {
			$template = str_replace(strtoupper("@$key@"), $value, $template);
		}
		return $template;
	}/*

	/**
	 * @inheritDoc
	 * /
	public function serialize()
	{
		return serialize([
			'title' => $this->title,
			'content' => $this->content,
			'statusCode' => $this->statusCode,
		]);
	}

	/**
	 * @inheritDoc
	 * /
	public function unserialize($serialized)
	{
		[$this->title, $this->content, $this->statusCode] = unserialize($serialized, ['allowed_classes' => [self::class]]);
	}*/
}