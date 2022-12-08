<template>
  <div class="sub-section">
    <div class="registrations-table">
        <vue-good-table
            :columns="columns"
            :rows="registrationsList"
            theme="black-rhino"
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
    <div class="edit-shelf" v-if="oneToEdit" v-scroll-lock="oneToEdit">
      <div class="content-wrap">
        <span class="options-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path d="M459.94 53.25a16.06 16.06 0 0 0-23.22-.56L424.35 65a8 8 0 0 0 0 11.31l11.34 11.32a8 8 0 0 0 11.34 0l12.06-12c6.1-6.09 6.67-16.01.85-22.38zM399.34 90 218.82 270.2a9 9 0 0 0-2.31 3.93L208.16 299a3.91 3.91 0 0 0 4.86 4.86l24.85-8.35a9 9 0 0 0 3.93-2.31L422 112.66a9 9 0 0 0 0-12.66l-9.95-10a9 9 0 0 0-12.71 0z"/><path d="M386.34 193.66 264.45 315.79A41.08 41.08 0 0 1 247.58 326l-25.9 8.67a35.92 35.92 0 0 1-44.33-44.33l8.67-25.9a41.08 41.08 0 0 1 10.19-16.87l122.13-121.91a8 8 0 0 0-5.65-13.66H104a56 56 0 0 0-56 56v240a56 56 0 0 0 56 56h240a56 56 0 0 0 56-56V199.31a8 8 0 0 0-13.66-5.65z"/></svg>
          <h2>Edit registration</h2>
        </span>

        <button class="close-button" @click="(oneToEdit = null)">
          <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32" d="M448 256c0-106-86-192-192-192S64 150 64 256s86 192 192 192 192-86 192-192z"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M320 320 192 192m0 128 128-128"/></svg>
        </button>
        <form id="edit-registrant">

          <h2>User contacts</h2>
          <input type="hidden" v-model="oneToEdit.id">
          <div class="double">
            <label for="fname">First name
              <input type="text" id="fname" v-model="oneToEdit.name"/></label>

            <label for="lname">Last name
              <input type="text" id="lname" v-model="oneToEdit.surname"/></label>
          </div>

          <div class="double">
            <label for="company">Company
              <input type="text" id="company" v-model="oneToEdit.company"/></label>

            <label for="website">Website
              <input type="url" id="website" v-model="oneToEdit.website"/></label>
          </div>

          <label for="role">Role
              <input type="text" id="role" v-model="oneToEdit.role"/></label>

          <label for="email">Email address
              <input type="email" id="email" v-model="oneToEdit.email"/></label>

          <div>
            <label for="mobile">Mobile phone
              <input type="text" id="company" v-model="oneToEdit.mobile_phone"/></label>

            <label for="office">Office phone
              <input type="text" id="office" v-model="oneToEdit.office_phone"/></label>
          </div>

          <h2>Address</h2>
          <label for="address">Address
            <input type="text" id="address" v-model="oneToEdit.street_address"/></label>
          <label for="city">City
            <input type="text" id="city" v-model="oneToEdit.city"/></label>
          <label for="country">Country
            <input type="text" id="country" v-model="oneToEdit.country"/></label>

          <h2>Sign-up details</h2>
          <ul>
            <li>Coupon: <strong>{{oneToEdit.coupon_code}}</strong></li>
            <li>Billed: <strong>CHF {{oneToEdit.paid}}</strong></li>
            <li>Payment status: <strong>{{oneToEdit.payment_status}}</strong></li>
            <li>Sign-up date: <strong><span v-html="registrationDate(oneToEdit)">
            </span></strong></li>
            <li>User interests: <strong>{{oneToEdit.interests.replaceAll(',', ', ')}}</strong></li>
          </ul>

          <button
            class="save-edits form-button"
            type="button" @click="editRegistration('save')">
            Save edits
          </button>
          <button
            class="delete-registration form-button"
            type="button"
            @click="editRegistration('kill')">
            Delete registration
          </button>
        </form>
      </div>
    </div>
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
          label: 'Registered',
          field: this.registrationDate,
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
    async editRegistration(cmd) {
      this.oneToEdit.command = cmd;
      const data = JSON.stringify(this.oneToEdit);
      const url = auth.editRegistrations;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'POST', headers, body: data })
        .then(this.oneToEdit = null)
        .then((result) => result.json())
        .then((result) => { this.announce = result; }); // NEED TO INCLUDE MSG ANNUONCE COMPONENT
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
      this.oneToEdit = this.registrationsList[rowId];
    },
    registrationDate(rowObj) {
      const s = rowObj.sign_up_date;
      return s.split('-').reverse().join('-');
    },
  },
  mounted() {
    this.grabRegistrations();
  },
};
</script>