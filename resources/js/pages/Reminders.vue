<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Reminders</h1>

    <!-- Add reminder -->
    <form @submit.prevent="addReminder" class="bg-white rounded-lg shadow p-4 mb-6 grid gap-3 md:grid-cols-5">
      <input v-model="form.title" type="text" placeholder="Title" required
        class="border rounded px-3 py-2 text-sm md:col-span-2" />
      <input v-model="form.remind_at" type="datetime-local" required
        class="border rounded px-3 py-2 text-sm" />
      <select v-model="form.recurrence" class="border rounded px-3 py-2 text-sm">
        <option value="none">No repeat</option>
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
      </select>
      <button type="submit" class="bg-gray-900 text-white rounded px-4 py-2 text-sm">Add</button>
    </form>

    <!-- Reminder list -->
    <div class="space-y-2">
      <div v-for="r in reminders" :key="r.id"
        class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
        <div>
          <div class="font-medium">{{ r.title }}</div>
          <div class="text-xs text-gray-400">
            {{ formatDate(r.remind_at) }}
            <span v-if="r.recurrence !== 'none'" class="ml-1 text-blue-400">↻ {{ r.recurrence }}</span>
          </div>
        </div>
        <button @click="deleteReminder(r.id)" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
      </div>
    </div>

    <div v-if="!reminders.length" class="text-gray-400 text-center py-8">No reminders yet</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';

const reminders = ref([]);
const form = ref({ title: '', remind_at: '', recurrence: 'none' });

const formatDate = (d) => new Date(d).toLocaleString();

const fetchReminders = async () => {
  const { data } = await api.get('/reminders');
  reminders.value = data;
};

const addReminder = async () => {
  await api.post('/reminders', form.value);
  form.value = { title: '', remind_at: '', recurrence: 'none' };
  fetchReminders();
};

const deleteReminder = async (id) => {
  await api.delete(`/reminders/${id}`);
  fetchReminders();
};

onMounted(fetchReminders);
</script>
