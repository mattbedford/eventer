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
                <th>Attended</th>
              </tr>
              <tr v-for="i in filteredRows" :key="i.id">
                <td v-html="i.name"></td>
                <td v-html="i.surname"></td>
                <td v-html="i.company"></td>
                <td><a :href="i.badge_link" target="_blank">Badge</a></td>
                <td>
                  <label :for="i.id">
                    <input type="checkbox"
                      :id="i.id"
                      :checked="i.attended"
                      @change="updateAttendance(i.id)"
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

          <div>
            <label for="company">Company (Required)
              <input type="text" id="company" v-model="newreg.company"/></label>
          </div>

          <div class="company_type_wrapper">
                <p>Company type</p>
                <label for="company_type">
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
            <div>
              <label for="role">Role
                <input type="text" id="role" v-model="newreg.role"/></label>
            </div>
            <div>
              <label for="email">Email address (Required)
                <input type="email" id="email" v-model="newreg.email"/></label>
                <button class="form-button" style="margin-top:20px;">Submit</button>
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
      newreg: {
        name: '',
        surname: '',
        company: '',
        my_company_is: '',
        role: '',
        email: '',
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
    async updateAttendance(id) {
      console.log(id);
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
        .then((result) => { this.registrationsDesk = result; });
    },
    async adHocRegistration() {
      if (!this.newreg.name || !this.newreg.surname || !this.newreg.email
      || !this.newreg.company) {
        this.announce = ['Hold it right there...', 'You need to supply all required fields (name, surname, company, email.)'];
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
        .then((result) => { this.announce = result; });
    },
    killMessage() {
      this.grabAllRegistrations();
      this.announce = null;
    },
  },
};
</script>
