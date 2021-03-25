<template>
  <div class="faucetCard">
    <v-form v-if="!isRequested" v-model="valid" ref="form">
      <v-card>
        <v-card-title>
          Get net.ton.dev rubies
        </v-card-title>
        <v-card-text>
          <v-text-field v-model="address" :rules="[rules.required,rules.address]" label="Address"
                        placeholder="0:xxxxxxx..."/>
          <div class="g-recaptcha" :data-sitekey="config.recaptchaSiteKey" data-theme="dark"></div>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <div class="error--text" style="padding-left:8px">{{ error }}</div>
          <v-spacer></v-spacer>
          <v-btn @click="request" :disabled="!valid" :loading="loading||form.loading" color="primary">Get Rubies</v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
    <v-alert v-else class="faucetCard__requested" type="success" text>
      Rubies successfully requested.
    </v-alert>
  </div>
</template>

<script>
import utils from "@/utils";
import {mapMutations} from "vuex";

export default {
  data: () => ({
    config: global.config,
    rules: {
      required: value => !!value || 'Required.',
      address: value => /^-?[0-9]:[a-f0-9]{64}$/i.test(value) || 'Incorrect address.',
    },
    valid: true,
    error: null,
    loading: false,
    address: '',
    isRequested: false,
    form: null,
  }),
  created() {
    this.form = this.$ewll.initForm(this, {
      url: '/request',
      isFormHandleValidationErrors: false,
      success: function () {
        this.isRequested = true;
      }.bind(this),
    });
  },
  computed: {},
  methods: {
    ...mapMutations('extension', [
      'openInstallDialog',
    ]),
    async request() {
      this.loading = true;
      try {
        if (!await utils.isExtensionAvailableWithMinimalVersion()) {
          this.openInstallDialog();
        } else {
          this.error = null;
          await this.$refs.form.validate();
          if (this.valid) {
            const captcha = global.grecaptcha.getResponse();
            if (0 === captcha.length) {
              this.error = 'Please, get captcha tested.';
            } else {
              this.form.submit({data: {address: this.address, captcha}});
            }

          }
        }
      } finally {
        this.loading = false;
      }
    },
  }
}
</script>

<style lang="scss">
.faucetCard {
  .g-recaptcha {
    height: 78px;

    > div {
      margin: auto;

    }
  }

  &__requested {
    margin-top: 50px;
  }
}
</style>
