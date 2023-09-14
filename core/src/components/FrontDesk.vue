<template>
    <div class="front-desk sub-section">
        <message-announce v-if="announce" :message="announce" @closeMessage="killMessage()" />
        <div class="my-front-desk">
          <div class="registrations-list">
            <h3>Current registrations</h3>
            <div class="text-filter">
                <label for="textfilter">
                  <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32" d="M221.09 64a157.09 157.09 0 1 0 157.09 157.09A157.1 157.1 0 0 0 221.09 64z"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29 448 448"/></svg>
                  <input name="textfilter" type="text" placeholder="Filter/Search"
                  v-model="filter" />
                </label>
              </div>
            <table>
              <tr>
                <th>Name</th>
                <th>Surname</th>
                <th>Company</th>
                <th>Badge link</th>
                <th>Checked in</th>
              </tr>
              <tr v-for="i in filteredRows" :key="i.id">
                <td v-html="i.name"></td>
                <td v-html="i.surname"></td>
                <td v-html="i.company"></td>
                <td>
                  <a v-if="i.badge_link" :href="i.badge_link" target="_blank">Badge</a>
                  <button class="ad-hoc-badge-printer" v-else
                  @click="printBadge(i.id)">Print badge &#8594;</button>
                </td>
                <td>
                  <label :for="i.id">
                    <input type="checkbox"
                      v-if="i.checked_in === '1'"
                      :id="i.id"
                      checked="checked"
                      @change="updateAttendance(i.id, 'remove')"
                    />
                    <input type="checkbox"
                      v-else
                      :id="i.id"
                      @change="updateAttendance(i.id, 'add')"
                    />
                  </label>
                </td>
              </tr>
            </table>
          </div>
          <div class="ad-hoc-registration-form">
            <form @submit.prevent="adHocRegistration()">
              <h3>Add new registration</h3>
          <div class="double">
            <label for="fname">First name (Required)
              <input type="text" id="fname" v-model="newreg.name"/></label>

            <label for="lname">Last name (Required)
              <input type="text" id="lname" v-model="newreg.surname"/></label>
          </div>
          <div class="company_type_wrapper double">
            <label for="company">Company (Required)
              <input type="text" id="company" v-model="newreg.company"/></label>
                <label for="company_type">Company type (Required)
                  <select name="company_type" id="company_type" v-model="newreg.my_company_is">
                      <option value="Brand, Retailer, Manufacturer or Online Shop">
                        Brand, Retailer, Manufacturer or Online Shop</option>
                      <option value="Investor, Family Office,">
                        Investor, Family Office, Business Angel</option>
                      <option value="Media &amp; Press">
                        Media / Press / Journalism</option>
                      <option value="Public Administration / Institution">
                        Public Administration / Institution</option>
                      <option value="Research Institute, University, School">
                        Research Institute, University, School</option>
                      <option value="Vendor / Supplier of Services">
                        Vendor / Supplier of Services for Innovation and e-Commerce</option>
                      <option value="Other">Other</option>
                  </select>
                </label>
            </div>
            <div class="double">
              <label for="role">Role (Required)
                <input type="text" id="role" v-model="newreg.role"/></label>
                <label for="email">Email address (Required)
                <input type="email" id="email" v-model="newreg.email"/></label>
            </div>
            <div class="double">
              <label for="phone">Mobile phone (Required)
              <input type="tel" id="phone" v-model="newreg.mobile"/></label>
                <button class="form-button" style="margin-top:20px;"
                :class="{ 'ready' : this.ready}">Submit</button>
            </div>
          </form>
          </div>
        </div>
    </div>
</template>

<script>
import auth from '@/assets/auth';
import MessageAnnounce from './MessageAnnounce.vue';

export default {

  data() {
    return {
      announce: null,
      registrationsDesk: [],
      filter: '',
      ready: false,
      newreg: {
        name: '',
        surname: '',
        company: '',
        my_company_is: '',
        role: '',
        email: '',
        mobile: '',
      },
    };
  },
  components: {
    MessageAnnounce,
  },
  mounted() {
    this.grabAllRegistrations();
  },
  computed: {
    filteredRows() {
      return this.registrationsDesk.filter((row) => {
        const company = row.company.toString().toLowerCase();
        const name = row.name.toLowerCase();
        const surname = row.surname.toLowerCase();
        const searchTerm = this.filter.toLowerCase();

        return company.includes(searchTerm)
      || name.includes(searchTerm)
      || surname.includes(searchTerm);
      });
    },
  },
  methods: {
    async updateAttendance(id, cmd) {
      const url = '/wp-json/core-vue/do_my_check_in';
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      const data = JSON.stringify({ id, cmd });
      fetch(url, { method: 'POST', headers, body: data })
        .then((result) => result.json())
        .then((result) => {
          this.announce = result;
          if (this.announce[0] !== 'Success') {
            this.announce = ['Something went wrong', 'Please try again'];
          }
        });
    },
    async grabAllRegistrations() {
      this.registrationsList = [];
      const url = auth.allRegistrations;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'GET', headers })
        .then((result) => result.json())
        .then((result) => { this.registrationsDesk = result.reverse(); });
    },
    async adHocRegistration() {
      if (!this.newreg.name || !this.newreg.surname || !this.newreg.email
      || !this.newreg.company || !this.newreg.mobile) {
        this.announce = ['Hold it right there...', 'You need to supply all required fields (name, surname, company, email, role and phone)'];
        return;
      }
      const data = JSON.stringify(this.newreg);
      const url = auth.adHocRegistration;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'POST', headers, body: data })
        .then(this.oneToEdit = null)
        .then((result) => result.json())
        .then((result) => {
          this.announce = result;
          if (this.announce[0] === 'Success') {
            this.resetForm();
          }
        });
    },
    killMessage() {
      this.grabAllRegistrations();
      this.announce = null;
    },
    resetForm() {
      this.newreg = {
        name: '',
        surname: '',
        company: '',
        my_company_is: '',
        role: '',
        email: '',
      };
      this.ready = false;
    },
    async printBadge(id) {
      const url = auth.printBadge;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      const data = JSON.stringify({ ids: id });
      fetch(url, { method: 'POST', headers, body: data })
        .then((result) => result.json())
        .then((result) => {
          this.announce = result;
          if (this.announce[0] === 'Success') {
            this.grabAllRegistrations();
          }
        });
    },
  },
  watch: {
    newreg: {
      handler() {
        if (this.newreg.name && this.newreg.surname && this.newreg.email
        && this.newreg.company) {
          this.ready = true;
        } else {
          this.ready = false;
        }
      },
      deep: true,
    },
  },
};
</script>
