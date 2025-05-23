<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import OrderInfoEditor from "./partial/OrderInfoEditor.vue";
import OrderDetailsEditor from "./partial/OrderDetailsEditor.vue";
import WorkAssignmentEditor from "./partial/WorkAssignmentEditor.vue";
import WorkReturnEditor from "./partial/WorkReturnEditor.vue";
import TailorPaymentEditor from "./partial/TailorPaymentEditor.vue";

const page = usePage();
const tab = ref('main');
const title = computed(() => {
  return (!!page.props.data.id ? `PO #${page.props.data.id} - ${page.props.data.model}` : "Order Baru");
});

const onSaved = ({ isNew, data }) => {
  if (isNew) {
    tab.value = 'detail';
    page.props.data.id = data.id;
    page.props.data.model = data.model;
  }
}

</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="q-card col">
            <q-tabs v-model="tab" align="left">
              <q-tab name="main" label="Info Order" />
              <q-tab name="detail" label="Rincian Order" :disable="!page.props.data.id" />
              <q-tab name="assignment" label="Pengambilan" :disable="!page.props.data.id" />
              <q-tab name="return" label="Pengembalian" :disable="!page.props.data.id" />
              <q-tab name="wages" label="Upah" :disable="!page.props.data.id" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <OrderInfoEditor @saved="onSaved" />
              </q-tab-panel>
              <q-tab-panel name="detail">
                <OrderDetailsEditor />
              </q-tab-panel>
              <q-tab-panel name="assignment">
                <WorkAssignmentEditor />
              </q-tab-panel>
              <q-tab-panel name="return">
                <WorkReturnEditor />
              </q-tab-panel>
              <q-tab-panel name="wages">
                <TailorPaymentEditor />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
