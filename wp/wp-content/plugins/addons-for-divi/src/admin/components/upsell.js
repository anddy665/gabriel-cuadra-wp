import React from 'react';
import { __ } from '@wordpress/i18n';

const UpsellSection = ({ heading, points, upgradeLink, videoLink }) => {
	return (
		<div className="max-w-6xl mt-10 mx-auto bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
			<div className="flex flex-col lg:flex-row">
				{/* Left Content Section */}
				<div className="lg:w-2/3 p-8 lg:p-12">
					<div className="mb-6">
						<h2 className="text-3xl lg:text-4xl font-bold text-gray-900 mb-2 leading-tight">
							{heading}
						</h2>
						<div className="w-20 h-1 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full"></div>
					</div>

					<ul className="mb-8 space-y-4">
						{points.map((point, index) => (
							<li
								key={index}
								className="flex items-start space-x-3 group"
							>
								<div className="flex-shrink-0 w-6 h-6 bg-gradient-to-r from-green-400 to-green-500 rounded-full flex items-center justify-center mt-0.5">
									<svg
										className="w-3 h-3 text-white"
										fill="currentColor"
										viewBox="0 0 20 20"
									>
										<path
											fillRule="evenodd"
											d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
											clipRule="evenodd"
										/>
									</svg>
								</div>
								<p className="text-gray-700 text-lg leading-relaxed group-hover:text-gray-900 transition-colors">
									{point}
								</p>
							</li>
						))}
					</ul>

					<div className="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-6">
						<div className="relative">
							<span className="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-bold rounded-full shadow-lg transform hover:scale-105 transition-transform duration-200">
								<svg
									className="w-4 h-4 mr-2"
									fill="currentColor"
									viewBox="0 0 20 20"
								>
									<path
										fillRule="evenodd"
										d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
										clipRule="evenodd"
									/>
								</svg>
								{__(
									'Black Friday - 50% OFF!',
									'addons-for-divi'
								)}
							</span>
						</div>

						<a
							href={upgradeLink}
							className="inline-flex items-center px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold text-lg rounded-lg shadow-lg hover:from-blue-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:text-white"
							target="_blank"
							rel="noopener noreferrer"
						>
							<svg
								className="w-5 h-5 mr-2"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path
									fillRule="evenodd"
									d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
									clipRule="evenodd"
								/>
							</svg>
							{__('Upgrade to Pro', 'addons-for-divi')}
						</a>
					</div>
				</div>

				{/* Right Visual Section */}
				<div className="lg:w-1/3 bg-gradient-to-br from-blue-50 to-purple-50 p-8 lg:p-12 flex items-center justify-center">
					<div className="text-center">
						<div className="w-24 h-24 mx-auto mb-6 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
							<svg
								className="w-12 h-12 text-white"
								fill="currentColor"
								viewBox="0 0 20 20"
							>
								<path
									fillRule="evenodd"
									d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
									clipRule="evenodd"
								/>
							</svg>
						</div>
						<h3 className="text-xl font-semibold text-gray-800 mb-2">
							{__('Pro Features', 'addons-for-divi')}
						</h3>
						<p className="text-gray-600 text-sm leading-relaxed">
							{__(
								'Unlock advanced functionality and take your website to the next level with our premium features.',
								'addons-for-divi'
							)}
						</p>
					</div>
				</div>
			</div>
		</div>
	);
};

export default UpsellSection;
