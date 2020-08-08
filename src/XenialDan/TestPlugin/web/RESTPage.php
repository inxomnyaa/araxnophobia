<?php

declare(strict_types=1);

namespace XenialDan\TestPlugin\web;

class RESTPage extends Page
{
	/** @var RESTEndpoint[] */
	private $endpoints = [];

	public function registerRESTEndpoint(RESTEndpoint $endpoint): void
	{
		$this->endpoints[] = $endpoint;
	}

	public function processRESTRequest(string $method, array $uriParts): void
	{
		//TODO
		//Parse parameters and find correct endpoint
		//Change content of page according to the result of the endpoint
	}

}