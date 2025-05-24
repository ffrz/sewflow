<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField, formatNumber } from "@/helpers/utils";
import { useApiForm } from '@/helpers/useApiForm';
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DatePicker from "@/components/DatePicker.vue";
import { ref, onMounted } from "vue";
import dayjs from "dayjs";
import { Dialog, Notify } from 'quasar';

const page = usePage();
const title = "Perhitungan Gaji";

const form = useApiForm({
  id: null,
  tailor_id: null,
  period_start: dayjs().format('YYYY-MM-DD'),
  period_end: dayjs().format('YYYY-MM-DD'),
  total_amount: 0,
  notes: '',
});

let tailors = [];
let tailor_options = [];
const filteredTailors = ref([]);

const items = ref([]);
const total_cost = ref(0);
const total_quantity = ref(0);
const dialog = ref(false);

const fetchTailors = async () => {
  try {
    const response = await axios.get(route('api.active-tailors'))
    tailors = response.data;
    tailor_options = tailors.map((i) => ({
      label: `#${i.id} - ${i.name}`, value: i.id
    }));
  } catch (error) {
    console.error('Gagal mengambil data penjahit:', error)
  }
}

const preview = () => handleSubmit({
  form,
  url: route('admin.production-tailor-payroll.preview'),
  onSuccess: (res) => {
    items.value = res.items;
    total_cost.value = res.total_cost;
    total_quantity.value = res.total_quantity;

    if (res.items.length > 0) {
      dialog.value = true;
      form.total_amount = total_cost.value;
    }
    else {
      Notify.create({ message: 'Tidak ada rekaman.' });
    }
  }
});

const save = () => handleSubmit({
  form,
  url: route('admin.production-tailor-payroll.save'),
  onSuccess: (res) => {
    Notify.create({ message: 'Rekaman gaji telah disimpan.' });
    dialog.value = false;
    form.tailor_id = null;
  }
});
const filterTailors = (val, update) => {
  update(() => {
    filteredTailors.value = tailor_options.filter(item => item.label.toLowerCase().includes(val.toLowerCase()));
  });
};

onMounted(() => {
  fetchTailors();
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-select autofocus v-model="form.tailor_id" label="Penjahit" use-input input-debounce="300" clearable
                :options="filteredTailors" map-options emit-value @filter="filterTailors"
                :error="!!form.errors.tailor_id" :disable="form.processing" :error-message="form.errors.tailor_id">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Penjahit tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <date-picker v-model="form.period_start" label="Dari Tanggal" :error="!!form.errors.period_start"
                :disable="form.processing" :error-message="form.errors.period_start" />
              <date-picker v-model="form.period_end" label="sd. Tanggal" :error="!!form.errors.period_end"
                :disable="form.processing" :error-message="form.errors.period_end" />
            </q-card-section>
            <q-card-section class="q-gutter-sm q-pt-none">
              <q-btn icon="visibility" type="submit" label="Tampilkan" color="primary" :disable="form.processing"
                @click="preview" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.production-tailor-payroll.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
        <q-dialog v-model="dialog" style="max-width: 350px;" class="q-pa-sm">
          <q-card style="max-width: 500px; width: 100%;" square flat bordered class="col">
            <template v-if="items.length > 0">
              <q-card-section>
                <div class="text-h6 q-pb-md">Rincian Upah Kerja</div>
                <div>Penjahit: #{{ items[0].work_assignment.tailor.id }}: {{ items[0].work_assignment.tailor.name }}
                </div>
                <div>Periode: {{ dayjs(form.period_start).format('DD-MM-YYYY') }} - {{
                  dayjs(form.period_end).format('DD-MM-YYYY') }} </div>
                <div class="q-py-sm" v-for="(item, i) in items">
                  <div>{{ i + 1 }} -
                    <a :href="route('admin.production-order.edit', { id: item.work_assignment.order_item.order.id })">Order
                      #{{ item.work_assignment.order_item.order.id }}</a>:
                    {{ item.work_assignment.order_item.order.customer?.name }} -
                    {{ item.work_assignment.order_item.order.model }}
                  </div>
                  <div>
                    <b>Pengembalian #{{ item.id }}</b> -
                    <q-icon name="history" /> {{ dayjs(item.datetime).format('DD-MM-YYYY HH:MM') }}:
                    {{ item.quantity }} pcs x Rp. {{ formatNumber(item.work_assignment.order_item.unit_cost) }}
                    = Rp. {{ formatNumber(item.quantity * item.work_assignment.order_item.unit_cost) }}
                  </div>
                </div>
              </q-card-section>
              <q-card-section class="q-pt-none">
                <LocaleNumberInput v-model:modelValue="form.total_amount" label="Total Pembayaran" lazyRules :disable="form.processing"
                  :error="!!form.errors.total_amount" :errorMessage="form.errors.total_amount" :rules="[]" />
                <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="1000" label="Catatan"
                  lazy-rules :disable="form.processing" :error="!!form.errors.notes"
                  :error-message="form.errors.notes" />
              </q-card-section>
              <q-card-section class="q-pt-sm" align="center">
                <q-btn color="primary" icon="check" label="Bayar" @click="save" />
                <q-btn icon="close" label="Tutup" v-close-popup class="q-ml-sm" />
              </q-card-section>
            </template>
          </q-card>
        </q-dialog>
      </div>
    </q-page>

  </authenticated-layout>
</template>
