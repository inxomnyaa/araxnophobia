<?php

declare(strict_types=1);

namespace xenialdan\araxnophobia;

use xenialdan\araxnophobia\web\Page;

class TestPage extends Page
{
	public function invoke(array $params = []): Page
	{
		$p = print_r($params, true);
		$str = $p . '<br>';
		if (($params['secret'] ?? '') === 'password') $str .= 'Oh you figured out my secret!<br>';
		$this->setContent($str . $this->getContent());
		return parent::invoke($params);
	}
}