<?php

declare(strict_types=1);

namespace XenialDan\TestPlugin;

use Exception;
use Frago9876543210\WebServer\API;
use Frago9876543210\WebServer\WSConnection;
use Frago9876543210\WebServer\WSRequest;
use Frago9876543210\WebServer\WSResponse;
use InvalidArgumentException;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginException;
use XenialDan\TestPlugin\web\Page;

class MyAPI extends API
{

	public static function handleRequests(): callable
	{
		$template = self::getTemplatePage();
		$pages = self::getPages();
		$css = self::getCSS();

		return static function (WSConnection $connection, WSRequest $request) use ($template, $pages, $css): void {
			$pieces = explode('\\', ltrim($request->getUri(), '\\'));

			//CSS HANDLER
			if ($pieces[0] === 'index.css') {
				$connection->send(new WSResponse($css, 200, 'text/css'));
				$connection->close();
				return;
			}
			//

			try {
				if ($template === null) throw new PluginException('Template couldn\'t be loaded');
				//----
				$navigation = '<dt><a href="/">Home</a></dt>';
				foreach ($pages as $pluginName => $entry) {
					$navigation .= "<dt>$pluginName</dt>";
					foreach ($entry as $pageName => $page) {
						$navigation .= "<dd><a href=\"/$pluginName/$pageName\">$pageName</a></dd>";
					}
				}
				//----
				$title = $template->getTitle();
				if (count($pieces) === 1 && !empty(trim($pieces[0]))) {
					$content = 'Accessing just the plugin name is not accepted!';
					$title = "Error - {$title}";
				} else if (count($pieces) === 2) {
					[$pluginName, $pageTitle] = $pieces;
					//-----
					if (!isset($pages[$pluginName])) throw new InvalidArgumentException("Plugin $pluginName registered no pages!");
					if (!isset($pages[$pluginName][$pageTitle])) throw new InvalidArgumentException("Plugin $pluginName registered no page with the title \"$pageName\"");

					/** @var Page $page */
					$page = $pages[$pluginName][$pageTitle];
					//-----
					$title = "$pluginName - {$page->getTitle()}";
					$content = $page->getContent();
				} else /*if (count($pieces) === 0 || empty(trim($pieces[0])))*/ {
					$content = 'Welcome to the web interface';
				}
				$connection->send(new WSResponse($template->insertTemplateContent(['CSS' => $css, 'NAVIGATION' => $navigation, 'TITLE' => $title, 'CONTENT' => $content])));
			} catch (Exception $exception) {
				#$connection->send(new WSResponse($exception->getMessage(), 404));
				$connection->send(new WSResponse($exception->getMessage(), 200));
				#$connection->close();
			} finally {
				$connection->close();
			}
		};
	}

	////////////////////////////////////////////////

	/** @var array */
	private static $pages = [];
	/** @var null|Page */
	public static $templatePage;
	/** @var string */
	public static $css;

	/**
	 * @return Page|null
	 */
	public static function getTemplatePage(): ?Page
	{
		return self::$templatePage;
	}

	public static function registerPage(Plugin $plugin, Page $page): void
	{
		$name = $plugin->getName();
		$title = $page->getTitle();
		if (empty(trim($title))) throw new InvalidArgumentException('Page title is empty!');
		if (isset(self::$pages[$name][$title])) throw new InvalidArgumentException("A page with the title \"$title\" is already registered for the plugin $name");
		self::$pages[$name] = self::$pages[$name] ?? [];//TODO PHP 7.4 change to ??=
		self::$pages[$name][$title] = $page;
		var_dump(self::$pages[$name]);
	}

	public static function getPage(array $pages, string $pluginName, string $pageName): Page
	{
		if (!isset($pages[$pluginName])) throw new InvalidArgumentException("Plugin $pluginName registered no pages!");
		if (!isset($pages[$pluginName][$pageName])) throw new InvalidArgumentException("Plugin $pluginName registered no page with the title \"$pageName\"");
		return $pages[$pluginName][$pageName];
	}

	/**
	 * @return Page[]
	 */
	public static function getPages(): array
	{
		return self::$pages;
	}

	private static function getCSS(): string
	{
		return self::$css;
	}
}