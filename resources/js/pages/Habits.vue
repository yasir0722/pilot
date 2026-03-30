<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Habits</h1>

    <!-- Add habit -->
    <form @submit.prevent="addHabit" class="bg-white rounded-lg shadow p-4 mb-6 grid gap-3 md:grid-cols-4">
      <input v-model="form.name" type="text" placeholder="Habit name" required
        class="border rounded px-3 py-2 text-sm md:col-span-2" />
      <select v-model="form.frequency" class="border rounded px-3 py-2 text-sm">
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
      </select>
      <button type="submit" class="bg-gray-900 text-white rounded px-4 py-2 text-sm">Add</button>
    </form>

    <!-- Habit list -->
    <div class="space-y-2">
      <div v-for="habit in habits" :key="habit.id"
        class="bg-white rounded-lg shadow p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <button @click="toggleComplete(habit)"
            :class="['w-8 h-8 rounded-full border-2 flex items-center justify-center text-sm transition',
              habit.completed_today
                ? 'bg-green-100 border-green-500 text-green-600'
                : 'border-gray-300 hover:border-gray-400']">
            {{ habit.completed_today ? '✓' : '' }}
          </button>
          <div>
            <div class="font-medium">{{ habit.name }}</div>
            <div class="text-xs text-gray-400">{{ habit.frequency }}</div>
          </div>
        </div>
        <button @click="deleteHabit(habit.id)" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
      </div>
    </div>

    <div v-if="!habits.length" class="text-gray-400 text-center py-8">No habits yet</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';

const habits = ref([]);
const form = ref({ name: '', frequency: 'daily' });

const fetchHabits = async () => {
  const { data } = await api.get('/habits');
  habits.value = data;
};

const addHabit = async () => {
  await api.post('/habits', form.value);
  form.value = { name: '', frequency: 'daily' };
  fetchHabits();
};

const toggleComplete = async (habit) => {
  if (habit.completed_today) {
    await api.delete(`/habits/${habit.id}/complete`);
  } else {
    await api.post(`/habits/${habit.id}/complete`);
  }
  habit.completed_today = !habit.completed_today;
};

const deleteHabit = async (id) => {
  await api.delete(`/habits/${id}`);
  fetchHabits();
};

onMounted(fetchHabits);
</script>
