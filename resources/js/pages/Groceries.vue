<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Grocery Lists</h1>

    <!-- Create list -->
    <form @submit.prevent="createList" class="flex gap-2 mb-6">
      <input v-model="newListName" type="text" placeholder="New list name" required
        class="border rounded px-3 py-2 text-sm flex-1" />
      <label class="flex items-center gap-1 text-sm">
        <input type="checkbox" v-model="newListTemplate" /> Template
      </label>
      <button type="submit" class="bg-gray-900 text-white rounded px-4 py-2 text-sm">Create</button>
    </form>

    <!-- Lists -->
    <div v-for="list in lists" :key="list.id" class="bg-white rounded-lg shadow p-4 mb-4">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold">
          {{ list.name }}
          <span v-if="list.is_template" class="text-xs text-blue-500 ml-1">(template)</span>
        </h2>
        <div class="flex gap-2">
          <button v-if="list.is_template" @click="cloneList(list.id)" class="text-xs text-blue-600 hover:underline">Clone</button>
          <button @click="deleteList(list.id)" class="text-xs text-red-500 hover:underline">Delete</button>
        </div>
      </div>

      <!-- Add item -->
      <form @submit.prevent="addItem(list.id)" class="flex gap-2 mb-3">
        <input v-model="newItems[list.id]" type="text" placeholder="Add item" required
          class="border rounded px-2 py-1 text-sm flex-1" />
        <button type="submit" class="bg-gray-800 text-white rounded px-3 py-1 text-sm">+</button>
      </form>

      <!-- Items -->
      <ul class="space-y-1">
        <li v-for="item in list.items" :key="item.id" class="flex items-center gap-2 text-sm">
          <button @click="toggleItem(item)" :class="['w-5 h-5 rounded border flex items-center justify-center text-xs',
            item.completed ? 'bg-green-100 border-green-400 text-green-600' : 'border-gray-300']">
            {{ item.completed ? '✓' : '' }}
          </button>
          <span :class="{ 'line-through text-gray-400': item.completed }">{{ item.name }}</span>
          <button @click="removeItem(item.id)" class="ml-auto text-red-400 hover:text-red-600 text-xs">✕</button>
        </li>
      </ul>
    </div>

    <div v-if="!lists.length" class="text-gray-400 text-center py-8">No grocery lists yet</div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import api from '../api';

const lists = ref([]);
const newListName = ref('');
const newListTemplate = ref(false);
const newItems = reactive({});

const fetchLists = async () => {
  const { data } = await api.get('/groceries');
  lists.value = data;
};

const createList = async () => {
  await api.post('/groceries', { name: newListName.value, is_template: newListTemplate.value });
  newListName.value = '';
  newListTemplate.value = false;
  fetchLists();
};

const deleteList = async (id) => {
  await api.delete(`/groceries/${id}`);
  fetchLists();
};

const addItem = async (listId) => {
  const name = newItems[listId];
  if (!name) return;
  await api.post(`/groceries/${listId}/items`, { name });
  newItems[listId] = '';
  fetchLists();
};

const toggleItem = async (item) => {
  await api.patch(`/grocery-items/${item.id}/toggle`);
  item.completed = !item.completed;
};

const removeItem = async (id) => {
  await api.delete(`/grocery-items/${id}`);
  fetchLists();
};

const cloneList = async (id) => {
  await api.post(`/groceries/${id}/clone`);
  fetchLists();
};

onMounted(fetchLists);
</script>
