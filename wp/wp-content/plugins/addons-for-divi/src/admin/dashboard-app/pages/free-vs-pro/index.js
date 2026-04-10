import React from 'react';
import { __ } from '@wordpress/i18n';

const FreeVsPro = () => {
	return (
		<div className="divitorque-app max-w-5xl mx-auto">
			<div className="text-center py-20">
				<h1 className="text-4xl font-bold text-gray-900 mb-4">
					{__('Free vs Pro', 'addons-for-divi')}
				</h1>
				<p className="text-lg text-gray-600">
					{__('Coming soon...', 'addons-for-divi')}
				</p>
			</div>
		</div>
	);
};

export default FreeVsPro;
