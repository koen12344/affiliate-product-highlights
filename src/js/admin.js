import {createRoot} from "@wordpress/element";
import ClearThumbCacheButton from "./components/ClearThumbCacheButton";
import './admin.scss';

// window.addEventListener(
// 	'load',
// 	function () {
//
//
// 	},
// 	false
// );

const container = document.getElementById( 'phft-clear-thumbnail-cache' );
const root = createRoot(container);
root.render(<ClearThumbCacheButton />);
