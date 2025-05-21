import { ref } from 'vue';

export function useCustomerFilter(customersRaw, includeAllOption = false) {
  const baseCustomers = customersRaw.map((item) => {
    return { value: item.id, label: '#' + item.id + ' - ' + item.name };
  });

  const customers = includeAllOption
    ? [{ value: 'all', label: 'Semua' }, ...baseCustomers]
    : baseCustomers;

  const filteredCustomers = ref([...customers]);

  const filterCustomerFn = (val, update) => {
    update(() => {
      filteredCustomers.value = customers.filter(item =>
        item.label.toLowerCase().includes(val.toLowerCase())
      );
    });
  };

  return {
    filteredCustomers,
    filterCustomerFn,
    customers,
  };
}
