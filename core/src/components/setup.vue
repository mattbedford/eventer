<template>
  <div class="sub-section">
    <div class="side-column">
        <ul class="submenu">
            <li :class="{'active' : menuItem == 'globals'}">
                <button @click="menuItem = 'globals'">Event globals</button>
            </li>
            <li :class="{'active' : menuItem == 'apis'}">
                <button @click="menuItem = 'apis'">APIs</button>
            </li>
            <li :class="{'active' : menuItem == 'venue'}">
                <button @click="menuItem = 'venue'">Venue details</button>
            </li>
            <li :class="{'active' : menuItem == 'tickets'}">
                <button @click="menuItem = 'tickets'">Tickets & badge</button>
            </li>
        </ul>
    </div>
    <message-announce v-if="announce" :message="announce" @closeMessage="killMessage()" />
    <div v-show="menuItem == 'globals'" class="main-column">
        <h3>Global setup</h3>
        <form id="globals" @submit.prevent="sendOptions('globals')">
            <label for="name">Event name
            <input type="text" id="name" v-model="globals.event_name"/></label>
            <label for="payoff">Event payoff
            <input type="text" id="payoff" v-model="globals.event_payoff"/></label>
            <label for="date">Event date
            <input type="date" id="date" v-model="globals.event_date"/></label>
            <div class="double">
                <label for="start">Kick-off time
                <input type="time" id="start" v-model="globals.event_start"/></label>
                <label for="end">Event end time
                <input type="time" id="end" v-model="globals.event_end"/></label>
            </div>

            <input type="submit" value="Save edits"/>
        </form>
    </div>

    <div v-show="menuItem == 'apis'" class="main-column">
        <h3>APIs setup</h3>
        <form id="apis" @submit.prevent="sendOptions('apis')">
            <label for="stripe_key">Stripe API key
            <input type="text" id="stripe_key" v-model="apis.alt_stripe_key"/></label>
            <label for="webhook">Stripe webhook key
            <input type="text" id="webhook" v-model="apis.stripe_webhook"/></label>
            <label for="hubspot-api">Hubspot API key
            <input type="text" id="hubspot-api" v-model="apis.hubspot_key"/></label>
            <div class="double">
                <label for="hubspot-list">Hubspot list ID
                <input type="text" id="hubspot-list" v-model="apis.hubspot_list"/></label>
                <label for="tag">Hubspot event tag (used in form to link to static list)
                <input type="text" id="tag" v-model="apis.event_tag"/></label>
            </div>

            <input type="submit" value="Save edits"/>
        </form>
    </div>

    <div v-show="menuItem == 'venue'" class="main-column">
        <h3>Venue setup</h3>
        <form id="venue" @submit.prevent="sendOptions('venue')">
            <label for="venue_name">Name of venue
            <input type="text" id="venue_name" v-model="venue.venue_name"/></label>
            <label for="venue_address">Venue address
            <input type="text" id="venue_address" v-model="venue.venue_address"/></label>
            <div class="double">
              <label for="venue_city">Venue city
              <input type="text" id="venue_city" v-model="venue.venue_city"/></label>
              <label for="venue_country">Venue country
              <input type="text" id="venue_country" v-model="venue.venue_country"/></label>
            </div>
            <label for="venue_max">Maximum attendees permitted
            <input type="number" id="venue_max" v-model="venue.max_attendees"/></label>

            <input type="submit" value="Save edits"/>
        </form>
    </div>

    <div v-show="menuItem == 'tickets'" class="main-column">
        <h3>Tickets & badge setup</h3>
        <form id="badge" @submit.prevent="sendOptions('badges')">
            <label for="badge_template">Badge template
            <input type="file" id="badge_template"/></label>
            <label for="ticket_price">Ticket price
            <input type="number" id="ticket_price" v-model="badges.ticket_price"/></label>
            <p>Text positioning badge page 1</p>
            <div class="double">
              <label for="p1x">X coordinate
              <input type="number" id="p1x" v-model="badges.badge_x"/></label>
              <label for="p1y">Y coordinate
              <input type="number" id="p1y" v-model="badges.badge_y"/></label>
            </div>
            <p>Text positioning badge page 2</p>
            <div class="double">
              <label for="p2x">X coordinate
              <input type="number" id="p2x" v-model="badges.badge_x_p2"/></label>
              <label for="p2y">Y coordinate
              <input type="number" id="p2y" v-model="badges.badge_y_p2"/></label>
            </div>

            <input type="submit" value="Save edits"/>
        </form>
    </div>
  </div>
</template>

<script>
import auth from '@/assets/auth';
import MessageAnnounce from './MessageAnnounce.vue';

export default {

  name: 'SetUp',
  components: {
    MessageAnnounce,
  },
  data() {
    return {
      menuItem: 'globals',
      announce: null,
      globals: {
        event_name: 'xxxx',
        event_payoff: 'ccccc',
        event_date: null,
        event_start: null,
        event_end: null,
      },
      apis: {
        alt_stripe_key: null,
        stripe_webhook: null,
        event_tag: null,
        hubspot_list: null,
        hubspot_key: null,
      },
      venue: {
        venue_name: null,
        venue_address: null,
        venue_city: null,
        venue_country: null,
        max_attendees: null,
      },
      badges: {
        badge_template: null,
        ticket_price: null,
        badge_x: null,
        badge_y: null,
        badge_x_p2: null,
        badge_y_p2: null,
      },
    };
  },
  methods: {
    killMessage() {
      this.announce = null;
    },
    async sendOptions(dataObj) {
      const data = JSON.stringify(this[dataObj]);
      const url = auth.optionsRoute;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'POST', headers, body: data })
        .then((result) => result.json())
        .then((result) => { this.announce = result; });
    },
    async getAllOptions() {
      const url = auth.AllOptionsRoute;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
        'X-WP-Nonce': this.nonce,
      };
      fetch(url, { method: 'GET', headers })
        .then((result) => result.json())
        .then((result) => {
          Object.entries(result).forEach(([key]) => {
            const reskey = result[key][0];
            const resval = result[key][1];
            if (this.globals[reskey] || this.globals[reskey] === null) {
              this.globals[reskey] = resval;
            }
            if (this.apis[reskey] || this.apis[reskey] === null) {
              this.apis[reskey] = resval;
            }
            if (this.venue[reskey] || this.venue[reskey] === null) {
              this.venue[reskey] = resval;
            }
            if (this.badges[reskey] || this.badges[reskey] === null) {
              this.badges[reskey] = resval;
            }
          });
        });
    },
  },
  mounted() {
    this.getAllOptions();
  },
};
</script>
