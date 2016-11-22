<?php //strict
namespace lenando\ResultFields;

use Plenty\Modules\DataExchange\Contracts\ResultFields;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use Plenty\Modules\Helper\Services\ArrayHelper;

/**
 * Class lenandoDE
 * @package lenando\ResultFields
 */
class lenandoDE extends ResultFields
{
	/*
	 * @var ArrayHelper
	 */
	private ArrayHelper $arrayHelper;

	/**
	 * lenando constructor.
	 * @param ArrayHelper $arrayHelper
	 */
	public function __construct(ArrayHelper $arrayHelper)
	{
		$this->arrayHelper = $arrayHelper;
	}

	/**
	 * Generate result fields.
	 * @param  array<FormatSetting> $formatSettings = []
	 * @return array
	 */
	public function generateResultFields(array<FormatSetting> $formatSettings = []):array<string, mixed>
	{
		$settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

        if($settings->get('variations') == 'mainVariations')
        {
            $this->setGroupByList(['groupBy.itemIdGetPrimaryVariation']);
        }
		else
		{
			$this->setOrderByList(['orderBy.itemId' => 'asc']);
		}


        $itemDescriptionFields = ['urlContent'];
        $itemDescriptionFields[] = ($settings->get('nameId')) ? 'name' . $settings->get('nameId') : 'name1';

		if($settings->get('descriptionType') == 'itemShortDescription'
            || $settings->get('previewTextType') == 'itemShortDescription')
        {
            $itemDescriptionFields[] = 'shortDescription';
        }

        if($settings->get('descriptionType') == 'itemDescription'
            || $settings->get('descriptionType') == 'itemDescriptionAndTechnicalData'
            || $settings->get('previewTextType') == 'itemDescription'
            || $settings->get('previewTextType') == 'itemDescriptionAndTechnicalData')
        {
            $itemDescriptionFields[] = 'description';
        }

        if($settings->get('descriptionType') == 'technicalData'
            || $settings->get('descriptionType') == 'itemDescriptionAndTechnicalData'
            || $settings->get('previewTextType') == 'technicalData'
            || $settings->get('previewTextType') == 'itemDescriptionAndTechnicalData')
        {
            $itemDescriptionFields[] = 'technicalData';
        }

		$fields = [
			'itemBase'=> [
				'id',
				'producer',
				'condition',
				'free1',
				'free2',
				'free3',
				'free4',
				'free5',
				'free6',
				'free7',
				'free8',
				'free9',
				'free10',
				'free11',
				'free12',
				'free13',
				'free14',
				'free15',
				'free16',
				'free17',
				'free18',
				'free19',
				'free20',
                'variationCount',
                'hasAttribute',
				'tradoriaCategory'
			],

			'itemDescription' => [
                'params' => [
                    'language' => $settings->get('lang') ? $settings->get('lang') : 'de',
                ],
                'fields' => $itemDescriptionFields,
            ],

			'variationImageList' => [
				'params' => [
					'type' => 'item_variation',
				],
				'referenceMarketplace' => $settings->get('referrerId') ? $settings->get('referrerId') : 106,
				'fields' => [
					'type',
					'path',
					'position',
				]
			],

			'variationBase' => [
				'availability',
				'attributeValueSetId',
				'content',
				'customNumber',
				'id',
				'limitOrderByStockSelect',
				'model',
				'unitId',
				'vatId',
			],

			'variationRecommendedRetailPrice' => [
				'price',
			],

            'variationRetailPrice' => [
                'price',
            ],

			'variationStandardCategory' => [
				'params' => [
					'plentyId' => $settings->get('plentyId') ? $settings->get('plentyId') : 1000,
				],
				'fields' => [
					'categoryId',
					'plentyId',
					'manually',
				],
			],

			'variationBarcodeList' => [
				'params' => [
					'barcodeType' => $settings->get('barcode') ? $settings->get('barcode') : 'EAN',
				],
				'fields' => [
					'code',
					'barcodeId',
				]
			],

			'variationMarketStatus' => [
				'params' => [
					'marketId' => 106
				],
				'fields' => [
					'sku'
				]
			],

			'variationStock' => [
				'params' => [
					'type' => 'virtual'
				],
				'fields' => [
					'stockNet'
				]
			],

			'variationAttributeValueList' => [
				'attributeId',
				'attributeValueId'
			]
		];


		return $fields;
	}
}
