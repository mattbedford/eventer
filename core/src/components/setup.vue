<template>
  <div class="sub-section">
    <div class="side-column">
        <ul>
            <li><button @click="menuItem = 'globals'">Event globals</button></li>
            <li><button @click="menuItem = 'apis'">APIs</button></li>
            <li><button @click="menuItem = 'venue'">Venue details</button></li>
            <li><button @click="menuItem = 'tickets'">Tickets & badge</button></li>
        </ul>
    </div>

    <div v-show="menuItem == 'globals'" class="main-column">
        <h3>Global setup</h3>

    </div>

    <div v-show="menuItem == 'apis'" class="main-column">
        <h3>APIs setup</h3>

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
    };
  },

};
</script>
