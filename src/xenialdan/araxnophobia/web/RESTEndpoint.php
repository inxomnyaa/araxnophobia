<?php

declare(strict_types=1);

namespace xenialdan\araxnophobia\web;

use Closure;

class RESTEndpoint
{
	/** @var string */
	private $method;
	/** @var Closure */
	private $closure;

	/**
	 * RESTEndpoint constructor.
	 * @param string $method
	 * @param Closure $closure
	 */
	public function __construct(string $method, Closure $closure)
	{
		$this->method = $method;
		//TODO verify Closure
		//Utils::validateCallableSignature(static function (...$params): void {
		//}, $closure);
		$this->closure = $closure;
	}

	public function invoke(array $params): bool
	{
		//TODO
		//call_user_func($this->closure, $params);
		return false;
	}

	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}

}