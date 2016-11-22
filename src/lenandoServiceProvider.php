<?php // strict
namespace lenando;

use Plenty\Modules\DataExchange\Services\ExportPresetContainer;
use Plenty\Plugin\DataExchangeServiceProvider;

class lenandoServiceProvider extends DataExchangeServiceProvider
{
	public function register():void
	{
	}

	public function exports(ExportPresetContainer $container):void
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
