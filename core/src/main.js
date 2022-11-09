import Vue from 'vue';
import App from './App.vue';

// eslint-disable-next-line
const wpi = wpApiSettings;

Vue.config.productionTip = false;
Vue.prototype.$wp = wpi;

Vue.mixin({
  data() {
    return {
      nonce: this.$wp.nonce,
    };
  },
});

new Vue({
  render: (h) => h(App),
}).$mount('#app');
