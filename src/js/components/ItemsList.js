import {useState} from "@wordpress/element";
import {CheckboxControl, Popover} from "@wordpress/components";
import {__} from "@wordpress/i18n";

export default function ItemsList({ items, isSaving, selection, setSelection }){

	const [ isVisible, setVisible ] = useState({});
	const toggleVisible = (id) => {
		setVisible( prevVisibility => ({
			...prevVisibility,
			[id]: !prevVisibility[id] // Toggle the visibility for the specific ID
		}));
	};

	const handleCheckbox = (checked, item) =>{
		if(checked){
			setSelection({...selection, [item]: true});
		}else{
			const updatedLocations = {...selection};
			delete updatedLocations[item];
			setSelection(updatedLocations);
		}
	}

	return (
		<table className="wp-list-table widefat fixed striped table-view-list">
			<thead>
			<tr>
				<th className="manage-column column-cb check-column"></th>
				<th>ID</th>
				<th>{__("Title", 'affiliate-product-highlights')}</th>
				<th>{__('Price', 'affiliate-product-highlights')}</th>
				<th>{__('Feed', 'affiliate-product-highlights')}</th>
			</tr>
			</thead>
			<tbody>
			{items?.map(item => (
				<tr key={item.id}>
					<td><CheckboxControl
						onChange={ (checked)=>handleCheckbox(checked, item.id) }
						checked={ selection[item.id] === true }
						disabled={isSaving}
					/></td>
					<td>{item.id}</td>
					<td><a href="#" onMouseOut={() => toggleVisible(item.id)} onMouseEnter={ () => toggleVisible(item.id) }>{item.product_name}</a>{ isVisible[item.id] && <Popover><img src={item.image_url} width={200} /></Popover> }</td>
					<td>{item.product_price}</td>
					<td><a href={item.feed_url}>{item.feed_name}</a></td>
				</tr>
			))}
			</tbody>
		</table>
)
}
