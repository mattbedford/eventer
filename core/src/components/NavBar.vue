<template>
  <div class="navbar">
    <div class="navbar-inner">
        <img class="logo"
        alt="Dagorà logo"
        src="\wp-content\plugins\eventer\assets\logo_dagora_white.png"
        />
        <div class="menu">
            <ul>
                <li :class="{active: onPage === 'setup'}">
                  <button @click="($emit('pageSelect', 'setup'),
                  selectThis('setup'))">
                  Event setup
                </button></li>

                <li :class="{active: onPage === 'badge'}">
                  <button @click="($emit('pageSelect', 'badge'),
                selectThis('badge'))">
                  Registrations & badges
                  </button></li>

                <li :class="{active: onPage === 'coupon'}">
                  <button @click="($emit('pageSelect', 'coupon'), selectThis('coupon'))">
                    Coupons & invitations
                  </button></li>

                <li :class="{active: onPage === 'invitation'}">
                  <button @click="($emit('pageSelect', 'invitation'), selectThis('invitation'))">
                    Data
                  </button></li>
            </ul>
        </div>
    </div>
  </div>
</template>

<script>
export default {

  data() {
    return {
      onPage: 'setup',
    };
  },
  props: ['selected'],
  methods: {
    selectThis(item) {
      this.onPage = item;
    },
  },

};
</script>
