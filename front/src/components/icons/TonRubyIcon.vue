<template>
  <img :src="require(`@/assets/ruby48.png`)" :style="{width, height}" alt="TON Ruby"/>
</template>

<script>
export default {
  props: {
    iconName: {
      type: String,
      default: 'ton-ruby'
    },
    width: {
      type: [Number, String],
      default: 18
    },
    height: {
      type: [Number, String],
      default: 18
    },
    iconColor: {
      type: String,
      default: 'currentColor'
    }
  }
}
</script>
