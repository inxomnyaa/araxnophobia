<?php

declare(strict_types=1);

namespace XenialDan\TestPlugin;

use Frago9876543210\WebServer\WebServer;
use pocketmine\plugin\PluginBase;
use XenialDan\TestPlugin\web\Page;

class Loader extends PluginBase
{
	public const HOME = 'Home';

	/**
	 * @var WebServer|null
	 */
	public static $web;
	/**
	 * @var Loader
	 */
	private static $instance;

	/**
	 * @return Loader
	 */
	public static function getInstance(): Loader
	{
		return self::$instance;
	}

	public function onLoad()
	{
		$index = $this->getResource('index.html');
		MyAPI::$templatePage = Page::provideFromResource(self::HOME, $index);
		MyAPI::$css = file_get_contents($this->getFile() . 'resources/index.css');
		MyAPI::registerPage($this, new Page('RegisterTest', 'This is a registered test page'));
		MyAPI::registerPage($this, new Page('RegisterTest2', 'This is 2nd registered test page'));
		MyAPI::registerPage($this, new Page('Hello World!</h3><h1>HI</h1><h3>', 'This is a registered test page'));
		self::$instance = $this;

		foreach ($this->getServer()->getPluginManager()->getPlugins() as $plugin) {
			MyAPI::registerPage($plugin, new Page($plugin->getName(), $plugin->getDataFolder()));
		}
		MyAPI::registerPage($this, new Page('Fish', 'This is a registered test page'));
		MyAPI::registerPage($this, new Page('HTML Test', '<form><input type="text" name="name"><button type="submit">Send</button></form><br>'));
	}

	public function onEnable()
	{
		$web = MyAPI::startWebServer($this, MyAPI::handleRequests(), 8081);
		self::$web = $web;

		#$this->getLogger()->info("Hi i enabled");
		#$this->getServer()->getPluginManager()->registerEvents(new EVListener(), $this);
	}

	protected function onDisable()
	{
		self::$web->shutdown();
	}

}
