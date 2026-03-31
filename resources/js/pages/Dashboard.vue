<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <div v-if="loading" class="text-gray-500">Loading...</div>

    <div v-else class="grid gap-4 md:grid-cols-3">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Spent Today</div>
        <div class="text-2xl font-bold">RM{{ summary.expenses?.today ?? '0.00' }}</div>
        <div class="text-xs text-gray-400 mt-1">This month: RM{{ summary.expenses?.this_month ?? '0.00' }}</div>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Habits Today</div>
        <div class="text-2xl font-bold">
          {{ summary.habits?.completed_today ?? 0 }}/{{ summary.habits?.total ?? 0 }}
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Upcoming Reminders</div>
        <ul class="mt-2 space-y-1 text-sm">
          <li v-for="r in summary.upcoming_reminders" :key="r.id">
            {{ r.title }} — <span class="text-gray-400">{{ formatDate(r.remind_at) }}</span>
          </li>
          <li v-if="!summary.upcoming_reminders?.length" class="text-gray-400">None</li>
        </ul>
      </div>
    </div>

    <!-- Quick actions for mobile -->
    <div class="mt-8 grid grid-cols-2 gap-3 md:hidden">
      <router-link to="/expenses" class="bg-white shadow rounded-lg p-4 text-center text-sm font-medium">
        💰 Add Expense
      </router-link>
      <router-link to="/groceries" class="bg-white shadow rounded-lg p-4 text-center text-sm font-medium">
        🛒 Groceries
      </router-link>
      <router-link to="/habits" class="bg-white shadow rounded-lg p-4 text-center text-sm font-medium">
        ✅ Habits
      </router-link>
      <router-link to="/reminders" class="bg-white shadow rounded-lg p-4 text-center text-sm font-medium">
        🔔 Reminders
      </router-link>
      <router-link to="/vehicles" class="bg-white shadow rounded-lg p-4 text-center text-sm font-medium">
        🚗 Vehicles
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';

const summary = ref({});
const loading = ref(true);

const formatDate = (d) => new Date(d).toLocaleDateString();

onMounted(async () => {
  try {
    const { data } = await api.get('/summary');
    summary.value = data;
  } finally {
    loading.value = false;
  }
});
</script>
