import 'froala-editor/js/plugins.pkgd.min.js';

import VueFroala from 'vue-froala-wysiwyg';

Nova.booting((Vue, router) => {
    Vue.use(VueFroala);

    Vue.component('index-nova-froala-field', require('./components/IndexField').default);
    Vue.component('detail-nova-froala-field', require('./components/DetailField').default);
    Vue.component('form-nova-froala-field', require('./components/FormField').default);
});
