<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { handleSubmit, handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { formatNumber, scrollToFirstErrorField, getQueryParams } from "@/helpers/utils";
import axios from 'axios';
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import { useApiForm } from '@/helpers/useApiForm';
import { Dialog, Notify } from 'quasar'
import { useQuasar } from "quasar";

const $q = useQuasar();
const page = usePage();
const dialog = ref(false);
const show_cost = ref(true);
const notesDialog = ref(false);
const selectedNote = ref('');

function showNotes(note) {
  selectedNote.value = note;
  notesDialog.value = true;
}

// Data list items
const items = ref([]);

// Form input untuk dialog
const form = useApiForm({
  order_id: page.props.data.id,
  id: null,
  description: '',
  ordered_quantity: 1,
  unit_cost: 0,
  unit_price: 0,
  notes: '',
});

// Reset form setiap buka dialog
function openDialog(index = null) {
  if (index === null) {
    form.id = null;
    form.description = '';
    form.ordered_quantity = 0;
    form.unit_cost = 0;
    form.unit_price = 0;
    form.notes = '';
  } else {
    const item = items.value[index];
    form.id = item.id;
    form.description = item.description;
    form.ordered_quantity = item.ordered_quantity;
    form.unit_cost = item.unit_cost;
    form.unit_price = item.unit_price;
    form.notes = item.notes;
  }

  dialog.value = true;
}

// Tambah atau update item
function save() {
  if (!form.description.trim()) {
    alert('Deskripsi tidak valid');
    return;
  }

  if (form.ordered_quantity <= 0 || form.cost < 0 || form.price < 0) {
    alert('Kwantitas, modal, atau harga tidak valid');
    return;
  }

  handleSubmit({
    form,
    url: route('admin.production-order-item.save'),
    onSuccess: (res) => {
      if (Number(form.id) == 0) {
        items.value.push(res.item);
      } else {
        const index = items.value?.findIndex(item => item.id === res.item?.id)
        if (index >= 0) items.value.splice(index, 1, res.item)
      }
      dialog.value = false;
      Notify.create({ message: 'Item berhasil disimpan.' });
    }
  });
}

const loading = ref(false)

onMounted(() => {
  fetchItems();
});

function removeItem(index) {
  const item = items.value[index];
  if (!item) return;

  Dialog.create({
    title: 'Konfirmasi',
    message: 'Apakah Anda yakin ingin menghapus item ini?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      loading.value = true;

      // Hanya panggil API kalau item ini memang disimpan ke backend
      if (item.id) {
        await axios.post(route('admin.production-order-item.delete', { id: item.id }));
      }

      // Hapus dari array lokal
      items.value.splice(index, 1);

      Notify.create({
        message: 'Item berhasil dihapus'
      });
    } catch (err) {
      Notify.create({
        type: 'negative',
        message: 'Gagal menghapus item'
      });
    } finally {
      loading.value = false;
    }
  });
}

const filter = reactive({
  search: "",
  status: "all",
  ...getQueryParams(),
});

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10,
  sortBy: "id",
  descending: false,
});


const fetchItems = (props = null) => {
  handleFetchItems({
    filter,
    pagination,
    props,
    rows: items,
    url: route("admin.production-order-item.data", { order_id: form.order_id }),
    loading,
  });
};

const total_completed_qty = computed(() => {
  return items.value.reduce((sum, item) => {
    return sum + Number(item.completed_quantity) || 0
  }, 0)
});

const total_assigned_qty = computed(() => {
  return items.value.reduce((sum, item) => {
    return sum + Number(item.assigned_quantity) || 0
  }, 0)
});

const total_qty = computed(() => {
  return items.value.reduce((sum, item) => {
    return sum + Number(item.ordered_quantity) || 0
  }, 0)
});

const grand_total_cost = computed(() => {
  return items.value.reduce((sum, item) => {
    const qty = Number(item.ordered_quantity) || 0
    const cost = Number(item.unit_cost) || 0
    return sum + (qty * cost)
  }, 0);
})

const grand_total_price = computed(() => {
  return items.value.reduce((sum, item) => {
    const qty = Number(item.ordered_quantity) || 0
    const price = Number(item.unit_price) || 0
    return sum + (qty * price)
  }, 0);
})

const submit = () =>
  handleSubmit({ form, url: route('admin.production-order-item.save') });

// Kolom untuk q-table
const columns = [
  { name: 'number', label: '#', field: 'number', align: 'left' },
  { name: 'description', label: 'Item', field: 'description', align: 'left' },
  { name: 'quantity', label: 'Qty / Diambil / Selesai', field: 'quantity', align: 'right' },
  { name: 'progress', label: 'Diambil / Selesai', field: 'progress', align: 'right' },
  { name: 'unit_cost', label: 'Biaya', field: 'unit_cost', align: 'right' },
  { name: 'subtotal_cost', label: 'Jml BIaya', field: 'subtotal_cost', align: 'right' },
  { name: 'unit_price', label: 'Harga', field: 'unit_price', align: 'right' },
  { name: 'subtotal_price', label: 'Jml Harga', field: 'subtotal_price', align: 'right' },
  { name: 'notes', label: 'Catatan', field: 'notes', align: 'left' },
  { name: 'action', label: 'Aksi', field: 'action', align: 'center' },
];

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) {
    if (show_cost.value) {
      return columns;
    }
    const newCols = columns.filter((col) => !['unit_cost', 'subtotal_cost'].includes(col.name));
    return newCols;
  }

  return columns.filter((col) => ['number', 'description', 'action'].includes(col.name));
});

</script>

<template>
  <q-btn icon="add" color="primary" size="sm" dense label="Tambah&nbsp;&nbsp;" @click="openDialog()" />
  <q-checkbox v-show="false" label="Tampilkan Modal" v-model="show_cost" />
  <q-table :columns="computedColumns" :rows="items" row-key="number" dense flat bordered class="q-mt-md"
    @request="fetchItems" :loading="loading" :rows-per-page-options="[10, 25, 50]">
    <template v-slot:body="props">
      <q-tr :props="props" class="cursor-pointer">
        <q-td key="number" :props="props" class="wrap-column">
          {{ props.row.id }}
        </q-td>
        <q-td key="description" :props="props" class="wrap-column">
          <div>{{ props.row.description }}</div>
          <template v-if="$q.screen.lt.md">
            <div>Qty: {{ formatNumber(props.row.ordered_quantity) }} |
              {{ formatNumber(props.row.assigned_quantity) }} |
              {{ formatNumber(props.row.completed_quantity) }}</div>
            <div v-if="show_cost">Biaya: Rp. {{ formatNumber(props.row.unit_cost) }}</div>
            <div>Harga: Rp. {{ formatNumber(props.row.unit_price) }}</div>
            <div><q-badge :color="props.row.completed_quantity >= props.row.ordered_quantity ? 'green' : 'red'">{{
              props.row.completed_quantity >= props.row.ordered_quantity ? 'Selesai' : 'Progress' }}</q-badge></div>
            <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes.substring(0, 30) }}...</div>
          </template>
        </q-td>
        <q-td key="quantity" :props="props" class="wrap-column">
          {{ formatNumber(props.row.ordered_quantity) }} |
          {{ formatNumber(props.row.assigned_quantity) }} |
          {{ formatNumber(props.row.completed_quantity) }}
        </q-td>
        <q-td key="progress" :props="props" class="wrap-column">
          {{ formatNumber(props.row.assigned_quantity / props.row.ordered_quantity * 100) }} % |
          {{ formatNumber(props.row.completed_quantity / props.row.ordered_quantity * 100) }} %
        </q-td>

        <q-td key="unit_cost" :props="props" class="wrap-column">
          {{ formatNumber(props.row.unit_cost) }}
        </q-td>
        <q-td key="subtotal_cost" :props="props" class="wrap-column">
          {{ formatNumber(props.row.ordered_quantity * props.row.unit_cost) }}
        </q-td>
        <q-td key="unit_price" :props="props" class="wrap-column">
          {{ formatNumber(props.row.unit_price) }}
        </q-td>
        <q-td key="subtotal_price" :props="props" class="wrap-column">
          {{ formatNumber(props.row.ordered_quantity * props.row.unit_price) }}
        </q-td>
        <q-td key="notes" :props="props" class="wrap-column">
          <div v-if="props.row.notes">{{ props.row.notes?.substring(0, 50) }}...</div>
        </q-td>
        <q-td key="action" :props="props">
          <div class="flex justify-end q-gutter-xs no-wrap">
            <q-btn v-if="props.row.notes?.length > 0" dense size="sm" flat round color="grey" icon="notes"
              @click="showNotes(props.row.notes)" />
            <q-btn dense flat round color="grey" icon="edit" @click="openDialog(props.rowIndex)" />
            <q-btn dense flat round color="negative" icon="delete" @click="removeItem(props.rowIndex)" />
          </div>
        </q-td>
      </q-tr>
    </template>
  </q-table>
  <div class="q-pt-md flex justify-end q-gutter-sm">
    <div class="bg-grey-4 q-pa-sm text-right" style="min-width: 100px">
      <div class="text-caption">Total Qty:</div>
      <div class="text-bold">{{ formatNumber(total_qty) }}</div>
    </div>
    <div class="bg-grey-4 q-pa-sm text-right" style="min-width: 100px">
      <div class="text-caption">Qty Diambil:</div>
      <div class="text-bold">{{ formatNumber(total_assigned_qty) }}</div>
    </div>
    <div class="bg-grey-4 q-pa-sm text-right" style="min-width: 100px">
      <div class="text-caption">Qty Selesai:</div>
      <div class="text-bold">{{ formatNumber(total_completed_qty) }}</div>
    </div>
    <div class="bg-grey-4 q-pa-sm text-right" style="min-width: 200px">
      <div class="text-caption">GRAND TOTAL:</div>
      <div class="text-h6">Rp. {{ formatNumber(grand_total_price) }}</div>
    </div>
  </div>

  <q-dialog v-model="notesDialog" style="min-width: 350px;" class="q-pa-sm">
    <q-card>
      <q-card-section>
        <div class="text-subtitle2">Catatan</div>
        <div class="text-body2">{{ selectedNote }}</div>
      </q-card-section>
    </q-card>
  </q-dialog>
  <q-dialog v-model="dialog" persistent>

    <q-card style="min-width: 350px;" class="q-pa-sm">
      <q-form @submit.prevent="save" @validation-error="scrollToFirstErrorField">
        <q-card-section>
          <div class="text-h6">{{ form.id === null ? 'Tambah Item' : 'Edit Item' }}</div>
        </q-card-section>
        <q-card-section>
          <input type="hidden" name="id" v-model="form.id" />
          <input type="hidden" name="order_id" v-model="form.order_id" />
          <q-input v-model="form.description" dense label="Deskripsi" autofocus lazy-rules :disable="form.processing"
            :error="!!form.errors.description" :error-message="form.errors.description" :rules="[
              (val) => (val && val.length > 0) || 'Deskripsi harus diisi.',
            ]" />
          <LocaleNumberInput v-model="form.ordered_quantity" dense label="Kwantitas" lazy-rules
            :disable="form.processing" :error="!!form.errors.ordered_quantity"
            :error-message="form.errors.ordered_quantity" :rules="[
              (val) => (val > 0) || 'Kwantitas harus diisi.',
            ]" />
          <LocaleNumberInput v-model="form.unit_cost" dense label="Biaya (Rp)" :disable="form.processing" />
          <LocaleNumberInput v-model="form.unit_price" dense label="Harga (Rp)" :disable="form.processing" />
          <q-input v-model="form.notes" dense label="Catatan" type="textarea" autogrow length="100"
            :disable="form.processing" />
          <q-card-actions align="center" class="q-pt-lg">
            <q-btn icon="check" label="Simpan" color="primary" type="submit" />
            <q-btn icon="cancel" label="Batal" color="grey" v-close-popup />
          </q-card-actions>
        </q-card-section>
      </q-form>
    </q-card>

  </q-dialog>

</template>
