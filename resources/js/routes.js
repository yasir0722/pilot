import Dashboard from './pages/Dashboard.vue';
import Expenses from './pages/Expenses.vue';
import Groceries from './pages/Groceries.vue';
import Habits from './pages/Habits.vue';
import Login from './pages/Login.vue';
import Reminders from './pages/Reminders.vue';
import Vehicles from './pages/Vehicles.vue';

const routes = [
    { path: '/login', name: 'login', component: Login, meta: { guest: true } },
    { path: '/', name: 'dashboard', component: Dashboard, meta: { auth: true } },
    { path: '/expenses', name: 'expenses', component: Expenses, meta: { auth: true } },
    { path: '/groceries', name: 'groceries', component: Groceries, meta: { auth: true } },
    { path: '/habits', name: 'habits', component: Habits, meta: { auth: true } },
    { path: '/reminders', name: 'reminders', component: Reminders, meta: { auth: true } },
    { path: '/vehicles', name: 'vehicles', component: Vehicles, meta: { auth: true } },
];

export default routes;
