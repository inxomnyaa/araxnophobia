# araxnophobia
Spins up webservers faster than your phobia kicks in
## Information
A PocketMine-MP plugin providing an API for REST-like endpoints.
## Examples
- [araxnophobia](https://github.com/thebigsmilexd/araxnophobia): Provides a basic PocketMine-MP config editor web page with API for registering additional per-plugin config files
## API
Add soft-dependency on this plugin to your `plugin.yml`:
```yml
name: MyPlugin
soft-depend: ['araxnophobia']
...
```
Registering a page as an endpoint is really easy:
```php
// Register test pages for this plugin
MyAPI::registerPage($this, new Page('RegisterTest', 'This is a registered test page'));
```
You can also extend \xenialdan\araxnophobia\web\Page to add your own handlings and provide the endpoint with data, i.e. from your plugin
```php
// YourPage class extends Page
class YourPage extends Page
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
// register it somewhere in your main class, best in `onLoad`
MyAPI::registerPage($this, new YourPage('Form Invoke Test', '<form method="post"><input type="text" name="secret" placeholder="Psst: try >password<"><button type="submit">Send</button></form><br>'));
```
If you are looking for a file serving type of endpoint, use `\xenialdan\araxnophobia\MyAPI`

Be sure to define a file root for the server
```php
MyAPI::$webRoot = $this->getFile() . 'resources/';
```
## Configuration
Adding config support is in work.

As for now, the plugin spins up a webserver serving static files (readonly, `rb`) from `plugins/araxnophobia/resources` on port `8081`
