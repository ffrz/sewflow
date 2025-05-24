<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { handleSubmit } from "@/helpers/client-req-handler";
import { create_options_from_operational_cost_categories, scrollToFirstErrorField } from "@/helpers/utils";
import LocaleNumberInput from "@/components/LocaleNumberInput.vue";
import DatePicker from "@/components/DatePicker.vue";
import { ref, onMounted } from "vue";

const page = usePage();
const title = (!!page.props.data.id ? "Edit" : "Tambah") + " Biaya Operasional";
const categories = ref([]);
const filteredCategories = ref([...categories.value]);
const form = useForm({
  id: page.props.data.id,
  category_id: page.props.data.category_id,
  date: page.props.data.date,
  description: page.props.data.description,
  notes: page.props.data.notes,
  amount: parseFloat(page.props.data.amount),
});


let tailors = [];
let tailor_options = [];
const filteredTailors = ref();

const fetchTailors = async () => {
  try {
    const response = await axios.get(route('api.active-tailors'))
    tailors = response.data;
    tailor_options = tailors.map((i) => ({
      label: `#${i.id} - ${i.name}`, value: i.id
    }));
    alert(railors)
  } catch (error) {
    console.error('Gagal mengambil data penjahit:', error)
  }
}

const submit = () => handleSubmit({ form, url: route('admin.operational-cost.save') });
const filterTailors = (val, update) => {
  update(() => {
    filteredTailors.value = tailor_options.value.filter(item => item.label.toLowerCase().includes(val.toLowerCase()));
  });
};

onMounted(() => {

});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <q-form class="row" @submit.prevent="submit" @validation-error="scrollToFirstErrorField">
          <q-card square flat bordered class="col">
            <q-card-section class="q-pt-none">
              <input type="hidden" name="id" v-model="form.id" />
              <q-select autofocus v-model="form.category_id" label="Kategori" use-input input-debounce="300" clearable
                :options="filteredCategories" map-options emit-value @filter="filterCategories"
                :error="!!form.errors.category_id" :disable="form.processing">
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section>Kategori tidak ditemukan</q-item-section>
                  </q-item>
                </template>
              </q-select>
              <date-picker v-model="form.period_start" label="Dari Tanggal" :error="!!form.errors.period_start"
                :disable="form.processing" :error-message="form.errors.period_start" />
              <date-picker v-model="form.period_end" label="sd. Tanggal" :error="!!form.errors.period_end"
                :disable="form.processing" :error-message="form.errors.period_end" />
              <q-input v-model.trim="form.description" label="Deskripsi" lazy-rules :error="!!form.errors.description"
                :disable="form.processing" :error-message="form.errors.description" :rules="[
                  (val) => (val && val.length > 0) || 'Deskripsi harus diisi.',
                ]" />
              <LocaleNumberInput v-model:modelValue="form.amount" label="Jumlah" lazyRules :disable="form.processing"
                :error="!!form.errors.amount" :errorMessage="form.errors.amount" :rules="[]" />
              <q-input v-model.trim="form.notes" type="textarea" autogrow counter maxlength="1000" label="Catatan"
                lazy-rules :disable="form.processing" :error="!!form.errors.notes" :error-message="form.errors.notes" />
            </q-card-section>
            <q-card-section class="q-gutter-sm">
              <q-btn icon="save" type="submit" label="Simpan" color="primary" :disable="form.processing" />
              <q-btn icon="cancel" label="Batal" :disable="form.processing"
                @click="router.get(route('admin.operational-cost.index'))" />
            </q-card-section>
          </q-card>
        </q-form>
      </div>
    </q-page>

  </authenticated-layout>
</template>
