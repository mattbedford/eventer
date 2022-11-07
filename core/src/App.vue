<template>
  <div>
    <NavBar selected="currentPage" @pageSelect="goToPage" />
    <section class="admin-main" v-if="currentPage == 'setup'">
      <setup />
    </section>

    <section class="admin-main" v-if="currentPage == 'badge'">
      <h3>This is the badge page</h3>
    </section>

    <section class="admin-main" v-if="currentPage == 'coupon'">
      <h3>This is the coupon page</h3>
    </section>

    <section class="admin-main" v-if="currentPage == 'invitation'">
      <h3>This is the invitations page</h3>
    </section>
  </div>
</template>

<script>
import NavBar from './components/NavBar.vue';
import setup from './components/setup.vue';

export default {
  name: 'App',
  data() {
    return {
      currentPage: 'setup',
    };
  },
  components: {
    NavBar, setup,
  },
  methods: {
    goToPage(slug) {
      this.currentPage = slug;
    },
  },
};
</script>
