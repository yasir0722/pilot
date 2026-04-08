<template>
  <div class="min-h-screen flex flex-col">
    <!-- Top nav (only shown when logged in) -->
    <nav v-if="isLoggedIn" class="bg-gray-900 text-white px-4 py-3 flex items-center justify-between">
      <router-link to="/" class="text-lg font-bold tracking-wide">Pilot</router-link>
      <div class="hidden md:flex items-center gap-4 text-sm">
        <router-link to="/expenses" class="hover:text-gray-300">Expenses</router-link>
        <router-link to="/groceries" class="hover:text-gray-300">Groceries</router-link>
        <router-link to="/habits" class="hover:text-gray-300">Habits</router-link>
        <router-link to="/reminders" class="hover:text-gray-300">Reminders</router-link>
        <router-link to="/vehicles" class="hover:text-gray-300">Vehicles</router-link>
        <span class="text-gray-400 text-xs">{{ user?.name }}</span>
        <button @click="logout" class="text-xs text-gray-300 hover:text-white border border-gray-600 rounded px-2 py-1">
          Logout
        </button>
      </div>
    </nav>

    <main :class="isLoggedIn ? 'flex-1 p-4 max-w-5xl mx-auto w-full' : 'flex-1'">
      <router-view />
    </main>

    <!-- Mobile bottom nav (only shown when logged in) -->
    <nav v-if="isLoggedIn" class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t flex justify-around py-2 text-xs">
      <router-link to="/" class="flex flex-col items-center gap-0.5" active-class="text-blue-600">
        <span>📊</span><span>Home</span>
      </router-link>
      <router-link to="/expenses" class="flex flex-col items-center gap-0.5" active-class="text-blue-600">
        <span>💰</span><span>Expenses</span>
      </router-link>
      <router-link to="/groceries" class="flex flex-col items-center gap-0.5" active-class="text-blue-600">
        <span>🛒</span><span>Groceries</span>
      </router-link>
      <router-link to="/habits" class="flex flex-col items-center gap-0.5" active-class="text-blue-600">
        <span>✅</span><span>Habits</span>
      </router-link>
      <router-link to="/reminders" class="flex flex-col items-center gap-0.5" active-class="text-blue-600">
        <span>🔔</span><span>Reminders</span>
      </router-link>
      <router-link to="/vehicles" class="flex flex-col items-center gap-0.5" active-class="text-blue-600">
        <span>🚗</span><span>Vehicles</span>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import api from './api';
import { isLoggedIn, currentUser, clearAuth } from './auth';

const router = useRouter();
const user = currentUser;

async function logout() {
  try { await api.post('/logout'); } catch {}
  clearAuth();
  router.push('/login');
}
</script>

