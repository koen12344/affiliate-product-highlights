import './metabox.scss';

import {createRoot} from "@wordpress/element";
import SelectionMetabox from "./components/SelectionMetabox";
import domReady from "@wordpress/dom-ready";

domReady( () => {
	const container = document.getElementById('prfr_selection_metabox-inner');
	const root = createRoot(container);
	root.render(<SelectionMetabox/>);
});
