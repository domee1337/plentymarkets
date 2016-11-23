<?php
namespace lenando;
use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;
class lenandoServiceProvider extends DataExchangeServiceProvider
{
	public function register()
	{
	}
	public function exports(ExportPresetContainer $container)
	{
		$formats = [
			'lenandoDE',
			          
		];
		foreach ($formats as $format)
		{
			$container->add(
				$format,
				'lenando\ResultFields\\'.$format,
				'lenando\Generators\\'.$format,
				'lenando\Filters\\' . $format
			);
		}
	}
}
