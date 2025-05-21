<script setup>
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import OrderInfoEditor from "./partial/OrderInfoEditor.vue";
import OrderDetailsEditor from "./partial/OrderDetailsEditor.vue";

const page = usePage();
const tab = ref('main');
const title = (!!page.props.data.id ? `Order #${page.props.data.id}` : "Order Baru");

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
              <q-tab name="assignment" label="Penugasan" :disable="!page.props.data.id" />
              <q-tab name="return" label="Setoran Pek." :disable="!page.props.data.id" />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                <OrderInfoEditor />
              </q-tab-panel>
              <q-tab-panel name="detail">
                <OrderDetailsEditor />
              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </div>
  </authenticated-layout>
</template>
