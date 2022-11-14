<template>
  <div class="message-announce">
    <p>{{message}}</p>
    <button class="closer" @click="$emit('closeMessage')">&#x2716;</button>
  </div>
</template>

<script>

export default {
  props: {
    message: {
      type: String,
      default: 'Options saved',
    },
  },
};
</script>
