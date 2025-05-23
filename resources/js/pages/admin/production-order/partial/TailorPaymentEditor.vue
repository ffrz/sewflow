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
import DateTimePicker from "@/components/DateTimePicker.vue";
import dayjs from "dayjs";

const $q = useQuasar();
const page = usePage();
const dialog = ref(false);
const notesDialog = ref(false);
const selectedNote = ref('');
const selectedItem = ref(null);

function showNotes(note) {
  selectedNote.value = note;
  notesDialog.value = true;
}

const statuses = [
  { label: 'Ditugaskan', value: 'assigned' },
  { label: 'Dikerjakan', value: 'in_progress' },
  { label: 'Selesai', value: 'completed' },
];

let work_assignments = [];
let work_assignment_options = [];

const fetchWorkAssignments = async () => {
  try {
    const response = await axios.get(route('admin.production-work-return.assignments', { order_id: page.props.data.id }))
    work_assignments = response.data;
    work_assignment_options = work_assignments.map((i) => ({
      label: `#${i.id} - ${i.tailor.name} - ${i.order_item.description}: ${i.quantity} pt`, value: i.id
    }));
  } catch (error) {
    console.error('Gagal mengambil data assignments:', error)
  }
}

// Data list items
const items = ref([]);

// Form input untuk dialog
const form = useApiForm({
  order_id: page.props.data.id,
  datetime: dayjs().format('YYYY-MM-DD HH:mm:ss'),
  id: null,
  assignment_id: null,
  quantity: 0,
  notes: '',
});

// Reset form setiap buka dialog
function openDialog(index = null) {
  if (index === null) {
    form.id = null;
    form.assignment_id = null;
    form.datetime = dayjs().format('YYYY-MM-DD HH:mm:ss');
    form.quantity = 0;
    form.notes = '';
  } else {
    const item = items.value[index];
    form.id = item.id;
    form.datetime = item.datetime;
    form.assignment_id = item.assignment_id;
    form.quantity = item.quantity;
    form.notes = item.notes;
  }
  dialog.value = true;
}

// Tambah atau update item
function save() {
  handleSubmit({
    form,
    url: route('admin.production-work-return.save'),
    onSuccess: (res) => {
      if (Number(form.id) == 0) {
        items.value.push(res.item);
      } else {
        const index = items.value?.findIndex(item => item.id === res.item?.id)
        if (index >= 0) items.value.splice(index, 1, res.item)
      }
      dialog.value = false;
      Notify.create({ message: 'Penyetoran berhasil disimpan.' });
    },
    onError: (err) => {
      if (err.response && err.response.status === 422) {
        const errors = err.response.data.errors || {};
        form.errors = errors;

        // Optional: tampilkan error global jika ada
        if (err.response.data.message) {
          Notify.create({
            type: 'negative',
            message: err.response.data.message,
          });
        }
      } else {
        alert('Terjadi kesalahan server.');
      }
    }
  });
}

const loading = ref(false)

onMounted(() => {
  fetchItems();
  fetchWorkAssignments();
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

      if (item.id) {
        await axios.post(route('admin.production-work-return.delete', { id: item.id }));
      }

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
  descending: true,
});

const fetchItems = (props = null) => {
  handleFetchItems({
    filter,
    pagination,
    props,
    rows: items,
    url: route("admin.production-work-return.data", { order_id: form.order_id }),
    loading,
  });
};

const submit = () =>
  handleSubmit({ form, url: route('admin.production-work-assignment.save') });

// Kolom untuk q-table
const columns = [
  { name: 'id', label: '#', field: 'id', align: 'left' },
  { name: 'datetime', label: 'Waktu', field: 'datetime', align: 'left', sortable: true },
  { name: 'item', label: 'Pengambilan', field: 'item', align: 'left' },
  { name: 'tailor', label: 'Penjahit', field: 'tailor', align: 'left' },
  { name: 'quantity', label: 'Dikembalikan', field: 'quantity', align: 'right' },
  { name: 'notes', label: 'Catatan', field: 'notes', align: 'left' },
  { name: 'action', label: 'Aksi', field: 'action', align: 'center' },
];

const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => ['id', 'action'].includes(col.name));
});

const selectedOrderItem = computed(() => {
  return items.value.find(i => i.id === form.id);
})

</script>

<template>
  <q-btn icon="add" color="primary" size="sm" dense label="Tambah&nbsp;&nbsp;" @click="openDialog()" />
  <q-table :columns="computedColumns" :rows="items" row-key="number" dense flat bordered class="q-mt-md"
    @request="fetchItems" :loading="loading" :rows-per-page-options="[10, 25, 50]">
    <template v-slot:body="props">
      <q-tr :props="props" class="cursor-pointer">
        <q-td key="id" :props="props" class="wrap-column">
          {{ props.row.id }}
          <template v-if="$q.screen.lt.md">
            - {{ props.row.datetime }}
            <div><q-icon name="people" /> #{{ props.row.work_assignment.tailor.id }} - {{
              props.row.work_assignment.tailor.name }}</div>
            <div><q-icon name="apparel" /> #{{ props.row.work_assignment.id }}: {{
              props.row.work_assignment.order_item.description }} - {{
                props.row.work_assignment.quantity }} pt</div>
            <div><q-icon name="box" /> {{ formatNumber(props.row.quantity) }} pt</div>
            <div v-if="props.row.notes"><q-icon name="notes" /> {{ props.row.notes.substring(0, 30) }}...</div>
          </template>
        </q-td>
        <q-td key="datetime" :props="props" class="wrap-column">
          <div>{{ props.row.datetime }}</div>
        </q-td>
        <q-td key="item" :props="props" class="wrap-column">
          #{{ props.row.work_assignment.id }}: {{ props.row.work_assignment.order_item?.description }} : {{ props.row.work_assignment.quantity }} pt
        </q-td>
        <q-td key="tailor" :props="props" class="wrap-column">
          #{{ props.row.work_assignment.tailor.id }} - {{ props.row.work_assignment.tailor.name }}
        </q-td>
        <q-td key="quantity" :props="props" class="wrap-column">
          {{ formatNumber(props.row.quantity) }} pt
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
          <date-time-picker v-model="form.datetime" label="Waktu" :error="!!form.errors.datetime"
            :disable="form.processing" :error-message="form.errors.datetime" />
          <q-select v-model="form.assignment_id" label="Pengambilan" :options="work_assignment_options" map-options
            emit-value :error="!!form.errors.assignment_id" :disable="form.processing"
            :error-message="form.errors.assignment_id" />
          <LocaleNumberInput v-model="form.quantity" dense label="Dikembalikan" lazy-rules :disable="form.processing"
            :error="!!form.errors.quantity" :error-message="form.errors.quantity" :rules="[
              val => (val > 0) || 'Kwantitas harus diisi.',
              val => (!selectedOrderItem || val <= selectedOrderItem.ordered_quantity) || `Maksimum: ${selectedOrderItem.ordered_quantity}`
            ]" />
          <q-input v-model="form.notes" dense label="Catatan" type="textarea" autogrow length="100" />
          <q-card-actions align="center" class="q-pt-lg">
            <q-btn icon="check" label="Simpan" color="primary" type="submit" />
            <q-btn icon="cancel" label="Batal" color="grey" v-close-popup />
          </q-card-actions>
        </q-card-section>
      </q-form>
    </q-card>

  </q-dialog>

</template>
