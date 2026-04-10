import { __ } from '@wordpress/i18n';
import ModuleCard from './module-card';
import { Toast, Divi5Notice } from '@DashboardComponents';
import { useDispatch, useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';

const Modules = () => {
	const {
		module_info: allLiteModules = [],
		pro_module_info: allProModules = [],
		is_lite_installed,
	} = window.diviTorqueLite || {};

	const allModules = [...allLiteModules, ...allProModules];

	const allModulesStatuses = useSelect((select) =>
		select('divitorque/dashboard').getModulesStatuses()
	);

	const [allEnabled, setAllEnabled] = useState(false);
	const [allDisabled, setAllDisabled] = useState(false);
	const [filter, setFilter] = useState('all');
	const [isLoading, setIsLoading] = useState(false);

	useEffect(() => {
		const liteModuleNames = allLiteModules.map((module) => module.name);
		const liteStatuses = Object.entries(allModulesStatuses)
			.filter(([key]) => liteModuleNames.includes(key))
			.map(([_, value]) => value);

		setAllDisabled(liteStatuses.every((status) => status === 'disabled'));
		setAllEnabled(liteStatuses.every((status) => status !== 'disabled'));
	}, [allModulesStatuses, allLiteModules]);

	// Sort modules based on the 'new' badge and title
	const sortedModules = allModules.sort((a, b) => {
		if (a.badge === 'new' && b.badge !== 'new') return -1;
		if (a.badge !== 'new' && b.badge === 'new') return 1;
		return a.title.localeCompare(b.title);
	});

	const proModules = sortedModules.filter((module) => module.is_pro);
	const liteModules = sortedModules.filter((module) => !module.is_pro);

	const dispatch = useDispatch('divitorque/dashboard');

	const getFilteredModules = () => {
		// Only show lite modules - no pro placeholders
		return filter === 'lite' ? liteModules : liteModules;
	};

	const toggleModuleStatus = async (status) => {
		if (isLoading) return;
		setIsLoading(true);

		const updatedStatuses = { ...allModulesStatuses };
		liteModules.forEach((module) => {
			updatedStatuses[module.name] = status ? module.name : 'disabled';
		});

		try {
			const res = await wp.apiFetch({
				path: '/divitorque-lite/v1/save_common_settings',
				method: 'POST',
				data: { modules_settings: updatedStatuses },
			});

			if (res.success) {
				dispatch.updateModuleStatuses(updatedStatuses);
				Toast(__('Successfully saved!', 'divitorque'), 'success');
			} else {
				Toast(__('Something went wrong!', 'divitorque'), 'error');
			}
		} catch (err) {
			Toast(err.message, 'error');
		} finally {
			setIsLoading(false);
		}
	};

	const renderModules = () => {
		return getFilteredModules().map((module, index) => (
			<ModuleCard
				key={module.name || index}
				moduleInfo={module}
				isLiteInstalled={is_lite_installed}
			/>
		));
	};

	return (
		<div className="divitorque-app">
			{/* Divi 5 Roadmap Notice */}
			<Divi5Notice />

			{/* Black Friday Compact Banner */}
			<div className="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl p-4 mb-6 shadow-lg">
				<div className="flex items-center justify-between">
					<div className="flex items-center gap-4">
						<div className="flex items-center gap-2">
							<svg
								className="w-5 h-5 text-yellow-300"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
							</svg>
							<div>
								<div className="text-white font-bold text-lg">
									{__(
										'Black Friday: 50% OFF',
										'addons-for-divi'
									)}
								</div>
								<div className="text-white/90 text-xs">
									{__(
										'Divi Torque Pro - $89.50 (was $179)',
										'addons-for-divi'
									)}
								</div>
							</div>
						</div>
					</div>
					<a
						href="https://divitorque.com/black-friday/?utm_source=divi-torque-lite&utm_medium=dashboard&utm_campaign=black-friday"
						target="_blank"
						rel="noopener noreferrer"
						className="px-6 py-2.5 bg-white text-purple-600 font-bold text-sm rounded-lg hover:bg-purple-50 transition-all shadow-md hover:shadow-lg"
					>
						{__('Get 50% OFF →', 'addons-for-divi')}
					</a>
				</div>
			</div>

			{/* Main Content Area - Full Width */}
			<div className="bg-white rounded-lg shadow-sm border border-gray-200">
				{/* Header */}
				<div className="px-6 py-4 border-b border-gray-200">
					<div className="flex items-center justify-between">
						<div>
							<h2 className="text-lg font-semibold text-gray-900">
								{__('Modules', 'addons-for-divi')}
							</h2>
							<p className="text-sm text-gray-500 mt-0.5">
								{__(
									'Manage your Divi Torque modules',
									'addons-for-divi'
								)}
							</p>
						</div>

						{/* Action Buttons */}
						<div className="flex items-center gap-2">
							<button
								type="button"
								className={`px-4 py-2 text-sm font-medium rounded-md transition-colors ${
									allDisabled || isLoading
										? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-400'
										: 'text-gray-700 hover:bg-gray-100'
								}`}
								onClick={() => toggleModuleStatus(false)}
								disabled={allDisabled || isLoading}
							>
								{isLoading
									? __('Processing...', 'addons-for-divi')
									: __('Disable all', 'addons-for-divi')}
							</button>
							<button
								type="button"
								className={`px-4 py-2 text-sm font-medium rounded-md transition-colors ${
									allEnabled || isLoading
										? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-500'
										: 'bg-indigo-600 text-white hover:bg-indigo-700'
								}`}
								onClick={() => toggleModuleStatus(true)}
								disabled={allEnabled || isLoading}
							>
								{isLoading
									? __('Processing...', 'addons-for-divi')
									: __('Enable all', 'addons-for-divi')}
							</button>
						</div>
					</div>
				</div>

				{/* Modules Grid - 5 columns */}
				<div className="p-6">
					<div className="grid grid-cols-5 gap-3">
						{renderModules()}
					</div>
				</div>
			</div>

			{/* Upgrade CTA - Minimal Footer */}
			<div className="mt-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-5 border border-purple-100">
				<div className="flex items-center justify-between">
					<div>
						<h4 className="font-bold text-gray-900 mb-1">
							{__('Need More Power?', 'addons-for-divi')}
						</h4>
						<p className="text-sm text-gray-600">
							{__(
								'Unlock 50+ PRO modules, extensions, and priority support',
								'addons-for-divi'
							)}
						</p>
					</div>
					<a
						href="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=dashboard&utm_campaign=upgrade"
						target="_blank"
						rel="noopener noreferrer"
						className="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold text-sm rounded-lg hover:from-purple-700 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg whitespace-nowrap"
					>
						{__('View Pro Features', 'addons-for-divi')}
					</a>
				</div>
			</div>
		</div>
	);
};

export default Modules;
