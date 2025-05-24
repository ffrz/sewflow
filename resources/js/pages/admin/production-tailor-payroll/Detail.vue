<script setup>
import { formatNumber } from "@/helpers/utils";
import { router, usePage } from "@inertiajs/vue3";
import { ref } from "vue";

const page = usePage();
const title = `Order #${page.props.data.id}`;
const tab = ref("main");
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
              <q-tab name="main" label="Info Order" disabled />
              <q-tab name="detail" label="Rincian Order" />
              <q-tab name="assignment" label="Penugasan" disabled />
              <q-tab name="return" label="Setoran Pek." disabled />
            </q-tabs>
            <q-tab-panels v-model="tab">
              <q-tab-panel name="main">
                
              </q-tab-panel>

              <q-tab-panel name="order_items">
                <!-- <q-table flat bordered square color="primary" class="full-height-table full-height-table2" row-key="id"
                  virtual-scroll v-model:pagination="pagination" :filter="filter.search" :loading="loading"
                  :columns="computedColumns" :rows="rows" :rows-per-page-options="[10, 25, 50]" @request="fetchItems"
                  binary-state-sort>
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
                    <q-tr :props="props">
                      <q-td key="id" :props="props">
                        <div class="flex q-gutter-sm">
                          <div><b>#{{ props.row.id }}</b></div>
                          <div>- {{ $dayjs(new Date(props.row.created_datetime)).format("DD/MM/YYYY hh:mm:ss") }}</div>
                          <div>- {{ props.row.created_by ? props.row.created_by.username : '--' }}</div>
                        </div>
                        <template v-if="$q.screen.lt.md">
                          <div class="">
                            {{ $CONSTANTS.STOCKMOVEMENT_REFTYPES[props.row.ref_type] }}
                          </div>
                          <div
                            :class="props.row.quantity < 0 ? 'text-red-10' : (props.row.quantity > 0 ? 'text-green-10' : '')">
                            <q-icon
                              :name="props.row.quantity < 0 ? 'arrow_downward' : (props.row.quantity > 0 ? 'arrow_upward' : '')" />
                            {{ formatNumber(props.row.quantity) }}
                          </div>
                        </template>
                      </q-td>
                      <q-td key="type" :props="props">
                        {{ $CONSTANTS.STOCKMOVEMENT_REFTYPES[props.row.ref_type] }}
                      </q-td>
                      <q-td key="quantity" :props="props">
                        <div
                          :class="props.row.quantity < 0 ? 'text-red-10' : (props.row.quantity > 0 ? 'text-green-10' : '')">
                          <q-icon
                            :name="props.row.quantity < 0 ? 'arrow_downward' : (props.row.quantity > 0 ? 'arrow_upward' : '')" />
                          {{ formatNumber(props.row.quantity) }}
                        </div>
                      </q-td>
                    </q-tr>
                  </template>
                </q-table> -->

              </q-tab-panel>
            </q-tab-panels>
          </q-card>
        </div>
      </div>
    </div>
    <!-- <q-page class="row justify-center">
      <div class="col col-lg-6 q-pa-sm">
        <div class="row">
          <q-card square flat bordered class="col">
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Utama</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:150px">ID</td>
                    <td style="width:1px">:</td>
                    <td>#{{ page.props.data.id }}</td>
                  </tr>
                  <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $dayjs(page.props.data.date).format('YYYY-MM-DD') }}</td>
                  </tr>
                  <tr>
                    <td>Brand</td>
                    <td>:</td>
                    <td>{{ page.props.data.brand.name }}</td>
                  </tr>
                  <tr>
                    <td>Model</td>
                    <td>:</td>
                    <td>{{ page.props.data.model }}</td>
                  </tr>
                  <tr>
                    <td>Total Kwantitas</td>
                    <td>:</td>
                    <td>{{ formatNumber(page.props.data.total_quantity) }}</td>
                  </tr>
                  <tr>
                    <td>Total Harga</td>
                    <td>:</td>
                    <td>Rp. {{ formatNumber(page.props.data.total_price) }}</td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
            <q-card-section>
              <div class="text-subtitle1 text-bold text-grey-8">Info Tambahan</div>
              <table class="detail">
                <tbody>
                  <tr>
                    <td style="width:150px">Deadline</td>
                    <td style="width:1px">:</td>
                    <td>{{ page.props.data.due_date ? $dayjs(page.props.data.due_date).format('YYYY-MM-DD') : '-' }}
                    </td>
                  </tr>
                  <tr>
                    <td>Status Order</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.ORDER_STATUSES[page.props.data.status] }}</td>
                  </tr>
                  <tr>
                    <td>Status Pembayaran</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.ORDER_PAYMENT_STATUSES[page.props.data.payment_status] }}</td>
                  </tr>
                  <tr>
                    <td>Status Pengiriman</td>
                    <td>:</td>
                    <td>{{ $CONSTANTS.ORDER_DELIVERY_STATUSES[page.props.data.delivery_status] }}</td>
                  </tr>
                </tbody>
              </table>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </q-page> -->
  </authenticated-layout>
</template>
