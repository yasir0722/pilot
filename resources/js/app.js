import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import routes from './routes';

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard — protect auth routes, redirect logged-in users away from login
router.beforeEach((to) => {
    const isLoggedIn = !!localStorage.getItem('token');

    if (to.meta.auth && !isLoggedIn) {
        return { name: 'login' };
    }
    if (to.meta.guest && isLoggedIn) {
        return { name: 'dashboard' };
    }
});

createApp(App).use(router).mount('#app');

// Register service worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js');
    });
}
