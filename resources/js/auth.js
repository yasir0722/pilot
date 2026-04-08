import { ref } from 'vue';

export const isLoggedIn = ref(!!localStorage.getItem('token'));
export const currentUser = ref(JSON.parse(localStorage.getItem('user') || 'null'));

export function setAuth(token, user) {
    localStorage.setItem('token', token);
    localStorage.setItem('user', JSON.stringify(user));
    isLoggedIn.value = true;
    currentUser.value = user;
}

export function clearAuth() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    isLoggedIn.value = false;
    currentUser.value = null;
}
