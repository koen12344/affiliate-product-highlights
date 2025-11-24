import {DataViews} from "@wordpress/dataviews";
import {useEffect, useMemo, useRef, useState} from "@wordpress/element";
import {ExternalLink, Icon} from "@wordpress/components";
import {getLocaleData, __} from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {addQueryArgs} from "@wordpress/url";
import {check, notFound, copy } from "@wordpress/icons";

import { useSelect } from '@wordpress/data';
import { store as coreDataStore } from '@wordpress/core-data';

const { userView } = psfg_localize_metabox;
const localeData = getLocaleData();
const locale = localeData[""]?.lang || 'en-US';
const formatprice = (price, currency) => {
	return new Intl.NumberFormat(locale, {
		style: 'currency',
		currency: currency,
	}).format(parseFloat(price));
}
export default function ItemsListDataViews( { itemSelection, setItemSelection }){

	const [items, setItems] = useState([]);
	const [isLoading, setIsLoading] = useState(false);
	const [paginationInfo, setPaginationInfo ] = useState({});

	const [selection, setSelection ] = useState([] );

	const feeds = useSelect(
		select =>
			select( coreDataStore ).getEntityRecords( 'postType', 'prfr-feeds' ),
		[]
	);

	const fields = [
		{
			id: 'in_selection',
			label: __('In selection', 'productframe'),
			// getValue: ({ item }) => !!selection[item.id],
			render: ( { item } ) => (
				!!itemSelection[item.id] && <Icon icon={ check } />
			),
			enableHiding: true,
			elements: [
				{
					value: true,
					label: __('Yes', 'productframe'),
				},
				{
					value: false,
					label: __('No', 'productframe'),
				},
			],
			filterBy: {
				operators: ['is']
			},
			enableSorting: false,
		},
		{
			id: 'in_latest_import',
			label: __('In latest import', 'productframe'),
			enableHiding: true,
			render: ( { item } ) => (
				item.in_latest_import === "1" && <Icon icon={ check } /> || <Icon icon={ notFound } />
			),
			elements: [
				{
					value: true,
					label: __('Yes', 'productframe'),
				},
				{
					value: false,
					label: __('No', 'productframe'),
				},
			],
			filterBy: {
				operators: ['is']
			}
		},
		{
			id: 'product_name',
			label: __('Product Name', 'productframe'),
			enableHiding: false,
			render: ( { item } ) => (
				<ExternalLink target="_blank" href={item.product_url}>{item.product_name}</ExternalLink>
			),
		},
		{
			id: 'id',
			label: 'ID',
		},
		{
			id: 'product_url',
			label: 'Link',
			render: ( { item } ) => (
				<ExternalLink target="_blank" href={item.product_url}>{__('View', 'productframe')}</ExternalLink>
			),
			enableSorting: false,
		},
		{
			id: 'product_price',
			label: __('Price', 'productframe'),
			render: ( { item } ) => {
				const on_sale = parseFloat(item.product_original_price) > parseFloat(item.product_price);

				return (
					<>
						{ formatprice(item.product_price, item.product_currency) }
						{on_sale && <>&nbsp;<span className='prfr-original-price'>{ formatprice(item.product_original_price, item.product_currency) }</span></>}
					</>
				);
			},
			elements: [
				{
					value: 'on_sale',
					label: __('On sale', 'productframe'),
				},
				{
					value: 'not_on_sale',
					label: __('Not on sale', 'productframe'),
				},
			],
			filterBy: {
				operators: ['is']
			},
		},
		{
			id: 'product_description',
			label: __('Description', 'productframe'),
			enableSorting: false,
		},
		{
			id: 'feed',
			label: __('Feed', 'productframe'),
			render: ({item}) => (
				<a target="_blank" href={ item.feed_url }>{ item.feed_name }</a>
			),
			enableSorting: false,
			elements: feeds?.map(feed => ({
				label: feed.title.rendered,
				value: feed.id,
			})),
			filterBy: {
				operators: ['is']
			}
		},
		{
			id: 'image_url',
			label: __('Product image', 'productframe'),
			enableSorting: false,
			render: ( { item } ) => (
				<img alt={ item.product_name } src={ item.image_url } loading="lazy" referrerPolicy="no-referrer" width="50" height="50" />
			),
		}
	];


	const [ view, setView ] = useState( {
		type: 'table',
		perPage: 10,
		page: 1,
		// sort: {
		// 	field: 'product_name',
		// 	direction: 'desc',
		// },
		search: '',
		filters: [
			// { field: 'in_selection', operator: 'is', value: [ true, false ] },
			// {
			// 	field: 'status',
			// 	operator: 'isAny',
			// 	value: [ 'publish', 'draft' ],
			// },
		],
		titleField: 'product_name',
		mediaField: 'image_url',
		descriptionField: 'product_description',
		fields: [ 'in_selection' ],
		layout: {},
		...userView,
	} );

	const didMount = useRef(false);

	useEffect(() => {
		if(!didMount.current){
			didMount.current = true;
			return;
		}

		const { type, perPage, fields, sort } = view;
		apiFetch({
			path: addQueryArgs('/prfr/v1/view'),
			method:'POST',
			data: {view: {type, perPage, fields, sort}}

		})
			.then()
			.finally();
	}, [view.type, view.perPage, view.fields, view.sort]);


	const defaultLayouts = {
		table: {
			showMedia: true,
		},
		grid: {
			showMedia: true,
		},
	};



	const actions = [
		// {
		// 	id: 'view',
		// 	label: 'View',
		// 	isPrimary: true,
		// 	supportsBulk: true,
		// 	// icon: <Icon icon={ view } />,
		// 	isEligible: ( item ) => item.status === 'published',
		// 	callback: ( items ) => {
		// 		console.log( 'Viewing item:', items[0] );
		// 	},
		// },
		{
			id: 'add-to-selection',
			label: __( 'Add to selection', 'productframe' ),
			isPrimary: true,
			icon: 'plus',
			supportsBulk: true,
			callback: ( selectedItems ) => {
				const itemstoAdd = {};
				selectedItems.forEach( ( item ) => {
					itemstoAdd[item.id] = true;
				});
				setItemSelection({...itemSelection, ...itemstoAdd});
			},
		},
		{
			id: 'remove-from-selection',
			label: __( 'Remove from selection', 'productframe' ),
			isPrimary: true,
			icon: 'minus',
			supportsBulk: true,
			callback: ( selectedItems ) => {
				const newSelection = { ...itemSelection };
				selectedItems.forEach( ( item ) => {
					delete newSelection[item.id];
				});
				setItemSelection(newSelection);
			},
		},
		{
			id: 'copy-link-to-clipboard',
			label: __('Copy single product link shortcode', 'productframe' ),
			isPrimary: false,
			icon: copy,
			supportsBulk: false,
			callback: ([ item ]) => {
				navigator.clipboard.writeText('[prfr-link product_id=' + item.id + ']');
			},
		}
	];

	const queryArgs = useMemo(() => {
		return {
			per_page: view.perPage,
			page: view.page,
			order: view.sort?.direction,
			orderby: view.sort?.field,
			search: view.search,
			filters: view.filters,
		};
	}, [ view ]);

	useEffect(() => {
		setIsLoading(true);
		apiFetch({
			path: addQueryArgs('/prfr/v1/items', {
				...queryArgs
			}),
			method:'POST',
			data: {'selection': itemSelection}

		})
			.then(data => {
				setItems(data.items);
				setPaginationInfo(data.paginationInfo);
			})
			.finally(() => {
				setIsLoading(false);
			});
	}, [queryArgs]);

	return (
		<DataViews
			data={ items }
			fields={ fields }
			getItemId={ ( item ) => item.id }
			view={ view }
			onChangeView={ setView }
			defaultLayouts={ defaultLayouts }
			actions={ actions }
			isLoading={ isLoading }
			paginationInfo={ paginationInfo }
			selection={ selection }
			onChangeSelection={( items ) => { setSelection(items); }}
		/>
	);
};
