<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Vehicles</h1>

    <!-- Add vehicle form -->
    <form @submit.prevent="addVehicle" class="bg-white rounded-lg shadow p-4 mb-6 grid gap-3 md:grid-cols-6">
      <input v-model="vehicleForm.name" type="text" placeholder="Name (e.g. MyVi)" required
        class="border rounded px-3 py-2 text-sm" />
      <select v-model="vehicleForm.type" class="border rounded px-3 py-2 text-sm" required>
        <option value="car">Car</option>
        <option value="motorbike">Motorbike</option>
      </select>
      <input v-model="vehicleForm.make" type="text" placeholder="Make (e.g. Proton)"
        class="border rounded px-3 py-2 text-sm" />
      <input v-model="vehicleForm.model" type="text" placeholder="Model"
        class="border rounded px-3 py-2 text-sm" />
      <input v-model="vehicleForm.plate_number" type="text" placeholder="Plate number"
        class="border rounded px-3 py-2 text-sm" />
      <button type="submit" class="bg-gray-900 text-white rounded px-4 py-2 text-sm">Add</button>
    </form>

    <!-- Vehicle cards -->
    <div v-for="v in vehicles" :key="v.id" class="bg-white rounded-lg shadow mb-6">
      <!-- Vehicle header -->
      <div class="p-4 border-b flex items-center justify-between cursor-pointer" @click="toggle(v.id)">
        <div>
          <h2 class="font-semibold text-lg">
            {{ v.type === 'car' ? '🚗' : '🏍️' }} {{ v.name }}
            <span v-if="v.make || v.model" class="text-sm text-gray-400 ml-1">{{ [v.make, v.model].filter(Boolean).join(' ') }}</span>
          </h2>
          <div class="text-xs text-gray-400 mt-0.5">
            <span v-if="v.plate_number">{{ v.plate_number }}</span>
            <span v-if="v.plate_number && v.services_sum_cost"> · </span>
            <span v-if="v.services_sum_cost">Total spent: RM{{ Number(v.services_sum_cost).toFixed(2) }}</span>
            <span v-if="v.services_count"> · {{ v.services_count }} services</span>
            <span v-if="v.warranty_claims_count"> · {{ v.warranty_claims_count }} warranty claims</span>
          </div>
        </div>
        <div class="flex gap-2 items-center">
          <button @click.stop="deleteVehicle(v.id)" class="text-red-400 hover:text-red-600 text-xs">Delete</button>
          <span class="text-gray-400">{{ expanded[v.id] ? '▲' : '▼' }}</span>
        </div>
      </div>

      <!-- Expanded content -->
      <div v-if="expanded[v.id]" class="p-4">
        <!-- Tab nav -->
        <div class="flex gap-4 mb-4 text-sm border-b">
          <button @click="activeTab[v.id] = 'services'" :class="['pb-2 px-1', activeTab[v.id] === 'services' ? 'border-b-2 border-gray-900 font-medium' : 'text-gray-400']">
            🔧 Services
          </button>
          <button @click="activeTab[v.id] = 'warranty'" :class="['pb-2 px-1', activeTab[v.id] === 'warranty' ? 'border-b-2 border-gray-900 font-medium' : 'text-gray-400']">
            🛡️ Warranty Claims
          </button>
        </div>

        <!-- Services tab -->
        <div v-if="activeTab[v.id] === 'services'">
          <!-- Add service form -->
          <form @submit.prevent="addService(v.id)" class="grid gap-2 md:grid-cols-6 mb-4">
            <input v-model="serviceForm[v.id].description" type="text" placeholder="Description" required
              class="border rounded px-3 py-2 text-sm md:col-span-2" />
            <input v-model="serviceForm[v.id].service_type" type="text" placeholder="Type (oil, tyres...)"
              class="border rounded px-3 py-2 text-sm" />
            <input v-model="serviceForm[v.id].cost" type="number" step="0.01" min="0" placeholder="Cost (RM)" required
              class="border rounded px-3 py-2 text-sm" />
            <input v-model="serviceForm[v.id].date" type="date" required
              class="border rounded px-3 py-2 text-sm" />
            <button type="submit" class="bg-gray-800 text-white rounded px-3 py-2 text-sm">Add</button>
          </form>

          <!-- Extra fields (collapsed by default) -->
          <div v-if="showExtraService[v.id]" class="grid gap-2 md:grid-cols-4 mb-4">
            <input v-model="serviceForm[v.id].mileage" type="number" min="0" placeholder="Mileage (km)"
              class="border rounded px-3 py-2 text-sm" />
            <input v-model="serviceForm[v.id].next_service_date" type="date" placeholder="Next service date"
              class="border rounded px-3 py-2 text-sm" />
            <input v-model="serviceForm[v.id].next_service_mileage" type="number" min="0" placeholder="Next service km"
              class="border rounded px-3 py-2 text-sm" />
            <input v-model="serviceForm[v.id].notes" type="text" placeholder="Notes"
              class="border rounded px-3 py-2 text-sm" />
          </div>
          <button @click="showExtraService[v.id] = !showExtraService[v.id]" class="text-xs text-blue-500 mb-4 block">
            {{ showExtraService[v.id] ? 'Less fields' : 'More fields (mileage, next service...)' }}
          </button>

          <!-- Service list -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="text-left px-3 py-2">Date</th>
                  <th class="text-left px-3 py-2">Description</th>
                  <th class="text-left px-3 py-2">Type</th>
                  <th class="text-right px-3 py-2">Cost</th>
                  <th class="text-right px-3 py-2">Mileage</th>
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in vehicleDetails[v.id]?.services ?? []" :key="s.id" class="border-t">
                  <td class="px-3 py-2">{{ s.date }}</td>
                  <td class="px-3 py-2">
                    {{ s.description }}
                    <div v-if="s.notes" class="text-xs text-gray-400">{{ s.notes }}</div>
                  </td>
                  <td class="px-3 py-2 text-gray-500">{{ s.service_type || '-' }}</td>
                  <td class="px-3 py-2 text-right font-mono">RM{{ Number(s.cost).toFixed(2) }}</td>
                  <td class="px-3 py-2 text-right text-gray-400">{{ s.mileage ? s.mileage + ' km' : '-' }}</td>
                  <td class="px-3 py-2 text-right">
                    <label class="text-blue-500 hover:text-blue-700 text-xs cursor-pointer mr-2">
                      📷
                      <input type="file" accept="image/*" class="hidden" @change="uploadReceipt(s.id, $event)" />
                    </label>
                    <button @click="deleteService(s.id, v.id)" class="text-red-500 hover:text-red-700 text-xs">✕</button>
                  </td>
                </tr>
                <tr v-if="!(vehicleDetails[v.id]?.services ?? []).length">
                  <td colspan="6" class="px-3 py-6 text-center text-gray-400">No services recorded</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Next service info -->
          <div v-if="nextService(v.id)" class="mt-3 text-xs text-gray-500 bg-blue-50 rounded p-2">
            📅 Next service: {{ nextService(v.id).next_service_date }}
            <span v-if="nextService(v.id).next_service_mileage"> · at {{ nextService(v.id).next_service_mileage }} km</span>
          </div>
        </div>

        <!-- Warranty Claims tab -->
        <div v-if="activeTab[v.id] === 'warranty'">
          <!-- Add warranty claim form -->
          <form @submit.prevent="addWarranty(v.id)" class="grid gap-2 md:grid-cols-5 mb-4">
            <input v-model="warrantyForm[v.id].description" type="text" placeholder="Description" required
              class="border rounded px-3 py-2 text-sm md:col-span-2" />
            <input v-model="warrantyForm[v.id].claim_number" type="text" placeholder="Claim #"
              class="border rounded px-3 py-2 text-sm" />
            <input v-model="warrantyForm[v.id].date_filed" type="date" required
              class="border rounded px-3 py-2 text-sm" />
            <button type="submit" class="bg-gray-800 text-white rounded px-3 py-2 text-sm">Add</button>
          </form>

          <!-- Warranty claims list -->
          <div class="space-y-2">
            <div v-for="w in vehicleDetails[v.id]?.warranty_claims ?? []" :key="w.id"
              class="border rounded-lg p-3 flex items-start justify-between">
              <div>
                <div class="font-medium text-sm">{{ w.description }}</div>
                <div class="text-xs text-gray-400 mt-0.5">
                  <span v-if="w.claim_number">Claim #{{ w.claim_number }} · </span>
                  Filed: {{ w.date_filed }}
                  <span v-if="w.date_resolved"> · Resolved: {{ w.date_resolved }}</span>
                  <span v-if="w.cost_covered"> · Covered: RM{{ Number(w.cost_covered).toFixed(2) }}</span>
                </div>
                <div v-if="w.notes" class="text-xs text-gray-400 mt-0.5">{{ w.notes }}</div>
              </div>
              <div class="flex items-center gap-2">
                <select :value="w.status" @change="updateWarrantyStatus(w.id, v.id, ($event.target).value)"
                  class="text-xs border rounded px-2 py-1"
                  :class="{
                    'bg-yellow-50 text-yellow-700': w.status === 'pending',
                    'bg-green-50 text-green-700': w.status === 'approved',
                    'bg-red-50 text-red-700': w.status === 'rejected',
                    'bg-blue-50 text-blue-700': w.status === 'completed',
                  }">
                  <option value="pending">Pending</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                  <option value="completed">Completed</option>
                </select>
                <button @click="deleteWarranty(w.id, v.id)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
              </div>
            </div>
            <div v-if="!(vehicleDetails[v.id]?.warranty_claims ?? []).length" class="text-gray-400 text-center py-6">
              No warranty claims
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!vehicles.length" class="text-gray-400 text-center py-8">No vehicles yet</div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../api';

const vehicles = ref([]);
const vehicleDetails = reactive({});
const expanded = reactive({});
const activeTab = reactive({});
const showExtraService = reactive({});
const serviceForm = reactive({});
const warrantyForm = reactive({});

const vehicleForm = ref({ name: '', type: 'car', make: '', model: '', plate_number: '' });

const today = new Date().toISOString().slice(0, 10);

const initForms = (id) => {
  if (!serviceForm[id]) serviceForm[id] = { description: '', service_type: '', cost: '', date: today, mileage: '', next_service_date: '', next_service_mileage: '', notes: '' };
  if (!warrantyForm[id]) warrantyForm[id] = { description: '', claim_number: '', date_filed: today };
  if (!activeTab[id]) activeTab[id] = 'services';
};

const fetchVehicles = async () => {
  const { data } = await api.get('/vehicles');
  vehicles.value = data;
  data.forEach((v) => initForms(v.id));
};

const fetchDetails = async (id) => {
  const { data } = await api.get(`/vehicles/${id}`);
  vehicleDetails[id] = data;
};

const toggle = (id) => {
  expanded[id] = !expanded[id];
  if (expanded[id] && !vehicleDetails[id]) {
    fetchDetails(id);
  }
};

const addVehicle = async () => {
  await api.post('/vehicles', vehicleForm.value);
  vehicleForm.value = { name: '', type: 'car', make: '', model: '', plate_number: '' };
  fetchVehicles();
};

const deleteVehicle = async (id) => {
  await api.delete(`/vehicles/${id}`);
  fetchVehicles();
};

const addService = async (vehicleId) => {
  const form = serviceForm[vehicleId];
  await api.post(`/vehicles/${vehicleId}/services`, {
    ...form,
    mileage: form.mileage || null,
    next_service_mileage: form.next_service_mileage || null,
    next_service_date: form.next_service_date || null,
    notes: form.notes || null,
  });
  serviceForm[vehicleId] = { description: '', service_type: '', cost: '', date: today, mileage: '', next_service_date: '', next_service_mileage: '', notes: '' };
  fetchDetails(vehicleId);
  fetchVehicles();
};

const deleteService = async (serviceId, vehicleId) => {
  await api.delete(`/vehicle-services/${serviceId}`);
  fetchDetails(vehicleId);
  fetchVehicles();
};

const uploadReceipt = async (serviceId, event) => {
  const file = event.target.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('file', file);
  await api.post(`/vehicle-services/${serviceId}/receipt`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
};

const addWarranty = async (vehicleId) => {
  const form = warrantyForm[vehicleId];
  await api.post(`/vehicles/${vehicleId}/warranty-claims`, form);
  warrantyForm[vehicleId] = { description: '', claim_number: '', date_filed: today };
  fetchDetails(vehicleId);
  fetchVehicles();
};

const updateWarrantyStatus = async (claimId, vehicleId, status) => {
  await api.put(`/vehicle-warranty-claims/${claimId}`, { status });
  fetchDetails(vehicleId);
};

const deleteWarranty = async (claimId, vehicleId) => {
  await api.delete(`/vehicle-warranty-claims/${claimId}`);
  fetchDetails(vehicleId);
  fetchVehicles();
};

const nextService = (vehicleId) => {
  const services = vehicleDetails[vehicleId]?.services ?? [];
  return services.find((s) => s.next_service_date);
};

onMounted(fetchVehicles);
</script>
