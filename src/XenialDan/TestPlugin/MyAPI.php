<?php

declare(strict_types=1);

namespace XenialDan\TestPlugin;

use Closure;
use Exception;
use Frago9876543210\WebServer\API;
use Frago9876543210\WebServer\WSConnection;
use Frago9876543210\WebServer\WSRequest;
use Frago9876543210\WebServer\WSResponse;
use InvalidArgumentException;
use pocketmine\plugin\Plugin;
use XenialDan\TestPlugin\web\Page;
use XenialDan\TestPlugin\web\RESTPage;

class MyAPI extends API
{

	public static function handleRequests(string $webRoot, string $template, string $navigation, array $pages): Closure
	{
		return function (WSConnection $connection, WSRequest $request) use ($webRoot, $template, $navigation, $pages): void {
			//MIME TYPE HANDLER
			$mimeTypes = [
				'.css' => 'text/css; charset=utf-8',
				'.gif' => 'image/gif',
				'.htm' => 'text/html; charset=utf-8',
				'.html' => 'text/html; charset=utf-8',
				'.jpg' => 'image/jpeg',
				'.js' => 'application/javascript',
				'.md' => 'text/markdown; charset=utf-8',
				'.pdf' => 'application/pdf',
				'.png' => 'image/png',
				'.svg' => 'image/svg+xml',
				'.txt' => 'text/plain; charset=utf-8',
				//'.wasm' => 'application/wasm',
				'.xml' => 'text/xml; charset=utf-8',
			];
			if (array_key_exists(($ext = '.' . (pathinfo($request->getUri(), PATHINFO_EXTENSION))), $mimeTypes)) {
				$filePath = $webRoot . $request->getUri();
				if (file_exists($filePath)) {
					ob_start(); // begin collecting output
					/** @noinspection PhpIncludeInspection */
					include $filePath;
					$content = ob_get_clean();
					$response = new WSResponse($content, 200, $mimeTypes[$ext]);
				} else {
					$response = WSResponse::error(404);
				}
				$connection->send($response);
				$connection->close();
				return;
			}
			//URI REQUESTS
			//TODO plan how to use the overflowing "REST API" $uriParts
			try {
				$uriParts = explode('\\', ltrim(urldecode($request->getUri()), '\\'));
				/* * @noinspection ForgottenDebugOutputInspection */
				//var_dump($ext, $uriParts, $request->getParameters(), $request->getHeaders());
				//Extract requested page information from $uriParts
				$pluginName = array_shift($uriParts);
				if ($pluginName === '') $pluginName = null;
				$pageTitle = array_shift($uriParts);
				//Check which site was requested
				if ($pluginName === null && $pageTitle === null) {
					//send 'home' page, nothing special requested
					$page = new Page('Home', 'Welcome to the web interface');
				} else if (trim($pluginName ?? '') === '' || trim($pageTitle ?? '') === '') {
					//send error page with invalid request
					$connection->send(WSResponse::error(400));
					$connection->close();
					return;
				} else if (is_string($pluginName) && is_string($pageTitle)) {
					if (count($uriParts) < 1) {
						//send requested page (no REST API)
						$page = MyAPI::getPage2($pages, $pluginName, $pageTitle)->invoke($request->getParameters());
					} else {
						//send requested page (REST API)
						$page = MyAPI::getPage2($pages, $pluginName, $pageTitle)->invoke($request->getParameters());
						if ($page instanceof RESTPage) {
							$page->processRESTRequest($request->getMethod(), $uriParts);//TODO maybe return WSResponse
						} else {
							$connection->send(WSResponse::error(400));
							$connection->close();
							return;
						}
					}
				}
				$connection->send(new WSResponse($page->applyTemplatePlaceholders($template, $navigation)));
			} catch (Exception $exception) {
				#$connection->send(new WSResponse($exception->getMessage(), 404));
				$connection->send(new WSResponse($exception->getMessage(), 200));//TODO change?
				#$connection->close();
			} finally {
				$connection->close();
			}
		};
	}

	////////////////////////////////////////////////

	/** @var Page[] */
	private static $pages = [];
	/** @var string */
	public static $template;
	/** @var string */
	public static $webRoot;

	/**
	 * @return string
	 */
	public static function getTemplate(): string
	{
		return self::$template;
	}

	public static function registerPage(Plugin $plugin, Page $page): void
	{
		$name = $plugin->getName();
		$title = $page->getTitle();
		if (empty(trim($title))) throw new InvalidArgumentException('Page title is empty!');
		if (isset(self::$pages[$name][$title])) throw new InvalidArgumentException("A page with the title \"$title\" is already registered for the plugin $name");
		self::$pages[$name] = self::$pages[$name] ?? [];//TODO PHP 7.4 change to ??=
		self::$pages[$name][$title] = $page;
	}

	public static function getPage(string $pluginName, string $pageName): Page
	{
		$pages = self::getPages();
		if (!isset($pages[$pluginName])) throw new InvalidArgumentException("Plugin $pluginName registered no pages!");
		if (!isset($pages[$pluginName][$pageName])) throw new InvalidArgumentException("Plugin $pluginName registered no page with the title \"$pageName\"");
		return $pages[$pluginName][$pageName];
	}

	public static function getPage2(array $pages, string $pluginName, string $pageName): Page
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
		$pages = self::$pages;
		ksort($pages, SORT_STRING | SORT_ASC);
		return $pages;
	}

	public static function getWebRoot(): string
	{
		return self::$webRoot;
	}

	/**
	 * @return string
	 */
	public static function generateNavigation(): string
	{
		$navigation = '<dt><a href="/">Home</a></dt>';
		foreach (MyAPI::getPages() as $pluginName => $value) {
			$navigation .= "<dt>$pluginName</dt>";
			foreach ($value as $pageTitle => $page) {
				$url = '/' . urlencode(stripslashes($pluginName)) . '/' . urlencode(stripslashes($pageTitle));
				$navigation .= "<dd><a href=\"$url\">$pageTitle</a></dd>";
			}
		}
		return $navigation;
	}
}