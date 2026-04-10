import React from 'react';
import { __ } from '@wordpress/i18n';

const Divi5Notice = () => {
	return (
		<div className="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 mb-6 shadow-lg">
			<div className="flex items-start gap-6">
				{/* Icon */}
				<div className="flex-shrink-0">
					<div className="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
						<svg
							className="w-8 h-8 text-white"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24"
						>
							<path
								strokeLinecap="round"
								strokeLinejoin="round"
								strokeWidth={2}
								d="M13 10V3L4 14h7v7l9-11h-7z"
							/>
						</svg>
					</div>
				</div>

				{/* Content */}
				<div className="flex-1">
					<div className="flex items-center gap-2 mb-2">
						<h3 className="text-xl font-bold text-white">
							{__(
								'Divi 5 Compatibility – Coming Soon',
								'addons-for-divi'
							)}
						</h3>
						<span className="inline-flex items-center px-3 py-1 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">
							{__('BETA SOON', 'addons-for-divi')}
						</span>
					</div>
					<p className="text-white/90 text-sm mb-4 leading-relaxed">
						{__(
							"We're rebuilding all modules for Divi 5's new React-based framework. Help us prioritize development by voting for the modules you use most!",
							'addons-for-divi'
						)}
					</p>

					{/* Action Buttons */}
					<div className="flex items-center gap-3">
						<a
							href="https://divitorque.com/divi5-roadmap/?utm_source=divi-torque-lite&utm_medium=dashboard&utm_campaign=divi5-roadmap"
							target="_blank"
							rel="noopener noreferrer"
							className="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold text-sm rounded-lg hover:bg-indigo-50 transition-all duration-200 shadow-md hover:shadow-lg"
						>
							<svg
								className="w-5 h-5 mr-2"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
							</svg>
							{__(
								'Vote for Your Favorite Modules',
								'addons-for-divi'
							)}
						</a>
						<a
							href="https://divitorque.com/divi5-roadmap/?utm_source=divi-torque-lite&utm_medium=dashboard&utm_campaign=divi5-roadmap"
							target="_blank"
							rel="noopener noreferrer"
							className="inline-flex items-center text-white/90 hover:text-white text-sm font-medium transition-colors"
						>
							{__('View Roadmap', 'addons-for-divi')}
							<svg
								className="w-4 h-4 ml-1"
								fill="none"
								stroke="currentColor"
								viewBox="0 0 24 24"
							>
								<path
									strokeLinecap="round"
									strokeLinejoin="round"
									strokeWidth={2}
									d="M9 5l7 7-7 7"
								/>
							</svg>
						</a>
					</div>
				</div>

				{/* Progress Indicator */}
				<div className="flex-shrink-0 text-center">
					<div className="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-xl flex flex-col items-center justify-center mb-2">
						<div className="text-2xl font-bold text-white">9</div>
						<div className="text-xs text-white/80 uppercase tracking-wide">
							{__('In Progress', 'addons-for-divi')}
						</div>
					</div>
					<div className="text-xs text-white/70">
						{__('1 Done', 'addons-for-divi')}
					</div>
				</div>
			</div>
		</div>
	);
};

export default Divi5Notice;
