<?php

namespace Koen12344\ProductFrame\Provider\Daisycon;

use Koen12344\ProductFrame\Provider\ProductMapInterface;
use SimpleXMLElement;

class ProductMapping implements ProductMapInterface {
	/**
	 * @var SimpleXMLElement
	 */
	private $product_xml;

	/**
	 * @inheritDoc
	 */
	public function __construct( SimpleXMLElement $product_xml ) {
		$this->product_xml = $product_xml;
	}

	/**
	 * @inheritDoc
	 */
	public function get_product_mapping(): array {
		return [
			'sku'                       => (string)$this->product_xml->sku,
			'product_name'              => (string)$this->product_xml->title,
			'product_price'             => number_format((float)$this->product_xml->price, 2,'.', ''),
			'product_original_price'    => number_format((float)$this->product_xml->price_old, 2,'.', ''),
			'product_currency'          => (string)$this->product_xml->currency,
			'product_url'               => sanitize_url((string)$this->product_xml->link),
			'product_description'       => (string)$this->product_xml->description,
			'product_ean'               => (string)$this->product_xml->ean,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_product_images(): array {
		return array_map(function($image) {
			return (string)$image->image->location;
		}, iterator_to_array($this->product_xml->images));
	}
}
