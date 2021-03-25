import Vue from 'vue';
import Vuetify from 'vuetify/lib/framework';
import TonCrystalIcon from "@/components/icons/TonCrystalIcon";
import TonRubyIcon from "@/components/icons/TonRubyIcon";

Vue.use(Vuetify);

export default new Vuetify({
  theme: {
    dark: true,
  },
  icons: {
    values: {
      tonCrystal: {
        component: TonCrystalIcon,
      },
      tonRuby: {
        component: TonRubyIcon,
      },
    },
  },
});
