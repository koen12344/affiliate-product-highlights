import {useEffect, useState} from "@wordpress/element";
import apiFetch from '@wordpress/api-fetch';
import {TextControl} from "@wordpress/components";
import {addQueryArgs} from "@wordpress/url";
import {useDebounce} from "@wordpress/compose";
import {__} from "@wordpress/i18n";
import ItemsListDataViews from "./ItemsListDataViews";


const { selection_id } = psfg_localize_metabox;

export default function SelectionMetabox(){

	const [selection, setSelection ] = useState({});

	const [isSaving, setSaving] = useState(false);



	const saveSelection = () => {
		setSaving(true);
		apiFetch({path:'/prfr/v1/selection', method:'POST', data: {'selection_id': selection_id, 'selection': selection}}).finally(()=> setSaving(false));
	}



	const debouncedSaveSelection = useDebounce(saveSelection, 500);


	useEffect(() => {
		debouncedSaveSelection();
	}, [selection]);



	useEffect(() => {
		setSaving(true);
		apiFetch({path:addQueryArgs('/prfr/v1/selection', {'selection_id': selection_id})}).then((data) =>{
			setSelection({...selection, ...data});
		}).finally(() => setSaving(false));
	}, []);

	return (
		<>
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
