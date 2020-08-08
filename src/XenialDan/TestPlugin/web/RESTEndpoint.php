<?php

declare(strict_types=1);

namespace XenialDan\TestPlugin\web;

use pocketmine\utils\Utils;

class RESTEndpoint
{
	/** @var string */
	private $method;
	/** @var callable */
	private $callable;

	/**
	 * RESTEndpoint constructor.
	 * @param string $method
	 * @param callable $callable
	 */
	public function __construct(string $method, callable $callable)
	{
		$this->method = $method;
		//TODO verify Closure
		//Utils::validateCallableSignature(static function (...$params): void {
		//}, $callable);
		$this->callable = $callable;
	}

	public function invoke(array $params): bool
	{
		//TODO
		//call_user_func($this->callable, $params);
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