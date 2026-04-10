import React from 'react';
import { useLocation } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import { UpsellSection } from '@DashboardComponents';

// Import your pages/components
import Modules from './pages/module-manager';
import FreeVsPro from './pages/free-vs-pro';
const Popups = () => (
	<UpsellSection
		heading="Create Popups"
		points={[
			'A Blank Canvas For Your Creations',
			'Determine the visitor’s activities that make the visitor qualify for a specific popup.',
			'Choose the pages you want to have the popup load on - based on a specific page, publish date, categories, and more.',
			'Set rules for the popup display, such as after a certain amount of time, page visits, scroll depth, and more.',
		]}
		upgradeLink="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button"
		videoLink=""
	/>
);

const GoogleReviews = () => (
	<UpsellSection
		heading="Display Google Reviews"
		points={[
			'All connected Google reviews are automatically updated',
			'Display reviews on any page, post, or custom page',
			'Choose the number of reviews to display',
			'Responsive and really cool Slider, Grid and List layouts',
		]}
		upgradeLink="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button"
		videoLink=""
	/>
);

const Submissions = () => (
	<UpsellSection
		heading="Collect Contact Form Submissions"
		points={[
			'Collect lead submissions directly within your WordPress Admin to manage, analyze and perform bulk actions on the submitted lead',
		]}
		upgradeLink="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button"
		videoLink=""
	/>
);

const DiviMailer = () => (
	<UpsellSection
		heading="Divi Mailer"
		points={[
			'Fixes your email deliverability issues by reconfiguring WordPress to use a proper SMTP provider when sending emails.',
		]}
		upgradeLink="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button"
		videoLink=""
	/>
);

const DarkMode = () => (
	<UpsellSection
		heading="Divi Dark Mode"
		points={['Floating Dark Mode Switch', 'Dark Mode Color Presets']}
		upgradeLink="https://divitorque.com/pricing/?utm_source=divi-torque-lite&utm_medium=wp-admin&utm_campaign=upgrade-to-pro&utm_content=menu-button"
		videoLink=""
	/>
);

const AppRoutes = () => {
	const location = useLocation();
	const query = new URLSearchParams(location.search);

	const page = query.get('page') || '';
	const path = query.get('path') || '';

	let routePage = <p>{__('Page not found', 'divitorque')}</p>;

	if (diviTorqueLite.admin_slug === page) {
		switch (path) {
			case '':
			case 'module-manager':
				routePage = <Modules />;
				break;

			case 'free-vs-pro':
				routePage = <FreeVsPro />;
				break;

			case 'popups':
				routePage = <Popups />;
				break;

			case 'form-submissions':
				routePage = <Submissions />;
				break;

			case 'google-reviews':
				routePage = <GoogleReviews />;
				break;

			case 'divi-mailer':
				routePage = <DiviMailer />;
				break;

			case 'dark-mode':
				routePage = <DarkMode />;
				break;

			default:
				routePage = <p>{__('Page not found', 'divitorque')}</p>;
				break;
		}
	}

	return <>{routePage}</>;
};

export default AppRoutes;
