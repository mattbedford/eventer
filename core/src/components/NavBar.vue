<template>
  <div class="navbar">
    <div class="navbar-inner">
        <img class="logo"
        alt="Dagorà logo"
        src="\wp-content\plugins\eventer\assets\logo_dagora_white.png"
        />
        <div class="menu">
            <ul>
                <li><button @click="$emit('pageSelect', 'setup')">Event setup</button></li>
                <li><button @click="$emit('pageSelect', 'badge')">
                  Registrations & badges
                  </button>
                </li>
                <li><button @click="$emit('pageSelect', 'coupon')">Coupons</button></li>
                <li><button @click="$emit('pageSelect', 'setup')">Invitations</button></li>
            </ul>
        </div>
    </div>
  </div>
</template>

<script>
export default {
//
};
</script>
