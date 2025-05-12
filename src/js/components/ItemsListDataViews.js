import {DataViews} from "@wordpress/dataviews";
import {useEffect, useMemo, useState} from "@wordpress/element";
import {Icon} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import {addQueryArgs} from "@wordpress/url";
import {check} from "@wordpress/icons";

export default function ItemsListDataViews( { itemSelection, setItemSelection }){

	const [items, setItems] = useState([]);
	const [isLoading, setIsLoading] = useState(false);
	const [paginationInfo, setPaginationInfo ] = useState({});

	const [selection, setSelection ] = useState([] );

	const fields = [
		{
			id: 'in_selection',
			label: 'In selection',
			// getValue: ({ item }) => !!selection[item.id],
			render: ( { item } ) => (
				!!itemSelection[item.id] && <Icon icon={ check } />
			),
			enableHiding: true,
			elements: [
				{
					value: true,
					label: "Yes"
				},
				{
					value: false,
					label: "No"
				},
			],
			filterBy: {
				operators: ['is']
			}
		},
		{
			id: 'product_name',
			label: 'Product Name',
			enableHiding: false,
		},
		{
			id: 'product_price',
			label: 'Price',
			enableHiding: true,
		},
		{
			id: 'product_description',
			label: 'Description',
			enableHiding: false,
		},
		{
			id: 'feed',
			label: 'Feed',
			render: ({item}) => (
				<a target="_blank" href={ item.feed_url }>{item.feed_name}</a>
			)
		},
		{
			id: 'image_url',
			label: 'image',
			enableHiding: false,
			render: ( { item } ) => (
				<img alt={ item.product_name } src={ item.image_url } width="50" height="50" />
			),
		}
	];


	const [ view, setView ] = useState( {
		type: 'table',
		perPage: 10,
		page: 1,
		sort: {
			field: 'product_name',
			direction: 'desc',
		},
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
		fields: [ 'in_selection', 'status' ],
		layout: {},
	} );


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
			label: __( 'Add to selection' ),
			isPrimary: true,
			icon: 'plus',
			supportsBulk: true,
			callback: ( selectedItems ) => {
				const itemstoAdd = {};
				selectedItems.forEach( ( item ) => {
					// console.log( `Image to upload: ${ item.id }` );
					itemstoAdd[item.id] = true;
				});
				setItemSelection({...itemSelection, ...itemstoAdd});
				console.log(itemSelection);
			},
		},
		{
			id: 'remove-from-selection',
			label: __( 'Remove from selection' ),
			isPrimary: true,
			icon: 'minus',
			supportsBulk: true,
			callback: ( selectedItems ) => {
				const newSelection = { ...itemSelection };
				selectedItems.forEach( ( item ) => {
					delete newSelection[item.id];
				});
				setItemSelection(newSelection);
				console.log(newSelection);
			},
		},
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
			path: addQueryArgs('/phft/v1/items', {
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
			onChangeSelection={( items ) => { setSelection(items); console.log(items); }}
		/>
	);
};
