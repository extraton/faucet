import Vue from 'vue'
import App from './App.vue'
import store from './store'
import vuetify from './plugins/vuetify';
import VueResource from 'vue-resource';
import Ewll from './../Main';

let snack = {
  install(Vue) {
    Vue.prototype.$snack = {
      listener: null,
      success(data) {
        if (null !== this.listener) {
          this.listener(data.text);
        }
      },
      danger(data) {
        return this.success(data);
      }
    }
  }
}

Vue.use(VueResource);
Vue.use(snack);
Vue.use(Ewll);

Vue.config.productionTip = false

new Vue({
  store,
  vuetify,
  render: h => h(App)
}).$mount('#app')
