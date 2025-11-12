import './metabox.scss';

import {createRoot} from "@wordpress/element";
import SelectionMetabox from "./components/SelectionMetabox";


const container = document.getElementById( 'phft_selection_metabox-inner' );
const root = createRoot(container);
root.render(<SelectionMetabox />);
