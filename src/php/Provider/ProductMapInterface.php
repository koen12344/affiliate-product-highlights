<?php

namespace Koen12344\AffiliateProductHighlights\Provider;

use SimpleXMLElement;

interface ProductMapInterface {

	/**
	 * @param $product_xml SimpleXMLElement XML of a singular product within a feed
	 */
	public function __construct(SimpleXMLElement $product_xml);

	/**
	 * Get normalized array mapping.
	 *
	 * @return array:
	 *                 - 'campaign_id': int|null
	 *                 - 'product_id': int|null
	 *                 - 'sku': string|null
	 *                 - 'product_name': string
	 *                 - 'product_price': float
	 *                 - 'product_currency': string
	 *                 - 'product_url': string
	 *                 - 'product_description': string
	 *                 - 'product_ean': string|null
	 */
	public function get_product_mapping(): array;

	/**
	 * Array with one or more product image URLs
	 *
	 * @return array
	 */
	public function get_product_images(): array;

}
