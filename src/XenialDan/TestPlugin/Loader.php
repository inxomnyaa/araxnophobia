<?php

declare(strict_types=1);

namespace XenialDan\TestPlugin;

use Frago9876543210\WebServer\WebServer;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use XenialDan\TestPlugin\web\Page;

class Loader extends PluginBase
{
	/** @var WebServer|null */
	public static $web;
	/** @var Loader */
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
		if (is_resource(($template = $this->getResource('index.html'))) && ($templateContents = stream_get_contents($template)) !== false && fclose($template))
			MyAPI::$template = $templateContents;
		else throw new PluginException('Template couldn\'t be loaded!');
		MyAPI::$webRoot = $this->getFile() . 'resources/';
		//Register test pages for this plugin
		MyAPI::registerPage($this, new Page('RegisterTest', 'This is a registered test page'));
		MyAPI::registerPage($this, new Page('Hello World!</h3><h1>HI</h1><h3>', 'This is a html tag exploit test'));
		//Register a default site for every enabled plugin for debug & navigation testing
		/*foreach ($this->getServer()->getPluginManager()->getPlugins() as $plugin) {
			MyAPI::registerPage($plugin, new Page($plugin->getName(), $plugin->getDataFolder() . '<br>' . $plugin->getDescription()->getDescription()));
		}*/
		//Test late-registering to check if they pop up in the correct plugin section
		MyAPI::registerPage($this, new Page('Fish', 'This is a registered test page'));
		/*MyAPI::registerPage($this, new Page('HTML Test', '<form><input type="text" name="name"><button type="submit">Send</button></form><br>'));
		MyAPI::registerPage($this, new Page('PHP Test', 'This should not work:</br>PHP $_GET name:"<?php print $_GET["name"];?>"'));*/

		self::$instance = $this;
	}

	public function onEnable()
	{
		$web = MyAPI::startWebServer($this, MyAPI::handleRequests(MyAPI::getWebRoot(), MyAPI::getTemplate(), MyAPI::generateNavigation()), 8081);
		self::$web = $web;
	}

	protected function onDisable()
	{
		self::$web->shutdown();
	}

}
