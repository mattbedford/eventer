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

    <div v-show="menuItem == 'globals'" class="main-column">
        <h3>Global setup</h3>
        <form id="globals" @submit.prevent="sendOptions('globals')">
            <label for="name">Event name
            <input type="text" id="name" v-model="globals.name"/></label>
            <label for="payoff">Event payoff
            <input type="text" id="payoff" v-model="globals.payoff"/></label>
            <label for="date">Event date
            <input type="date" id="date" v-model="globals.date"/></label>
            <div class="double">
                <label for="start">Kick-off time
                <input type="time" id="start" v-model="globals.start"/></label>
                <label for="end">Event end time
                <input type="time" id="end" v-model="globals.end"/></label>
            </div>

            <input type="submit" value="Save edits"/>
        </form>
    </div>

    <div v-show="menuItem == 'apis'" class="main-column">
        <h3>APIs setup</h3>
        <form id="apis">
            <label for="stripe_key">Stripe API key
            <input type="text" id="stripe_key" v-model="apis.stripe_api"/></label>
            <label for="webhook">Stripe webhook key
            <input type="text" id="webhook" v-model="apis.stripe_webhook"/></label>
            <label for="hubspot-api">Hubspot API key
            <input type="text" id="hubspot-api" v-model="apis.hubspot"/></label>
            <div class="double">
                <label for="hubspot-list">Hubspot list ID
                <input type="text" id="hubspot-list" v-model="apis.list_id"/></label>
                <label for="tag">Hubspot event tag (used in form to link to static list)
                <input type="text" id="tag" v-model="apis.tag"/></label>
            </div>

            <input type="submit" value="Save edits"/>
        </form>
    </div>

    <div v-show="menuItem == 'venue'" class="main-column">
        <h3>Venue setup</h3>
        <form id="venue">
            <label for="venue_name">Name of venue
            <input type="text" id="venue_name" v-model="venue.name"/></label>
            <label for="venue_address">Venue address
            <input type="text" id="venue_address" v-model="venue.address"/></label>
            <div class="double">
              <label for="venue_city">Venue city
              <input type="text" id="venue_city" v-model="venue.city"/></label>
              <label for="venue_country">Venue country
              <input type="text" id="venue_country" v-model="venue.country"/></label>
            </div>
            <label for="venue_max">Maximum attendees permitted
            <input type="number" id="venue_max" v-model="venue.max_attendees"/></label>

            <input type="submit" value="Save edits"/>
        </form>
    </div>

    <div v-show="menuItem == 'tickets'" class="main-column">
        <h3>Tickets & badge setup</h3>
        <form id="badge">
            <label for="badge_template">Badge template
            <input type="file" id="badge_template"/></label>
            <label for="ticket_price">Ticket price
            <input type="number" id="ticket_price" v-model="ticket_price"/></label>
            <p>Text positioning badge page 1</p>
            <div class="double">
              <label for="p1x">X coordinate
              <input type="number" id="p1x" v-model="venue.city"/></label>
              <label for="p1y">Y coordinate
              <input type="number" id="p1y" v-model="venue.country"/></label>
            </div>
            <p>Text positioning badge page 2</p>
            <div class="double">
              <label for="p2x">X coordinate
              <input type="number" id="p2x" v-model="venue.city"/></label>
              <label for="p2y">Y coordinate
              <input type="number" id="p2y" v-model="venue.country"/></label>
            </div>

            <input type="submit" value="Save edits"/>
        </form>
    </div>
  </div>
</template>

<script>
import auth from '@/assets/auth';

export default {

  name: 'SetUp',
  data() {
    return {
      menuItem: 'globals',
      globals: {
        name: null,
        payoff: null,
        date: null,
        start: null,
        end: null,
      },
      apis: {
        stripe_api: null,
        webhook: null,
        tag: null,
        list_id: null,
        hubspot_api_key: null,
      },
      venue: {
        name: null,
        address: null,
        city: null,
        country: null,
        max_attendees: null,
      },
      badge_template: null,
      ticket_price: null,
      p1x: null,
      p1y: null,
      p2x: null,
      p2y: null,
    };
  },
  methods: {
    async sendOptions(dataObj) {
      const data = JSON.stringify(this[dataObj]);
      const url = auth.optionsRoute;
      const headers = {
        credentials: 'same-origin',
        'Content-Type': 'application/json',
      };
      fetch(url, { method: 'POST', headers, body: data })
        .then((result) => {
          console.log(result);
        });
    },
  },
};
</script>
