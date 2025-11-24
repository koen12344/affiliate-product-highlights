import {createRoot} from "@wordpress/element";
import ClearThumbCacheButton from "./components/ClearThumbCacheButton";
import './admin.scss';
import domReady from '@wordpress/dom-ready';

domReady( () => {
	const container = document.getElementById( 'prfr-clear-thumbnail-cache' );
	const root = createRoot(container);
	root.render(<ClearThumbCacheButton />);
});

