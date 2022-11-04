<template>
  <div>
    <NavBar @pageSelect="goToPage()" />
  </div>
</template>

<script>
import NavBar from './components/NavBar.vue';

export default {
  name: 'App',
  data() {
    return {
      currentPage: 'setup',
    };
  },
  components: {
    NavBar,
  },
  methods: {
    goToPage: (slug) => {
      this.currentPage = slug;
    },
  },
};
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
