import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import axios from 'axios';

window.Alpine = Alpine;
window.Chart = Chart;
window.axios = axios;

// Auto setup CSRF token for Axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

Alpine.start();
