<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { scrollToFirstErrorField } from "@/helpers/utils";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Order";
const brands = page.props.brands.map((b) => (
  { label: b.name, value: b.id }
));
const order_types = [
  { label: 'Maklun', value: 'maklon' },
  { label: 'Konveksi ', value: 'full_production' },
];

const form = useForm({
  id: page.props.data.id,
  brand_id: page.props.data.brand_id,
  order_type: page.props.data.order_type,
  order_date: page.props.data.order_date,
  due_date: page.props.data.due_date,
  status: page.props.data.status,
  due_date: page.props.data.due_date,
});

const submit = () =>
  handleSubmit({ form, url: route('admin.order.save') });

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <!-- <q-card-section>
              <div class="text-subtitle1">Info Order</div>
            </q-card-section> -->
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-select v-model="form.brand_id" label="Brand" :options="brands" map-options emit-value
                :error="!!form.errors.brand_id" :disable="form.processing" :error-message="form.errors.brand_id" />
              <q-select v-model="form.order_type" label="Jenis Order" :options="order_types" map-options emit-value
                :error="!!form.errors.order_type" :disable="form.processing" :error-message="form.errors.order_type" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="255" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes"
                :rules="[]" />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.order.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>
