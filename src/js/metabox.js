import {render} from "@wordpress/element";

import './style.scss';
import SelectionMetabox from "./components/SelectionMetabox";

window.addEventListener(
	'load',
	function () {
		render(
			<SelectionMetabox />,
			document.getElementById( 'phft_selection_metabox-inner' )
		);
	},
	false
);
