import ItemsList from "./ItemsList";
import {useEffect, useState} from "@wordpress/element";
import apiFetch from '@wordpress/api-fetch';
import {SearchControl, TextControl} from "@wordpress/components";
import {addQueryArgs} from "@wordpress/url";
import {useDebounce, usePrevious} from "@wordpress/compose";

const { selection_id } = psfg_localize_metabox;

export default function SelectionMetabox(){


	const [items, setItems] = useState([]);
	const [searchTerm, setSearchTerm] = useState( '' );
	const prevSearchTerm = usePrevious( searchTerm );

	const [selection, setSelection ] = useState({});

	const loadItems = () => {
		apiFetch({path: addQueryArgs('/phft/v1/items', {
				'search': searchTerm,
			})
		})
			.then(data => {
				setItems(data);
			});
	}
	const debouncedFetchData  = useDebounce(loadItems, 500);

	useEffect(() => {
		if(prevSearchTerm === undefined){
			loadItems();
		}else{
			debouncedFetchData();
		}
	}, [searchTerm]);

	const saveSelection = () => {
		apiFetch({path:'/phft/v1/selection', method:'POST', data: {'selection_id': selection_id, 'selection': selection}}).finally();
	}

	useEffect(() => {
		saveSelection();
	}, [selection]);


	useEffect(() => {
		apiFetch({path:addQueryArgs('/phft/v1/selection', {'selection_id': selection_id})}).then((data) =>{
			setSelection({...selection, ...data});
		});
	}, []);

	return (
		<>
			<SearchControl
				onChange={ setSearchTerm }
				value={ searchTerm }
			/>
			<ItemsList items={ items } selection={selection} setSelection={setSelection}/>
			<br />
			<TextControl
				label="Shortcode"
				value={"[product-highlights selection=" + selection_id + "]"}
				disabled={true}
			/>
		</>

	);
}
