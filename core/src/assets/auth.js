const rootUrl = document.location.origin;
const config = {
  rootUrl,
  optionsRoute: `${rootUrl}/wp-json/core-vue/options`,
  AllOptionsRoute: `${rootUrl}/wp-json/core-vue/options-all`,
};
export default config;
