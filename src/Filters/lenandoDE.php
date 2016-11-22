<?php
namespace lenando\Filters;
use Plenty\Modules\DataExchange\Contracts\Filters;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use Plenty\Modules\Helper\Services\ArrayHelper;
class lenandoDE extends Filters
{
    /*
	 * @var ArrayHelper
	 */
	private $arrayHelper;
    /**
     * lenando constructor.
     * @param ArrayHelper $arrayHelper
     */
    public function __construct(ArrayHelper $arrayHelper)
    {
		$this->arrayHelper = $arrayHelper;
    }
    /**
     * Generate filters.
     * @param  array $formatSettings = []
     * @return array
     */
    public function generateFilters(array $formatSettings = []):array
    {
        $settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');
		$searchFilter = [
			'variationBase.isActive?' => [],
            'variationVisibility.isVisibleForMarketplace' => [
				'mandatoryOneMarketplace' => [],
				'mandatoryAllMarketplace' => [
					106
				]
            ]
		];
		return $searchFilter;
    }
}
