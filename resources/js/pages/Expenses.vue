<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Expenses</h1>
    </div>

    <!-- Add expense form -->
    <form @submit.prevent="addExpense" class="bg-white rounded-lg shadow p-4 mb-6 grid gap-3 md:grid-cols-5">
      <input v-model="form.amount" type="number" step="0.01" min="0.01" placeholder="Amount" required
        class="border rounded px-3 py-2 text-sm" />
      <input v-model="form.description" type="text" placeholder="Description" required
        class="border rounded px-3 py-2 text-sm md:col-span-2" />
      <input v-model="form.category" type="text" placeholder="Category"
        class="border rounded px-3 py-2 text-sm" />
      <button type="submit" class="bg-gray-900 text-white rounded px-4 py-2 text-sm">Add</button>
    </form>

    <!-- Filters (desktop) -->
    <div class="hidden md:flex gap-3 mb-4">
      <input v-model="filters.search" type="text" placeholder="Search..." class="border rounded px-3 py-1.5 text-sm" @input="fetchExpenses" />
      <input v-model="filters.from" type="date" class="border rounded px-3 py-1.5 text-sm" @change="fetchExpenses" />
      <input v-model="filters.to" type="date" class="border rounded px-3 py-1.5 text-sm" @change="fetchExpenses" />
    </div>

    <!-- CSV Import -->
    <div class="hidden md:block mb-4">
      <label class="text-sm text-gray-500 cursor-pointer hover:text-gray-700">
        📎 Import CSV
        <input type="file" accept=".csv" class="hidden" @change="importCsv" />
      </label>
    </div>

    <!-- Receipt upload -->
    <div class="mb-4">
      <label class="text-sm text-gray-500 cursor-pointer hover:text-gray-700">
        📷 Upload Receipt
        <input type="file" accept="image/*" class="hidden" @change="uploadReceipt" />
      </label>
    </div>

    <!-- Expense list -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-100 hidden md:table-header-group">
          <tr>
            <th class="text-left px-4 py-2">Date</th>
            <th class="text-left px-4 py-2">Description</th>
            <th class="text-left px-4 py-2">Category</th>
            <th class="text-right px-4 py-2">Amount</th>
            <th class="px-4 py-2"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in expenses" :key="e.id" class="border-t">
            <td class="px-4 py-2">{{ e.date }}</td>
            <td class="px-4 py-2">{{ e.description }}</td>
            <td class="px-4 py-2 text-gray-500">{{ e.category || '-' }}</td>
            <td class="px-4 py-2 text-right font-mono">RM{{ Number(e.amount).toFixed(2) }}</td>
            <td class="px-4 py-2 text-right">
              <button @click="deleteExpense(e.id)" class="text-red-500 hover:text-red-700 text-xs">✕</button>
            </td>
          </tr>
          <tr v-if="!expenses.length">
            <td colspan="5" class="px-4 py-8 text-center text-gray-400">No expenses yet</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2 mt-4">
      <button v-for="p in pagination.last_page" :key="p" @click="fetchExpenses(p)"
        :class="['px-3 py-1 rounded text-sm', p === pagination.current_page ? 'bg-gray-900 text-white' : 'bg-white border']">
        {{ p }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';

const expenses = ref([]);
const pagination = ref({});
const form = ref({ amount: '', description: '', category: '', date: new Date().toISOString().slice(0, 10) });
const filters = ref({ search: '', from: '', to: '' });

const fetchExpenses = async (page = 1) => {
  const params = { page, ...filters.value };
  const { data } = await api.get('/expenses', { params });
  expenses.value = data.data;
  pagination.value = data;
};

const addExpense = async () => {
  await api.post('/expenses', { ...form.value, date: form.value.date || new Date().toISOString().slice(0, 10) });
  form.value = { amount: '', description: '', category: '', date: new Date().toISOString().slice(0, 10) };
  fetchExpenses();
};

const deleteExpense = async (id) => {
  await api.delete(`/expenses/${id}`);
  fetchExpenses();
};

const importCsv = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('file', file);
  await api.post('/expenses/import-csv', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
  fetchExpenses();
};

const uploadReceipt = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('file', file);
  await api.post('/receipts', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
};

onMounted(fetchExpenses);
</script>
