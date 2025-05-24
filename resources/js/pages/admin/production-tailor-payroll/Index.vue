<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { router } from "@inertiajs/vue3";
import { handleDelete, handleFetchItems } from "@/helpers/client-req-handler";
import { check_role, getQueryParams, formatNumber } from "@/helpers/utils";
import { useQuasar } from "quasar";
import dayjs from "dayjs";

const title = "Penggajian";
const $q = useQuasar();
const showFilter = ref(false);
const rows = ref([]);
const loading = ref(true);
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

const columns = [
  {
    name: "id",
    label: "#",
    field: "id",
    align: "left",
    sortable: true,
  },
  {
    name: "tailor",
    label: "Penjahit",
    field: "tailor",
    align: "left",
  },
  {
    name: "total_amount",
    label: "Jumlah",
    field: "total_amount",
    align: "right",
  },
  {
    name: "notes",
    label: "Catatan",
    field: "notes",
    align: "left",
  },
  {
    name: "action",
    align: "right",
  },
];

const statuses = [
  { value: "all", label: "Semua" },
];

onMounted(() => {
  fetchItems();
});

const deleteItem = (row) =>
  handleDelete({
    message: `Hapus rekaman penggajian #${row.id}? Seluruh transaksi terkait akan dipulihkan.`,
    url: route("admin.production-tailor-payroll.delete", row.id),
    fetchItemsCallback: fetchItems,
    loading,
  });

const fetchItems = (props = null) => {
  handleFetchItems({
    pagination,
    filter,
    props,
    rows,
    url: route("admin.production-tailor-payroll.data"),
    loading,
  });
};

const onFilterChange = () => fetchItems();
const onRowClicked = (row) => router.get(route('admin.production-tailor-payroll.detail', { id: row.id }));
const computedColumns = computed(() => {
  if ($q.screen.gt.sm) return columns;
  return columns.filter((col) => col.name === "id" || col.name === "action");
});
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <template #right-button>
      <q-btn icon="add" color="primary" @click="router.get(route('admin.production-tailor-payroll.add'))" size="small"
        dense />
      <q-btn icon="bolt" class="q-ml-xs" color="warning" label="Generate&nbsp;"
        @click="router.get(route('admin.production-tailor-payroll.generate'))" size="small" dense />
      <q-btn class="q-ml-xs" :icon="!showFilter ? 'filter_alt' : 'filter_alt_off'" color="grey" size="small" dense
        @click="showFilter = !showFilter" />
    </template>
    <template #header v-if="showFilter">
      <q-toolbar class="filter-bar">
        <div class="row q-col-gutter-xs items-center q-pa-sm full-width">
          <q-select class="custom-select col-xs-12 col-sm-2" style="min-width: 150px" v-model="filter.status"
            :options="statuses" label="Status" dense map-options emit-value outlined
            @update:model-value="onFilterChange" />
          <q-input class="col" outlined dense debounce="300" v-model="filter.search" placeholder="Cari" clearable>
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </q-toolbar>
    </template>
    <div class="q-pa-sm">
      <q-table class="full-height-table" ref="tableRef" flat bordered square color="primary" row-key="id" virtual-scroll
        v-model:pagination="pagination" :filter="filter.search" :loading="loading" :columns="computedColumns"
        :rows="rows" :rows-per-page-options="[10, 25, 50]" @request="fetchItems" binary-state-sort>
        <template v-slot:loading>
          <q-inner-loading showing color="red" />
        </template>

        <template v-slot:no-data="{ icon, message, filter }">
          <div class="full-width row flex-center text-grey-8 q-gutter-sm">
            <q-icon size="2em" name="sentiment_dissatisfied" />
            <span>
              {{ message }}
              {{ filter ? " with term " + filter : "" }}</span>
            <q-icon size="2em" :name="filter ? 'filter_b_and_w' : icon" />
          </div>
        </template>

        <template v-slot:body="props">
          <q-tr :props="props" class="cursor-pointer" @click="onRowClicked(props.row)">
            <q-td key="id" :props="props" class="wrap-column">
              <div>#{{ props.row.id }} - <q-icon name="history" /> {{ $dayjs(props.row.date).format('YYYY-MM-DD') }}
              </div>
              <template v-if="$q.screen.lt.md">
                <div><q-icon name="apparel" /> {{ props.row.model }}</div>
                <div><q-icon name="person" /> {{ props.row.customer ? props.row.customer.name : '-' }}</div>
                <div>
                  <div class="q-pt-sm">{{ formatNumber(props.row.completed_quantity / props.row.total_quantity * 100)
                    }}%</div>
                  <q-linear-progress :value="props.row.total_quantity > 0
                    ? props.row.completed_quantity / props.row.total_quantity
                    : 0" color="primary" track-color="grey-3" size="10px" rounded stripe animated />
                </div>
                <div class="q-pt-sm">Rp. {{ formatNumber(props.row.total_cost) }} / Rp. {{
                  formatNumber(props.row.total_price) }}</div>
              </template>
            </q-td>

            <q-td key="tailor" :props="props">
              #{{ props.row.tailor.id }} - {{ props.row.tailor.name }}
            </q-td>
            <q-td key="total_amount" :props="props">
              {{ formatNumber(props.row.total_amount) }}
            </q-td>
            <q-td key="notes" :props="props">
              {{ props.row.notes }}
            </q-td>
            <q-td key="action" :props="props">
              <div class="flex justify-end">
                <q-btn :disabled="!check_role($CONSTANTS.USER_ROLE_ADMIN)" icon="more_vert" dense flat
                  style="height: 40px; width: 30px" @click.stop>
                  <q-menu anchor="bottom right" self="top right" transition-show="scale" transition-hide="scale">
                    <q-list style="width: 200px">
                      <q-item @click.stop="deleteItem(props.row)" clickable v-ripple v-close-popup>
                        <q-item-section avatar>
                          <q-icon name="delete_forever" />
                        </q-item-section>
                        <q-item-section>Hapus</q-item-section>
                      </q-item>
                    </q-list>
                  </q-menu>
                </q-btn>
              </div>
            </q-td>
          </q-tr>
        </template>
      </q-table>
    </div>
  </authenticated-layout>
</template>
