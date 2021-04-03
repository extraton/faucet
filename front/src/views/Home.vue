<template>
  <div class="home">
    <v-form v-if="!isRequested" v-model="valid" ref="form">
      <v-card>
        <v-card-title>
          Get net.ton.dev rubies
        </v-card-title>
        <v-card-text>
          <div class="home__available">
            <div class="text-h5">Available:</div>
            <div v-if="null !== available" class="text-h5 white--text">
              {{ available }}
              <v-icon style="margin-top:-10px">$vuetify.icons.tonRuby</v-icon>
            </div>
            <v-skeleton-loader v-else type="heading" width="150"/>
          </div>
          <v-text-field v-model="address" :rules="[rules.required,rules.address]" label="Address"
                        placeholder="0:xxxxxxx..."/>
          <vue-recaptcha class="home__recaptcha" ref="recaptcha" @verify="onVerify" :sitekey="config.recaptchaSiteKey"
                         :loadRecaptchaScript="true" theme="dark"/>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <div class="error--text" style="padding-left:8px">{{ error }}</div>
          <v-spacer></v-spacer>
          <v-btn @click="request" :disabled="!valid" :loading="loading||form.loading" color="primary">Get Rubies</v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
    <v-alert v-else class="home__requested" type="success" text>
      Rubies successfully requested.
    </v-alert>
  </div>
</template>

<script>
import ton from "@/lib/ton"
import {mapMutations} from "vuex";
import VueRecaptcha from 'vue-recaptcha';

export default {
  components: {VueRecaptcha},
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
    captcha: '',
    available: null,
  }),
  created() {
    this.form = this.$ewll.initForm(this, {
      url: '/request',
      isFormHandleValidationErrors: false,
      success: function () {
        this.isRequested = true;
      }.bind(this),
      error: function (response) {
        if (429 === response.status) {
         this.$snack.danger({text: 'Sorry, you recently received tokens.'});
        }
        this.$refs.recaptcha.reset();
      }.bind(this),
    });
    if (typeof this.$route.query.address === 'string') {
      this.address = this.$route.query.address;
    }
    this.setAvailable();
  },
  methods: {
    ...mapMutations('extension', [
      'openInstallDialog',
    ]),
    async setAvailable() {
      const walletAddressData = await ton.findAddressData('net.ton.dev', this.config.walletAddress);
      const walletColdAddressData = await ton.findAddressData('net.ton.dev', this.config.walletColdAddress);

      this.available = `${ton.convertFromNanoToIntView(walletAddressData.balance)} (${ton.convertFromNanoToIntView(walletColdAddressData.balance)})`;
    },
    onVerify(captcha) {
      this.captcha = captcha;
    },
    async request() {
      this.loading = true;
      try {
        if (!await ton.isExtensionAvailableWithMinimalVersion()) {
          this.openInstallDialog();
        } else {
          this.error = null;
          await this.$refs.form.validate();
          if (this.valid) {
            if (0 === this.captcha.length) {
              this.error = 'Please, get captcha tested.';
            } else {
              this.form.submit({data: {address: this.address, captcha: this.captcha}});
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
.home {
  &__available {
    display: table;
    margin: 25px auto;

    > div {
      display: table-cell;
      vertical-align: middle;
    }

    > div:first-child {
      text-align: right;
      padding-right: 10px;
    }
  }

  &__recaptcha {
    height: 78px;

    > div {
      margin: auto;
    }
  }

  &__requested {
    margin-top: 50px;
  }

  .v-skeleton-loader__heading {
    width: 100%;
  }
}
</style>
