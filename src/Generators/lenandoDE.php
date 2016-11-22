<?php
namespace ElasticExport\Generators;
use Plenty\Modules\DataExchange\Contracts\CSVGenerator;
use Plenty\Modules\Helper\Services\ArrayHelper;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\DataLayer\Models\RecordList;
use Plenty\Modules\DataExchange\Models\FormatSetting;
use ElasticExport\Helper\ElasticExportHelper;
use Plenty\Modules\Helper\Models\KeyValue;
use Plenty\Modules\Market\Helper\Contracts\MarketPropertyHelperRepositoryContract;
class lenandoDE extends CSVGenerator
{
	const PROPERTY_TYPE_ENERGY_CLASS       = 'energy_efficiency_class';
	const PROPERTY_TYPE_ENERGY_CLASS_GROUP = 'energy_efficiency_class_group';
	const PROPERTY_TYPE_ENERGY_CLASS_UNTIL = 'energy_efficiency_class_until';

	/*
	 * @var lenandoHelper
	 */
	private lenandoHelper $lenandoHelper;

	/*
	 * @var ArrayHelper
	 */
	private ArrayHelper $arrayHelper;

	/**
	 * lenando constructor.
	 * @param lenandoHelper $lenandoHelper
	 * @param ArrayHelper $arrayHelper
	 */
	public function __construct(lenandoHelper $lenandoHelper, ArrayHelper $arrayHelper)
	{
		$this->lenandoHelper = $lenandoHelper;
		$this->arrayHelper = $arrayHelper;
	}

	/**
	 * @param mixed $resultData
	 */
	protected function generateContent(mixed $resultData, array<FormatSetting> $formatSettings = []):void
	{
		if($resultData instanceof RecordList)
		{
			$settings = $this->arrayHelper->buildMapFromObjectList($formatSettings, 'key', 'value');

			$this->setDelimiter(";");
			
			$this->addCSVContent([
				'external_id',
				'external_parent_id',
				'categoryname',
				'active',
				'sort',
				'level',
			]);
			
			
			
			
	$data = [
			'external_id'			=> '1',
			'external_parent_id'	=> '0',
			'categoryname'			=> 'alle Produkte',
			'active'				=> '1',
			'sort'					=> '0',
			'level'					=> '0',
		
		];

		$this->addCSVContent(array_values($data));
			
		$this->addCSVContent([
				'',
			]);

			$this->addCSVContent([
				'Produktname',
				'Artikelnummer',
				'ean',
				'Hersteller',
				'Steuersatz',
				'Preis',
				'Kurzbeschreibung',
				'Beschreibung',
				'Versandkosten',
				'Lagerbestand',
				'Kategoriestruktur',
				'Attribute',
				'Gewicht',
				'Lieferzeit',
				'Nachnahmegebühr',
				'MPN',
				'Bildlink',
				'Bildlink2',
				'Bildlink3',
				'Bildlink4',
				'Bildlink5',
				'Bildlink6',
				'Zustand',
				'Familienname1',
				'Eigenschaft1',
				'Familienname2',
				'Eigenschaft2',
				'ID',
				'Inhalt',
				'Einheit',
				'Freifeld1',
				'Freifeld2',
				'Freifeld3',
				'Freifeld4',
				'Freifeld5',
				'Freifeld6',
				'Freifeld7',
				'Freifeld8',
				'Freifeld9',
				'Freifeld10',
				'baseid',
				'basename',
				'level',
				'status',
				'external_categories',
				'base',
				'dealer_price',
				'link'		 ,
				'ASIN',
				'Mindestabnahme',
				'Maximalabnahme',
				'Abnahmestaffelung',
			]);

			$previousItemId = 0;
            $attributeName = array();
            foreach($resultData as $item)
            {
                $attributeName[$item->itemBase->id] = $this->lenandoHelper->getAttributeName($item, $settings);
            }

			foreach($resultData as $item)
			{
				$currentItemId = $item->itemBase->id;
                $attributeValue = $this->lenandoHelper->getAttributeValueSetShortFrontendName($item, $settings, '|');

                /**
                 * Case of an item with more variation
                 */
                if ($previousItemId != $currentItemId && $item->itemBase->variationCount > 1)
				{
                    /**
                     * The item has multiple active variations with attributes
                     */
                    if(strlen($attributeName[$item->itemBase->id]) > 0)
                    {
                        $this->buildParentWithChildrenRow($item, $settings, $attributeName);
                    }
                    /**
                     * The item has only inactive variations
                     */
                    else
                    {
                        $this->buildParentWithoutChildrenRow($item, $settings);
                    }
                    /**
                     * This will only be triggered if the main variation also has a attribute value
                     */
					if(strlen($attributeValue) > 0)
					{
						$this->buildChildRow($item, $settings, $attributeValue);
					}
					$previousItemId = $currentItemId;
				}
                /**
                 * Case item has only the main variation
                 */
				elseif($previousItemId != $currentItemId && $item->itemBase->variationCount == 1 && $item->itemBase->hasAttribute == false)
				{
					$this->buildParentWithoutChildrenRow($item, $settings);
					$previousItemId = $currentItemId;
				}
                /**
                 * The parent is already in the csv
                 */
				elseif(strlen($attributeValue) > 0)
				{
					$this->buildChildRow($item, $settings, $attributeValue);
				}
			}
		}
	}

	/**
	 * @param Record $item
	 * @param KeyValue $settings
	 * @return void
	 */
	private function buildParentWithoutChildrenRow(Record $item, KeyValue $settings):void
	{
		if($item->variationBase->limitOrderByStockSelect == 2)
		{
			$variationAvailable = 1;
			$inventoryManagementActive = 0;
			$stock = 999;
		}
		elseif($item->variationBase->limitOrderByStockSelect == 1 && $item->variationStock->stockNet > 0)
		{
			$variationAvailable = 1;
			$inventoryManagementActive = 1;
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				$stock = $item->variationStock->stockNet;
			}
		}
		elseif($item->variationBase->limitOrderByStockSelect == 0)
		{
			$variationAvailable = 1;
			$inventoryManagementActive = 0;
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				if($item->variationStock->stockNet > 0)
				{
					$stock = $item->variationStock->stockNet;
				}
				else
				{
					$stock = 0;
				}
			}
		}
		else
		{
			$variationAvailable = 0;
			$inventoryManagementActive = 1;
			$stock = 0;
		}

		$vat = $item->variationBase->vatId;
		if($vat == '19')
		{
			$vat = 19;
		}
		else if($vat == '7')
		{
			$vat = 7;
		}
		else
		{
			$vat = 19;
		}

		$rrp = $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) > $this->lenandoHelper->getPrice($item) ? $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) : $this->lenandoHelper->getPrice($item);
		$price = $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) > $this->lenandoHelper->getPrice($item) ? $this->lenandoHelper->getPrice($item) : $this->lenandoHelper->getRecommendedRetailPrice($item, $settings);
		$price = $price > 0 ? $price : '';
		$unit = $this->getUnit($item, $settings);
		$basePriceContent = (int)$item->variationBase->content;
		
		
        
		
		

		$data = [
			'Produktname'			=> $this->lenandoHelper->getName($item, $settings, 150),
			'Artikelnummer'			=> $item->itemBase->id,
			'ean'				=> $this->lenandoHelper->getBarcodeByType($item, $settings->get('barcode')),
			'Hersteller'			=> $item->itemBase->producer,
			'Steuersatz'			=> $vat,
			'Preis'				=> number_format($rrp, 2, '.', ''),
			'Kurzbeschreibung'		=> '',
			'Beschreibung'			=> $this->lenandoHelper->getDescription($item, $settings, 5000),
			'Versandkosten'			=> '',
			'Lagerbestand'			=> $stock,
			'Kategoriestruktur'		=> '',
			'Attribute'			=> '',
			'Gewicht'			=> $item->variationBase->weightG,
			'Lieferzeit'			=> $this->lenandoHelper->getAvailability($item, $settings, false),
			'Nachnahmegebühr'		=> '',
			'MPN'				=> $item->variationBase->model,
			'Bildlink'			=> $this->getImageByNumber($item, $settings, 0),
			'Bildlink2'			=> $this->getImageByNumber($item, $settings, 1),
			'Bildlink3'			=> $this->getImageByNumber($item, $settings, 2),
			'Bildlink4'			=> $this->getImageByNumber($item, $settings, 3),
			'Bildlink5'			=> $this->getImageByNumber($item, $settings, 4),
			'Bildlink6'			=> $this->getImageByNumber($item, $settings, 5),
			'Zustand'			=> 'neu',
			'Familienname1'			=> '',
			'Eigenschaft1'			=> '',
			'Familienname2'			=> '',
			'Eigenschaft2'			=> '',
			'ID'				=> $item->variationBase->id, //$item->itemBase->id,
			'Inhalt'			=> strlen($unit) > 0 ? $basePriceContent : '',
			'Einheit'			=> $unit,
			'Freifeld1'			=> $item->itemBase->free1,
			'Freifeld2'			=> $item->itemBase->free2,
			'Freifeld3'			=> $item->itemBase->free3,
			'Freifeld4'			=> $item->itemBase->free4,
			'Freifeld5'			=> $item->itemBase->free5,
			'Freifeld6'			=> $item->itemBase->free6,
			'Freifeld7'			=> $item->itemBase->free7,
			'Freifeld8'			=> $item->itemBase->free8,
			'Freifeld9'			=> $item->itemBase->free9,
			'Freifeld10'			=> $item->itemBase->free10,
			'baseid'			=> '',
			'basename'			=> '',
			'level'				=> '0',
			'status'			=> $variationAvailable,
			'external_categories'		=> '1', //$item->variationStandardCategory->categoryId,
			'base'				=> '3',
			'dealer_price'			=> '',
			'link'				=> '',
			'ASIN'				=> '',
			'Mindestabnahme'		=> '',
			'Maximalabnahme'		=> '',
			'Abnahmestaffelung'		=> '',
		];

		$this->addCSVContent(array_values($data));
		
	}
	

	/**
	 * @param Record $item
	 * @param KeyValue $settings
     * @param array $attributeName
	 * @return void
	 */
	private function buildParentWithChildrenRow(Record $item, KeyValue $settings, array<int, mixed> $attributeName):void
	{

		if($item->variationBase->limitOrderByStockSelect == 2)
		{
			$variationAvailable = 1;
			$stock = 999;
		}
		elseif($item->variationBase->limitOrderByStockSelect == 1 && $item->variationStock->stockNet > 0)
		{
			$variationAvailable = 1;
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				$stock = $item->variationStock->stockNet;
			}
		}
		elseif($item->variationBase->limitOrderByStockSelect == 0)
		{
			$variationAvailable = 1;
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				if($item->variationStock->stockNet > 0)
				{
					$stock = $item->variationStock->stockNet;
				}
				else
				{
					$stock = 0;
				}
			}
		}
		else
		{
			$variationAvailable = 0;
			$stock = 0;
		}


        if($item->variationBase->limitOrderByStockSelect == 2)
        {
            $inventoryManagementActive = 0;
        }
        elseif($item->variationBase->limitOrderByStockSelect == 1 && $item->variationStock->stockNet > 0)
        {
            $inventoryManagementActive = 1;
        }
        elseif($item->variationBase->limitOrderByStockSelect == 0)
        {
            $inventoryManagementActive = 0;
        }
        else
        {
            $inventoryManagementActive = 1;
        }
        
        $vat = $item->variationBase->vatId;
		if($vat == '19')
		{
			$vat = 19;
		}
		else if($vat == '7')
		{
			$vat = 7;
		}
		else
		{
			$vat = 19;
		}
		
	
		$rrp = $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) > $this->lenandoHelper->getPrice($item) ? $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) : $this->lenandoHelper->getPrice($item);
		$price = $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) > $this->lenandoHelper->getPrice($item) ? $this->lenandoHelper->getPrice($item) : $this->lenandoHelper->getRecommendedRetailPrice($item, $settings);
		$price = $price > 0 ? $price : '';
		$unit = $this->getUnit($item, $settings);
		$basePriceContent = (int)$item->variationBase->content;
		
		

		$data = [
			'Produktname'			=> $this->lenandoHelper->getName($item, $settings, 150),
			'Artikelnummer'			=> $item->itemBase->id,
			'ean'				=> $this->lenandoHelper->getBarcodeByType($item, $settings->get('barcode')),
			'Hersteller'			=> $item->itemBase->producer,
			'Steuersatz'			=> $vat,
			'Preis'				=> number_format($rrp, 2, '.', ''),
			'Kurzbeschreibung'		=> '',
			'Beschreibung'			=> $this->lenandoHelper->getDescription($item, $settings, 5000),
			'Versandkosten'			=> '',
			'Lagerbestand'			=> $stock,
			'Kategoriestruktur'		=> '',
			'Attribute'			=> '',
			'Gewicht'			=> $item->variationBase->weightG,
			'Lieferzeit'			=> $this->lenandoHelper->getAvailability($item, $settings, false),
			'Nachnahmegebühr'		=> '',
			'MPN'				=> $item->variationBase->model,
			'Bildlink'			=> $this->getImageByNumber($item, $settings, 0),
			'Bildlink2'			=> $this->getImageByNumber($item, $settings, 1),
			'Bildlink3'			=> $this->getImageByNumber($item, $settings, 2),
			'Bildlink4'			=> $this->getImageByNumber($item, $settings, 3),
			'Bildlink5'			=> $this->getImageByNumber($item, $settings, 4),
			'Bildlink6'			=> $this->getImageByNumber($item, $settings, 5),
			'Zustand'			=> 'neu',
			'Familienname1'			=> '',
			'Eigenschaft1'			=> '',
			'Familienname2'			=> '',
			'Eigenschaft2'			=> '',
			'ID'				=> $item->variationBase->id, //$item->itemBase->id,
			'Inhalt'			=> strlen($unit) > 0 ? $basePriceContent : '',
			'Einheit'			=> $unit,
			'Freifeld1'			=> $item->itemBase->free1,
			'Freifeld2'			=> $item->itemBase->free2,
			'Freifeld3'			=> $item->itemBase->free3,
			'Freifeld4'			=> $item->itemBase->free4,
			'Freifeld5'			=> $item->itemBase->free5,
			'Freifeld6'			=> $item->itemBase->free6,
			'Freifeld7'			=> $item->itemBase->free7,
			'Freifeld8'			=> $item->itemBase->free8,
			'Freifeld9'			=> $item->itemBase->free9,
			'Freifeld10'			=> $item->itemBase->free10,
			'baseid'			=> '',
			'basename'			=> '',
			'level'				=> '0',
			'status'			=> $variationAvailable,
			'external_categories'		=> '1', //$item->variationStandardCategory->categoryId,
			'base'				=> '1',
			'dealer_price'			=> '',
			'link'				=> '',
			'ASIN'				=> '',
			'Mindestabnahme'		=> '',
			'Maximalabnahme'		=> '',
			'Abnahmestaffelung'		=> '',
		];

		$this->addCSVContent(array_values($data));
		

	}
	

	/**
	 * @param Record $item
	 * @param KeyValue $settings
     * @param string $attributeValue
	 * @return void
	 */


	private function buildChildRow(Record $item, KeyValue $settings, string $attributeValue = ''):void
	{
		if($item->variationBase->limitOrderByStockSelect == 2)
		{
			$variationAvailable = 1;
			$stock = 999;
		}
		elseif($item->variationBase->limitOrderByStockSelect == 1 && $item->variationStock->stockNet > 0)
		{
			$variationAvailable = 1;
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				$stock = $item->variationStock->stockNet;
			}
		}
		elseif($item->variationBase->limitOrderByStockSelect == 0)
		{
			$variationAvailable = 1;
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				if($item->variationStock->stockNet > 0)
				{
					$stock = $item->variationStock->stockNet;
				}
				else
				{
					$stock = 0;
				}
			}
		}
		else
		{
			$variationAvailable = 0;
			$stock = 0;
		}

		$vat = $item->variationBase->vatId;
		if($vat == '19')
		{
			$vat = 19;
		}
		else if($vat == '7')
		{
			$vat = 7;
		}
		else
		{
			$vat = 19;
		}
		
		

		$rrp = $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) > $this->lenandoHelper->getPrice($item) ? $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) : $this->lenandoHelper->getPrice($item);
		$price = $this->lenandoHelper->getRecommendedRetailPrice($item, $settings) > $this->lenandoHelper->getPrice($item) ? $this->lenandoHelper->getPrice($item) : $this->lenandoHelper->getRecommendedRetailPrice($item, $settings);
		$price = $price > 0 ? $price : '';

		$unit = $this->getUnit($item, $settings);
		$basePriceContent = (int)$item->variationBase->content;

		$data = [
			'Produktname'			=> $this->lenandoHelper->getName($item, $settings, 150),
			'Artikelnummer'			=> $item->itemBase->id,
			'ean'				=> $this->lenandoHelper->getBarcodeByType($item, $settings->get('barcode')),
			'Hersteller'			=> $item->itemBase->producer,
			'Steuersatz'			=> $vat,
			'Preis'				=> number_format($rrp, 2, '.', ''),
			'Kurzbeschreibung'		=> '',
			'Beschreibung'			=> $this->lenandoHelper->getDescription($item, $settings, 5000),
			'Versandkosten'			=> '',
			'Lagerbestand'			=> $stock,
			'Kategoriestruktur'		=> '',
			'Attribute'			=> '',
			'Gewicht'			=> $item->variationBase->weightG,
			'Lieferzeit'			=> $this->lenandoHelper->getAvailability($item, $settings, false),
			'Nachnahmegebühr'		=> '',
			'MPN'				=> $item->variationBase->model,
			'Bildlink'			=> $this->getImageByNumber($item, $settings, 0),
			'Bildlink2'			=> $this->getImageByNumber($item, $settings, 1),
			'Bildlink3'			=> $this->getImageByNumber($item, $settings, 2),
			'Bildlink4'			=> $this->getImageByNumber($item, $settings, 3),
			'Bildlink5'			=> $this->getImageByNumber($item, $settings, 4),
			'Bildlink6'			=> $this->getImageByNumber($item, $settings, 5),
			'Zustand'			=> 'neu',
			'Familienname1'			=> '',
			'Eigenschaft1'			=> '',
			'Familienname2'			=> '',
			'Eigenschaft2'			=> '',
			'ID'				=> $item->variationBase->id,
			'Inhalt'			=> strlen($unit) > 0 ? $basePriceContent : '',
			'Einheit'			=> $unit,
			'Freifeld1'			=> $item->itemBase->free1,
			'Freifeld2'			=> $item->itemBase->free2,
			'Freifeld3'			=> $item->itemBase->free3,
			'Freifeld4'			=> $item->itemBase->free4,
			'Freifeld5'			=> $item->itemBase->free5,
			'Freifeld6'			=> $item->itemBase->free6,
			'Freifeld7'			=> $item->itemBase->free7,
			'Freifeld8'			=> $item->itemBase->free8,
			'Freifeld9'			=> $item->itemBase->free9,
			'Freifeld10'			=> $item->itemBase->free10,
			'baseid'			=> $item->itemBase->id,
			'basename'			=> $this->lenandoHelper->getAttributeName($item, $settings),
			'level'				=> '0',
			'status'			=> $variationAvailable,
			'external_categories'		=> '1', //$item->variationStandardCategory->categoryId,
			'base'				=> '3',
			'dealer_price'			=> '',
			'link'				=> '',
			'ASIN'				=> '',
			'Mindestabnahme'		=> '',
			'Maximalabnahme'		=> '',
			'Abnahmestaffelung'		=> '',
		];

		$this->addCSVContent(array_values($data));
	}

	// gibt das jeweilige Bild
	private function getImageByNumber(Record $item, KeyValue $settings, int $number):string
	{
		$imageList = $this->lenandoHelper->getImageList($item, $settings);
		
		if(count($imageList) > 0 && array_key_exists($number, $imageList))
		{
			return $imageList[$number];
		}
		else
		{
			return '';
		}
	}

	// Wandelt die Einheitsangaben um
	private function getUnit(Record $item, KeyValue $settings):string
	{
		$unit = $this->lenandoHelper->getBasePriceDetailUnit($item, $settings);

		switch($unit)
		{
			case 'MLT':
				return 'ml'; // Milliliter
			case 'LTR':
				return 'l'; // Liter
			case 'GRM':
				return 'g'; // Gramm
			case 'KGM':
				return 'kg'; // Kilogramm
			case 'CTM':
				return 'cm'; // Zentimeter
			case 'MTR':
				return 'm'; // Meter
			case 'MTK':
				return 'm²'; // Quadratmeter
			default:
				return '';
		}
	}
}
