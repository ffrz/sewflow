<script setup>
import { formatNumber } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";
import dayjs from "dayjs";
const page = usePage();
const title = `Rincian Gaji #${page.props.data.id}`;
const items = page.props.data.payments;
console.log(page.props.data);
</script>

<template>
  <i-head :title="title" />
  <authenticated-layout>
    <template #title>{{ title }}</template>
    <div class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="q-card col">
            <q-card-section>
              <q-card-section>
                <div class="text-h6 q-pb-md">Rincian Upah Kerja</div>
                <div>Penjahit: #{{ page.props.data.tailor.id }}: {{ page.props.data.tailor.name }}
                </div>
                <div>Periode: {{ dayjs(page.props.data.period_start).format('DD-MM-YYYY') }} - {{
                  dayjs(page.props.data.period_end).format('DD-MM-YYYY') }} </div>
                <div class="q-py-sm" v-for="(item, i) in items">
                  <div>{{ i + 1 }} -
                    <a :href="route('admin.production-order.edit', { id: item.work_return.work_assignment.order_item.order.id })">Order
                      #{{ item.work_return.work_assignment.order_item.order.id }}</a>:
                    {{ item.work_return.work_assignment.order_item.order.customer?.name }} -
                    {{ item.work_return.work_assignment.order_item.order.model }}
                  </div>
                  <div>
                    <b>Pengembalian #{{ item.id }}</b> -
                    <q-icon name="history" /> {{ dayjs(item.datetime).format('DD-MM-YYYY HH:MM') }}:
                    {{ item.quantity }} pcs x Rp. {{ formatNumber(item.work_return.work_assignment.order_item.unit_cost) }}
                    = Rp. {{ formatNumber(item.quantity * item.work_return.work_assignment.order_item.unit_cost) }}
                  </div>
                </div>
                <hr>
                <div>Total Upah Kerja: Rp. {{ formatNumber(page.props.data.total_amount) }}</div>
              </q-card-section>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

  </authenticated-layout>
</template>
