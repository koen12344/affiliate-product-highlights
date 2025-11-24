<?php

namespace Koen12344\ProductFrame\Provider\AdTraction;

use Koen12344\ProductFrame\Provider\ProductMapInterface;
use SimpleXMLElement;

class ProductMapping implements ProductMapInterface {
	/**
	 * @var SimpleXMLElement
	 */
	private $product_xml;

	public function __construct( SimpleXMLElement $product_xml ) {
		$this->product_xml = $product_xml;
	}

	public function get_product_mapping(): array {
		return [
			'sku'                       => (string)$this->product_xml->SKU,
			'product_name'              => (string)$this->product_xml->Name,
			'product_price'             => number_format((float)$this->product_xml->Price, 2,'.', ''),
			'product_original_price'    => number_format((float)$this->product_xml->OriginalPrice, 2, '.', ''),
			'product_currency'          => (string)$this->product_xml->Currency,
			'product_url'               => sanitize_url((string)$this->product_xml->TrackingUrl),
			'product_description'       => (string)$this->product_xml->Description,
			'product_ean'               => (string)$this->product_xml->Ean,
		];
	}

	public function get_product_images(): array {
		return [
			$this->product_xml->ImageUrl,
		];
	}
}
