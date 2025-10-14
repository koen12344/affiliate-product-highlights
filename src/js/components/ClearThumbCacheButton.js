import {Button} from "@wordpress/components";
import {__} from "@wordpress/i18n";
import {useState} from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";

export default function ClearThumbCacheButton( ){
	const [loading, setLoading] = useState(false);

	const handleClick = () => {
		setLoading(true);
		apiFetch({path: '/phft/v1/thumbnails', method: 'DELETE'}).finally(() => setLoading(false));
	}

	return (
		<Button
			variant="primary"
			isPrimary
			isBusy={loading}
			disabled={loading}
			onClick={handleClick}
		>
			{loading ? __('Clearing...', 'affiliate-product-highlights') : __('Clear product thumbnail cache', 'affiliate-product-highlights')}
		</Button>
	);
}
