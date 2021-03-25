<template>
  <v-dialog v-model="dialogInstall" max-width="500">
    <v-card>
      <v-card-title>
        Install extraTON extension
      </v-card-title>
      <v-card-text>
        <p>In order to use this DApp you need to install extraTON extension with minimal version 0.12.1.</p>
        <p>Go to <a href="https://chrome.google.com/webstore/detail/extraton/hhimbkmlnofjdajamcojlcmgialocllm"
                    target="_blank">Chrome Store</a>.</p>
      </v-card-text>
      <v-divider></v-divider>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="closeInstallDialog" color="primary" text>ok</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import {mapMutations} from "vuex";

export default {
  computed: {
    dialogInstall: {
      get () {
        return this.$store.state.extension.dialogInstall
      },
      set () {
        this.closeInstallDialog();
      }
    },
  },
  methods: {
    ...mapMutations('extension', [
      'closeInstallDialog',
    ]),
  }
}
</script>
