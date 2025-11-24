import {useEffect, useState} from "@wordpress/element";
import apiFetch from '@wordpress/api-fetch';
import {TextControl} from "@wordpress/components";
import {addQueryArgs} from "@wordpress/url";
import {useDebounce, usePrevious} from "@wordpress/compose";
import {__} from "@wordpress/i18n";
import ItemsListDataViews from "./ItemsListDataViews";


const { selection_id } = psfg_localize_metabox;

export default function SelectionMetabox(){


	const [items, setItems] = useState([]);
	const [searchTerm, setSearchTerm] = useState( '' );
	const prevSearchTerm = usePrevious( searchTerm );

	const [selection, setSelection ] = useState({});

	const [isSaving, setSaving] = useState(false);

	const loadItems = () => {
		apiFetch({path: addQueryArgs('/prfr/v1/items', {
				'search': searchTerm,
			})
		})
			.then(data => {
				setItems(data.items);
			});
	}

	const saveSelection = () => {
		setSaving(true);
		apiFetch({path:'/prfr/v1/selection', method:'POST', data: {'selection_id': selection_id, 'selection': selection}}).finally(()=> setSaving(false));
	}

	const debouncedFetchData  = useDebounce(loadItems, 500);

	const debouncedSaveSelection = useDebounce(saveSelection, 500);
	//
	// useEffect(() => {
	// 	if(prevSearchTerm === undefined){
	// 		loadItems();
	// 	}else{
	// 		debouncedFetchData();
	// 	}
	// }, [searchTerm]);

	useEffect(() => {
		debouncedSaveSelection();
	}, [selection]);
	const updateSelection = (selection) => {
		setSelection(selection);
		// debouncedSaveSelection();
	}


	useEffect(() => {
		setSaving(true);
		apiFetch({path:addQueryArgs('/prfr/v1/selection', {'selection_id': selection_id})}).then((data) =>{
			setSelection({...selection, ...data});
		}).finally(() => setSaving(false));
	}, []);

	return (
		<>
			{/*<SearchControl*/}
			{/*	onChange={ setSearchTerm }*/}
			{/*	value={ searchTerm }*/}
			{/*/>*/}
			{/*<ItemsList items={ items } isSaving={isSaving} selection={selection} setSelection={updateSelection}/>*/}
			<ItemsListDataViews itemSelection={ selection } setItemSelection={ setSelection }/>
			<br />
			<TextControl
				label={__('Shortcode', 'productframe')}
				value={"[productframe selection=" + selection_id + "]"}
				disabled={true}
			/>
		</>

	);
}
