<script setup>
import { ref, reactive, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { formatNumber, scrollToFirstErrorField } from "@/helpers/utils";
import axios from 'axios'; // gunakan Inertia atau axios sesuai kebutuhan
import { Notify } from 'quasar';
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";

const page = usePage();

const dialog = ref(false);

// Data list items
const items = ref([]);

// Form input untuk dialog
const form = reactive({
  id: null,          // index item kalau edit, null kalau tambah baru
  description: '',
  quantity: 1,
  cost: 0,
  price: 0,
  notes: '',
});

// Reset form setiap buka dialog
function openDialog(index = null) {
  if (index === null) {
    form.id = null;
    form.description = '';
    form.quantity = 1;
    form.cost = 0;
    form.price = 0;
    form.notes = '';
  } else {
    const item = items.value[index];
    form.id = index;
    form.description = item.description;
    form.quantity = item.quantity;
    form.cost = item.cost;
    form.price = item.price;
    form.notes = item.notes;
  }
  dialog.value = true;
}

// Tambah atau update item
function save() {
  if (!form.description.trim()) {
    alert('Deskripsi wajib diisi');
    return;
  }
  if (form.quantity <= 0 || form.cost < 0 || form.price < 0) {
    alert('Quantity, Cost, dan Price harus valid');
    return;
  }

  if (form.id === null) {
    // Add
    items.value.push({
      description: form.description,
      quantity: form.quantity,
      cost: form.cost,
      price: form.price,
      notes: form.notes,
    });
  } else {
    // Edit
    items.value[form.id] = {
      description: form.description,
      quantity: form.quantity,
      cost: form.cost,
      price: form.price,
      notes: form.notes,
    };
  }

  dialog.value = false;
}

// Hapus item dari tabel
function removeItem(index) {
  items.value.splice(index, 1);
}

// Kolom untuk q-table
const columns = [
  { name: 'number', label: '#', field: 'number', align: 'left' },
  { name: 'description', label: 'Item', field: 'description', align: 'left' },
  { name: 'quantity', label: 'Quantity', field: 'quantity', align: 'right' },
  { name: 'cost', label: 'Cost', field: 'cost', align: 'right' },
  { name: 'price', label: 'Price', field: 'price', align: 'right' },
  { name: 'notes', label: 'Catatan', field: 'notes', align: 'left' },
  { name: 'action', label: 'Aksi', field: 'action', align: 'center' },
];

// Label tombol dinamis (Tambah / Simpan)
const saveLabel = computed(() => (form.id === null ? 'Tambah' : 'Simpan'));
</script>

<template>
  <q-btn icon="add" color="primary" dense label="Tambah Item" @click="openDialog()" />

  <q-table :columns="columns" :rows="items" row-key="number" flat bordered class="q-mt-md">
    <template v-slot:body="props">
      <q-tr :props="props" class="cursor-pointer">
        <q-td key="number" :props="props" class="wrap-column">
          {{ props.rowIndex + 1 }}
        </q-td>
        <q-td key="description" :props="props" class="wrap-column">
          {{ props.row.description }}
        </q-td>
        <q-td key="quantity" :props="props" class="wrap-column">
          {{ formatNumber(props.row.quantity) }}
        </q-td>
        <q-td key="cost" :props="props" class="wrap-column">
          {{ formatNumber(props.row.cost) }}
        </q-td>
        <q-td key="price" :props="props" class="wrap-column">
          {{ formatNumber(props.row.price) }}
        </q-td>
        <q-td key="notes" :props="props" class="wrap-column">
          {{ props.row.notes }}
        </q-td>
        <q-td key="action" :props="props">
          <div class="flex justify-end q-gutter-xs">
            <q-btn dense round color="grey" icon="edit" @click="openDialog(props.rowIndex)" />
            <q-btn dense round color="negative" icon="delete" @click="removeItem(props.rowIndex)" />
          </div>
        </q-td>
      </q-tr>
    </template>
  </q-table>

  <q-dialog v-model="dialog" persistent>
    <q-card style="min-width: 350px;" class="q-pa-sm">
      <q-card-section>
        <div class="text-h6">{{ form.id === null ? 'Tambah Item' : 'Edit Item' }}</div>
      </q-card-section>

      <q-card-section>
        <q-input v-model="form.description" label="Deskripsi" autofocus required />
        <LocaleNumberInput v-model="form.quantity" label="Quantity" required class="q-mt-sm" />
        <LocaleNumberInput v-model="form.cost" label="Cost" required class="q-mt-sm" />
        <LocaleNumberInput v-model="form.price" label="Price" required class="q-mt-sm" />
        <q-input v-model="form.notes" label="Catatan" type="textarea" autogrow length="100" />
      </q-card-section>

      <q-card-actions align="center" class="q-pa-md">
        <q-btn icon="save" :label="saveLabel" color="primary" @click="save" />
        <q-btn icon="cancel" label="Batal" color="grey" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
