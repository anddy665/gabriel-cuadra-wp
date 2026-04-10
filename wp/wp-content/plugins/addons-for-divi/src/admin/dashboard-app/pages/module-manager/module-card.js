import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { ToggleControl } from '@wordpress/components';
import { Toast } from '@DashboardComponents';

const ModuleCard = ({ moduleInfo: { name, title } }) => {
	const modulesStatuses = useSelect((select) =>
		select('divitorque/dashboard').getModulesStatuses()
	);

	const dispatch = useDispatch('divitorque/dashboard');

	const isModuleActive = modulesStatuses[name] === name;

	const [isLoading, setIsLoading] = useState(false);

	const toggleModuleStatus = async () => {
		setIsLoading(true);

		const newStatus = isModuleActive ? 'disabled' : name;
		const updatedStatuses = { ...modulesStatuses, [name]: newStatus };

		wp.apiFetch({
			path: '/divitorque-lite/v1/save_common_settings',
			method: 'POST',
			data: { modules_settings: updatedStatuses },
		})
			.then((res) => {
				if (res.success) {
					dispatch.updateModuleStatuses(updatedStatuses);
					Toast(__('Successfully saved!', 'divitorque'), 'success');
				} else {
					Toast(__('Something went wrong!', 'divitorque'), 'error');
				}
			})
			.catch((err) => {
				Toast(err.message, 'error');
			})
			.finally(() => {
				setIsLoading(false);
			});
	};

	return (
		<div className="bg-white border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-all">
			<div className="flex items-center justify-between gap-3">
				<div className="text-sm font-medium text-gray-800 leading-snug flex-1">
					{title}
				</div>
				<div className="dt-toggle-control flex-shrink-0">
					<ToggleControl
						checked={isModuleActive}
						onChange={toggleModuleStatus}
						disabled={isLoading}
					/>
				</div>
			</div>
		</div>
	);
};

export default ModuleCard;
