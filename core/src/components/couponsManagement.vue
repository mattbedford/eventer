<template>
  <div class="sub-section">
    <message-announce v-if="announce" :message="announce" @closeMessage="killMessage()" />
    <vue-good-table
            :columns="columns"
            :rows="couponsList"
            theme="black-rhino"
            :search-options="{
              enabled: true
            }"
            :pagination-options="{
                enabled: true,
                perPage: 50,
                position: 'bottom',
            }">
            <div slot="table-actions">
              <button class="create-new-coupon" @click="startNewCoupon">
                <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path d="M256 48C141.31 48 48 141.31 48 256s93.31 208 208 208 208-93.31 208-208S370.69 48 256 48zm80 224h-64v64a16 16 0 0 1-32 0v-64h-64a16 16 0 0 1 0-32h64v-64a16 16 0 0 1 32 0v64h64a16 16 0 0 1 0 32z"/></svg>
              </button>
            </div>
            <template slot="table-row" slot-scope="props">
                <span v-if="props.column.field == 'edit'">
                    <button @click="editCoupon(props.row.originalIndex)">Edit</button>
                </span>
            </template>
        </vue-good-table>

        <div class="edit-shelf" v-if="(couponToEdit !== null)" v-scroll-lock="couponToEdit">
            <div class="content-wrap">
                <span class="options-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path d="M459.94 53.25a16.06 16.06 0 0 0-23.22-.56L424.35 65a8 8 0 0 0 0 11.31l11.34 11.32a8 8 0 0 0 11.34 0l12.06-12c6.1-6.09 6.67-16.01.85-22.38zM399.34 90 218.82 270.2a9 9 0 0 0-2.31 3.93L208.16 299a3.91 3.91 0 0 0 4.86 4.86l24.85-8.35a9 9 0 0 0 3.93-2.31L422 112.66a9 9 0 0 0 0-12.66l-9.95-10a9 9 0 0 0-12.71 0z"/><path d="M386.34 193.66 264.45 315.79A41.08 41.08 0 0 1 247.58 326l-25.9 8.67a35.92 35.92 0 0 1-44.33-44.33l8.67-25.9a41.08 41.08 0 0 1 10.19-16.87l122.13-121.91a8 8 0 0 0-5.65-13.66H104a56 56 0 0 0-56 56v240a56 56 0 0 0 56 56h240a56 56 0 0 0 56-56V199.31a8 8 0 0 0-13.66-5.65z"/></svg>
                    <h2>Edit coupon</h2>
                </span>

                <button class="close-button" @click="(couponToEdit = null)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32" d="M448 256c0-106-86-192-192-192S64 150 64 256s86 192 192 192 192-86 192-192z"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32" d="M320 320 192 192m0 128 128-128"/></svg>
                </button>
                <form id="edit-registrant">
                    <h2>Coupon details</h2>
                    <input type="hidden" v-model="couponToEdit.invitation_post_id">
                    <div class="double">
                        <label for="code">Coupon code
                        <input type="text" id="code" v-model="couponToEdit.coupon_title"/></label>

                        <label for="lname">Discount
                        <input type="text" id="lname" v-model="couponToEdit.discount"/></label>
                    </div>
                    <div class="double">
                        <label for="max-uses">Max uses allowed
                            <input type="number" id="max-uses"
                            v-model="couponToEdit.max_uses"/>
                        </label>
                        <label for="actual-uses">Actual uses
                            <input type="number" id="actual-uses"
                            v-model="couponToEdit.actual_uses" disabled/>
                        </label>
                    </div>

                    <h2>Invitation details</h2>
                    <div class="double">
                        <label for="related-post">Recipient
                        <select name="related-post" v-model="couponToEdit.recipient_id">
                            <option value="other">Other</option>
                            <option v-for="(item, index) in usersList"
                            :value="item.id"
                            :key="index">
                            {{item.name}}
                            </option>
                        </select></label>

                        <label for="other-recipient" v-show="couponToEdit.recipient_id === 'other'">
                            Other recipient
                            <input type="text"
                                id="other-recipient"
                                v-model="couponToEdit.recipient_name"/>
                        </label>
                    </div>

                    <label for="coupon-type">Coupon type
                        <select id="coupon-type" v-model="couponToEdit.invitation_type">
                            <option value="generic">Generic coupon</option>
                            <option value="Netcomm member">Netcomm member</option>
                            <option value="Dagorà member">Dagorà member</option>
                            <option value="LTCC member">LTCC member</option>
                            <option value="Brand-retailer-manufacturer">
                                Brand/Retailer/Manufacturer</option>
                            <option value="Staff">Staff</option>
                            <option value="Speaker">Speaker</option>
                            <option value="Sponsor">Sponsor</option>
                            <option value="Prospect">Prospect</option>
                            <option value="Institution">Institution</option>
                            <option value="School-university">School/University</option>
                        </select>
                        </label>

                        <label for="related-post">Invitation layout
                        <select id="coupon-type" v-model="couponToEdit.with_headliners">
                            <option value="standard">Standard</option>
                            <option value="custom">Custom</option>
                        </select></label>

                        <div style="padding-bottom:40px;">
                            <span v-show="couponToEdit.with_headliners == 'custom'">
                                Choose who appears on the invitation
                            </span>
                            <v-select multiple
                                id="custom-headliners"
                                name="custom-headliners"
                                v-show="couponToEdit.with_headliners == 'custom'"
                                v-model="couponToEdit.headliners"
                                :options="speakersList"
                                :reduce="name => name.id"
                                label="id">
                                </v-select>
                        </div>

                    <div class="double">
                        <button v-if="couponToEdit.invitation_post_id"
                        class="save-edits form-button"
                        type="button" @click="editCoupon('edit')">
                        Save edits
                        </button>
                        <button v-else
                        class="save-edits form-button"
                        type="button" @click="editCoupon('create')">
                        Save edits
                        </button>
                        <button
                        v-if="couponToEdit.id"
                        class="delete-registration form-button"
                        type="button"
                        @click="editCoupon('delete')">
                        Delete coupon
                        </button>
                    </div>
                </form>
            </div>
        </div>
  </div>
</template>

<script>
import auth from '@/assets/auth';
import 'vue-good-table/dist/vue-good-table.css';
import { VueGoodTable } from 'vue-good-table';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

export default {
  name: 'CouponsManagement',
  components: { VueGoodTable, vSelect },
  data() {
    return {
      couponToEdit: null,
      couponsList: [],
      usersList: [],
      speakersList: [],
      columns: [
        {
          label: 'Coupon',
          field: 'coupon_title',
        },
        {
          label: 'Discount %',
          field: 'discount',
        },
        {
          label: 'Recipient',
          field: 'recipient_name',
        },
        {
          label: 'For guests?',
          field: this.guestCheck,
        },
        {
          label: 'Invitation type',
          field: 'invitation_type',
        },
        {
          label: 'Invitation display',
          field: this.headlinersCheck,
        },
        {
          label: 'Max uses',
          field: 'max_uses',
        },
        {
          label: 'Actual uses',
          field: 'actual_uses',
        },
        {
          label: 'View/Edit',
          field: 'edit',
        },
      ],
    };
  },
  methods: {
    async grabCoupons() {
      this.couponsList = [];
      const url = auth.allCoupons;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'GET', headers })
        .then((result) => result.json())
        .then((result) => { this.couponsList = result; });
    },
    async grabUsers() {
      this.usersList = [];
      const url = auth.peopleAndOrgs;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'GET', headers })
        .then((result) => result.json())
        .then((result) => { this.usersList = result; });
    },
    async grabSpeakers() {
      this.usersList = [];
      const url = auth.onlyPeople;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'GET', headers })
        .then((result) => result.json())
        .then((result) => { this.speakersList = result; });
    },
    guestCheck(rowObj) {
      if (rowObj.guest_status === '1') {
        return 'Yes';
      }
      return 'No';
    },
    headlinersCheck(rowObj) {
      if (rowObj.headliners.length >= 1) {
        return 'Custom';
      }
      return 'Standard';
    },
    editCoupon(rowId) {
      this.couponToEdit = this.couponsList[rowId];
    },
    startNewCoupon() {
      this.couponToEdit = {};
    },
  },
  mounted() {
    this.grabCoupons();
    this.grabUsers();
    this.grabSpeakers();
  },

};
</script>
