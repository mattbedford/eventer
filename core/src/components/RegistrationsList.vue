<template>
  <div class="sub-section">
    <div class="registrations-table">
        <vue-good-table
            :columns="columns"
            :rows="registrationsList"
            :search-options="{
              enabled: true
            }"
            :pagination-options="{
                enabled: true,
                perPage: 50,
                position: 'bottom',
            }">
            <template slot="table-row" slot-scope="props">
                <span v-if="props.column.field == 'edit'">
                    <button @click="editRegistrant(props.row.originalIndex)">Edit</button>
                </span>
            </template>
        </vue-good-table>
    </div>
    <h1 v-if="oneToEdit">{{oneToEdit}}</h1>
  </div>
</template>

<script>
import auth from '@/assets/auth';
import 'vue-good-table/dist/vue-good-table.css';
import { VueGoodTable } from 'vue-good-table';

export default {

  name: 'SetUp',
  components: {
    VueGoodTable,
  },
  data() {
    return {
      oneToEdit: null,
      registrationsList: [],
      columns: [
        {
          label: 'Name',
          field: this.concatName,
        },
        {
          label: 'Email',
          field: 'email',
        },
        {
          label: 'Company',
          field: 'company',
        },
        {
          label: 'Coupon',
          field: 'coupon_code',
        },
        {
          label: 'Paid',
          field: this.paidAmount,
        },
        {
          label: 'Printed',
          field: this.printedOrNot,
        },
        {
          label: 'View/Edit',
          field: 'edit',
        },
      ],
    };
  },
  methods: {
    async grabRegistrations() {
      const url = auth.allRegistrations;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'GET', headers })
        .then((result) => result.json())
        .then((result) => { this.registrationsList = result; });
    },
    editButton(rowObj) {
      const z = rowObj.id;
      const x = `<button @click="editRegistrant(${z})">View/Edit</button>`;
      return x;
    },
    concatName(rowObj) {
      return rowObj.name + ' ' + rowObj.surname;
    },
    paidAmount(rowObj) {
      return 'CHF ' + rowObj.paid;
    },
    printedOrNot(rowObj) {
      if (rowObj.printed === '0') {
        return 'No';
      }
      return 'Yes';
    },
    editRegistrant(rowId) {
      alert(rowId);
      this.oneToEdit = rowId;
    },
  },
  mounted() {
    this.grabRegistrations();
  },
};
</script>
