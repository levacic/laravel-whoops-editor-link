<?php namespace Levacic\LaravelWhoopsEditorLink;

use Illuminate\Support\ServiceProvider;

class LaravelWhoopsEditorLinkServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('levacic/laravel-whoops-editor-link', 'levacic/laravel-whoops-editor-link');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$config = $this->app['config']->get('levacic/laravel-whoops-editor-link::path');

		$this->fixPrettyWhoopsHandlerEditor($config['host'], $config['guest']);
	}

	/**
	 * Fixes the editor for the whoops pretty page handler, so that it links
	 * correctly even though the app is running within a VM.
	 *
	 * If the currently configured handler doesn't support the `setEditor()`
	 * method, nothing will happen.
	 *
	 * In order for this to work, you need to publish the package's config file,
	 * and configure the appropriate `host` and `guest` paths. If one of these
	 * is not configured, the handler will fall through to the default editor
	 * link.
	 *
	 * @param string $hostPath
	 * @param string $guestPath
	 * @return void
	 */
	protected function fixPrettyWhoopsHandlerEditor($hostPath, $guestPath)
	{
		if (!$hostPath || !$guestPath)
		{
			return;
		}

		$handler = $this->app['whoops.handler'];

		if (!is_callable([$handler, 'setEditor']))
		{
			return;
		}

		$this->app['whoops.handler']->addEditor('sublime', function($filePath, $line) use ($hostPath, $guestPath)
		{
			$filePath = str_replace($guestPath, $hostPath, $filePath);

			$href = 'subl://open?url=file://%file&line=%line';
			$href = str_replace("%line", rawurlencode($line), $href);
			$href = str_replace("%file", rawurlencode($filePath), $href);

			return $href;
		});
	}

}
