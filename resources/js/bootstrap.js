import axios from 'axios';
window.axios = axios;

// Send cookies on cross-site requests so Sanctum session auth works from the frontend
window.axios.defaults.withCredentials = true;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Request Sanctum CSRF cookie on bootstrap so subsequent POST/PUT/DELETE requests include the XSRF token
(async () => {
	try {
		await window.axios.get('/sanctum/csrf-cookie');
	} catch (e) {
		// Fail silently; environments without Sanctum route will continue to work for public requests
	}
})();
