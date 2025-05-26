<script setup>
import SummaryCard from "./cards/SummaryCard.vue";
import ChartCard from "./cards/ChartCard.vue";
import TopCard from "./cards/TopCard.vue";
import TopCard2 from "./cards/TopCard2.vue";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import { getQueryParams } from "@/helpers/utils";

const title = "Dashboard";
const showFilter = ref(true);
const selected_month = ref(getQueryParams()["month"] ?? "this_month");

const month_options = ref([
  { value: "this_week", label: "7 Hari Terakhir" },
  { value: "this_month", label: "30 Hari Terakhir" },
]);
const onFilterChange = () => {
  router.visit(route("admin.dashboard", { month: selected_month.value }));
};
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn
        class="q-ml-sm"
        :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'"
        color="grey"
        dense
        @click="showFilter = !showFilter"
      />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select
            class="custom-select col-12"
            style="min-width: 150px"
            v-model="selected_month"
            :options="month_options"
            label="Bulan"
            dense
            map-options
            emit-value
            outlined
            @update:model-value="onFilterChange"
          />
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
      <div>
        <div class="text-subtitle1 text-bold text-grey-8">Statistik Aktual</div>
        <summary-card class="q-py-none" />
      </div>
      <div class="q-pt-md">
        <div class="text-subtitle1 text-bold text-grey-8">
          Statistik
          {{ month_options.find((a) => a.value == selected_month).label }}
        </div>
      </div>
      <div class="q-pt-sm">
        <div class="row q-col-gutter-sm">
          <div class="col-md-6 col-12">
            <top-card class="full-width full-height"
              :items="[
                { id: 1, name: 'Alychia', total: 27515000 },
                { id: 2, name: 'Persada', total:  7525000 },
                { id: 3, name: 'G-Fashion', total: 5520000 },
                { id: 4, name: 'Parinda', total: 730000 },
                { id: 5, name: 'Anto', total: 1215000 },
              ]"
              title="Top 5 Customer"
              route_url="admin.customer.index"
            />
          </div>
          <div class="col-md-6 col-12">
            <top-card-2 class="full-width full-height"
              :items="[
                { id: 1, name: 'Deni', total: 122 },
                { id: 2, name: 'Wanda', total: 121 },
                { id: 3, name: 'Jeri', total: 93 },
                { id: 4, name: 'Uden', total: 88 },
                { id: 5, name: 'Dani', total: 60 },
              ]"
              title="Top 5 Karyawan"
              route_url="admin.employee.index"
            />
          </div>
        </div>
      </div>
      <div v-if="0">
        <chart-card class="q-py-none q-pt-sm" />
      </div>
    </div>
  </authenticated-layout>
</template>
