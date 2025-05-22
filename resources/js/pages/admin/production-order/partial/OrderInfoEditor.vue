<script setup>
import { router, usePage } from "@inertiajs/vue3";
import { useApiForm } from '@/helpers/useApiForm';
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";
import { useCustomerFilter } from "@/helpers/useCustomerFilter";
import { computed } from "vue";
import DatePicker from "@/components/DatePicker.vue";
import dayjs from "dayjs";
import { Notify } from 'quasar'
const page = usePage();

const { filteredCustomers, filterCustomerFn } = useCustomerFilter(page.props.customers);

const types = Object.entries(window.CONSTANTS.PRODUCTION_ORDER_TYPES).map(([k, v]) => ({ label: v, value: k }));
const statuses = Object.entries(window.CONSTANTS.PRODUCTION_ORDER_STATUSES).map(([k, v]) => ({ label: v, value: k }));
const payment_statuses = Object.entries(window.CONSTANTS.PRODUCTION_ORDER_PAYMENT_STATUSES).map(([k, v]) => ({ label: v, value: k }));
const delivery_statuses = Object.entries(window.CONSTANTS.PRODUCTION_ORDER_DELIVERY_STATUSES).map(([k, v]) => ({ label: v, value: k }));

const form = useApiForm({
  id: page.props.data.id,
  customer_id: page.props.data.customer_id,
  model: page.props.data.model,
  date: dayjs(page.props.data.date).format('YYYY-MM-DD'),
  type: page.props.data.type,
  due_date: page.props.data.due_date ? dayjs(page.props.data.due_date).format('YYYY-MM-DD') : null,
  status: page.props.data.status,
  payment_status: page.props.data.payment_status,
  delivery_status: page.props.data.delivery_status,
  notes: page.props.data.notes,
});

const selectedCustomerLabel = computed(() => {
  const found = filteredCustomers.value.find(c => Number(c.value) === Number(form.customer_id))
  return found ? found.label : ''
});

const emit = defineEmits(['saved']); // untuk Vue 3 Composition API

const submit = () =>
  handleSubmit({
    form,
    url: route('admin.production-order.save'),
    onSuccess: (res) => {
      const isNew = !page.props.data.id;
      Notify.create({
        message: 'Item berhasil dismpan'
      });
      emit('saved', { isNew, data: res.data });
    }
  });
</script>
<template>
  <q-form @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
    <q-card-section class="q-pa-none">
      <input type="hidden" name="id" v-model="form.id" />
      <q-select v-show="false" v-model="form.type" label="Jenis Order" :options="types" map-options emit-value
        :error="!!form.errors.type" :disable="form.processing" :error-message="form.errors.type" />
      <date-picker v-model="form.date" label="Tanggal Order" :error="!!form.errors.date" :disable="form.processing" />
      <q-select v-model="form.customer_id" label="Pelanggan" use-input input-debounce="300" clearable
        :options="filteredCustomers" map-options emit-value @filter="filterCustomerFn" option-label="label"
        :display-value="selectedCustomerLabel" option-value="value" :error="!!form.errors.customer_id"
        :disable="form.processing">
        <template v-slot:no-option>
          <q-item>
            <q-item-section>Pelanggan tidak ditemukan</q-item-section>
          </q-item>
        </template>
      </q-select>
      <q-input v-model.trim="form.model" type="text" maxlength="100" label="Nama Model" :disable="form.processing"
        :error="!!form.errors.model" :error-message="form.errors.model" lazy-rules :rules="[
          (val) => (val && val.length > 0) || 'Nama harus diisi.',
        ]" />
      <date-picker v-model="form.due_date" label="Batas Pengerjaan / Deadline" :error="!!form.errors.due_date"
        :disable="form.processing" :error-message="form.errors.due_date" />
      <q-select v-show="false" v-model="form.status" label="Status Order" :options="statuses" map-options emit-value
        :error="!!form.errors.status" :disable="form.processing" :error-message="form.errors.status" />
      <q-select v-show="false" v-model="form.payment_status" label="Status Pembayaran" :options="payment_statuses"
        map-options emit-value :error="!!form.errors.payment_status" :disable="form.processing"
        :error-message="form.errors.payment_status" />
      <q-select v-show="false" v-model="form.delivery_status" label="Status Pengiriman" :options="delivery_statuses"
        map-options emit-value :error="!!form.errors.delivery_status" :disable="form.processing"
        :error-message="form.errors.delivery_status" />
      <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
        :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes" />
    </q-card-section>
    <q-card-actions class="q-pa-none">
      <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
      <q-btn icon="cancel" label="Batal" :disable="form.processing" @click="router.get(route('admin.production-order.index'))" />
    </q-card-actions>
  </q-form>
</template>