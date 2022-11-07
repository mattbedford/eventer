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
        <form id="globals">
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
                <label for="tag">Hubspot event tag (used in form to link to Hubspot)
                <input type="text" id="tag" v-model="apis.tag"/></label>
            </div>

            <input type="submit" value="Save edits"/>
        </form>
    </div>

    <div v-show="menuItem == 'venue'" class="main-column">
        <h3>Venue setup</h3>

    </div>

    <div v-show="menuItem == 'tickets'" class="main-column">
        <h3>Tickets & badge setup</h3>

    </div>
  </div>
</template>

<script>
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
    };
  },

};
</script>
